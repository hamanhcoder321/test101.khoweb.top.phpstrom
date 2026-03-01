<?php

namespace App\CRMDV\Controllers\Api;
use App\CRMDV\Controllers\Helpers\CRMDVHelper;
use App\CRMDV\Models\BillReceipts;
use App\CRMDV\Models\Lead;
use App\Library\JWT\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\CURDBaseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Helpers\CommonHelper;
use App\CRMDV\Models\Admin;
use App\Models\RoleAdmin;
use App\Modules\HBBill\Models\Bill;
use App\Modules\HBBill\Models\BillPayment;
use App\CRMDV\Models\Setting;
use App\CRMDV\Models\Roles;
use App\CRMDV\Models\User;
use App\CRMDV\Models\Tag;
//use App\CRMDV\Models\GoiDichVu;
use Auth;
use Mail;
use phpDocumentor\Reflection\Types\Nullable;
use Session;
use Validator;
//use App\CRMDV\Controllers\Helpers\CRMDVHelper;
//use App\CRMDV\Models\Bill;
use App\CRMDV\Models\BillHistory;
use App\CRMDV\Models\BillFinance;
use App\CRMDV\Models\BillProgress;
use App\CRMDV\Models\BillProgressHistory;
use App\CRMDV\Models\Service;
use Carbon\Carbon;

use Cache;
class BillController extends CURDBaseController
{
    protected $orderByRaw = 'id DESC';

//    protected $whereRaw = 'service_id IN (1, 17, 18, 19, 20, 21)';

    protected $module = [
        'code' => 'bill',
        'table_name' => 'bills',
        'label' => 'Hợp đồng',
        'modal' => '\App\CRMDV\Models\Bill',
        'list' => [
            ['name' => 'domain', 'type' => 'text_edit', 'label' => 'Tên miền', 'sort' => true],
            ['name' => 'customer_id', 'type' => 'relation', 'label' => 'Khách hàng', 'object' => 'user', 'display_field' => 'name'],
            ['name' => 'total_price', 'type' => 'price_vi', 'label' => 'Doanh số', 'sort' => true],
            ['name' => 'total_price_contract', 'type' => 'price_vi', 'label' => 'Tổng $'],
            ['name' => 'total_received', 'type' => 'price_vi', 'label' => '$ đã thu'],
            ['name' => 'finance_id', 'type' => 'custom', 'td' => 'CRMDV.dhbill.list.td.chua_thu', 'label' => '$ chưa thu'],
            ['name' => 'service_id', 'type' => 'relation', 'label' => 'Dịch vụ', 'object' => 'service', 'display_field' => 'name_vi', 'sort' => true],
//            ['name' => 'count_product', 'type' => 'custom', 'td' => 'CRMDV.list.td.count_product', 'label' => 'Tổng SP'],
            ['name' => 'registration_date', 'type' => 'date_vi', 'label' => 'Ngày ký', 'sort' => true],
            ['name' => 'expiry_date', 'type' => 'date_vi', 'label' => 'Ngày hết hạn', 'sort' => true],
            // ['name' => 'exp_price', 'type' => 'price_vi', 'label' => 'Giá gia hạn', 'sort' => true],
            // ['name' => 'expiry_date', 'type' => 'date_vi', 'label' => 'Hết hạn', 'sort' => true],
            ['name' => 'auto_extend', 'type' => 'custom', 'td' => 'CRMDV.list.td.status', 'label' => 'Duy trì', 'sort' => true
            ],
            ['name' => 'status', 'type' => 'custom', 'td' => 'CRMDV.list.td.status', 'label' => 'Trạng thái', 'sort' => true
            ],
            ['name' => 'sale_id', 'type' => 'relation_name', 'label' => 'Sale', 'sort' => true, 'object' => 'saler', 'display_field' => 'name',],
        ],
        'form' => [
            'general_tab' => [
                ['name' => 'total_price', 'type' => 'price_vi', 'class' => 'required', 'label' => 'Doanh số ', 'group_class' => 'col-md-4'],
                ['name' => 'total_price_contract', 'type' => 'price_vi', 'class' => 'required', 'label' => 'Tổng tiền HĐ', 'group_class' => 'col-md-4'],
                ['name' => 'domain', 'type' => 'text', 'label' => 'CRMDV_admin.ten_mien', 'group_class' => 'col-md-4'],
                ['name' => 'service_id', 'type' => 'select2_model', 'class' => ' required', 'label' => 'Gói DV', 'model' => Service::class, 'display_field' => 'name_vi', 'group_class' => 'col-md-3'],
                ['name' => 'registration_date', 'type' => 'date', 'label' => 'Ngày ký HĐ', 'class' => 'required', 'group_class' => 'col-md-3'],
                ['name' => 'contract_time', 'type' => 'number', 'label' => 'Thời gian sử dụng (tháng)', 'class' => 'required', 'group_class' => 'col-md-3'],
                ['name' => 'mst', 'type' => 'text', 'label' => 'Mã số thuế', 'class' => '', 'group_class' => 'col-md-3'],
                ['name' => 'link_hd', 'des' => '<a href="https://youtu.be/sC-oYxe9obQ" target="_blank" >Hướng dẫn</a>', 'type' => 'text', 'label' => 'Link hợp đồng', 'class' => '', 'group_class' => 'col-md-3'],
                ['name' => 'hd_luu_tru', 'des' => '', 'type' => 'select', 'label' => 'HĐ lưu trữ', 'options' => [
                    '' => '',
                    'gửi mail' => 'đã gửi mail',
                    'online' => 'Bản online',
                    'giấy' => 'Bản giấy',
                ], 'class' => '', 'group_class' => 'col-md-3'],
                ['name' => 'bbtl_luu_tru', 'des' => '', 'type' => 'select', 'label' => 'BBTL lưu trữ', 'options' => [
                    '' => '',
                    'online' => 'Bản online',
                    'giấy' => 'Bản giấy',
                    'ko cần' => 'ko cần vì xuất hoá đơn đúng ngày',
                ], 'class' => '', 'group_class' => 'col-md-3'],
                ['name' => 'product_or_service', 'des' => 'Web này trưng bày sản phẩm hoặc dịch vụ gì?', 'type' => 'text', 'label' => 'Sản phẩm / dịch vụ', 'class' => 'required', 'group_class' => 'col-md-3'],


                // ['name' => 'curator_ids', 'type' => 'select2_ajax_model', 'label' => 'Người KH phụ trách', 'model' => Admin::class, 'object' => 'admin', 'display_field' => 'name', 'display_field2' => 'email', 'multiple' => true, 'group_class' => 'col-md-6'],

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
                ['name' => 'marketer_id', 'type' => 'select2_ajax_model', 'label' => 'Marketing', 'model' => Admin::class,
                    'where' => 'status=1', 'object' => 'admin', 'display_field' => 'name', 'display_field2' => 'code', 'group_class' => 'col-md-12'],
                ['name' => 'saler_id', 'type' => 'custom', 'field' => 'CRMDV.form.fields.select_sale', 'label' => 'Sale',
                    'where' => 'status=1', 'model' => Admin::class, 'object' => 'admin', 'display_field' => 'name', 'display_field2' => 'code', 'class' => 'required'],
                ['name' => 'staff_care', 'type' => 'select2_ajax_model', 'label' => 'NV theo dõi', 'model' => Admin::class, 'object' => 'admin', 'display_field' => 'name', 'display_field2' => 'code', 'multiple' => true, 'group_class' => 'col-md-12'],
                ['name' => 'customer_id', 'type' => 'custom', 'type_history' => 'relation_multiple', 'field' => 'CRMDV.form.fields.select_customer',
                    'label' => 'Khách hàng', 'model' => User::class, 'object' => 'user', 'display_field' => 'name', 'display_field2' => 'tel', 'class' => 'required'],
                ['name' => 'customer_legal_id', 'type' => 'custom', 'type_history' => 'relation_multiple', 'field' => 'CRMDV.form.fields.select_customer',
                    'label' => 'Đại diện pháp lý', 'model' => User::class, 'object' => 'user', 'display_field' => 'name', 'display_field2' => 'tel', 'class' => ''],
            ],
            'gia_han_tab' => [
                ['name' => 'auto_extend', 'type' => 'checkbox', 'label' => 'Duy trì bên mình', 'value' => 1, 'group_class' => 'col-md-4'],
                ['name' => 'expiry_date', 'type' => 'date', 'field' => 'CRMDV.form.fields.expiry_date', 'label' => 'Ngày hết hạn', 'class' => '', 'group_class' => 'col-md-4', 'inner' => ''],
                ['name' => 'exp_price', 'type' => 'price_vi', 'class' => ' required', 'label' => 'Giá gia hạn', 'group_class' => 'col-md-4', 'des' => 'Thuê hosting bên mình thì. 1,4tr cho 3G, 1,76tr cho 6G'],
            ],
            'domain_tab' => [
            ],
            'hosting_tab' => [

            ],
            'ldp_tab' => [

            ],
            'wp_tab' => [

            ],
            'service_tab' => [
                ['name' => 'status', 'type' => 'checkbox', 'label' => 'Kích hoạt', 'value' => 1,],
                ['name' => 'note', 'type' => 'textarea', 'label' => 'Ghi chú'],
                ['name' => 'customer_note', 'type' => 'textarea', 'class' => 'required', 'label' => 'Có đky tên miền cho khách không? Nếu có thì: đuôi gì? mấy năm? đã trả tiền chưa?'],
                ['name' => 'web_lock', 'des' => '', 'type' => 'select', 'label' => 'Khoá', 'options' => [
                    -1 => '',
                    0 => "trắng trang",
                    1 => "chuyển sang trang chủ",
                    2 => "chuyển sang trang đăng nhập",
                    3 => "chuyển sang trang báo lỗi /kiem-tra-dang-nhap*/",
                    4 => "Khoá trang /add",
                    5 => "Chậm random 1-6s",
                ], 'class' => '', 'value' => '-1'],
                ['name' => 'web_lock_date', 'type' => 'date', 'label' => 'Ngày bắt đầu khoá'],
            ],
            'account_tab' => [

            ],
        ],
    ];

    protected $filter = [
            'marketer_id' => [
            'label' => 'Nguồn marketing',
            'type' => 'select2_ajax_model',
            'display_field' => 'name',
            'display_field2' => 'code',
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
        'service_id' => [
            'label' => 'Dịch vụ',
            'type' => 'select2_model',
            'display_field' => 'name_vi',
            'model' => Service::class,
            'query_type' => '='
        ],
        /*'total_price' => [
            'label' => 'Tổng tiền',
            'type' => 'number',
            'query_type' => 'like'
        ],*/
        'filter_date' => [
            'label' => 'Lọc theo',
            'type' => 'filter_date',
            'options' => [
                '' => '',
//                'created_at' => 'Ngày tạo',
                'expiry_date' => 'Hết hạn',
                'registration_date' => 'Ngày ký HĐ',
            ],
            'query_type' => 'filter_date'
        ],
        'auto_extend' => [
            'label' => 'Tự động gia hạn',
            'type' => 'select',
            'query_type' => '=',
            'options' => [
                '' => '',
                0 => 'Không kích hoạt',
                1 => 'Kích hoạt',
            ],
        ],
//        'status' => [
//            'label' => 'Trạng thái',
//            'type' => 'select',
//            'query_type' => '=',
//            'options' => [
//                '' => 'Tất cả',
//                0 => 'Không kích hoạt',
//                1 => 'Kích hoạt',
//            ],
//        ],
        'custom' => [
            'label' => 'Lọc theo',
            'type' => 'select',
            'query_type' => 'custom',
            'options' => [
                '' => '',
                'Chưa thanh toán hết' => 'Chưa thanh toán hết',
            ],
        ],
    ];

    protected $quick_search = [
        'label' => 'ID, tên miền, giá, note',
        'fields' => 'id, domain, exp_price, total_price, note, customer_note, mst'
    ];

    public function quickSearch($listItem, $r) {
        if (@$r->quick_search != '') {
            $listItem = $listItem->where(function ($query) use ($r) {
                foreach (explode(',', $this->quick_search['fields']) as $field) {
                    $query->orWhere(trim($field), 'LIKE', '%' . $r->quick_search . '%');    //  truy vấn các tin thuộc các danh mục con của danh mục hiện tại
                }

                //  Tìm kiếm nhanh theo khách hàng
                $customer_ids = \App\Models\User::select('id')->where(function ($query) use ($r) {
                    $query->orWhere('name', 'like', "%".$r->quick_search."%");
                    $query->orWhere('tel', 'like', "%".$r->quick_search."%");
                    $query->orWhere('email', 'like', "%".$r->quick_search."%");
                })->pluck('id')->toArray();
//                dd($customer_ids);

                $query->orWhereIn('customer_id', $customer_ids);
            });

        }

        return $listItem;
    }

    public function appendWhere($query, $request)
    {
        if ($request->filled('domain')) {
            $query->where('domain', 'like', '%' . $request->domain . '%');
        }
        if ($request->filled('custom')) {
            if ($request->custom === 'Chưa thanh toán hết') {
                $query->where(function ($q) {
                    $q->whereNull('total_received')
                        ->orWhereColumn('total_received', '<', 'total_price_contract');
                });
            }
        }

        if ($request->filled('service_name')) {
            $service_ids = Service::where('name_vi', 'like', '%' . $request->service_name . '%')->pluck('id')->toArray();
            if (!empty($service_ids)) $query->whereIn('service_id', $service_ids);
        }

        if ($request->filled('saler_name')) {
            $saler_ids = \App\Models\Admin::where('name', 'like', '%' . $request->saler_name . '%')->pluck('id')->toArray();
            if (!empty($saler_ids)) $query->whereIn('saler_id', $saler_ids);
        }

        if ($request->filled('marketing_name')) {
            $mkt_ids = \App\Models\Admin::where('name', 'like', '%' . $request->marketing_name . '%')->pluck('id')->toArray();
            if (!empty($mkt_ids)) $query->whereIn('marketer_id', $mkt_ids);
        }

        if ($request->filled('customer_name')) {
            $customer_ids = \App\Models\User::where('name', 'like', '%' . $request->customer_name . '%')->pluck('id')->toArray();
            if (!empty($customer_ids)) $query->whereIn('customer_id', $customer_ids);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('auto_extend')) {
            $query->where('auto_extend', $request->auto_extend);
        }

        // LỌC NGÀY – PHIÊN BẢN HOÀN HẢO, AN TOÀN 100%
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $field = 'created_at'; // mặc định

            if ($request->filled('filter_date') && in_array($request->filter_date, ['registration_date', 'expiry_date','created_at'])) {
                $field = $request->filter_date;
            }

            $start = $request->start_date ? $request->start_date . ' 00:00:00' : null;
            $end   = $request->end_date   ? $request->end_date   . ' 23:59:59' : null;

            if ($start && $end) {
                $query->whereBetween($field, [$start, $end]);
            } elseif ($start) {
                $query->where($field, '>=', $start);
            } elseif ($end) {
                $query->where($field, '<=', $end);
            }
        }

        // Lọc ngày hết hạn riêng (nếu có param expiry_from/to)
        if ($request->filled('expiry_from') || $request->filled('expiry_to')) {
            if ($request->filled('expiry_from') && $request->filled('expiry_to')) {
                $query->whereBetween('expiry_date', [
                    $request->expiry_from . ' 00:00:00',
                    $request->expiry_to . ' 23:59:59'
                ]);
            } elseif ($request->filled('expiry_from')) {
                $query->where('expiry_date', '>=', $request->expiry_from);
            } elseif ($request->filled('expiry_to')) {
                $query->where('expiry_date', '<=', $request->expiry_to . ' 23:59:59');
            }
        }

        return $query;
    }
    public function getDataList(Request $request)
    {
        $where = $this->filterSimple($request);
        $query = $this->model->whereRaw($where);

        $query = $this->quickSearch($query, $request);
        if ($this->whereRaw) {
            $query->whereRaw($this->whereRaw);
        }
        $summaryQuery = clone $query;
        $data['record_total']         = $summaryQuery->count();
        $data['doanh_so']             = $summaryQuery->sum('total_price');
        $data['total_received']       = $summaryQuery->sum('total_received');
        $data['total_price_contract'] = $summaryQuery->sum('total_price_contract');

        $query = $this->appendWhere($query, $request);

        // Export thì dùng toàn bộ dữ liệu đã lọc
        if ($request->has('export')) {
            $this->exportExcel($request, $summaryQuery->get());
        }

        // BÂY GIỜ MỚI SORT + PAGINATE CHO DANH SÁCH HIỂN THỊ
        $query = $this->sort($request, $query);

        if ($request->has('limit')) {
            $data['listItem'] = $query->paginate($request->limit);
            $data['limit']    = (int) $request->limit;
        } else {
            $data['listItem'] = $query->paginate($this->limit_default);
            $data['limit']    = $this->limit_default;
        }

        $data['page']       = $request->get('page', 1);
        $data['param_url']  = $request->all();

        // Các data khác
        $data['module']       = $this->module;
        $data['quick_search'] = $this->quick_search;
        $data['filter']       = $this->filter;
        $data['page_title']   = $this->module['label'];
        $data['page_type']    = 'list';

        return $data;
    }



    private function formatDate($date, $format = 'd/m/Y')
    {
        try {
            return (!empty($date)) ? \Carbon\Carbon::parse($date)->format($format) : '';
        } catch (\Exception $e) {
            return '';
        }
    }
    public function getAll1(Request $request){
        $bill = Bill::all();
        $data_mobile = $bill->map(function ($item) {
            return [
                'id'                => $item->id,
                'domain'            => $item->domain ?? '',
//                'service_name'      => optional($item->service)->name_vi ?? '',
//                'saler_name'        => optional($item->saler)->name ?? '',
//                'customer_name'     => optional($item->customer)->name ?? '',
//                'total_price'       => $item->total_price,
//                'total_price_contract' => $item->total_price_contract,
//                'total_received'    => $item->total_received,
//                'chua_thu'          => $item->total_price_contract - $item->total_received,
//                'registration_date' => $this->formatDate($item->registration_date),
//                'expiry_date'       => $this->formatDate($item->expiry_date),
//                'status'            => $item->status == 1,
//                'auto_extend'       => $item->auto_extend??0,
//                'customer_note'     => $item->customer_note ?? '',
//                'note'              => $item->note ?? '',
//                'image'             => $item->image ? url('/filemanager/userfiles/' . $item->image) : '',
            ];
        });

        return response()->json([
            'msg' => 'Lấy danh sách hợp đồng thành công',
            'status'=>true,
            'data' => $data_mobile
        ]);
    }
    public function getAll(Request $request)
    {
        // Lấy dữ liệu từ getDataList (có paginate và tổng tất cả bản ghi)
        $billData = $this->getDataList($request);
        $paginator = $billData['listItem'];   // LengthAwarePaginator
        $items = $paginator->getCollection();

        // Lấy tổng của tất cả bản ghi (không theo trang)
        $tong_doanh_so = $billData['doanh_so'];
        $tong_da_thu = $billData['total_received'];
        $tong = $billData['total_price_contract'];
        $tong_chua_thu = $tong - $tong_da_thu;
      //  return  $items;
        // Map dữ liệu trả về mobile
        $data_mobile = $items->map(function ($item) {
            return [
                'id'                => $item->id,
                'domain'            => $item->domain ?? '',
                'service_name'      => optional($item->service)->name_vi ?? '',
                'saler_name'        => optional($item->saler)->name ?? '',
                'customer_name'     => optional($item->customer)->name ?? '',
                'total_price'       => $item->total_price,
                'total_price_contract' => $item->total_price_contract??0,
                'total_received'    => $item->total_received,
                'chua_thu'          => $item->total_price_contract - $item->total_received??0,
                'registration_date' => $this->formatDate($item->registration_date),
                'expiry_date'       => $this->formatDate($item->expiry_date),
                'status'            => $item->status == 1,
                'auto_extend'       => $item->auto_extend??0,
                'customer_note'     => $item->customer_note ?? '',
                'note'              => $item->note ?? '',
                'tel'             =>  optional($item->customer)->tel ?? '',
            ];
        });

        return response()->json([
            'data' => $data_mobile,
            'paginate' => [
                'current_page' => (int)$paginator->currentPage(),
                'per_page'     => (int)$paginator->perPage(),
                'total'        => (int)$paginator->total(),
            ],
            'summary' => [
                'tong'          => (int)$tong,
                'tong_doanh_so' => (int)$tong_doanh_so,
                'tong_da_thu'   => (int)$tong_da_thu,
                'tong_chua_thu' => $tong_chua_thu,
            ],
            'msg' => 'Lấy danh sách hợp đồng thành công',
        ]);
    }



    public function hopDongMoi4Thang()
    {
        // Lấy 4 tháng gần nhất tính từ hiện tại
        $now = now();
        $months = collect(range(0, 3))->map(function ($i) use ($now) {
            return $now->copy()->subMonths($i)->format('Y-m');
        })->reverse(); // để hiển thị theo thứ tự thời gian tăng dần

        // Lấy dữ liệu từ bảng bills
        $data = Bill::select(
            DB::raw('YEAR(registration_date) as year'),
            DB::raw('MONTH(registration_date) as month'),
            DB::raw('COUNT(id) as so_hop_dong'),
            DB::raw('SUM(total_price) as tong_doanh_so')
        )
            ->whereBetween('registration_date', [
                now()->subMonths(3)->startOfMonth(),
                now()->endOfMonth()
            ])
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Chuyển dữ liệu sang format phù hợp cho frontend
        $result = $months->map(function ($m) use ($data) {
            // Thay cú pháp [$year, $month] = explode('-', $m);
            $parts = explode('-', $m);
            $year = $parts[0];
            $month = $parts[1];

            // Thay arrow function fn() => ... bằng closure thông thường
            $record = $data->first(function ($r) use ($year, $month) {
                return $r->year == $year && $r->month == $month;
            });

            return [
                'thang'         => (int)$month,
                'so_hop_dong'   => (int)(isset($record->so_hop_dong) ? $record->so_hop_dong : 0),
                'tong_doanh_so' => round((isset($record->tong_doanh_so) ? $record->tong_doanh_so : 0) / 1000000, 2),
            ];
        });

        return response()->json([
            'status' => true,
            'msg'    => 'Thống kê hợp đồng ký mới 4 tháng gần nhất',
            'data'   => $result
        ]);
    }



    public function store(Request $request)
    {
        $data = $request->all();
        $bill = new Bill();

        // ====== thong_tin_don_hang ======
        $bill->total_price = $data['thong_tin_don_hang']['doanh_so'] ?? 0;
        $bill->total_price_contract = $data['thong_tin_don_hang']['tong_tien_hop_dong'] ?? 0;
        $bill->service_name2 = $data['thong_tin_don_hang']['goi_dich_vu'] ?? '';
        $bill->registration_date = $data['thong_tin_don_hang']['ngay_ky_hd'] ?? null;
        $bill->contract_time = $data['thong_tin_don_hang']['thoi_gian_su_dung'] ?? null;
        $bill->product_or_service = $data['thong_tin_don_hang']['san_pham_dich_vu'] ?? '';
        $bill->status = ($data['thong_tin_don_hang']['status'] ?? false) ? 1 : 0;
        $bill->domain = $data['thong_tin_don_hang']['domain'] ?? '';
        $bill->note = $data['thong_tin_don_hang']['note'] ?? '';

        // ====== thong_tin_dich_vu ======
        $bill->customer_note = $data['thong_tin_dich_vu']['ghi_chu'] ?? '';
       $web_lock_id= [
            "trắng trang" => 0,
            "chuyển sang trang chủ" => 1,
            "chuyển sang trang đăng nhập" => 2,
            "chuyển sang trang báo lỗi /kiem-tra-dang-nhap*/" => 3,
            "Khoá trang /add" => 4,
            "Chậm random 1-6s" => 5
        ];
        $bill->web_lock = $web_lock_id[$data['thong_tin_dich_vu']['khoa_web']] ?? 0;
        $bill->web_lock_date = $data['thong_tin_dich_vu']['ngay_khoa'] ?? null;

        // ====== quan_ly_hop_dong ======
        $bill->mst = $data['quan_ly_hop_dong']['ma_so_thue'] ?? '';
        $bill->link_hd = $data['quan_ly_hop_dong']['link_hop_dong'] ?? '';
        $bill->hd_luu_tru = $data['quan_ly_hop_dong']['hd_luu_tru'] ?? '';
        $bill->bbtl_luu_tru = $data['quan_ly_hop_dong']['bbtl_luu_tru'] ?? '';

        // ====== thong_tin_gia_han ======
        $bill->auto_extend = ($data['thong_tin_gia_han']['duy_tri_ben_minh'] ?? false) ? 1 : 0;
        $bill->exp_price = $data['thong_tin_gia_han']['price_giahan'] ?? 0;
        $bill->expiry_date = $data['thong_tin_gia_han']['date_giahan'] ?? null;

        // Nhân viên theo dõi
        $nv_care = Admin::where('name', $data['thong_tin_khach']['nhan_vien_theo_doi'] ?? '')->first();
        $bill->staff_care = $nv_care->id ?? null;

// Sale
        $sale = Admin::where('name', $data['thong_tin_khach']['sale'] ?? '')->first();
        $bill->saler_id = $sale->id ?? null;

// Marketing
        $marketing = Admin::where('name', $data['thong_tin_khach']['marketing'] ?? '')->first();
        $bill->marketer_id = $marketing->id ?? null;

// Đại diện pháp lý
        $legal = Admin::where('name', $data['thong_tin_khach']['dai_dien_phap_ly'] ?? '')->first();
        $bill->customer_legal_id = $legal->id ?? null;

        $customer = \App\CRMDV\Models\Lead::where('name', $data['thong_tin_khach']['khach_hang'])->first();
        $bill->customer_id = $customer->id ?? null;
        $bill->save();

        return response()->json([
            'status' => true,
            'msg' => 'Tạo hợp đồng thành công',
            'bill_id' => $bill->id,
        ]);
    }
    public function web_lock()
    {
        $list = [
            "0" => "trắng trang",
            "1" => "chuyển sang trang chủ",
            "2" => "chuyển sang trang đăng nhập",
            "3" => "chuyển sang trang báo lỗi /kiem-tra-dang-nhap*/",
            "4" => "Khoá trang /add",
            "5" => "Chậm random 1-6s"
        ];

        return response()->json([
            'status' => true,
            'msg' => 'Lấy danh sách khoá web thành công (0-5)',
            'data' => $list,
        ]);
    }



    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $bill= Bill::find($id);
        $data = $request->all();
        if ($bill) {
        $bill->total_price = $data['thong_tin_don_hang']['doanh_so'] ?? 0;
        $bill->total_price_contract = $data['thong_tin_don_hang']['tong_tien_hop_dong'] ?? 0;
        $bill->service_name2 = $data['thong_tin_don_hang']['goi_dich_vu'] ?? '';
        $bill->registration_date = $data['thong_tin_don_hang']['ngay_ky_hd'] ?? null;
        $bill->contract_time = $data['thong_tin_don_hang']['thoi_gian_su_dung'] ?? null;
        $bill->product_or_service = $data['thong_tin_don_hang']['san_pham_dich_vu'] ?? '';
        $bill->status = ($data['thong_tin_don_hang']['status'] ?? false) ? 1 : 0;
        $bill->domain = $data['thong_tin_don_hang']['domain'] ?? '';
        $bill->note = $data['thong_tin_don_hang']['note'] ?? '';

        // ====== thong_tin_dich_vu ======
        $bill->customer_note = $data['thong_tin_dich_vu']['ghi_chu'] ?? '';
        $bill->web_lock = $data['thong_tin_dich_vu']['khoa_web'] ?? '';
        $bill->web_lock_date = $data['thong_tin_dich_vu']['ngay_khoa'] ?? null;

        // ====== quan_ly_hop_dong ======
        $bill->mst = $data['quan_ly_hop_dong']['ma_so_thue'] ?? '';
        $bill->link_hd = $data['quan_ly_hop_dong']['link_hop_dong'] ?? '';
        $bill->hd_luu_tru = $data['quan_ly_hop_dong']['hd_luu_tru'] ?? '';
        $bill->bbtl_luu_tru = $data['quan_ly_hop_dong']['bbtl_luu_tru'] ?? '';

        // ====== thong_tin_gia_han ======
        $bill->auto_extend = ($data['thong_tin_gia_han']['duy_tri_ben_minh'] ?? false) ? 1 : 0;
        $bill->exp_price = $data['thong_tin_gia_han']['price_giahan'] ?? 0;
        $bill->expiry_date = $data['thong_tin_gia_han']['date_giahan'] ?? null;

        // Nhân viên theo dõi
        $nv_care = Admin::where('name', $data['thong_tin_khach']['nhan_vien_theo_doi'] ?? '')->first();
        $bill->staff_care = $nv_care->id ?? null;
        $khach_hang = Lead::where('name',$data['thong_tin_khach']['khach_hang'] ?? '')->first();
        $bill->customer_id= $khach_hang->id??null;
// Sale
        $sale = Admin::where('name', $data['thong_tin_khach']['sale'] ?? '')->first();
        $bill->saler_id = $sale->id ?? null;

// Marketing
        $marketing = Admin::where('name', $data['thong_tin_khach']['marketing'] ?? '')->first();
        $bill->marketer_id = $marketing->id ?? null;

// Đại diện pháp lý
        $legal = Admin::where('name', $data['thong_tin_khach']['dai_dien_phap_ly'] ?? '')->first();
        $bill->customer_legal_id = $legal->id ?? null;
        $bill->update();
        return response()->json([
            'msg' => 'Cập nhật hợp đồng thành công',
            'data' => $bill,
        ]);}
        return response()->json([
            'msg' => 'Hợp đồng không tồn tại',
        ],404);
    }

    public function getStaffCareNames($stringIds)
    {
        if (empty($stringIds)) return '';

        // Tách chuỗi |1|2|4 thành array: [1, 2, 4]
        $ids = array_filter(explode('|', $stringIds));

        // Lấy danh sách tên từ bảng admin
        $staffNames = Admin::whereIn('id', $ids)->pluck('name')->toArray();

        // Trả về dạng "Tên1, Tên2, Tên3"
        return implode(', ', $staffNames);
    }

    public function show($id)
    {
        try {
            $bill = Bill::with(['customer', 'customer_legal', 'service'])->findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 404,
                'msg' => 'Hợp đồng không tồn tại',
            ], 404);
        }

        $da_thu = BillReceipts::where('bill_id', $bill->id)->where('status', 1)->sum('price');
        $da_chi = BillReceipts::where('bill_id', $bill->id)->where('status', 2)->sum('price');
        $thu_chi = $da_thu - $da_chi;
        $chua_thanh_toan = max(0, ($bill->total_price ?? 0) - $da_thu);
        $staff_care_names = $this->getStaffCareNames($bill->staff_care);
        $phieu_thu = BillReceipts::where('bill_id', $bill->id)
            ->orderBy('status', 'asc') // 0 trước, 1 sau
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($p) use ($bill) {

                $imageThumb = '';
                if ($p->image) {
                    $dir = trim(pathinfo($p->image, PATHINFO_DIRNAME), '/');
                    $filename = pathinfo($p->image, PATHINFO_FILENAME);
                    $ext = pathinfo($p->image, PATHINFO_EXTENSION);
                    $imageThumb = url("/filemanager/userfiles/_thumbs/{$dir}/{$filename}.{$ext}");
                }

                $accountName = '';
                if (!empty($p->receiving_account)) {
                    $tag = \App\CRMDV\Models\Tag::find($p->receiving_account);
                    $accountName = $tag->name ?? '';
                }

//                return [
//                    'id'=> $p->id,
//                    'ngay' => $p->date ?? '0000-00-00 00:00:00',
//                    'so_tien' => (int)($p->price ?? 0),
//                    'trang_thai' => (int)($p->status ?? 0),
//                    'hop_dong' => 'Số : ' . $p->bill_id . $p->date . $bill->registration_date ?? '',
//                    'ngay_het_han' => $bill->expiry_date
//                        ? Carbon::parse($bill->expiry_date)->format('Y-m-d')
//                        : '',
//                    'gia_gia_han'=> $bill->exp_price ?? '',
//                    'tai_khoan_nguoi_nhan'=> $accountName,
//                    'chi_tiet' =>
//                        'ID : ' . $p->id . '| Số hóa đơn : '. $p->so_hoa_don . ' TK nhận :'. $accountName.'\n Nội dung : '. $p->note. '\n NV : '.(optional($p->admin)->name ?? ''),
//                    'anh_ck' => $imageThumb,
//                ];
                return [
                    'id'=> $p->id,
                    'ngay' => $p->date ?? '0000-00-00 00:00:00',
                    'so_tien' => (int)($p->price ?? 0),
                    'trang_thai' => (int)($p->status ?? 0),
                    'hop_dong' => 'Số : ' . $p->bill_id .'| Ngày ký: ' . $bill->registration_date ?? '',
                    'ngay_het_han' => $bill->expiry_date
                        ? Carbon::parse($bill->expiry_date)->format('Y-m-d')
                        : '',
                    'gia_gia_han'=> $bill->exp_price ?? '',
                    'tai_khoan_nguoi_nhan'=> $accountName,
                    'chi_tiet' =>
                        'ID : ' . $p->id . ' | Số hóa đơn : '. $p->so_hoa_don . ' | TK nhận :'. $accountName.' | Nội dung : '. $p->note. ' | NV : '.(optional($p->admin)->name ?? ''),
                    'anh_ck' => $imageThumb,
                ];
            });
       $web_lock=[ 0 => "trắng trang",
                    1 => "chuyển sang trang chủ",
                    2 => "chuyển sang trang đăng nhập",
                    3 => "chuyển sang trang báo lỗi /kiem-tra-dang-nhap*/",
                    4 => "Khoá trang /add",
                    5 => "Chậm random 1-6s"];
        return response()->json([
            'status' => 'success',
            'msg' => 'Lấy thông tin chi tiết hợp đồng thành công',
            'bill' => [
                'thong_tin_don_hang' => [
                    'doanh_so' => (int)($bill->total_price ?? 0),
                    'tong_tien_hop_dong' => (int)($bill->total_price_contract ?? 0),
                    'goi_dich_vu' => optional($bill->service)->name_vi ?? $bill->service_name2 ?? '',
                    'ngay_ky_hd' => $bill->registration_date ?? '',
                    'thoi_gian_su_dung' => (int)$bill->contract_time??0,
                    'san_pham_dich_vu' => $bill->product_or_service ?? '',
                    'status' => $bill->status == 1,
                    'domain'=> $bill->domain ?? '',
                    'note'=> $bill->note ?? '',
                ],

                'thong_tin_dich_vu'=>[
                    'status'=>$bill->status == 1,
                    'ghi_chu'=>$bill->customer_note ?? "",
                    'note'=> $bill->note ?? '',
                    'khoa_web'=>$web_lock[$bill->web_lock] ?? '',
                    'ngay_khoa'=>$bill->web_lock_date ?? ''
                ],

                'quan_ly_hop_dong' => [
                    'ma_so_thue' => $bill->mst ?? '',
                    'link_hop_dong' => $bill->link_hd ?? '',
                    'hd_luu_tru' => $bill->hd_luu_tru ?? '',
                    'bbtl_luu_tru' => $bill->bbtl_luu_tru ?? '',
                ],

                'thong_tin_khach' => [
                    'marketing' => optional($bill->marketer)->name ?? '',
                    'sale' => optional($bill->saler)->name ?? '',
                    'nhan_vien_theo_doi' => $staff_care_names?? '',
                    'khach_hang' =>$bill->customer ?[
                        'ten' => optional($bill->customer)->name ?? '',
                        'sdt' => optional($bill->customer)->tel ?? '',
                        'email' => optional($bill->customer)->email ?? '',
                    ]:[],
                    'dai_dien_phap_ly' => optional($bill->legal_representative)->name ?? '',
                ],

                'thong_tin_thanh_toan' => [
                    'da_thu' => (int)$da_thu,
                    'chua_thanh_toan' => (int)$chua_thanh_toan,
                    'da_chi' => (int)$da_chi,
                    'thu_chi' => (int)$thu_chi,
                    'phieu_thu' => $phieu_thu ?? [],
                ],

                'thong_tin_gia_han' => [
                    'duy_tri_ben_minh' => $bill->auto_extend == 1,
                    'price_giahan' =>  (int)($bill->exp_price) ?? '',
                    'date_giahan' =>  $bill->expiry_date ?? '',
                ],
            ],
        ]);
    }
    public function createReceipt(Request $request, $id)
    {
        return $this->handleReceipt($request, $id);
    }
    public function updateReceipt(Request $request, $id, $receipt_id)
    {
        $request->merge(['receipt_id' => $receipt_id]);
        return $this->handleReceipt($request, $id);
    }
    private function handleReceipt(Request $request, $id)
    {
        $bill = Bill::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric|min:1000',
            'date' => 'nullable|date',
            'note' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'pay_id' => 'nullable|string|max:255',
            'receipt_id' => 'nullable|integer|exists:bill_receipts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $bank = \DB::table('tags')->where('name', $request->pay_id)->first();
        if (!$bank) {
            return response()->json([
                'status' => false,
                'msg' => 'Tài khoản ngân hàng không tồn tại trong hệ thống',
            ], 400);
        }
        $uploadFile = function($file, $folderName) {
            $fileName = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $folder = date('Y/m/d');
            $destinationPath = base_path("public_html/filemanager/userfiles/_thumbs/$folderName/$folder");
            if (!file_exists($destinationPath)) mkdir($destinationPath, 0755, true);
            $file->move($destinationPath, $fileName);
            return "$folderName/$folder/$fileName";
        };

        $bank_account_id = $bank->id;
        $currenUser = JWTAuth::parseToken();

        // ===== UPDATE RECEIPT =====
        if ($request->filled('receipt_id')) {
            $receipt = BillReceipts::findOrFail($request->receipt_id);
            $receipt->price = $request->price;
            $receipt->date = $request->date ?? $receipt->date;
            $receipt->note = $request->note ?? $receipt->note;
            if ($request->hasFile('image')) {
                $receipt->image = $uploadFile($request->file('image'), 'bill_receipts');
            }
            $receipt->admin_id = $currenUser->id ?? $receipt->admin_id;
            $receipt->receiving_account = $bank_account_id;
            $receipt->save();
            $action = 'Cập nhật phiếu thu thành công';
        }

        // ===== CREATE RECEIPT =====
        else {
            $receipt = new BillReceipts();
            $receipt->bill_id = $bill->id;
            $receipt->price = $request->price;
            $receipt->date = $request->date ?? Carbon::now();
            $receipt->note = $request->note ?? '';

            if ($request->hasFile('image')) {
                $receipt->image = $uploadFile($request->file('image'), 'bill_receipts');
            }

            $receipt->status = 0;
            $receipt->admin_id = $currenUser->id ?? null;

            // ===== THÊM CÁC CỘT MỚI =====
            $receipt->saler_id = $bill->saler_id ?? null;
            $receipt->employees = $request->employees ?? null;
            $receipt->invite_by = $currenUser->id ?? null;
            $receipt->type = $request->type ?? 1;

            $receipt->receiving_account = $bank_account_id;
            $receipt->so_hoa_don = $bill->id;

            $receipt->save();
            $action = 'Thêm phiếu thu thành công';
        }


        $totalReceived = BillReceipts::where('bill_id', $bill->id)
            ->where('status', 1)
            ->sum('price');

        $bill->total_received = $totalReceived;
        $bill->save();

        return response()->json([
            'status' => true,
            'msg' => $action,
            'data' => [
                'bill_id' => $bill->id,
                'receipt' => $receipt,
                'tong_da_thu' => $totalReceived,
                'con_lai' => max(0, ($bill->total_price ?? 0) - $totalReceived),
            ],
        ]);
    }


    public function showOrUpdate(Request $request, $id = null)
    {
        // ====================== CREATE BILL ======================
        if ($request->isMethod('post') && $id === null) {

            $data = $request->all();

            $bill = new Bill();

            // ====== thong_tin_don_hang ======
            $bill->total_price = $data['thong_tin_don_hang']['doanh_so'] ?? 0;
            $bill->total_price_contract = $data['thong_tin_don_hang']['tong_tien_hop_dong'] ?? 0;
            $bill->service_name2 = $data['thong_tin_don_hang']['goi_dich_vu'] ?? '';
            $bill->registration_date = $data['thong_tin_don_hang']['ngay_ky_hd'] ?? null;
            $bill->contract_time = $data['thong_tin_don_hang']['thoi_gian_su_dung'] ?? null;
            $bill->product_or_service = $data['thong_tin_don_hang']['san_pham_dich_vu'] ?? '';
            $bill->status = ($data['thong_tin_don_hang']['status'] ?? false) ? 1 : 0;
            $bill->domain = $data['thong_tin_don_hang']['domain'] ?? '';
            $bill->note = $data['thong_tin_don_hang']['note'] ?? '';

            // ====== thong_tin_dich_vu ======
            $bill->customer_note = $data['thong_tin_dich_vu']['ghi_chu'] ?? '';
            $bill->web_lock = ($data['thong_tin_dich_vu']['khoa_web'] ?? '') == 'Khóa' ? 1 : 0;
            $bill->web_lock_date = $data['thong_tin_dich_vu']['ngay_khoa'] ?? null;

            // ====== quan_ly_hop_dong ======
            $bill->mst = $data['quan_ly_hop_dong']['ma_so_thue'] ?? '';
            $bill->link_hd = $data['quan_ly_hop_dong']['link_hop_dong'] ?? '';
            $bill->hd_luu_tru = $data['quan_ly_hop_dong']['hd_luu_tru'] ?? '';
            $bill->bbtl_luu_tru = $data['quan_ly_hop_dong']['bbtl_luu_tru'] ?? '';

            // ====== thong_tin_gia_han ======
            $bill->auto_extend = ($data['thong_tin_gia_han']['duy_tri_ben_minh'] ?? false) ? 1 : 0;
            $bill->exp_price = $data['thong_tin_gia_han']['price_giahan'] ?? 0;
            $bill->expiry_date = $data['thong_tin_gia_han']['date_giahan'] ?? null;

            // ====== thong_tin_khach ======
            $bill->staff_care = $data['thong_tin_khach']['nhan_vien_theo_doi'] ?? '';
            $bill->saler_id = $data['thong_tin_khach']['sale'] ?? '';
            $bill->marketer_id = $data['thong_tin_khach']['marketing'] ?? '';
            $bill->legal_representative = $data['thong_tin_khach']['dai_dien_phap_ly'] ?? '';

            $bill->save();

            return response()->json([
                'status' => true,
                'msg' => 'Tạo hợp đồng thành công',
                'bill_id' => $bill->id,
            ]);
        }

        // ====================== GET BILL DETAIL ======================
        if ($request->isMethod('get')) {

            try {
                $bill = Bill::with(['customer', 'customer_legal', 'service'])->findOrFail($id);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 404,
                    'msg' => 'Hợp đồng không tồn tại',
                ], 404);
            }

            $da_thu = BillReceipts::where('bill_id', $bill->id)->where('status', 1)->sum('price');
            $da_chi = BillReceipts::where('bill_id', $bill->id)->where('status', 2)->sum('price');
            $thu_chi = $da_thu - $da_chi;
            $chua_thanh_toan = max(0, ($bill->total_price ?? 0) - $da_thu);

            $phieu_thu = BillReceipts::where('bill_id', $bill->id)
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($p) use ($bill) {

                    $imageThumb = '';
                    if ($p->image) {
                        $dir = trim(pathinfo($p->image, PATHINFO_DIRNAME), '/');
                        $filename = pathinfo($p->image, PATHINFO_FILENAME);
                        $ext = pathinfo($p->image, PATHINFO_EXTENSION);
                        $imageThumb = url("/filemanager/userfiles/_thumbs/{$dir}/{$filename}.{$ext}");
                    }

                    $accountName = '';
                    if (!empty($p->receiving_account)) {
                        $tag = \App\CRMDV\Models\Tag::find($p->receiving_account);
                        $accountName = $tag->name ?? '';
                    }

                    return [
                        'ngay' => $p->date ?? '0000-00-00 00:00:00',
                        'so_tien' => (int)($p->price ?? 0),
                        'trang_thai' => (int)($p->status ?? 0),
                        'hop_dong' => 'Số : ' . $p->bill_id . ' | ' .
                            (optional($p->bill)->created_at
                                ? Carbon::parse($p->bill->created_at)->format('Y-m-d')
                                : ''),
                        'ngay_het_han' => $bill->expiry_date
                            ? Carbon::parse($bill->expiry_date)->format('Y-m-d')
                            : '',
                        'gia_gia_han'=> $bill->exp_price ?? '',
                        'tai_khoan_nguoi_nhan'=> $accountName,
                        'chi_tiet' =>
                            'ID: ' . $p->id .
                            ' | Số hóa đơn: ' . $p->so_hoa_don .
                            ' | Nội dung: ' . ($p->note ?? '') .
                            ' | Nhân viên: ' . (optional($p->admin)->name ?? ''),
                        'anh_ck' => $imageThumb,
                    ];
                });

            $khoa = [
                '1'=>'trắng trang',
                '2'=>"chuyển sang trang chủ",
                '3'=>"chuyển sang trang đăng nhập",
                '4'=>"chuyển sang trang báo lỗi /kiem-tra-dang-nhap*/",
                '5'=>"Khoá trang /add",
                '6'=>"Chậm random 1-6s",
            ];

            return response()->json([
                'status' => 'success',
                'msg' => 'Lấy thông tin chi tiết hợp đồng thành công',
                'bill' => [
                    'thong_tin_don_hang' => [
                        'doanh_so' => (int)($bill->total_price ?? 0),
                        'tong_tien_hop_dong' => (int)($bill->total_price_contract ?? 0),
                        'goi_dich_vu' => optional($bill->service)->name_vi ?? $bill->service_name2 ?? '',
                        'ngay_ky_hd' => $bill->registration_date ?? '',
                        'thoi_gian_su_dung' => $bill->contract_time ?? '',
                        'san_pham_dich_vu' => $bill->product_or_service ?? '',
                        'status' => $bill->status == 1,
                        'domain'=> $bill->domain ?? '',
                        'note'=> $bill->note ?? '',
                    ],

                    'thong_tin_dich_vu'=>[
                        'status'=>$bill->status == 1,
                        'ghi_chu'=>$bill->customer_note ?? "",
                        'note'=> $bill->note ?? '',
                        'khoa_web'=>$bill->web_lock==0?'Không khóa':'Khóa',
                        'ngay_khoa'=>$bill->web_lock_date ?? ''
                    ],

                    'quan_ly_hop_dong' => [
                        'ma_so_thue' => $bill->mst ?? '',
                        'link_hop_dong' => $bill->link_hd ?? '',
                        'hd_luu_tru' => $bill->hd_luu_tru ?? '',
                        'bbtl_luu_tru' => $bill->bbtl_luu_tru ?? '',
                    ],

                    'thong_tin_khach' => [
                        'marketing' => $bill->marketer_id ?? '',
                        'sale' => $bill->saler_id ?? '',
                        'nhan_vien_theo_doi' => $bill->staff_care ?? '',
                        'khach_hang' => $bill->customer ? [
                            'ten' => $bill->customer->name ?? '',
                            'sdt' => $bill->customer->tel ?? '',
                            'email' => $bill->customer->email ?? '',
                        ] : null,
                        'dai_dien_phap_ly' => $bill->legal_representative ?? '',
                    ],

                    'thong_tin_thanh_toan' => [
                        'da_thu' => (int)$da_thu,
                        'chua_thanh_toan' => (int)$chua_thanh_toan,
                        'da_chi' => (int)$da_chi,
                        'thu_chi' => (int)$thu_chi,
                        'phieu_thu' => $phieu_thu ?? [],
                    ],

                    'thong_tin_gia_han' => [
                        'duy_tri_ben_minh' => $bill->auto_extend == 1,
                        'price_giahan' =>  (int)($bill->exp_price) ?? '',
                        'date_giahan' =>  $bill->expiry_date ?? '',
                    ],
                ],
            ]);
        }

        // ====================== UPDATE / CREATE RECEIPT ======================
        if ($request->isMethod('post') || $request->isMethod('put')) {

            $bill = Bill::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'price' => 'required|numeric|min:1000',
                'date' => 'nullable|date',
                'note' => 'nullable|string',
                'image' => 'nullable|string',
                'pay_id' => 'nullable|string|max:255',
                'receipt_id' => 'nullable|integer|exists:bill_receipts,id',
            ]);

            $bank = \DB::table('tags')->where('name', $request->pay_id)->first();
            if (!$bank) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Tài khoản ngân hàng không tồn tại trong hệ thống',
                ], 400);
            }

            $bank_account_id = $bank->id;
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $accountTag = Tag::find($request->pay_id);
            $accountName = $accountTag->name ?? '';
            $currenUser = JWTAuth::parseToken();

            // ================= UPDATE RECEIPT =================
            if ($request->filled('receipt_id')) {
                $receipt = BillReceipts::findOrFail($request->receipt_id);
                $receipt->price = $request->price;
                $receipt->date = $request->date ?? $receipt->date;
                $receipt->note = $request->note ?? $receipt->note;
                $receipt->image = $request->image ?? $receipt->image;
                $receipt->admin_id = $currenUser->id ?? $receipt->admin_id;
                $receipt->receiving_account = $bank_account_id;
                $receipt->save();
                $action = 'Cập nhật phiếu thu thành công';
            }
            else {
                // ================= CREATE RECEIPT =================
                $receipt = new BillReceipts();
                $receipt->bill_id = $bill->id;
                $receipt->price = $request->price;
                $receipt->date = $request->date ?? Carbon::now();
                $receipt->note = $request->note ?? '';
                $receipt->image = $request->image ?? '';
                $receipt->status = 1;
                $receipt->admin_id = $currenUser->id ?? '';
                $receipt->receiving_account = $bank_account_id;
                $receipt->so_hoa_don = $bill->id;
                $receipt->save();
                $action = 'Thêm phiếu thu thành công';
            }

            $totalReceived = BillReceipts::where('bill_id', $bill->id)
                ->where('status', 1)
                ->sum('price');

            $bill->total_received = $totalReceived;
            $bill->save();

            return response()->json([
                'status' => true,
                'msg' => $action,
                'data' => [
                    'bill_id' => $bill->id,
                    'receipt' => $receipt,
                    'tai_khoan' => $accountName,
                    'tong_da_thu' => $totalReceived,
                    'con_lai' => max(0, ($bill->total_price ?? 0) - $totalReceived),
                ],
            ]);
        }

        return response()->json(['status' => false, 'msg' => 'Phương thức không hợp lệ'], 405);
    }




    public function add(Request $request)
    {
        DB::beginTransaction();

        try {
            $bill = new Bill();

            // Thông tin đơn hàng
            $bill->service_id = $request->input('service_id');
            $bill->total_price = $request->input('total_price');
            $bill->total_price_contract = $request->input('total_price_contract');
            $bill->registration_date = $request->input('registration_date');
            $bill->contract_time = $request->input('contract_time');
            $bill->product_or_service = $request->input('product_note');
            // Quản lý hợp đồng
            $bill->mst = $request->input('mst');
            $bill->link_hd = $request->input('link_hd');
            $bill->hd_luu_tru = $request->input('hd_luu_tru');
            $bill->bbtl_luu_tru = $request->input('bbtl_luu_tru');
            // Thông tin khách
            $bill->marketer_id = $request->input('marketer_id');
            // Saler
            $saler = Admin::where('name', $request->input('sale_name'))->first();
            $bill->saler_id = $saler ? $saler->id : '';

            if ($request->staff_care) {
                $staffNames = array_map('trim', explode(',', $request->staff_care));
                $staffIds = array_unique(\App\Models\Admin::whereIn('name', $staffNames)->pluck('id')->toArray());
                $bill->staff_care = !empty($staffIds) ? '|' . implode('|', $staffIds) . '|' : '';
            }

            $bill->customer_id = $request->input('customer_id');
            $bill->customer_legal_id = $request->input('legal_representative_id');
            // Thông tin dịch vụ
            $bill->invite_more_services = $request->input('service_forever', 0);
            $bill->customer_note = $request->input('customer_note');
            $bill->web_lock = $request->input('lock_service', 0);
            $bill->web_lock_date = $request->input('lock_start_date');
            // Thông tin gia hạn
            $bill->expiry_date = $request->input('expiry_date');
            $bill->exp_price = $request->input('renew_price');

            $bill->save();

            // 🧩 FIX: chỉ xử lý khi payments là mảng
            if (is_array($request->input('payments'))) {
                foreach ($request->input('payments') as $p) {
                    BillReceipts::create([
                        'bill_id' => $bill->id,
                        'price' => $p['price'] ?? 0,
                        'date' => $p['date'] ?? now(),
                        'status' => $p['status'] ?? 1,
                        'note' => $p['note'] ?? '',
                        'admin_id' => $p['admin_id'] ?? '',
                        'image' => $p['image'] ?? '',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'msg' => 'Thêm hợp đồng thành công',
                'data' => $bill
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }



}

    public function goiDichVu(){
        $goidichvu = GoiDichVu::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'name_vi' => $item->name_vi,
            ];
        });
        return response()->json([
            'status' => 'success',
            'msg' => 'Lấy gói dịch vụ thành công',
            'data' => $goidichvu
        ]);
    }
}