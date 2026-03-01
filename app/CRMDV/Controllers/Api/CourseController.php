<?php

namespace App\CRMDV\Controllers\Api;

use App\Modules\Courses\Models\UserLessonProgress;
use App\Modules\Courses\Models\Course;
use App\CRMDV\Models\Room;
use App\Library\JWT\Facades\JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\CURDBaseController;
use Illuminate\Support\Facades\DB;
use App\CRMDV\Models\Category;

use Carbon\Carbon;

class CourseController extends CURDBaseController
{
    protected $orderByRaw = 'order_no DESC';
    private $domain;
    public function __construct(){
        parent::__construct();
        $this->domain = env('APP_DOMAIN');
    }
    protected $module = [
        'code' => 'course',
        'table_name' => 'courses',
        'label' => 'Tài liệu đào tạo nội bộ',
        'modal' => '\App\CRMDV\Models\Course',
        'list' => [
            ['name' => 'name', 'type' => 'text_edit', 'label' => 'Tiêu đề', 'sort' => true],
            ['name' => 'author_name', 'type' => 'text', 'label' => 'Tác giả'],
            ['name' => 'link', 'type' => 'link', 'label' => 'Link'],
            ['name' => 'level', 'type' => 'text', 'label' => 'Cấp độ'],
            ['name' => 'status', 'type' => 'status', 'label' => 'Trạng thái'],
            ['name' => 'room.name', 'type' => 'relation', 'label' => 'Phòng ban'], // tốt hơn là dùng relation
        ],
        'form' => [
            'general_tab' => [
                ['name' => 'name', 'type' => 'text', 'class' => 'required', 'label' => 'Tiêu đề', 'group_class' => 'col-md-6'],
                ['name' => 'author_name', 'type' => 'text', 'label' => 'Tên tác giả', 'group_class' => 'col-md-6'],
                ['name' => 'link', 'type' => 'text', 'label' => 'Đường link', 'group_class' => 'col-md-9'],
                ['name' => 'order_no', 'type' => 'number', 'label' => 'Thứ tự', 'value' => 1, 'group_class' => 'col-md-3'],
                ['name' => 'multi_cat', 'type' => 'custom', 'field' => 'CRMDV.form.fields.multi_cat', 'label' => 'Danh mục', 'model' => \App\CRMDV\Models\Category::class, 'object' => 'category_course', 'where' => 'type in (1)', 'display_field' => 'name', 'multiple' => true, 'group_class' => 'col-md-8'],
                ['name' => 'status', 'type' => 'checkbox', 'label' => 'Hiển thị', 'value' => 1, 'group_class' => 'col-md-4'],
                ['name' => 'note', 'type' => 'text', 'label' => 'Từ khóa tìm kiếm', 'group_class' => 'col-md-12'],
                ['name' => 'content', 'type' => 'textarea_editor', 'label' => 'Ghi chú', 'group_class' => 'col-md-12', 'height' => '700px'],
            ],
            'remind_tab' => [
                ['name' => 'image', 'type' => 'file_image', 'label' => 'Hình ảnh khóa học'],
            ],
        ]
    ];

    protected $quick_search = [
        'label' => 'ID, tiêu đề, link, tác giả',
        'fields' => 'id,name,link,author_name,note'
    ];

    protected $filter = [
        'name' => ['label' => 'Tiêu đề', 'type' => 'text', 'query_type' => 'like'],
        'room_name' => ['label' => 'Phòng ban', 'type' => 'text', 'query_type' => 'custom'],
        'created_at' => ['label' => 'Khoảng ngày tạo', 'type' => 'from_to_date', 'query_type' => 'from_to_date'],
    ];

    public function appendWhere($query, $request)
    {
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . trim($request->name) . '%');
        }

        if ($request->filled('room_name')) {
            $room_id = Room::where('name', 'like', '%' . trim($request->room_name) . '%')
                ->value('id');
            if ($room_id) {
                $query->where('room_id', $room_id);
            }
        }

        return $query;
    }
    public function getDataList(Request $request) {
        //  Filter
        $where = $this->filterSimple($request);
        $listItem = $this->model->whereRaw($where);
        $listItem = $this->quickSearch($listItem, $request);
        if ($this->whereRaw) {
            $listItem = $listItem->whereRaw($this->whereRaw);
        }
        $listItem = $this->appendWhere($listItem, $request);

        //  Export
        if ($request->has('export')) {
            $this->exportExcel($request, $listItem->take(9000)->get());
        }

        //  Sort
        $listItem = $this->sort($request, $listItem);

        $data['record_total'] = $listItem->count();
        $data = $this->thongKe($data, $listItem, $request);

        if ($request->has('limit')) {
            $data['listItem'] = $listItem->paginate($request->limit);
            $data['limit'] = $request->limit;
        } else {
            $data['listItem'] = $listItem->paginate($this->limit_default);
            $data['limit'] = $this->limit_default;
        }
        $data['page'] = $request->get('page', 1);

        $data['param_url'] = $request->all();

        //  Get data default (param_url, filter, module) for return view
        $data['module'] = $this->module;
        $data['quick_search'] = $this->quick_search;
        $data['filter'] = $this->filter;

        //  Set data for seo
        $data['page_title'] = $this->module['label'];
        $data['page_type'] = 'list';
        return $data;
    }
    public function thongKe($data, $listItem, $request) {
        return $data;
    }
    public function getAll(Request $request)
    {
        $dataList = $this->getDataList($request);
        $paginated = $dataList['listItem'];
        $courses = $paginated->getCollection()->map(function ($course) {
            // Thay null bằng chuỗi rỗng
            $course->makeHidden(['created_at','updated_at']); // ẩn field không cần
            foreach ($course->toArray() as $key => $value) {
                $course[$key] = $value ?? '';
            };

//            dd($dataList);
            return [
                'id' => $course->id,
                'name' => $course->name,
                'author_name' => $course->author_name,
//                'link' => $course->link,
                'level' => $course->level,
//                'status' => $course->status,
                'room_name' => optional($course->room)->name ?? '',
            ];
        });

        return response()->json([
            'status' => true,
            'msg'    => 'Lấy danh sách khóa học thành công',
            'data'   =>$courses->values(),
            'paginate' => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ]
        ]
        );
    }
    public function show($id)
    {
        $course = \App\CRMDV\Models\Course::with(['chapters.lessons'])->find($id);
        if (!$course) {
            return $this->sendError('Khóa học không tồn tại', [], 404);
        }
        $user = JWTAuth::parseToken();
        $userId = $user->id;
        // Lấy danh sách lesson đã hoàn thành của user hiện tại
        $completedLessonIds = DB::table('user_lesson_progress')->where('user_id', $userId)
            ->where('completed', 1)
            ->pluck('lesson_id')
            ->toArray();

        $totalLessons = 0;
        $completedLessons = 0;

        // Duyệt chapter → lesson để thêm thông tin học tập
        foreach (optional($course->chapters) as $chapter) {
            $chapter->lessons = $chapter->lessons->map(function ($lesson) use (&$totalLessons, &$completedLessons, $completedLessonIds, $userId) {
                $totalLessons++;
                $isCompleted = in_array($lesson->id, $completedLessonIds);
                if ($isCompleted) $completedLessons++;

                $progress = DB::table('user_lesson_progress')
                    ->where('user_id', $userId)
                    ->where('lesson_id', $lesson->id)
                    ->first();
                $lesson->is_completed = (bool)$isCompleted;
                $lesson->watched_seconds = $progress->watched_seconds ?? 0;
                $lesson->video_source = $lesson->iframe ? 'iframe' : 'direct';
                $lesson->video_url = $lesson->iframe ?: $lesson->link_docs;

                return $lesson;
            });
        }
        $course->departments = $this->getCategoryNames($course->multi_cat);
        $thoiLuong = $course->chapters->flatMap(function($chapter){
            return $chapter->lessons ?? collect();
        })->sum('time');
        $plainText = preg_replace('#<li>(.*?)</li>#i', '- $1', $course->content);
        $plainText = strip_tags($plainText);
        $plainText = html_entity_decode($plainText);
        $plainText = trim(preg_replace('/\s+/', ' ', $plainText));
        // Sắp xếp chapter theo order_no
        $course->chapters = optional($course->chapters)->sortBy('order_no');

        $json1= [
             $course->chapters
                ->sortByDesc('order_no') // sắp xếp giảm dần
                ->values()
                ->map(function ($chapter) use ($user) {

                    return [
                    'id' => $chapter->id,
                                'name' => $chapter->name,
                                'order_no' => $chapter->order_no,
                                'lessons' => $chapter->lessons->map(function ($lesson) use ($user) {
                                    $completedLessons= UserLessonProgress::where('lesson_id', $lesson->id)->where('user_id', $user->id)->first();
                        return [
                            'id' => $lesson->id,
                            'name' => $lesson->name,
                            'duration' => $lesson->time,
                            'is_completed' =>$completedLessons? (bool)$completedLessons->completed:false,
                            'progress_time'=>$completedLessons? $completedLessons->progress_time:0,
                            'note' => $completedLessons? $completedLessons->note:'',
                            'src' => $lesson->iframe ? $this->extractIframeSrc($lesson->iframe) : $lesson->link_docs



                        ];
                    }),

                            ];
                        }),
        ];
        $json=[
            'status' => true,
            'msg'    => 'Lấy chi tiết khóa học thành công',
            'data'   => [
                'id'=>$course->id,
                'ten'=>$course->name,
                'chuc_vu'=>'giảng viên toán hoc',
                'tac_gia'=>$course->author_name,
                'thoi_luong'=>$thoiLuong,
                'thong_tin_bai_giang'=>$plainText,
                'ngay_phat_hanh'=>$course->created_at,
                 'chapter' => $json1]


        ];
    return response()->json($json);
    }
    function extractIframeSrc($iframeHtml) {
        if (preg_match('/src="([^"]+)"/', $iframeHtml, $matches)) {
            return $matches[1]; // trả về URL trong src
        }
        return null;
    }
    private function getCategoryNames($multiCat)
    {
        if (empty($multiCat)) return [];

        $ids = array_filter(explode('|', trim($multiCat, '|')));
        return Category::whereIn('id', $ids)
            ->where('status', 1)
            ->pluck('name', 'id')
            ->toArray();
    }

    private function getAllowedCategoryIds()
    {
        $query = Category::where('type', 1)->where('status', 1);

        return $query->pluck('id')->toArray();
    }


}