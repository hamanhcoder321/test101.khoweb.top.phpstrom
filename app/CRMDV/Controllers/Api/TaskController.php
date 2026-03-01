<?php

namespace App\CRMDV\Controllers\Api;

use App\CRMDV\Models\Bill;
use App\CRMDV\Models\Task;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\CURDBaseController;
use DB;
use Carbon\Carbon;
use Validator;
class TaskController extends CURDBaseController
{
    protected $module = [
        'code' => 'task',
        'table_name' => 'task',
        'label' => 'Công việc',
        'modal' => '\App\CRMDV\Models\Task',
        'list' => [
            ['name' => 'id', 'type' => 'text', 'label' => 'ID', 'sort' => true],
            ['name' => 'name', 'type' => 'text', 'label' => 'Tên công việc', 'sort' => true],
            ['name' => 'bill_id', 'type' => 'custom', 'td' => 'CRMDV.task.list.bill', 'label' => 'Hóa đơn', 'sort' => true],
            ['name' => 'admin_id', 'type' => 'custom', 'td' => 'CRMDV.task.list.admin', 'label' => 'Người thực hiện', 'sort' => true],
            ['name' => 'status', 'type' => 'select', 'label' => 'Trạng thái', 'sort' => true, 'options' => [
                'chua_bat_dau' => 'Chưa bắt đầu',
                'dang_lam' => 'Đang làm',
                'tam_dung' => 'Tạm dừng',
                'hoan_thanh' => 'Hoàn thành',
                'huy' => 'Hủy',
            ]],
            ['name' => 'priority', 'type' => 'select', 'label' => 'Ưu tiên', 'sort' => true, 'options' => [
                'cao' => 'Cao',
                'trung_binh' => 'Trung bình',
                'thap' => 'Thấp',
            ]],
            ['name' => 'progress', 'type' => 'text', 'label' => 'Tiến độ', 'sort' => true],
            ['name' => 'deadline', 'type' => 'date', 'label' => 'Hạn hoàn thành', 'sort' => true],
        ],
        'form' => [
            'general_tab' => [
                ['name' => 'name', 'type' => 'text', 'class' => 'required', 'label' => 'Tên công việc', 'group_class' => 'col-md-6'],
                ['name' => 'bill_id', 'type' => 'select2_model', 'model' => \App\CRMDV\Models\Bill::class, 'label' => 'Hóa đơn', 'group_class' => 'col-md-6', 'display_field' => 'name'],
                ['name' => 'admin_id', 'type' => 'select2_model', 'model' => Admin::class, 'label' => 'Người thực hiện', 'group_class' => 'col-md-6', 'display_field' => 'name'],
                ['name' => 'status', 'type' => 'select', 'label' => 'Trạng thái', 'group_class' => 'col-md-6', 'options' => [
                    'Chưa bắt đầu' => 'Chưa bắt đầu',
                    'Đang làm' => 'Đang làm',
                    'Tạm dừng' => 'Tạm dừng',
                    'Hoàn thành' => 'Hoàn thành',
                    'Hủy' => 'Hủy',
                ]],
                ['name' => 'priority', 'type' => 'select', 'label' => 'Ưu tiên', 'group_class' => 'col-md-6', 'options' => [
                    'Cao' => 'Cao',
                    'Trung bình' => 'Trung bình',
                    'Thấp' => 'Thấp'
                ]],
                ['name' => 'progress', 'type' => 'text', 'label' => 'Tiến độ (%)', 'group_class' => 'col-md-6'],
                ['name' => 'deadline', 'type' => 'date', 'label' => 'Hạn hoàn thành', 'group_class' => 'col-md-6'],
                ['name' => 'description', 'type' => 'textarea', 'label' => 'Mô tả', 'group_class' => 'col-md-12', 'inner' => 'rows=5'],
            ]
        ]
    ];

    protected $quick_search = [
        'label' => 'ID, tên công việc, tiến độ, trạng thái',
        'fields' => 'id, name, progress, status'
    ];

    protected $filter = [
        'name' => [
            'label' => 'Tên công việc',
            'type' => 'text',
            'query_type' => 'like',
            'field' => 'name'
        ],
        'status' => [
            'label' => 'Trạng thái',
            'type' => 'select',
            'options' => [
                '' => 'Tất cả',
                'Chưa bắt đầu' => 'Chưa bắt đầu',
                'Đang làm' => 'Đang làm',
                'Tạm dừng' => 'Tạm dừng',
                'Hoàn thành' => 'Hoàn thành',
                'Hủy' => 'Hủy',
            ],
            'query_type' => '='
        ],
        'priority' => [
            'label' => 'Mức độ ưu tiên',
            'type' => 'select',
            'options' => [
                '' => 'Tất cả',
                'Cao' => 'Cao',
                'Trung bình' => 'Trung bình',
                'Thấp' => 'Thấp'
            ],
            'query_type' => '='
        ],
        'admin_id' => [
            'label' => 'Người thực hiện',
            'type' => 'select2_ajax_model',
            'model' => Admin::class,
            'display_field' => 'name',
            'object' => 'admin',
            'query_type' => '='
        ]
    ];


    public function getDataList(Request $request)
    {
        $listItem = $this->model->query();
        $listItem = $this->quickSearch($listItem, $request);
        $listItem = $this->appendWhere($listItem, $request);

        $data['listItem'] = $listItem->paginate($request->limit ?? $this->limit_default);
        $data['param_url'] = $request->all();
        $data['module'] = $this->module;
        $data['quick_search'] = $this->quick_search;
        $data['filter'] = $this->filter;

        // Trả về mảng, không json
        return $data;
    }




    public function getAll(Request $request)
    {
        $query = Task::with(['bill', 'admin']);
        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('bill_name')) {
            $query->whereHas('bill', function ($q) use ($request) {
                $q->where('domain', 'like', '%' . $request->bill_name . '%');
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }


        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }


        if ($request->filled('deadline_from')) {
            $query->whereDate('deadline', '>=', $request->deadline_from);
        }

        if ($request->filled('deadline_to')) {
            $query->whereDate('deadline', '<=', $request->deadline_to);
        }


        $perPage = $request->get('per_page', 10);
        $paginated = $query->orderByDesc('id')->paginate($perPage);
        $data_mobile = $paginated->getCollection()->map(function ($item) {
            return [
                'id'          => $item->id,
                'name'        => $item->name ?? '',
                'bill_name'   => optional($item->bill)->domain ?? '',
                'admin_name'  => optional($item->admin)->name ?? '',
                'status'      => $item->status ?? '',
                'priority'    => $item->priority ?? '',
                'progress'    => $item->progress ?? 0,
                'deadline'    => $item->deadline
                    ? Carbon::parse($item->deadline)->format('Y-m-d')
                    : '',
                'description' => $item->description ?? '',
            ];
        })->values();

        return response()->json([
            'status'   => true,
            'msg'      => 'Lấy danh sách công việc thành công',
            'data'     => $data_mobile,
            'paginate' => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }


    // Filter các trường
    public function appendWhere($query, $request)
    {
        if ($request->filled('name')) $query->where('name', 'like', "%{$request->name}%");
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('priority')) $query->where('priority', $request->priority);
        if ($request->filled('admin_id')) $query->where('admin_id', $request->admin_id);

        return $query;
    }

    // Thêm mới công việc
    public function createTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'bill_name'      => 'required|string|max:255',
            'admin_name'  => 'required|string|max:255',
            'status'      => 'nullable|in:Chưa bắt đầu,Đang làm,Hoàn thành,Tạm dừng,Hủy',
            'priority'    => 'nullable|in:Cao,Trung bình,Thấp',
            'progress'    => 'nullable|integer|min:0|max:100',
            'deadline'    => 'nullable|date',
            'description' => 'nullable|string',
            'created_by'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bill = Bill::where('domain', $request->bill_name)->first();
        if (!$bill) {
            return response()->json([
                'status' => false,
                'msg' => 'Không tìm thấy bill theo domain'
            ], 404);
        }
        $admin = Admin::where('name', $request->admin_name)->first();
        if (!$admin) {
            return response()->json([
                'status' => false,
                'msg' => 'Người phụ trách không tồn tại'
            ], 404);
        }
        $creatorId = null;
        if (!empty($request->created_by)) {
            $creator = Admin::where('name', $request->created_by)->first();
            if ($creator) {
                $creatorId = $creator->id;
            }
        }

        $task = Task::create([
            'bill_id'     => $bill->id,
            'name'        => $request->name,
            'description' => $request->description,
            'admin_id'    => $admin->id,
            'status'      => $request->status ?? 'Chưa bắt đầu',
            'priority'    => $request->priority ?? 'Trung bình',
            'progress'    => $request->progress ?? 0,
            'deadline'    => $request->deadline,
            'created_by'  => $creatorId,
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Tạo công việc thành công',
            'data' => $task
        ], 201);
    }


    // Chi tiết công việc
    public function getTask($id)
    {
        $task = Task::with(['bill', 'admin', 'createdBy'])
            ->where('id', $id)
            ->first();

        if (!$task) {
            return response()->json([
                'status' => false,
                'msg' => 'Công việc không tồn tại'
            ]);
        }
        $billname = Bill::where('id', $task->bill_id)->value('domain');
        return response()->json([
            'status' => true,
            'data' => [
                'id'          => $task->id,
                'name'        => $task->name ?? '',
                'description' => $task->description ?? '',
                'status'      => $task->status ?? '',
                'priority'    => $task->priority ?? '',
                'progress'    => $task->progress ?? 0,
                'create_at'   => optional($task->created_at)->format('Y-m-d'),
                'deadline'    => $task->deadline??"",
                'created_by'  => optional($task->createdBy)->name ?? '',
                'admin_name'  => optional($task->admin)->name ?? '',
                'bill_id'   =>  $task->bill_id ?? 0,
                'bill_name'   => $billname??"",
            ]
        ]);
    }


    // Cập nhật công việc
    public function updateTask(Request $request, $id)
    {
        $task = Task::with(['bill', 'admin'])->find($id);
        if (!$task) {
            return response()->json([
                'status' => false,
                'msg' => 'Công việc không tồn tại'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|required|string|max:255',
            'bill_name'   => 'sometimes|required|string|max:255', // domain
            'admin_name'  => 'sometimes|required|string|max:255',
            'status'      => 'nullable|in:Chưa bắt đầu,Đang làm,Hoàn thành,Tạm dừng,Hủy',
            'priority'    => 'nullable|in:Cao,Trung bình,Thấp',
            'progress'    => 'nullable|integer|min:0|max:100',
            'deadline'    => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        if ($request->filled('bill_name')) {
            $bill = Bill::where('domain', $request->bill_name)->first();

            if (!$bill) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Không tìm thấy bill theo domain'
                ], 404);
            }

            $task->bill_id = $bill->id;
        }
        if ($request->filled('admin_name')) {
            $admin = Admin::where('name', trim($request->admin_name))->first();

            if (!$admin) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Người phụ trách không tồn tại'
                ], 404);
            }

            $task->admin_id = $admin->id;
        }

        $task->fill(
            $request->except(['bill_name', 'admin_name'])
        );

        $task->save();

        return response()->json([
            'status' => true,
            'msg' => 'Cập nhật công việc thành công',
            'data' => [
                'id'          => $task->id,
                'name'        => $task->name,
                'description' => $task->description ?? '',
                'status'      => $task->status,
                'priority'    => $task->priority,
                'progress'    => $task->progress,
                'deadline'    => optional($task->deadline)->format('Y-m-d'),
                'bill_id'     => $task->bill_id,
                'bill_name'   => optional($task->bill)->domain ?? '',
                'admin_id'    => $task->admin_id,
                'admin_name'  => optional($task->admin)->name ?? '',
            ]
        ], 200);
    }



    // Xóa công việc
    public function deleteTask($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['status' => false, 'msg' => 'Công việc không tồn tại']);
        }

        $task->delete();
        return response()->json(['status' => true, 'msg' => 'Xóa công việc thành công']);
    }

    // Cập nhật trạng thái riêng
    public function updateStatus(Request $request, $id)
    {
        $task = Task::find($id);
        if (!$task) return response()->json(['status' => false, 'msg' => 'Công việc không tồn tại']);

        $request->validate([
            'status' => 'required|in:chua_bat_dau,dang_lam,tam_dung,hoan_thanh,huy'
        ]);

        $task->status = $request->status;
        $task->save();

        return response()->json(['status' => true, 'msg' => 'Cập nhật trạng thái thành công', 'data' => $task]);
    }

    // Cập nhật tiến độ riêng
    public function updateProgress(Request $request, $id)
    {
        $task = Task::find($id);
        if (!$task) return response()->json(['status' => false, 'msg' => 'Công việc không tồn tại']);

        $request->validate([
            'progress' => 'required|integer|min:0|max:100'
        ]);

        $task->progress = $request->progress;
        $task->save();

        return response()->json(['status' => true, 'msg' => 'Cập nhật tiến độ thành công', 'data' => $task]);
    }
    public function searchTasks(Request $request)
    {
        $query = Task::with(['bill', 'admin']);


        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . trim($request->keyword) . '%');
        }


        if ($request->filled('bill_name')) {
            $query->whereHas('bill', function ($q) use ($request) {
                $q->where('domain', 'like', '%' . trim($request->bill_name) . '%');
            });
        }


        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('deadline_from')) {
            $query->whereDate('deadline', '>=', $request->deadline_from);
        }

        if ($request->filled('deadline_to')) {
            $query->whereDate('deadline', '<=', $request->deadline_to);
        }

        $tasks = $query
            ->orderBy('deadline', 'asc')
            ->paginate(10);

        /**
         * 📤 7. Response
         */
        return response()->json([
            'status' => true,
            'data' => $tasks->map(function ($task) {
                return [
                    'id'            => $task->id,
                    'name'          => $task->name,
                    'bill_id'       => $task->bill_id,
                    'bill_name'     => optional($task->bill)->domain ?? '',
                    'admin_name'    => optional($task->admin)->name ?? '',
                    'status'        => $task->status,
                    'priority'      => $task->priority,
                    'progress'      => $task->progress,
                    'deadline'      => optional($task->deadline)->format('Y-m-d'),
                    'started_at'    => optional($task->started_at)->format('Y-m-d'),
                    'completed_at'  => optional($task->completed_at)->format('Y-m-d'),
                ];
            }),
//            'meta' => [
//                'current_page' => $tasks->currentPage(),
//                'total'        => $tasks->total(),
//            ]
        ]);
    }


}
