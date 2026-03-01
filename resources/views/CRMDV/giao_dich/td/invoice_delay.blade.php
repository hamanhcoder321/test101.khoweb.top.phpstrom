@php
    use Carbon\Carbon;

    // ===== MẶC ĐỊNH =====
    $bg   = '#e74c3c'; // đỏ
    $text = 'Chưa xuất HĐ';

    if (!empty($item->hoa_don_date)) {

        if ($item->invoice_delay_days <= 0) {
            // Không chậm
            $bg   = '#00b894'; // xanh
            $text = 'Không chậm';

        } else {
            $days = (int) $item->invoice_delay_days;

            if ($days > 3) {
                // 🔴 Chậm quá 3 ngày → đỏ hẳn
                $bg = '#e74c3c';
            } else {
                // 🟠 1–3 ngày → cam → đỏ nhạt
                $start = [243, 156, 18]; // cam
                $end   = [231, 76, 60];  // đỏ

                $ratio = $days / 3;

                $r = (int) ($start[0] + ($end[0] - $start[0]) * $ratio);
                $g = (int) ($start[1] + ($end[1] - $start[1]) * $ratio);
                $b = (int) ($start[2] + ($end[2] - $start[2]) * $ratio);

                $bg = "rgb($r, $g, $b)";
            }

            $text = 'Chậm ' . $days . ' ngày';
        }
    }
  $soHoaDon = $item->so_hoa_don ?? null;
    $editUrl  = $soHoaDon
        ? url('admin/hoa_don/edit/' . $item->hoa_don_id)
        : null;
@endphp

{{-- ===== SỐ HÓA ĐƠN / HỢP ĐỒNG (KHÔNG TRONG BADGE) ===== --}}
@if($soHoaDon && $editUrl)
    <div style="text-align:center; margin-bottom:4px;">
        <a href="{{ $editUrl }}"
           style="
               font-size:11px;
               font-weight:700;

               text-decoration:none;
           "
           title="Sửa hóa đơn">
            {{ $soHoaDon }}
        </a>
    </div>
@endif

{{-- ===== BADGE TRẠNG THÁI ===== --}}
<span style="
    display:inline-block;
    padding:4px 12px;
    border-radius:999px;
    background:{{ $bg }};
    color:#fff;
    font-size:9px;
    font-weight:600;
    white-space:nowrap;
    line-height:1.3;
    text-align:center;
">
    {{ $text }}
</span>
