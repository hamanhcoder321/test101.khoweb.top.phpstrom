<div class="log_logs">


    <?php
    $logs = \App\Modules\HBBill\Models\LeadContactedLog::where('type', 'hđ')->where('lead_id', @$bill_id)->orderBy('id', 'desc')->get();
    ?>
    @foreach($logs as $log)
        <hr>
        <div class="log-item" data-id="{{ $log->id }}" style="color: #000;">
            <i></i>
            <div class="log-content">
                <span><strong>{{ $log->title }}</strong></span>
                <p style="font-size: 13px; margin: 0;">{!! $log->note !!}</p>
            </div>
            <i style="font-size: 11px;">{{ date('H:i d/m/Y', strtotime($log->created_at)) }}
                - Bởi: {{ @$log->admin->name }}</i>
        </div>
    @endforeach
{{--    <hr style=" margin: 0;">--}}
{{--    <div class="log-item" data-id="" style="color: #000;">--}}
{{--        <i></i>--}}
{{--        <div class="log-content">--}}
{{--            <p style="font-size: 13px; margin: 0;--}}
{{--    color: blue;">Tạo mới</p>--}}
{{--        </div>--}}
{{--        <i style="font-size: 11px;">{{ date('H:i d/m/Y', strtotime(@$bill->created_at)) }}   - Bởi: {{ @$bill->admin->name }}</i>--}}
{{--    </div>--}}
{{--    <hr style=" margin: 0;">--}}
{{--    <div class="log-item" data-id="" style="color: #000;">--}}
{{--        <i></i>--}}
{{--        <div class="log-content">--}}
{{--            <p style="font-size: 13px; margin: 0;--}}
{{--    color: blue;">Profile</p>--}}
{{--        </div>--}}
{{--        <i style="font-size: 11px;">{!! @$bill->profile !!}</i>--}}
{{--    </div>--}}
{{--    <hr style=" margin: 0;">--}}
{{--    <div class="log-item" data-id="" style="color: #000;">--}}
{{--        <i></i>--}}
{{--        <div class="log-content">--}}
{{--            <p style="font-size: 13px; margin: 0;--}}
{{--    color: blue;">Nhu cầu</p>--}}
{{--        </div>--}}
{{--        <i style="font-size: 11px;">{!! $bill->need !!}</i>--}}
{{--    </div>--}}
</div>