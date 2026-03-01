<?php

namespace App\CRMDV\Controllers\Admin;

use App\CRMDV\Controllers\Admin\CURDBaseController;
use App\CRMDV\Models\Bill;
use App\Http\Helpers\CommonHelper;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;
class GiaoDichController extends CURDBaseController
{
    protected $orderByRaw = 'transaction_date desc';

    protected $module = [
        'code' => 'giao_dich',
        'table_name' => 'giao_dich',
        'label' => 'Sao kê ngân hàng',
        'modal' => '\App\CRMDV\Models\GiaoDich',

        'list' => [
            ['name' => 'transaction_date', 'type' => 'datetime_vi', 'label' => 'Ngày giao dịch'],
            ['name' => 'transaction_number', 'type' => 'text_admin_edit', 'label' => 'Số GD'],
            ['name' => 'transaction_content', 'type' => 'text', 'label' => 'Nội dung'],
            ['name' => 'amount', 'type' => 'price_vi', 'label' => 'Số tiền'],
            ['name' => 'tk_doi_ung_ten', 'type' => 'text', 'label' => 'Tên đối ứng',],
            ['name' => 'cong_ty', 'type' => 'text', 'label' => 'Công ty'],
            ['name' => 'status', 'type' => 'status', 'label' => 'Trạng thái '],


            [
                'name'  => 'bill_receipt_match',
                'type'  => 'custom',
                'label' => 'Phiếu thu',
                'td'    => 'CRMDV.giao_dich.td.bill_receipt_match'
            ],

            [
                'name'  => 'invoice_delay',
                'type'  => 'custom',
                'label' => 'Xuất Hóa Đơn',
                'td'    => 'CRMDV.giao_dich.td.invoice_delay'
            ],
        ],



        'form' => [
            'general_tab' => [
                ['name' => 'transaction_date', 'type' => 'datetime-local', 'class' => 'required', 'label' => 'Ngày giao dịch', 'group_class' => 'col-md-3',  'inner' => 'disabled'],
                ['name' => 'transaction_number', 'type' => 'number', 'class' => 'required', 'label' => 'Số giao dịch', 'group_class' => 'col-md-3',  'inner' => 'disabled'],
                ['name' => 'amount', 'type' => 'price_vi', 'class' => 'required', 'label' => 'Số tiền gửi vào', 'group_class' => 'col-md-3',  'inner' => 'disabled'],
                ['name' => 'tk_doi_ung_ten', 'type' => 'text', 'class' => 'required', 'label' => 'Tên TK đối ứng', 'group_class' => 'col-md-4',  'inner' => 'disabled'],
                ['name' => 'tk_doi_ung', 'type' => 'text', 'class' => 'required', 'label' => 'TK đối ứng', 'group_class' => 'col-md-4',  'inner' => 'disabled'],
                ['name' => 'transaction_content', 'type' => 'text', 'class' => 'required', 'label' => 'Nội dung giao dịch', 'group_class' => 'col-md-12',  'inner' => 'disabled'],
                ['name' => 'cong_ty', 'type' => 'text', 'class' => '', 'label' => 'Chi nhánh / Công ty', 'group_class' => 'col-md-3'],
                ['name' => 'status', 'type' => 'select', 'options' => [
                    0 => 'Chưa duyệt', 1 => 'Đã duyệt',
                ], 'class' => '', 'label' => 'Trạng thái duyệt', 'group_class' => 'col-md-3', 'value' => 0],
                ['name' => 'note', 'type' => 'text', 'label' => 'Ghi chú', 'group_class' => 'col-md-12'],
                ['name' => 'ko_xuat_hoa_don', 'type' => 'select', 'options' => [
                    0 => 'Phải xuất hoá đơn', 1 => 'Không cần xuất hoá đơn',
                ], 'class' => '', 'label' => 'Có phải xuất hoá đơn không?', 'group_class' => 'col-md-3', 'value' => 0],

            ],
        ],
    ];
    protected $quick_search = [
        'label'  => 'Nội dung, Số giao dịch, Tên đối ứng, Số tiền',
        'fields' => 'giao_dich.transaction_content, giao_dich.transaction_number, giao_dich.tk_doi_ung_ten, giao_dich.tk_doi_ung, giao_dich.amount'
    ];


    protected $filter = [
        'transaction_date' => [
            'label' => 'Ngày giao dịch',
            'type' => 'from_to_date',
            'query_type' => 'from_to_date'
        ],

        'status' => [
            'label' => 'Tình trạng duyệt',
            'type' => 'select',
            'options' => [
                '' => 'Tất cả',
                0 => 'Chưa duyệt',
                1 => 'Đã duyệt',
            ],
            'query_type' => '='
        ],

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

        // ⭐ FILTER MỚI: HÓA ĐƠN XUẤT CHẬM
        'invoice_delay' => [
            'label' => 'Xuất hóa đơn',
            'type'  => 'select',
            'options' => [
                ''  => 'Tất cả',
                'late' => 'Xuất chậm',
                'ontime' => 'Đúng / sớm',
                'none' => 'Chưa xuất HĐ',
                'Không cần xuất hoá đơn' => 'Không cần xuất hoá đơn',
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


    public function getIndex(Request $request)
    {
        $data = $this->getDataList($request);

        return view('CRMDV.giao_dich.list')->with($data);
    }

    public function appendWhere($query, $request)
    {
        // Phiếu thu
        $query->leftJoin('bill_receipts as br', function ($join) {
            $join->on(
                \DB::raw('DATE(giao_dich.transaction_date)'),
                '=',
                \DB::raw('DATE(br.date)')
            )
                ->on('giao_dich.amount', '=', 'br.price')
                ->whereNull('br.deleted_at');
        });

        $query->leftJoin('hoa_don as hd', function ($join) {
            $join->on(
                DB::raw("TRIM(LEADING '0' FROM hd.so_hoa_don)"),
                '=',
                DB::raw("TRIM(LEADING '0' FROM br.so_hoa_don)")
            )
                ->where('hd.status_CQT', 'CQT Xác nhận');
        });


        $query->addSelect(
            'giao_dich.*',
            DB::raw('br.id as bill_receipt_id'),
            DB::raw('hd.ngay_ky as hoa_don_date'),
            DB::raw('hd.so_hoa_don as so_hoa_don'),
    DB::raw('DATEDIFF(hd.ngay_ky, br.date) as invoice_delay_days')
        );


        // ===== FILTER XUẤT HÓA ĐƠN =====
        if ($request->invoice_delay === 'none') {
            $query->whereNull('hd.ngay_ky')->where('ko_xuat_hoa_don', '!=', 1);

        } elseif ($request->invoice_delay === 'Không cần xuất hoá đơn') {
            $query->where('ko_xuat_hoa_don', 1);

        }

        if ($request->invoice_delay === 'late') {
            $query->whereNotNull('hd.ngay_ky')
                ->whereRaw('DATE(hd.ngay_ky) > DATE(giao_dich.transaction_date)');
        }

        if ($request->invoice_delay === 'ontime') {
            $query->whereNotNull('hd.ngay_ky')
                ->whereRaw('DATE(hd.ngay_ky) <= DATE(giao_dich.transaction_date)');
        }

        // ===== SORT =====
        if ($request->sort === 'invoice_delay_desc') {
            $query->whereNotNull('hd.ngay_ky')
                ->orderByRaw('DATEDIFF(hd.ngay_ky, giao_dich.transaction_date) DESC');
        }

        if ($request->sort === 'invoice_delay_asc') {
            $query->whereNotNull('hd.ngay_ky')
                ->orderByRaw('DATEDIFF(hd.ngay_ky, giao_dich.transaction_date) ASC');
        }
        // ===== SORT XUẤT HÓA ĐƠN =====
        if ($request->sort === 'invoice_delay_desc') {
            $query->whereNotNull('hd.ngay_ky')
                ->orderByRaw('DATEDIFF(hd.ngay_ky, giao_dich.transaction_date) DESC');
        }

        if ($request->sort === 'invoice_delay_asc') {
            $query->whereNotNull('hd.ngay_ky')
                ->orderByRaw('DATEDIFF(hd.ngay_ky, giao_dich.transaction_date) ASC');
        }

// ===== FILTER PHIẾU THU =====
        if ($request->bill_receipt === '1') {
            // ĐÃ có phiếu thu
            $query->whereNotNull('br.id');
        }

        if ($request->bill_receipt === '0') {
            // CHƯA có phiếu thu
            $query->whereNull('br.id');
        }

        return $query;
    }





    public function add(Request $request)
    {
        try {
            if (!$_POST) {
                $data = $this->getDataAdd($request);
                return view('CRMDV.giao_dich.add')->with($data);
            } else if ($_POST) {
                $validator = Validator::make($request->all(), [
                    'transaction_date' => 'required',
                    'transaction_number' => 'required',
                    'amount' => 'required',
                    'transaction_content' => 'required',
                    'tk_doi_ung_ten' => 'required',
                    'tk_doi_ung' => 'required',
                ], [
                    'transaction_date.required' => 'Bắt buộc phải nhập giá trị',
                    'transaction_number.required' => 'Bắt buộc phải nhập giá trị',
                    'amount.required' => 'Bắt buộc phải nhập giá trị',
                    'transaction_content.required' => 'Bắt buộc phải nhập giá trị',
                    'tk_doi_ung_ten.required' => 'Bắt buộc phải nhập giá trị',
                    'tk_doi_ung.required' => 'Bắt buộc phải nhập giá trị',
                ]);
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                } else {
                    $data = $this->processingValueInFields($request, $this->getAllFormFiled());

                    //  Tùy chỉnh dữ liệu insert

                    foreach ($data as $k => $v) {
                        $this->model->$k = $v;
                    }

                    if ($this->model->save()) {
                        CommonHelper::flushCache();
                        CommonHelper::one_time_message('success', 'Tạo mới thành công!');
                    } else {
                        CommonHelper::one_time_message('error', 'Lỗi tao mới. Vui lòng load lại trang và thử lại!');
                    }

                    if ($request->ajax()) {
                        return response()->json([
                            'status' => true,
                            'msg' => '',
                            'data' => $this->model
                        ]);
                    }

                    return redirect('admin/' . $this->module['code']);
                }
            }
        } catch (\Exception $ex) {
            CommonHelper::one_time_message('error', $ex->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function update(Request $request)
    {
        $item = $this->model->find($request->id);

        if (!is_object($item)) abort(404);
        if (!$_POST) {
            $data = $this->getDataUpdate($request, $item);
            return view('CRMDV.giao_dich.edit')->with($data);
        } else if ($_POST) {
            $validator = Validator::make($request->all(), [
                // 'value' => 'required'
            ], [
                // 'value.required' => 'Bắt buộc phải nhập tên',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                $data = $this->processingValueInFields($request, $this->getAllFormFiled());

                #

                foreach ($data as $k => $v) {
                    $item->$k = $v;
                }
                if ($item->save()) {
                    CommonHelper::flushCache();
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

                return redirect('admin/' . $this->module['code'] . '/edit/' . $item->id);
            }
        }
    }



    public function getPublish(Request $request)
    {
        try {

            \DB::beginTransaction();
            $id = $request->get('id', 0);
            $item = $this->model->find($id);

            if (!is_object($item))
                return response()->json([
                    'status' => false,
                    'msg' => 'Không tìm thấy bản ghi'
                ]);

            if ($item->{$request->column} == 0) {
                $item->{$request->column} = 1;
            } else {
                $item->{$request->column} = 0;
            }

            $item->save();

            \DB::commit();

            return response()->json([
                'status' => true,
                'published' => $item->{$request->column} == 1 ? true : false
            ]);
        } catch (\Exception $ex) {
            \DB::rollback();
            return response()->json([
                'status' => false,
                'published' => null,
                'msg' => $ex->getMessage()
            ]);
        }
    }

    public function delete(Request $request)
    {
        try {
            $item = $this->model->find($request->id);

            $item->delete();

            CommonHelper::flushCache();
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

    public function importExcel(Request $request)
    {
        if (!$request->isMethod('post')) {
            return view('CRMDV.giao_dich.import');
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

        session()->put('gd_import_file', 'imports/' . $fileName);
        session()->forget('gd_preview_logs');

        return redirect('/admin/giao_dich/import-log');
    }

    public function importLog()
    {
        if (!session()->has('gd_preview_logs')) {
            return $this->previewGiaoDichImport();
        }

        return view('CRMDV.giao_dich.import_log', [
            'rows' => session('gd_preview_logs', [])
        ]);
    }


    public function previewGiaoDichImport()
    {
        $rows = [];
        $filePath = session('gd_import_file');

        if (!$filePath) {
            return redirect('/admin/giao_dich/import-excel');
        }

        \Excel::load(
            base_path('public_html/filemanager/userfiles/' . $filePath),
            function ($reader) use (&$rows) {

                $reader->get()->each(function ($row, $i) use (&$rows) {

                    $result = $this->previewGiaoDichRow($row);

                    // BỎ DÒNG SKIP (trống / tiền ra)
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

        session()->put('gd_preview_logs', $rows);

        return view('CRMDV.giao_dich.import_log', compact('rows'));
    }


    public function previewGiaoDichRow($row)
    {
        $rowData = $row->all();

        // 1. Bỏ dòng trống
        if (trim(implode('', $rowData)) === '') {
            return ['ok' => false, 'skip' => true, 'msg' => 'Dòng trống'];
        }

        // 2. Lấy tiền gửi / rút
        $amountIn  = $this->moneyToNumber($rowData['so_tien_gui_vao'] ?? 0);
        $amountOut = $this->moneyToNumber($rowData['so_tien_rut_ra'] ?? 0);

        // 3. Chỉ lấy TIỀN VÀO
        if ($amountIn <= 0) {
            return [
                'ok'   => false,
                'skip' => true,
                'msg'  => 'Không phải tiền vào'
            ];
        }

        // 4. Parse ngày giao dịch
        $transactionDate = $this->parseDateTime($rowData['ngay_giao_dich'] ?? null);

        if (!$transactionDate) {
            return [
                'ok'   => false,
                'skip' => false,
                'msg'  => 'Ngày giao dịch không hợp lệ'
            ];
        }

        // 5. Chuẩn hóa data
        $data = [
            'transaction_date'    => $transactionDate,
            'transaction_number'  => trim($rowData['so_gd'] ?? ''),
            'transaction_content' => trim($rowData['noi_dung_giao_dich'] ?? ''),
            'amount'              => $amountIn,
            'tk_doi_ung_ten'      => trim($rowData['ten_tk_doi_ung'] ?? 'Chưa rõ'),
            'tk_doi_ung'          => trim($rowData['tk_doi_ung'] ?? 'Chưa rõ'),
        ];

        /*
         |--------------------------------------------------------------------------
         | 6. CHECK TRÙNG (LINH HOẠT)
         | - Ưu tiên: date + amount + tk_doi_ung
         | - Fallback: date + amount + tk_doi_ung_ten
         | - Cuối cùng: date + amount
         |--------------------------------------------------------------------------
         */
        $query = \DB::table('giao_dich')
            ->whereDate('transaction_date', date('Y-m-d', strtotime($transactionDate)))
            ->where('amount', $amountIn);

        if (!empty($data['tk_doi_ung']) && $data['tk_doi_ung'] !== 'Chưa rõ') {
            $query->where('tk_doi_ung', $data['tk_doi_ung']);
        } elseif (!empty($data['tk_doi_ung_ten']) && $data['tk_doi_ung_ten'] !== 'Chưa rõ') {
            $query->where('tk_doi_ung_ten', $data['tk_doi_ung_ten']);
        }
        // else: chỉ check date + amount

        $exists = $query->exists();

        if ($exists) {
            return [
                'ok'   => false,
                'skip' => false,
                'msg'  => 'Giao dịch đã tồn tại',
                'data' => $data
            ];
        }

        return [
            'ok'   => true,
            'skip' => false,
            'msg'  => 'Hợp lệ',
            'data' => $data
        ];
    }






    public function commitImport()
    {
        $rows = session('gd_preview_logs', []);

        foreach ($rows as $row) {
            if (!empty($row['ok']) && !empty($row['data'])) {
                \DB::table('giao_dich')->insert([
                    'transaction_date'    => $row['data']['transaction_date'],
                    'transaction_number'  => $row['data']['transaction_number'],
                    'transaction_content' => $row['data']['transaction_content'],
                    'amount'              => $row['data']['amount'],
                    'tk_doi_ung_ten'      => $row['data']['tk_doi_ung_ten'],
                    'tk_doi_ung'          => $row['data']['tk_doi_ung'],
                    'status'              => 0,
                    'bill_receipts_check' => 0,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
        }

        session()->forget(['gd_preview_logs', 'gd_import_file']);

        CommonHelper::one_time_message('success', 'Đã lưu các giao dịch hợp lệ');
        return redirect('/admin/giao_dich');
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
        if (empty($value)) {
            return null;
        }

        try {
            // Excel có thể là d/m/Y hoặc d/m/Y H:i:s
            return \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', trim($value))
                ->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            try {
                return \Carbon\Carbon::createFromFormat('d/m/Y', trim($value))
                    ->format('Y-m-d 00:00:00');
            } catch (\Exception $e2) {
                return null;
            }
        }
    }
    protected function parseDate($value)
    {
        try {
            return \Carbon\Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

}
