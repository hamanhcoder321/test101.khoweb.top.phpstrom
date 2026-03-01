@extends(config('core.admin_theme').'.template')
@section('main')
<div class="kt-container kt-container--fluid kt-grid__item kt-grid__item--fluid import-nhanhoa">

    {{-- ===== ALERT MESSAGES ===== --}}
    @if(session('_one_time_message'))
        @php $otm = session('_one_time_message'); @endphp
        <div class="alert alert-{{ $otm['type'] == 'success' ? 'success' : 'danger' }} alert-dismissible fade show mb-3" role="alert">
            {!! $otm['message'] !!}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <strong><i class="la la-exclamation-circle"></i></strong> {{ $errors->first() }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- ===== STEP INDICATOR ===== --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="kt-portlet" style="margin-bottom:0;">
                <div class="kt-portlet__head kt-portlet__head--lg">
                    <div class="kt-portlet__head-label">
                        <span class="kt-portlet__head-icon"><i class="la la-upload" style="font-size:22px;"></i></span>
                        <h3 class="kt-portlet__head-title">
                            Import tên miền Nhanhoa
                            <small>{{ isset($step) && $step == 2 ? 'Bước 2: Xem trước dữ liệu' : 'Bước 1: Chọn file Excel' }}</small>
                        </h3>
                    </div>
                    <div class="kt-portlet__head-toolbar">
                        {{-- Step progress --}}
                        <div class="d-flex align-items-center" style="gap:8px;">
                            <span class="badge badge-{{ isset($step) && $step == 2 ? 'success' : 'primary' }} badge-pill px-3 py-2">
                                <i class="la la-file-excel-o"></i> 1. Upload
                            </span>
                            <i class="la la-angle-right text-muted"></i>
                            <span class="badge badge-{{ isset($step) && $step == 2 ? 'primary' : 'secondary' }} badge-pill px-3 py-2">
                                <i class="la la-eye"></i> 2. Xem trước
                            </span>
                            <i class="la la-angle-right text-muted"></i>
                            <span class="badge badge-secondary badge-pill px-3 py-2">
                                <i class="la la-check"></i> 3. Lưu HĐ
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!isset($step) || $step == 1)
    {{-- ============================================================ --}}
    {{-- BƯỚC 1: Form upload                                          --}}
    {{-- ============================================================ --}}
    <div class="row">
        <div class="col-xl-8 col-lg-8">
            <form id="form-upload" action="/admin/import/add_nhanhoa" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}
                <input type="hidden" name="action" value="upload">

                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <span class="kt-portlet__head-icon"><i class="la la-info-circle text-primary"></i></span>
                            <h3 class="kt-portlet__head-title">Thông tin Import</h3>
                        </div>
                        <div class="kt-portlet__head-toolbar">
                            <button type="submit" id="btn-next-header" class="btn btn-primary btn-sm" disabled>
                                <i class="la la-arrow-right"></i> Tiếp tục xem trước
                            </button>
                        </div>
                    </div>
                    <div class="kt-portlet__body">

                        <div class="alert alert-info mb-4" role="alert">
                            <i class="la la-info-circle"></i>
                            Tối đa <strong class="text-white">500 bản ghi</strong> trong 1 lần import.
                        </div>

                        {{-- Tải file mẫu --}}
                        <div class="form-group">
                            <label class="kt-font-bold">Tải file Excel mẫu</label>
                            <div>
                                <button type="button" onclick="downloadExcelDemo()" class="btn btn-outline-primary btn-sm">
                                    <i class="la la-file-excel-o"></i> Tải về file mẫu (.xlsx)
                                </button>
                            </div>
                            <span class="form-text text-muted mt-1">
                                File mẫu gồm các cột: <code>domain</code>, <code>tld</code>, <code>status</code>, <code>expired_at</code>, <code>note</code>
                            </span>
                        </div>

                        {{-- Dropzone --}}
                        <div class="form-group">
                            <label class="kt-font-bold">Nhập file Excel <span class="text-danger">*</span></label>

                            <div id="drop-zone" onclick="document.getElementById('file-input').click();"
                                 style="border:2px dashed #d1d5db; border-radius:8px; padding:50px 20px;
                                        text-align:center; cursor:pointer; transition:all .2s; background:#fafbfc;">
                                <div id="drop-content">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64' width='60' height='60'%3E%3Cpath fill='%23f5a623' d='M32 4L8 16v30l24 14 24-14V16z'/%3E%3Cpath fill='%23e08e0b' d='M32 4v44l24-14V16z'/%3E%3Cpath fill='%23fff' d='M22 28h20v3H22zm0 6h14v3H22z'/%3E%3C/svg%3E"
                                         alt="upload" style="margin-bottom:12px; opacity:.75;">
                                    <p style="font-size:15px; font-weight:600; color:#374151; margin:0;">
                                        Kéo thả file vào đây hoặc nhấn để chọn
                                    </p>
                                    <p style="font-size:12px; color:#9ca3af; margin-top:4px;">
                                        Chấp nhận: xlsx, xls, csv — Tối đa 5MB
                                    </p>
                                </div>
                                <div id="file-preview" style="display:none;">
                                    <i class="la la-file-excel-o" style="font-size:40px; color:#1d6f42;"></i>
                                    <p id="file-name-text" style="font-weight:600; margin:8px 0 4px; color:#111827;"></p>
                                    <p id="file-size-text" style="font-size:12px; color:#6b7280; margin:0;"></p>
                                    <button type="button" onclick="clearFile(event)" class="btn btn-outline-danger btn-sm mt-2">
                                        <i class="la la-times"></i> Xóa file
                                    </button>
                                </div>
                            </div>

                            <input type="file" id="file-input" name="file" accept=".xlsx,.xls,.csv" style="display:none;">
                            <span class="form-text text-muted" style="font-size:12px; margin-top:4px;">
                                Lưu ý: hệ thống chỉ nhận dữ liệu ở các cột đã được khai báo trong file mẫu.
                            </span>
                        </div>

                    </div>
                    <div class="kt-portlet__foot" style="text-align:center; padding:20px 30px 16px;">
                        <button type="submit" id="btn-submit" class="btn btn-primary btn-lg" style="width:100%; max-width:400px;" disabled>
                            <i class="la la-arrow-right"></i> Tiếp tục xem trước
                        </button>
                        <p id="hint-text" style="font-size:12px; color:#9ca3af; margin-top:8px;">Vui lòng chọn file Excel trước</p>
                    </div>
                </div>
            </form>
        </div>

        {{-- Cột phải: Hướng dẫn --}}
        <div class="col-xl-4 col-lg-4">
            <div class="kt-portlet">
                <div class="kt-portlet__head">
                    <div class="kt-portlet__head-label">
                        <span class="kt-portlet__head-icon"><i class="la la-lightbulb-o" style="color:#f6b93b;"></i></span>
                        <h3 class="kt-portlet__head-title">Hướng dẫn</h3>
                    </div>
                </div>
                <div class="kt-portlet__body">
                    <ol style="padding-left:18px; margin:0; line-height:2;">
                        <li>Tải file Excel mẫu về máy.</li>
                        <li>Điền danh sách tên miền vào file theo đúng định dạng.</li>
                        <li>Upload file đã điền.</li>
                        <li>Kiểm tra dữ liệu preview.</li>
                        <li>Nhấn <strong>"Tiếp tục lưu"</strong> để tạo hợp đồng.</li>
                    </ol>
                </div>
            </div>

            <div class="kt-portlet">
                <div class="kt-portlet__head">
                    <div class="kt-portlet__head-label">
                        <span class="kt-portlet__head-icon"><i class="la la-table" style="color:#f6b93b;"></i></span>
                        <h3 class="kt-portlet__head-title">Cấu trúc file mẫu</h3>
                    </div>
                </div>
                <div class="kt-portlet__body" style="padding-top:10px;">
                    <table class="table table-sm table-bordered" style="font-size:13px;">
                        <thead class="thead-light">
                            <tr><th>Cột</th><th style="text-align:center;">Bắt buộc</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><strong>domain</strong><br><small class="text-muted">Tên miền (không có TLD)</small></td><td style="text-align:center;"><span class="badge badge-success"><i class="la la-check"></i></span></td></tr>
                            <tr><td><strong>tld</strong><br><small class="text-muted">Đuôi (.com, .vn)</small></td><td style="text-align:center;"><span class="badge badge-danger"><i class="la la-times"></i></span></td></tr>
                            <tr><td><strong>status</strong><br><small class="text-muted">active / expired / pending</small></td><td style="text-align:center;"><span class="badge badge-danger"><i class="la la-times"></i></span></td></tr>
                            <tr><td><strong>expired_at</strong><br><small class="text-muted">Ngày hết hạn (YYYY-MM-DD)</small></td><td style="text-align:center;"><span class="badge badge-danger"><i class="la la-times"></i></span></td></tr>
                            <tr><td><strong>note</strong><br><small class="text-muted">Ghi chú</small></td><td style="text-align:center;"><span class="badge badge-danger"><i class="la la-times"></i></span></td></tr>
                        </tbody>
                    </table>

                    <div class="alert alert-warning mt-2 mb-0" style="font-size:12px;">
                        <i class="la la-exclamation-triangle"></i>
                        Mỗi tên miền được import sẽ <strong>tự động tạo 1 hợp đồng</strong> trong hệ thống (service_id = 3 - Tên miền).
                    </div>
                </div>
            </div>
        </div>
    </div>

    @elseif($step == 2)
    {{-- BƯỚC 2: Preview & Lưu --}}
    @php
        $column_keys = $column_keys ?? [];

        // Detect cột domain — VN: tn_min / Quốc tế: domain, domain_name...
        $domain_candidates = ['tn_min','tnmin','domain','domain_name','name','ten_mien','tenmien','ten_mien_quoc_te','hostname', 0, '0'];
        $detected_domain_col = null;
        foreach ($domain_candidates as $dc) {
            if (in_array($dc, $column_keys, true)) { $detected_domain_col = $dc; break; }
        }
        if ($detected_domain_col === null && !empty($column_keys)) $detected_domain_col = $column_keys[0];

        // Detect cột ngày đăng ký
        $reg_candidates = ['ngp_ng_k','ngy_ng_k','ng_ng_k','registered_at','registration_date','start_date','begin_date','ngay_dang_ky', 1, '1'];
        $detected_reg_col = null;
        foreach ($reg_candidates as $rc) {
            if (in_array($rc, $column_keys, true)) { $detected_reg_col = $rc; break; }
        }

        // Detect cột ngày hết hạn
        $expiry_candidates = ['ngp_ht_hn','ngy_ht_hn','ngp_h_hn','expired_at','expiry_date','expiry','expire','end_date','end_at','het_han','ngay_het_han', 2, '2'];
        $detected_expiry_col = null;
        foreach ($expiry_candidates as $ec) {
            if (in_array($ec, $column_keys, true)) { $detected_expiry_col = $ec; break; }
        }

        // Helper parse ngày (hỗ trợ dd/mm/yyyy Nhanhoa VN & quốc tế)
        $parseDatePreview = function($val) {
            if (empty($val)) return null;
            if ($val instanceof \Carbon\Carbon) return $val->format('Y-m-d');
            $str = trim((string)$val);
            if (empty($str)) return null;
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $str, $m)) {
                return date('Y-m-d', mktime(0, 0, 0, (int)$m[2], (int)$m[1], (int)$m[3]));
            }
            if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $str, $m)) {
                return date('Y-m-d', mktime(0, 0, 0, (int)$m[2], (int)$m[1], (int)$m[3]));
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) return $str;
            try { return \Carbon\Carbon::parse($str)->format('Y-m-d'); } catch(\Exception $e) {}
            return null;
        };
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="kt-portlet">
                <div class="kt-portlet__head">
                    <div class="kt-portlet__head-label">
                        <span class="kt-portlet__head-icon"><i class="la la-eye text-success"></i></span>
                        <h3 class="kt-portlet__head-title">
                            Xem trước dữ liệu
                            <small>{{ count($rows) }} bản ghi từ file Excel</small>
                        </h3>
                    </div>
                    <div class="kt-portlet__head-toolbar d-flex" style="gap:8px;">
                        <a href="/admin/import/add_nhanhoa" class="btn btn-secondary btn-sm">
                            <i class="la la-arrow-left"></i> Quay lại
                        </a>
                        <form method="POST" action="/admin/import/add_nhanhoa" style="display:inline-block;">
                            @csrf
                            <input type="hidden" name="action" value="save_bills">
                            <button type="submit" class="btn btn-success btn-sm"
                                    onclick="return confirm('Lưu {{ count($rows) }} tên miền vào hợp đồng (bills)?')">
                                <i class="la la-check"></i> Tiếp tục lưu ({{ count($rows) }} domains)
                            </button>
                        </form>
                    </div>
                </div>

                <div class="kt-portlet__body" style="padding:0;">

                    {{-- Thông tin cột detect được --}}
                    <div class="alert alert-info m-3 mb-2" style="font-size:13px;">
                        <i class="la la-columns"></i>
                        <strong>Cột đã nhận diện từ file Excel:</strong><br>
                        <span class="mr-3">
                            📌 <strong>Tên miền:</strong>
                            @if($detected_domain_col !== null)
                                <code>{{ $detected_domain_col }}</code> <span class="text-success">✓</span>
                            @else
                                <span class="text-danger">Không tìm thấy!</span>
                            @endif
                        </span>
                        <span class="mr-3">
                            📅 <strong>Ngày ĐK:</strong>
                            @if($detected_reg_col !== null)
                                <code>{{ $detected_reg_col }}</code> <span class="text-success">✓</span>
                            @else
                                <span class="text-warning">Không rõ (sẽ bỏ trống)</span>
                            @endif
                        </span>
                        <span>
                            📅 <strong>Ngày HH:</strong>
                            @if($detected_expiry_col !== null)
                                <code>{{ $detected_expiry_col }}</code> <span class="text-success">✓</span>
                            @else
                                <span class="text-warning">Không rõ (sẽ bỏ trống)</span>
                            @endif
                        </span>
                    </div>

                    <div class="alert alert-success m-3 mb-2">
                        <i class="la la-check-circle"></i>
                        Đọc file thành công! <strong>{{ count($rows) }}</strong> tên miền sẵn sàng tạo <strong>hợp đồng</strong> (bảng <code>bills</code>).
                    </div>

                    {{-- Preview table: chỉ hiện các cột quan trọng --}}
                    <div style="overflow-x:auto; max-height:500px; overflow-y:auto;">
                        <table class="table table-bordered table-hover table-sm" style="font-size:13px; margin:0;">
                            <thead class="thead-dark" style="position:sticky; top:0; z-index:2;">
                                <tr>
                                    <th style="width:45px;">#</th>
                                    <th>Tên miền</th>
                                    <th>TLD</th>
                                    <th>Ngày đăng ký</th>
                                    <th>Ngày hết hạn</th>
                                    <th>Thời hạn</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $i => $row)
                                @php
                                    $dv = $detected_domain_col !== null ? ($row[$detected_domain_col] ?? '') : '';
                                    $dv = str_replace(['http://','https://','www.'], '', strtolower(trim($dv)));
                                    $dv = rtrim($dv, '/');

                                    // TLD từ domain
                                    $tld_v = '';
                                    if (substr_count($dv, '.') >= 2) {
                                        $pp = explode('.', $dv);
                                        $tld_v = '.'.implode('.', array_slice($pp, -2));
                                    } elseif (strpos($dv, '.') !== false) {
                                        $pp = explode('.', $dv);
                                        $tld_v = '.'.end($pp);
                                    }

                                    $rv = $detected_reg_col !== null ? ($row[$detected_reg_col] ?? '') : '';
                                    $ev = $detected_expiry_col !== null ? ($row[$detected_expiry_col] ?? '') : '';

                                    // Tính số tháng — dùng parseDate để xử lý dd/mm/yyyy Nhanhoa
                                    $months_v = '';
                                    if (!empty($rv) && !empty($ev)) {
                                        $r_parsed = $parseDatePreview($rv);
                                        $e_parsed = $parseDatePreview($ev);
                                        if ($r_parsed && $e_parsed) {
                                            try {
                                                $diff = \Carbon\Carbon::parse($r_parsed)->diffInMonths(\Carbon\Carbon::parse($e_parsed));
                                                if ($diff > 0) $months_v = $diff . ' tháng';
                                            } catch(\Exception $e) {}
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td><strong>{{ $dv ?: '-' }}</strong></td>
                                    <td><code>{{ $tld_v ?: '-' }}</code></td>
                                    <td>{{ $rv ?: '-' }}</td>
                                    <td>{{ $ev ?: '-' }}</td>
                                    <td>{{ $months_v ?: '-' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="kt-portlet__foot" style="text-align:center; padding:16px 30px;">
                    <a href="/admin/import/add_nhanhoa" class="btn btn-secondary btn-lg mr-3">
                        <i class="la la-arrow-left"></i> Quay lại
                    </a>
                    <form method="POST" action="/admin/import/add_nhanhoa" style="display:inline-block;">
                        @csrf
                        <input type="hidden" name="action" value="save_bills">
                        <button type="submit" class="btn btn-success btn-lg"
                                onclick="return confirm('Xác nhận lưu {{ count($rows) }} tên miền vào hệ thống?')">
                            <i class="la la-save"></i> Lưu {{ count($rows) }} tên miền vào hệ thống
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif



</div>
@endsection

@section('custom_head')
<style>
    #drop-zone:hover, #drop-zone.drag-over {
        border-color: #5867dd !important;
        background: #f0f3ff !important;
    }
    .import-nhanhoa .kt-portlet__head-title small {
        display: block;
        font-size: 13px;
        font-weight: 400;
        color: #74788d;
    }
</style>
@endsection

@push('scripts')
@if(!isset($step) || $step == 1)
<script>
    const dropZone    = document.getElementById('drop-zone');
    const fileInput   = document.getElementById('file-input');
    const btnSubmit   = document.getElementById('btn-submit');
    const btnNextHdr  = document.getElementById('btn-next-header');
    const dropContent = document.getElementById('drop-content');
    const filePreview = document.getElementById('file-preview');
    const hintText    = document.getElementById('hint-text');

    ['dragenter','dragover'].forEach(evt =>
        dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.add('drag-over'); })
    );
    ['dragleave','drop'].forEach(evt =>
        dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.remove('drag-over'); })
    );
    dropZone.addEventListener('drop', e => {
        if (e.dataTransfer.files.length) setFile(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', function () {
        if (this.files.length) setFile(this.files[0]);
    });

    function setFile(file) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['xlsx','xls','csv'].includes(ext)) {
            alert('File không hợp lệ! Chỉ chấp nhận xlsx, xls, csv.'); return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('File không được vượt quá 5MB!'); return;
        }
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;

        document.getElementById('file-name-text').textContent = file.name;
        document.getElementById('file-size-text').textContent = (file.size / 1024).toFixed(1) + ' KB';
        dropContent.style.display  = 'none';
        filePreview.style.display  = 'block';
        btnSubmit.disabled  = false;
        btnNextHdr.disabled = false;
        hintText.textContent = '';
    }

    function clearFile(e) {
        e.stopPropagation();
        fileInput.value = '';
        dropContent.style.display = 'block';
        filePreview.style.display = 'none';
        btnSubmit.disabled  = true;
        btnNextHdr.disabled = true;
        hintText.textContent = 'Vui lòng chọn file Excel trước';
    }

    function downloadExcelDemo() {
        window.location.href = '/admin/import/download-excel-demo?module=nhanhoa';
    }
</script>
@endif
@endpush
