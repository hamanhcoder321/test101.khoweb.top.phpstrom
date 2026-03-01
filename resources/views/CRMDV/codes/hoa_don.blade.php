@extends(config('core.admin_theme').'.template')
@section('main')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid import" action="{{ route('codes.hoa_don.import') }}" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="aF4zy7Y7wopbucx2J6gYDQ8rHIJQjF3RUEymi6qV">
        <input name="table" value="check_web_server" type="hidden">
        <input name="return_direct" value="save_exit" type="hidden">
        <div class="row">
            <div class="col-xs-12 col-md-12">
                <!--begin::Portlet-->
                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                Import hóa đơn
                            </h3>
                        </div>
                    </div>
                    <!--begin::Form-->
                    <div class="kt-form">
                        {{-- Flash messages --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="la la-check-circle"></i> {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="la la-exclamation-triangle"></i> {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Vui lòng kiểm tra lại:</strong>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
                            </div>
                        @endif

                        <span class="alert text-danger" style="margin: 0;">Tối đa chỉ import được 998 bản ghi trong 1 lần</span>

                        <div class="kt-portlet__body">
                            <div class="kt-section kt-section--first">
                                <div class="form-group-div form-group" id="form-group-btn_download_excel_demo">
                                    <label for="btn_download_excel_demo">Tải file Excel mẫu</label>
                                    <div class="col-xs-12">
                                        <a href="{{ url('filemanager/userfiles/excel_default/DanhSachHoaDon.xlsx') }}"
                                           class="btn btn-brand" target="_blank">
                                            <i class="la la-download"></i> Tải về file mẫu
                                        </a>
                                        <span class="form-text text-muted"></span>
                                    </div>
                                </div>

                                <div class="form-group-div form-group" id="form-group-file">
                                    <label for="file">
                                        Nhập file Excel <span class="color_btd">*</span>
                                    </label>
                                    <div class="col-xs-12">
                                        <input type="file"
                                               name="file"
                                               id="file"
                                               class="form-control {{ $errors->has('file') ? 'is-invalid' : '' }}"
                                               required
                                               accept=".xlsx,.xls,.csv">

                                        @if ($errors->has('file'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('file') }}</span>
                                        @endif

                                        <span class="form-text text-muted">
            Nhập đúng định dạng theo file mẫu (xlsx/xls/csv).
        </span>
                                    </div>
                                </div>


                                <button type="submit" class="btn btn-brand" id="btn-import">
                                    <i class="la la-upload"></i> Import
                                </button>
                            </div>
                        </div>
                    </div>
                    <!--end::Form-->

                </div>
                <!--end::Portlet-->
            </div>

            <div class="col-xs-12 col-md-12">
                <!--begin::Portlet-->
                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                So sánh tiền nhận vào & tiền xuất hoá đơn ra
                            </h3>
                        </div>
                    </div>
                    <!--begin::Form-->
                    <div class="kt-form">
                        <div class="kt-portlet__body">
                            <div class="d-flex flex-wrap align-items-end mb-3">
                                <div class="mx-2">Ngày đầu <input type="date" name="date1-ho-don" id="date1-ho-don" class="form-control"></div>
                                <div class="mx-2">Ngày cuối <input type="date" name="date2-ho-don" id="date2-ho-don" class="form-control"></div>
                                <button type="button" class="btn btn-primary mx-3" id="tra-cuu">Tra cứu</button>
                                <script>
                                    $('#tra-cuu').on('click', function () {
                                        var ngaydau = $('#date1-ho-don').val();
                                        var ngaycuoi = $('#date2-ho-don').val();
                                        $('.table-hoa-don').empty();
                                        $.ajax({
                                            url: '{{route('table.hoaDon')}}',
                                            type: 'POST',
                                            data: {
                                                ngaydau: ngaydau,
                                                ngaycuoi: ngaycuoi
                                            },
                                            success: function(response) {
                                                // Xử lý khi thành công
                                                console.log(response);
                                                response.hoadon.forEach(item => {
                                                    var tong_tien_nhan = new Intl.NumberFormat('vi-VN', {
                                                        style: 'decimal',
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2
                                                    }).format(item.tong_tien_nhan ?? 0);
                                                    var tien_hoa_don = new Intl.NumberFormat('vi-VN', {
                                                        style: 'decimal',
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2
                                                    }).format(item.tong_tien1 ?? 0);
                                                    $('.table-hoa-don').append(`
                                                                <tr>
                                                                    <td>${item.cty_name}</td>
                                                                    <td>${item.cty_mst}</td>
                                                                    <td>${tien_hoa_don}</td>
                                                                    <td>${tong_tien_nhan}</td>
                                                                </tr>
                                                            `);
                                                });
                                            },

                                            error: function(xhr, status, error) {
                                                // Xử lý khi có lỗi
                                                console.log(xhr.responseText);
                                            }

                                        })
                                    })
                                </script>
                            </div>
                            <div >
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th scope="col">Tên công ty</th>
                                        <th scope="col">Mã số thuế</th>
                                        <th scope="col">Tổng tiền xuất hóa đơn</th>
                                        <th scope="col">Tổng tiền nhận</th>
                                    </tr>
                                    </thead>
                                    <tbody style="font-size: 13px" class="table-hoa-don">

                                    </tbody>
                                </table>
                            </div>
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
            window.location.href = "/admin/{{ $module['code'] }}/download-excel-demo?module=DanhSachHoaDon";
            console.log("/admin/{{ $module['code'] }}/download-excel-demo?module=")
        }

        //  Set link import
        var module_selected = $('select[name=module] option:first').attr('value');
        @if(isset($_GET['table']))
            module_selected = '{{ substr($_GET['table'], -1) == 's' ? substr($_GET['table'], 0, -1) : $_GET['table'] }}';
        @endif

    </script>
@endpush