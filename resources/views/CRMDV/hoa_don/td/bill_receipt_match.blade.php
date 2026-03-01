@if(!empty($item->bill_receipt_id))
    <a href="/admin/receipt_payment/edit/{{ $item->bill_receipt_id }}"
       title="Số phiếu thu #{{ $item->bill_receipt_id }}"
       style="
           display:inline-block;
           padding:4px 12px;
           border-radius:999px;
           background:#00b894;
           color:#fff;
           font-size:9px;
           font-weight:600;
           text-decoration:none;
           white-space:nowrap;
           max-width:120px;
           overflow:hidden;
           text-overflow:ellipsis;
       ">
        {{ $item->bill_receipt_id }}
    </a>
@else
    <span
            style="
            display:inline-block;
            padding:4px 12px;
            border-radius:999px;
            background:#ff4d6d;
            color:#fff;
            font-size:9px;
            font-weight:600;
            white-space:nowrap;
        ">
        Chưa có
    </span>
@endif
