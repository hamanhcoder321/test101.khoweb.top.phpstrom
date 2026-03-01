<?php

namespace App\CRMDV\Controllers\Api;

use App\Http\Controllers\Admin\CURDBaseController;
use Illuminate\Http\Request;
use Validator;
use DB;
use Auth;
class QuizQuestionController extends CURDBaseController
{
    protected $module = [
        'code'       => 'quiz_question',
        'table_name' => 'quiz_questions',
        'label'      => 'Câu hỏi Quiz',
        'modal'      => '\App\CRMDV\Models\QuizQuestion',   // tạo model nếu chưa có
        'list'       => [
            ['name' => 'id',                'type' => 'text',      'label' => 'ID',      'sort' => true],
            ['name' => 'quiz_id',           'type' => 'text',      'label' => 'ID Quiz', 'sort' => true],
            ['name' => 'question',         'type' => 'text_long', 'label' => 'Câu hỏi', 'sort' => true],
            ['name' => 'answer',            'type' => 'text',      'label' => 'Đáp án đúng', 'sort' => true],
            ['name' => 'explain_the_answer','type' => 'text',      'label' => 'Giải thích',  'sort' => false],
            ['name' => 'created_at',        'type' => 'datetime',  'label' => 'Ngày tạo',    'sort' => true],
        ],
        'form' => [
            'general_tab' => [
                ['name' => 'quiz_id',   'type' => 'text',     'class' => 'required', 'label' => 'ID Quiz (bài thi)', 'group_class' => 'col-md-6'],
                ['name' => 'question',  'type' => 'textarea', 'class' => 'required', 'label' => 'Câu hỏi',           'group_class' => 'col-md-12', 'inner' => 'rows=4'],
                ['name' => 'a',         'type' => 'text',     'label' => 'Đáp án A',          'group_class' => 'col-md-6'],
                ['name' => 'b',         'type' => 'text',     'label' => 'Đáp án B',          'group_class' => 'col-md-6'],
                ['name' => 'c',         'type' => 'text',     'label' => 'Đáp án C',          'group_class' => 'col-md-6'],
                ['name' => 'd',         'type' => 'text',     'label' => 'Đáp án D',          'group_class' => 'col-md-6'],
                ['name' => 'answer',    'type' => 'select',   'class' => 'required',
                    'options' => ['a'=>'A', 'b'=>'B', 'c'=>'C', 'd'=>'D'],
                    'label' => 'Đáp án đúng', 'group_class' => 'col-md-6'],
                ['name' => 'explain_the_answer', 'type' => 'textarea', 'label' => 'Giải thích đáp án', 'group_class' => 'col-md-12', 'inner' => 'rows=4'],
                ['name' => 'quiz_question_group_id', 'type' => 'text', 'label' => 'Nhóm câu hỏi (ID)', 'group_class' => 'col-md-6'],
            ],
        ]
    ];

    protected $quick_search = [
        'label'  => 'ID, câu hỏi, đáp án',
        'fields' => 'id, question, a, b, c, d, answer, explain_the_answer'
    ];

    protected $filter = [
        'quiz_id' => [
            'label'      => 'ID Quiz',
            'type'       => 'text',
            'query_type' => '='
        ],
        'answer' => [
            'label'      => 'Đáp án đúng',
            'type'       => 'select',
            'options'    => [
                '' => 'Tất cả',
                'a' => 'A',
                'b' => 'B',
                'c' => 'C',
                'd' => 'D',
            ],
            'query_type' => '='
        ],
        'created_at' => [
            'label' => 'Ngày tạo',
            'type'  => 'date_range',
            'field' => 'created_at'
        ],
    ];
    public function quickSearch($listItem, $r)
    {
        if (!empty($r->quick_search)) {
            $value = trim($r->quick_search);

            $listItem = $listItem->where(function ($q) use ($value) {
                foreach (explode(',', $this->quick_search['fields']) as $field) {
                    $q->orWhere($field, 'LIKE', "%{$value}%");
                }
                // Tìm theo quiz_id chính xác
                if (is_numeric($value)) {
                    $q->orWhere('quiz_id', $value);
                }
            });
        }

        return $listItem;
    }

    public function appendWhere($query, $request)
    {
        // Lọc theo khoảng thời gian tạo
        if ($request->filled('created_at')) {
            $parts = explode(' - ', $request->created_at);
            if (count($parts) === 2) {
                $from = trim($parts[0]) . ' 00:00:00';
                $to   = trim($parts[1]) . ' 23:59:59';
                $query->whereBetween('created_at', [$from, $to]);
            }
        }

        return $query;
    }

    public function getAll(Request $request)
    {
        $dataList = $this->getDataList($request);
        $paginated = $dataList['listItem'];

        $items = $paginated->getCollection()->map(function ($item) {
            return [
                'id'                    => $item->id,
                'quiz_id'               => $item->quiz_id,
                'question'              => strip_tags($item->question),
                'a'                     => $item->a,
                'b'                     => $item->b,
                'c'                     => $item->c,
                'd'                     => $item->d,
                'answer'                => strtoupper($item->answer),
                'explain_the_answer'    => $item->explain_the_answer,
//                'group_id'              => $item->quiz_question_group_id,
//                'created_at'            => $item->created_at ? $item->created_at->format('d/m/Y H:i') : '',
            ];
        });

        return response()->json([
            'status'    => true,
            'msg'       => 'Lấy danh sách câu hỏi thành công',
            'data'      => $items,
            'paginate'  => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function show($id)
    {
        $question = DB::table('quiz_questions')->find($id);

        if (!$question) {
            return response()->json([
                'status' => false,
                'msg'    => 'Không tìm thấy câu hỏi'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'msg'    => 'Chi tiết câu hỏi',
            'data'   => [
                'id'                    => $question->id,
                'quiz_id'               => $question->quiz_id,
                'question'              => $question->question,
                'a'                     => $question->a,
                'b'                     => $question->b,
                'c'                     => $question->c,
                'd'                     => $question->d,
                'answer'                => strtoupper($question->answer),
                'explain_the_answer'    => $question->explain_the_answer,
                'group_id'              => $question->quiz_question_group_id,
                'created_at'            => $question->created_at,
                'updated_at'            => $question->updated_at,
            ]
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quiz_id'   => 'required|integer',
            'question'  => 'required|string',
            'a'         => 'required|string',
            'b'         => 'required|string',
            'c'         => 'required|string',
            'd'         => 'required|string',
            'answer'    => 'required|in:a,b,c,d',
        ], [
            'quiz_id.required' => 'Vui lòng nhập ID Quiz',
            'question.required'=> 'Câu hỏi không được để trống',
            'answer.in'        => 'Đáp án đúng phải là a, b, c hoặc d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg'    => $validator->errors()->first()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $id = $request->id;

            if ($id) {
                $question = DB::table('quiz_questions')->where('id', $id)->first();
                if (!$question) {
                    return response()->json(['status' => false, 'msg' => 'Câu hỏi không tồn tại'], 404);
                }
            }

            $data = [
                'quiz_id'               => $request->quiz_id,
                'question'              => $request->question,
                'a'                     => $request->a,
                'b'                     => $request->b,
                'c'                     => $request->c,
                'd'                     => $request->d,
                'answer'                => strtolower($request->answer),
                'explain_the_answer'    => $request->explain_the_answer ?? null,
                'quiz_question_group_id'=> $request->quiz_question_group_id ?? null,
                'updated_at'            => now(),
            ];

            if ($id) {
                DB::table('quiz_questions')->where('id', $id)->update($data);
                $msg = 'Cập nhật câu hỏi thành công';
            } else {
                $data['created_at'] = now();
                $newId = DB::table('quiz_questions')->insertGetId($data);
                $data['id'] = $newId;
                $msg = 'Thêm câu hỏi thành công';
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'msg'    => $msg,
                'data'   => $data
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg'    => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $question = DB::table('quiz_questions')->where('id', $id)->first();

        if (!$question) {
            return response()->json(['status' => false, 'msg' => 'Câu hỏi không tồn tại'], 404);
        }

        DB::table('quiz_questions')->where('id', $id)->delete();

        return response()->json([
            'status' => true,
            'msg'    => 'Xóa câu hỏi thành công'
        ]);
    }
}