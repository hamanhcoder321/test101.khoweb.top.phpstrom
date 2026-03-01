@extends(config('core.admin_theme').'.template')
@section('main')
    <form class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid {{ @$module['code'] }}"
          action="" method="POST"
          enctype="multipart/form-data">
        {{ csrf_field() }}
        <input name="return_direct" value="save_continue" type="hidden">
        <div class="row">
            <div class="col-lg-12">
                <!--begin::Portlet-->
                <div class="kt-portlet kt-portlet--last kt-portlet--head-lg kt-portlet--responsive-mobile"
                     id="kt_page_portlet">
                    <div class="kt-portlet__head kt-portlet__head--lg" style="">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">Thông tin {{ $module['label'] }}
                            </h3>
                        </div>
                        <div class="kt-portlet__head-toolbar">
                            <a href="/admin/{{ $module['code'] }}" class="btn btn-clean kt-margin-r-10">
                                <i class="la la-arrow-left"></i>
                                <span class="kt-hidden-mobile">Quay lại</span>
                            </a>
                            <div class="btn-group">
                                @if(@in_array('money_edit', $permissions))
                                    <button type="submit" class="btn btn-brand">
                                        <i class="la la-check"></i>
                                        <span class="kt-hidden-mobile">Lưu</span>
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
                                                    Lưu và tiếp tục
                                                </a>
                                            </li>
                                            <li class="kt-nav__item">
                                                <a class="kt-nav__link save_option" data-action="save_exit">
                                                    <i class="kt-nav__link-icon flaticon2-power"></i>
                                                    Lưu & Thoát
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
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Portlet-->
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-12">
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
                        <div class="kt-portlet__body">
                            <div class="kt-section kt-section--first">
                                @foreach($module['form']['general_tab'] as $field)
                                    @php
                                        $field['value'] = @$result->{$field['name']};
                                    @endphp
                                    <div class="form-group-div form-group {{ @$field['group_class'] }}"
                                         id="form-group-{{ $field['name'] }}">
                                        @if($field['type'] == 'custom')
                                            @include($field['field'], ['field' => $field])
                                        @else
                                            <label for="{{ $field['name'] }}">{{ @$field['label'] }} @if(strpos(@$field['class'], 'require') !== false)
                                                    <span class="color_btd">*</span>@endif</label>
                                            <div class="col-xs-12">
                                                @include(config('core.admin_theme').".form.fields.".$field['type'], ['field' => $field])
                                                <span class="form-text text-muted">{!! @$field['des'] !!}</span>
                                                <span class="text-danger">{{ $errors->first($field['name']) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <!--end::Form-->
                </div>
                <!--end::Portlet-->
            </div>

        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                Phiếu thu (± 2 ngày)
                            </h3>
                        </div>
                    </div>

                    <div class="kt-portlet__body">
                        @if(!empty($bill_receipts) && $bill_receipts->count())
                            <table class="table table-striped table-bordered table-hover table-checkable">
                                <thead class="thead-light">
                                <tr>
                                    <th>Ngày</th>
                                    <th>Số tiền</th>
                                    <th>Ảnh CK</th>
                                    <th>Tài khoản nhận</th>
                                    <th>Chi tiết</th>
                                    <th>Người tạo</th>
                                    <th>Hợp đồng</th>
                                </tr>
                                </thead>

                                <tbody>
                                @foreach($bill_receipts as $r)
                                    <tr>
                                        {{-- Ngày --}}
                                        <td class="kt-font-bold">
                                            {{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}
                                        </td>

                                        {{-- Số tiền --}}
                                        <td class="kt-font-danger kt-font-bold">
                                            {{ number_format($r->price) }}đ
                                        </td>

                                        <td class="text-center">
                                            @php
                                                $ckImage = !empty($r->image)
                                                    ? asset('filemanager/userfiles/'.$r->image)
                                                    : asset('filemanager/userfiles/z6946505242312_e3a6a0e0d8a04f1990b950390c8a7af0.jpg');
                                            @endphp

                                            <img src="{{ $ckImage }}"
                                                 data-image="{{ $ckImage }}"
                                                 class="ck-thumb"
                                                 style="height:40px;border-radius:4px;cursor:pointer;object-fit:cover"
                                                 alt="Ảnh CK">
                                        </td>



                                        {{-- Tài khoản nhận --}}
                                        <td class="kt-font">
                                            {{ $r->receiving_account }}
                                        </td>

                                        {{-- CHI TIẾT (kiểu giống ảnh bạn gửi) --}}
                                        <td style="white-space: normal; line-height: 1.6">
                                            <div><strong>ID:</strong> {{ $r->id }}</div>
                                            <div>
                                                <strong>Số hóa đơn:</strong>
                                                {{ !empty($r->so_hoa_don) ? ltrim($r->so_hoa_don, '0') : '-' }}
                                            </div>

                                            <div><strong>TK nhận:</strong> {{ $r->receiving_account }}</div>

                                            <div><strong>Nội dung CK:</strong>
                                                {{ $r->note ?: '-' }}
                                            </div>
                                            @if(!empty($r->saler))
                                                <div>
                                                    <strong>NV:</strong> {{ $r->saler->name }}
                                                </div>
                                            @endif

                                        </td>

                                        {{-- Người tạo --}}
                                        <td class="kt-font">
                                            {{ optional($r->admin)->name }}
                                        </td>

                                        {{-- HỢP ĐỒNG + NGÀY KÝ --}}
                                        <td>
                                            <a href="{{ url('admin/bill/edit/'.$r->bill_id) }}"
                                               target="_blank"
                                               class="kt-font-info kt-font-bold">
                                                Số: {{ $r->bill_id }}
                                            </a>

                                            @if(!empty($r->bill) && !empty($r->bill->registration_date))
                                                <div class="text-muted" style="font-size: 12px">
                                                    {{ \Carbon\Carbon::parse($r->bill->registration_date)->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="kt-font-danger kt-font-bold">
                                Không tìm thấy phiếu thu phù hợp trong khoảng ± 2 ngày.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>


        {{-- MODAL PHÓNG ẢNH CK --}}
        <div class="modal fade" id="ckImageModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ảnh chuyển khoản</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body text-center">
                        <img id="ckImagePreview"
                             src=""
                             style="max-width:100%;max-height:80vh;border-radius:6px"
                             alt="Ảnh CK">
                    </div>
                </div>
            </div>
        </div>

    </form>
@endsection
@section('custom_head')
    <link type="text/css" rel="stylesheet" charset="UTF-8"
          href="{{ asset(config('core.admin_asset').'/css/form.css') }}">
    <script src="{{asset('ckeditor/ckeditor.js') }}"></script>
    <script src="{{asset('ckfinder/ckfinder.js') }}"></script>
    <script src="{{asset('libs/file-manager.js') }}"></script>
    {{--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>--}}
@endsection
@section('custom_footer')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.ck-thumb').forEach(function (img) {
                img.addEventListener('click', function () {
                    let src = this.getAttribute('data-image');
                    document.getElementById('ckImagePreview').src = src;
                    $('#ckImageModal').modal('show');
                });
            });
        });
    </script>

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
    {{--    <script src="{{asset('backend/themes/metronic1/js/pages/crud/file-upload/dropzonejs.js') }}"></script>--}}
@endsection