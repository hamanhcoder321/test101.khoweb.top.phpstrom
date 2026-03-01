@extends(config('core.admin_theme').'.template')
@section('main')
    <form class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid import" action="/admin/codes/check-web-server" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="aF4zy7Y7wopbucx2J6gYDQ8rHIJQjF3RUEymi6qV">
        <input name="table" value="check_web_server" type="hidden">
        <input name="return_direct" value="save_exit" type="hidden">
        <div class="row">
            <div class="col-lg-12">
                <!--begin::Portlet-->
                <div class="kt-portlet kt-portlet--last kt-portlet--head-lg kt-portlet--responsive-mobile" id="kt_page_portlet">
                    <div class="kt-portlet__head kt-portlet__head--lg" style="">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">Thêm Import
                            </h3>
                        </div>
                        <div class="kt-portlet__head-toolbar">

                            <div class="btn-group">

                                <button type="submit" class="btn btn-brand">
                                    <i class="la la-check"></i>
                                    <span class="kt-hidden-mobile">Lưu</span>
                                </button>
                                <button type="button" class="btn btn-brand dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <ul class="kt-nav">
                                        <li class="kt-nav__item">
                                            <a class="kt-nav__link save_option" data-action="save_continue">
                                                <i class="kt-nav__link-icon flaticon2-reload"></i>
                                                Lưu và tiếp tục
                                            </a>
                                        </li>
                                        <li class="kt-nav__item">
                                            <a class="kt-nav__link save_option" data-action="save_exit">
                                                <i class="kt-nav__link-icon flaticon2-power"></i>
                                                Lưu và thoát
                                            </a>
                                        </li>
                                        <li class="kt-nav__item">
                                            <a class="kt-nav__link save_option" data-action="save_create">
                                                <i class="kt-nav__link-icon flaticon2-add-1"></i>
                                                Lưu và tạo mới
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
                                Thông tin cơ bản
                            </h3>
                        </div>
                    </div>
                    <!--begin::Form-->
                    <div class="kt-form">
                        <span class="alert text-danger" style="margin: 0;">Tối đa chỉ import được 999 bản ghi trong 1 lần</span>
                        <div class="kt-portlet__body">
                            <div class="kt-section kt-section--first">
                                <div class="form-group-div form-group " id="form-group-module">
                                    <label for="module">Chọn module        <span class="color_btd">*</span></label>
                                    <div class="col-xs-12">
                                        <select class="form-control required" id="module" required="" name="module">
                                            <option value="user">Khách hàng / đối tác</option>
                                            <option value="admin">Thành viên quản lý</option>
                                            <option value="check_web_server" selected="">ds-web</option>
                                        </select>
                                    </div>                                                                            </div>
                                <div class="form-group-div form-group " id="form-group-btn_download_excel_demo">
                                    <label for="btn_download_excel_demo">Tải file Excel mẫu </label>
                                    <div class="col-xs-12">
                                        <button type="button" onclick="downloadExcelDemo();" class="btn btn-brand">
                                            <i class="la la-download"></i>
                                            <span class="kt-hidden-mobile">Tải về file mẫu</span>
                                        </button>                                                <span class="text-danger"></span>
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
                                <div class="form-group-div form-group " id="form-group-field_options">
                                    <label for="field_options">Tham số mặc định </label>
                                    <div class="col-xs-12">
                                        <style>
                                            .fieldwrapper > div:nth-child(1) {
                                                padding-left: 0;
                                            }
                                            .fieldwrapper >div {
                                                display: inline-block;
                                            }
                                        </style>
                                        <fieldset id="buildyourform-field_options" class="">
                                            <div class="fieldwrapper" id="field1">
                                                <div class="col-xs-5 col-md-5">Trường</div>
                                                <div class="col-xs-5 col-md-5">Giá trị</div>
                                                <div class="col-xs-2 col-md-2">
                                                </div>
                                            </div>
                                        </fieldset>
                                        <a class="btn btn-icon btn btn-label btn-label-brand btn-bold btn-add-dynamic" title="Thêm dòng mới">
                                            <i class="flaticon2-add-1"></i>
                                        </a>
                                        <script>
                                            $(document).ready(function () {
                                                $(".btn-add-dynamic").click(function () {
                                                    var lastField = $("#buildyourform-field_options div:last");
                                                    var intId = (lastField && lastField.length && lastField.data("idx") + 1) || 1;
                                                    var fieldWrapper = $('<div class="fieldwrapper" style="margin-bottom: 8px;" id="field' + intId + '"/>');
                                                    fieldWrapper.data("idx", intId);
                                                    var fKey = $('<div class="col-xs-5 col-md-5"><input type="text" class="form-control fieldname" name="field_options_key[]" placeholder="Trường"/></div>');
                                                    var fValue = $('<div class="col-xs-5 col-md-5"><input type="text" class="form-control fieldValue" name="field_options_value[]" placeholder="Giá trị"/></div>');
                                                    var removeButton = $('<div class="col-xs-2 col-md-2" style="left: 7px;"><i title="xóa hàng" style="cursor: pointer;" class="btn remove btn btn-outline-hover-danger btn-sm btn-icon btn-circle flaticon-delete" ></i>');

                                                    fieldWrapper.append(fKey);
                                                    fieldWrapper.append(fValue);
                                                    fieldWrapper.append(removeButton);
                                                    $("#buildyourform-field_options").append(fieldWrapper);
                                                });
                                                $('body').on('click', '.remove', function () {
                                                    $(this).parents('.fieldwrapper').remove();
                                                });
                                            });
                                        </script>                                                <span class="text-danger"></span>
                                        <span class="form-text text-muted">Các trường dữ liệu mà bạn muốn set cứng vào các bản ghi khi import dữ liệu vào</span>
                                    </div>
                                </div>

                                <div class="form-group-div form-group " id="form-group-note">
                                    <label for="note">Ghi chú </label>
                                    <div class="col-xs-12">
                                        <p id="textarea-note" style="color: #000; margin: 0;"></p>
                                        <textarea id="note" name="note" class="form-control "></textarea>

                                        <script>
                                            var callCountnote = 0
                                            function countCallFunctionnote() {
                                                if (callCountnote == 0) {
                                                    initFunctionnote();
                                                }
                                                callCountnote ++;
                                                return true;
                                            }

                                            function initFunctionnote() {
                                                textareaInitnote();
                                            }


                                            $('#textarea-note, #form-group-note').click(function () {
                                                $('#textarea-note').hide();
                                                $('#note').show().click();
                                            });
                                            $(document).ready(function () {
                                                $('#note').click(function () {
                                                    countCallFunctionnote();
                                                });
                                            });
                                            var observe;
                                            if (window.attachEvent) {
                                                observe = function (element, event, handler) {
                                                    element.attachEvent('on' + event, handler);
                                                };
                                            } else {
                                                observe = function (element, event, handler) {
                                                    element.addEventListener(event, handler, false);
                                                };
                                            }

                                            function textareaInitnote() {
                                                var note = document.getElementById('note');

                                                function resize() {
                                                    note.style.height = 'auto';
                                                    note.style.height = note.scrollHeight + 'px';
                                                }

                                                /* 0-timeout to get the already changed note */
                                                function delayedResize() {
                                                    window.setTimeout(resize, 0);
                                                }

                                                observe(note, 'change', resize);
                                                observe(note, 'cut', delayedResize);
                                                observe(note, 'paste', delayedResize);
                                                observe(note, 'drop', delayedResize);
                                                observe(note, 'keydown', delayedResize);

                                                note.focus();
                                                note.select();
                                                resize();
                                            }
                                        </script>                                                <span class="text-danger"></span>
                                        <span class="form-text text-muted"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Form-->
                </div>
                <!--end::Portlet-->
            </div>

            <div class="col-xs-12 col-md-6">
                <!--begin::Portlet-->
                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                Hướng dẫn
                            </h3>
                        </div>
                    </div>
                    <!--begin::Form-->
                    <div class="kt-form">
                        <div class="kt-portlet__body">
                            <ul>
                                <li><a href="https://youtu.be/08qs4CtnuO0" target="_blank">Hướng dẫn import đầu mối</a></li>
                            </ul>
                        </div>
                    </div>
                    <!--end::Form-->
                </div>
                <!--end::Portlet-->
            </div>
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
            window.location.href = "/admin/{{ $module['code'] }}/download-excel-demo?module=" + module;
        }

        //  Set link import
        var module_selected = $('select[name=module] option:first').attr('value');
        @if(isset($_GET['table']))
            module_selected = '{{ substr($_GET['table'], -1) == 's' ? substr($_GET['table'], 0, -1) : $_GET['table'] }}';
        @endif

    </script>
@endpush>>>