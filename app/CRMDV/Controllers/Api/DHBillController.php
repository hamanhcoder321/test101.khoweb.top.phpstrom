<?php

namespace App\CRMDV\Controllers\Api;

use App\Library\JWT\Facades\JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\CURDBaseController;
use Carbon\Carbon;
use App\CRMDV\Models\Admin;

use App\CRMDV\Models\Setting;
use App\CRMDV\Models\Roles;
use App\CRMDV\Models\User;
use Auth;
use Mail;
use Session;
use Validator;
use App\CRMDV\Models\Lead;
use App\CRMDV\Controllers\Helpers\CRMDVHelper;
use App\CRMDV\Models\Bill;
use App\CRMDV\Models\BillHistory;
use App\CRMDV\Models\BillFinance;
use App\CRMDV\Models\BillProgress;
use App\CRMDV\Models\BillProgressHistory;
use App\CRMDV\Models\Service;

use DB;
class DHBillController extends CURDBaseController
{
    protected $orderByRaw = 'id DESC';

    protected $module = [
        'code' => 'dhbill',
        'table_name' => 'bills',
        'label' => 'Dự án',
        'modal' => '\App\CRMDV\Models\Bill',
        'list' => [
            ['name' => 'domain', 'type' => 'text_edit', 'label' => 'Tên miền'],
            ['name' => 'customer_id', 'type' => 'relation', 'label' => 'Khách hàng', 'object' => 'user', 'display_field' => 'name'],
//            ['name' => 'customer_id', 'type' => 'relation', 'label' => 'SĐT', 'object' => 'admin', 'display_field' => 'tel'],
//            ['name' => 'customer_id', 'type' => 'relation', 'label' => 'Loại khách', 'object' => 'user', 'display_field' => 'classify'],
            ['name' => 'service_id', 'type' => 'custom', 'label' => 'Dịch vụ', 'td' => 'CRMDV.dhbill.list.td.service'],
            ['name' => 'progress_id', 'type' => 'relation', 'label' => 'Tiến độ', 'object' => 'bill_progress', 'display_field' => 'status'],
            ['name' => 'finance_id', 'type' => 'custom', 'td' => 'CRMDV.dhbill.list.td.thanh_toan', 'label' => 'Thanh toán', 'object' => 'bill_finance', 'display_field' => 'debt'],
            ['name' => 'saler_id', 'type' => 'relation', 'label' => 'Kinh doanh', 'object' => 'saler', 'display_field' => 'name'],
            ['name' => 'progress_id', 'type' => 'relation_through', 'label' => 'Điều hành', 'object' => 'bill_progress', 'object2' => 'dieu_hanh', 'display_field' => 'name'],
            ['name' => 'progress_id', 'type' => 'relation_through', 'label' => 'Kỹ thuật', 'object' => 'bill_progress', 'object2' => 'ky_thuat', 'display_field' => 'name'],
            // ['name' => 'reminder_customer', 'type' => 'custom', 'td' => 'CRMDV.dhbill.list.td.reminder_customer', 'label' => 'Deadline', 'sort' => true],
            ['name' => 'registration_date', 'type' => 'date_vi', 'label' => 'Ngày ký', 'sort' => true],
//            ['name' => 'rate_content', 'type' => 'custom', 'td' => 'CRMDV.dhbill.list.td.rate_content', 'label' => 'Đánh giá', 'sort' => true],
            ['name' => 'kh_xong_image', 'type' => 'custom', 'td' => 'CRMDV.dhbill.list.td.kh_xong_image', 'label' => 'Ảnh KH XN', 'sort' => true],
            ['name' => 'kh_xong_date', 'type' => 'custom', 'td' => 'CRMDV.dhbill.list.td.kh_xong_date', 'label' => 'Ngày xong',],

            ['name' => 'invite_more_services', 'type' => 'status', 'label' => 'Mời thêm DV', 'sort' => true],

        ],
        'form' => [
            'general_tab' => [

                ['name' => 'domain', 'type' => 'text', 'label' => 'Tên miền', 'class' => ' required', 'group_class' => 'col-md-3'],
                ['name' => 'service_id', 'type' => 'select2_model', 'class' => ' required', 'label' => 'Gói DV', 'model' => Service::class, 'display_field' => 'name_vi', 'group_class' => 'col-md-6', 'inner' => 'disabled'],
                ['name' => 'registration_date', 'type' => 'date', 'label' => 'Ngày ký HĐ', 'class' => 'required', 'group_class' => 'col-md-3', 'inner' => 'disabled'],
                ['name' => 'customer_note', 'type' => 'textarea', 'class' => 'required', 'label' => 'Khách đã trả tiền mua tên miền chưa? đuôi tên miền gì?'],

                /*['name' => 'retention_time', 'type' => 'select', 'options' =>
                    [
                        0 => 'Không bảo hành',
                        1 => '1 tháng',
                        3 => '3 tháng',
                        6 => '6 tháng',
                        8 => '8 tháng',
                        12 => '12 tháng',
                        36 => '36 tháng',
                    ], 'class' => '', 'label' => 'Thời hạn duy trì', 'value' => 5, 'group_class' => 'col-md-6'],*/

            ],
            'customer_tab' => [
                ['name' => 'saler_id', 'type' => 'select2_ajax_model', 'label' => 'Người sale', 'model' => Admin::class, 'object' => 'admin', 'display_field' => 'name', 'display_field2' => 'code', 'inner' => 'disabled'],
                ['name' => 'customer_id', 'type' => 'custom', 'type_history' => 'relation_multiple', 'field' => 'CRMDV.form.fields.select_customer',
                    'label' => 'Khách hàng', 'model' => Admin::class, 'object' => 'admin', 'display_field' => 'name', 'display_field2' => 'tel', 'class' => 'required', 'inner' => 'disabled'],
                ['name' => 'customer_legal_id', 'type' => 'custom', 'type_history' => 'relation_multiple', 'field' => 'CRMDV.form.fields.select_customer',
                    'label' => 'Đại diện pháp lý', 'model' => Admin::class, 'object' => 'admin', 'display_field' => 'name', 'display_field2' => 'tel', 'class' => '', 'inner' => 'disabled'],
            ],
            'gia_han_tab' => [],
            'service_tab' => [

                ['name' => 'account_note', 'type' => 'textarea', 'label' => 'Note tài khoản'],
                ['name' => 'status', 'type' => 'checkbox', 'label' => 'Kích hoạt', 'value' => 1,],
                ['name' => 'note', 'type' => 'textarea', 'label' => 'Ghi chú'],

            ],
            'wp_tab' => [],
            'ldp_tab' => [],
        ],
    ];

    protected $filter = [
        'progress' => [
            'label' => 'Tiến độ',
            'type' => 'select',
            'options' => [
                '' => '',
                'Đang làm' => 'Đang làm',
                'Khách xác nhận xong' => 'Khách xác nhận xong',
                'Kết thúc' => 'Kết thúc',
                'Bỏ' => 'Bỏ',
                'test' => 'test',
            ],
            'query_type' => 'custom'
        ],
        'customer_id' => [
            'label' => 'Tên/SĐT khách hàng',
            'type' => 'select2_ajax_model',
            'display_field' => 'name',
            'model' => \App\Models\Admin::class,
            'object' => 'admin',
            'query_type' => '='
        ],
        'saler_id' => [
            'label' => 'Sale',
            'type' => 'select2_ajax_model',
            'display_field' => 'name',
            'display_field2' => 'code',
            'model' => \App\Models\Admin::class,
            'object' => 'admin',
            'query_type' => '='
        ],
        'nv_id' => [
            'label' => 'Điều hành / kỹ thuật',
            'type' => 'select2_ajax_model',
            'display_field' => 'name',
            'display_field2' => 'code',
            'model' => \App\Models\Admin::class,
            'object' => 'admin',
            'query_type' => 'custom'
        ],
        'service_id' => [
            'label' => 'Dịch vụ',
            'type' => 'select2_model',
            'display_field' => 'name_vi',
            'model' => Service::class,
            'query_type' => '='
        ],
        'check_chua_xong' => [
            'label' => 'Check D.Án chưa xong',
            'type' => 'textarea',
            'query_type' => 'custom'
        ],
        'filter_date' => [
            'label' => 'Lọc theo',
            'type' => 'filter_date',
            'options' => [
                '' => '',
//                'kh_xong_date' => 'Ngày khách xác nhận xong',
            ],
            'query_type' => 'filter_date'
        ],
    ];

    protected $quick_search = [
        'label' => 'ID, tên miền, giá, note',
        'fields' => 'id, domain, note, customer_note'
    ];

    public function sort($request, $model)
    {
        $orderRaw = 'CASE ';

        //  - Nếu dự án chưa set trạng thái thì đẩy lên đầu
        //  lấy id các dự án đang làm
        $bill_id_dang_lam = BillProgress::where(function ($query) use ($request) {
            $query->orWhere('status', '');    //
            $query->orWhereNull('status');    //
        })->pluck('bill_id')->toArray();


        if (!empty($bill_id_dang_lam)) {
            $orderRaw .= " WHEN id in (";

            foreach ($bill_id_dang_lam as $k => $v) {
                if ($k == 0) {
                    $orderRaw .= $v;
                } else {
                    $orderRaw .= "," . $v;
                }
            }
            $orderRaw .= ') THEN 1';
        }

        $bill_id_dang_lam = BillProgress::whereNotIn('status', ['Kết thúc', 'Tạm dừng', 'Khách xác nhận xong'])->pluck('bill_id')->toArray();

        if (!empty($bill_id_dang_lam)) {
            //  Nếu là dự án đang làm thì ưu tiên hiển thị lên trên
            $orderRaw .= " WHEN id in (";

            foreach ($bill_id_dang_lam as $k => $v) {
                if ($k == 0) {
                    $orderRaw .= $v;
                } else {
                    $orderRaw .= "," . $v;
                }
            }
            $orderRaw .= ') THEN 2';
        }
        $bill_id_dang_lam = BillProgress::whereIn('status', ['Khách xác nhận xong'])->pluck('bill_id')->toArray();

        if (!empty($bill_id_dang_lam)) {
            //  Nếu là dự án đang làm thì ưu tiên hiển thị lên trên
            $orderRaw .= " WHEN id in (";

            foreach ($bill_id_dang_lam as $k => $v) {
                if ($k == 0) {
                    $orderRaw .= $v;
                } else {
                    $orderRaw .= "," . $v;
                }
            }
            $orderRaw .= ') AND total_price_contract != total_received THEN 3';
        }


        //  - Nếu dự án "tạm dừng" đẩy lên trên
        //  lấy id các dự án đang làm
        $bill_id_dang_lam = BillProgress::whereIn('status', ['Tạm dừng'])->pluck('bill_id')->toArray();

        if (!empty($bill_id_dang_lam)) {
            //  Nếu là dự án đang làm thì ưu tiên hiển thị lên trên
            $orderRaw .= " WHEN id in (";

            foreach ($bill_id_dang_lam as $k => $v) {
                if ($k == 0) {
                    $orderRaw .= $v;
                } else {
                    $orderRaw .= "," . $v;
                }
            }
            $orderRaw .= ') AND total_price_contract != total_received THEN 4';
        }

        $orderRaw .= ' ELSE id END ASC';

        $model = $model->orderByRaw($orderRaw);
        $model = $model->orderBy('id', 'desc');

        return $model;
    }

    public function appendWhere($query, $request)
    {
        if (@$request->filled('progress')) {
            $progress = $request->progress ?? '';
            if ($progress !== '') {
                switch ($progress) {
                    case 'Bỏ':
                        $bill_ids = BillProgress::whereIn('status', ['Bỏ'])->pluck('bill_id')->toArray();
                        $query = $query->whereIn('id', $bill_ids);
                        break;
                    case 'Đang làm':
                        $statuses = [
                            'Thu thập YCTK L1', 'Triển khai L1', 'Nghiệm thu L1 & thu thập YCTK L2',
                            'Triển khai L2', 'Nghiệm thu L2 & thu thập YCTK L3', 'Triển khai L3',
                            'Nghiệm thu L3 & thu thập YCTK L4', 'Triển khai L4',
                            'Nghiệm thu L4 & thu thập YCTK L5', 'Triển khai L5',
                            'Nghiệm thu L5 & thu thập YCTK L6', 'Triển khai L6',
                        ];
                        $bill_ids = BillProgress::whereIn('status', $statuses)->pluck('bill_id')->toArray();
                        $query = $query->whereIn('id', $bill_ids);
                        break;
                    case 'Khách xác nhận xong':
                        $bill_ids = BillProgress::where('status', 'Khách xác nhận xong')->pluck('bill_id')->toArray();
                        $query = $query->whereIn('id', $bill_ids);
                        break;
                    case 'Kết thúc':
                        $bill_ids = BillProgress::whereIn('status', ['Tạm dừng', 'Kết thúc'])->pluck('bill_id')->toArray();
                        $query = $query->whereIn('id', $bill_ids);
                        break;
                }
            } else {

                $bill_bo_ids = BillProgress::where('status', 'Bỏ')->pluck('bill_id')->toArray();
                $query = $query->whereNotIn('id', $bill_bo_ids);
            }
        }


        if (@$request->filled('customer_name')) {
            $id = Lead::where('name', 'like', $request->customer_name)->pluck('id')->toArray();
            $query = $query->where('customer_id', $id);
        }

        if (@$request->filled('saler_name')) {
            $saler_id = Admin::where('name', 'like', $request->saler_name)->pluck('id')->toArray();
            $query = $query->where('saler_id', $saler_id);
        }

        if (@$request->filled('nv_name')) {
            $nv_id = Admin::where('name', 'like', $request->nv_name)->pluck('id')->toArray();
            $bill_ids = BillProgress::where(function ($q) use ($nv_id) {
                $q->orWhere('dh_id', $nv_id);
                $q->orWhere('kt_id', $nv_id);
            })->pluck('bill_id')->toArray();
            $query = $query->whereIn('id', $bill_ids);
        }

        if ($request->filled('service_name')) {
            $default_not_in = [3, 4, 7, 6]; // loại bỏ dự án duy trì web

            // Lấy các service_id từ tên
            $service_ids = Service::where('name_vi', 'like', $request->service_name)
                ->pluck('id')
                ->toArray();

            if (!empty($service_ids)) {
                // Loại bỏ các id nằm trong danh sách mặc định not_in
                $service_ids = array_diff($service_ids, $default_not_in);

                if (!empty($service_ids)) {
                    $query = $query->whereIn('service_id', $service_ids);
                } else {
                    // Không có service nào hợp lệ
                    $query = $query->whereRaw('0=1');
                }
            } else {
                // Không tìm thấy service nào trùng tên
                $query = $query->whereRaw('0=1');
            }
        }


        if (@$request->filled('check_chua_xong')) {
            $domains = preg_split('/\r\n|[\r\n]/', $request->check_chua_xong);
            $bill_ids = [];
            foreach ($domains as $domain) {
                $bill = Bill::where('domain', trim($domain))->first();
                if ($bill && !BillProgress::where('bill_id', $bill->id)
                        ->whereIn('status', ['Khách xác nhận xong', 'Kết thúc'])
                        ->exists()) {
                    $bill_ids[] = $bill->id;
                }
            }
            if (!empty($bill_ids)) {
                $query = $query->whereIn('id', $bill_ids);
            } else {
                $query = $query->whereRaw('0=1'); // Không tìm thấy domain nào chưa xong
            }
        }


        if ($request->filled('filter_date')) {
            // ví dụ: filter theo registration_date
            if ($request->filter_date['from'] ?? false && $request->filter_date['to'] ?? false) {
                $from = Carbon::parse($request->filter_date['from'])->startOfDay();
                $to = Carbon::parse($request->filter_date['to'])->endOfDay();
                $query = $query->whereBetween('registration_date', [$from, $to]);
            }
        }

        return $query;
    }

    public function getDataList(Request $request)
    {

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
            $this->exportExcel($request, $listItem->get());
        }

        //  Sort
        $listItem = $this->sort($request, $listItem);

        $data['record_total'] = $listItem->count();


        //  Tính điểm
        //  id người tính điểm
        $nv_id = $request->has('nv_id') ? $request->nv_id : JWTAuth::parseToken()->id;

        $bills = $listItem->get();
        $data['tong_diem'] = 0;
        foreach ($bills as $bill) {
            $bill_process = BillProgress::where('bill_id', $bill->id)->whereIn('status', ['Khách xác nhận xong', 'Kết thúc'])->first();

            if (is_object($bill_process)) {
                if ($bill_process->dh_id == $nv_id) {
                    //  nếu dự án do mình điều hành thì tính điểm điều hành
                    if (isset($diem_dh[$bill->service_id])) {

                        $data['tong_diem'] += (float)@$diem_dh[$bill->service_id];
                    }
                }
                if ($bill_process->kt_id == $nv_id) {
                    //  nếu dự án do mình triển khai thì tính điểm kỹ thuật
                    if (isset($diem_kt[$bill->service_id])) {
                        $data['tong_diem'] += @$diem_kt[$bill->service_id];
                    }

                }
            }
        }

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

        $dataList = $this->getDataList($request);
        $paginated = $dataList['listItem']; // LengthAwarePaginator

        $data_mobile = $paginated->getCollection()->map(function ($item) {
            return [
                'id' => $item->id,
                'domain' => $item->domain ?? '',
                'customer_name' => optional($item->customer)->name ?? '',
                'service_name' => optional($item->service)->name_vi ?? '',
                'total_price' => $item->total_price,
                'registration_date' => $item->registration_date
                    ?Carbon::parse($item->registration_date)->format('d M, Y')
                    : null,
                'saler_name' => optional($item->saler)->name ?? '',
                'image' => $item->image
                    ? url('filemanager/userfiles/' . $item->image)
                    : url('default/avatar.png'),
            ];
        })->toArray();

        return response()->json([
            'status' => true,
            'msg' => 'Lấy danh sách dự án thành công',
            'data' => $data_mobile,
            'paginate' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }


    public function getDetail($id)
    {
        // Lấy bill kèm các quan hệ cần thiết
        $bill = Bill::with([
            'saler',
            'customer_legal',
            'bill_progress'
        ])->find($id);


        if (!$bill) {
            return response()->json([
                'status' => false,
                'msg' => 'Không tìm thấy dự án'
            ], 404);
        }

        // Ghi chú tiến độ mới nhất
        $latestProgress = $bill->bill_progress_history
            ->sortByDesc('created_at')
            ->first();
        $saler = Admin::where('id', $bill->saler_id)->first();
        $customerLegal = Lead::where('id', $bill->customer_legal_id)->first();
        $data = [
            'thong_tin_don_hang' => [
                'ten_mien' => $bill->domain ?? '',
                'goi_dich_vu' => optional($bill->service)->name_vi ?? $bill->service_name2 ?? '',
                'ngay_ky_hd' => $bill->registration_date
                    ? Carbon::parse($bill->registration_date)->format('d/m/Y')
                    : null,
                'khach_da_tra_tien_ten_mien' => ($bill->total_received ?? 0) >= ($bill->total_price ?? 0),
                'khach_hang' => optional( $bill->customer)->name ?? '',
                'total_price' => $bill->total_price ?? 0,
                'total_received' => $bill->total_received ?? 0,
            ],

            'thong_tin_dich_vu' => [
                'nguoi_sale' => $saler->name ?? '',
                'nguoi_dai_dien' => optional(optional($bill->bill_progress)->dai_dien)->name ?? '',
                'nguoi_dieu_hanh' => optional(optional($bill->bill_progress)->dieu_hanh)->name ?? '',
                'tien_do' => optional($bill->bill_progress)->status ?? '',
            ],

            'trien_khai' => [
                'nguoi_trien_khai' => optional(optional($bill->bill_progress)->trien_khai)->name ?? '',
                'nguoi_ky_thuat' => optional(optional($bill->bill_progress)->ky_thuat)->name ?? '',
                'kich_hoat' => (bool)$bill->status ,
                'ghi_chu' => $latestProgress->note ?? $bill->note ?? '',
            ],

//            'anh_dai_dien' => $bill->image
//                ? url('filemanager/userfiles/' . $bill->image)
//                : url('default/avatar.png'),
        ];

        return response()->json([
            'status' => true,
            'msg' => 'Lấy thông tin chi tiết dự án thành công',
            'data' => $data,
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ten_mien'  => 'nullable|string|max:255',
            'goi_dich_vu' => 'nullable|string|max:255',
            'ngay_ky_hd' => 'nullable|date_format:d/m/Y',
            'khach_da_tra_tien_ten_mien' => 'nullable|boolean',

            'nguoi_sale' => 'nullable|string',
            'dai_dien' => 'nullable|string',
            'nguoi_dieu_hanh' => 'nullable|string',
            'tien_do' => 'nullable|string',


            'khach_hang'=>'nullable|string',
            'total_price'=>'nullable|numeric',
            'total_received'=>'nullable|numeric',
            'trien_khai' => 'nullable|string',
            'nguoi_ky_thuat' => 'nullable|string',
            'kich_hoat' => 'boolean',
            'ghi_chu' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 400);
        }
        $bill = new Bill();
        $bill->domain = $request->ten_mien ?? null;
        if ($request->goi_dich_vu) {
            $service = Service::where('name_vi', $request->goi_dich_vu)->first();
            if (!$service) {
                return response()->json(['status' => false, 'msg' => 'Gói dịch vụ không tồn tại'], 400);
            }
            $bill->service_id = $service->id;
        }
        $bill->total_price = $request->total_price ?? 0;
        $bill->total_received = $request->total_received ?? 0;

        $bill->note = $request->ghi_chu ?? null;

        if ($request->ngay_ky_hd) {
            $bill->registration_date = Carbon::createFromFormat('d/m/Y', $request->ngay_ky_hd)
                ->format('Y-m-d');
        }

        if ($request->has('kich_hoat')) {
            $bill->status = $request->kich_hoat==true ? 1:0;
        }
        if($request->has('khach_hang')){
            $lead = Lead::where('name',$request->khach_hang)->first();
            if(!$lead){
                return response()->json(['status'=>false,'msg'=>'Khách hàng không tồn tại'],400);
            }
            $bill->customer_id = $lead->id;
        }
        // SALE
        if ($request->nv_sale) {
            $admin = Admin::where('name', $request->nv_sale)->first();
            if (!$admin) return response()->json(['status'=>false,'msg'=>'NV Sale không tồn tại'], 400);
            $bill->saler_id = $admin->id;
        }

        $bill->save();

        $progress = new BillProgress();
        $progress->bill_id = $bill->id;

        $mapping = [
            'nv_dai_dien' => 'dd_id',
            'nv_dieu_hanh' => 'dh_id',
            'nv_ky_thuat' => 'kt_id',
            'nv_trien_khai' => 'tk_id'
        ];

        foreach ($mapping as $key => $column) {
            if ($request->$key) {
                $admin = Admin::where('name', $request->$key)->first();
                if (!$admin) return response()->json(['status'=>false,'msg'=>"$key không tồn tại"], 400);
                $progress->$column = $admin->id;
            }
        }

        if ($request->tien_do) $progress->status = $request->tien_do;
        if ($request->ghi_chu) $progress->note = $request->ghi_chu;

        $progress->save();

        return response()->json([
            'status' => true,
            'msg' => 'Tạo dự án thành công',
            'data' => [
                'id' => $bill->id,
                'ten_mien'  => $bill->domain ?? '',
                'goi_dich_vu' => $service->name_vi ?? '',
                'ngay_ky_hd' => $request->ngay_ky_hd ?? null,
                'khach_da_tra_tien_ten_mien' => $request->khach_da_tra_tien_ten_mien ?? null,

                'nguoi_sale' => $request->nv_sale ?? null,
                'dai_dien' => $request->nv_dai_dien ?? null,
                'nguoi_dieu_hanh' => $request->nv_dieu_hanh ?? null,
                'tien_do' => $request->tien_do ?? null,

                'khach_hang'=>$request->khach_hang ?? null,
                'total_price'=>$request->total_price ?? null,
                'total_received'=> $request->total_received ?? null,
                'trien_khai' => $request->nv_trien_khai ?? null,
                'nguoi_ky_thuat' => $request->nv_ky_thuat ?? null,
                'kich_hoat' => (bool)$bill->status,
                'ghi_chu' => $request->ghi_chu ?? null,
            ]
        ], 200);

    }


    public function update(Request $request, $id)
    {
        $bill = Bill::find($id);
        if (!$bill) {
            return response()->json(['status'=>false,'msg'=>'Không tìm thấy dự án'], 404);
        }

        $validator = Validator::make($request->all(), [
            'ten_mien' => 'nullable|string|max:255',
            'goi_dich_vu' => 'nullable|string|max:255',
            'ngay_ky_hd' => 'nullable|date_format:d/m/Y',
            'khach_da_tra_tien_ten_mien' => 'nullable|boolean',

            'nv_sale' => 'nullable|string',
            'nv_dai_dien' => 'nullable|string',
            'nv_dieu_hanh' => 'nullable|string',
            'tien_do' => 'nullable|string',

            'khach_hang'=>'nullable|string',
            'total_price'=>'nullable|numeric',
            'total_received'=>'nullable|numeric',
            'nv_trien_khai' => 'nullable|string',
            'nv_ky_thuat' => 'nullable|string',
            'kich_hoat' => 'boolean',
            'ghi_chu' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $progress = BillProgress::where('bill_id', $id)->first();
        if (!$progress) {
            return response()->json(['status'=>false,'msg'=>'Không tìm thấy tiến độ dự án'], 404);
        }

        // SALE
        if ($request->has('nv_sale')) {
            $admin = Admin::where('name', $request->nv_sale)->first();
            if (!$admin) return response()->json(['status'=>false,'msg'=>'NV Sale không tồn tại'], 400);
            $bill->saler_id = $admin->id;
        }

        // Mapping admin
        $mapping = [
            'nv_dai_dien' => 'dd_id',
            'nv_dieu_hanh' => 'dh_id',
            'nv_ky_thuat' => 'kt_id',
            'nv_trien_khai' => 'tk_id'
        ];

        foreach ($mapping as $key => $column) {
            if ($request->has($key)) {
                $admin = Admin::where('name', $request->$key)->first();
                if (!$admin) return response()->json(['status'=>false,'msg'=>"$key không tồn tại"], 400);
                $progress->$column = $admin->id;
            }
        }

        if ($request->has('ten_mien')) {
            $bill->domain = $request->ten_mien;
        }

        if ($request->has('goi_dich_vu')) {
            $service = Service::where('name_vi', $request->goi_dich_vu)->first();
            if (!$service) return response()->json(['status'=>false,'msg'=>'Gói dịch vụ không tồn tại'], 400);
            $bill->service_id = $service->id;
        }
        if ($request->has('ghi_chu')) {
            $bill->note = $request->ghi_chu;
            $progress->note = $request->ghi_chu;
        }
        if ($request->has('ngay_ky_hd')) {
            $bill->registration_date = Carbon::createFromFormat('d/m/Y', $request->ngay_ky_hd)->format('Y-m-d');
        }
        if ($request->has('tien_do')) {
            $progress->status = $request->tien_do;
        }
        if ($request->has('kich_hoat')) {
            $bill->status = $request->kich_hoat==true ? 1 : 0;
        }
        if($request->has('khach_hang')){
            $lead = Lead::where('name',$request->khach_hang)->first();
            if(!$lead){
                return response()->json(['status'=>false,'msg'=>'Khách hàng không tồn tại'],400);
            }
            $bill->customer_id = $lead->id;
        }
        if($request->has('total_price')){
            $bill->total_price = $request->total_price;
        }
        if($request->has('total_received')){
            $bill->total_received = $request->total_received;
        }
        $bill->save();
        $progress->save();
        return response()->json([
            'status' => true,
            'msg' => 'Tạo dự án thành công',
            'data' => [
                'id' => $bill->id,
                'ten_mien'  => $bill->domain ?? '',
                'goi_dich_vu' => $service->name_vi ?? '',
                'ngay_ky_hd' => $request->ngay_ky_hd ?? null,
                'khach_da_tra_tien_ten_mien' => $request->khach_da_tra_tien_ten_mien ?? null,

                'nguoi_sale' => $request->nv_sale ?? null,
                'dai_dien' => $request->nv_dai_dien ?? null,
                'nguoi_dieu_hanh' => $request->nv_dieu_hanh ?? null,
                'tien_do' => $request->tien_do ?? null,

                'trien_khai' => $request->nv_trien_khai ?? null,
                'nguoi_ky_thuat' => $request->nv_ky_thuat ?? null,
                'kich_hoat' => $bill->status==1? true:false,
                'ghi_chu' => $request->ghi_chu ?? null,
            ]
        ], 200);

    }
 public function getService(){
        $services = Service::all()->map(function ($service) {
            return [
                'id' => $service->id,
                'name_vi' => $service->name_vi,
            ];
        })->toArray();

        return response()->json([
            'status' => true,
            'msg' => 'Lấy danh sách dịch vụ thành công',
            'data' => $services,
        ]);
 }



}