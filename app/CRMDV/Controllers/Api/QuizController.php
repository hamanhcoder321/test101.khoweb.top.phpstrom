<?php

namespace App\CRMDV\Controllers\Api;

use App\Library\JWT\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Validator;

class QuizController extends \App\Http\Controllers\Controller
{
    /**
     * Bắt đầu làm bài thi
     */
    public function start(Request $request, $quiz_id)
    {
        $user =JWTAuth::parseToken();
        $quiz = DB::table('quiz')->find($quiz_id);
        if (!$quiz) {
            return response()->json(['status' => false, 'msg' => 'Không tìm thấy bài thi'], 404);
        }

        // Kiểm tra đã làm chưa (tùy chính sách: cho làm lại hay không)
        $done = DB::table('quiz_history')
            ->where('admin_id', $user->id)
            ->where('quiz_id', $quiz_id)
            ->whereNotNull('submitted_at')
            ->exists();

//        if ($done) {
//            return response()->json(['status' => false, 'msg' => 'Bạn đã hoàn thành bài thi này rồi!'], 403);
//        }

        // Lấy câu hỏi theo nhóm độ khó (có thể random mỗi nhóm)
        $questions = collect();
        $groups = DB::table('quiz_question_group')->orderBy('order_no')->get();

        foreach ($groups as $group) {
            $qs = DB::table('quiz_questions')
                ->where('quiz_id', $quiz_id)
                ->where('quiz_question_group_id', $group->id)
                ->inRandomOrder()
                ->get(['id', 'question', 'a', 'b', 'c', 'd']);

            $questions = $questions->merge($qs);
        }

        if ($questions->isEmpty()) {
            return response()->json(['status' => false, 'msg' => 'Bài thi chưa có câu hỏi'], 400);
        }
        // Tạo bản ghi làm bài
        $history_id = DB::table('quiz_history')->insertGetId([
            'admin_id' => $user->id,
            'quiz_id' => $quiz_id,
            'total_questions' => $questions->count(),
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Bắt đầu làm bài thành công',
            'data' => [
                'history_id' => $history_id,
                'quiz_name' => $quiz->name,
                'total_questions' => $questions->count(),
                'questions' => $questions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'question' => $q->question,
                        'options' => [
                            'a' => $q->a,
                            'b' => $q->b,
                            'c' => $q->c,
                            'd' => $q->d,
                        ]
                    ];
                })
            ]
        ]);
    }
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'history_id' => 'required|integer',
            'answers'    => 'required|array',
            'answers.*'  => 'required|in:a,b,c,d'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 422);
        }

        $history_id = $request->history_id;
        $userAnswers = $request->answers; // ["12" => "a", "15" => "c", ...]
        $history = DB::table('quiz_history')->where('id', $history_id)->first();

        if (!$history) {
            return response()->json(['status' => false, 'msg' => 'Phiên làm bài không hợp lệ'], 400);
        }

//        $history = DB::table('quiz_history')->where('id', $history_id)->first();
//        if (!$history || $history->submitted_at) {
//            return response()->json(['status' => false, 'msg' => 'Phiên làm bài không hợp lệ hoặc đã nộp'], 400);
//        }

        // Lấy đáp án đúng
        $questionIds = array_keys($userAnswers);
        $correctAnswers = DB::table('quiz_questions')
            ->whereIn('id', $questionIds)
            ->pluck('answer', 'id'); // ['12' => 'a', '15' => 'd']

        $correctCount = 0;
        $detail = [];

        foreach ($userAnswers as $qid => $ans) {
            $correct = $correctAnswers[$qid] ?? null;
            $isCorrect = strtolower($ans) === $correct;

            if ($isCorrect) $correctCount++;

            $explain = DB::table('quiz_questions')
                ->where('id', $qid)
                ->value('explain_the_answer');

            $detail[] = [
                'question_id' => (int)$qid,
                'user_answer' => strtoupper($ans),
                'correct_answer' => strtoupper($correct),
                'is_correct' => $isCorrect,
                'explain' => $explain ?? ''
            ];
        }

        $score = $history->total_questions > 0
            ? round(($correctCount / $history->total_questions) * 100, 1)
            : 0;

        $timeSpent = now()->diffInSeconds($history->started_at);

        DB::table('quiz_history')->where('id', $history_id)->update([
            'score' => $score,
            'correct_count' => $correctCount,
            'answers' => json_encode($userAnswers),
            'time_spent' => $timeSpent,
            'submitted_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Nộp bài thành công!',
            'data' => [
                'score' => $score . '%',
                'correct' => "$correctCount/{$history->total_questions}",
                'time_spent' => $this->formatTime($timeSpent),
                'detail' => $detail
            ]
        ]);
    }

    /**
     * Xem lại kết quả đã làm
     */
    public function result($history_id)
    {
        $history = DB::table('quiz_history')
            ->join('quiz', 'quiz.id', '=', 'quiz_history.quiz_id')
            ->where('quiz_history.id', $history_id)
            ->select('quiz_history.*', 'quiz.name as quiz_name')
            ->first();

        if (!$history) {
            return response()->json(['status' => false, 'msg' => 'Không tìm thấy kết quả'], 404);
        }

        $userAnswers = json_decode($history->answers, true) ?? [];
        $questionIds = array_keys($userAnswers);

        $questions = DB::table('quiz_questions')
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        $detail = [];
        foreach ($userAnswers as $qid => $ans) {
            $q = $questions[$qid] ?? null;
            if (!$q) continue;

            $detail[] = [
                'question' => $q->question,
                'user_answer' => strtoupper($ans),
                'correct_answer' => strtoupper($q->answer),
                'is_correct' => strtolower($ans) === $q->answer,
                'explain' => $q->explain_the_answer ?? '',
                'options' => ['A'=>$q->a, 'B'=>$q->b, 'C'=>$q->c, 'D'=>$q->d]
            ];
        }

        return response()->json([
            'status' => true,
            'data' => [
                'quiz_name' => $history->quiz_name,
                'score' => $history->score . '%',
                'correct' => "$history->correct_count/{$history->total_questions}",
                'time_spent' => $this->formatTime($history->time_spent),
                'submitted_at' => $history->submitted_at,
                'detail' => $detail
            ]
        ]);
    }

    /**
     * Danh sách bài đã làm của user
     */
    public function history(Request $request)
    {
        $user =JWTAuth::parseToken();
        $list = DB::table('quiz_history')
            ->join('quiz', 'quiz.id', '=', 'quiz_history.quiz_id')
            ->where('quiz_history.admin_id', $user->id)
            ->whereNotNull('quiz_history.submitted_at')
            ->select(
                'quiz_history.id',
                'quiz.name as quiz_name',
                'quiz_history.score',
                'quiz_history.correct_count',
                'quiz_history.total_questions',
                'quiz_history.submitted_at'
            )
            ->orderByDesc('quiz_history.submitted_at')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $list->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quiz_name' => $item->quiz_name,
                    'score' => $item->score . '%',
                    'correct' => "$item->correct_count/$item->total_questions",
                    'submitted_at' => Carbon::parse($item->submitted_at)->format('d/m/Y H:i')
                ];
            })
        ]);
    }

    private function formatTime($seconds)
    {
        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;
        return "$minutes phút $secs giây";
    }
}