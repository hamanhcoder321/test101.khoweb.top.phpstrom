@php
    use Carbon\Carbon;


    $bg   = '#e74c3c'; // 🔴
    $text = 'Chưa xuất HĐ';


    $soHoaDon = $item->so_hoa_don ?? null;
    $editUrl  = $soHoaDon && !empty($item->hoa_don_id)
        ? url('admin/hoa_don/edit/' . $item->hoa_don_id)
        : null;

    if (!empty($item->ngay_ky) && !empty($item->bill_receipt_date)) {

        $invDate = Carbon::parse($item->ngay_ky)->startOfDay();
        $brDate  = Carbon::parse($item->bill_receipt_date)->startOfDay();


        $days = $invDate->diffInDays($brDate, false);

        if ($days <= 0) {

            $bg   = '#00b894';
            $text = 'Đúng ngày';

        } else {
      
            if ($days > 3) {
                $bg = '#e74c3c';
            } else {
                $bg = '#f39c12';
            }

            $text = 'Chậm ' . $days . ' ngày';
        }
    }
@endphp

{{-- ===== SỐ HÓA ĐƠN ===== --}}
@if($soHoaDon && $editUrl)
    <div style="text-align:center; margin-bottom:4px;">
        <a href="{{ $editUrl }}"
           style="font-size:11px;font-weight:700;text-decoration:none;"
           title="Sửa hóa đơn">
            {{ $soHoaDon }}
        </a>
    </div>
@endif

{{-- ===== BADGE XUẤT HÓA ĐƠN ===== --}}
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
