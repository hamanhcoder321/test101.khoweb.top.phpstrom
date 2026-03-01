<?php

namespace App\CRMDV\Controllers\Admin;

use App\CRMDV\Models\Bill;
use App\CRMDV\Models\Lead;
use App\CRMDV\Models\Timekeeper;
use App\Http\Helpers\CommonHelper;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\CRMDV\Models\Category;
use App\CRMDV\Models\Codes;
use App\CRMDV\Models\HoaDon;
use App\CRMDV\Models\Theme;
use App\CRMDV\Models\Tag;
use Validator;
use App\CRMDV\Models\PostTag;
use App\CRMDV\Models\BillProgress;
use DB;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Collections\CellCollection;

class CodesController extends CURDBaseController
{

    protected $module = [
        'code' => 'codes',
        'table_name' => 'codes',
        'label' => 'Code',
        'modal' => '\App\CRMDV\Models\Codes',
        'list' => [
            ['name' => 'image', 'type' => 'image', 'label' => 'Ảnh'],
            ['name' => 'name', 'type' => 'text_edit', 'label' => 'Tên'],
            ['name' => 'link', 'type' => 'text', 'label' => 'Link'],
            ['name' => 'link', 'type' => 'relation', 'object' => 'bill', 'display_field' => 'domain', 'label' => 'Link trong HĐ'],
            ['name' => 'multi_cat', 'type' => 'custom', 'td' => 'CRMDV.list.td.multi_cat', 'label' => 'Danh mục'],
            ['name' => 'tags', 'type' => 'text', 'label' => 'Sản phẩm',],
            ['name' => 'source', 'type' => 'text', 'label' => 'Mã nguồn',],
            ['name' => 'owned', 'type' => 'text', 'label' => 'Nguồn',],
            ['name' => 'status', 'type' => 'status', 'label' => 'Trang thái'],
        ],
        'form' => [
            'general_tab' => [
                ['name' => 'name', 'type' => 'text', 'class' => '', 'label' => 'Tên', 'group_class' => 'col-md-6'],
                ['name' => 'link', 'type' => 'text', 'class' => 'required', 'label' => 'Link', 'des' => 'Link website. VD: https://dienmayxanh.vn', 'group_class' => 'col-md-6'],
                ['name' => 'multi_cat', 'type' => 'custom', 'field' => 'CRMDV.form.fields.multi_cat', 'label' => 'Danh mục sản phẩm', 'model' => Category::class,
                    'object' => 'category_product', 'where' => 'type in (10)', 'display_field' => 'name', 'multiple' => true, 'group_class' => 'col-md-6', 'des' => 'Danh mục đầu tiên chọn là danh mục chính'],
                ['name' => 'tags', 'type' => 'tags', 'label' => 'Ngành hàng', 'model' => Tag::class, 'where' => "type = 'code'", 'group_class' => 'col-md-6'],
                ['name' => 'source', 'type' => 'checkbox_multiple', 'options' =>
                    [
                        'wordpress' => 'Wordpress',
                        'ladipage' => 'Ladipage',
                        'laravel' => 'Laravel',
                        // 'magento' => 'Magento',
                        'react native' => 'react native',
                        'native' => 'native',
                        'flutter' => 'flutter',
                        'khác' => 'Khác',
                    ], 'class' => 'required', 'multiple' => true, 'label' => 'Mã nguồn', 'group_class' => 'col-md-12',],
                ['name' => 'price_setup', 'type' => 'price_vi', 'class' => 'required', 'group_class' => 'col-md-4', 'label' => 'Giá bán code', 'des' => 'Bán code, setup lên hosting cho khách'],
                ['name' => 'price_interface_change', 'type' => 'price_vi', 'class' => '', 'group_class' => 'col-md-4', 'label' => 'Giá thay giao diện  1 chút', 'des' => 'Sửa màu, di chuyển khối, làm cho khác web cũ đi 1 chút'],
                ['name' => 'price_interface_change_all', 'type' => 'price_vi', 'class' => '', 'group_class' => 'col-md-4', 'label' => 'Giá thay giao diện toàn bộ', 'des' => 'Tìm theme trên mạng và thay vào'],
                ['name' => 'owned', 'type' => 'radio', 'class' => '', 'label' => 'Lưu trữ tại', 'value' => 'server mình', 'options' => [
                    'server mình' => 'Trên server mình',
                    'Mẫu nước ngoài' => 'Mẫu nước ngoài',
                    'trên mạng' => 'Kiếm trên mạng',
                    'asite.vn' => 'asite.vn',
                    'bizhostvn.com' => 'bizhostvn.com',
                    'mauwebsitedep.net' => 'mauwebsitedep.net',
                    'webrt.vn' => 'webrt.vn',
                ]],
                ['name' => 'status', 'type' => 'checkbox', 'label' => 'admin.active', 'value' => 1, 'group_class' => 'col-md-4'],
                ['name' => 'created_by_name', 'type' => 'text', 'class' => '', 'label' => 'Tác giả', 'group_class' => 'col-md-4'],
                ['name' => 'link_ios', 'type' => 'text', 'class' => '', 'label' => 'Link ios', 'group_class' => 'col-md-4'],
            ],
            'remind_tab' => [
                ['name' => 'image', 'type' => 'file_image', 'label' => 'Ảnh đại diện'],
                ['name' => 'image_extra', 'type' => 'multiple_image_dropzone', 'count' => '6', 'label' => 'Thêm nhiều ảnh khác'],
            ],
            'des_tab' => [
                ['name' => 'intro', 'type' => 'textarea_editor', 'class' => '', 'label' => 'Mô tả qua về tính năng'],
                ['name' => 'content', 'type' => 'textarea_editor', 'class' => '', 'label' => 'Mô tả chi tiết tính năng'],
                ['name' => 'ten_file_ma_nguon', 'type' => 'text', 'class' => '', 'label' => 'Tên file mã nguồn', 'group_class' => 'col-md-3'],

            ],
        ]
    ];

    protected $quick_search = [
        'label' => 'ID',
        'fields' => 'id, name, link, source, owned'
    ];

    protected $filter = [
        'multi_cat' => [
            'label' => 'Thể loại',
            'type' => 'select2_model',
            'display_field' => 'name',
            'object' => 'category',
            'model' => Category::class,
            'query_type' => 'custom',
        ],
        'link' => [
            'label' => 'Link',
            'type' => 'text',
            'query_type' => '=',
        ],

    ];

    public function getIndex(Request $request)
    {
        $data = $this->getDataList($request);

        return view('CRMDV.codes.list')->with($data);
    }

    public function appendWhere($query, $request)
    {
        //  Nếu không có quyền xem toàn bộ dữ liệu thì chỉ được xem các dữ liệu mình tạo
        if (!CommonHelper::has_permission(\Auth::guard('admin')->user()->id, 'view_all_data')) {
            // $query = $query->where('admin_id', \Auth::guard('admin')->user()->id);
        }

        if (!is_null($request->get('multi_cat'))) {
            $query = $query->where('multi_cat', 'like', '%|'.$request->multi_cat.'|%');
        }

        return $query;
    }

    public function add(Request $request)
    {
        try {


            if (!$_POST) {
                $data = $this->getDataAdd($request);
                return view('CRMDV.codes.add')->with($data);
            } else if ($_POST) {
                \DB::beginTransaction();

                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'link' => 'required|unique:codes,link',
                ], [
                    'name.required' => 'Bắt buộc phải nhập tên',
                    'link.unique' => 'Web này đã đăng!',
                ]);
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                } else {
                    $data = $this->processingValueInFields($request, $this->getAllFormFiled());
                    //  Tùy chỉnh dữ liệu insert

                    $data['admin_id'] = \Auth::guard('admin')->user()->id;
                    if ($request->has('multi_cat')) {
                        $data['multi_cat'] = '|' . implode('|', $request->multi_cat) . '|';
                    }

                    if ($request->has('image_extra')) {
                        $data['image_extra'] = implode('|', $request->image_extra);
                    }


                    if ($request->has('source')) {
                        $data['source'] = '|' . implode('|', $data['source']) . '|';
                    } else {
                        $data['source'] = '';
                    }

                    if ($request->has('type')) {
                        $data['type'] = '|' . implode('|', $data['type']) . '|';
                    } else {
                        $data['type'] = '';
                    }

                    $data['link'] = preg_replace('/\s+/', '', $data['link']);
                    if (substr($data['link'], -1) != '/') {
                        //  Nếu cuối chuỗi không có dấu '/' thì nối thêm
                        $data['link'] .= '/';
                    }

                    foreach ($data as $k => $v) {
                        $this->model->$k = $v;
                    }

                    if ($this->model->save()) {
                        \DB::commit();

                        $this->afterAddLog($request, $this->model);

                        //  Xử lý tag
                        $this->xulyTag($this->model->id, $data);

                        CommonHelper::one_time_message('success', 'Tạo mới thành công!');
                    } else {
                        \DB::rollback();
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
            \DB::rollback();
            CommonHelper::one_time_message('error', $ex->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function quickAdd(Request $request)
    {
        try {


            if (!$_POST) {
                $data = $this->getDataAdd($request);
                return view('CRMDV.codes.quick_add')->with($data);
            } else if ($_POST) {
                $validator = Validator::make($request->all(), [
//                    'name' => 'required',
//                    'link' => 'required|unique:codes,link',
                ], [
//                    'name.required' => 'Bắt buộc phải nhập tên',
//                    'link.unique' => 'Web này đã đăng!',
                ]);
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                } else {
//                    dd($request->all());
                    //  Lưu ladipage của mình
                    $code = trim($request->ladipage);
                    $lines = explode("\n", $code);

                    //  Tùy chỉnh dữ liệu insert
                    $datainsert = [];
                    foreach ($lines as $v) {
                        $v = preg_replace('/\s+/', '', $v);
                        if ($v != '') {
                            if (substr($v, -1) != '/') {
                                //  Nếu cuối chuỗi không có dấu '/' thì nối thêm
                                $v .= '/';
                            }
                            if (!empty(trim($v)) && Codes::where('link', $v)->count() == 0 && !in_array($v, $datainsert)) {
                                $datainsert[] = [
                                    'link' => $v,
                                    'price_setup' => 800000,
                                    'source' => 'ladipage',
                                    'admin_id' => @\Auth::guard('admin')->user()->id
                                ];
                            }
                        }
                    }
                    if (!empty($datainsert)) {
                        Codes::insert($datainsert);
                    }


                    //  Lưu ladipage kiếm mạng
                    $code = trim($request->ladipage_mang);
                    $lines = explode("\n", $code);

                    //  Tùy chỉnh dữ liệu insert
                    $datainsert = [];
                    foreach ($lines as $v) {
                        $v = preg_replace('/\s+/', '', $v);
                        if ($v != '') {
                            if (substr($v, -1) != '/') {
                                //  Nếu cuối chuỗi không có dấu '/' thì nối thêm
                                $v .= '/';
                            }
                            if (!empty(trim($v)) && Codes::where('link', $v)->count() == 0 && !in_array($v, $datainsert)) {
                                $datainsert[] = [
                                    'link' => $v,
                                    'price_setup' => 800000,
                                    'source' => 'ladipage',
                                    'admin_id' => @\Auth::guard('admin')->user()->id,
                                    'owned' => 2,
                                ];
                            }
                        }
                    }
                    if (!empty($datainsert)) {
                        Codes::insert($datainsert);
                    }


                    //  Lưu wordpress
                    $code = trim($request->wordpress);
                    $lines = explode("\n", $code);

                    //  Tùy chỉnh dữ liệu insert
                    $datainsert = [];

                    foreach ($lines as $v) {
                        $v = preg_replace('/\s+/', '', $v);
                        if ($v != '') {
                            if (substr($v, -1) != '/') {
                                //  Nếu cuối chuỗi không có dấu '/' thì nối thêm
                                $v .= '/';
                            }
                            if (!empty(trim($v)) && Codes::where('link', $v)->count() == 0 && !in_array($v, $datainsert)) {
                                $datainsert[] = [
                                    'link' => $v,
                                    'source' => 'wordpress',
                                    'admin_id' => @\Auth::guard('admin')->user()->id
                                ];
                            }
                        }
                    }
                    if (!empty($datainsert)) {
                        Codes::insert($datainsert);
                    }


                    //  Lưu wordpress kiếm mạng
                    $code = trim($request->wordpress_mang);
                    $lines = explode("\n", $code);

                    //  Tùy chỉnh dữ liệu insert
                    $datainsert = [];

                    foreach ($lines as $v) {
                        $v = preg_replace('/\s+/', '', $v);
                        if ($v != '') {
                            if (substr($v, -1) != '/') {
                                //  Nếu cuối chuỗi không có dấu '/' thì nối thêm
                                $v .= '/';
                            }
                            if (!empty(trim($v)) && Codes::where('link', $v)->count() == 0 && !in_array($v, $datainsert)) {
                                $datainsert[] = [
                                    'link' => $v,
                                    'source' => 'wordpress',
                                    'admin_id' => @\Auth::guard('admin')->user()->id,
                                    'owned' => 2
                                ];
                            }
                        }
                    }
                    if (!empty($datainsert)) {
                        Codes::insert($datainsert);
                    }

                    CommonHelper::one_time_message('success', 'Tạo mới thành công!');

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
            CommonHelper::one_time_message('error', $ex->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function hoaDon(Request $request)
    {
//        try {
        if (!$_POST) {

            $data = $this->getDataAdd($request);
            $str = ('CRMDV.codes.hoa_don');
//                dd($str);
            return view($str)->with($data);
        } else if ($_POST) {
            $validator = Validator::make($request->all(), [
//                    'module' => 'required',
            ], [
//                    'module.required' => 'Bắt buộc phải nhập module!',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                DB::beginTransaction();

                if ($request->has('file')) {
                    $file_name = $request->file('file')->getClientOriginalName();
                    $file_name = str_replace(' ', '', $file_name);
                    $file_name_insert = date('s_i_') . $file_name;
                    $request->file('file')->move(base_path() . '/public_html/filemanager/userfiles/codes/hoa-don/', $file_name_insert);
                    $data['file'] = 'codes/hoa-don/' . $file_name_insert;
                    dd($data);
                    \Excel::load('public_html/filemanager/userfiles/'.$data['file'], function ($reader) use ($request) {
                        echo '<table style="font-family: arial, sans-serif;
                                      border-collapse: collapse;
                                      width: 100%;">';
                        echo '<thead>';
                        echo '<tr>
                                       <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">STT</th>
                                       <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Ngày ký</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Tên công ty</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Mã số thuế</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Cộng tiền hàng</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Tiền thuế GTGT</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Tổng cộng tiền thanh toán</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Trạng thái hóa đơn</th>                      
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Trạng thái CQT</th>
                      </tr>';
                        echo '</thead>';
                        echo '<tbody>';



                        $reader->each(function ($sheet) use ($request, $reader) {

                            if ($reader->getSheetCount() == 1) {

//                                    echo 'bắt đầu import sđt : ' . @$sheet->all()['name'] . '<br>';
//                                    echo '<tr>';
//                                    $this->webServerShow($sheet, $request);
////                                    dd($request);
////                                    dd($sheet->domains);
//
//
//                                    echo '</tr>';
                                $domains = preg_split('/\s+/', $sheet->domains, -1, PREG_SPLIT_NO_EMPTY);

                                foreach ($domains as $domain) {
//                                        dd($domain);
                                    $result = DB::table('bills')->where('domain', $domain)->first();
//                                        dd($result);
//
//                                        if(isset($result->service_id)) {
//                                            // Xử lý nếu tồn tại thuộc tính service_id
//                                            $service = DB::table('services')->where('id', $result->service_id)->first();
//                                        } else {
//                                            // Xử lý khi $service_id không tồn tại
//                                            $service = null;
//                                        }
//                                        dd($serviceName);
//                                        dd('ID Service: '.$result->service_id.' Name: '.$service->name_vi);
                                    // Kiểm tra xem có bản ghi nào được trả về hay không
                                    if ($result) {
                                        $data=$sheet->all();
//                                            dd($data);
                                        if($data['ip']==null){
                                            $data['ip']=' ';
                                        }
                                        $data['domains']=$domain;
                                        $data['ngayky']=$result->registration_date;
                                        $data['ngayhethan']=$result->expiry_date;
                                        $data['doanhso']=number_format($result->total_price);
                                        $data['trangthai']=$result->status;
                                        $data['tudonggiahan']=$result->auto_extend;
                                        $service = DB::table('services')->where('id', $result->service_id)->first();
                                        if($service){
                                            $data['dichvu']=$service->name_vi;
                                        }else{
                                            $data['dichvu']=' ';
                                        }

                                        $sheet2 = new CellCollection($data);
//                                        dd($sheet2);
                                        // Nếu có, hiển thị thông tin của bản ghi
                                        echo '<tr>';
                                        $this->webServerShow($sheet2, $request);

//                                        echo '<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;">'.$domain.'</td>';

                                        echo '</tr>';

                                    } else {
                                        // Nếu không, bỏ trống
                                        $data=$sheet->all();
//                                            dd($data);
                                        $data['domains']=$domain;
                                        $data['doanhso']=' ';
                                        $data['ngayky']=' ';
                                        $data['ngayhethan']=' ';
                                        $data['trangthai']=' ';
                                        $data['tudonggiahan']=' ';
                                        $data['dichvu']=' ';
                                        $data['ip']=' ';
                                        $sheet2 = new CellCollection($data);
                                        echo '<tr>';
                                        $this->webServerShow($sheet2, $request);
//                                            echo '<td colspan="12" style="border: 1px solid #dddddd; text-align: left; padding: 8px;">'.$domain.'</td>';
                                        echo '</tr>';

                                    }
                                }
                            } else {

                                $sheet->each(function ($row) use ($request) {
                                    $this->webServerShow($row, $request);
                                });
                            }

//                                $domains = preg_split('/\s+/', $sheet->domains, -1, PREG_SPLIT_NO_EMPTY);
//                                foreach ($domains as $domain) {
//                                    $result = DB::table('bills')->where('domain', $domain)->first();
//
//                                    // Kiểm tra xem có bản ghi nào được trả về hay không
//                                    if ($result) {
//                                        $data=$sheet->all();
//                                        $data['domains']=$domain;
//                                        $sheet2 = new CellCollection($data);
////                                        dd($sheet2);
//                                        // Nếu có, hiển thị thông tin của bản ghi
//                                        echo '<tr>';
////                                        echo '<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;">'.$domain.'</td>';
//                                        $this->webServerShow($sheet2, $request);
//                                        echo '</tr>';
//
//                                    } else {
//                                        // Nếu không, hiển thị thông báo không tìm thấy
//                                        echo '<tr>';
//                                        echo '<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;">'.$domain.'</td>';
//                                        echo '<td colspan="7"  style="border: 1px solid #dddddd; text-align: left; padding: 8px;"> Không tìm thấy </td>';
//                                        echo '</tr>';
//
//                                    }
//                                }


                        });
                        echo '</tbody>';

                        echo '</table>';
                        dd($reader->getSheetCount());
                    });
                    dd('a');
                } else {
                    dd('Chưa nhập file');
                }

            }
        }
//        }
//        catch (\Exception $ex) {
//            DB::rollback();
//            CommonHelper::one_time_message('error', $ex->getMessage());
//            return redirect()->back()->withInput();
//        }
    }
    public function tableHoaDon(Request $request)
    {
        $array_mst = [];
        $array_id = [];
        $tong_tien_nhan = [];

        if ($request->ngaycuoi == null) {
            $hoadon = \App\CRMDV\Models\HoaDon::select('cty_mst', DB::raw('SUM(tong_tien) as tong_tien1'), 'cty_name')
                ->where('ngay_ky', '>=', $request->ngaydau)->where('status_CQT', 'CQT Xác nhận')
                ->groupBy('cty_mst')->get();
        } else {
            $hoadon = \App\CRMDV\Models\HoaDon::select('cty_mst', DB::raw('SUM(tong_tien) as tong_tien1'), 'cty_name')
                ->where('ngay_ky', '>=', $request->ngaydau)->where('ngay_ky', '<=', $request->ngaycuoi)->where('status_CQT', 'CQT Xác nhận')
                ->groupBy('cty_mst')->get();
        }

        foreach ($hoadon as $key => $value) {
            $array_mst[] = $value->cty_mst;
        }

        $bill = \App\CRMDV\Models\Bill::select('id', 'mst')->whereIn('mst', $array_mst)->get();
        foreach ($bill as $k => $val) {
            $array_id[] = $val->id;
        }

        $bill_receipt = \App\CRMDV\Models\BillReceipts::select('date', 'bill_id', DB::raw('SUM(price) as price'))
            ->whereIn('bill_id', $array_id)
            ->when($request->ngaydau && $request->ngaycuoi, function ($query) use ($request) {
                return $query->whereBetween('date', [$request->ngaydau, $request->ngaycuoi]);
            })->with('bill')->groupBy('bill_id')->get();
        foreach ($bill_receipt as $value) {
            $tong_tien_nhan[$value->bill->mst] = $value->price;
        }

        foreach ($hoadon as $item) {
            $item->tong_tien_nhan = $tong_tien_nhan[$item->cty_mst] ?? 0;
        }

        return response()->json([
            'hoadon' => $hoadon,
            '$bill_receipt' => $tong_tien_nhan,
        ]);
    }

    public function checkWebServer(Request $request)
    {
//        try {
            if (!$_POST) {

                $data = $this->getDataAdd($request);
                $str = ('CRMDV.codes.check_web_server');
//                dd($str);
                return view($str)->with($data);
            } else if ($_POST) {
                $validator = Validator::make($request->all(), [
//                    'module' => 'required',
                ], [
//                    'module.required' => 'Bắt buộc phải nhập module!',
                ]);
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                } else {
                    DB::beginTransaction();

                    if ($request->has('file')) {
                        $file_name = $request->file('file')->getClientOriginalName();
                        $file_name = str_replace(' ', '', $file_name);
                        $file_name_insert = date('s_i_') . $file_name;
                        $request->file('file')->move(base_path() . '/public_html/filemanager/userfiles/codes/check-web-server/', $file_name_insert);
                        $data['file'] = 'codes/check-web-server/' . $file_name_insert;
                        \Excel::load('public_html/filemanager/userfiles/'.$data['file'], function ($reader) use ($request) {
                            echo '<table style="font-family: arial, sans-serif;
                                      border-collapse: collapse;
                                      width: 100%;">';
                            echo '<thead>';
                            echo '<tr>
                                       <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">STT</th>
                                       <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Tên miền</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">User</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Creator</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Bandwidth</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Disk Usage</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">IP</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Dịch vụ</th>                      
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Ngày tạo</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Ngày ký</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Ngày hết hạn</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Doanh số</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Trạng thái</th>
                                      <th style=" position: sticky; top: 0; background-color: #f9f9f9; z-index: 1; border: 1px solid #dddddd; padding: 8px; text-align: left;">Tự động gia hạn</th>      
                      </tr>';
                            echo '</thead>';
                            echo '<tbody>';



                            $reader->each(function ($sheet) use ($request, $reader) {

                                if ($reader->getSheetCount() == 1) {

//                                    echo 'bắt đầu import sđt : ' . @$sheet->all()['name'] . '<br>';
//                                    echo '<tr>';
//                                    $this->webServerShow($sheet, $request);
////                                    dd($request);
////                                    dd($sheet->domains);
//
//
//                                    echo '</tr>';
                                    $domains = preg_split('/\s+/', $sheet->domains, -1, PREG_SPLIT_NO_EMPTY);
                                    foreach ($domains as $domain) {
//                                        dd($domain);
                                        $result = DB::table('bills')->where('domain', $domain)->first();
//                                        dd($result);
//
//                                        if(isset($result->service_id)) {
//                                            // Xử lý nếu tồn tại thuộc tính service_id
//                                            $service = DB::table('services')->where('id', $result->service_id)->first();
//                                        } else {
//                                            // Xử lý khi $service_id không tồn tại
//                                            $service = null;
//                                        }
//                                        dd($serviceName);
//                                        dd('ID Service: '.$result->service_id.' Name: '.$service->name_vi);
                                        // Kiểm tra xem có bản ghi nào được trả về hay không
                                        if ($result) {
                                            $data=$sheet->all();
//                                            dd($data);
                                            if($data['ip']==null){
                                                $data['ip']=' ';
                                            }
                                            $data['domains']=$domain;
                                            $data['ngayky']=$result->registration_date;
                                            $data['ngayhethan']=$result->expiry_date;
                                            $data['doanhso']=number_format($result->total_price);
                                            $data['trangthai']=$result->status;
                                            $data['tudonggiahan']=$result->auto_extend;
                                            $service = DB::table('services')->where('id', $result->service_id)->first();
                                            if($service){
                                                $data['dichvu']=$service->name_vi;
                                            }else{
                                                $data['dichvu']=' ';
                                            }

                                            $sheet2 = new CellCollection($data);
//                                        dd($sheet2);
                                            // Nếu có, hiển thị thông tin của bản ghi
                                            echo '<tr>';
                                            $this->webServerShow($sheet2, $request);

//                                        echo '<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;">'.$domain.'</td>';

                                            echo '</tr>';

                                        } else {
                                            // Nếu không, bỏ trống
                                            $data=$sheet->all();
//                                            dd($data);
                                            $data['domains']=$domain;
                                            $data['doanhso']=' ';
                                            $data['ngayky']=' ';
                                            $data['ngayhethan']=' ';
                                            $data['trangthai']=' ';
                                            $data['tudonggiahan']=' ';
                                            $data['dichvu']=' ';
                                            $data['ip']=' ';
                                            $sheet2 = new CellCollection($data);
                                            echo '<tr>';
                                            $this->webServerShow($sheet2, $request);
//                                            echo '<td colspan="12" style="border: 1px solid #dddddd; text-align: left; padding: 8px;">'.$domain.'</td>';
                                            echo '</tr>';

                                        }
                                    }
                                } else {

                                    $sheet->each(function ($row) use ($request) {
                                        $this->webServerShow($row, $request);
                                    });
                                }

//                                $domains = preg_split('/\s+/', $sheet->domains, -1, PREG_SPLIT_NO_EMPTY);
//                                foreach ($domains as $domain) {
//                                    $result = DB::table('bills')->where('domain', $domain)->first();
//
//                                    // Kiểm tra xem có bản ghi nào được trả về hay không
//                                    if ($result) {
//                                        $data=$sheet->all();
//                                        $data['domains']=$domain;
//                                        $sheet2 = new CellCollection($data);
////                                        dd($sheet2);
//                                        // Nếu có, hiển thị thông tin của bản ghi
//                                        echo '<tr>';
////                                        echo '<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;">'.$domain.'</td>';
//                                        $this->webServerShow($sheet2, $request);
//                                        echo '</tr>';
//
//                                    } else {
//                                        // Nếu không, hiển thị thông báo không tìm thấy
//                                        echo '<tr>';
//                                        echo '<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;">'.$domain.'</td>';
//                                        echo '<td colspan="7"  style="border: 1px solid #dddddd; text-align: left; padding: 8px;"> Không tìm thấy </td>';
//                                        echo '</tr>';
//
//                                    }
//                                }


                            });
                            echo '</tbody>';

                            echo '</table>';
                            dd($reader->getSheetCount());
                        });
                        dd('a');
                    } else {
                        dd('Chưa nhập file');
                    }

                }
            }
//        }
//        catch (\Exception $ex) {
//            DB::rollback();
//            CommonHelper::one_time_message('error', $ex->getMessage());
//            return redirect()->back()->withInput();
//        }
    }

    public function webServerShow($row, $r)
    {
        try {
            static $rowCounter = 1;
            $user_name = $row->all()['name'];//trả ra dữ liệu trong cột có tiêu đề là name
//            dd($row->all());
            //lấy  ra tiêu đề
            $columns = $row->all();
//            dd($columns);
//          sắp xếp thứ tự hiển thị
            $desiredOrder = ['stt','domains', 'name', 'creator', 'bandwidth',
                'disk_usage','ip','dichvu','date_created','ngayky', 'ngayhethan', 'doanhso',
                'trangthai', 'tudonggiahan'];
            $sortedColumns = [];
            $sortedColumns['stt'] = $rowCounter++;
            foreach ($desiredOrder as $columnName) {
                if (isset($columns[$columnName])) {
                    $sortedColumns[$columnName] = $columns[$columnName];
                }
            }


            $row_empty = true;
            foreach ($row->all() as $key => $value) {
                if ($value != null) {
                    $row_empty = false;
                }
            }
            if (!$row_empty) {
//                echo '__bắt đầu insert:' .$user_name;
                $data = [];
                //  hiển thị dữ liệu ra
                foreach ($sortedColumns as $key => $value) {
//                    echo $key . ': '.  . '<br>';
                    if (!in_array($key, ['of_domains','suspended', 'sent_e_mails', 'select'])) {
                        if($key === 'stt'){
                            echo '<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;">' . $value . '</td>';
                        }
                        else if ($key === 'domains') {
                            echo '<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;">';
                            // Tách các tên miền thành mảng, tách theo khoảng trắng
                            $domains = preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY);
//                            echo $domains;
//                            echo $value;
                            // Hiển thị mỗi tên miền trên một dòng
                            foreach ($domains as $domain) {
                                echo '<div>'.$domain . '</dib>';
                            }
                            echo '</td>';

                        }
                        elseif ($key === 'trangthai' || $key === 'tudonggiahan') {

                            if($value=='1'){
                                echo '<td style="border: 1px solid #dddddd;
                                      text-align: left;color:forestgreen;
                                      padding: 8px;">' . $value . '</td>';
                            }
                            else{
                                echo '<td style="border: 1px solid #dddddd;
                                      text-align: left;color:red;
                                      padding: 8px;">' . $value . '</td>';
                            }
                        }
                        elseif ($key === 'date_created' || $key === 'ngayky' || $key==='ngayhethan') {
                            if($value!=' '){
                                $value = strtotime($value);
                                $value = date("d/m/Y", $value);
                                echo '<td style="border: 1px solid #dddddd;
                                      text-align: left;
                                      padding: 8px;">' . $value . '</td>';
                            }
                            else{
                                echo '<td style="border: 1px solid #dddddd;
                                      text-align: left;
                                      padding: 8px;">' . $value . '</td>';
                            }




                        }elseif($key === 'ip_address'){
                            if($value!=' '){
                                echo '<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;">' . $value . '</td>';
                            }else{
                                echo '<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;">N/A</td>'; // Hoặc để trống
                            }
                        }
                        else{
                            echo '<td style="border: 1px solid #dddddd;
                                      text-align: left;
                                      padding: 8px;">' . $value . '</td>';
                        }

                    }
                }
            } else {
                return [
                    'status' => false,
                    'import' => false,
                    'msg' => 'Dòng trống',
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status' => true,
                'import' => false,
                'msg' => $ex->getMessage()
            ];
        }
    }


    public function importExcel(Request $r)
    {
        // XÁC NHẬN
        if ((int)$r->get('confirm') === 1) {
            $validator = Validator::make($r->all(), [
                'module' => 'required|in:hoa_don',
                'token'  => 'required',
            ]);
            if ($validator->fails()) return back()->withErrors($validator)->withInput();
            return $this->processingImport($r, null); // <-- trả về Response/Redirect
        }

        // UPLOAD LẦN ĐẦU (preview)
        if (!$r->has('module')) $r->merge(['module' => 'hoa_don']);
        $validator = Validator::make($r->all(), [
            'module' => 'required|in:hoa_don',
            'file'   => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $importController = new \App\Http\Controllers\Admin\ImportController();
        $data = $importController->processingValueInFields($r, $importController->getAllFormFiled());

        if ($r->hasFile('file')) {
            $file_name = str_replace(' ', '', $r->file('file')->getClientOriginalName());
            $file_name_insert = date('s_i_') . $file_name;
            $r->file('file')->move(base_path('public_html/filemanager/userfiles/imports'), $file_name_insert);
            $data['file'] = 'imports/' . $file_name_insert;
        }

        unset($data['field_options_key'], $data['field_options_value']);

        $item = new \App\Models\Import();
        foreach ($data as $k => $v) $item->$k = $v;

        if ($item->save()) {
            $importController->updateAttributes($r, $item);
            // TRẢ VỀ preview (HTML), KHÔNG redirect
            return $this->processingImport($r, $item);
        }

        CommonHelper::one_time_message('error', 'Lỗi tạo mới. Vui lòng thử lại!');
        return back();
    }

    public function processingImport($r, $item = null)
    {
        // ====== NHÁNH LƯU (sau khi bấm nút xác nhận) ======
        if ((int)$r->get('confirm') === 1 && $r->filled('token')) {
            $rows = \Session::get('preview_'.$r->token);
            if (!$rows || !is_array($rows)) {
                return back()->with('error', 'Hết hạn hoặc không có dữ liệu preview. Vui lòng import lại.');
            }

            $record_total = $record_success = 0;
            foreach ($rows as $row) {
                $res = $this->importItem($row, $r, []); // importItem() có thể tự chuẩn hoá khi lưu
                $record_total++;
                if (!empty($res['import'])) $record_success++;
            }

            \Session::forget('preview_'.$r->token);
            \CommonHelper::one_time_message('success', "Đã import {$record_success}/{$record_total} dòng.");
            return redirect('/admin/import');
        }

        // ====== NHÁNH PREVIEW (mặc định, KHÔNG tính toán) ======
        if (!$item || empty($item->file)) {
            return back()->with('error', 'Không tìm thấy file để preview.');
        }
        $filePath = 'public_html/filemanager/userfiles/' . $item->file;

        // Helpers gọn
        $slug  = function ($k) { return \Illuminate\Support\Str::slug(trim((string)$k), '_'); };
        $clean = function ($v) { return is_string($v) ? trim(preg_replace("/\x{00A0}/u", ' ', $v)) : $v; };

        // Map cột về bộ chuẩn (chỉ lấy đúng key, không tính gì thêm)
        $map = [
            'id'             => ['id','so_id','ma'],
            'cty_name'       => ['cty_name','ten_cong_ty','ten_cty','cong_ty','company','company_name'],
            'cty_mst'        => ['cty_mst','mst','ma_so_thue','tax_code','mst_cty'],
            'tien_hang'      => ['tien_hang','gia_tri_truoc_thue','subtotal','cong_tien_hang'],
            'tien_thue_gtgt' => ['tien_thue_gtgt','thue_vat','thue_gtgt','vat'],
            'tong_tien'      => ['tong_tien','tong_cong','total','tong_cong_tien_thanh_toan'],
            'status_CQT'     => ['status_cqt','trang_thai_cqt','tinh_trang_cqt','cqt_status'],
            'ngay_ky'        => ['ngay_ky','ngayky','ngay_ky_so','ngay_lap','ngay_xuat'],
            // Optional gộp:
            'ten_cong_ty_mst'=> ['ten_cong_ty_mst'],
        ];

        $rowsNormalized = [];
        $header = []; $isNumericKeys = false;

        \Excel::load($filePath, function ($reader) use (&$rowsNormalized, &$header, &$isNumericKeys, $slug, $clean, $map) {
            $rows = $reader->get()->toArray();
            if (empty($rows)) return;

            $first = reset($rows);
            if (!$first || !is_array($first)) return;

            $isNumericKeys = (array_keys($first) === range(0, count($first)-1));
            if ($isNumericKeys) {
                // Dòng 1 là header
                $header = array_map(function ($v) use ($slug) { return $slug($v); }, array_values($first));
                $data = array_slice($rows, 1);
                foreach ($data as $row) {
                    $assoc = [];
                    foreach (array_values($row) as $i => $v) {
                        $key = $header[$i] ?? null;
                        if ($key) $assoc[$key] = $clean($v);
                    }
                    $rowsNormalized[] = self::reduceToTargets($assoc, $map);
                }
            } else {
                // Keys đã là tên cột
                $header = array_map($slug, array_keys($first));
                foreach ($rows as $row) {
                    $assoc = [];
                    foreach ($row as $k => $v) $assoc[$slug($k)] = $clean($v);
                    $rowsNormalized[] = self::reduceToTargets($assoc, $map);
                }
            }

            // Fallback tách "ten_cong_ty_mst" nếu có (cũng chỉ cắt chuỗi, không tính toán)
            foreach ($rowsNormalized as &$r) {
                if ((!isset($r['cty_name']) || !isset($r['cty_mst'])) && !empty($r['ten_cong_ty_mst'])) {
                    $parts = preg_split('/[\/\-\|]+/', (string)$r['ten_cong_ty_mst']);
                    if (!isset($r['cty_name']) && isset($parts[0])) $r['cty_name'] = trim($parts[0]);
                    if (!isset($r['cty_mst'])  && isset($parts[1])) $r['cty_mst']  = trim($parts[1]);
                }
                unset($r['ten_cong_ty_mst']); // bỏ trường phụ này khỏi preview/session
            }
            unset($r);
        });

        if (empty($rowsNormalized)) {
            return response('<div style="color:red">Không có dữ liệu để preview.</div>');
        }

        // Lưu vào session bằng token để xác nhận lưu ở bước sau (giữ nguyên giá trị y như Excel sau normalize key)
        $token = 'tok'.bin2hex(random_bytes(6));
        \Session::put('preview_'.$token, $rowsNormalized);

        // ----- Render preview (KHÔNG format/không tính) -----
        ob_start();
        echo '<a style="padding:8px 12px; background:#1e40af; color:#fff; font-weight:600; border-radius:6px; display:inline-block; margin:10px 0" href="javascript:history.back()">Quay lại</a>';

        // ẨN cột id ở preview, chỉ show các cột sau
        $cols = ['cty_name','cty_mst','tien_hang','tien_thue_gtgt','tong_tien','status_CQT','ngay_ky'];

        echo '<div style="margin:8px 0; color:#374151">Xem trước (hiển thị tối đa 200 dòng):</div>';
        echo '<div style="max-width:100%; overflow:auto; border:1px solid #e5e7eb; border-radius:8px">';
        echo '<table style="border-collapse:collapse; width:100%; font-family:Arial, sans-serif; font-size:13px">';
        echo '<thead><tr style="background:#f3f4f6"><th style="border:1px solid #e5e7eb; padding:8px; text-align:center">#</th>';
        foreach ($cols as $c) echo '<th style="border:1px solid #e5e7eb; padding:8px; text-transform:uppercase">'.e($c).'</th>';
        echo '</tr></thead><tbody>';

        $limit = min(200, count($rowsNormalized));
        for ($i=0; $i<$limit; $i++) {
            echo '<tr><td style="border:1px solid #e5e7eb; padding:6px; text-align:center">'.($i+1).'</td>';
            foreach ($cols as $c) {
                $v = $rowsNormalized[$i][$c] ?? '';
                echo '<td style="border:1px solid #e5e7eb; padding:6px; white-space:nowrap">'.e((string)$v).'</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table></div>';

        // Nút xác nhận (post lại vào cùng URL)
        $action = url()->current();
        echo '
        <form method="POST" action="'.$action.'" style="margin-top:12px">
            '.csrf_field().'
            <input type="hidden" name="confirm" value="1">
            <input type="hidden" name="token" value="'.$token.'">
            <input type="hidden" name="module" value="hoa_don">
            <button type="submit" style="padding:10px 16px; background:#059669; color:#fff; border:none; border-radius:6px; font-weight:600">
                Tiếp tục lưu vào DB
            </button>
        </form>
       
    ';

        $html = ob_get_clean();
        return response($html);
    }

    /** Map -> chỉ giữ đúng schema DB, không tính toán giá trị */
    private static function reduceToTargets(array $assoc, array $map): array
    {
        $allowed = ['id','cty_name','cty_mst','tien_hang','tien_thue_gtgt','tong_tien','status_CQT','ngay_ky','ten_cong_ty_mst'];
        $out = [];

        foreach ($map as $target => $cands) {
            foreach ($cands as $cand) {
                if (array_key_exists($cand, $assoc) && $assoc[$cand] !== '' && $assoc[$cand] !== null) {
                    $out[$target] = $assoc[$cand];
                    break;
                }
            }
        }

        // Chỉ giữ key hợp lệ, giữ nguyên giá trị như Excel
        return array_intersect_key($out, array_flip($allowed));
    }


    public function importItem($data, $r = null, $dataInsertFix = [])
    {
        try {
            // --- LỌC SỐ (không tính bù)
            $parseNumber = function ($v) {
                if ($v === null) return null;
                $s = trim(str_replace("\xC2\xA0", ' ', (string)$v));
                if ($s === '') return null;
                $s = preg_replace('/[^\d,.\-]/u', '', $s);
                if ($s === '' || $s === '-' || $s === '--') return null;
                $lastDot = strrpos($s,'.'); $lastComma = strrpos($s,',');
                if ($lastDot!==false && $lastComma!==false) {
                    if ($lastComma > $lastDot) { $s=str_replace('.','',$s); $s=str_replace(',', '.',$s); }
                    else { $s=str_replace(',','',$s); }
                } elseif ($lastComma!==false) {
                    $right = substr($s,$lastComma+1);
                    $s = (strlen($right) <= 2) ? str_replace(',', '.', $s) : str_replace(',', '', $s);
                } elseif ($lastDot!==false) {
                    $right = substr($s,$lastDot+1);
                    if (strlen($right) > 2) $s = str_replace('.', '', $s);
                }
                $s = str_replace(' ','',$s);
                return is_numeric($s) ? (float)$s : null;
            };

            // --- PARSE NGÀY
            $parseDate = function ($v) {
                if ($v === null || $v === '') return null;
                if (is_numeric($v)) {
                    try { return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($v)->format('Y-m-d'); }
                    catch (\Throwable $e) {}
                }
                $s = trim(str_replace("\xC2\xA0", ' ', (string)$v));
                $s = rtrim($s, ". "); // xử lý '19/04/2025.' có dấu chấm
                foreach (['d/m/Y','d-m-Y','d.m.Y','Y-m-d'] as $fmt) {
                    try { return \Carbon\Carbon::createFromFormat($fmt, $s)->format('Y-m-d'); } catch (\Throwable $e) {}
                }
                return null;
            };

            // --- Giữ đúng 8 cột theo schema
            $rec = array_intersect_key($data, array_flip([
                'id','cty_name','cty_mst','tien_hang','tien_thue_gtgt','tong_tien','status_CQT','ngay_ky'
            ]));

            // Ép số & ngày (chỉ lọc, không tính)
            if (array_key_exists('tien_hang', $rec))      $rec['tien_hang']      = $parseNumber($rec['tien_hang']);
            if (array_key_exists('tien_thue_gtgt', $rec)) $rec['tien_thue_gtgt'] = $parseNumber($rec['tien_thue_gtgt']);
            if (array_key_exists('tong_tien', $rec))      $rec['tong_tien']      = $parseNumber($rec['tong_tien']);
            if (array_key_exists('ngay_ky', $rec))        $rec['ngay_ky']        = $parseDate($rec['ngay_ky']);

            // Validate bắt buộc
            if (empty($rec['cty_name']) || empty($rec['cty_mst'])) {
                return ['status'=>true,'import'=>false,'msg'=>'Bỏ qua: thiếu Tên công ty/MST'];
            }

            // Upsert theo ID nếu có
            if (!empty($rec['id'])) {
                $id = $rec['id']; unset($rec['id']);
                \DB::table('hoa_don')->updateOrInsert(['id'=>$id], $rec);
                return ['status'=>true,'import'=>true,'msg'=>"Upsert theo ID {$id}"];
            }

            // CHỐNG TRÙNG ĐÚNG CÁCH (kể cả khi giá trị NULL)
            $q = \DB::table('hoa_don')->where('cty_mst', $rec['cty_mst']);

            if (array_key_exists('ngay_ky', $rec)) {
                if ($rec['ngay_ky'] === null) $q->whereNull('ngay_ky'); else $q->whereDate('ngay_ky', $rec['ngay_ky']);
            } else {
                $q->whereNull('ngay_ky');
            }

            if (array_key_exists('tong_tien', $rec)) {
                if ($rec['tong_tien'] === null) $q->whereNull('tong_tien'); else $q->where('tong_tien', $rec['tong_tien']);
            } else {
                $q->whereNull('tong_tien');
            }

            $dup = $q->first();

            if ($dup) {
                if (!empty($rec['status_CQT'])) {
                    \DB::table('hoa_don')->where('id', $dup->id)->update(['status_CQT'=>$rec['status_CQT']]);
                    return ['status'=>true,'import'=>true,'msg'=>'Cập nhật trạng thái CQT'];
                }
                return ['status'=>true,'import'=>false,'msg'=>'Trùng (MST+Ngày+Tổng)'];
            }

            // Insert mới
            $newId = \DB::table('hoa_don')->insertGetId($rec);
            return ['status'=>true,'import'=>true,'msg'=>'Thêm mới ID '.$newId];

        } catch (\Throwable $ex) {
            return ['status'=>false,'import'=>false,'msg'=>'Lỗi: '.$ex->getMessage()];
        }
    }





    public function downloadExcelDemo(Request $r) {
        $zipFileName = 'excel_default/'.@$r->module.'.xlsx';
//        dd($$zipFileName);
        $filetopath = base_path() . '/public_html/filemanager/userfiles/' . $zipFileName;
        //  Nếu có sẵn file thì tải về
//        dd($filetopath);
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
        //  Nếu không có sẵn file thì lấy tất cả các cột của bảng đó ra cho vào file excel rồi cho tải về
        \Excel::create('file_mau_import_' . $r->module, function ($excel) use ($r) {

            // Set the title
            $excel->setTitle('file_mau_import_' . $r->module);

            $excel->sheet($r->module, function ($sheet) use ($r) {

                foreach (\Schema::getColumnListing($r->module) as $column) {
                    if (!in_array($column, ['id', 'created_at', 'updated_at']))
                        $field_name[] = $column;
                }
                $sheet->row(1, $field_name);
            });
        })->download('xlsx');
    }

    //  Xử lý tag
    public function xulyTag($post_id, $data)
    {
        $id_updated = [];
        $tags = json_decode($data['tags']);

        if (is_array($tags) && !empty($tags)) {
            foreach ($tags as $tag_name) {
                $tag_name = $tag_name->value;
                //  Tạo tag nếu chưa có
                $tag = Tag::where('name', $tag_name)->first();
                if (!is_object($tag)) {
                    $tag = new Tag();
                    $tag->name = $tag_name;
                    $tag->slug = str_slug($tag_name, '-');
                    $tag->type = 'code';
                    $tag->save();
                }


                $post_tag = PostTag::updateOrCreate([
                    'post_id' => $post_id,
                    'tag_id' => $tag->id,
                ], [
                    'multi_cat' => $data['multi_cat']
                ]);
                $id_updated[] = $post_tag->id;
            }
        }
        //  Xóa tag thừa
        PostTag::where('post_id', $post_id)->whereNotIn('id', $id_updated)->delete();

        return true;
    }

    public function update(Request $request)
    {
        try {


            $item = $this->model->find($request->id);

            if (!is_object($item)) abort(404);
            if (!$_POST) {
                $data = $this->getDataUpdate($request, $item);
                return view('CRMDV.codes.edit')->with($data);
            } else if ($_POST) {
                \DB::beginTransaction();

                $validator = Validator::make($request->all(), [
//                    'name' => 'required',
                    'link' => 'required',
                ], [
//                    'name.required' => 'Bắt buộc phải nhập tên gói',
//                    'link.unique' => 'Web này đã đăng!',
                ]);

                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                } else {
                    $data = $this->processingValueInFields($request, $this->getAllFormFiled());

                    //  Tùy chỉnh dữ liệu insert
                    if ($request->has('image_extra')) {
                        $data['image_extra'] = implode('|', $request->image_extra);
                    }
                    if ($request->has('source')) {
                        $data['source'] = '|' . implode('|', $data['source']) . '|';
                    } else {
                        $data['source'] = '';
                    }
                    if ($request->has('multi_cat')) {
                        $data['multi_cat'] = '|' . implode('|', $request->multi_cat) . '|';
                    }

                    if ($request->has('type')) {
                        $data['type'] = '|' . implode('|', $data['type']) . '|';
                    } else {
                        $data['type'] = '';
                    }

                    foreach ($data as $k => $v) {
                        $item->$k = $v;
                    }
                    if ($item->save()) {
                        //  Xử lý tag
                        $this->xulyTag($item->id, $data);
                        \DB::commit();
                        CommonHelper::one_time_message('success', 'Cập nhật thành công!');
                    } else {
                        \DB::rollback();
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
            }
        } catch (\Exception $ex) {
            \DB::rollback();
//            CommonHelper::one_time_message('error', 'Lỗi hệ thống! Vui lòng liên hệ kỹ thuật viên.');
            CommonHelper::one_time_message('error', $ex->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function getPublish(Request $request)
    {
        try {

            $item = $this->model->find($request->id);

            if (!is_object($item))
                return response()->json([
                    'status' => false,
                    'msg' => 'Không tìm thấy bản ghi'
                ]);

            if ($item->{$request->column} == 0)
                $item->{$request->column} = 1;
            else
                $item->{$request->column} = 0;

            $item->save();

            return response()->json([
                'status' => true,
                'published' => $item->{$request->column} == 1 ? true : false
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => false,
                'published' => null,
                'msg' => 'Lỗi hệ thống! Vui lòng liên hệ kỹ thuật viên.'
            ]);
        }
    }

    public function delete(Request $request)
    {
        try {

            $item = $this->model->find($request->id);

            $item->delete();

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

    public function updateBillToCode()
    {
        //  lấy id các đơn hàng đang làm
        $bill_ids_dang_lam = BillProgress::whereNotIn('status', ['Kết thúc', 'Tạm dừng', 'Khách xác nhận xong', '', null])
            ->where('status', '!=', null)->pluck('bill_id')->toArray();


        //  Lấy các bill chua duoc update sang codes
        $count_bill_create = 0;
        $bills = \App\CRMDV\Models\Bill::select('id', 'domain', 'service_id', 'update_to_codes')
            ->where('update_to_codes', 0)
            ->whereNotIn('id', $bill_ids_dang_lam)  //  không lấy các đơn đang làm
            ->where(function ($query) { //  chỉ lấy các tên miền chuẩn
                $query->orWhere('domain', 'like', '%.com%');
                $query->orWhere('domain', 'like', '%.vn%');
                $query->orWhere('domain', 'like', '%.com.vn%');
                $query->orWhere('domain', 'like', '%.edu.vn%');
                $query->orWhere('domain', 'like', '%.net%');
            })
            ->get();

        foreach ($bills as $bill) {
            if (Codes::where('link', 'like', '%' . $bill->domain . '%')->count() == 0) {
                //  Nếu chưa có thì update sang
                $code = new Codes();
                $code->link = 'https://' . $bill->domain . '/';
                if (in_array($bill->service_id, [1, 17, 18, 19, 20, 21])) {
                    $code->source = '|ladipage|';
                } elseif (in_array($bill->service_id, [5, 10, 11, 12, 13, 14, 15, 16])) {
                    $code->source = '|wordpress|';
                }
                $code->multi_cat = '|' .@$bill->ldp->career_id. '|';
                $code->bill_id = $bill->id;
                $code->owned = 'server mình';
                $code->save();
                $count_bill_create++;
            }

            $bill->update_to_codes = 1;
            $bill->save();
        }

        CommonHelper::one_time_message('success', number_format($count_bill_create) . ' code được tạo');
        return back();
    }

    public function backupToHtml()
    {
        $count = 0;
        $codes = Codes::select('id', 'backup_to_html', 'link', 'source')->where('source', 'like', '%ladipage%')->where('backup_to_html', 0)->get();
        foreach ($codes as $code) {
            if (strpos($code->source, 'ladipage') !== false) {

                //  Nếu là sources ladipage thì lưu vào thành .html
                if (strpos($code->link, '//') !== false) {
                    $file_name = explode('//', $code->link)[1];
                    $file_name = str_replace('/', '', $file_name);
                    $file_name = str_replace('.', '_', $file_name);
                    //                dd($file_name);
                    if (!file_exists(base_path() . '/public_html/ldp-template/' . $file_name . '.html')) {

                        //  Nếu chưa có lưu file .html thì lưu lại
                        try {
                            $filename = base_path() . '/public_html/ldp-template/' . $file_name . '.html'; // whatever name you want.
                            $myfile = fopen($filename, "w") or die("Unable to open file!");
                            $txt = $this->httpPost($code->link, ""); //<url> replace by url you want.
                            $txt = str_replace('href="/', 'href="' . $code->link, $txt);
                            $txt = str_replace('src="/', 'src="' . $code->link, $txt);
                            fwrite($myfile, $txt);
                            fclose($myfile);

                            //                        $v = file_get_contents($code->link);
                            //                        file_put_contents(base_path() . '/public_html/ldp-template/' . $file_name . '.html', $v);
                            $count ++;
                        } catch (\Exception $ex) {
                            dd($ex->getMessage());
                        }
                    }
                }

            } else {
//                dd($code);
            }
            $code->backup_to_html = 1;
            $code->save();
        }
        CommonHelper::one_time_message('success', number_format($count) . ' bản backup được tạo');
        return back();
    }

    function httpPost($url, $data)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "postman-token: d6c19d4c-1d67-1ed2-6ee4-4378c5a64dc2"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    public function hoaDon2(Request $request)
    {
        // Hiển thị form upload nếu không phải là request POST
        if (!$request->isMethod('post')) {
            $data = $this->getDataAdd($request);
            return view('CRMDV.codes.hoa_don')->with($data);
        }

        // Xác thực file đầu vào, chỉ kiểm tra file có tồn tại
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
        ], [
            'file.required' => 'Vui lòng chọn file để tải lên.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('file');

        if (!$file->isValid()) {
            return back()->with('error', 'Tải file lên không thành công. Vui lòng thử lại.');
        }

        $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $path = public_path('filemanager/userfiles/codes/hoa-don/');

        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
        $file->move($path, $filename);
        $filepath = $path . $filename;

        // Đọc file với tùy chọn dấu phân cách là dấu chấm phẩy
        try {
            Excel::load($filepath, function ($reader) {
                echo '<table style="font-family: arial, sans-serif; border-collapse: collapse; width: 100%;">';
                echo '<thead><tr>
                <th style="border: 1px solid #ddd; padding: 8px;">STT</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Ngày ký</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Tên công ty</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Mã số thuế</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Cộng tiền hàng</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Tiền thuế GTGT</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Tổng cộng tiền thanh toán</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Trạng thái hóa đơn</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Trạng thái CQT</th>
            </tr></thead><tbody>';

                $stt = 1;
                $reader->each(function ($sheet) use (&$stt) {
                    $rows = $sheet->toArray();

                    foreach ($rows as $row) {
                        if (empty($row['ngay_ky'])) {
                            continue;
                        }

                        try {
                            $ngayKy = Carbon::createFromFormat('d/m/Y', trim($row['ngay_ky']))->format('Y-m-d');
                        } catch (\Exception $e) {
                            continue;
                        }

                        $maSoThue = trim($row['ma_so_thue'] ?? '');
                        if (empty($maSoThue) && !empty($row['ten_cong_ty'])) {
                            if (preg_match('/\((.*?)\)/', $row['ten_cong_ty'], $matches)) {
                                $maSoThue = preg_replace('/\D/', '', $matches[1]);
                            }
                        }

                        $dataToInsert = [
                            'ngay_ky'           => $ngayKy,
                            'cty_mst'           => $maSoThue,
                            'tong_tien'         => (int) str_replace(['.', ','], '', $row['tong_cong_tien_thanh_toan'] ?? 0),
                            'cty_name'          => trim($row['ten_cong_ty'] ?? ''),
                            'tien_hang'         => (int) str_replace(['.', ','], '', $row['cong_tien_hang'] ?? 0),
                            'tien_thue_gtgt'    => (int) str_replace(['.', ','], '', $row['tien_thue_gtgt'] ?? 0),
                            'status_CQT'        => trim($row['trang_thai_cqt'] ?? ''),
                        ];

                        $exists = DB::table('hoa_don')->where([
                            'ngay_ky'   => $dataToInsert['ngay_ky'],
                            'cty_mst'   => $dataToInsert['cty_mst'],
                            'tong_tien' => $dataToInsert['tong_tien'],
                        ])->exists();

                        if (!$exists) {
                            DB::table('hoa_don')->insert($dataToInsert);
                        }

                        echo '<tr>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">' . $stt++ . '</td>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">' . ($row['ngay_ky'] ?? '') . '</td>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">' . ($row['ten_cong_ty'] ?? '') . '</td>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">' . ($row['ma_so_thue'] ?? '') . '</td>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">' . number_format($row['cong_tien_hang'] ?? 0) . '</td>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">' . number_format($row['tien_thue_gtgt'] ?? 0) . '</td>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">' . number_format($row['tong_cong_tien_thanh_toan'] ?? 0) . '</td>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">' . ($row['trang_thai_hoa_don'] ?? '') . '</td>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">' . ($row['trang_thai_cqt'] ?? '') . '</td>';
                        echo '</tr>';
                    }
                });

                echo '</tbody></table>';

            }, null, true, 'UTF-8', [
                'delimiter' => ';'
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Đã xảy ra lỗi khi xử lý file: ' . $e->getMessage());
        }

        return;
    }


    //  Xử lý tag
    /*public function xulyTag($post_id, $tags_data)
    {
        $id_updated = [];
        $tags = json_decode($tags_data);

        if (is_array($tags) && !empty($tags)) {
            foreach ($tags as $tag_name) {
                $tag_name = $tag_name->value;
                //  Tạo tag nếu chưa có
                $tag = Tag::where('name', $tag_name)->first();
                if (!is_object($tag)) {
                    $tag = new Tag();
                    $tag->name = $tag_name;
                    $tag->slug = str_slug($tag_name, '-');
                    $tag->type = 'post';
                    $tag->save();
                }

                $post_tag = PostTag::updateOrCreate([
                    'post_id' => $post_id,
                    'tag_id' => $tag->id,
                ], [

                ]);
                $id_updated[] = $post_tag->id;
            }
        }
        //  Xóa tag thừa
        PostTag::where('post_id', $post_id)->whereNotIn('id', $id_updated)->delete();

        return true;
    }*/
}
