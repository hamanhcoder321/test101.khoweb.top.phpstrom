@extends(config('core.admin_theme').'.template')
@section('main')
    <div class="flex-between" style="margin-bottom: 20px;">
        <div>
            <h1 style="font-size:20px; font-weight:700;">⬆️ Thêm Import</h1>
            <p class="text-muted text-sm mt-1">Nhập danh sách tên miền từ file Excel</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('lead.import.sample') }}" class="btn btn-outline btn-sm">
                ⬇️ Tải file mẫu
            </a>
        </div>
    </div>
    <div style="display:grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start;">

        {{-- === FORM CARD === --}}
        <div class="card">
            <div class="card-header">
                <h2>📋 Thông tin Import</h2>
                <button type="submit" form="importForm" class="btn btn-primary btn-sm" id="btnSubmit">
                    ✔ Tiếp tục xem trước
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-info" style="margin-bottom: 20px;">
                    ℹ️ Tối đa <strong>500 bản ghi</strong> trong 1 lần import.
                </div>

                <form class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid import" id="importForm" action="/admin/codes/ten-mien" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Tải file mẫu --}}
                    <div class="form-group">
                        <label class="form-label">Tải file Excel mẫu</label>
                        <div>
                            <a href="{{ route('lead.import.sample') }}" class="btn btn-outline btn-sm">
                                ⬇️ Tải về file mẫu (.xlsx)
                            </a>
                        </div>
                        <p class="form-hint">File mẫu gồm các cột: <code>domain, tld, status, expired_at, note</code></p>
                    </div>

                    <hr class="divider">

                    {{-- Upload file --}}
                    <div class="form-group">
                        <label class="form-label">Nhập file Excel <span class="req">*</span></label>

                        <div class="file-upload-zone" id="dropZone">
                            <input type="file" name="excel_file" id="excelFile" accept=".xlsx,.xls,.csv">
                            <div class="file-upload-icon">📂</div>
                            <div class="file-upload-text">Kéo thả file vào đây hoặc nhấn để chọn</div>
                            <div class="file-upload-sub">Chấp nhận: .xlsx, .xls, .csv — Tối đa 5MB</div>
                            <div class="file-upload-selected" id="fileLabel"></div>
                        </div>

                        <p class="form-hint mt-2">
                            Nhập vào file excel mà bạn muốn import dữ liệu. Lưu ý, hệ thống chỉ nhận dữ liệu ở các cột có được khai báo trong file mẫu.
                        </p>

                        {{-- Nút submit ngay dưới vùng upload --}}
                        <div style="margin-top: 16px;">
                            <button type="submit" form="importForm" class="btn btn-primary" id="btnSubmitBottom" style="width:100%; justify-content:center; padding:12px; font-size:14px;" disabled>
                                ✔ Tiếp tục xem trước
                            </button>
                            <p class="form-hint" style="text-align:center; margin-top:6px;" id="hintChoose">Vui lòng chọn file Excel trước</p>
                        </div>

                    </div>

                </form>
            </div>
        </div>

        {{-- === HƯỚNG DẪN === --}}
        <div class="card">
            <div class="card-header">
                <h2>💡 Hướng dẫn</h2>
            </div>
            <div class="card-body">
                <ol style="font-size: 13px; line-height: 1.8; padding-left: 18px; color: var(--text-dark);">
                    <li>Tải file Excel mẫu về máy.</li>
                    <li>Điền danh sách tên miền vào file theo đúng định dạng.</li>
                    <li>Upload file đã điền.</li>
                    <li>Kiểm tra dữ liệu preview.</li>
                    <li>Nhấn <strong>"Tiếp tục lưu"</strong> để lưu vào hệ thống.</li>
                </ol>

                <hr class="divider">

                <p style="font-size: 13px; font-weight: 600; margin-bottom: 10px;">📄 Cấu trúc file mẫu</p>
                <div style="overflow-x: auto;">
                    <table style="font-size: 11.5px; min-width: 100%;">
                        <thead>
                        <tr>
                            <th style="background: var(--accent-light); color: var(--accent); padding: 7px 10px; border-radius: 4px 0 0 4px;">Cột</th>
                            <th style="background: var(--accent-light); color: var(--accent); padding: 7px 10px;">Bắt buộc</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ([
                            ['domain',     'Tên miền (không có TLD)', '✅'],
                            ['tld',        'Đuôi mở rộng (.com, .vn)', '❌'],
                            ['status',     'active / expired / pending', '❌'],
                            ['expired_at', 'Ngày hết hạn (YYYY-MM-DD)', '❌'],
                            ['note',       'Ghi chú thêm', '❌'],
                        ] as $col)
                            <tr>
                                <td style="padding: 7px 10px; border-bottom: 1px solid #f1f5f9;">
                                    <code style="background: #f1f5f9; padding: 1px 5px; border-radius: 4px; font-size: 11px;">{{ $col[0] }}</code>
                                    <div class="text-muted" style="font-size: 10.5px; margin-top: 2px;">{{ $col[1] }}</div>
                                </td>
                                <td style="padding: 7px 10px; border-bottom: 1px solid #f1f5f9; text-align: center;">{{ $col[2] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <hr class="divider">

                <div class="alert alert-warning" style="font-size: 12px; padding: 10px 14px;">
                    ⚠️ Tối đa <strong>500 bản ghi</strong> mỗi lần import. Nếu cần import nhiều hơn, hãy chia thành nhiều file.
                </div>
            </div>
        </div>

    </div>
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