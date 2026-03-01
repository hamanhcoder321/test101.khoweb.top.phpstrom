<?php

namespace App\CRMDV\Controllers\Admin;

use App\CRMDV\Models\Bill;
use App\CRMDV\Models\Tag;
use App\Http\Helpers\CommonHelper;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use App\CRMDV\Controllers\Admin\CURDBaseController;
use Validator;

class ReceiptPaymentController extends CURDBaseController
{
    protected $orderByRaw = 'status ASC, date desc';

    protected $module = [
        'code' => 'receipt_payment',
        'table_name' => 'bill_receipts',
        'label' => 'Thu - chi',
        'modal' => '\App\CRMDV\Models\BillReceipts',
        'list' => [
            ['name' => 'date', 'type' => 'date_vi', 'label' => 'Ngày'],
            ['name' => 'price', 'type' => 'price_vi', 'label' => 'Số tiền'],
            ['name' => 'image', 'type' => 'image', 'label' => 'Ảnh CK'],
            ['name' => 'status', 'type' => 'status', 'label' => 'Duyệt phiếu'],
        ],
        'form' => [
            'general_tab' => [
                ['name' => 'date', 'type' => 'date', 'class' => 'required', 'label' => 'Ngày', 'value' => '', 'group_class' => 'col-md-3'],
                ['name' => 'receiving_account', 'type' => 'select2_model', 'label' => 'Tk nhận', 'model' => Tag::class,
                    'where' => 'type="bill_receipts"', 'object' => 'tag', 'display_field' => 'name', 'group_class' => 'col-md-3'],
                ['name' => 'bill_id', 'type' => 'text', 'class' => '', 'label' => 'ID hợp đồng', 'value' => '', 'group_class' => 'col-md-3'],
                ['name' => 'type', 'type' => 'select', 'options' => [
                    '' => '',
                    'luong' => 'Lương',
                    'dt' => 'Đầu tư',
                    'co_so_so' => 'Cơ sở số',
                    'luong_kd' => 'Lương KD',
                    'luong_kt' => 'Lương KT',
                    'phuc_loi' => 'Phúc lợi',
                    'co_so' => 'Cơ sở vật chất',
                    'khac' => 'Khác',
                ], 'class' => '', 'label' => 'Loại', 'group_class' => 'col-md-3'],
                ['name' => 'so_hoa_don', 'type' => 'number', 'class' => '', 'label' => 'Số hoá đơn', 'group_class' => 'col-sm-4'],
                ['name' => 'price', 'type' => 'price_vi', 'class' => 'required','label' => 'Số tiền giao dịch', 'group_class' => 'col-md-4'],
                ['name' => 'image', 'type' => 'file_image', 'class' => '', 'label' => 'Ảnh bằng chứng', 'group_class' => 'col-md-4'],
                ['name' => 'employees', 'type' => 'text', 'class' => '','label' => 'Tên người thực hiện', 'group_class' => 'col-md-4'],
                ['name' => 'note', 'type' => 'textarea', 'class' => 'required', 'label' => 'Lý do'],

            ],
        ],
    ];

    protected $quick_search = [
        'label' => 'ID, số tiền, lý do, người thực hiện, loại',
        'fields' => 'id, type, price, note, employees'
    ];

    protected $filter = [
        'admin_id' => [
            'label' => 'Người tạo',
            'type' => 'select2_ajax_model',
            'display_field' => 'name',
            'display_field2' => 'code',
            'model' => \App\Models\Admin::class,
            'object' => 'admin',
            'query_type' => '='
        ],
        'customer' => [
            'label' => 'Khách hàng',
            'type' => 'select2_ajax_model',
            'display_field' => 'name',
            'display_field2' => 'tel',
            'model' => User::class,
            'object' => 'user',
            'query_type' => 'custom'
        ],
        'thu_hay_chi' => [
            'label' => 'Thu hay chi',
            'type' => 'select',
            'options' => [
                '' => '',
                '>' => 'Thu',
                '<' => 'Chi',
            ],
            'query_type' => 'custom'
        ],
        'type' => [
            'label' => 'Loại',
            'type' => 'select',
            'options' => [
                '' => '',
                'luong' => 'Lương',
                'dt' => 'Đầu tư',
                'co_so_so' => 'Cơ sở số',
                'luong_kd' => 'Lương KD',
                'luong_kt' => 'Lương KT',
                'phuc_loi' => 'Phúc lợi',
                'co_so' => 'Cơ sở vật chất',
                'khac' => 'Khác',
            ],
            'query_type' => '='
        ],
        'receiving_account' => [
            'label' => 'Tk nhận',
            'type' => 'select2_model',
            'display_field' => 'name',
            'model' => Tag::class,
            'where' => 'type="bill_receipts"',
            'object' => 'tag',
            'query_type' => '='
        ],

        'status' => [
            'label' => 'Tình trạng duyệt',
            'type' => 'select',
            'options' => [
                '' => '',
                0 => 'Chưa duyệt',
                1 => 'Đã duyệt',
            ],
            'query_type' => '='
        ],
        'domain' => [
            'label' => 'Tên miền',
            'type' => 'text',
            'query_type' => 'custom'
        ],
        'filter_date' => [
            'label' => 'Lọc thời gian',
            'type' => 'filter_date',
            'options' => [
                '' => '',
                'date' => 'Ngày giao dịch',
                'created_at' => 'Ngày tạo',
            ],
            'query_type' => 'filter_date'
        ],
        'custom' => [
            'label' => 'Lọc khác',
            'type' => 'select',
            'options' => [
                '' => '',
                'Chưa điền số hoá đơn' => 'Chưa điền số hoá đơn',
            ],
            'query_type' => 'custom'
        ],
    ];

    public function getIndex(Request $request)
    {
        $data = $this->getDataList($request);

        return view('CRMDV.receipt_payment.list')->with($data);
    }

    public function thongKe($data, $listItem, $request) {
        $query = clone $listItem;
        $data['chua_duyet'] = $query->where('status', 0)->count();

        $query = clone $listItem;
        $data['thu_chua_duyet'] = $query->where('price', '>', 0)->where('status', 0)->sum('price');

        $query = clone $listItem;
        $data['chi_chua_duyet'] = $query->where('price', '<', 0)->where('status', 0)->sum('price');

        $query = clone $listItem;
        $data['tong_thu'] = $query->where('price', '>', 0)->sum('price');

        $query = clone $listItem;
        $data['tong_chi'] = $query->where('price', '<', 0)->sum('price');


        return $data;
    }

    /*public function getData($request) {
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
        return $listItem;
    }

    public function getDataList(Request $request) {
        $listItem = $this->getData($request);

        $data['record_total'] = $listItem->count();

        $data['tong_thu'] = $this->getData($request)->where('price', '>', 0)->sum('price');
        $data['tong_chi'] = $this->getData($request)->where('price', '<', 0)->sum('price');

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
    }*/

    public function appendWhere($query, $request)
    {
//        dd ($request->all());

        // Nếu ko phải super Admin thì chỉ truy vấn thu - chi trong 60 ngày trở lại
        if (\Auth::guard('admin')->user()->super_admin != 1) {
            $query = $query->where('created_at', '>', date('Y-m-d', strtotime('-90 day')));
        }

        if (@$request->thu_hay_chi != null) {
            $query = $query->where('price', $request->thu_hay_chi, 0);
        }

        if (@$request->domain != null) {
            $bill_ids = Bill::where('domain', 'like', '%'.$request->domain.'%')->pluck('id')->toArray();
            $query = $query->whereIn('bill_id', $bill_ids);
        }

        if (@$request->customer != null) {
//            $bill_ids = Bill::where('domain', 'like', '%'.$request->domain.'%')->pluck('id')->toArray();
//            $query = $query->whereIn('bill_id', $bill_ids);
        }

        if($request->has('custom') && $request->custom != '') {
            if ($request->custom == 'Chưa điền số hoá đơn') {
                $query = $query->where('so_hoa_don')->whereIn('receiving_account', [61, 68]);   //  id của tk ngân hàng cty hobasoft và hbweb
            }
        }




        return $query;
    }

    public function add(Request $request)
    {
        try {
            if (!$_POST) {
                $data = $this->getDataAdd($request);

                // set ngày mặc định là hôm nay
                foreach ($data['module']['form']['general_tab'] as &$field) {
                    if ($field['name'] === 'date' && empty($field['value'])) {
                        $field['value'] = date('Y-m-d');
                    }
                }

                return view('CRMDV.receipt_payment.add')->with($data);
            } else if ($_POST) {
                $validator = Validator::make($request->all(), [
//                    'value' => 'required'
                ], [
//                    'value.required' => 'Bắt buộc phải nhập giá trị',
                ]);
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                } else {
                    $data = $this->processingValueInFields($request, $this->getAllFormFiled());

                    //  Tùy chỉnh dữ liệu insert
                    $data['admin_id'] = $data['saler_id'] = \Auth::guard('admin')->user()->id;

                    // if ($request->file('image') != null) {
                    //     $data['image'] = CommonHelper::saveFile($request->file('image'), $this->module['code']);

                    // } else {
                    //     unset($data['image']);
                    // }

                    #

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
            return view('CRMDV.receipt_payment.edit')->with($data);
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

                    if ($item->bill_id != null) {
                        //  nếu là giao dịch của HĐ thì cập nhật tiền đã nhận của HĐ
                        $BillReceiptsController = new BillReceiptsController();
                        $BillReceiptsController->updateTienDaTraHD($item->bill_id);
                    }

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

                return redirect('admin/' . $this->module['code']);
            }
        }
    }

    public function getPublish(Request $request)
    {
        // dd($request->all());
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
                //  duyệt phiếu thu
                $item->{$request->column} = 1;
            } else {
                //  huỷ duyệt phiếu thu
                $item->{$request->column} = 0;
            }

            $item->save();

            if ($item->bill_id != null) {
                //  nếu là giao dịch của HĐ thì cập nhật tiền đã nhận của HĐ
                $BillReceiptsController = new BillReceiptsController();
                $BillReceiptsController->updateTienDaTraHD($item->bill_id);
            }

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

            $bill_id = $item->bill_id;

            $item->delete();

            if ($bill_id != null) {
                //  nếu là giao dịch của HĐ thì cập nhật tiền đã nhận của HĐ
                $BillReceiptsController = new BillReceiptsController();
                $BillReceiptsController->updateTienDaTraHD($bill_id);
            }

            CommonHelper::flushCache();
            CommonHelper::one_time_message('success', 'Xóa thành công!');

            return redirect('admin/' . $this->module['code']);
        } catch (\Exception $ex) {
            CommonHelper::one_time_message('error', 'Lỗi hệ thống! Vui lòng liên hệ kỹ thuật viên.');
            return back();
        }
    }

    /*public function multiDelete(Request $request)
    {
        try {
            $ids = $request->ids;
            if (is_array($ids)) {
                $this->model->whereIn('id', $ids)->delete();
            }
            CommonHelper::flushCache();
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
    }*/

}
