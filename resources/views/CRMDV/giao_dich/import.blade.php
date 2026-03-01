@extends(config('core.admin_theme').'.template')
@section('main')
    <form class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid giao_dich"
          action="" method="POST"
          enctype="multipart/form-data">
        {{ csrf_field() }}
        <input name="table" value="{{ @$_GET['table'] }}" type="hidden">
        <input name="return_direct" value="save_exit" type="hidden">
        <div class="row">
            <div class="col-lg-12">
                <!--begin::Portlet-->
                <div class="kt-portlet kt-portlet--last kt-portlet--head-lg kt-portlet--responsive-mobile"
                     id="kt_page_portlet">
                    <div class="kt-portlet__head kt-portlet__head--lg" style="">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">{{trans('admin.add')}} Sao kê ngân hàng
                            </h3>
                        </div>
                        <div class="kt-portlet__head-toolbar">
                            <a href="/admin/giao_dich" class="btn btn-clean kt-margin-r-10">
                                <i class="la la-arrow-left"></i>
                                <span class="kt-hidden-mobile">{{trans('admin.back')}}</span>
                            </a>
                            <div class="btn-group">

                                <button type="submit" class="btn btn-brand">
                                    <i class="la la-check"></i>
                                    <span class="kt-hidden-mobile">{{trans('admin.save')}}</span>
                                </button>
                                <button type="button"
                                        class="btn btn-brand dropdown-toggle dropdown-toggle-split"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <ul class="kt-nav">
                                        <li class="kt-nav__item">
                                            <a class="kt-nav__link save_option" data-action="save_continue">
                                                <i class="kt-nav__link-icon flaticon2-reload"></i>
                                                {{trans('admin.save_and_continue')}}
                                            </a>
                                        </li>
                                        <li class="kt-nav__item">
                                            <a class="kt-nav__link save_option" data-action="save_exit">
                                                <i class="kt-nav__link-icon flaticon2-power"></i>
                                                {{trans('admin.save_and_quit')}}
                                            </a>
                                        </li>
                                        <li class="kt-nav__item">
                                            <a class="kt-nav__link save_option" data-action="save_create">
                                                <i class="kt-nav__link-icon flaticon2-add-1"></i>
                                                {{trans('admin.save_and_create')}}
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Portlet-->
            </div>
        </div>

        <div class="row">

            <div class="col-xs-12 col-md-6">
                <!--begin::Portlet-->
                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                {{trans('admin.basic_information')}}
                            </h3>
                        </div>
                    </div>
                    <!--begin::Form-->
                    <div class="kt-form">
                        <span class="alert text-danger" style="margin: 0;">Tối đa chỉ import được 999 bản ghi trong 1 lần</span>
                        <div class="kt-portlet__body">
                            <div class="kt-section kt-section--first">
                                <div class="form-group-div form-group " id="form-group-module">
                                    <label for="module">Chọn module <span class="color_btd">*</span></label>
                                    <div class="col-xs-12">
                                        <select class="form-control required" id="module" required="" name="module">
                                            <option value="giao_dich" selected="">Sao kê ngân hàng</option>
                                        </select>
                                    </div>                                                                            </div>
                                <div class="form-group-div form-group " id="form-group-btn_download_excel_demo">
                                    <label for="btn_download_excel_demo">Tải file Excel mẫu </label>
                                    <div class="col-xs-12">
                                        <a href="{{ asset('filemanager/userfiles/excel_default/sao_ke_ngan_hang_26_12_2025.xlsx') }}"
                                           class="btn btn-brand"
                                           download>
                                            <i class="la la-download"></i> Tải về file mẫu
                                        </a>

                                        <span class="text-danger"></span>
                                        <span class="form-text text-muted"></span>
                                    </div>
                                </div>
                                <div class="form-group-div form-group " id="form-group-file">
                                    <label for="file">Nhập file Excel                                                     <span class="color_btd">*</span></label>
                                    <div class="col-xs-12">
                                        <input type="file" name="file" class="form-control required" required="" id="file" value="">                                                <span class="text-danger"></span>
                                        <span class="form-text text-muted">Nhập vào file excel mà bạn muốn import dữ liệu. Lưu ý: hệ thống chỉ nhận dữ liệu ở các cột đã được khai báo trong file mẫu</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Form-->
                </div>
                <!--end::Portlet-->
            </div>

{{--            <div class="col-xs-12 col-md-6">--}}
{{--                <!--begin::Portlet-->--}}
{{--                <div class="kt-portlet">--}}
{{--                    <div class="kt-portlet__head">--}}
{{--                        <div class="kt-portlet__head-label">--}}
{{--                            <h3 class="kt-portlet__head-title">--}}
{{--                                Hướng dẫn--}}
{{--                            </h3>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <!--begin::Form-->--}}
{{--                    <div class="kt-form">--}}
{{--                        <div class="kt-portlet__body">--}}
{{--                            <ul>--}}
{{--                                <li><a href="https://youtu.be/08qs4CtnuO0" target="_blank">Hướng dẫn import đầu mối</a></li>--}}
{{--                            </ul>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <!--end::Form-->--}}
{{--                </div>--}}
{{--                <!--end::Portlet-->--}}
{{--            </div>--}}
        </div>
    </form>
@endsection
@section('custom_head')
    <link type="text/css" rel="stylesheet" charset="UTF-8"
          href="{{ asset(config('core.admin_asset').'/css/form.css') }}">


@endsection
@section('custom_footer')
    <script src="{{ asset(config('core.admin_asset').'/js/pages/crud/metronic-datatable/advanced/vertical.js') }}"
            type="text/javascript"></script>
    <script type="text/javascript" src="{{ asset('tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('tinymce/tinymce_editor.js') }}"></script>
    <script type="text/javascript">
        editor_config.selector = ".editor";
        editor_config.path_absolute = "{{ (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" }}/";
        tinymce.init(editor_config);
    </script>
    <script type="text/javascript" src="{{ asset(config('core.admin_asset').'/js/form.js') }}"></script>


@endsection
@push('scripts')
    <script>
        function downloadExcelDemo() {
            var module = $('select[name=module]').val();
            window.location.href = "/admin/giao_dich/download-excel-demo?module=giao_dich";
        }

        //  Set link import
        var module_selected = $('select[name=module] option:first').attr('value');
        @if(isset($_GET['table']))
            module_selected = '{{ substr($_GET['table'], -1) == 's' ? substr($_GET['table'], 0, -1) : $_GET['table'] }}';
        @endif
        $(document).ready(function () {
            $('form.import').attr('action', '/admin/giao_dich/import-excel');
        });

        $('select[name=module]').change(function () {
            $('form.import').attr('action', '/admin/giao_dich/import-excel');
        });
    </script>
@endpush