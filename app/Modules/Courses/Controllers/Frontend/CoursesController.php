<?php

namespace App\Modules\Courses\Controllers\Frontend;

use App\CRMDV\Models\Admin;
use App\CRMDV\Models\HoaDon;
use App\CRMDV\Models\Invoice;
use App\CRMDV\Models\PackDetail;
use App\Http\Controllers\Controller;

use App\Modules\Courses\Models\Chapter;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\Lesson;


use App\Modules\Logistics\Controllers\Admin\CURDBaseController;
use App\Modules\Logistics\Models\Bill;
use App\Http\Helpers\CommonHelper;
use App\Modules\Post\Models\BackDetail;
use Illuminate\Http\Request;
use App\Modules\Logistics\Models\Codes;
use App\Modules\Logistics\Models\Theme;
use App\Modules\Logistics\Models\Tag;
use Validator;
use App\Modules\Logistics\Models\PostTag;
use App\Modules\Logistics\Models\BillProgress;

class CoursesController extends Controller
{



    public function list(Request $request, string $id=null)
    {
//        $request->merge(['category_id' => $id]);

//        $data = $this->getDataList($request);
//        $data['category_id'] = $id;
        $admin_id=\Auth::guard('admin')->user()->id;
        $admin = \App\Modules\Courses\Models\Admin::find($admin_id);
        if($id!=null) {
            $list_course = \App\Modules\Courses\Models\Course::where('multi_cat', 'like', '%|' . $id . '|%')->paginate(30);
        }
        else{
            $list_course = \App\Modules\Courses\Models\Course::orderBy('id', 'desc')->paginate(30);
        }
        //tìm kiếm
        if ($request->has('keyword')) {
            $keyword = $request->input('keyword');
            $list_course= \App\Modules\Courses\Models\Course::where('name', 'like', '%' . $keyword . '%')->paginate(30);
        }
        return view('Courses.frontend.pages.list', ['list_course'=>$list_course, 'category_id'=>$id], ['admin'=>$admin]);
    }
    public function baiCanThi(Request $request)
    {
//        $request->merge(['category_id' => $id]);

//        $data = $this->getDataList($request);
//        $data['category_id'] = $id;
        $admin_id=\Auth::guard('admin')->user()->id;
        $admin = \App\Modules\Courses\Models\Admin::find($admin_id);
        {
            $list_course = \App\Modules\Courses\Models\Course::where('type', '2')->orderBy('ngay_thuc_hien', 'asc')->paginate(30);
        }
        //tìm kiếm
        if ($request->has('keyword')) {
            $keyword = $request->input('keyword');
            $list_course= \App\Modules\Courses\Models\Course::where('type', '2')
                ->where('name', 'like', '%' . $keyword . '%')
                ->orderBy('ngay_thuc_hien', 'asc')
                ->paginate(30);
        }
        return view('Courses.frontend.pages.bai_can_thi', ['list_course'=>$list_course], ['admin'=>$admin]);
    }
    public function loTrinhHoc(Request $request)
    {

        $admin_id=\Auth::guard('admin')->user()->id;
        $admin = \App\Modules\Courses\Models\Admin::find($admin_id);
        {
            $list_course = \App\Modules\Courses\Models\Course::orderBy('ngay_thuc_hien', 'asc')->paginate(30);
        }
        //tìm kiếm
        if ($request->has('keyword')) {
            $keyword = $request->input('keyword');
            $list_course= \App\Modules\Courses\Models\Course::where('name', 'like', '%' . $keyword . '%')
                ->orderBy('ngay_thuc_hien', 'asc')
                ->paginate(30);
        }
        return view('Courses.frontend.pages.lo_trinh_hoc', ['list_course'=>$list_course], ['admin'=>$admin]);
    }
    public function detail(string $id)
    {
        $admin_id=\Auth::guard('admin')->user()->id;
        $admin = \App\Modules\Courses\Models\Admin::find($admin_id);
        $course = \App\CRMDV\Models\Course::find($id);
        //lấy ra danh sách các danh mục
        $categories = explode('|', $course->multi_cat);
        //loại bỏ phần tử đầu và cuối vì là khoảng trắng
        $categories = array_filter($categories);
        $related_courses = \App\CRMDV\Models\Course::where(function ($query) use ($categories) {
            foreach ($categories as $category) {
                $query->orWhere('multi_cat', 'like', '%' . $category . '%');
            }
        })->where('id', '!=', $id) // Loại bỏ bài viết hiện tại
        ->limit(3) // Giới hạn số lượng bài viết trả về
        ->get();

        //lấy ra số chươngdd
        $chapter = Chapter::where('course_id', $id)->get();

        $count_chapter = 0;
        $count_lesson = 0;

        if($chapter){
            $count_chapter = count($chapter);
            if($count_chapter){
                foreach ($chapter as $item ){
                    $lesson = Lesson::where('course_chapter_id', $item->id)->get();
                    $count_lesson +=count($lesson);
                }
            }
        }



        //lấy ra số chươngdd
        $chapter = Chapter::where('course_id', $id)->orderBy('order_no', 'desc')->get();

        $count_chapter = 0;
        $count_lesson = 0;

        if($chapter){
            $count_chapter = count($chapter);
            if($count_chapter){
                foreach ($chapter as $item ){
                    $lesson = Lesson::where('course_chapter_id', $item->id)->get();
                    $count_lesson +=count($lesson);
                }
            }
        }
        $lesson =null;


        return view('Courses.frontend.pages.detail',
            ['course'=>$course, 'related_courses'=>$related_courses, 'admin'=>$admin,
                'count_chapter'=>$count_chapter, 'count_lesson'=>$count_lesson,
                'chapter'=>$chapter, 'lesson'=>$lesson
            ]);
    }
    public function lessonVideo(string $id, string $lesson_id)
    {
//        dd($lesson_id);
        $admin_id=\Auth::guard('admin')->user()->id;
        $admin = \App\Modules\Courses\Models\Admin::find($admin_id);
        $course = \App\CRMDV\Models\Course::find($id);
        //lấy ra danh sách các danh mục
        $categories = explode('|', $course->multi_cat);
        //loại bỏ phần tử đầu và cuối vì là khoảng trắng
        $categories = array_filter($categories);
        $related_courses = \App\CRMDV\Models\Course::where(function ($query) use ($categories) {
            foreach ($categories as $category) {
                $query->orWhere('multi_cat', 'like', '%' . $category . '%');
            }
        })->where('id', '!=', $id) // Loại bỏ bài viết hiện tại
        ->limit(3) // Giới hạn số lượng bài viết trả về
        ->get();
        //lấy ra số chươngdd
        $chapter = Chapter::where('course_id', $id)->orderBy('order_no', 'desc')->get();

        $count_chapter = 0;
        $count_lesson = 0;

        if($chapter){
            $count_chapter = count($chapter);
            if($count_chapter){
                foreach ($chapter as $item ){
                    $lesson = Lesson::where('course_chapter_id', $item->id)->get();
                    $count_lesson +=count($lesson);
                }
            }
        }
        //lấy ra bài học
        $lesson=Lesson::find($lesson_id);


//        dd($related_courses);
        return view('Courses.frontend.pages.detail',
            ['course'=>$course, 'related_courses'=>$related_courses, 'admin'=>$admin,
                'count_chapter'=>$count_chapter, 'count_lesson'=>$count_lesson,
                'chapter'=>$chapter, 'lesson'=>$lesson
            ]);

    }
    public function view(Request $request)
    {
        $where = $this->filterSimple($request);
        $listItem = $this->model->whereRaw($where);
        $listItem = $this->quickSearch($listItem, $request);
        if ($this->whereRaw) {
            $listItem = $listItem->whereRaw($this->whereRaw);
        }
//        $listItem = $this->appendWhere($listItem, $request);

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

        //  Get data default (param_url, filter, module) for
        $data['module'] = [
            'code' => 'package',
            'table_name' => 'package',
            'label' => 'package',
            'modal' => '\App\Modules\Logistics\Models\Package',
            'list' => [
                ['name' => 'code', 'type' => 'text', 'label' => 'RG CODE'],
                ['name' => 'contact', 'type' => 'text', 'label' => 'CONTACT'],
                ['name' => 'address', 'type' => 'text', 'label' => 'ADDRESS'],
                ['name' => 'sentdate', 'type' => 'text', 'label' => 'SENT DATE'],
                ['name' => 'tracking', 'type' => 'text', 'label' => 'TRACKING'],
                ['name' => 'service', 'type' => 'text', 'label' => 'SERVICE'],
                ['name' => 'bill', 'type' => 'text', 'label' => 'BILL/INVOICE'],
            ]
        ];
        $data['quick_search'] = $this->quick_search;
        $data['filter'] = $this->filter;

        //  Set data for seo
        $data['page_title'] = $this->module['label'];
        $data['page_type'] = 'list';

        return view('Logistics.package.view')->with($data);
    }

    public function appendWhere($query, $request)
    {

        return $query;
    }

    public function formupdate($id)
    {
        $bill = \App\Modules\Post\Models\Bill::where('id', $id)->first();
        if($bill->type == 'doc'){
            return view('Logistics.bill.doc_hoa_don', compact('bill'));
        }else{
            $backdetail = BackDetail::where('bill_id', $id)->get();
            $invoice = \App\Modules\Post\Models\Invoice::where('bill_id', $id)->get();
            return view('Logistics.bill.pack_hoa_don', compact('bill', 'backdetail', 'invoice'));
        }


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

    public function update(Request $request, $id)
    {
        try {
            \DB::beginTransaction();
            if ($request->doc !== null) {
                // dd($request->all());
                $hoadon = HoaDon::where('id', $id)->update([
                    'services' => $request->dichvuvanchuyen,
                    'ref_code' => $request->refcode,
                    'hawb_code' => $request->hawbCode,
                    'rg_code' => $request->rgcode,
                    'number_commodity' => $request->sokien,
                    'weight' => $request->cannang,
                    'type' => 'doc',
                    'send_company_name' => $request->congtygui,
                    'sender_name' => $request->nguoilhgui,
                    'sender_address' => $request->diachilienhegui,
                    'sender_tel' => $request->sdtgui,
                    'email' => $request->emailgui,
                    'receiver_company' => $request->congtynhan,
                    'receiver_name' => $request->nguoilhnhan,
                    'receiver_tel' => $request->sdtnhan,
                    'country' => $request->quocgianhan,
                    'postal_code' => $request->mabuuchinh,
                    'city' => $request->thanhpho,
                    'province' => $request->tinh,
                    'receiver_address1' => $request->diachinhan1,
                    'receiver_address2' => $request->diachinhan2,
                    'receiver_address3' => $request->diachinhan3
                ]);
                if ($hoadon !== null) {
                    \DB::commit();
                    CommonHelper::one_time_message('success', 'Cập nhật thành công!');
                    return redirect()->back();
                } else {
                    \DB::rollback();
                    CommonHelper::one_time_message('error', 'Lỗi cập nhật. Vui lòng load lại trang và thử lại!');
                    return redirect()->back();
                }

            } else {
                $hoadon = HoaDon::where('id', $id)->update([
                    'services' => $request->dichvuvanchuyen,
                    'ref_code' => $request->refcode,
                    'totalCharfedWeight'=>$request->totalCharfedWeight,
                    'totalNumberPack'=>$request->totalNumberPack,
                    'hawb_code' => $request->hawbCode,
                    'rg_code' => $request->rgcode,
                    'content' => $request->tenhang,
                    'invoice_value' => $request->giatrikhaibao,
                    'type' => 'pack',
                    'send_company_name' => $request->congtygui,
                    'sender_name' => $request->nguoilhgui,
                    'sender_address' => $request->diachilienhegui,
                    'sender_tel' => $request->sdtgui,
                    'email' => $request->emailgui,
                    'receiver_company' => $request->congtynhan,
                    'receiver_name' => $request->nguoilhnhan,
                    'receiver_tel' => $request->sdtnhan,
                    'country' => $request->quocgianhan,
                    'postal_code' => $request->mabuuchinh,
                    'city' => $request->thanhpho,
                    'province' => $request->tinh,
                    'receiver_address1' => $request->diachinhan1,
                    'receiver_address2' => $request->diachinhan2,
                    'receiver_address3' => $request->diachinhan3,
                    'export_as' => $request->invoiceExportFormat
                ]);
                if ($hoadon !== null) {
                    $quantitypack = $request->quantitypack;
                    $typeback = $request->typeback;
                    $lengthpack = $request->lengthpack;
                    $widthpack = $request->widthpack;
                    $heightpack = $request->heightpack;
                    $weightpack = $request->weightpack;
                    $tlquydoi = $request->tlquydoi;
                    $tltinh = $request->tltinh;
                    PackDetail::where('bill_id', $id)->delete();
                    Invoice::where('bill_id', $id)->delete();
                    foreach ($quantitypack as $key => $item) {
                        PackDetail::create([
                            'bill_id' => $id,
                            'quantity' => $item,
                            'type' => $typeback[$key],
                            'length' => $lengthpack[$key],
                            'width' => $widthpack[$key],
                            'height' => $heightpack[$key],
                            'weight' => $weightpack[$key],
                            'converted_weight' => $tlquydoi[$key],
                            'charged_weight' => $tltinh[$key]
                        ]);
                    }

                    $description = $request->description;
                    $quantityinvoice = $request->quantityinvoice;
                    $unitinvoice = $request->unitinvoice;
                    $unitPriceinvoice = $request->unitPriceinvoice;
                    $subTotalinvoice = $request->subTotalinvoice;
                    foreach ($description as $key => $item) {
                        Invoice::create([
                            'bill_id' => $id,
                            'goods_detail' => $item,
                            'quantity' => $quantityinvoice[$key],
                            'unit' => $unitinvoice[$key],
                            'price' => $unitPriceinvoice[$key],
                            'total_value' => $subTotalinvoice[$key]
                        ]);
                    }
                    \DB::commit();
                    CommonHelper::one_time_message('success', 'Cập nhật thành công!');
                    return redirect()->back();
                } else {
                    \DB::rollback();
                    CommonHelper::one_time_message('error', 'Lỗi cập nhật. Vui lòng load lại trang và thử lại!');
                    return redirect()->back();
                }
            }

        } catch (\Exception $ex) {
            \DB::rollback();
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
            PackDetail::where('bill_id', $request->id)->delete();
            Invoice::where('bill_id', $request->id)->delete();
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

    public function exportExcel($request, $data)
    {
        \Excel::create(str_slug($this->module['label'], '_') . '_' . date('d_m_Y'), function ($excel) use ($data) {

            // Set the title
            $excel->setTitle($this->module['label'] . ' ' . date('d m Y'));

            $excel->sheet(str_slug($this->module['label'], '_') . '_' . date('d_m_Y'), function ($sheet) use ($data) {

                $field_name = [];
                foreach ($this->getAllFormFiled() as $field) {
                    if (!isset($field['no_export']) && isset($field['label'])) {
                        $field_name[] = $field['label'];
                    }
                }

                //   thêm cột tỉnh / huyện / xã
//                $field_name[] = 'Tỉnh';
//                $field_name[] = 'Huyện';
//                $field_name[] = 'Xã';
//                $field_name[] = 'Tạo lúc';
//                $field_name[] = 'Cập nhập lần cuối';

                $sheet->row(1, $field_name);

                $k = 2;

                foreach ($data as $value) {
                    $data_export = [];
//                    $data_export[] = $value->id;
                    foreach ($this->getAllFormFiled() as $field) {
                        if (!isset($field['no_export']) && isset($field['label'])) {
                            try {
                                if ($field['label'] == 'Mô tả chi tiết') {
                                    $dataInput = $value->{$field['name']};
                                    $data_export[] = strip_tags($dataInput);
                                } elseif (in_array($field['type'], ['text', 'number', 'textarea', 'textarea_editor', 'date', 'datetime-local', 'email', 'hidden', 'checkbox', 'textarea_editor', 'textarea_editor2', 'custom', 'radio', 'price_vi'])) {
                                    if (in_array(CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name'), ['cvkd_parttime'])) {
                                        if ($field['label'] == 'Địa chỉ chi tiết') {
                                            $data_export[] = '--Đã ẩn đối với quyền cvkd parttime--';
                                        } else {
                                            $data_export[] = $value->{$field['name']};
                                        }
                                    } else {
                                        $data_export[] = $value->{$field['name']};
                                    }
                                } elseif (in_array($field['type'], [
                                    'relation', 'select_model', 'select2_model', 'select2_ajax_model', 'select_model_tree',

                                ])) {
                                    $data_export[] = @$value->{$field['object']}->{$field['display_field']};
                                } elseif ($field['type'] == 'select') {
                                    $data_export[] = @$field['options'][$value->{$field['name']}];
                                } elseif (in_array($field['type'], ['file', 'file_editor2'])) {
                                    $data_export[] = \URL::asset('public/filemanager/userfiles/' . @$value->{$field['name']});
                                } elseif (in_array($field['type'], ['file_editor_extra'])) {
                                    $items = explode('|', @$value->{$field['name']});
                                    foreach ($items as $item) {
                                        $data_export[] = \URL::asset('public/filemanager/userfiles/' . @$item) . ' | ';
                                    }
                                } else {
                                    $data_export[] = $field['label'];
                                }
                            } catch (\Exception $ex) {
                                $data_export[] = $ex->getMessage();
                            }
                        }
                    }

                    //  xuất ra tỉnh / huyện / xã
//                    $data_export[] = @$value->province->name;
//                    $data_export[] = @$value->district->name;
//                    $data_export[] = @$value->ward->name;
//                    $data_export[] = @$value->created_at;
//                    $data_export[] = @$value->updated_at;
                    // dd($this->getAllFormFiled());
                    $sheet->row($k, $data_export);
                    $k++;
                }
            });
        })->download('xlsx');
    }

    public function ajaxGetInfo($id)
    {
        $data = $this->model->find($id);
        if (!is_object($data)) abort(404);
        $service = $data->service->name_vi;
        // tăng số lượt xem thêm 1
        $data->luot_xem += 1;
        $data->save();

        // lấy thông tin đầu chủ
        $dauchu = \App\Modules\Logistics\Models\Admin::query()->where('id', $data->admin_id)->first();
        $anhDauChu = asset('/filemanager/userfiles/' . $dauchu->image);


        //lay thong tin phong ban
        $phongban = \App\Modules\Logistics\Models\Phong_ban::query()->where('id', $dauchu->phong_ban_id)->first();

        $imagePath = asset('/filemanager/userfiles/' . $data->image);
        $anhSoDo = asset('/filemanager/userfiles/' . $data->so_do_va_hop_dong_chu_nha);
        // image chi tiet
        $imagePaths = explode('|', $data->image_extra);
        $fullPaths = array_map(function ($path) {
            return asset('/filemanager/userfiles/' . $path);
        }, $imagePaths);

        // image sổ đỏ và hợp đồng với chủ nhà
        $imageRedBook = explode('|', $data->so_do_va_hop_dong_chu_nha);
        $imageRedBooks = array_map(function ($path) {
            return asset('/filemanager/userfiles/' . $path);
        }, $imageRedBook);


        $show = true;
        if ($data->admin_id == \Auth::guard('admin')->user()->id || \Auth::guard('admin')->user()->super_admin == 1 || CommonHelper::has_permission(\Auth::guard('admin')->user()->id, 'hcns_195')) {
            $show = true;
        } else {
            $show = false;
        }
        return response()->json([
            'status' => true,
            'data' => $data,
            'service' => $service,
            'imageRedBooks' => $imageRedBooks,
            'imagePaths' => $fullPaths,
            'dauchu' => $dauchu,
            'anhDauChu' => $anhDauChu,
            'phongban' => $phongban,
            'show' => $show
        ]);

    }

    public function ajaxGetImage($id)
    {
//        $data = $id;
        $data = $this->model->find($id);
        $imagePaths = explode('|', $data->image_extra);
        $fullPaths = array_map(function ($path) {
            return asset('/filemanager/userfiles/' . $path);
        }, $imagePaths);
        return Response()->json([
            'data' => $data,
            'fullPaths' => $fullPaths
        ]);
    }

}

