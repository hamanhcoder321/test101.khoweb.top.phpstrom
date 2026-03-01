<?php
namespace App\Http\Controllers\Admin;

use App\Http\Helpers\CommonHelper;
use App\Models\Attribute;
use App\Models\Import;
use Illuminate\Http\Request;
use Validator;
use DB;

class ImportController extends CURDBaseController
{
    protected $table_import = '';

    protected $module = [
        'code' => 'import',
        'table_name' => 'imports',
        'label' => 'Import',
        'modal' => '\App\Models\Import',
        'list' => [
            ['name' => 'module', 'type' => 'select', 'label' => 'Module', 'options' => [
                'user' => 'Khách hàng / đối tác',
                'admin' => 'Thành viên quản lý',
            ]],
//            ['name' => 'record_total', 'type' => 'custom', 'td' => 'import.list.td.count_record', 'label' => 'Thành công/Tổng số'],
//            ['name' => 'record_success', 'type' => 'number', 'label' => 'Bản ghi thành công'],
            ['name' => 'file', 'type' => 'file', 'label' => 'File import'],
            ['name' => 'created_at', 'type' => 'datetime_vi', 'label' => 'Thời gian'],
            ['name' => 'action', 'type' => 'action', 'class' => '', 'label' => '#'],
        ],
        'form' => [
            'general_tab' => [
                /*['name' => 'module', 'type' => 'select', 'options' => [
                    'user' => 'Khách hàng / đối tác',
                    'admin' => 'Thành viên quản lý',
                ], 'class' => 'required', 'label' => 'Chọn module', 'des' => 'Khu vực mà bạn muốn đẩy dữ liệu vào'],*/
                ['name' => 'module', 'type' => 'custom', 'field' => 'import.partials.select_module', 'options' => [
                    'user' => 'Khách hàng / đối tác',
                    'admin' => 'Thành viên quản lý',
                ], 'class' => 'required', 'label' => 'Chọn module', 'des' => 'Khu vực mà bạn muốn đẩy dữ liệu vào'],
                ['name' => 'btn_download_excel_demo', 'type' => 'inner', 'class' => '', 'label' => 'Tải file Excel mẫu', 'html' => '<button type="button" onclick="downloadExcelDemo();" class="btn btn-brand">
                                        <i class="la la-download"></i>
                                        <span class="kt-hidden-mobile">Tải về file mẫu</span>
                                    </button>'],
                ['name' => 'file', 'type' => 'file', 'class' => 'required', 'label' => 'Nhập file Excel', 'des' => 'Nhập vào file excel mà bạn muốn import dữ liệu. Lưu ý: hệ thống chỉ nhận dữ liệu ở các cột đã được khai báo trong file mẫu'],
                ['name' => 'field_options', 'type' => 'dynamic', 'label' => 'Tham số mặc định', 'cols' => ['Trường', 'Giá trị'], 'des' => 'Các trường dữ liệu mà bạn muốn set cứng vào các bản ghi khi import dữ liệu vào'],
                ['name' => 'note', 'type' => 'textarea', 'label' => 'Ghi chú'],
            ],
        ]
    ];

    protected $import = [
        'users' => [
            'fields' => [
            ],
            'modal' => '\App\Models\User',
            'field_require' => 'tel',
            'unique' => 'tel'
        ],
        'admin' => [
            'fields' => [
            ],
            'modal' => '\App\Models\Admin',
            'field_require' => 'tel',
            'unique' => 'tel'
        ]
    ];

    protected $filter = [
        'module' => [
            'label' => 'Module',
            'type' => 'select',
            'query_type' => '=',
            'options' => [
                '' => 'Module',
                'user' => 'Khách hàng / đối tác',
                'admin' => 'Thành viên quản lý',
            ]
        ],
    ];

    

    /**
     * BƯỚC 1: GET  → hiển thị form upload
     * BƯỚC 2: POST action=upload → đọc Excel, lưu session, redirect sang preview
     * BƯỚC 3: POST action=save_bills → lưu dữ liệu từ session vào bảng bills
     */
    public function addNhanhoa(Request $request)
    {
        // =============================================
        // BƯỚC 1: Hiển thị form upload
        // =============================================
        if ($request->isMethod('GET')) {
            session()->forget('nhanhoa_preview');
            return view('admin.themes.metronic1.import.add_nhanhoa', [
                'step' => 1,
            ]);
        }

        // =============================================
        // BƯỚC 2: Upload & parse Excel → preview
        // =============================================
        if ($request->input('action') === 'upload') {
            // Validate cơ bản (không dùng mimes: vì macOS gửi MIME khác Windows)
            $validator = \Validator::make($request->all(), [
                'file' => 'required|file|max:5120',
            ], [
                'file.required' => 'Vui lòng chọn file Excel trước!',
                'file.max'      => 'File không được vượt quá 5MB!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            // Kiểm tra extension thủ công (tương thích cả Windows & macOS)
            $ext = strtolower($request->file('file')->getClientOriginalExtension());
            if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
                return back()->withErrors(['file' => 'File phải có định dạng xlsx, xls hoặc csv!'])->withInput();
            }

            try {
                $file_name        = str_replace(' ', '', $request->file('file')->getClientOriginalName());
                $file_name_insert = date('s_i_') . $file_name;
                $dest_dir         = base_path() . '/public_html/filemanager/userfiles/imports/';
                $request->file('file')->move($dest_dir, $file_name_insert);
                $file_path = 'public_html/filemanager/userfiles/imports/' . $file_name_insert;

                $rows        = [];
                $column_keys = [];

                // Đọc tất cả cột bằng noHeading + toArray
                $sheet_data = \Excel::load($file_path, function ($reader) {
                    $reader->noHeading();
                })->toArray();

                // Lấy sheet đầu tiên (tránh lỗi "only variables by reference")
                $raw_rows = [];
                if (!empty($sheet_data)) {
                    $first_sheet = reset($sheet_data);
                    if (is_array($first_sheet)) {
                        $first_row_check = reset($first_sheet);
                        if (is_array($first_row_check)) {
                            // 3 chiều: [sheet][row][col] → lấy sheet đầu
                            $raw_rows = $first_sheet;
                        } else {
                            // 2 chiều: [row][col]
                            $raw_rows = $sheet_data;
                        }
                    } else {
                        $raw_rows = $sheet_data;
                    }
                }

                if (empty($raw_rows)) {
                    throw new \Exception('File Excel trống hoặc không đọc được!');
                }

                // Phát hiện dòng đầu có phải header không
                $first_row_values = array_values($raw_rows[0] ?? []);
                $is_header = !empty(array_filter($first_row_values, function ($v) {
                    return is_string($v) && !is_numeric($v) && !empty(trim((string)$v));
                }));

                $header_map = [];
                $data_start = 0;

                if ($is_header) {
                    // Dùng dòng đầu làm tên cột
                    foreach ($first_row_values as $idx => $name) {
                        $key = mb_strtolower(trim((string)$name));
                        $key = preg_replace('/\s+/', '_', $key);
                        $key = preg_replace('/[^a-z0-9_]/u', '', $key);
                        $header_map[$idx] = $key ?: "col_{$idx}";
                    }
                    $data_start = 1;
                } else {
                    // Không có header → dùng index số
                    foreach ($first_row_values as $idx => $v) {
                        $header_map[$idx] = $idx;
                    }
                }

                $column_keys = array_values($header_map);

                for ($i = $data_start; $i < count($raw_rows); $i++) {
                    $raw = array_values($raw_rows[$i] ?? []);
                    $row = [];
                    foreach ($header_map as $idx => $colName) {
                        $row[$colName] = $raw[$idx] ?? null;
                    }
                    $rows[] = $row;
                }

                // Lọc dòng trống (kiểm tra trên tất cả giá trị)
                $rows = array_values(array_filter($rows, function ($row) {
                    return !empty(array_filter(array_values($row), function($v) {
                        return $v !== null && $v !== '';
                    }));
                }));

                // Giới hạn 500 bản ghi
                if (count($rows) > 500) {
                    $rows = array_slice($rows, 0, 500);
                }

                session(['nhanhoa_preview' => [
                    'rows'        => $rows,
                    'column_keys' => $column_keys,
                    'file_path'   => 'imports/' . $file_name_insert,
                    'file_name'   => $file_name,
                ]]);

                return view('admin.themes.metronic1.import.add_nhanhoa', [
                    'step'        => 2,
                    'rows'        => $rows,
                    'column_keys' => $column_keys,
                ]);

            } catch (\Exception $ex) {
                \App\Http\Helpers\CommonHelper::one_time_message('error', 'Lỗi đọc file: ' . $ex->getMessage());
                return redirect('/admin/import/add_nhanhoa');
            }
        }

        // =============================================
        // BƯỚC 3: Lưu tên miền Nhanhoa vào hợp đồng (bills)
        // =============================================
        if ($request->input('action') === 'save_bills') {
            $preview = session('nhanhoa_preview');
            if (empty($preview) || empty($preview['rows'])) {
                \App\Http\Helpers\CommonHelper::one_time_message('error', 'Không có dữ liệu để lưu. Vui lòng upload lại!');
                return redirect('/admin/import/add_nhanhoa');
            }

            // Lấy Sale mặc định theo SĐT từ bảng admin
            $default_tel   = '0987519120';
            $saler_admin   = \App\Models\Admin::where('tel', $default_tel)->first();
            $saler_id      = $saler_admin ? $saler_admin->id : null;

            // Lấy Khách hàng mặc định theo SĐT từ bảng USERS (không phải leads)
            $default_user  = \App\Models\User::where('tel', $default_tel)->first();
            $customer_id   = $default_user ? $default_user->id : null;

            $added   = [];
            $skipped = [];
            $errors  = [];

            foreach ($preview['rows'] as $index => $row) {
                try {
                    $result = $this->saveNhanhoaAsBill($row, $saler_id, $customer_id);
                    if ($result === 'added') {
                        $added[] = $result;
                    } elseif (is_string($result) && substr($result, 0, 8) === 'skipped:') {
                        // PHP7 compatible: không dùng str_starts_with (PHP8+)
                        $skipped[] = substr($result, 8);
                    } else {
                        $errors[] = "Dòng " . ($index + 1) . ": thiếu tên miền";
                    }
                } catch (\Exception $rowEx) {
                    $errors[] = "Dòng " . ($index + 1) . ": " . $rowEx->getMessage();
                }
            }

            session()->forget('nhanhoa_preview');

            // Thông báo kết quả
            if (!empty($added)) {
                \App\Http\Helpers\CommonHelper::one_time_message('success',
                    'Đã tạo ' . count($added) . ' hợp đồng thành công!');
            }
            if (!empty($skipped)) {
                \App\Http\Helpers\CommonHelper::one_time_message('warning',
                    count($skipped) . ' tên miền đã có trong hợp đồng, bỏ qua: ' . implode(', ', $skipped));
            }
            if (!empty($errors)) {
                \App\Http\Helpers\CommonHelper::one_time_message('error',
                    count($errors) . ' dòng lỗi: ' . implode(' | ', array_slice($errors, 0, 5)));
            }

            return redirect('/admin/bill');
        }

        return redirect('/admin/import/add_nhanhoa');
    }

    /**
     * Lưu 1 dòng từ Excel Nhanhoa vào bảng bills (hợp đồng)
     * Return: 'added' | 'skipped:domain' | false
     */
    protected function saveNhanhoaAsBill(array $row, $saler_id = null, $customer_id = null)
    {
        // Chuẩn hóa key
        $n = [];
        foreach ($row as $k => $v) {
            $n[strtolower(trim((string)$k))] = $v;
        }

        // === DOMAIN (VN: tn_min / Quốc tế: domain, domain_name, hoặc cột đầu tiên) ===
        $domain_candidates = [
            // Nhanhoa VN (stripped accent)
            'tn_min', 'tnmin', 'ten_mien', 'tenmien',
            // Nhanhoa quốc tế / format khác
            'domain', 'domain_name', 'name', 'hostname',
            'ten_mien_quoc_te', 'tnmienquocte',
            // Fallback index
            0, '0',
        ];
        $domain = '';
        foreach ($domain_candidates as $c) {
            if (isset($n[$c]) && !empty(trim((string)$n[$c]))) {
                $domain = trim($n[$c]); break;
            }
        }
        if (empty($domain)) {
            $vals = array_values($n);
            $domain = trim($vals[0] ?? '');
        }
        if (empty($domain)) return false;

        // Chuẩn hóa domain (VN và quốc tế đều xử lý giống nhau)
        $domain = str_replace(['http://', 'https://', 'www.'], '', strtolower($domain));
        $domain = rtrim(trim($domain), '/');

        // === Kiểm tra đã có hợp đồng với domain này chưa ===
        $exists = \App\CRMDV\Models\Bill::where('domain', $domain)->whereNull('deleted_at')->exists();
        if ($exists) {
            return 'skipped:' . $domain;
        }

        // === HELPER: parse ngày từ Excel (hỗ trợ dd/mm/yyyy của Nhanhoa VN & quốc tế) ===
        $parseDate = function($val) {
            if (empty($val)) return null;
            // Carbon instance (maatwebsite tự convert date cell)
            if ($val instanceof \Carbon\Carbon) return $val->format('Y-m-d');
            $str = trim((string)$val);
            if (empty($str)) return null;
            // dd/mm/yyyy hoặc d/m/yyyy (format Nhanhoa VN)
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $str, $m)) {
                return date('Y-m-d', mktime(0, 0, 0, (int)$m[2], (int)$m[1], (int)$m[3]));
            }
            // dd-mm-yyyy (dấu gạch)
            if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $str, $m)) {
                return date('Y-m-d', mktime(0, 0, 0, (int)$m[2], (int)$m[1], (int)$m[3]));
            }
            // yyyy-mm-dd (chuẩn ISO)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) return $str;
            // Thử Carbon parse chung
            try { return \Carbon\Carbon::parse($str)->format('Y-m-d'); } catch (\Exception $e) {}
            return null;
        };

        // === NGÀY ĐĂNG KÝ — VN: ngp_ng_k / Quốc tế: start_date, registered_at... ===
        $registered_at = null;
        $reg_candidates = [
            // Nhanhoa VN stripped
            'ngp_ng_k', 'ngy_ng_k', 'ng_ng_k',
            // Nhanhoa quốc tế / format khác
            'registered_at', 'registration_date', 'start_date',
            'ngay_dang_ky', 'created_date', 'begin_date',
            // Fallback index
            1, '1',
        ];
        foreach ($reg_candidates as $c) {
            if (!empty($n[$c])) {
                $registered_at = $parseDate($n[$c]);
                if ($registered_at) break;
            }
        }

        // === NGÀY HẾT HẠN — VN: ngp_ht_hn / Quốc tế: expiry_date, end_date... ===
        $expired_at = null;
        $exp_candidates = [
            // Nhanhoa VN stripped
            'ngp_ht_hn', 'ngy_ht_hn', 'ngp_h_hn',
            // Nhanhoa quốc tế / format khác
            'expired_at', 'expiry_date', 'expiry', 'expire',
            'end_date', 'end_at', 'het_han', 'ngay_het_han',
            // Fallback index
            2, '2',
        ];
        foreach ($exp_candidates as $c) {
            if (!empty($n[$c])) {
                $expired_at = $parseDate($n[$c]);
                if ($expired_at) break;
            }
        }


        // === THỜI GIAN SỬ DỤNG (tháng) ===
        $contract_time = null;
        if ($registered_at && $expired_at) {
            $contract_time = (int) \Carbon\Carbon::parse($registered_at)
                ->diffInMonths(\Carbon\Carbon::parse($expired_at));
        }

        // === TẠO HỢP ĐỒNG ===
        $bill = new \App\CRMDV\Models\Bill();
        $bill->domain                = $domain;
        $bill->product_or_service    = $domain;
        $bill->registration_date     = $registered_at ?? date('Y-m-d');
        $bill->expiry_date           = $expired_at;
        $bill->contract_time         = $contract_time;
        $bill->total_price           = 0;
        $bill->total_price_contract  = 0;
        $bill->price_period          = 0;
        $bill->exp_price             = 1;
        $bill->auto_extend           = 0;
        $bill->service_id            = 16;
        $bill->status                = 1;
        $bill->saler_id              = $saler_id;
        // customer_id NOT NULL → fallback lấy user đầu tiên nếu null
        $user_fallback               = $customer_id ? null : \App\Models\User::first();
        $bill->customer_id           = $customer_id ?? ($user_fallback ? $user_fallback->id : 1);
        $bill->save();

        return 'added';
    }






    
    public function appendData($request, $data)
    {
        if ($request->has('file')) {
            $file_name = $request->file('file')->getClientOriginalName();
            $file_name = str_replace(' ', '', $file_name);
            $file_name_insert = date('s_i_') . $file_name;
            $request->file('file')->move(base_path() . '/public_html/filemanager/userfiles/imports/', $file_name_insert);
            $data['file'] = 'imports/' . $file_name_insert;
        }

        unset($data['field_options_key']);
        unset($data['field_options_value']);
        return $data;
    }

    public function getIndex(Request $request)
    {

//        if (in_array('view', $this->permission)) {
//            if (!CommonHelper::has_permission(\Auth::guard('admin')->user()->id, $this->module['name'] . '_view')) {
//                CommonHelper::one_time_message('error', 'Bạn không có quyền sử dụng chức năng này!');
//                return redirect()->back();
//            }
//        }
        #
        $data = $this->getDataList($request);

        return view('admin.themes.metronic1.'.$this->module['code'].'.list')->with($data);
    }

    public function add(Request $request)
    {
        try {

            if (!$_POST) {
                $data = $this->getDataAdd($request);
                return view('admin.themes.metronic1.' . $this->module['code'] . '.add')->with($data);
            } else if ($_POST) {
                $validator = Validator::make($request->all(), [
                    'module' => 'required',
                ], [
                    'module.required' => 'Bắt buộc phải nhập module!',
                ]);
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                } else {
                    DB::beginTransaction();
                    $data = $this->processingValueInFields($request, $this->getAllFormFiled());
                    //  Tùy chỉnh dữ liệu insert

                    $data = $this->appendData($request, $data);
                    #
                    foreach ($data as $k => $v) {
                        $this->model->$k = $v;
                    }

                    if ($this->model->save()) {
                        $this->afterAdd($request, $this->model);
                        CommonHelper::flushCache($this->module['table_name']);
                        CommonHelper::one_time_message('success', 'Tạo mới thành công!');
                        DB::commit();
                        return redirect('/admin/import');
                    } else {
                        DB::rollback();
                        CommonHelper::one_time_message('error', 'Lỗi tao mới. Vui lòng load lại trang và thử lại!');
                    }

                    if ($request->ajax()) {
                        return response()->json([
                            'status' => true,
                            'msg' => '',
                            'data' => $this->model
                        ]);
                    }
                    if ($request->return_direct == 'save_continue') {
                        return redirect('admin/' . $this->module['code'] . '/edit/' . $this->model->id);
                    } elseif ($request->return_direct == 'save_create') {
                        return redirect('admin/' . $this->module['code'] . '/add');
                    }

                    return redirect('admin/' . $this->module['code']);
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            CommonHelper::one_time_message('error', $ex->getMessage());
            return redirect()->back()->withInput();
        }
    }
    public function addCheckWebServer(Request $request)
    {
        try {

            if (!$_POST) {
                $data = $this->getDataAdd($request);
                $str = ('admin.themes.metronic1.' . $this->module['code'] . '.add_check');
//                dd($str);
                return view($str)->with($data);
            } else if ($_POST) {
                $validator = Validator::make($request->all(), [
                    'module' => 'required',
                ], [
                    'module.required' => 'Bắt buộc phải nhập module!',
                ]);
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                } else {
                    DB::beginTransaction();
                    $data = $this->processingValueInFields($request, $this->getAllFormFiled());
                    //  Tùy chỉnh dữ liệu insert

                    $data = $this->appendData($request, $data);
                    #
                    foreach ($data as $k => $v) {
                        $this->model->$k = $v;
                    }

                    if ($this->model->save()) {
                        $this->afterAdd($request, $this->model);
                        CommonHelper::flushCache($this->module['table_name']);
                        CommonHelper::one_time_message('success', 'Tạo mới thành công!');
                        DB::commit();
                        return redirect('/admin/import');
                    } else {
                        DB::rollback();
                        CommonHelper::one_time_message('error', 'Lỗi tao mới. Vui lòng load lại trang và thử lại!');
                    }

                    if ($request->ajax()) {
                        return response()->json([
                            'status' => true,
                            'msg' => '',
                            'data' => $this->model
                        ]);
                    }
                    if ($request->return_direct == 'save_continue') {
                        return redirect('admin/' . $this->module['code'] . '/edit/' . $this->model->id);
                    } elseif ($request->return_direct == 'save_create') {
                        return redirect('admin/' . $this->module['code'] . '/add_check');
                    }

                    return redirect('admin/' . $this->module['code']);
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            CommonHelper::one_time_message('error', $ex->getMessage());
            return redirect()->back()->withInput();
        }
    }
    public function update(Request $request)
    {
        try {

            $item = $this->model->find($request->id);

            if (!is_object($item)) {
                abort(404);
            }
            if (!$_POST) {
                $data = $this->getDataUpdate($request, $item);
                return view('admin.themes.metronic1.' . $this->module['code'] . '.edit')->with($data);
            } else if ($_POST) {
                if ($item->id == \Auth::guard('admin')->user()->id) {
                    $validator = Validator::make($request->all(), [
                        'module' => 'required'
                    ], [
                        'module.required' => 'Bắt buộc phải nhập module',
                    ]);

                    if ($validator->fails()) {
                        return back()->withErrors($validator)->withInput();
                    }
                }
                $data = $this->processingValueInFields($request, $this->getAllFormFiled());
//                    dd($data);
                //  Tùy chỉnh dữ liệu edit

                #
                foreach ($data as $k => $v) {
                    $item->$k = $v;
                }
                if ($item->save()) {
                    $this->afterUpdate($request, $item);
                    CommonHelper::flushCache($this->module['table_name']);
                    CommonHelper::one_time_message('success', 'Cập nhật thành công!');
                } else {
                    CommonHelper::one_time_message('error', 'Lỗi cập nhật. Vui lòng load lại trang và thử lại!');
                }
                if ($request->ajax()) {
                    return response()->json([
                        'status' => true,
                        'msg' => '',
                        'data' => $item
                    ]);
                }

                if ($request->return_direct == 'save_continue') {
                    return redirect('admin/' . $this->module['code'] . '/edit/' . $item->id);
                } elseif ($request->return_direct == 'save_create') {
                    return redirect('admin/' . $this->module['code'] . '/add');
                }

                return redirect('admin/' . $this->module['code']);
            }
        } catch (\Exception $ex) {
            CommonHelper::one_time_message('error', 'Lỗi hệ thống! Vui lòng liên hệ kỹ thuật viên.');
//            CommonHelper::one_time_message('error', $ex->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function delete(Request $request)
    {
        try {
            $item = $this->model->find($request->id);

            $item->delete();
            CommonHelper::flushCache($this->module['table_name']);
            CommonHelper::one_time_message('success', 'Xóa thành công!');
            return redirect('admin/' . $this->module['code']);
        } catch (\Exception $ex) {
            CommonHelper::one_time_message('error', 'Lỗi hệ thống! Vui lòng liên hệ kỹ thuật viên.');
            return back();
        }
    }

    public function multiDelete(Request $request)
    {
        try {

            $ids = $request->ids;
            if (is_array($ids)) {
                $this->model->whereIn('id', $ids)->delete();
            }
            CommonHelper::flushCache($this->module['table_name']);
            return response()->json([
                'status' => true,
                'msg' => ''
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => false,
                'msg' => 'Lỗi hệ thống! Vui lòng liên hệ kỹ thuật viên'
            ]);
        }
    }

    public function afterAdd($request, $item)
    {
        $this->updateAttributes($request, $item);
        $this->importExcel($request, $item);
        return true; // TODO: Change the autogenerated stub
    }

    public function afterUpdate($request, $item)
    {
        $this->updateAttributes($request, $item);
        $this->importExcel($request, $item);
        return true; // TODO: Change the autogenerated stub
    }

    /*public function importExcel($request, $item)
    {
        $table_import = $r->has('table') ? $r->table : $this->module['table_name'];
        $record_total = $record_success = 0;
        $dataInsertFix = Attribute::where('table', $table_import)->where('type', 'field_options')->where('item_id', @$item->id)->pluck('value', 'key')->toArray();
        \Excel::load('public_html/filemanager/userfiles/' . $item->file, function ($reader) use ($request, $dataInsertFix, &$model, &$record_total, &$record_success) {
            $reader->each(function ($sheet) use ($request, $reader, $dataInsertFix, &$model, &$record_total, &$record_success) {

                if ($reader->getSheetCount() == 1) {

                    $result = $this->importItem($sheet, $request, $dataInsertFix);
                    if ($result) {
                        $record_total++;
                        $record_success++;
                    }
                } else {
                    $sheet->each(function ($row) use ($request, $dataInsertFix, &$model, &$record_total, &$record_success) {
                        $result = $this->importItem($row, $request, $dataInsertFix);
                        if ($result) {
                            $record_total++;
                            $record_success++;
                        }
                    });
                }
            });
        });
        $item->record_total = $record_total;
        $item->record_success = $record_total;
        $item->save();
        return true;
    }*/

    //  Xử lý import 1 dòng excel
//    public function importItem($row, $request, $dataInsertFix)
//    {
//        try {
//            if (!isset($row->{$this->import[$request->module]['field_require']}) || $row->{$this->import[$request->module]['field_require']} == '' || $row->{$this->import[$request->module]['field_require']} == null) {
//                return false;
//            }
//
//            /*if ($this->import[$request->module]['unique']) {
//                $field_name = $this->import[$request->module]['fields'][$this->import[$request->module]['unique']];
//                $model_new = new $this->import[$request->module]['modal'];
//                $model = $model_new->where($field_name, $row->{$this->import[$request->module]['unique']})->first();
//            }*/
//            if (!isset($model) || !is_object($model)) {
//                $model = new $this->import[$request->module]['modal'];
//            }
//
//            //  Xử lý các data set cứng
//            foreach ($dataInsertFix as $k => $v) {
//                $model->{$k} = $v;
//            }
//            $fields = $this->import[$request->module]['fields'];
//
//
//            foreach ($row->all() as $key => $value) {
//                if (\Schema::hasColumn($model->getTable(), $key)) {
//                    $model->{$key} = $value;
//                }
//            }
//            if ($model->save()) {
//                return true;
//            }
//        } catch (\Exception $ex) {
//            return false;
//        }
//        return false;
//    }

    /**
     * Tải về file_mau_importexcel
    */
    public function downloadExcelDemo(Request $r) {
        $module = $r->module ?? '';
        $zipFileName = 'excel_default/'.$module.'.xlsx';
        $filetopath = base_path() . '/public_html/filemanager/userfiles/' . $zipFileName;

        //  Nếu có sẵn file tĩnh thì tải về luôn
        if (file_exists($filetopath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename='.basename($filetopath));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filetopath));
            readfile($filetopath);
            exit();
        }

        // Nếu không có file tĩnh → tạo file mẫu động
        \Excel::create('file_mau_import_' . $module, function ($excel) use ($module, $r) {
            $excel->setTitle('file_mau_import_' . $module);
            $excel->sheet($module ?: 'Sheet1', function ($sheet) use ($module, $r) {

                // Nhanhoa: dùng cột đặc thù
                if ($module === 'nhanhoa') {
                    $field_name = ['tn_min', 'ngp_ng_k', 'ngp_ht_hn'];
                } else {
                    // Module khác: lấy cột từ DB
                    $field_name = [];
                    foreach (\Schema::getColumnListing($module) as $column) {
                        if (!in_array($column, ['id', 'created_at', 'updated_at'])) {
                            $field_name[] = $column;
                        }
                    }
                }

                if (!empty($field_name)) {
                    $sheet->row(1, $field_name);
                }
            });
        })->download('xls');
    }

    /**
     * Tối đa import được 999 dòng
     */
    public function importExcel(Request $r)
    {

        $table_import = $r->has('table') ? $r->table : $this->module['table_name'];
        $validator = Validator::make($r->all(), [
            'module' => 'required',
        ], [
            'module.required' => 'Bắt buộc phải nhập module!',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            $importController = new \App\Http\Controllers\Admin\ImportController();
            $data = $this->processingValueInFields($r, $importController->getAllFormFiled());
            //  Tùy chỉnh dữ liệu insert

            if ($r->has('file')) {
                $file_name = $r->file('file')->getClientOriginalName();
                $file_name = str_replace(' ', '', $file_name);
                $file_name_insert = date('s_i_') . $file_name;
                $r->file('file')->move(base_path() . '/public_html/filemanager/userfiles/imports/', $file_name_insert);
                $data['file'] = 'imports/' . $file_name_insert;
            }

            unset($data['field_options_key']);
            unset($data['field_options_value']);
            #

            $item = new \App\Models\Import();
            foreach ($data as $k => $v) {
                $item->$k = $v;
            }
            if ($item->save()) {
                

                //  Import dữ liệu vào
                $this->updateAttributes($r, $item);

                $this->processingImport($r, $item);

                CommonHelper::flushCache($table_import);
                CommonHelper::one_time_message('success', 'Tạo mới thành công!');
//                return redirect('/admin/import');
            } else {
                CommonHelper::one_time_message('error', 'Lỗi tao mới. Vui lòng load lại trang và thử lại!');
            }

            if ($r->ajax()) {
                return response()->json([
                    'status' => true,
                    'msg' => '',
                    'data' => $item
                ]);
            }

//            return redirect('/admin/import');
        }
    }

    public function updateAttributes($r, $item)
    {
        $table_import = $r->has('table') ? $r->table : $this->module['table_name'];
        if ($r->has('field_options_key')) {
            $key_update = [];
            foreach ($r->field_options_key as $k => $key) {
                if ($key != null && $r->field_options_value[$k] != null) {
                    $key_update[] = $key;
                    \App\Models\Attribute::updateOrCreate([
                        'key' => $key,
                        'table' => $table_import,
                        'type' => 'field_options',
                        'item_id' => $item->id
                    ], [
                        'value' => $r->field_options_value[$k]
                    ]);
                }
            }
            if (!empty($key_update)) {
                \App\Models\Attribute::where([
                    'table' => $table_import,
                    'type' => 'field_options',
                    'item_id' => $item->id
                ])->whereNotIn('key', $key_update)->delete();
            }
        } else {
            \App\Models\Attribute::where([
                'table' => $table_import,
                'type' => 'field_options',
                'item_id' => $item->id
            ])->delete();
        }
        return true;
    }

    public function processingImport($r, $item)
    {


// echo phpinfo();

// // Show just the module information.
// // phpinfo(8) yields identical results.
// echo phpinfo(INFO_MODULES);
// die;
        $table_import = $r->has('table') ? $r->table : $this->module['table_name'];
        $record_total = $record_success = 0;
        $dataInsertFix =\App\Models\Attribute::where('table', $table_import)->where('type', 'field_options')->where('item_id', @$item->id)->pluck('value', 'key')->toArray();

        // dd('public_html/filemanager/userfiles/' . $item->file, $item->file, $r, $dataInsertFix, $record_total, $record_success);



        echo '<a style="padding: 20px; background-color: blue; color: #FFF; font-weight: bold;" href="javascript:history.back()">Quay lại</a><br>';

        \Excel::load('public_html/filemanager/userfiles/' . $item->file, function ($reader) use ($r, $dataInsertFix, &$record_total, &$record_success) {
            
            $reader->each(function ($sheet) use ($r, $reader, $dataInsertFix, &$record_total, &$record_success) {
                
                if ($reader->getSheetCount() == 1) {
                    echo 'bắt đầu import <br>';
                    $result = $this->importItem($sheet, $r, $dataInsertFix);
                    if (isset($result['msg'])) {
                        echo '&nbsp;&nbsp;&nbsp;&nbsp; => '.$result['msg'].'<br>';
                    }

                    if ($result['status']) {
                        $record_total++;
                    }
                    if ($result['import']) {
                        $record_success++;
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;=> Import thành công<br>';
                    }
                } else {
                    $sheet->each(function ($row) use ($r, $dataInsertFix, &$model, &$record_total, &$record_success) {
                        $result = $this->importItem($row, $r, $dataInsertFix);
                        if ($result['status']) {
                            $record_total++;
                        }
                        if ($result['import']) {
                            $record_success++;
                        }
                    });
                }
            });
        });
        $item->record_total = $record_total;
        $item->record_success = $record_total;
        $item->save();
        return true;
    }

    //  Xử lý import 1 dòng excel
    public function importItem($row, $r, $dataInsertFix)
    {
        try {
            //  Kiểm tra trường dữ liêu bắt buộc có
            /*$fields_require = ['tel'];
            foreach ($fields_require as $field_require) {
                if (!isset($row->{$field_require}) || $row->{$field_require} == '' || $row->{$field_require} == null) {
                    return false;
                }
            }*/

            $row_empty = true;
            foreach ($row->all() as $key => $value) {
                if ($value != null) {
                    $row_empty = false;
                }
            }

            //  Các trường không được trùng
            /*$item_model = new $this->module['modal'];
            $item = $item_model->where('tel', $row->all()['tel'])->first();
            if (!is_object($item)) {
                $item = $item_model;
            }*/

            /*if ($this->import[$request->module]['unique']) {
                $field_name = $this->import[$request->module]['fields'][$this->import[$request->module]['unique']];
                $model_new = new $this->import[$request->module]['modal'];
                $model = $model_new->where($field_name, $row->{$this->import[$request->module]['unique']})->first();
            }*/

            if (!$row_empty) {
                $data = [];

                //  Gán các dữ liệu được fix cứng từ view
                foreach ($dataInsertFix as $k => $v) {
                    $data[$k] = $v;
                }

                //  Chèn các dữ liệu lấy vào từ excel
                foreach ($row->all() as $key => $value) {
                    switch ($key) {
                        case 'password': {
                            $data['password'] = bcrypt($value);
                            break;
                        }
                        default: {
                            if (\Schema::hasColumn($r->table, $key)) {
                                $data[$key] = $value;
                            }
                        }
                    }
                }
                if (DB::table($r->table)->insert($data)) {
                    return [
                        'status' => true,
                        'import' => true
                    ];
                }
            }
        } catch (\Exception $ex) {
            return [
                'status' => true,
                'import' => false,
                'msg' => $ex->getMessage()
            ];
        }
    }
}
