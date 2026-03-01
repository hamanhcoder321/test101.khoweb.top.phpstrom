<?php

namespace App\CRMDV\Controllers\Api;
use App\CRMDV\Models\Tag;
use App\Http\Controllers\Admin\CURDBaseController;
use App\CRMDV\Models\Room;
use App\Models\Admin;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
class RoomController extends CURDBaseController
{
    protected $module = [
        'code' => 'room',
        'table_name' => 'rooms',
        'label' => 'Phòng làm việc',
        'modal' => '\App\CRMDV\Models\Room',
        'list' => [
            ['name' => 'name',            'type' => 'text',      'label' => 'Tên phòng',          'sort' => true],
            ['name' => 'code',            'type' => 'text',      'label' => 'Mã phòng',           'sort' => true],
            ['name' => 'parent_name',     'type' => 'custom',    'td' => 'CRMDV.room.list.parent', 'label' => 'Phòng ban trực thuộc'],
            ['name' => 'manager_name',  'type' => 'custom',    'td' => 'CRMDV.room.list.manager', 'label' => 'Trưởng phòng'],
            ['name' => 'employee_count',  'type' => 'number',    'label' => 'Số nhân viên',       'sort' => true],
            ['name' => 'address',         'type' => 'text',      'label' => 'Địa chỉ'],
            ['name' => 'established_date','type' => 'date_vi',   'label' => 'Ngày thành lập',     'sort' => true],
            ['name' => 'status',          'type' => 'status',    'label' => 'Trạng thái'],
        ],
        'form' => [
            'general_tab' => [
                ['name' => 'parent_id',
                    'type' => 'select2_model',
                    'label' => 'Phòng ban trực thuộc',
                    'model' => \App\CRMDV\Models\Room::class,
                    'display_field' => 'name',
                    'object' => 'room',
                    'allow_null' => true,
                    'placeholder' => '— Không có —',
                    'group_class' => 'col-md-6',
                    'where' => 'id != :current' // tránh chọn chính nó
                ],
                ['name' => 'name',
                    'type' => 'text',
                    'class' => 'required',
                    'label' => 'Tên phòng ban',
                    'group_class' => 'col-md-6'
                ],
                ['name' => 'code',
                    'type' => 'text',
                    'label' => 'Mã phòng ban',
                    'group_class' => 'col-md-6'
                ],
                ['name' => 'manager_id',
                    'type' => 'select2_ajax_model',
                    'label' => 'Trưởng phòng',
                    'model' => \App\Models\Admin::class,
                    'display_field' => 'name',
                    'display_field2' => 'email',
                    'object' => 'admin',
                    'where' => 'status = 1',
                    'allow_null' => true,
                    'group_class' => 'col-md-6'
                ],
                ['name' => 'employee_count',
                    'type' => 'number',
                    'label' => 'Số lượng nhân viên',
                    'value' => 0,
                    'group_class' => 'col-md-4'
                ],
                ['name' => 'address',
                    'type' => 'text',
                    'label' => 'Địa chỉ',
                    'group_class' => 'col-md-8'
                ],
                ['name' => 'established_date',
                    'type' => 'date',
                    'label' => 'Ngày thành lập',
                    'group_class' => 'col-md-4'
                ],
                ['name' => 'description',
                    'type' => 'textarea',
                    'label' => 'Mô tả',
                    'inner' => 'rows=6',
                    'group_class' => 'col-md-12'
                ],
                ['name' => 'status',
                    'type' => 'checkbox',
                    'label' => 'Hoạt động',
                    'value' => 1,
                    'unchecked' => 0,
                    'group_class' => 'col-md-12'
                ],
            ],
        ]
    ];

    protected $quick_search = [
        'label' => 'Tìm tên, mã phòng, trưởng phòng',
        'fields' => 'name,code,address'
    ];

    protected $filter = [
        'name' => [
            'label' => 'Tên phòng',
            'type' => 'text',
            'query_type' => 'like'
        ],
        'code' => [
            'label' => 'Mã phòng',
            'type' => 'text',
            'query_type' => 'like'
        ],
        'parent_id' => [
            'label' => 'Phòng ban trực thuộc',
            'type' => 'select2_model',
            'model' => \App\CRMDV\Models\Room::class,
            'display_field' => 'name',
            'object' => 'room',
            'allow_null' => true,
        ],
        'manager_id' => [
            'label' => 'Trưởng phòng',
            'type' => 'select2_ajax_model',
            'model' => \App\Models\Admin::class,
            'display_field' => 'name',
            'object' => 'admin',
        ],
        'status' => [
            'label' => 'Trạng thái',
            'type' => 'select',
            'options' => [
                '' => 'Tất cả',
                '1' => 'Hoạt động',
                '0' => 'Ngừng hoạt động'
            ],
        ],
    ];
    public function sort($request, $model)
    {
        // Kiểm tra nếu có tham số 'sorts' (sorts[] trong request)
        if ($request->sorts != null) {
            foreach ($request->sorts as $sort) {
                if ($sort != null) {
                    $sort_data = explode('|', $sort);
                    // $sort_data[0] là field, $sort_data[1] là direction (asc/desc)
                    $model = $model->orderBy($sort_data[0], $sort_data[1]);
                }
            }
        }

        // Nếu không có sắp xếp tùy chỉnh, sử dụng sắp xếp mặc định (nếu có)
        // Cần định nghĩa $this->orderByRaw trong CURDBaseController hoặc RoomController
        // if (isset($this->orderByRaw)) {
        //     $model = $model->orderByRaw($this->orderByRaw);
        // } else {
        //     // Mặc định sắp xếp theo id giảm dần
        //     $model = $model->orderBy('id', 'desc');
        // }

        return $model;
    }
    public function quickSearch($listItem, $r) {
        if (@$r->quick_search != '') {
            $listItem = $listItem->where(function ($query) use ($r) {
                // Duyệt qua các trường đã định nghĩa trong $this->quick_search['fields']
                foreach (explode(',', $this->quick_search['fields']) as $field) {
                    $query->orWhere(trim($field), 'LIKE', '%' . $r->quick_search . '%');
                }

                // (Tùy chọn) Thêm logic tìm kiếm SĐT như LeadController nếu cần cho Room (ít khả năng)
                // Ví dụ: tìm kiếm nhân viên, mã phòng, tên phòng
            });
        }
        return $listItem;
    }
    public function appendWhere($query, $request)
    {
        // Ví dụ: Lọc chỉ những phòng ban không phải là chính nó khi chọn parent_id (như trong form)
        // if ($request->filled('id')) { // Giả sử dùng để chỉnh sửa
        //     $query->where('id', '!=', $request->id);
        // }

        // Ví dụ: Lọc theo trưởng phòng (manager_id) nếu có nhu cầu tùy chỉnh
        if ($request->filled('manager_name')) {
            $ids = Admin::where('name', 'like', "{$request->manager_name}")->pluck('id')->toArray();
            $query->whereIn('manager_id', $ids);
        }

        // Tùy chỉnh các logic lọc khác nếu cần, ví dụ:
        // if ($request->filled('employee_count_min')) {
        //     $query->where('employee_count', '>=', $request->employee_count_min);
        // }

        return $query;
    }
    public function index(Request $request)
    {
        $data =$this->getDataList($request);
        return response()->json([
            'status' => true,
            'msg' => 'Lấy danh sách phòng ban thành công',
            'data' => $data
        ]);
    }


    public function list(Request $request)
    {
        // Dùng lại hoàn toàn logic lọc + tìm kiếm + sort + phân trang của hệ thống
        $dataList = $this->getDataList($request);
        $paginated = $dataList['listItem']; // Đây là LengthAwarePaginator
        // Map dữ liệu nhẹ gọn cho mobile
        $rooms = $paginated->getCollection()->map(function ($room) {
            return [
                'id'                 => $room->id,
                'name'               => $room->name ?? '',
                'manager_name'       => optional($room->manager)->name ?? '',
                'status'        => (bool)$room->status,
            ];
        })->values();

        return response()->json([
            'status'  => true,
            'msg'     => 'Lấy danh sách phòng ban thành công',
            'data'    => $rooms,
            'paginate' => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ]
        ]);
    }

    public function show($id)
    {
        $room = Room::with(['parent', 'manager'])->findOrFail($id);

        $room['manager_name'] = $room->manager ? $room->manager->name:"";
        $room['parent_name'] = $room->parent ? $room->parent->name : "";
        unset($room['parent']);
        unset($room['manager']);
        unset($room['parent_id']);
        unset($room['manager_id']);

        $list = Admin::where('room_id', $room->id)
            ->select('id', 'name')
            ->get();

        $list->map(function ($admin) {
            $admin->chuc_vu = $admin->roles()->pluck('display_name')->first() ?? '';
            return $admin;
        });

        return response()->json([
            'status' => true,
            'data' => $room,
            'list'=>$list
        ]);
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'code'             => 'nullable|string|max:50|unique:rooms,code',
            'manager_name'     => 'nullable|string',
            'parent_name'      => 'nullable|string',

            'established_date' => 'nullable|date',
            'address'          => 'nullable|string',
            'description'      => 'nullable|string',
            'status'           => 'nullable|in:true,false',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        }
        $managerId = null;
        if ($request->filled('manager_name')) {
            $manager = \App\Models\Admin::where('name', $request->manager_name)->first();
            if ($manager) {
                $managerId = $manager->id;
            }
        }

        // Tìm parent_id từ tên phòng ban cha (nếu có)
        $parentId = null;
        if ($request->filled('parent_name')) {
            $parent = \App\CRMDV\Models\Room::where('name', 'like', $request->parent_name )
                ->first();
            $parentId = $parent ? $parent->id : null;
// an toàn 100%
        }

        // Tạo phòng ban với dữ liệu đã xử lý an toàn
        $room = Room::create([
            'parent_id'        => $parentId,
            'name'             => $request->name,
            'code'             => $request->code,
            'manager_id'       => $managerId,
//            'employee_count'   => $request->employee_count ?? 0,
            'address'          => $request->address,
            'established_date' => $request->established_date,
            'description'      => $request->description,
            'status'           => $request->has('status') ? (bool)$request->status : 1,
        ]);

        return response()->json([
            'status' => true,
            'msg'    => 'Thêm phòng ban thành công!',
//            'data'   => $room->load(['parent', 'manager']) // trả về kèm thông tin cha & trưởng phòng
        ]);
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:rooms,code,' . $id,
            'parent_id' => 'nullable|exists:rooms,id|not_in:' . $id, // không cho chọn chính nó làm cha
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 422);
        }

        $room->update($request->all());

        return response()->json([
            'status' => true,
            'msg' => 'Cập nhật phòng ban thành công!',
//            'data' => $room->fresh(['parent', 'manager'])
        ]);
    }
    public function destroy($id)
    {
        $room = Room::findOrFail($id);

        // Kiểm tra nếu có phòng con thì không cho xóa
        if ($room->children()->exists()) {
            return response()->json(['status' => false, 'msg' => 'Không thể xóa vì phòng ban này đang có phòng con!'], 400);
        }

        $room->delete();

        return response()->json(['status' => true, 'msg' => 'Xóa thành công!']);
    }
    public function show1()
    {
        $tagIds = Tag::where('type', 'phong_ban')->pluck('id');
        $list = Admin::whereIn('room_id', $tagIds)->get();
        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }
    public function detailsRoom1($id)
    {
        $roomDetails = Tag::where('id', $id)->first();
        // hoặc: Tag::find($id);

        if (!$roomDetails) {
            return response()->json([
                'status' => false,
                'msg' => 'Không tìm thấy dữ liệu'
            ], 404);
        }
        $listAdmin = Admin::where('room_id',$roomDetails->id)->get();
        $dataListAdmin = $listAdmin->map(function ($item) {
            return[
                'name'  => $item->name,
                'email' => $item->email,
            ];

        });
        return response()->json([
            'status' => true,
            'msg' => "Lấy thành công chi tiết phòng ban",
            'data' => [
                'name' =>$roomDetails->slug??'',
                'slg' => (int)$dataListAdmin->count(),
                'nhan_vien' =>  $dataListAdmin
            ]
        ]);
    }

    public function addAdmin(Request  $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'room_id'  => 'nullable|String',
        ]);
        $nameRoom = Tag::where('slug',$request->room_id)->first();
        $admin = Admin::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'room_id'  => $nameRoom ? $nameRoom->id : "",
        ]);
        return response()->json([
            'status' => true,
            'msg'=>"Thêm thành công",
            'data'=>$admin
        ]);
    }
}