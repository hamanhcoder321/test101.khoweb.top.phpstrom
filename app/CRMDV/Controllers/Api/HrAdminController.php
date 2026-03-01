<?php

namespace App\CRMDV\Controllers\Api;
use App\CRMDV\Models\Tag;
use App\Http\Requests\UpdateAdminRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Model\Roles;
use App\Http\Controllers\Admin\CURDBaseController;
use App\CRMDV\Models\Bill;
use App\CRMDV\Models\Room;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Helpers\CommonHelper;
use App\CRMDV\Models\Admin;
use App\Models\RoleAdmin;
use App\CRMDV\Models\Setting;
use Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\DB;
use App\Http\Requests\APIStoreAdminRequest;
use Mail;
use Session;
use Validator;
class HrAdminController extends CURDBaseController
{
    protected $_role;

    protected $orderByRaw = 'status DESC, id DESC';

    public function __construct()
    {
        parent::__construct();
        $this->_role = new RoleController();
    }
    protected $module = [
        'code' => 'hradmin',
        'label' => 'Thành viên',
        'modal' => 'App\CRMDV\Models\Admin',
        'table_name' => 'admin',
        'list' => [
            ['name' => 'image', 'type' => 'image', 'label' => 'admin.image'],
            ['name' => 'name', 'type' => 'text_admin_edit', 'label' => 'admin.name', 'sort' => true],
            ['name' => 'room_id', 'type' => 'select', 'options' => [
                '' => '',
                1 => 'Phòng kinh doanh 1',
                2 => 'Phòng kinh doanh 2',
                3 => 'Phòng kinh doanh 3',
                4 => 'Phòng kinh doanh 4',
                5 => 'Phòng kinh doanh 5',
                6 => 'Phòng Telesale',
                10 => 'Kỹ thuật',
                15 => 'Điều hành',
                20 => 'Marketing',
                25 => 'Tuyển dụng',
                30 => 'CSKH',
            ], 'label' => 'Phòng', 'sort' => true],
            ['name' => 'role_id', 'type' => 'role_name', 'label' => 'admin.permission'],
            ['name' => 'tel', 'type' => 'text', 'label' => 'admin.phone', 'sort' => true],
            ['name' => 'code', 'type' => 'text', 'label' => 'Mã NV', 'sort' => true],
            ['name' => 'email', 'type' => 'text', 'label' => 'admin.email', 'sort' => true],
            ['name' => 'may_cham_cong_id', 'type' => 'text', 'label' => 'ID chấm công', 'sort' => true],
            ['name' => 'work_time', 'type' => 'select', 'options' => [
                '' => '',
                1 => 'Fulltime',
                2 => 'Parttime',
                3 => 'Online',
            ], 'label' => 'Thời gian', 'sort' => true],
            ['name' => 'status', 'type' => 'status', 'label' => 'admin.status'],
            ['name' => 'created_at', 'type' => 'date_vi', 'label' => 'Ngày tạo', 'sort' => true],
            ['name' => 'invite_by', 'type' => 'relation', 'label' => 'Người tuyển', 'object' => 'invite', 'display_field' => 'name', 'sort' => true],
        ],
        'form' => [
            'general_tab1' => [
                ['name' => 'name', 'type' => 'text', 'class' => 'required', 'label' => 'admin.full_name', 'group_class' => 'col-md-6'],
                ['name' => 'short_name', 'type' => 'text', 'class' => '', 'label' => 'Tên ngắn gọn', 'group_class' => 'col-md-6'],
                ['name' => 'email', 'type' => 'custom', 'field' => 'CRMDV.admin.form.email', 'class' => 'required', 'label' => 'admin.email', 'group_class' => 'col-md-3'],
                ['name' => 'tel', 'type' => 'custom', 'class' => 'required', 'field' => 'CRMDV.admin.form.tel', 'label' => 'admin.phone', 'group_class' => 'col-md-2'],
                ['name' => 'code', 'type' => 'custom', 'class' => 'required', 'field' => 'CRMDV.admin.form.code', 'label' => 'Mã nhân viên', 'group_class' => 'col-md-2'],
                ['name' => 'may_cham_cong_id', 'type' => 'number', 'class' => '', 'label' => 'ID máy chấm công', 'group_class' => 'col-md-2'],
                ['name' => 'status', 'type' => 'checkbox', 'label' => 'admin.active', 'value' => 1, 'group_class' => 'col-md-3'],
                ['name' => 'password', 'type' => 'password', 'class' => 'required', 'label' => 'admin.password', 'group_class' => 'col-md-6'],
                ['name' => 'password_confimation', 'type' => 'password', 'class' => 'required', 'label' => 'admin.re_password', 'group_class' => 'col-md-6'],
                /*['name' => 'role_id', 'type' => 'custom', 'field' => 'CRMDV.hradmin.partials.select_role', 'label' => 'Quyền', 'class' => 'required', 'model' => \App\Models\Roles::class, 'display_field' => 'display_name', 'group_class' => 'col-md-6'],*/
                ['name' => 'address', 'type' => 'text', 'class' => '', 'label' => 'admin.address', 'group_class' => 'col-md-3'],
                ['name' => 'province_id', 'type' => 'select_location', 'label' => 'admin.choose_place', 'group_class' => 'col-md-9'],
                ['name' => 'date_start_work', 'type' => 'date', 'class' => '', 'label' => 'Ngày bắt đầu tính lương', 'group_class' => 'col-md-4'],
                ['name' => 'birthday', 'type' => 'date', 'class' => '', 'label' => 'Ngày sinh', 'group_class' => 'col-md-4'],
                ['name' => 'intro', 'type' => 'textarea', 'class' => '', 'label' => 'admin.introduce'],
                ['name' => 'note', 'type' => 'textarea', 'class' => '', 'label' => 'admin.note', 'inner' => 'rows=10'],
            ],
            'general_tab' => [
                ['name' => 'cccd', 'type' => 'text', 'label' => 'admin.cccd', 'group_class' => 'col-md-6'],
                ['name' => 'gioitinh', 'type' => 'select', 'options' => [
                    'Nam' => 'Nam',
                    'Nữ' => 'Nữ',
                ], 'label' => 'admin.gioitinh', 'group_class' => 'col-md-6', 'value' => 'Nam'],
                ['name' => 'ID_card_photo_on_the_front', 'type' => 'file_image', 'label' => 'admin.ID_card_photo_on_the_front','group_class' => 'col-md-6'],
                ['name' => 'ID_card_photo_on_the_back', 'type' => 'file_image', 'label' => 'admin.ID_card_photo_on_the_back','group_class' => 'col-md-6'],
            ],
            'more_info_tab' => [
                ['name' => 'image', 'type' => 'file_image', 'label' => 'Ảnh đại diện'],
                ['name' => 'tknh_image', 'type' => 'file_image', 'label' => 'Ảnh tài khoản ngân hàng'],
                ['name' => 'facebook', 'type' => 'text', 'class' => '', 'label' => 'facebook'],
                ['name' => 'skype', 'type' => 'text', 'class' => '', 'label' => 'skype'],
                ['name' => 'zalo', 'type' => 'text', 'class' => '', 'label' => 'zalo'],
                ['name' => 'invite_by', 'type' => 'select2_ajax_model', 'label' => 'Người tuyển', 'model' => Admin::class, 'object' => 'admin', 'display_field' => 'name', 'display_field2' => 'tel', 'class' => 'required'],
                ['name' => 'room_id', 'type' => 'select', 'options' => [
                    '' => '',
                    1 => 'Phòng kinh doanh 1',
                    2 => 'Phòng kinh doanh 2',
                    3 => 'Phòng kinh doanh 3',
                    4 => 'Phòng kinh doanh 4',
                    5 => 'Phòng kinh doanh 5',
                    6 => 'Phòng Telesale',
                    10 => 'Kỹ thuật',
                    15 => 'Điều hành',
                    20 => 'Marketing',
                    25 => 'Tuyển dụng',
                    30 => 'CSKH',
                ], 'label' => 'Phòng', 'group_class' => 'col-md-12'],
                ['name' => 'work_time', 'type' => 'select', 'options' => [
                    '' => '',
                    1 => 'Fulltime',
                    2 => 'Parttime',
                    3 => 'Online',
                ], 'label' => 'Thời gian làm', 'group_class' => 'col-md-12'],
                // ['name' => 'role_id', 'type' => 'select', 'options' => [
                //     '' => '',
                //     2 => 'Kinh doanh',
                //     176 => 'CTV kinh doanh',
                //     182 => 'Trưởng phòng kinh doanh',
                //     183 => 'Telesale'
                //     173 => 'Kỹ thuật',
                //     174 => 'Marketing',
                //     178 => 'Điều hành',
                //     179 => 'CSKH',
                //     180 => 'HR Tuyển dụng',
                // ], 'label' => 'Phân quyền'],
            ],
        ]
    ];
    protected $filter = [
        'status' => [
            'label' => 'admin.status',
            'type' => 'select',
            'query_type' => '=',
            'options' => [
                '' => '',
                1 => 'True',
                0 => 'False'
            ]
        ],
        'room_id' => [
            'label' => 'Phòng',
            'type' => 'select',
            'query_type' => '=',
            'options' => [
                '' => '',
                1 => 'Phòng kinh doanh 1',
                2 => 'Phòng kinh doanh 2',
                3 => 'Phòng kinh doanh 3',
                4 => 'Phòng kinh doanh 4',
                5 => 'Phòng kinh doanh 5',
                10 => 'Kỹ thuật',
                15 => 'Điều hành',
                20 => 'Marketing',
                25 => 'Tuyển dụng',
                30 => 'CSKH',
            ]
        ],
        'role_id' => [
            'label' => 'Quyền',
            'type' => 'select2_model',
            'display_field' => 'display_name',
            'model' => \App\Models\Roles::class,
            'object' => 'role',
            'query_type' => 'custom'
        ],

        'work_time' => [
            'query_type' => 'custom',
            'options' => [
                'fulltime' => 'Fulltime',
                'parttime' => 'Parttime',
                'online'   => 'Online',
            ] ],
        'invite_by' => [
            'label' => 'Người tuyển',
            'type' => 'select2_ajax_model',
            'display_field' => 'name',
            'display_field2' => 'code',
            'model' => \App\Models\Admin::class,
            'object' => 'invite',
            'query_type' => '='
        ],
    ];
    protected $quick_search = [
        'label' => 'ID, tên, sđt, email',
        'fields' => 'id, name, tel, code, email'
    ];
    public function quickSearch($listItem, $r) {
        if (@$r->quick_search != '') {
            $listItem = $listItem->where(function ($query) use ($r) {
                foreach (explode(',', $this->quick_search['fields']) as $field) {
                    $query->orWhere(trim($field), 'LIKE', '%' . $r->quick_search . '%');
                }
                //  Tìm theo sđt
                $search_tel = str_replace('.', '', $r->quick_search);
                $search_tel = str_replace(',', '', $search_tel);
                $search_tel = trim($search_tel);
                $query->orWhere('tel', 'LIKE', '%' . $search_tel . '%');
            });
        }
        return $listItem;
    }

    public function appendWhere($query, $request)
    {
        if ($request->filled('work_time')) {

            $map = [
                'fulltime' => 1,
                'parttime' => 2,
                'online'   => 3,
            ];
            $value = strtolower(trim($request->work_time));

            if (array_key_exists($value, $map)) {
                $query->where('work_time', $map[$value]);
            }
        }



        if (@$request->filled('tel')) {
            $query->where('tel', 'like',  $request->tel);
        }
        if( @$request->filled('name')) {
            $query->where('name', 'like',  $request->name);
        }
        if( @$request->filled('room_name')) {
            $room_id = DB::table('rooms')->where('name', 'like',  $request->room_name)->value('id');
            $query->where('room_id', 'like', $room_id);
        }
        if(@$request->filled('status')) {
            $query->where('status', 'like', $request->status);
        }
        if(@$request->filled('code')) {
            $query->where('code', 'like',  $request->code);
        }


        if(@$request->filled('role_name')) {
            $roleId = \App\Models\Roles::where('display_name', 'like',  $request->role_name)->value('id');
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('role_id', $roleId);
            });
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

    public function getAll(Request $request)
    {
        // Lấy dữ liệu phân trang
        $dataList = $this->getDataList($request);
        $paginated = $dataList['listItem']; // đây là LengthAwarePaginator

        // Lấy collection gốc rồi transform
        $transformed = $paginated->getCollection()->transform(function ($admin) {
            return [
                'id' => $admin->id,
                'name' => $admin->name ?? '',
                'tel' => $admin->tel ?? '',
                'role_names' => optional($admin->roles->first())->name ?? '',
                'is_active' => (bool) $admin->status,
                'room' => optional($admin->room)->name ?? '',
                'work' => optional($admin->roles->first())->display_name ?? '',
                'image' => $admin->image
                    ? url('/filemanager/userfiles/' . str_replace(' ', '%20', $admin->image))
                    : '',
                'code' => $admin->code ?? '',
            ];
        });

        // Gắn collection đã transform vào paginator
        $paginated->setCollection($transformed);

        return response()->json([
            'status' => true,
            'msg' => 'Danh sách ',
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => (int)$paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
            ]
        ]);
    }
    public function getAllUser(Request $request)
    {
        // Lấy dữ liệu phân trang
        $dataList = $this->getDataList($request);
        $paginated = $dataList['listItem']; // đây là LengthAwarePaginator

        // Lấy collection gốc rồi transform
        $transformed = $paginated->getCollection()->transform(function ($admin) {
            return [
                'id' => $admin->id,
                'name' => $admin->name ?? '',
                'tel' => $admin->tel ?? '',
                'email' => $admin->email ?? '',
//                'role_names' => optional($admin->roles->first())->name ?? '',
                'is_active' => (bool) $admin->status,
//                'room' => optional($admin->room)->name ?? '',
//                'work' => optional($admin->roles->first())->display_name ?? '',
                'image' => $admin->image
                    ? url('/filemanager/userfiles/' . str_replace(' ', '%20', $admin->image))
                    : '',
                'code' => $admin->code ?? '',
            ];
        });

        // Gắn collection đã transform vào paginator
        $paginated->setCollection($transformed);

        return response()->json([
            'status' => true,
            'msg' => 'Danh sách ',
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => (int)$paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
            ]
        ]);
    }
    public function getAll1(Request $request)
    {
        $list = Admin::with(['roles', 'room'])->get();

        $data = $list->map(function ($admin) {
            return [
                'id'          => $admin->id,
                'name'        => $admin->name ?? '',
                'tel'         => $admin->tel ?? '',
                'role_names'  => optional($admin->roles->first())->name ?? '',
                'is_active'   => (bool) $admin->status,
                'room'        => optional($admin->room)->name ?? '',
                'work'        => optional($admin->roles->first())->display_name ?? '',
                'image'       => $admin->image
                    ? url('/filemanager/userfiles/' . str_replace(' ', '%20', $admin->image))
                    : '',
                'code'        => $admin->code ?? '',
            ];
        });

        return response()->json([
            'status' => true,
            'msg'    => 'Danh sách nhân viên',
            'data'   => $data
        ]);
    }


    public function thongKe($data, $listItem, $request) {
        return $data;
    }

    public function store(Request $request)
    {
      #validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6',
            'work_time' => 'required|in:fulltime,parttime,online',
            'role_name' => 'required|string',
            'room_name' => 'nullable|string',
            'ID_card_photo_on_the_front' => 'nullable|string|max:2048',
            'ID_card_photo_on_the_back' => 'nullable|string|max:2048',
        ]);

        $workTimeMap = [
            'fulltime' => 2,
            'parttime' => 1,
            'online'   => 3,
        ];

        $workTime = $workTimeMap[strtolower($request->work_time)] ?? 0;
        $roleId = \App\CRMDV\Models\Roles::where('display_name', $request->role_name)->value('id');
        $room_id = DB::table('tags')->where('slug', 'like',  $request->room_name)->value('id');

        // Upload ảnh CCCD nếu có
        $frontImage = $request->hasFile('ID_card_photo_on_the_front')
            ? $request->file('ID_card_photo_on_the_front')->store('cccd', 'public')
            : null;

        $backImage = $request->hasFile('ID_card_photo_on_the_back')
            ? $request->file('ID_card_photo_on_the_back')->store('cccd', 'public')
            : null;

        //  Tạo admin mới
        $admin = new    \App\CRMDV\Models\Admin();
        $admin->fill([
            'name' => $request->name,
            'short_name' => $request->short_name,
            'email' => $request->email,
            'tel' => $request->tel,
            'password' => bcrypt($request->password),
            'facebook' => $request->facebook,
            'work_time' => $workTime,
            'room_id' => $room_id,
            'invite_by' => $request->invite_by,
            'admin_id' => $request->admin_id ?? 0,
            'code' => $request->code,
            'may_cham_cong_id' => $request->may_cham_cong_id,
            'status' => $request->status ?? 1,
            'address' => $request->address,
            'date_start_work' => $request->date_start_work,
            'birthday' => $request->birthday,
            'intro' => $request->intro,
            'note' => $request->note,
            'cccd' => $request->cccd,
            'gender' => $request->gioitinh,
            'ID_card_photo_on_the_front' => $frontImage,
            'ID_card_photo_on_the_back' => $backImage,

        ]);
        $admin->save();
        if ($roleId) {
            RoleAdmin::create([
                'admin_id' => $admin->id,
                'role_id' => $roleId,
            ]);
        }
        return response()->json([
            'success' => true,
            'msg' => 'Thêm nhân sự thành công',
            'data' => $admin,
        ]);
    }

    public function update(Request $request, $id)
    {
        $formRequest = new UpdateAdminRequest();
        $validator = Validator::make($request->all(), $formRequest->rules(), $formRequest->messages());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json([
                'success' => false,
                'msg' => 'Không tìm thấy nhân sự',
            ], 404);
        }

        // Validate với rules từ UpdateAdminRequest
        $formRequest = new UpdateAdminRequest();
        $validator = Validator::make($request->all(), $formRequest->rules(), $formRequest->messages());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        //  Upload ảnh mới (xóa ảnh cũ nếu có)
        if ($request->hasFile('ID_card_photo_on_the_front')) {
            if ($admin->ID_card_photo_on_the_front) {
                Storage::disk('public')->delete($admin->ID_card_photo_on_the_front);
            }
            $admin->ID_card_photo_on_the_front = $request->file('ID_card_photo_on_the_front')->store('cccd', 'public');
        }

        if ($request->hasFile('ID_card_photo_on_the_back')) {
            if ($admin->ID_card_photo_on_the_back) {
                Storage::disk('public')->delete($admin->ID_card_photo_on_the_back);
            }
            $admin->ID_card_photo_on_the_back = $request->file('ID_card_photo_on_the_back')->store('cccd', 'public');
        }

        //  Các field động được cập nhật nếu tồn tại trong request
        $fields = [
            'name', 'short_name', 'email', 'tel', 'facebook', 'work_time',
             'super_admin', 'code', 'may_cham_cong_id', 'status',
            'address', 'date_start_work', 'birthday', 'intro', 'note',
            'cccd', 'gioitinh',
        ];

        foreach ($fields as $field) {
            if ($request->filled($field) || $request->has($field)) {
                $admin->$field = $request->$field;
            }
        }

        if ($request->filled('password')) {
            $admin->password = bcrypt($request->password);
        }
        if($request->invite_by){
            $admin->invite_by = Admin:: where('name',$request->invite_by)->first();
        }
        $admin->save();

        return response()->json([
            'success' => true,
            'msg' => 'Cập nhật nhân sự thành công',
            'data' => $admin,
        ]);
    }

    public function top10NhanVienQuanTam(Request $request)
    {
        try {
            $start = $request->query('start');
            $end = $request->query('end');
            $month = $request->query('month');
            $year = $request->query('year');

            $query = Bill::select('saler_id', DB::raw('COUNT(DISTINCT customer_id) as so_luong_khach_quan_tam'));

            // --- Khai báo biến ngày để trả ra ---
            $startDate = null;
            $endDate = null;
            $periodLabel = '';
            if ($start && $end) {
                $query->whereBetween(DB::raw('DATE(registration_date)'), [$start, $end]);
                $startDate = $start;
                $endDate = $end;
                $periodLabel = "từ {$start} đến {$end}";
            } elseif ($month && $year) {
                $startDate = date("{$year}-{$month}-01");
                $endDate = date("Y-m-t", strtotime($startDate));
                $query->whereBetween(DB::raw('DATE(registration_date)'), [$startDate, $endDate]);
                $periodLabel = "tháng {$month} năm {$year}";
            } elseif ($year) {
                $startDate = "{$year}-01-01";
                $endDate = "{$year}-12-31";
                $query->whereBetween(DB::raw('DATE(registration_date)'), [$startDate, $endDate]);
                $periodLabel = "năm {$year}";
            } else {
                // Mặc định là tháng hiện tại
                $now = now();
                $startDate = $now->copy()->startOfMonth()->toDateString();
                $endDate = $now->copy()->endOfMonth()->toDateString();
                $query->whereBetween(DB::raw('DATE(registration_date)'), [$startDate, $endDate]);
                $periodLabel = "tháng này";
            }

            $topNhanVien = $query->whereNotNull('saler_id')
                ->groupBy('saler_id')
                ->orderByDesc('so_luong_khach_quan_tam')
                ->limit(10)
                ->get()
                ->map(function ($item, $index) {
                    $nvName = optional(Admin::find($item->saler_id))->name ?? 'Chưa có';
                    return [
                        'stt' => $index + 1,
                        'ten_nhan_vien' => $nvName,
                        'so_luong_khach_quan_tam' => (int)$item->so_luong_khach_quan_tam,
                    ];
                });

            return response()->json([
                'status' => true,
                'msg' => "Top 10 nhân viên có nhiều khách hàng quan tâm nhất trong {$periodLabel}",
                'data' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'top_nhan_vien' => $topNhanVien,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }





    public function list(Request $request)
    {
        // Lấy danh sách nhân sự từ bảng 'admin'
        $staffs = Admin::select([
            'id',
            'name',
            'code',
            'tel',
            'role_id',
            'room_id',
            'status'
        ])->get();

        // Mapping role_id và room_id sang tên hiển thị
        $roles = [
            1 => 'Kinh doanh',
            2 => 'Telesale',
            3 => 'Kỹ thuật',
            4 => 'Điều hành',
            5 => 'Marketing',
            6 => 'CSKH',
            7 => 'HR Tuyển dụng'
        ];

        $rooms = [
            1 => 'Phòng kinh doanh 1',
            2 => 'Phòng kinh doanh 2',
            3 => 'Phòng kinh doanh 3',
            4 => 'Phòng kinh doanh 4',
            5 => 'Phòng kinh doanh 5',
            6 => 'Phòng Telesale',
            10 => 'Kỹ thuật',
            15 => 'Điều hành',
            20 => 'Marketing',
            25 => 'Tuyển dụng',
            30 => 'CSKH',
        ];

        $list = $staffs->map(function($s) use ($roles, $rooms) {
            return [
                'ten' => $s->name,
                'ma_nv' => $s->code,
                'sdt' => $s->tel,
                'chuc_vu' => $roles[$s->role_id] ?? 'Chưa phân quyền',
                'phong' => $rooms[$s->room_id] ?? '',
                'kich_hoat' => $s->status == 1 ? 'Có' : 'Không',
            ];
        });

        return response()->json([
            'status' => 'success',
            'msg' => 'Danh sách nhân sự',
            'data' => $list
        ]);
    }

    public function detailOrUpdate(Request $request, $id = null)
    {

        $staff = $id? Admin::find($id) : new Admin();
        if ($id && !$staff) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nhân viên không tồn tại'
            ], 404);
        }
        if ($request->isMethod('post') || $request->isMethod('put')) {

            $validator = Validator::make($request->all(), [
                'invite_by' => 'nullable|string|max:255',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:admin,email,' . ($id ?? 'NULL'),
                'password' => $id ? 'nullable|string|min:6' : 'required|string|min:6',
                'work_time' => 'required|in:fulltime,parttime,online',
                'role_name' => 'nullable|string|max:255',
                'room_name' => 'nullable|string|max:255',
                'ID_card_photo_on_the_front' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
                'ID_card_photo_on_the_back' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                // Ném exception
                throw new ValidationException($validator, response()->json([
                    'status' => 'error',
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors(),
                ], 422));
            }
            $uploadFile = function($file, $folderName) {
                $fileName = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folder = date('Y/m/d');
                $destinationPath = base_path("public_html/filemanager/userfiles/$folderName/$folder");
                if (!file_exists($destinationPath)) mkdir($destinationPath, 0755, true);
                $file->move($destinationPath, $fileName);
                return "$folderName/$folder/$fileName";
            };
            if ($request->hasFile('image')) {
                $staff->image = $uploadFile($request->file('image'), 'staff');
            }
            if ($request->hasFile('ID_card_photo_on_the_front')) {
                $staff->ID_card_photo_on_the_front = $uploadFile($request->file('ID_card_photo_on_the_front'), 'staff/cccd');
            }
            if ($request->hasFile('ID_card_photo_on_the_back')) {
                $staff->ID_card_photo_on_the_back = $uploadFile($request->file('ID_card_photo_on_the_back'), 'staff/cccd');
            }

            // Gán các trường khác
            $staff->fill($request->except(['password','image','ID_card_photo_on_the_front','ID_card_photo_on_the_back']));

            if ($request->filled('password')) {
                $staff->password = Hash::make($request->password);
            }
            if ($request->filled('short_name')) {
                $staff->short_name = $request->short_name;
            }
            if ($request->filled('gender')) {
                $staff->gender = $request->gender;
            }

            // Room & role
            $staff->room_id = Room::where('name', $request->room_name)->value('id') ?? '';

            if ($request->filled('work_time')) {
                $map = [
                    'fulltime' => 1,
                    'parttime' => 2,
                    'online' => 3,
                ];
                $value = strtolower(trim($request->work_time));
                if (array_key_exists($value, $map)) {
                    $staff->work_time = $map[strtolower($request->work_time)] ?? 3;
                }
            }
            if ($request->filled('invite_by')) {
                $inviteById = Admin::where('name', $request->invite_by)->value('id');
                $staff->invite_by = $inviteById ?? null;
            }
            $staff->password = Hash::make('123456');
            if ($request->filled('status')) {
                $staff->status = $request->status=='false'? 0 : 1;
            }
            $staff->save();
            $roleId = \App\Models\Roles::where('display_name', $request->role_name)->value('id');
            if ($roleId) {
                RoleAdmin::updateOrCreate(
                    ['admin_id' => $staff->id],
                    ['role_id' => $roleId]
                );
            }
            if ($id === null) {
                Room::increment('employee_count');
            }

            return response()->json([
                'status' => 'success',
                'message' => $id ? 'Cập nhật nhân sự thành công' : 'Thêm nhân sự thành công',
                'data' => [
                    'image' => $staff->image ? asset("filemanager/userfiles/" . $staff->image) : "",
                    'ID_card_photo_on_the_front' => $staff->ID_card_photo_on_the_front ? asset("filemanager/userfiles/" . $staff->ID_card_photo_on_the_front) : "",
                    'ID_card_photo_on_the_back' => $staff->ID_card_photo_on_the_back ? asset("filemanager/userfiles/" . $staff->ID_card_photo_on_the_back) : "",
                    'work_time' => $request->work_time??'',
                    'role_id' => $request->role_name??'',
                    'room_id' => $request->room_name??'',
                    'invite_by' => $request->invite_by,
                    'name' => $staff->name,
                    'short_name' => $staff->short_name,
                    'email' => $staff->email,
                    'tel' => $staff->tel,
                    'code' => $staff->code,
                    'may_cham_cong_id' => (int)$staff->may_cham_cong_id ?? 0,
                    'status' => $staff->status,
                    'address' => $staff->address,
                    'date_start_work' => $staff->date_start_work,
                    'birthday' => $staff->birthday,
                    'intro' => $staff->intro,
                    'note' => $staff->note,
                    'cccd' => $staff->cccd,
                    'gioitinh' => $staff->gender,
                ]
            ]);
        }
        // GET → xem chi tiết
        $staff = Admin::findOrFail($id);

// Role & Room
        $roleId = $staff->roles()->first()->id ?? null;
        $roleName = CommonHelper::getRoleName($staff->id, 'display_name');
        if (is_array($roleName)) $roleName = implode(', ', $roleName);

        $roomName = optional($staff->room)->name ?? '';
        $roomSlug = optional($staff->room)->slug ?? '';

        $workTimeMap = [1 => 'fulltime', 2 => 'parttime', 3 => 'online'];
        $work_time1 = $workTimeMap[$staff->work_time] ?? "";
        $roleName = RoleAdmin::where('role_admin.admin_id', $staff->id)
            ->join('roles', 'roles.id', '=', 'role_admin.role_id')
            ->pluck('roles.display_name')
            ->implode(', ');


        return response()->json([
            'status' => 'success',
            'msg' => 'Chi tiết nhân sự',
            'data' => [
                'image' => $staff->image ? asset("filemanager/userfiles/" . $staff->image) : null,
                'ID_card_photo_on_the_front' => $staff->ID_card_photo_on_the_front ? asset("filemanager/userfiles/" . $staff->ID_card_photo_on_the_front) : null,
                'ID_card_photo_on_the_back' => $staff->ID_card_photo_on_the_back ? asset("filemanager/userfiles/" . $staff->ID_card_photo_on_the_back) : null,
                'work_time' => $work_time1,
                'role_id' => $roleName,
                'room_id' => optional($staff->room)->name ?? '',
                'name' => $staff->name,
                'short_name' => $staff->short_name,
                'email' => $staff->email,
                'tel' => $staff->tel,
                'code' => $staff->code,
                'may_cham_cong_id' => (int)$staff->may_cham_cong_id ?? 0,
                'status' => $staff->status,
                'address' => $staff->address,
                'date_start_work' => $staff->date_start_work,
                'birthday' => $staff->birthday,
                'intro' => $staff->intro,
                'note' => $staff->note,
                'cccd' => $staff->cccd,
                'gioitinh' => $staff->gender,
                'facebook' => $staff->facebook ?? '',
                'skype' => $staff->skype ?? '',
                'zalo' => $staff->zalo ?? '',
                'nguoi_tuyen' => optional($staff->invite)->name ?? '',
            ]
        ]);
    }
    public function updateStaff(Request $request, $id)
    {
        $staff = Admin::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin,email,' . $id,
            'password' => 'nullable|string|min:6',
            'work_time' => 'required|in:fulltime,parttime,online',
            'role_name' => 'required|string',
            'room_name' => 'nullable|string',
            'ID_card_photo_on_the_front' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'ID_card_photo_on_the_back' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator, response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422));
        }

        // Upload helper
        $uploadFile = function($file, $folderName) {
            $fileName = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $folder = date('Y/m/d');
            $destinationPath = base_path("public_html/filemanager/userfiles/$folderName/$folder");
            if (!file_exists($destinationPath)) mkdir($destinationPath, 0755, true);
            $file->move($destinationPath, $fileName);
            return "$folderName/$folder/$fileName";
        };

        if ($request->has('status')) {
            $staff->status = $request->status ? 1 : 0;
        }
        if ($request->hasFile('image')) {
            $staff->image = $uploadFile($request->file('image'), 'staff');
        }
        if ($request->hasFile('ID_card_photo_on_the_front')) {
            $staff->ID_card_photo_on_the_front = $uploadFile($request->file('ID_card_photo_on_the_front'), 'staff/cccd');
        }
        if ($request->hasFile('ID_card_photo_on_the_back')) {
            $staff->ID_card_photo_on_the_back = $uploadFile($request->file('ID_card_photo_on_the_back'), 'staff/cccd');
        }

        // Work time mapping
        $workTimeMap = ['fulltime' => 1, 'parttime' => 2, 'online' => 3];
        $staff->work_time = $workTimeMap[strtolower($request->work_time ?? '')] ?? 0;

        // Gán các trường khác
        $staff->fill($request->except(['password','image','ID_card_photo_on_the_front','ID_card_photo_on_the_back']));
        if ($request->filled('password')) {
            $staff->password = \Hash::make($request->password);
        }
        if ($request->filled('short_name')) {
            $staff->short_name = $request->short_name;
        }
        if ($request->filled('gender')) {
            $staff->gender = $request->gender;
        }

        $staff->room_id = DB::table('tags')->where('slug', $request->room_name)->value('id') ?? null;
        $staff->save();

        $roleId = \App\Models\Roles::where('display_name', $request->role_name)->value('id');
        if ($roleId) {
            $staff->roles()->sync([$roleId]);
        }
        $staff['may_cham_cong_id']=(int)$staff->may_cham_cong_id ?? 0;

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật nhân sự thành công',
            'data' => $staff
        ]);
    }



    public function sinhNhatNhanSu(Request $request)
    {
        $month = $request->input('month', \Carbon\Carbon::now()->month);

        // Lấy danh sách nhân sự có sinh nhật trong tháng hiện tại và đang active
        $staffs = DB::table('admin')
            ->select('name', 'room_id',  DB::raw("DATE_FORMAT(birthday, '%d-%m') as birthday"))
            ->whereNotNull('birthday')
            ->where('status', 1)
            ->whereRaw("birthday != '0000-00-00'")
            ->whereRaw("MONTH(birthday) = ?", [$month])
            ->orderByRaw('DAY(birthday)')
            ->get();

        return response()->json([
            'status' => 'success',
            'msg' => 'Danh sách sinh nhật nhân sự tháng '. $month,
            'data' => $staffs
        ]);
    }



//    public function update(Request $request, $id)
//    {
//        $admin = Admin::findOrFail($id);
//        $admin->update($request->all());
//        return response()->json([
//            'status' => true,
//            'message' => 'Cập nhật nhan vien thành công',
//            'data' => $admin
//        ]);
//    }

//    public function destroy($id)
//    {
//        $admin = Admin::findOrFail($id);
//        $admin->delete();
//        return response()->json([
//            'status' => true,
//            'message' => 'Xóa nhan vien thành công'
//        ]);
//    }
    public function getNhansu(Request $request)
    {
       $listNhansu = Admin::all();
       $phongbanName = Tag::where('type','phong_ban')->get();

        return response()->json([
            'status' => true,
            'msg' => 'Danh sách ',
            'data' => $phongbanName,

        ]);
    }
}
