<?php

namespace App\CRMDV\Controllers\Api;

use App\CRMDV\Controllers\Helpers\CRMDVHelper;
use App\Http\Helpers\CommonHelper;
use App\Models\RoleAdmin;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\EworkingCompany\Models\Company;
use App\CRMDV\Models\Service;
use App\CRMDV\Models\ServiceHistory;
use Validator;

use App\CRMDV\Models\Lead;
use App\Models\Admin;
use App\Library\JWT\Facades\JWTAuth;

use Illuminate\Support\Facades\Auth;
use App\CRMDV\Models\Bill;
use App\Http\Controllers\Admin\CURDBaseController;
use DB;
use Carbon\Carbon;
class LeadController extends CURDBaseController
{
    protected $module = [
        'code' => 'lead',
        'table_name' => 'leads',
        'label' => 'Đầu mối',
        'modal' => '\App\CRMDV\Models\Lead',
        'list' => [
            ['name' => 'name', 'type' => 'custom', 'td' => 'CRMDV.lead.list.name', 'label' => 'Tên', 'sort' => true],
            ['name' => 'product', 'type' => 'text', 'label' => 'SP', 'sort' => true],
            ['name' => 'project', 'type' => 'text', 'label' => 'Dự án', 'sort' => true],
            ['name' => 'rate', 'type' => 'custom', 'td' => 'CRMDV.lead.list.rate', 'label' => 'Đánh giá', 'sort' => true],
            ['name' => 'tel', 'type' => 'custom', 'td' => 'CRMDV.lead.list.tel', 'label' => 'SĐT', 'sort' => true],
            ['name' => 'service', 'type' => 'select', 'options' => [
                '' => '',
                'landingpage' => 'landingpage',
                'wordpress' => 'wordpress',
                'laravel' => 'laravel',
                'web khác' => 'web khác',
                'app' => 'app',
                'marketing tổng thể' => 'Marketing tổng thể',
                'ads' => 'ads',
                'seo' => 'seo',
                'content' => 'content',
                'logo' => 'logo',
                'banner' => 'banner',
                'design khác' => 'design khác',
                'game' => 'game',
            ], 'label' => 'Dịch vụ', 'sort' => true],

            ['name' => 'dating', 'type' => 'custom', 'td' => 'CRMDV.lead.list.dating', 'label' => 'Hẹn ngày', 'sort' => true],
            ['name' => 'contacted_log_last', 'type' => 'custom', 'td' => 'CRMDV.lead.list.contacted_log_last', 'label' => 'TT lần cuối', 'sort' => true],
            ['name' => 'field_1', 'type' => 'text', 'label' => 'field_1', 'sort' => true],
            ['name' => 'field_2', 'type' => 'text', 'label' => 'field_2', 'sort' => true],
            ['name' => 'field_3', 'type' => 'text', 'label' => 'field_3', 'sort' => true],
            ['name' => 'field_4', 'type' => 'text', 'label' => 'field_4', 'sort' => true],
            ['name' => 'field_5', 'type' => 'text', 'label' => 'field_5', 'sort' => true],
            ['name' => 'reason_refusal', 'type' => 'text', 'label' => 'Lý do từ chối', 'sort' => true],
            ['name' => 'advise_suggest', 'type' => 'text', 'label' => 'Hướng xử lý', 'sort' => true],
            ['name' => 'status', 'type' => 'text', 'label' => 'Trạng thái', 'sort' => true],
            ['name' => 'image', 'type' => 'image', 'label' => 'leads.image'],
        ],
        'form' => [
            'general_tab' => [
                ['name' => 'name', 'type' => 'text', 'class' => 'required', 'label' => 'Tên', 'group_class' => 'col-md-4'],
                ['name' => 'tel', 'type' => 'custom', 'field' => 'CRMDV.lead.form.tel', 'class' => 'required', 'label' => 'SĐT', 'group_class' => 'col-md-4'],
                ['name' => 'email', 'type' => 'text', 'label' => 'Email', 'group_class' => 'col-md-4'],
//                ['name' => 'rate', 'type' => 'select', 'options' => [
//                    '' => '',
//                    'Không liên lạc được' => 'Không liên lạc được',
//                    'Không có nhu cầu' => 'Không có nhu cầu',
//                    'Đang tìm hiểu' => 'Đang tìm hiểu',
//                    'Care dài' => 'Care dài',
//                    'Quan tâm cao' => 'Quan tâm cao',
//                    'Cơ hội' => 'Cơ hội',
////                    'Đã ký HĐ' => 'Đã ký HĐ',
//                ], 'label' => 'Đánh giá', 'group_class' => 'col-md-3'],
                ['name' => 'rate', 'type' => 'select2_model', 'label' => 'Tình trạng', 'model' => Tag::class, 'group_class' => 'col-md-4',
                    'display_field' => 'name', 'where' => 'type="lead_rate"', 'object' => 'tag', 'orderByRaw' => 'order_no desc',],
                ['name' => 'service', 'type' => 'select2', 'options' => [
                    '' => '',
                    'landingpage' => 'landingpage',
                    'wordpress' => 'wordpress',
                    'laravel' => 'laravel',
                    'web khác' => 'web khác',
                    'app' => 'app',
                    'marketing tổng thể' => 'Marketing tổng thể',
                    'ads' => 'ads',
                    'seo' => 'seo',
                    'content' => 'content',
                    'logo' => 'logo',
                    'banner' => 'banner',
                    'design khác' => 'design khác',
                    'game' => 'game',
                ], 'label' => 'Dịch vụ', 'multiple' => true, 'group_class' => 'col-md-4'],
                ['name' => 'project', 'type' => 'text', 'label' => 'Tên dự án', 'group_class' => 'col-md-4'],
                ['name' => 'status', 'type' => 'select', 'options' => [
                    'Thả nổi' => 'Thả nổi',
                    'Đang chăm sóc' => 'Đang chăm sóc',
                    // 'Tạm dừng' => 'Tạm dừng',
//                    'Đã ký HĐ' => 'Đã ký HĐ',
                ], 'label' => 'Trạng thái', 'group_class' => 'col-md-4', 'value' => 'Đang chăm sóc'],
                ['name' => 'product', 'type' => 'text', 'label' => 'Sản phẩm/dịch vụ của khách', 'group_class' => 'col-md-4'],
                ['name' => 'source', 'type' => 'select2_model', 'model' => Tag::class, 'label' => 'Nguồn khách', 'group_class' => 'col-md-4', 'display_field' => 'name', 'where' => 'type="lead_rate"', 'object' => 'tag', 'orderByRaw' => 'order_no desc'],

            ],
            'tab_2' => [
                ['name' => 'profile', 'type' => 'textarea', 'label' => 'Chân dung KH', 'group_class' => 'col-md-12', 'inner' => 'rows=5'],
                ['name' => 'need', 'type' => 'textarea', 'label' => 'Nhu cầu & khó khăn', 'group_class' => 'col-md-12', 'inner' => 'rows=5'],
                ['name' => 'reason_refusal', 'type' => 'textarea', 'label' => 'Lý do từ chối', 'group_class' => 'col-md-12', 'inner' => 'rows=5'],
                ['name' => 'advise_suggest', 'type' => 'textarea', 'label' => 'Hướng xử lý', 'group_class' => 'col-md-12', 'inner' => 'rows=5'],

            ],
            'tab_3' => [
                // ['name' => 'terms', 'type' => 'textarea', 'label' => 'Thương hiệu sở hữu', 'group_class' => 'col-md-12', 'inner' => 'rows=3 placeholder="Lấy thương hiệu cá nhân sale"'],
                // ['name' => 'discount', 'type' => 'text', 'label' => '% tiền dự án trả lại cho hệ thống', 'group_class' => 'col-md-12', 'inner' => 'rows=3 placeholder="Mặc định 15%"', 'des' => 'Bạn tự báo giá chênh lên số % này để trả tiền giới thiệu khách từ hệ thống'],
            ],
            'extend_tab' => [],
        ]
    ];
    protected $quick_search = [
        'label' => 'ID, tên, sđt, đánh giá, mô tả',
        'fields' => 'id, name, tel, rate, profile, need, product'
    ];
    protected $filter = [
        'name' => [
            'label'      => 'Tên',
            'type'       => 'text',
            'query_type' => 'like',
            'field'      => 'name'
        ],

        'tel' => [
            'label'      => 'Số điện thoại',
            'type'       => 'text',
            'query_type' => 'custom',           // ĐỔI THÀNH custom
            'field'      => 'tel'               // vẫn giữ để frontend gửi đúng tên param
        ],

        'time' => [
            'label' => 'Khoảng thời gian',
            'type'  => 'date_range',
            'field' => 'created_at'
        ],

        'saler_ids' => [
            'name'           => 'saler_ids',
            'label'          => 'Sale',
            'type'           => 'select2_ajax_model',
            'field'          => 'CoreERP.list.filter.select_kinh_doanh',
            'display_field'  => 'name',
            'display_field2' => 'code',
            'model'          => \App\Models\Admin::class,
            'object'         => 'admin',
            'where'          => 'status = 1',
            'query_type'     => 'custom'
        ],

        'marketer_ids' => [
            'label'          => 'Nguồn marketing',
            'type'           => 'select2_ajax_model',
            'display_field'  => 'name',
            'display_field2' => 'code',
            'model'          => \App\Models\Admin::class,
            'object'         => 'admin',
            'query_type'     => 'custom'
        ],

        'source' => [
            'label'      => 'Nguồn khách',
            'type'       => 'text',
            'query_type' => 'like'
        ],

        'tinh' => [
            'label'      => 'Tỉnh / thành',
            'type'       => 'text',
            'query_type' => 'like'
        ],

        'service' => [
            'label' => 'Dịch vụ',
            'type'  => 'select',
            'options' => [
                ''                  => 'Tất cả',
                'landingpage'       => 'landingpage',
                'wordpress'         => 'wordpress',
                'laravel'           => 'laravel',
                'web khác'          => 'web khác',
                'app'               => 'app',
                'marketing tổng thể'=> 'Marketing tổng thể',
                'ads'               => 'ads',
                'seo'               => 'seo',
                'content'           => 'content',
                'logo'              => 'logo',
                'banner'            => 'banner',
                'design khác'       => 'design khác',
                'game'              => 'game',
            ],
            'query_type' => 'like'
        ],

        'status' => [
            'label' => 'Trạng thái',
            'type'  => 'select',
            'options' => [
                ''             => 'Tất cả',
                'Đang chăm sóc'=> 'Đang chăm sóc',
                'Thả nổi'      => 'Thả nổi',
                // 'Tạm dừng'     => 'Tạm dừng',
                // 'Đã ký HĐ'     => 'Đã ký HĐ',
            ],
            'query_type' => '='
        ],

        'rate' => [
            'label' => 'Đánh giá',
            'type'  => 'select',
            'options' => [
                ''                     => 'Tất cả',
                'Chưa đánh giá'        => 'Chưa đánh giá',
                'Không liên lạc được'  => 'Không liên lạc được',
                'Không có nhu cầu'     => 'Không có nhu cầu',
                'Đang tìm hiểu / Care dài' => 'Đang tìm hiểu / Care dài',
                'Quan tâm cao'         => 'Quan tâm cao',
                'Đã từng quan tâm'     => 'Đã từng quan tâm',
            ],
            'query_type' => 'custom'
        ],

        'sale_status' => [
            'label' => 'Tình trạng sale',
            'type'  => 'select',
            'options' => [
                ''              => 'Tất cả',
                'Chưa có sale'  => 'Chưa có sale',
                'Đã có sale'    => 'Đã có sale',
            ],
            'query_type' => 'custom'
        ],

        'lead_status' => [
            'label' => 'Sắp xếp',
            'type'  => 'select',
            'options' => [
                ''                    => 'Không',
                'Ngày TT: Mới -> cũ'  => 'Ngày TT: Mới -> cũ',
                'Ngày TT: Cũ -> mới'  => 'Ngày TT: Cũ -> mới',
                'Ngày tạo: Mới -> cũ' => 'Ngày tạo: Mới -> cũ',
                'Ngày nhận: Mới -> cũ'=> 'Ngày nhận: Mới -> cũ',
                'Đến ngày TT'         => 'Đến ngày TT',
            ],
            'query_type' => 'custom'
        ],

        'filter_date' => [
            'label' => 'Lọc theo',
            'type'  => 'filter_date',
            'options' => [
                ''             => '',
                'created_at'   => 'Ngày tạo',
                'received_date'=> 'Ngày nhận',
                'dating'       => 'Ngày hẹn',
            ],
            'query_type' => 'filter_date'
        ],
    ];
    public function serviceType(Request $request)
    {
        $types = [
            'landingpage',
            'wordpress',
            'laravel',
            'web khác',
            'app',
            'marketing tổng thể',
            'ads',
            'seo',
            'content',
            'logo',
            'banner',
            'design khác',
            'game',
        ];

        // Lọc theo query ?name=
        if ($request->has('name') && !empty($request->name)) {
            $name = strtolower($request->name);
            $types = array_filter($types, function ($type) use ($name) {
                return strpos(strtolower($type), $name) !== false;
            });
            // array_filter giữ key, nên reset lại index nếu muốn
            $types = array_values($types);
        }

        return response()->json([
            'status' => true,
            'msg' => 'Danh sách loại dịch vụ',
            'data' => $types
        ]);
    }

    public function sort($request, $model)
    {
        if (@$request->lead_status != null) {
            if ($request->lead_status == 'Ngày TT: Cũ -> mới') {
                $model = $model->orderBy('contacted_log_last', 'asc');
            } elseif ($request->lead_status == 'Ngày TT: Mới -> cũ') {
                $model = $model->orderBy('contacted_log_last', 'desc');
            } elseif ($request->lead_status == 'Ngày tạo: Mới -> cũ') {
                $model = $model->orderBy('id', 'desc');
            } elseif ($request->lead_status == 'Ngày nhận: Mới -> cũ') {
                $model = $model->orderBy('received_date', 'desc');
            } elseif ($request->lead_status == 'Đến ngày TT') {
                $model = $model->orderBy('dating', 'asc');
//                ->where('dating', '<=', date('Y-m-d 23:59:59'));
            }
        }
        if ($request->sorts != null) {
            foreach ($request->sorts as $sort) {
                if ($sort != null) {
                    $sort_data = explode('|', $sort);
                    $model = $model->orderBy($sort_data[0], $sort_data[1]);
                }
            }
        } else {
            $model = $model->orderByRaw($this->orderByRaw);
        }
        return $model;
    }
    public function quickSearch($listItem, $r)
    {
        if (empty($r->quick_search)) {
            return $listItem;
        }

        $keyword = trim($r->quick_search);

        return $listItem->where(function ($query) use ($keyword) {

            // search name luôn có
            $query->orWhere('name', 'LIKE', "%{$keyword}%");

            // search các field cấu hình
            foreach (explode(',', $this->quick_search['fields']) as $field) {
                $field = trim($field);
                if ($field !== 'name') {
                    $query->orWhere($field, 'LIKE', "%{$keyword}%");
                }
            }

            // search tel chỉ khi có số
            if (preg_match('/\d/', $keyword)) {
                $tel = preg_replace('/\D+/', '', $keyword);
                $query->orWhere('tel', 'LIKE', "%{$tel}%");
            }
        });
    }

    public function appendWhere($query, $request)
    {
        if ($request->filled('tel')) {
            $cleanTel = preg_replace('/\D+/', '', $request->tel); // chỉ giữ số
            $query->whereRaw("REPLACE(REPLACE(REPLACE(tel,' ',''),'.',''),'-','') LIKE ?", ['%'.$cleanTel.'%']);
            return $query;
        }
        $filterDate = $request->filter_date; // created_at | received_date | dating
        $fromDate   = $request->from_date;
        $toDate     = $request->to_date;

        $allowedFields = ['created_at', 'received_date', 'dating'];

        if (
            $filterDate &&
            in_array($filterDate, $allowedFields) &&
            $fromDate &&
            $toDate
        ) {
            $query->whereBetween($filterDate, [
                $fromDate . ' 00:00:00',
                $toDate   . ' 23:59:59',
            ]);
        }


// Lọc theo ngày nhận riêng lẻ
        if ($request->filled('regis_date')) {
            $query->whereDate('registration_date', $request->regis_date);
        }

        // 1. Lọc theo tên
        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . trim($request->name) . '%');
        }


        // 2. Lọc theo số điện thoại


        // 3. Lọc theo khoảng thời gian tạo
        if ($request->filled('time')) {
            $parts = explode(' - ', $request->time);
            if (count($parts) === 2) {
                $from = trim($parts[0]) . ' 00:00:00';
                $to   = trim($parts[1]) . ' 23:59:59';
                $query->whereBetween('created_at', [$from, $to]);
            }
        }

        // 4. Trang khách thả nổi
        if ($request->status === 'Thả nổi') {
            $query->where(function ($q) {
                $q->whereIn('status', ['Thả nổi', ''])
                    ->orWhereNull('status');
            });
            return $query;
        }

        // 5. Lọc đánh giá (rate) – thay match bằng if-else
        if ($request->filled('rate')) {
            $rate = $request->rate;

            if ($rate === 'Đang tìm hiểu / Care dài') {
                $query->whereIn('rate', ['Đang tìm hiểu', 'Care dài']);
            } elseif ($rate === 'Chưa đánh giá') {
                $query->where(function ($q) {
                    $q->whereNull('rate')->orWhere('rate', '');
                });
            } elseif ($rate === 'Quan tâm cao') {
                $query->whereIn('rate', ['Quan tâm cao', 'Cơ hội']);
            } else {
                $query->where('rate', $rate);
            }
        }

        // 6. Lọc marketer theo tên
        if ($request->filled('marketer_name')) {
            $ids = Admin::where('name', 'like', "%{$request->marketer_name}%")->pluck('id')->toArray();
            $this->wherePipeIds($query, 'marketer_ids', $ids);
        }

        // 7. Lọc sale theo tên
        if ($request->filled('saler_name')) {
            $ids = Admin::where('name', 'like', "%{$request->saler_name}%")->pluck('id')->toArray();
            $this->wherePipeIds($query, 'saler_ids', $ids);
        }

        // 8. Lọc có sale hay chưa
        if ($request->filled('sale_status')) {
            if ($request->sale_status === 'Chưa có sale') {
                $query->where(function ($q) {
                    $q->whereNull('saler_ids')
                        ->orWhere('saler_ids', '')
                        ->orWhere('saler_ids', '|')
                        ->orWhere('saler_ids', '||');
                });
            } elseif ($request->sale_status === 'Đã có sale') {
                $query->whereNotNull('saler_ids')
                    ->where('saler_ids', '!=', '')
                    ->where('saler_ids', '!=', '|')
                    ->where('saler_ids', '!=', '||');
            }
        }

        // 9. Xử lý các trang đặc biệt
        $url = $request->url();

        if (str_contains($url, '/tha-noi')) {
            $query->where(function ($q) {
                $q->whereIn('status', 'Thả nổi')
                    ->orWhere('status', '')
                    ->orWhereNull('status');
            });

        } elseif (str_contains($url, '/telesale')) {
            $query->whereNotNull('telesale_id');

        } elseif (str_contains($url, '/quan-tam-moi')) {
            if (now()->diffInDays(Auth::guard('admin')->user()->created_at) < 14) {
                CommonHelper::one_time_message('error', 'Vào làm 2 tuần mới được nhận nguồn telesale');
                return $query->whereRaw('1 = 0');
            }

            $query->where(function ($q) {
                $q->whereIn('status', ['Thả nổi', ''])
                    ->orWhereNull('status');
            })
                ->whereIn('rate', ['Đang tìm hiểu', 'Care dài', 'Quan tâm cao', 'Cơ hội'])
                ->whereNotNull('telesale_id')
                ->orderBy('contacted_log_last', 'desc');

        } else {
            // Mặc định: chỉ hiển thị lead đang chăm sóc
            if ($request->filled('search') && blank($request->status)) {
                $query->where('status', '!=', 'Thả nổi');
            }

            $query->where(function ($q) {
                $q->whereIn('status', ['Đang chăm sóc', 'Tạm dừng', 'Đã ký HĐ', ''])
                    ->orWhereNull('status');
            });
        }

        return $query;
    }
    private function wherePipeIds($query, $column, array $ids)
    {
        if (empty($ids)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function ($q) use ($column, $ids) {
            foreach ($ids as $id) {
                $q->orWhere($column, 'like', "%|{$id}|%");
            }
        });
    }
    public function thongKe($data, $listItem, $request) {
        return $data;
    }
    public function getDataList(Request $request) {
        //  Filter
        $where = $this->filterSimple($request);
        $listItem = $this->model->whereRaw($where);

        // FIX: nếu có quick_search → CHỈ search, KHÔNG filter mặc định
        if ($request->filled('quick_search')) {
            $listItem = $this->quickSearch($listItem, $request);
        } else {
            $listItem = $this->appendWhere($listItem, $request);
        }

        if ($this->whereRaw) {
            $listItem = $listItem->whereRaw($this->whereRaw);
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
    public function getDataListNoPaginate(Request $request)
    {
        $where = $this->filterSimple($request);
        $query = $this->model->whereRaw($where);
        $query = $this->quickSearch($query, $request);
        if ($this->whereRaw) {
            $query = $query->whereRaw($this->whereRaw);
        }
        $query = $this->appendWhere($query, $request);
        $query = $this->sort($request, $query);
        $items = $query->get();
        $data['record_total'] = $items->count();
        $data = $this->thongKe($data, $query, $request); // vẫn dùng query gốc để thống kê chính xác

        // 5. Gắn thêm thông tin chung
        $data['listItem']     = $items;
        $data['param_url']    = $request->all();
        $data['module']       = $this->module;
        $data['quick_search'] = $this->quick_search;
        $data['filter']       = $this->filter;
        $data['page_title']   = $this->module['label'];
        $data['page_type']    = 'list';

        return $data;
    }
    public function getAll(Request $request)
    {
        $dataList = $this->getDataList($request);
        $paginated = $dataList['listItem'];
        // map dữ liệu cho mobile
        $data_mobile = $paginated->getCollection()->map(function ($item) {
            // xử lý sale_name
            $saleNames = '';
            if (!empty($item->saler_ids)) {
                $saleIds = array_filter(array_map('trim', explode('|', $item->saler_ids)));
                if (!empty($saleIds)) {
                    $saleNames = Admin::whereIn('id', $saleIds)
                        ->pluck('name')
                        ->filter()
                        ->implode(',');
                }
            }
            return [
                'id'        => $item->id,
                'name'      => $item->name ?? '',
                'tel'       => $item->tel ?? '',
                'sale_name' => $saleNames ?: '',
                'project'   => $item->project    ?? '',
                'rate' => $item->rate ?? '',
                'contacted_log_last' => $item->contacted_log_last
                    ? Carbon::parse($item->contacted_log_last)->format('d/m')
                    : '',
                'dating' => $item->dating
                    ? Carbon::parse($item->dating)->format('Y-m-d') // yyyy-MM-dd
                    : '',


            ];
        })->toArray();

        return response()->json([
            'status'   => true,
            'msg'      => 'Lấy danh sách khách hàng thành công',
            'data'     => $data_mobile,
            'paginate' => [
                'current_page' => $paginated->currentPage(), // đúng page request
                'per_page'     => $paginated->perPage(),     // đúng limit request
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function getBySaleName(Request $request)
    {
        $saleName = $request->query('name');
        if (!$saleName) {
            return response()->json([
                'success' => false,
                'msg' => 'Vui lòng nhập tên sale'
            ], 400);
        }


        $sales = \App\Models\Admin::where('name', 'like', '%' . $saleName . '%')->get();

        if ($sales->isEmpty()) {
            return response()->json([
                'success' => false,
                'msg' => 'Không tìm thấy sale có tên: ' . $saleName
            ], 404);
        }

        $saleIds = $sales->pluck('id')->toArray();

        $leads = Lead::where(function ($query) use ($saleIds) {
            foreach ($saleIds as $id) {
                $pattern = '(^|\\|)' . $id . '(\\||$)'; // match đầu, giữa, cuối
                $query->orWhereRaw("saler_ids REGEXP ?", [$pattern]);
            }
        })->get();

        if ($leads->isEmpty()) {
            return response()->json([
                'success' => false,
                'msg' => 'Không tìm thấy khách hàng cho sale có tên: ' . $saleName
            ], 404);
        }

        $data = $leads->map(function ($item) {
            return [
                'id'    => $item->id,
                'name'  => $item->name ?? '',
                'tel'   => $item->tel ?? '',
                'image'     => $item->image ? url('/filemanager/userfiles/'.$item->image) : '',
                'project' => $item->project ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'msg' => 'Danh sách khách hàng của các sale trùng tên: ' . $saleName,
            'data' => $data
        ]);
    }


    public function show($id)
    {
        try {
            // Lấy thông tin lead
            $lead = DB::table('leads')
                ->select([
                    'id',
                    'name',
                    'tel',
                    'image',
                    'email',
                    'marketer_ids',
                    'project',
                    'saler_ids',
                    'profile',
                    'need',
                    'terms',
                    'created_at',
                    'updated_at'
                ])
                ->where('id', $id)
                ->first();

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Không tìm thấy khách hàng'
                ], 404);
            }

            // Lấy danh sách sale_name
            $saleNames = '';
            if (!empty($lead->saler_ids)) {
                $saleIds = array_filter(array_map('trim', explode('|', $lead->saler_ids)));
                if (!empty($saleIds)) {
                    $saleNames = DB::table('admin')
                        ->whereIn('id', $saleIds)
                        ->pluck('name')
                        ->filter()
                        ->implode(',');
                }
            }
            $marketingNames = '';
            if (!empty($lead->marketer_ids)) {
                $marketerIds = array_filter(array_map('trim', explode('|', $lead->marketer_ids)));
                if (!empty($marketerIds)) {
                    $marketingNames = DB::table('admin')
                        ->whereIn('id', $marketerIds)
                        ->pluck('name')
                        ->filter()
                        ->implode(',');
                }
            }
            // Format dữ liệu trả về
            $response = [
                'success' => true,
                'msg' => 'Lấy thông tin khách hàng thành công',
                'data' => [
                    'id' => $lead->id,
                    'name' => $lead->name ?? '',
                    'tel' => $lead->tel ?? '',
                    'email' => $lead->email ?? '',
                    'image' => $lead->image ? url('/filemanager/userfiles/' . $lead->image) : '',
                    'project' => $lead->project ?? '',
                    'sale_name' => $saleNames ?? '',
                    'marketer_name' => $marketingNames, // chưa có trường marketer_ids

                ]
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Đã có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail($id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['status' => false, 'msg' => 'Lead không tồn tại'], 404);
        }

        return response()->json([
            'status' => true,
            'msg' => 'Chi tiết lead',
            'data' => [
                'contact_info' => [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'tel' => $lead->tel,
                    'email' => $lead->email,
                    'status' => $lead->status,
                    'company' => $lead->company,
                    'tax_code' => $lead->tax_code,
                    'address' => $lead->address,
                    'founded_date' => $lead->founded_date,
                    'tinh' => $lead->tinh,
                ],
                'advise' => [
                    'project' => $lead->project,
                    'product' => $lead->product,
                    'need' => $lead->need,
                    'advise_suggest' => $lead->advise_suggest,
                ],
                'refusal' => [
                'refusal' => [
                    'reason_refusal' => $lead->reason_refusal,
                ]
            ]
       ]]);
    }

   //  🟦 THÊM MỚI
    public function add(Request $request)
    {
        $method = $request->method();
        $leadId = $request->id ?? null;

        if ($method === 'GET') {

            if (!$leadId) {
                return response()->json(['status' => false, 'msg' => 'Thiếu ID Lead'], 400);
            }

            $lead = Lead::find($leadId);
            if (!$lead) {
                return response()->json(['status' => false, 'msg' => 'Lead không tồn tại'], 404);
            }

            $data = $lead->toArray();
            foreach ($data as $k => $v) $data[$k] = $v ?? '';

            // Sale
            $data['sale_name'] = '';
            if (!empty($lead->saler_ids)) {
                $saleIds = array_filter(array_map('trim', explode('|', $lead->saler_ids)));
                $data['sale_name'] = DB::table('admin')
                    ->whereIn('id', $saleIds)
                    ->pluck('name')
                    ->implode(',');
            }

            // Marketer
            $data['marketer_name'] = '';
            if (!empty($lead->marketer_ids)) {
                $mkIds = array_filter(array_map('trim', explode('|', $lead->marketer_ids)));
                $data['marketer_name'] = DB::table('admin')
                    ->whereIn('id', $mkIds)
                    ->pluck('name')
                    ->implode(',');
            }

            // Staff care
            $data['staff_care'] = '';
            if (!empty($lead->staff_care)) {
                $data['staff_care'] = str_replace('|', ',', $lead->staff_care);
            }

            $data['partner'] = !empty($lead->partner);
            $data['dating']  = $lead->dating ? Carbon::parse($lead->dating)->format('Y-m-d H:i:s') : '';
            $data['image']   = $lead->image ? asset($lead->image) : '';

            unset($data['saler_ids'], $data['marketer_ids'], $data['created_at'], $data['updated_at'], $data['telesale_id']);

            // Lịch sử tư vấn
            $logs = DB::table('lead_contacted_log as l')
                ->join('admin as a', 'a.id', '=', 'l.admin_id')
                ->where('l.lead_id', $leadId)
                ->orderBy('l.created_at', 'desc')
                ->select('l.id', 'a.name as admin_name', 'l.note', 'l.created_at', 'l.updated_at', 'l.title')
                ->get();

            $lastLog = DB::table('lead_contacted_log')
                ->where('lead_id', $leadId)
                ->latest('created_at')
                ->first();


            $data['contact_logs'] = $logs;
            $data['reason_refusal'] = $lead->reason_refusal ?: ($lastLog->reason_refusal ?? '');
            $data['advise_suggest'] = $lead->advise_suggest ?: ($lastLog->advise_suggest ?? '');


            return response()->json([
                'status' => true,
                'msg'    => 'Lấy chi tiết thành công',
                'data'   => $data
            ]);
        }

        // POST: validate
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'tel'  => 'required|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ], [
            'name.required' => 'Vui lòng nhập tên khách hàng',
            'tel.required'  => 'Vui lòng nhập số điện thoại',
            'image.image'   => 'File tải lên phải là hình ảnh',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg'    => $validator->errors()->first()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = $request->get('currentUser');
            $lead = $leadId ? Lead::find($leadId) : new Lead();

            if ($leadId && !$lead) {
                return response()->json(['status' => false, 'msg' => 'Lead không tồn tại'], 404);
            }

            // Upload ảnh
            $imagePath = $lead->image ?? '';
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = 'z' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folder = date('Y/m/d');
                $destinationPath = base_path("public_html/filemanager/userfiles/lead/$folder");
                if (!file_exists($destinationPath)) mkdir($destinationPath, 0755, true);
                $file->move($destinationPath, $fileName);
                $imagePath = "filemanager/userfiles/lead/$folder/$fileName";
            }

            // Cập nhật Lead
            $lead->fill([
                'name'   => $request->name,
                'tel'    => $request->tel,
                'email'  => $request->email ?? '',
                'company'=> $request->company ?? '',
                'tax_code'=> $request->tax_code ?? '',
                'address'=> $request->address ?? '',
                'dating' => $request->dating ? Carbon::parse($request->dating)->format('Y-m-d H:i:s') : $lead->dating,
                'project'=> $request->project ?? '',
                'need'   => $request->need ?? '',
                'difficulty' => $request->difficulty ?? '',
                'rate'   => $request->rate ?? '',
                'profile'=> $request->profile ?? '',
                'tags'   => $request->tags ?? '',
                'status' => $request->status ?? 'Thả nổi',
                'partner'=> $request->partner == 'true' ? 1 : 0,
                'service'=> $request->service ?? '',
                'product'=> $request->product ?? '',
                'image'  => $imagePath,
                'reason_refusal'=>$request->reason_refusal ?? '',
                'advise_suggest'=>$request->advise_suggest ?? '',
                'admin_id' => $user->id ?? $lead->admin_id,
                'received_date' => $request->received_date
                    ? Carbon::parse($request->received_date)->format('Y-m-d H:i:s')
                    : ($lead->received_date ?? now()),
                'updated_at' => now(),

                // Gia hạn
                'expiry_date' => $request->expiry_date ? Carbon::parse($request->expiry_date)->format('Y-m-d') : $lead->expiry_date,
                'renew_price' => $request->renew_price ?? $lead->renew_price,
                'contract_time'=> $request->contract_time ?? $lead->contract_time,
            ]);

            // Sale IDs
            if (!empty($request->sale_name)) {
                $saleNames = explode(',', $request->sale_name); // tách theo dấu ,
                $saleIds = Admin::whereIn('name', $saleNames)->pluck('id')->toArray();
                $lead->saler_ids = implode('|', $saleIds); // hoặc lưu trực tiếp mảng nếu DB hỗ trợ
            }

// Marketer IDs
            if (!empty($request->marketer_name)) {
                $marketerNames = explode(',', $request->marketer_name);
                $marketerIds = Admin::whereIn('name', $marketerNames)->pluck('id')->toArray();
                $lead->marketer_ids = implode('|', $marketerIds);
            }



            if ($request->staff_care) {
                // 1. Chia chuỗi tên nhân viên thành mảng
                $staffNames = array_map('trim', explode(',', $request->staff_care));

                // 2. Lấy danh sách nhân viên theo tên
                $staffIds = \App\Models\Staff::whereIn('name', $staffNames)->pluck('id')->toArray();

                // 3. Chuyển sang format |1|2|3|
                $lead->staff_care = '|' . implode('|', $staffIds) . '|';
            }

            $lead->save();

            // Thêm lịch sử tư vấn nếu có
            if ($request->filled('log_name') || $request->filled('log_note')) {
                DB::table('lead_contacted_log')->insert([
                    'admin_id' => $user->id,
                    'lead_id'  => $lead->id,
                    'title'    => $request->log_name ?? '',
                    'note'     => $request->log_note ?? '',
                    'advise_suggest' => $request->advise_suggest ?? '',
                    'reason_refusal' => $request->reason_refusal ?? '',
                    'type' => 'lead',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'msg'    => $leadId ? 'Cập nhật thành công' : 'Tạo mới thành công',
                'data'   => $lead
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg'    => $e->getMessage()
            ], 500);
        }
    }

    public function uploadLeadImage($file)
    {
        $fileName = 'z' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $folder = date('Y/m/d');

        $destinationPath = base_path("public_html/filemanager/userfiles/lead/$folder");

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $file->move($destinationPath, $fileName);

        return "filemanager/userfiles/lead/$folder/$fileName";
    }


    /**
     * 🔧 Hàm format dữ liệu lead (dùng chung cho cả GET và POST)
     */
    public function formatLeadDetail($lead)
    {
        $leadArray = $lead->toArray();

        foreach ($leadArray as $key => $val) {
            $leadArray[$key] = $val === null ? '' : $val;
        }

        // Service -> mảng
        $leadArray['service'] = !empty($leadArray['service'])
            ? explode(',', $leadArray['service'])
            : [];

        // Saler / Marketer
        $leadArray['sale_name'] = '';
        $leadArray['marketer_name'] = '';

        if (!empty($lead->saler_ids)) {
            $salerIds = array_filter(explode('|', $lead->saler_ids));
            $salerNames = Admin::whereIn('id', $salerIds)->pluck('name')->toArray();
            $leadArray['sale_name'] = implode(', ', $salerNames);
        }

        if (!empty($lead->marketer_ids)) {
            $marketerIds = array_filter(explode('|', $lead->marketer_ids));
            $marketerNames = Admin::whereIn('id', $marketerIds)->pluck('name')->toArray();
            $leadArray['marketer_name'] = implode(', ', $marketerNames);
        }

        unset($leadArray['saler_ids'], $leadArray['marketer_ids']);
        unset($leadArray['created_at'], $leadArray['updated_at']);
        unset($leadArray['rate'], $leadArray['profile']);

        // Lịch sử tư vấn
        $contactedLogs = [];
        foreach ($lead->contactedLogs->sortByDesc('created_at') as $log) {
            $logArr = $log->toArray();
            foreach ($logArr as $key => $val) {
                $logArr[$key] = $val === null ? '' : $val;
            }
            $logArr['created_at'] = !empty($log->created_at)
                ? Carbon::parse($log->created_at)->format('d-m-Y H:i:s')
                : '';
            $logArr['updated_at'] = !empty($log->updated_at)
                ? Carbon::parse($log->updated_at)->format('d-m-Y H:i:s')
                : '';
            $contactedLogs[] = $logArr;
        }

        // Bản ghi khởi tạo
        $contactedLogs[] = [
            'admin_id' => $lead->admin_id ?? '',
            'lead_id' => $lead->id ?? '',
            'note' => 'Lead được khởi tạo',
            'created_at' => $lead->created_at ? $lead->created_at->format('d-m-Y H:i:s') : '',
            'updated_at' => $lead->created_at ? $lead->created_at->format('d-m-Y H:i:s') : '',
            'title' => 'Khởi tạo lead',
            'type' => 'lead'
        ];

        $leadArray['contacted_logs'] = array_values($contactedLogs);

        return $leadArray;
    }
    public function updateList(Request $request, $id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Không tìm thấy khách hàng',
                ], 404);
            }

            // Validate dữ liệu
            $validated = $request->validate([
                'dating' => 'nullable|date',
                'rate'   => 'nullable|string|max:250',
            ]);

            if (array_key_exists('dating', $validated)) {

                $newDating = $validated['dating']
                    ? Carbon::parse($validated['dating'])->format('Y-m-d')
                    : null;

                $oldDating = $lead->dating
                    ? Carbon::parse($lead->dating)->format('Y-m-d')
                    : null;

                // Chỉ lưu log khi có ngày cũ và ngày thay đổi
                if ($oldDating && $oldDating !== $newDating) {
                    $lead->contacted_log_last = $oldDating;
                }

                // Update dating (kể cả null)
                $lead->dating = $newDating;
            }

            if (array_key_exists('rate', $validated)) {
                $lead->rate = $validated['rate'];
            }

            $lead->save();

            return response()->json([
                'status' => true,
                'msg' => 'Cập nhật khách hàng thành công',
                'data' => [
                    'id' => $lead->id,
                    'rate' => $lead->rate ?? '',
                    'dating' => $lead->dating ?? '',
                    'contacted_log_last' => $lead->contacted_log_last ?? '',
                ],
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Lỗi cập nhật khách hàng',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function updateLead(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'tel'   => 'required|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ], [
            'name.required' => 'Vui lòng nhập tên khách hàng',
            'tel.required'  => 'Vui lòng nhập số điện thoại',
            'image.image'   => 'File tải lên phải là hình ảnh',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg'    => $validator->errors()->first()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = $request->get('currentUser');
            $lead = Lead::find($id);
             $last_log=$lead->dating;
            if (!$lead) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Lead không tồn tại'
                ], 404);
            }

            /** ================= Upload ảnh ================= */
            $imagePath = $lead->image;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = 'z' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folder = date('Y/m/d');
                $destinationPath = base_path("public_html/filemanager/userfiles/lead/$folder");

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $fileName);
                $imagePath = "filemanager/userfiles/lead/$folder/$fileName";
            }

            /** ================= Update Lead ================= */
            $lead->fill([
                'name'        => $request->name,
                'tel'         => $request->tel,
                'email'       => $request->email ?? '',
                'company'     => $request->company ?? '',
                'tax_code'    => $request->tax_code ?? '',
                'address'     => $request->address ?? '',
                'dating'      => $request->dating
                    ? Carbon::parse($request->dating)->format('Y-m-d H:i:s')
                    : $lead->dating,
                'project'     => $request->project ?? '',
                'need'        => $request->need ?? '',
                'difficulty'  => $request->difficulty ?? '',
                'rate'        => $request->rate ?? '',
                'profile'     => $request->profile ?? '',
                'tags'        => $request->tags ?? '',
                'status'      => $request->status ?? 'Thả nổi',
                'partner'     => $request->partner == 'true' ? 1 : 0,
                'service'     => $request->service ?? '',
                'product'     => $request->product ?? '',
                'image'       => $imagePath,
                'contacted_log_last'=>$last_log,
                'reason_refusal' => $request->reason_refusal ?? '',
                'advise_suggest' => $request->advise_suggest ?? '',
                'admin_id'    => $user->id ?? $lead->admin_id,
                'received_date' => $request->received_date
                    ? Carbon::parse($request->received_date)->format('Y-m-d H:i:s')
                    : ($lead->received_date ?? now()),
                'expiry_date' => $request->expiry_date
                    ? Carbon::parse($request->expiry_date)->format('Y-m-d')
                    : $lead->expiry_date,
                'renew_price' => $request->renew_price ?? $lead->renew_price,
                'contract_time'=> $request->contract_time ?? $lead->contract_time,
                'updated_at'  => now(),
            ]);

            /** ================= Sale IDs ================= */
            if (!empty($request->sale_name)) {
                $saleNames = array_map('trim', explode(',', $request->sale_name));
                $saleIds = Admin::whereIn('name', $saleNames)->pluck('id')->toArray();
                $lead->saler_ids = implode('|', $saleIds);
            }

            /** ================= Marketer IDs ================= */
            if (!empty($request->marketer_name)) {
                $mkNames = array_map('trim', explode(',', $request->marketer_name));
                $mkIds = Admin::whereIn('name', $mkNames)->pluck('id')->toArray();
                $lead->marketer_ids = implode('|', $mkIds);
            }

            /** ================= Staff care ================= */
            if ($request->staff_care) {
                $staffNames = array_map('trim', explode(',', $request->staff_care));
                $staffIds = \App\Models\Staff::whereIn('name', $staffNames)->pluck('id')->toArray();
                $lead->staff_care = '|' . implode('|', $staffIds) . '|';
            }

            $lead->save();

            /** ================= Log tư vấn ================= */
            if ($request->filled('log_name') || $request->filled('log_note')) {
                DB::table('lead_contacted_log')->insert([
                    'admin_id' => $user->id,
                    'lead_id'  => $lead->id,
                    'title'    => $request->log_name ?? '',
                    'note'     => $request->log_note ?? '',
                    'advise_suggest' => $request->advise_suggest ?? '',
                    'reason_refusal' => $request->reason_refusal ?? '',
                    'type'      => 'lead',
                    'status'    => 1,
                    'created_at'=> now(),
                    'updated_at'=> now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'msg'    => 'Cập nhật thành công',
                'data'   => $lead
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg'    => $e->getMessage()
            ], 500);
        }
    }

//    public function search(Request $request)
//    {
//        $q = trim($request->get('q', ''));
//        $limit = $request->get('limit', 20);
//        if (empty($q)) {
//            return response()->json([
//                'status' => false,
//                'msg' => 'Vui lòng nhập từ khóa tìm kiếm'
//            ], 400);
//        }
//
//        // Xử lý SĐT: loại bỏ dấu chấm, phẩy, khoảng trắng
//        $searchTel = preg_replace('/[^0-9]/', '', $q);
//
//        $query = Lead::query()
//            ->where(function ($query) use ($q, $searchTel) {
//                $query->where('name', 'LIKE', "%{$q}%")
//                    ->orWhere('tel', 'LIKE', "%{$q}%");
//
//                if (!empty($searchTel) && $searchTel !== $q) {
//                    $query->orWhere('tel', 'LIKE', "%{$searchTel}%");
//                }
//            })
//            ->orderBy('id', 'desc')
//            ->limit($limit);
//
//        $leads = $query->get();
//
//        $data = $leads->map(function ($item) {
//            $saleNames = '';
//            if (!empty($item->saler_ids)) {
//                $saleIds = array_filter(explode('|', $item->saler_ids));
//                $saleNames = Admin::whereIn('id', $saleIds)->pluck('name')->implode(', ');
//            }
//
//            return [
//                'id' => $item->id,
//                'name' => $item->name ?? '',
//                'tel' => $item->tel ?? '',
//                'image' => $item->image ?url('/filemanager/userfiles/' . $item->image) : '',
//                'project' => $item->project ?? '',
//                'sale_name' => $saleNames,
//                'status' => $item->status ?? '',
//            ];
//        });
//
//        return response()->json([
//            'status' => true,
//            'msg' => 'Tìm thấy ' . $leads->count() . ' kết quả',
//            'data' => $data
//        ]);
//    }

}