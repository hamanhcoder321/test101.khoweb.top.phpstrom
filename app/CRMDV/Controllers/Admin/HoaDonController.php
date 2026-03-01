<?php

namespace App\CRMDV\Controllers\Admin;

use App\CRMDV\Controllers\Admin\CURDBaseController;
use App\CRMDV\Models\HoaDon;
use App\Http\Helpers\CommonHelper;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class HoaDonController extends CURDBaseController
{
    protected $orderByRaw = 'ngay_ky desc';

    protected $module = [
        'code'       => 'hoa_don',
        'table_name' => 'hoa_don',
        'label'      => 'Hóa đơn',
        'modal'      => '\App\CRMDV\Models\HoaDon',

        'list' => [
            ['name' => 'ngay_ky', 'type' => 'datetime_vi', 'label' => 'Ngày ký'],
            ['name' => 'ky_hieu', 'type' => 'text', 'label' => 'Ký hiệu'],
            ['name' => 'so_hoa_don', 'type' => 'text_admin_edit', 'label' => 'Số hóa đơn'],
            ['name' => 'cty_name', 'type' => 'text_admin_edit', 'label' => 'Tên công ty'],
            ['name' => 'cty_mst', 'type' => 'text', 'label' => 'MST'],
            ['name' => 'tien_hang', 'type' => 'price_vi', 'label' => 'Tiền hàng'],
            ['name' => 'tien_thue_gtgt', 'type' => 'price_vi', 'label' => 'Thuế GTGT'],
            ['name' => 'tong_tien', 'type' => 'price_vi', 'label' => 'Tổng tiền'],
            ['name' => 'status_CQT', 'type' => 'text', 'label' => 'Trạng thái CQT'],
            ['name' => 'cong_ty', 'type' => 'text', 'label' => 'Công ty'],
            ['name' => 'status', 'type' => 'status', 'label' => 'Trạng thái '],
            [
                'name'  => 'bill_receipt_match',
                'type'  => 'custom',
                'label' => 'Phiếu thu',
                'td'    => 'CRMDV.hoa_don.td.bill_receipt_match'
            ],
            [
                'name'  => 'receipt_delay',
                'type'  => 'custom',
                'label' => 'Xuất Hóa Đơn',
                'td'    => 'CRMDV.hoa_don.td.receipt_delay'
            ],

        ],
        'form' => [
            'general_tab' => [

                [
                    'name' => 'ky_hieu',
                    'type' => 'text',
                    'class' => '',
                    'label' => 'Ký hiệu hóa đơn',
                    'group_class' => 'col-md-3',
                    'inner' => 'disabled'
                ],
                [
                    'name' => 'so_hoa_don',
                    'type' => 'text',
                    'class' => '',
                    'label' => 'Số hóa đơn',
                    'group_class' => 'col-md-3',
                    'inner' => 'disabled'
                ],
                [
                    'name' => 'ngay_ky',
                    'type' => 'date',
                    'class' => 'required',
                    'label' => 'Ngày ký hóa đơn',
                    'group_class' => 'col-md-3',
                    'inner' => 'disabled'
                ],
                [
                    'name' => 'cty_name',
                    'type' => 'text',
                    'class' => '',
                    'label' => 'Tên công ty',
                    'group_class' => 'col-md-6',
                    'inner' => 'disabled'
                ],
                [
                    'name' => 'cty_mst',
                    'type' => 'text',
                    'class' => '',
                    'label' => 'Mã số thuế',
                    'group_class' => 'col-md-6',
                    'inner' => 'disabled'
                ],

                [
                    'name' => 'tien_hang',
                    'type' => 'number',
                    'class' => 'required',
                    'label' => 'Tiền hàng',
                    'group_class' => 'col-md-3',
                    'inner' => 'disabled'
                ],
                [
                    'name' => 'tien_thue_gtgt',
                    'type' => 'number',
                    'class' => '',
                    'label' => 'Thuế GTGT',
                    'group_class' => 'col-md-3',
                    'inner' => 'disabled'
                ],
                [
                    'name' => 'tong_tien',
                    'type' => 'number',
                    'class' => 'required',
                    'label' => 'Tổng tiền',
                    'group_class' => 'col-md-3',
                    'inner' => 'disabled'
                ],
                [
                    'name' => 'status_CQT',
                    'type' => 'select',
                    'options' => [
                        '' => '---',
                        'Chưa gửi' => 'Chưa gửi',
                        'Đã gửi' => 'Đã gửi',
                        'CQT xác nhận' => 'CQT xác nhận',
                    ],
                    'label' => 'Trạng thái CQT',
                    'group_class' => 'col-md-4',
                    'inner' => 'disabled'
                ],
                ['name' => 'cong_ty', 'type' => 'text', 'class' => '', 'label' => 'Chi nhánh / Công ty', 'group_class' => 'col-md-3'],
                ['name' => 'status', 'type' => 'select', 'options' => [
                    0 => 'Chưa duyệt', 1 => 'Đã duyệt',
                ], 'class' => '', 'label' => 'Trạng thái duyệt', 'group_class' => 'col-md-3', 'value' => 0],
            ],
        ],

    ];

    protected $quick_search = [
        'label'  => 'Số hóa đơn, Ký hiệu, Tên công ty, MST, Tiền hàng, Tổng tiền',
        'fields' => 'hoa_don.so_hoa_don, hoa_don.ky_hieu, hoa_don.cty_name, hoa_don.cty_mst, hoa_don.tong_tien'
    ];


    protected $filter = [
        'ngay_ky' => [
            'label' => 'Ngày ký',
            'type'  => 'from_to_date',
            'query_type' => 'from_to_date'
        ],

        'status_CQT' => [
            'label' => 'Trạng thái CQT',
            'type'  => 'select',
            'options' => [
                '' => 'Tất cả',
                'Chưa gửi' => 'Chưa gửi',
                'Hóa đơn sai sót' => 'Hóa đơn sai sót',
                'CQT xác nhận' => 'CQT xác nhận',
            ],
            'query_type' => 'custom'
        ],


        /* ===== PHIẾU THU ===== */
        'bill_receipt' => [
            'label' => 'Phiếu thu',
            'type'  => 'select',
            'options' => [
                ''  => 'Tất cả',
                '1' => 'Đã có phiếu thu',
                '0' => 'Chưa có phiếu thu',
            ],
            'query_type' => 'custom'
        ],

        'invoice_delay' => [
            'label' => 'Tình trạng thu tiền',
            'type'  => 'select',
            'options' => [
                ''        => 'Tất cả',
                'none'    => 'Chưa thu',
                'ontime'  => 'Thu đúng ngày',
                'late'    => 'Thu chậm',
                'early'   => 'Thu sớm',
            ],
            'query_type' => 'custom'
        ],

        'sort' => [
            'label' => 'Sắp xếp',
            'type'  => 'select',
            'options' => [
                ''                    => 'Mặc định',
                'invoice_delay_desc'  => 'Thu chậm nhiều → ít',
                'invoice_delay_asc'   => 'Thu sớm → đúng → chậm',
            ],
            'query_type' => 'custom'
        ]

    ];


    public function add(Request $request)
    {
        try {
            if (!$request->isMethod('post')) {
                $data = $this->getDataAdd($request);
                return view('CRMDV.hoa_don.add')->with($data);
            }

            $validator = Validator::make($request->all(), [
                'cty_name'   => 'required',
                'ngay_ky'    => 'required',
                'tong_tien'  => 'required',
            ], [
                'cty_name.required'  => 'Bắt buộc nhập tên công ty',
                'ngay_ky.required'   => 'Bắt buộc nhập ngày ký',
                'tong_tien.required' => 'Bắt buộc nhập tổng tiền',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $data = $this->processingValueInFields(
                $request,
                $this->getAllFormFiled()
            );

            foreach ($data as $k => $v) {
                $this->model->$k = $v;
            }

            if ($this->model->save()) {
                CommonHelper::flushCache();
                CommonHelper::one_time_message('success', 'Thêm hóa đơn thành công');
            } else {
                CommonHelper::one_time_message('error', 'Lỗi khi thêm hóa đơn');
            }

            return redirect('admin/hoa_don');

        } catch (\Exception $ex) {
            CommonHelper::one_time_message('error', $ex->getMessage());
            return back()->withInput();
        }
    }

    public function update(Request $request)
    {
        $item = $this->model->find($request->id);
        if (!is_object($item)) abort(404);

        if (!$request->isMethod('post')) {
            $data = $this->getDataUpdate($request, $item);
            return view('CRMDV.hoa_don.edit')->with($data);
        }

        $validator = Validator::make($request->all(), [
            // 'value' => 'required'
        ], [
            // 'value.required' => 'Bắt buộc phải nhập tên',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $this->processingValueInFields(
            $request,
            $this->getAllFormFiled()
        );

        foreach ($data as $k => $v) {
            $item->$k = $v;
        }

        if ($item->save()) {
            CommonHelper::flushCache();
            CommonHelper::one_time_message('success', 'Cập nhật hóa đơn thành công');
        } else {
            CommonHelper::one_time_message('error', 'Lỗi cập nhật hóa đơn');
        }


        if ($request->return_direct == 'save_continue') {
            return redirect('admin/' . $this->module['code'] . '/edit/' . $item->id);
        } elseif ($request->return_direct == 'save_create') {
            return redirect('admin/' . $this->module['code'] . '/add');
        }

        return redirect('admin/' . $this->module['code'] . '/edit/' . $item->id);
    }
    public function delete(Request $request)
    {
        try {
            $item = $this->model->find($request->id);
            if (!is_object($item)) abort(404);

            $item->delete();

            CommonHelper::flushCache();
            CommonHelper::one_time_message('success', 'Xóa hóa đơn thành công');

            return redirect('admin/hoa_don');

        } catch (\Exception $ex) {
            CommonHelper::one_time_message('error', 'Không thể xóa hóa đơn');
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
            CommonHelper::one_time_message('success', 'Xóa thành công!');
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

    public function getIndex(Request $request)
    {
        $data = $this->getDataList($request);
        return view('CRMDV.hoa_don.list')->with($data);
    }

    public function appendWhere($query, $request)
    {
        if ($request->filled('status_CQT')) {
            $query->where('hoa_don.status_CQT', $request->status_CQT);
        }


        $query->leftJoin('bill_receipts as br', function ($join) {
        $join->on(
            DB::raw("TRIM(LEADING '0' FROM br.so_hoa_don)"),
            '=',
            DB::raw("TRIM(LEADING '0' FROM hoa_don.so_hoa_don)")
        )
            ->whereNull('br.deleted_at');
//            ->where('hoa_don.status_CQT', 'CQT Xác nhận');
    });



        // SELECT thêm cột phụ trợ
        $query->addSelect(
            'hoa_don.*',

            DB::raw('hoa_don.id as hoa_don_id'),   // ✅ THÊM DÒNG NÀY

            DB::raw('br.id as bill_receipt_id'),
            DB::raw('br.date as bill_receipt_date'),
//            DB::raw('br.so_hoa_don as so_hoa_don'),

            DB::raw('DATEDIFF(br.date, hoa_don.ngay_ky) as delay_days')
        );


        /*
        |--------------------------------------------------------------------------
        | FILTER: PHIẾU THU
        |--------------------------------------------------------------------------
        */
        if ($request->get('bill_receipt') === '1') {
            // Đã có phiếu thu
            $query->whereNotNull('br.id');
        }

        if ($request->get('bill_receipt') === '0') {
            // Chưa có phiếu thu
            $query->whereNull('br.id');
        }

        // ===== FILTER PHIẾU THU / THU TIỀN =====
        if ($request->invoice_delay === 'none') {
            // Chưa có phiếu thu
            $query->whereNull('br.date');
        }

        if ($request->invoice_delay === 'late') {
            // Thu tiền SAU ngày ký HĐ (thu chậm)
            $query->whereNotNull('br.date')
                ->whereRaw('DATE(br.date) > DATE(hoa_don.ngay_ky)');
        }

        if ($request->invoice_delay === 'ontime') {
            // Thu đúng ngày
            $query->whereNotNull('br.date')
                ->whereRaw('DATE(br.date) = DATE(hoa_don.ngay_ky)');
        }

        if ($request->invoice_delay === 'early') {
            // Thu sớm
            $query->whereNotNull('br.date')
                ->whereRaw('DATE(br.date) < DATE(hoa_don.ngay_ky)');
        }

// ===== SORT THEO SỐ NGÀY CHẬM =====
        if ($request->sort === 'invoice_delay_desc') {
            $query->whereNotNull('br.date')
                ->orderByRaw('DATEDIFF(br.date, hoa_don.ngay_ky) DESC');
        }

        if ($request->sort === 'invoice_delay_asc') {
            $query->whereNotNull('br.date')
                ->orderByRaw('DATEDIFF(br.date, hoa_don.ngay_ky) ASC');
        }


        return $query;
    }


    public function getPublish(Request $request)
    {
        try {
            DB::beginTransaction();

            $id = $request->get('id', 0);
            $item = $this->model->find($id);

            if (!is_object($item)) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Không tìm thấy bản ghi'
                ]);
            }

            if ($item->{$request->column} == 0) {
                $item->{$request->column} = 1;
            } else {
                $item->{$request->column} = 0;
            }

            $item->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'published' => $item->{$request->column} == 1
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'published' => null,
                'msg' => $ex->getMessage()
            ]);
        }
    }



    public function importExcel(Request $request)
    {
        if (!$request->isMethod('post')) {
            return view('CRMDV.hoa_don.import');
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file'
        ], [
            'file.required' => 'Vui lòng chọn file Excel'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('file');
        $fileName = time() . '_' . str_replace(' ', '', $file->getClientOriginalName());

        $file->move(
            base_path('public_html/filemanager/userfiles/imports'),
            $fileName
        );

        session()->put('hd_import_file', 'imports/' . $fileName);
        session()->forget('hd_preview_logs');

        return redirect('/admin/hoa_don/import-log');
    }
    public function importLog()
    {
        if (!session()->has('hd_preview_logs')) {
            return $this->previewHoaDonImport();
        }

        return view('CRMDV.hoa_don.import_log', [
            'rows' => session('hd_preview_logs', [])
        ]);
    }
    public function previewHoaDonImport()
    {
        $rows = [];
        $filePath = session('hd_import_file');

        if (!$filePath) {
            return redirect('/admin/hoa_don/import-excel');
        }

        try {

            \Excel::load(
                base_path('public_html/filemanager/userfiles/' . $filePath),
                function ($reader) use (&$rows) {

                    $reader->get()->each(function ($row, $i) use (&$rows) {
                        $result = $this->previewHoaDonRow($row);

                        if (!empty($result['skip'])) {
                            return;
                        }

                        $rows[] = [
                            'row'  => $i + 1,
                            'ok'   => $result['ok'],
                            'msg'  => $result['msg'],
                            'data' => $result['data'] ?? [],
                            'raw'  => $row->all(),
                        ];
                    });
                }
            );

        } catch (\Exception $e) {

            if (str_contains($e->getMessage(), 'Sheet code name cannot be empty')) {
                return redirect('/admin/hoa_don/import-excel')
                    ->withErrors([
                        'excel' => 'File Excel này được tạo từ phần mềm khác và chưa chuẩn. 
Vui lòng mở file bằng Microsoft Excel và chọn **Save As → Excel Workbook (.xlsx)** rồi upload lại.'
                    ]);
            }

            throw $e; // lỗi khác thì throw tiếp
        }

        session()->put('hd_preview_logs', $rows);

        return view('CRMDV.hoa_don.import_log', compact('rows'));
    }


    public function previewHoaDonRow($row)
    {
        $rowData = $row->all();

        if (trim(implode('', $rowData)) === '') {
            return ['ok' => false, 'skip' => true, 'msg' => 'Dòng trống'];
        }

        $ngayLap = $this->parseDate($rowData['ngay_lap'] ?? null);
        $ngayKy  = $this->parseDate($rowData['ngay_ky'] ?? null);

        if (!$ngayKy && $ngayLap) {
            $ngayKy = $ngayLap;
        }

        if (!$ngayKy) {
            return [
                'ok'  => false,
                'msg' => 'Thiếu ngày ký / ngày lập'
            ];
        }

        $tongTien = $this->moneyToNumber(
            $rowData['tong_cong_tien_thanh_toan'] ?? 0
        );

        if ($tongTien <= 0) {
            return [
                'ok'  => false,
                'msg' => 'Tổng tiền không hợp lệ'
            ];
        }

        // ===== so_hoa_don =====
        $soHoaDonRaw = trim($rowData['so_hoa_don'] ?? '');
        if ($soHoaDonRaw === '') {
            return [
                'ok'  => false,
                'msg' => 'Thiếu số hóa đơn'
            ];
        }
        $soHoaDonCmp = ltrim($soHoaDonRaw, '0'); // CHỈ DÙNG ĐỂ CHECK

        $data = [
            'cty_name'       => trim($rowData['ten_cong_ty'] ?? ''),
            'cty_mst'        => trim($rowData['ma_so_thue'] ?? ''),
            'so_hoa_don'     => $soHoaDonRaw, // LƯU NGUYÊN BẢN EXCEL
            'tien_hang'      => $this->moneyToNumber($rowData['cong_tien_hang'] ?? 0),
            'tien_thue_gtgt' => $this->moneyToNumber($rowData['tien_thue_gtgt'] ?? 0),
            'tong_tien'      => $tongTien,
            'status_CQT'     => trim($rowData['trang_thai_cqt'] ?? ''),
            'ngay_ky'        => $ngayKy,
        ];

        // ===== CHECK TRÙNG theo so_hoa_don + YEAR(ngay_ky) =====
        // Lý do: mỗi năm số hóa đơn chạy lại từ 1, nên số 35/2025 ≠ số 35/2026
        $namKy = \Carbon\Carbon::parse($ngayKy)->year;
        $existRow = \DB::table('hoa_don')
            ->select('id', 'status_CQT')
            ->whereRaw(
                "TRIM(LEADING '0' FROM so_hoa_don) = ?",
                [$soHoaDonCmp]
            )
            ->whereYear('ngay_ky', $namKy)
            ->first();

        if ($existRow) {
            return [
                'ok'     => true,
                'msg'    => 'Hóa đơn đã tồn tại (số ' . $soHoaDonRaw . ' năm ' . $namKy . ') → cập nhật trạng thái CQT',
                'action' => 'update',
                'id'     => $existRow->id,
                'data'   => $data
            ];
        }

        return [
            'ok'     => true,
            'msg'    => 'Hóa đơn mới',
            'action' => 'insert',
            'data'   => $data
        ];
    }

    public function commitImport()
    {
        $rows = session('hd_preview_logs', []);

        foreach ($rows as $row) {

            if (empty($row['ok']) || empty($row['data'])) {
                continue;
            }

            $data = $row['data'];

            // ===== CHECK TRÙNG theo so_hoa_don + YEAR(ngay_ky) =====
            $soHoaDonRaw = $data['so_hoa_don'];
            $soHoaDonCmp = ltrim($soHoaDonRaw, '0');
            $namKy       = \Carbon\Carbon::parse($data['ngay_ky'])->year;

            $existRow = \DB::table('hoa_don')
                ->select('id', 'status_CQT')
                ->whereRaw(
                    "TRIM(LEADING '0' FROM so_hoa_don) = ?",
                    [$soHoaDonCmp]
                )
                ->whereYear('ngay_ky', $namKy)
                ->first();

            if ($existRow) {

                if (trim($existRow->status_CQT) !== trim($data['status_CQT'])) {
                    \DB::table('hoa_don')
                        ->where('id', $existRow->id)
                        ->update([
                            'status_CQT' => trim($data['status_CQT']),
                            'updated_at' => now(),
                        ]);
                }

                continue;
            }
            // ===== INSERT MỚI – GIỮ NGUYÊN so_hoa_don =====
            \DB::table('hoa_don')->insert([
                'cty_name'       => $data['cty_name'],
                'cty_mst'        => $data['cty_mst'],
                'so_hoa_don'     => $soHoaDonRaw, // NGUYÊN BẢN
                'tien_hang'      => $data['tien_hang'],
                'tien_thue_gtgt' => $data['tien_thue_gtgt'],
                'tong_tien'      => $data['tong_tien'],
                'status_CQT'     => trim($data['status_CQT']),
                'ngay_ky'        => $data['ngay_ky'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        session()->forget(['hd_preview_logs', 'hd_import_file']);

        CommonHelper::one_time_message('success', 'Đã import hóa đơn thành công');
        return redirect('/admin/hoa_don');
    }


    protected function moneyToNumber($value)
    {
        if ($value === null) {
            return 0;
        }

        // bỏ dấu chấm, phẩy, chữ
        // 3.800.000,00 → 3800000
        $v = preg_replace('/[^\d]/', '', (string)$value);

        return (float)$v;
    }
    protected function parseDateTime($value)
    {


        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject($value)
                ->format('Y-m-d H:i:s');
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }

            try {
                return Carbon::parse($value)->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }



protected function parseDate($value)
{
    // 1. null
    if ($value === null) {
        return null;
    }

    // 2. Carbon / DateTime (Laravel Excel mới)
    if ($value instanceof \DateTimeInterface) {
        return $value->format('Y-m-d');
    }

    // 3. Excel serial number
    if (is_numeric($value)) {
        if ((float)$value < 1) {
            return null;
        }

        return ExcelDate::excelToDateTimeObject($value)
            ->format('Y-m-d');
    }

    // 4. String (file Excel CŨ)
    if (is_string($value)) {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        // 4.1 d/m/Y
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
            return Carbon::createFromFormat('d/m/Y', $value)
                ->format('Y-m-d');
        }

        // 4.2 d/m/Y H:i:s
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}\s+\d{2}:\d{2}:\d{2}$/', $value)) {
            return Carbon::createFromFormat('d/m/Y H:i:s', $value)
                ->format('Y-m-d');
        }

        // 4.3 fallback ISO
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    return null;
}


}
