<div class="log_logs">
	<!-- @if (\Auth::guard('admin')->user()->super_admin == 1)
    <?php
    $sales = \App\Models\Admin::select('name', 'tel')->whereIn('id', explode('|', $lead->saler_ids))->get();
?>
<span>
	<strong style="color: red;">Người sale:</strong>
	@foreach($sales as $sale)
		{{ $sale->name }} {{ $sale->tel }} |
	@endforeach
</span>
@endif -->


<?php
$logs = \App\CRMDV\Models\LeadContactedLog::where('lead_id', @$lead->id)->orderBy('id', 'desc')->limit(3)->get();
?>
@foreach($logs as $log)
    <div class="log-item" style="color: #000;">
        <i></i>
        <div class="log-content">
            <span><strong>{{ $log->title }}</strong></span>
            <p style="font-size: 13px; margin: 0;">{!! $log->note !!}</p>
        </div>
        <i style="font-size: 11px; color: #666;">{{ date('H:i d/m/Y', strtotime($log->created_at)) }}   - Bởi: {{ @$log->admin->name }}</i>
    </div>
@endforeach

    <div class="log-item" data-id="" style="color: #000;">
        <i></i>
        <div class="log-content">
            <p style="font-size: 13px; margin: 0;
    color: blue;">Tạo mới</p>
        </div>
        <i style="font-size: 11px;">{{ date('H:i d/m/Y', strtotime($lead->created_at)) }}   - Bởi: {{ @$lead->admin->name }}</i>
    </div>

    <div class="log-item" data-id="" style="color: #000;">
        <i></i>
        <div class="log-content">
            <p style="font-size: 13px; margin: 0;
    color: blue;">Profile</p>
        </div>
        <i style="font-size: 11px;">{!! $lead->profile !!}</i>
    </div>

    <div class="log-item" data-id="" style="color: #000;">
        <i></i>
        <div class="log-content">
            <p style="font-size: 13px; margin: 0;
    color: blue;">Nhu cầu</p>
        </div>
        <i style="font-size: 11px;">{!! $lead->need !!}</i>
    </div>
</div>
<style>
    .log_logs {
        font-family: Arial, sans-serif;
        font-size: 13px;
        color: #333;
    }

    .log-item {
        padding: 6px 0;              /* khoảng cách trên/dưới mỗi log */
        border-bottom: 1px solid #eee; /* gạch ngăn cách nhẹ */
    }

    .log-item:last-child {
        border-bottom: none; /* bỏ gạch dưới cùng */
    }

    .log-content strong {
        font-size: 13.5px;
        color: #222;
        display: block;
        margin-bottom: 2px;
    }

    .log-content p {
        margin: 0;
        font-size: 12.5px;
        line-height: 1.4;
        color: #444;
    }

    .log-item > i {
        display: block;
        margin-top: 2px;
        font-size: 11px;
        color: #777;
    }
</style>