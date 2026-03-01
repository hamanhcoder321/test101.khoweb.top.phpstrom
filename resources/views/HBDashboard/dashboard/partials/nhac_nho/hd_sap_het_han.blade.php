<?php
$next_month = strftime('%m', strtotime(strtotime(date('m')) . " +1 month"));
$last_month = strftime('%m', strtotime(strtotime(date('m')) . " -1 month"));

$cau_hinh = CommonHelper::getFromCache('settings_web_service', ['settings']);
if (!$cau_hinh) {
    $cau_hinh = \App\Models\Setting::whereIn('type', ['web_service'])->pluck('value', 'name')->toArray();
    CommonHelper::putToCache('settings_web_service', $cau_hinh, ['settings']);
}

$bill_warning = CommonHelper::getFromCache('bills_sap_het_han' . \Auth::guard('admin')->user()->id, ['bills']);
if (!$bill_warning) {
    // ====|Min|======|Now|=====|Closed|======|Max|======>
    $bill_warning = \App\Modules\HBDashboard\Models\Bill::select('service_id', 'customer_id', 'account_note', 'id', 'created_at', 'total_price', 'exp_price', 'expiry_date', 'domain', 'auto_extend', 'saler_id', 'staff_care', 'customer_note')
//            ->whereRaw($whereCompany)
        ->where('status', 1)->where('auto_extend', 1);   //  trạng thái đang kich hoạt & đang kich hoạt gia hạn


    $whereSaleId = '1 = 1';
    if(!in_array(CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name'), ['super_admin'])) {
        //  nếu ko phải super_admin thì chỉ xem được hợp đồng của mình
        $whereSaleId .= ' AND saler_id = ' . \Auth::guard('admin')->user()->id;
    }

    $bill_warning = $bill_warning->where(function ($query) use ($whereSaleId) {
        $query->orWhereRaw($whereSaleId);   //  tìm theo sale
        $query->orWhere('staff_care', 'like', '%|' . \Auth::guard('admin')->user()->id . '|%');   //  mình theo dõi hđ thì cũng được xem
    });

    $bill_warning = $bill_warning
//        ->whereNull('bill_parent')  //  là hđ gốc
        ->whereIn('service_id', [
            1,
            2,
            3,
            4,
            5,
            6,
            10, 11, 12, 13, 14, 15, 16, //  wp
            17, 18, 19, 20, 21,  // ldp
            23,
        ])
        ->where('expiry_date', '<>', Null)
        ->where('expiry_date', '>=', date('Y-m-d', strtotime('-' . $cau_hinh['min_day'] . ' day')))
        ->where('expiry_date', '<=', date('Y-m-d', strtotime('+' . $cau_hinh['max_day'] . ' day')))->get();
    CommonHelper::putToCache('bills_sap_het_han' . \Auth::guard('admin')->user()->id, $bill_warning, ['bills']);
}

?>

<div class="custom-card p-4 mb-4" style="box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-radius: 12px; background: #fff;">
    <div class="kt-portlet__head" style="border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem; margin-bottom: 25px;">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title bold uppercase" style="color: #1e293b; font-size: 1.25rem; font-weight: 600; margin: 0; display: flex; align-items: center;">
                <i class="fas fa-bell" style="color: #f39c12; margin-right: 10px; font-size: 22px;"></i>
                Nhắc nhở HĐ sắp hết hạn
            </h3>
        </div>
        <div class="kt-portlet__head-toolbar" style="margin-top: 12px;">
            <div class="kt-portlet__head-wrapper">
                <div class="kt-portlet__head-actions" style="display: flex; gap: 16px; flex-wrap: wrap;">
                    <div>
                        <span style="color:#7f8c8d">Tổng tiền gia hạn cần thu trong tháng này</span><br>
                        <?php
                        $thang_nay = \App\Modules\HBDashboard\Models\Bill::where('expiry_date', 'like', date('Y') . '-' . date('m') . '-%')->where('auto_extend', 1)->where('status', 1)->whereIn('service_id', [
                            1,2,3,4,5,6,10,11,12,13,14,15,16,17,18,19,20,21,23,
                        ]);
                        if(!in_array(CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name'), ['super_admin'])) {
                            $thang_nay = $thang_nay->where('saler_id', \Auth::guard('admin')->user()->id)->orWhere('staff_care', 'like', '%|' . \Auth::guard('admin')->user()->id . '|%');
                        }
                        $thang_nay = $thang_nay->sum('exp_price');
                        ?>
                        <span class="kt-widget12__value" style="font-weight:700; color:#2c3e50">{{number_format($thang_nay, 0, '.', '.')}}</span>
                    </div>
                    <div>
                        <span style="color:#7f8c8d">Tổng tiền gia hạn cần thu trong tháng sau</span><br>
                        <?php
                        $thang_sau = \App\Modules\HBDashboard\Models\Bill::select('exp_price')->where('expiry_date', 'like', date('Y') . '-' . $next_month . '-%')->where('auto_extend', 1)->where('status', 1)->whereIn('service_id', [
                            1,2,3,4,5,6,10,11,12,13,14,15,16,17,18,19,20,21,23,
                        ]);
                        if(!in_array(CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name'), ['super_admin'])) {
                            $thang_sau = $thang_sau->where('saler_id', \Auth::guard('admin')->user()->id)->orWhere('staff_care', 'like', '%|' . \Auth::guard('admin')->user()->id . '|%');
                        }
                        $thang_sau = $thang_sau->sum('exp_price');
                        ?>
                        <span class="kt-widget12__value" style="font-weight:700; color:#2c3e50">{{number_format($thang_sau, 0, '.', '.')}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="kt-portlet__body">
        <div class="kt-widget12">
            <div class="kt-widget12__content" style="padding-bottom:0 !important;">
                <div style="overflow-x: auto; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                    <table class="table table-striped" style="margin-bottom: 0; background: #fff; border-collapse: separate; border-spacing: 0; width:100%">
                        <thead class="kt-datatable__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <tr>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">Tên khách</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">Gói DV</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">Tên miền</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; text-align:right; position: sticky; top: 0; z-index: 10;">Tổng tiền</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">Ngày hết hạn</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; text-align:right; position: sticky; top: 0; z-index: 10;">Giá gia hạn</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">Sale</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">NV theo dõi</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;max-width: 200px;">Server</th>
                            @if(in_array(CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name'), ['super_admin']))
                                <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">Hủy gia hạn</th>
                            @endif
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">Chi tiết</th>
                        </tr>
                        </thead>
                        <tbody class="kt-datatable__body ps ps--active-y">
                        {{-- Hợp đồng đã hết hạn --}}
@if($bill_warning->count()>0)
        <?php $index = 0; ?>
    @foreach($bill_warning as $k=>$v)
        @if(strtotime($v->expiry_date) < time())
            <tr class="hover-row ajax-tooltip"
                data-id="{{ $v->id }}"
                data-modal="App\Modules\HBDashboard\Models\Bill"
                data-tooltip_info='["customer_note"]'
                style="border-bottom: 1px solid #e8ecef; transition: all 0.3s ease;"
                onmouseover="this.style.backgroundColor='#e3f2fd'; this.style.transform='translateX(4px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'"
                onmouseout="this.style.backgroundColor=''; this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                <td style="padding: 16px; vertical-align: middle; border: none;">
                    <a href="/admin/user/edit/{{ @$v->user->id }}" style="color: #000; text-decoration: none; font-weight: 600; font-size: 14px;">
                        {{@$v->user->name}}
                    </a>
                </td>
                <td style="padding: 16px; vertical-align: middle; border: none; font-size: 13px;">{{@$v->service->name_vi}}</td>
                <td style="padding: 16px; vertical-align: middle; border: none; font-weight: 600;">{{@$v->domain}}</td>
                <td style="padding: 16px; vertical-align: middle; text-align:right; border: none; font-weight: 700;">{{number_format(@$v->total_price, 0, '.', '.')}}</td>
                <td style="padding: 16px; vertical-align: middle; border: none; color: red;">{{date('d/m',strtotime(@$v->expiry_date))}}</td>
                <td style="padding: 16px; vertical-align: middle; text-align:right; border: none; font-weight: 700;">{{number_format(@$v->exp_price, 0, '.', '.')}}</td>
                <td style="padding: 16px; vertical-align: middle; border: none;">
                    {{ strtolower(mb_substr(explode(' ', $v->saler->name)[0], 0, 1)) . '.' . strtolower(mb_substr(explode(' ', $v->saler->name)[1], 0, 1)) . ' ' . last(explode(' ', $v->saler->name)) }}
                </td>
                <td style="padding: 16px; vertical-align: middle; border: none;">
                        <?php $nv_phu_trach = \App\Models\Admin::select(['id', 'name'])->whereIn('id', explode('|', $v->staff_care))->get(); ?>
                    @foreach($nv_phu_trach as $nv)
                        {{ strtolower(mb_substr(explode(' ', $nv->name)[0], 0, 1)) . '.' . strtolower(mb_substr(@explode(' ', $nv->name)[1], 0, 1)) . ' ' . last(explode(' ', $nv->name)) }}<br>
                    @endforeach
                </td>
                <td style="padding: 16px; vertical-align: middle; border: none;max-width: 200px; overflow: hidden; text-overflow: ellipsis">{{ @$v->account_note }}</td>
                @if(in_array(CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name'), ['super_admin']))
                    <td style="padding: 16px; vertical-align: middle; border: none;">
                        <span class="fa fa-times-circle fa-2x cancel-extension text-white" data-id="{{$v->id}}" style="cursor: pointer; background-color: red; color: #000 !important; border-radius: 50%; padding: 4px;"></span>
                    </td>
                @endif
                <td style="padding: 16px; vertical-align: middle; border: none;">
                    <a href="/admin/bill/edit/{{ $v->id }}" class="btn btn-sm btn-label-brand btn-bold text-white" onclick="openInNewTab(event)">Xem</a>
                </td>
            </tr>
        @endif
    @endforeach
@endif

{{-- Hợp đồng sát hạn --}}
@if($bill_warning->count()>0)
    @foreach($bill_warning as $k=>$v)
            <?php $day_check = (strtotime($v->expiry_date) - strtotime(date('Y-m-d'))) / (60 * 60 * 24); ?>
        @if(strtotime($v->expiry_date) >= time() && $day_check <= $cau_hinh['close_day'])
            <tr class="hover-row ajax-tooltip"
                data-id="{{ $v->id }}"
                data-modal="App\Modules\HBDashboard\Models\Bill"
                data-tooltip_info='["customer_note"]'
                style="border-bottom: 1px solid #e8ecef; transition: all 0.3s ease;"
                onmouseover="this.style.backgroundColor='#e3f2fd'; this.style.transform='translateX(4px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'"
                onmouseout="this.style.backgroundColor=''; this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                <td style="padding: 16px; vertical-align: middle; border: none;">
                    <a target="_blank" href="/admin/user/edit/{{$v->customer_id}}" style="color: #2c3e50; text-decoration: none; font-weight: 600; font-size: 14px;">{{@$v->user->name}}</a>
                </td>
                <td style="padding: 16px; vertical-align: middle; border: none;">{{@$v->service->name_vi}}</td>
                <td style="padding: 16px; vertical-align: middle; border: none; font-weight: 600;">{{@$v->domain}}</td>
                <td style="padding: 16px; vertical-align: middle; text-align:right; border: none; font-weight: 700;">{{number_format(@$v->total_price, 0, '.', '.')}}</td>
                <td style="padding: 16px; vertical-align: middle; border: none; color: #faad00;">{{date('d/m',strtotime(@$v->expiry_date))}}</td>
                <td style="padding: 16px; vertical-align: middle; text-align:right; border: none; font-weight: 700;">{{number_format(@$v->exp_price, 0, '.', '.')}}</td>
                <td style="padding: 16px; vertical-align: middle; border: none;">
                    {{ strtolower(mb_substr(explode(' ', $v->saler->name)[0], 0, 1)) . '.' . strtolower(mb_substr(explode(' ', $v->saler->name)[1], 0, 1)) . ' ' . last(explode(' ', $v->saler->name)) }}
                </td>
                <td style="padding: 16px; vertical-align: middle; border: none;">

                        <?php $nv_phu_trach = \App\Models\Admin::select(['id', 'name'])->whereIn('id', explode('|', $v->staff_care))->get(); ?>
                    @foreach($nv_phu_trach as $nv)
                        {{ strtolower(mb_substr(explode(' ', $nv->name)[0], 0, 1)) . '.' . strtolower(mb_substr(explode(' ', $nv->name)[1], 0, 1)) . ' ' . last(explode(' ', $nv->name)) }}<br>
                    @endforeach
                </td>
                <td style="padding: 16px; vertical-align: middle; border: none;max-width: 200px; overflow: hidden; text-overflow: ellipsis">{{ @$v->account_note }}</td>
                @if(in_array(CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name'), ['super_admin']))
                    <td style="padding: 16px; vertical-align: middle; border: none;">
                        <span class="fa fa-times-circle fa-2x cancel-extension text-white" data-id="{{$v->id}}" style="cursor: pointer; background-color: red; color: #000 !important; border-radius: 50%; padding: 4px;"></span>
                    </td>
                @endif
                <td style="padding: 16px; vertical-align: middle; border: none;">
                    <a href="/admin/bill/edit/{{ $v->id }}" target="_blank" class="btn btn-sm btn-label-brand btn-bold">Xem</a>
                </td>
            </tr>
        @endif
    @endforeach
@endif

{{-- Báo trước các HĐ tiếp theo hết hạn --}}
@if($bill_warning->count()>0)
        <?php $index = 0; ?>
    @foreach($bill_warning as $k=>$v)
            <?php $day_check = (strtotime($v->expiry_date) - strtotime(date('Y-m-d'))) / (60 * 60 * 24); ?>
        @if($day_check > $cau_hinh['close_day'])
                <?php $index++; ?>
            <tr class="hover-row ajax-tooltip"
                data-id="{{ $v->id }}"
                data-modal="App\Modules\HBDashboard\Models\Bill"
                data-tooltip_info='["customer_note"]'
                style="border-bottom: 1px solid #e8ecef; transition: all 0.3s ease;"
                onmouseover="this.style.backgroundColor='#e3f2fd'; this.style.transform='translateX(4px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'"
                onmouseout="this.style.backgroundColor=''; this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                <td style="padding: 16px; vertical-align: middle; border: none; font-weight: 600;">{{$k+1}}. {{@$v->user->name}}</td>
                <td style="padding: 16px; vertical-align: middle; border: none;">{{@$v->service->name_vi}}</td>
                <td style="padding: 16px; vertical-align: middle; border: none; font-weight: 600;">{{@$v->domain}}</td>
                <td style="padding: 16px; vertical-align: middle; text-align:right; border: none; font-weight: 700;">{{number_format(@$v->total_price, 0, '.', '.')}}</td>
                <td style="padding: 16px; vertical-align: middle; border: none; ">{{date('d/m',strtotime(@$v->expiry_date))}}</td>
                <td style="padding: 16px; vertical-align: middle; text-align:right; border: none; font-weight: 700;">{{number_format(@$v->exp_price, 0, '.', '.')}}</td>
                <td style="padding: 16px; vertical-align: middle; border: none;">
                    {{ strtolower(mb_substr(explode(' ', $v->saler->name)[0], 0, 1)) . '.' . strtolower(mb_substr(@explode(' ', $v->saler->name)[1], 0, 1)) . ' ' . last(explode(' ', $v->saler->name)) }}
                </td>
                <td style="padding: 16px; vertical-align: middle; border: none;">
                        <?php $nv_phu_trach = \App\Models\Admin::select(['id', 'name'])->whereIn('id', explode('|', $v->staff_care))->get(); ?>
                    @foreach($nv_phu_trach as $nv)
                        {{ strtolower(mb_substr(explode(' ', $nv->name)[0], 0, 1)) . '.' . strtolower(mb_substr(explode(' ', $nv->name)[1], 0, 1)) . ' ' . last(explode(' ', $nv->name)) }}<br>
                    @endforeach
                </td>
                <td style="padding: 16px; vertical-align: middle; border: none;max-width: 200px; overflow: hidden; text-overflow: ellipsis">{{ @$v->account_note }}</td>
                <td style="padding: 16px; vertical-align: middle; border: none;">
                    <span class="fa fa-times-circle fa-2x cancel-extension" data-id="{{$v->id}}" style="cursor: pointer; background-color: red; color: #000 !important; border-radius: 50%; padding: 4px;"></span>
                </td>
                <td style="padding: 16px; vertical-align: middle; border: none;">
                    <a href="/admin/bill/edit/{{ $v->id }}" class="btn btn-sm btn-label-brand btn-bold">Xem</a>
                </td>
            </tr>
            @endif
            @endforeach
            @endif
            </tbody>
            </table>
            </div>
            {{--                <div style="margin-top: 20px; padding: 15px; background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); border-radius: 8px; border-left: 4px solid #f39c12;">--}}
            {{--                    <i class="fas fa-info-circle" style="color: #d35400; margin-right: 8px;"></i>--}}
            {{--                    <span style="font-size: 12px; color: #d35400; font-weight: 500;">--}}
            {{--                        Truy vấn các HĐ: <strong>Đã hết hạn</strong>, <strong>Sát hạn</strong>, <strong>Sắp tới</strong>--}}
            {{--                    </span>--}}
            {{--                </div>--}}
            <div class="note-section mt-3">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Truy vấn theo các HĐ: Đã hết hạn, Sát hạn, Sắp tới
                </small>
            </div>
            </div>
            </div>
            </div>
            <div id="history-tooltip">
                <div class="tooltip-header"><i class="fas fa-history"></i> Lịch sử chăm sóc</div>
                <div class="tooltip-body" id="history-content"></div>
            </div>
            </div>

            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

            <style>
                @media (max-width: 768px) {
                    .custom-card { margin: 10px; padding: 15px !important; }
                    .kt-portlet__head-title { font-size: 18px !important; }
                    table { font-size: 12px; }
                    th, td { padding: 10px 8px !important; }
                }

                @keyframes shimmer {
                    0% { background-position: -200px 0; }
                    100% { background-position: calc(200px + 100%) 0; }
                }
                .loading { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200px 100%; animation: shimmer 1.5s infinite; }
                .table tbody tr:hover { transform: translateY(-2px); transition: all 0.3s ease; }
                .kt-widget12__content::-webkit-scrollbar { height: 8px; }
                .kt-widget12__content::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
                .kt-widget12__content::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; }
                .kt-widget12__content::-webkit-scrollbar-thumb:hover { background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%); }

                /* Style cho Popup lịch sử */
                #history-tooltip {
                    display: none;
                    position: absolute; /* ĐỔI TỪ FIXED SANG ABSOLUTE */
                    z-index: 99999; /* Tăng index lên cao nhất */
                    background: #fff;
                    border: 1px solid #e2e8f0;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                    border-radius: 8px;
                    width: 350px;
                    max-height: 400px;
                    overflow-y: auto;
                    padding: 0;
                    font-size: 13px;
                    pointer-events: none;
                    font-family: Arial, Helvetica, sans-serif;
                }
                #history-tooltip .tooltip-header {
                    background: #f8f9fa;
                    padding: 10px 15px;
                    border-bottom: 1px solid #eee;
                    font-weight: 700;
                    color: #2c3e50;
                }
                #history-tooltip .tooltip-body {
                    padding: 10px 15px;
                }
                #history-tooltip .note-item {
                    background: #f1f5f9;
                    padding: 8px 12px;
                    border-radius: 6px;
                    margin-bottom: 8px;
                    color: #334155;
                    border-left: 3px solid #667eea;
                    white-space: pre-wrap; /* Giữ định dạng xuống dòng của note */
                }
            </style>

            <script>
                function openInNewTab(event) {
                    event.preventDefault();
                    window.open(event.target.href, '_blank');
                }
                $(document).ready(function () {
                    $('#active_service a').click(function (event) {
                        event.preventDefault();
                        var object = $(this);
                        $.ajax({
                            url: '/admin/service_history/ajax-publish',
                            data: { id: object.data('service_history_id') },
                            success: function (result) {
                                if (result.status == true) {
                                    toastr.success(result.msg);
                                    object.parents('tr').remove();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                            error: function (e) { console.log(e.message); }
                        })
                    });
                    $('.cancel-extension ').click(function () {
                        var id = $(this).data('id');
                        $.ajax({
                            url: '{{route('dashboard.cancel_extension')}}',
                            data: { id: id },
                            success: function (result) {
                                if (result.status == true) {
                                    toastr.success(result.msg);
                                    location.reload();
                                } else { toastr.error(result.msg); }
                            },
                            error: function (e) { console.log(e.message); }
                        })
                    });
                })
                var tooltip = $('#history-tooltip');
                var tooltipContent = $('#history-content');

                // QUAN TRỌNG: Di chuyển popup ra ngoài body để tránh bị lỗi vị trí do div cha
                $('body').append(tooltip);

                // Xử lý hover sử dụng AJAX gọi đến đúng route của Bill
                $('.hover-row.ajax-tooltip').on('mouseenter', function(e) {
                    var id = $(this).data('id');
                    var modal = $(this).data('modal');
                    var tooltip_info = $(this).data('tooltip_info');

                    // Hiển thị loading
                    tooltipContent.html('<div style="text-align:center; padding:15px;"><img src="/images_core/icons/loading.gif" style="width:25px;"> Đang tải dữ liệu...</div>');
                    tooltip.show();

                    $.ajax({
                        url: '/admin/bill/tooltip-info', // Đã sửa thành route của Bill
                        type: 'GET',
                        data: {
                            id: id,
                            modal: modal,
                            tooltip_info: tooltip_info
                        },
                        success: function (result) {
                            tooltipContent.html(result);
                        },
                        error: function (xhr) {
                            console.log(xhr.responseText);
                            tooltipContent.html('<div style="padding:10px; color:red;">Không thể tải dữ liệu. Lỗi ' + xhr.status + '</div>');
                        }
                    });
                });

                $('.hover-row').on('mouseleave', function() {
                    tooltip.hide();
                });

                $('.hover-row').on('mousemove', function(e) {
                    // Dùng pageX/pageY để tính theo toạ độ lăn chuột của cả trang web
                    var top = e.pageY + 15;
                    var left = e.pageX + 15;

                    // Xử lý chống tràn dưới màn hình
                    if (top + tooltip.outerHeight() > $(document).height()) {
                        top = e.pageY - tooltip.outerHeight() - 10;
                    }

                    // Xử lý chống tràn bên phải màn hình
                    if (left + tooltip.outerWidth() > $(document).width()) {
                        left = e.pageX - tooltip.outerWidth() - 10;
                    }

                    tooltip.css({
                        top: top + 'px',
                        left: left + 'px'
                    });
                });
            </script>