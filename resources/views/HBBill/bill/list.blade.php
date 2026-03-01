    @extends(config('core.admin_theme').'.template')
@section('main')
    <?php
    if (@$_GET['saler_ids'] != null) {
        $whereSaler = 'saler_id = ' . $_GET['saler_ids'];
        $whereSale = 'admin_id = ' . $_GET['saler_ids'];
        $whereLikeSale = "saler_ids LIKE '%|" . $_GET['saler_ids'] . "|%'";
    } else {
        if (\Auth::guard('admin')->user()->super_admin == 1) {
            $whereSale = $whereSaler = $whereLikeSale = '1=1';
        } else {
            $whereSaler = 'saler_id = ' . \Auth::guard('admin')->user()->id;
            $whereSale = 'admin_id = ' . \Auth::guard('admin')->user()->id;
            $whereLikeSale = "saler_ids LIKE '%|" . \Auth::guard('admin')->user()->id . "|%'";
        }
    }

    $whereCreated_at = $whereRegistrationAt = '1=1';
    if (isset($_GET['contacted_log_last']) && $_GET['contacted_log_last'] != '') {
        if (isset($_GET['from_date']) && $_GET['from_date'] != '') {
            $whereCreated_at .= " AND created_at >= '" . date('Y-m-d 00:00:00', strtotime($_GET['from_date'])) . "'";
            $whereRegistrationAt .= " AND registration_date >= '" . date('Y-m-d 00:00:00', strtotime($_GET['from_date'])) . "'";
        }
        if (isset($_GET['to_date']) && $_GET['to_date'] != '') {
            $whereCreated_at .= " AND created_at <= '" . date('Y-m-d 23:59:59', strtotime($_GET['to_date'])) . "'";
            $whereRegistrationAt .= " AND registration_date <= '" . date('Y-m-d 23:59:59', strtotime($_GET['to_date'])) . "'";
        }
    }
    if ($whereCreated_at == '1=1' && $whereRegistrationAt == '1=1') {
        $whereCreated_at .= " AND created_at >= '" . date('Y-m-d 00:00:00') . "'";
        $whereRegistrationAt .= " AND registration_date >= '" . date('Y-m-d 00:00:00') . "'";
    }
// dd($whereCreated_at);

    $day = isset($_GET['day']) ? $_GET['day'] : date('Y-m-d');
    ?>
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <?php
        $count_leads_created = \App\Modules\HBBill\Models\Lead::whereRaw($whereSale)
            ->whereRaw($whereCreated_at)
            ->count();

        $coun_lead_contacted_logs = \App\Modules\HBBill\Models\LeadContactedLog::whereRaw($whereSale)
            ->whereRaw($whereCreated_at)
            ->whereNotIn('note', ['Không nghe', 'Sai số'])->count();

        $count_khqt = \App\Modules\HBBill\Models\Lead::whereRaw($whereLikeSale)
            ->whereIn('rate', ['Đang tìm hiểu', 'Care dài'])->whereNotIn('status', ['Thả nổi', 'Đã ký HĐ'])
            // ->where('created_at', '>=', date('Y-'.date('m', strtotime($day)).'-01 00:00:00'))
            // ->where('created_at', '<=', date('Y-'.date('m', strtotime($day)).'-t 23:59:59', strtotime($day)))
            ->count();

        $quanTamMoi = \App\Modules\HBBill\Models\LeadContactedLog::whereRaw($whereSale)
            ->where('created_at', '>', date('Y-m-d 00:00:00'))
            ->where('type', 'lead_quan_tam_lai')->count();

        $count_khqt_cao = \App\Modules\HBBill\Models\Lead::whereRaw($whereLikeSale)
            ->whereIn('rate', ['Quan tâm cao'])->whereNotIn('status', ['Thả nổi', 'Đã ký HĐ'])
            // ->where('created_at', '>=', date('Y-'.date('m', strtotime($day)).'-01 00:00:00'))
            // ->where('created_at', '<=', date('Y-'.date('m', strtotime($day)).'-t 23:59:59', strtotime($day)))
            ->count();
        $count_co_hoi = \App\Modules\HBBill\Models\Lead::whereRaw($whereLikeSale)
            ->whereIn('rate', ['Cơ hội'])->whereNotIn('status', ['Thả nổi', 'Đã ký HĐ'])
            ->where('created_at', '>=', date('Y-' . date('m', strtotime($day)) . '-01 00:00:00'))
            ->where('created_at', '<=', date('Y-' . date('m', strtotime($day)) . '-t 23:59:59', strtotime($day)))
            ->count();



        $plan = \App\Modules\HBBill\Models\Plan::whereRaw($whereSale)->orderBy('id', 'desc')->first();
        $ds_tuan = \App\Modules\HBBill\Models\Bill::whereRaw($whereSaler)
            ->where('registration_date', '>=', date('Y-m-d 00:00:00', strtotime('-' . date('w') . ' days')))
            ->sum('total_price');
        $ds_thang = \App\Modules\HBBill\Models\Bill::whereRaw($whereSaler)
            ->where('registration_date', '>=', date('Y-m-01 00:00:00', time()))
            ->sum('total_price');
        $hd = \App\Modules\HBBill\Models\Bill::whereRaw($whereSaler)
            ->where('registration_date', '>=', date('Y-m-d 00:00:00', strtotime('-' . date('w') . ' days')))
            ->count();


        ?>

        <div class="kt-portlet kt-portlet--mobile">
            <div class="kt-portlet__head kt-portlet__head--lg">
                <div class="kt-portlet__head-label">
                 <span class="kt-portlet__head-icon">
                    <i class="kt-font-brand flaticon-calendar-with-a-clock-time-tools"></i>
                </span>
                    <h3 class="kt-portlet__head-title">
                        {{ $module['label'] }}
                    </h3>
                </div>
                <div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-wrapper">
                        <div class="dropdown show-columns-dropdown mr-1">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                <span class="kt-menu__link-icon"><!--begin::Svg Icon | path:/var/www/preview.keenthemes.com/metronic/releases/2021-05-14-112058/theme/html/demo1/dist/../src/media/svg/icons/General/Settings-2.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <rect x="0" y="0" width="24" height="24"></rect>
                                        <path d="M5,8.6862915 L5,5 L8.6862915,5 L11.5857864,2.10050506 L14.4852814,5 L19,5 L19,9.51471863 L21.4852814,12 L19,14.4852814 L19,19 L14.4852814,19 L11.5857864,21.8994949 L8.6862915,19 L5,19 L5,15.3137085 L1.6862915,12 L5,8.6862915 Z M12,15 C13.6568542,15 15,13.6568542 15,12 C15,10.3431458 13.6568542,9 12,9 C10.3431458,9 9,10.3431458 9,12 C9,13.6568542 10.3431458,15 12,15 Z" fill="#000000"></path>
                                    </g>
                                </svg><!--end::Svg Icon--></span>
                            </button>
                            <div class="dropdown-menu">
                                <label class="dropdown-item">
                                    <strong>Chọn cột hiển thị</strong>
                                </label>
                                <hr class="dropdown-divider">
                                <label class="dropdown-item cursor-pointer">
                                    <input type="checkbox" class="mr-2" id="toggle-all-columns" checked>
                                    <strong>Chọn tất cả</strong>
                                </label>
                                <hr class="dropdown-divider">
                                @foreach($module['list'] as $field)
                                    <label class="dropdown-item cursor-pointer">
                                        <input type="checkbox" class="toggle-column mr-2" data-column="{{ $field['name'] }}" checked>
                                        {{ $extendFields[$field['name']] ?? $field['label'] }}
                                    </label>
                                @endforeach
{{--                                <label class="dropdown-item cursor-pointer">--}}
{{--                                    <input type="checkbox" class="toggle-column mr-2" data-column="sales" checked>--}}
{{--                                    Sales--}}
{{--                                </label>--}}
                            </div>
                        </div>
{{--                        @if(in_array('lead_float_view', $permissions))--}}
{{--                            <div>--}}
{{--                                <a href="/admin/lead?limit=20&lead_status=%C4%90%E1%BA%BFn+ng%C3%A0y+TT"--}}
{{--                                   class="btn {{ @$_GET['lead_status'] == 'Đến ngày TT' ? 'btn-primary' : 'btn-default' }}">Đến ngày TT</a>--}}
{{--                                <a href="/admin/lead"--}}
{{--                                   class="btn {{ strpos($_SERVER['REQUEST_URI'], '/tha-noi') == false && strpos($_SERVER['REQUEST_URI'], '/quan-tam-moi') == false ? 'btn-primary' : 'btn-default' }}">Khách--}}
{{--                                    của tôi</a>--}}
{{--                                <a href="/admin/lead/tha-noi"--}}
{{--                                   class="btn {{ strpos($_SERVER['REQUEST_URI'], '/tha-noi') != false ? 'btn-primary' : 'btn-default' }}">Khách--}}
{{--                                    thả nổi</a>--}}

{{--                            </div>--}}
{{--                        @endif--}}
                        <div class="">
                            <input type="text" name="quick_search" value="{{ @$_GET['quick_search'] }}"
                                   class="form-control" title="Chỉ cần enter để thực hiện tìm kiếm"
                                   placeholder="Tìm kiếm nhanh">
                        </div>
                        <div class="kt-portlet__head-actions">
                            <button type="button" class="btn btn-default btn-icon-sm dropdown-toggle btn-closed-search"
                                    onclick="$('.form-search').slideToggle(100); $('.kt-portlet-search').toggleClass('no-padding');">
                                <i class="la la-search"></i> Tìm kiếm
                            </button>
                        </div>

                        <div class="dropdown dropdown-inline">
                            <button type="button" class="btn btn-default btn-icon-sm dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="la la-download"></i> Hành động
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end"
                                 style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(114px, 38px, 0px);">
                                <ul class="kt-nav">
                                    <li class="kt-nav__section kt-nav__section--first">
                                        <span class="kt-nav__section-text">Chọn hành động</span>
                                    </li>
                                    @if (\Auth::guard('admin')->user()->super_admin == 1)
                                        <li class="kt-nav__item">
                                            <a class="kt-nav__link export-excel"
                                               title="Xuất các bản ghi đang lọc ra file excel"
                                               onclick="$('input[name=export]').click();">
                                                <i class="kt-nav__link-icon la la-file-excel-o"></i>
                                                <span class="kt-nav__link-text">Xuất Excel</span>
                                            </a>
                                        </li>
                                    @endif


                                    <li class="kt-nav__item">
                                        <a href="/admin/import/add?table=leads&table_label=Đầu mối" class="kt-nav__link"
                                           title="Nhập file excel lên để đẩy dữ liệu vào hệ thống">
                                            <i class="kt-nav__link-icon la la-copy"></i>
                                            <span class="kt-nav__link-text">Import excel</span>
                                        </a>
                                    </li>

                                    @if(in_array($module['code'] . '_delete', $permissions))
                                        <li class="kt-nav__item">
                                            <a href="#" class="kt-nav__link" onclick="multiDelete();"
                                               title="Xóa tất cả các dòng đang được tích chọn">
                                                <i class="kt-nav__link-icon la la-copy"></i>
                                                <span class="kt-nav__link-text">Xóa nhiều</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if(in_array($module['code'] . '_assign', $permissions))
                                        <li class="kt-nav__item">
                                            <a href="#" class="kt-nav__link" onclick="leadAssign();"
                                               title="Chuyển đầu mối sang cho nv khác">
                                                <i class="kt-nav__link-icon la la-copy"></i>
                                                <span class="kt-nav__link-text">Chuyển lead</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        @if(in_array($module['code'] . '_add', $permissions))
                            <a href="{{ url('/admin/'.$module['code'].'/add/') }}"
                               class="btn btn-brand btn-elevate btn-icon-sm">
                                <i class="la la-plus"></i>
                                Tạo mới
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="kt-portlet__body kt-portlet-search @if(!isset($_GET['search'])) no-padding @endif">
                <!--begin: Search Form -->
                <form class="kt-form kt-form--fit kt-margin-b-20 form-search" id="form-search" method="GET" action=""
                      @if(!isset($_GET['search'])) style="display: none;" @endif>
                    <input name="search" type="hidden" value="true">
                    <input name="limit" type="hidden" value="{{ $limit }}">

                    <input type="hidden" name="quick_search"
                           value="{{ @$_GET['quick_search'] }}"
                           id="quick_search_hidden"
                           class="form-control"
                           placeholder="Tìm kiếm nhanh">

                    <div class="row">

                        @foreach($filter as $filter_name => $field)
                            <div class="col-sm-6 col-lg-3 kt-margin-b-10-tablet-and-mobile list-filter-item">
                                @if($field['type'] == 'custom')
                                    @include($field['field'], ['name' => $filter_name, 'field' => $field])
                                @else
                                    <label>{{ @$field['label'] }}:</label>
                                    @include(config('core.admin_theme').'.list.filter.' . $field['type'], ['name' => $filter_name, 'field'  => $field])
                                @endif

                            </div>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <button class="btn btn-primary btn-brand--icon" id="kt_search" type="submit">
              <span>
                 <i class="la la-search"></i>
                 <span>Lọc</span>
             </span>
                            </button>
                            &nbsp;&nbsp;
                            <a class="btn btn-secondary btn-secondary--icon" id="kt_reset" title="Xóa bỏ bộ lọc"
                               href="/admin/{{ $module['code'] }}">
         <span>
             <i class="la la-close"></i>
             <span>Reset</span>
         </span>
                            </a>
                        </div>
                    </div>
                    <input name="export" type="submit" value="export" style="display: none;">
                    @foreach($module['list'] as $k => $field)
                        <input name="sorts[]" value="{{ @$_GET['sorts'][$k] }}"
                               class="sort sort-{{ $field['name'] }}" type="hidden">
                    @endforeach
                </form>
                <!--end: Search Form -->
            </div>
            <div class="kt-separator kt-separator--md kt-separator--dashed" style="margin: 0;"></div>
            <div class="kt-portlet__body kt-portlet__body--fit">
                <!--begin: Datatable -->
                <div class="kt-datatable kt-datatable--default kt-datatable--brand kt-datatable--scroll kt-datatable--loaded"
                     id="scrolling_vertical" style="">
                    <table class="table table-striped">
                        <thead class="kt-datatable__head">
                        <tr class="kt-datatable__row" style="left: 0px;">
                            <th style="display: none;"></th>
                            <th data-field="id"
                                class="kt-datatable__cell--center kt-datatable__cell kt-datatable__cell--check"><span
                                        style="width: 20px;"><label
                                            class="kt-checkbox kt-checkbox--single kt-checkbox--all kt-checkbox--solid"><input
                                                type="checkbox"
                                                class="checkbox-master">&nbsp;<span></span></label></span></th>

                            @php $count_sort = 0; @endphp
                            @foreach($module['list'] as $field)
                                <th data-field="{{ $field['name'] }}" data-column="{{ $field['name'] }}"
                                    class="kt-datatable__cell dieptv-thead-th kt-datatable__cell--sort {{ @$_GET['sorts'][$count_sort] != '' ? 'kt-datatable__cell--sorted' : '' }}"
                                    @if(isset($field['sort']))
                                        onclick="sort('{{ $field['name'] }}')"
                                        @endif
                                >
                                    {{ $extendFields[$field['name']] ?? $field['label'] }}
                                    @if(isset($field['sort']))
                                        @if(@$_GET['sorts'][$count_sort] == $field['name'].'|asc')
                                            <i class="flaticon2-arrow-up"></i>
                                        @else
                                            <i class="flaticon2-arrow-down"></i>
                                        @endif
                                    @endif

                                </th>
                                @php $count_sort++; @endphp
                            @endforeach

                            <th>Lỗi</th>
                        </tr>
                        </thead>
                        <tbody class="kt-datatable__body ps ps--active-y" style="max-height: 496px;">

                        @foreach($listItem as $item)

                            <tr data-row="0" class="kt-datatable__row" style="left: 0px;">
                                <td style="display: none;"
                                    class="id id-{{ $item->id }}">{{ $item->id }}</td>
                                <td class="kt-datatable__cell--center kt-datatable__cell kt-datatable__cell--check"
                                    data-field="ID"><span style="width: 20px;"><label
                                                class="kt-checkbox kt-checkbox--single kt-checkbox--solid"><input
                                                    name="id[]"
                                                    type="checkbox" class="ids"
                                                    value="{{ $item->id }}">&nbsp;<span></span></label></span>
                                    <div style="width: 500px;    left: 356px;" id="tooltip-info-{{$field['name']}}"
                                         class="dropdown-menu div-tooltip_info"
                                         data-modal="{{ $module['modal'] }}"
                                         data-tooltip_info="{{ json_encode(@$field['tooltip_info']) }}"><img class="tooltip_info_loading" src="/images_core/icons/loading.gif"></div>
                                </td>

                                @foreach($module['list'] as $field)
                                    <td data-field="{{ @$field['name'] }}" data-column="{{ $field['name'] }}"
                                        class="kt-datatable__cell item-{{ @$field['name'] }}">
                                        @if($field['type'] == 'custom')
                                            @include($field['td'], ['field' => $field])
                                        @else
                                            @if(in_array($field['name'], ['expiry_date', 'auto_extend']) && in_array($item->service_id, [7, 8, 9, 22, 24, 25, 26, 30]))
{{--                                                Nếu là các dịch vụ duy trì thì ko hiển thị nội dung ngày hết hạn--}}
                                                -
                                            @else
                                                @include(config('core.admin_theme').'.list.td.'.$field['type'])
                                            @endif
                                        @endif
                                    </td>
                                @endforeach

                                <td> {{--Hiển thị thông tin lỗi chưa điền link hợp đồng hoặc chưa xuất hoá đơn--}}

                                        <?php
                                        $bill_receipts = \App\Modules\HBBill\Models\BillReceipts::where('bill_id', $item->id)->whereIn('receiving_account', [61, 68])->get();
                                        $chua_dien_so_hoa_don = false;
                                        foreach ($bill_receipts as $bill_receipt) {
                                            if ($bill_receipt->so_hoa_don == null) {
                                                $chua_dien_so_hoa_don = true;
                                            }
                                        }
                                        ?>
                                    @if((count($bill_receipts) > 0 && $item->link_hd == null) || (count($bill_receipts) > 0 && $item->hd_luu_tru == null))
                                        <span title="Chưa điền link HĐ">
                                            <span class="svg-icon svg-icon-primary svg-icon-2x"><!--begin::Svg Icon | path:/var/www/preview.keenthemes.com/metronic/releases/2021-05-14-112058/theme/html/demo1/dist/../src/media/svg/icons/Communication/Clipboard-check.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
    <title>Stockholm-icons / Communication / Clipboard-check</title>
    <desc>Created with Sketch.</desc>
    <defs/>
    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <rect x="0" y="0" width="24" height="24"/>
        <path d="M8,3 L8,3.5 C8,4.32842712 8.67157288,5 9.5,5 L14.5,5 C15.3284271,5 16,4.32842712 16,3.5 L16,3 L18,3 C19.1045695,3 20,3.8954305 20,5 L20,21 C20,22.1045695 19.1045695,23 18,23 L6,23 C4.8954305,23 4,22.1045695 4,21 L4,5 C4,3.8954305 4.8954305,3 6,3 L8,3 Z" fill="#000000" opacity="0.3"/>
        <path d="M10.875,15.75 C10.6354167,15.75 10.3958333,15.6541667 10.2041667,15.4625 L8.2875,13.5458333 C7.90416667,13.1625 7.90416667,12.5875 8.2875,12.2041667 C8.67083333,11.8208333 9.29375,11.8208333 9.62916667,12.2041667 L10.875,13.45 L14.0375,10.2875 C14.4208333,9.90416667 14.9958333,9.90416667 15.3791667,10.2875 C15.7625,10.6708333 15.7625,11.2458333 15.3791667,11.6291667 L11.5458333,15.4625 C11.3541667,15.6541667 11.1145833,15.75 10.875,15.75 Z" fill="#000000"/>
        <path d="M11,2 C11,1.44771525 11.4477153,1 12,1 C12.5522847,1 13,1.44771525 13,2 L14.5,2 C14.7761424,2 15,2.22385763 15,2.5 L15,3.5 C15,3.77614237 14.7761424,4 14.5,4 L9.5,4 C9.22385763,4 9,3.77614237 9,3.5 L9,2.5 C9,2.22385763 9.22385763,2 9.5,2 L11,2 Z" fill="#000000"/>
    </g>
</svg><!--end::Svg Icon--></span>
                                        </span>
                                    @endif
                                    @if($chua_dien_so_hoa_don)
                                        <span title="Chưa điền số hoá đơn">
                                            <span class="svg-icon svg-icon-primary svg-icon-2x"><!--begin::Svg Icon | path:/var/www/preview.keenthemes.com/metronic/releases/2021-05-14-112058/theme/html/demo1/dist/../src/media/svg/icons/Files/Selected-file.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
    <title>Stockholm-icons / Files / Selected-file</title>
    <desc>Created with Sketch.</desc>
    <defs/>
    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <polygon points="0 0 24 0 24 24 0 24"/>
        <path d="M4.85714286,1 L11.7364114,1 C12.0910962,1 12.4343066,1.12568431 12.7051108,1.35473959 L17.4686994,5.3839416 C17.8056532,5.66894833 18,6.08787823 18,6.52920201 L18,19.0833333 C18,20.8738751 17.9795521,21 16.1428571,21 L4.85714286,21 C3.02044787,21 3,20.8738751 3,19.0833333 L3,2.91666667 C3,1.12612489 3.02044787,1 4.85714286,1 Z M8,12 C7.44771525,12 7,12.4477153 7,13 C7,13.5522847 7.44771525,14 8,14 L15,14 C15.5522847,14 16,13.5522847 16,13 C16,12.4477153 15.5522847,12 15,12 L8,12 Z M8,16 C7.44771525,16 7,16.4477153 7,17 C7,17.5522847 7.44771525,18 8,18 L11,18 C11.5522847,18 12,17.5522847 12,17 C12,16.4477153 11.5522847,16 11,16 L8,16 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
        <path d="M6.85714286,3 L14.7364114,3 C15.0910962,3 15.4343066,3.12568431 15.7051108,3.35473959 L20.4686994,7.3839416 C20.8056532,7.66894833 21,8.08787823 21,8.52920201 L21,21.0833333 C21,22.8738751 20.9795521,23 19.1428571,23 L6.85714286,23 C5.02044787,23 5,22.8738751 5,21.0833333 L5,4.91666667 C5,3.12612489 5.02044787,3 6.85714286,3 Z M8,12 C7.44771525,12 7,12.4477153 7,13 C7,13.5522847 7.44771525,14 8,14 L15,14 C15.5522847,14 16,13.5522847 16,13 C16,12.4477153 15.5522847,12 15,12 L8,12 Z M8,16 C7.44771525,16 7,16.4477153 7,17 C7,17.5522847 7.44771525,18 8,18 L11,18 C11.5522847,18 12,17.5522847 12,17 C12,16.4477153 11.5522847,16 11,16 L8,16 Z" fill="#000000" fill-rule="nonzero"/>
    </g>
</svg><!--end::Svg Icon--></span>
                                        </span>
                                    @endif

                                </td>

                            </tr>
                        @endforeach
                        <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                            <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                        </div>
                        <div class="ps__rail-y" style="top: 0px; height: 496px; right: 0px;">
                            <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 207px;"></div>
                        </div>
                        </tbody>
                    </table>
                    <div class="kt-datatable__pager kt-datatable--paging-loaded">
                        {!! $listItem->appends(isset($param_url) ? $param_url : '')->links() != '' ? $listItem->appends(isset($param_url) ? $param_url : '')->links() : '<ul class="pagination page-numbers nav-pagination links text-center"></ul>' !!}
                        <div class="kt-datatable__pager-info">
                            <div class="dropdown bootstrap-select kt-datatable__pager-size"
                                 style="width: 60px;">
                                <select class="selectpicker kt-datatable__pager-size select-page-size"
                                        onchange="$('input[name=limit]').val($(this).val());$('#form-search').submit();"
                                        title="Chọn số bản ghi hiển thị" data-width="60px"
                                        data-selected="20" tabindex="-98">
                                    <option value="20" {{ $limit == 20 ? 'selected' : '' }}>20</option>
                                    <option value="30" {{ $limit == 30 ? 'selected' : '' }}>30</option>
                                    <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $limit == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                            <span class="kt-datatable__pager-detail">Hiển thị {{ (($page - 1) * $limit) + 1 }} - {{ ($page * $limit) < $record_total ? ($page * $limit) : $record_total }} của {{ @number_format($record_total) }}</span>
                        </div>
                    </div>
                </div>
                <!--end: Datatable -->
            </div>
        </div>
        <div class="lead-chart">
            <span>Doanh số: {{ number_format($doanh_so, 0, '.', '.') }}đ.</span>&nbsp;&nbsp;|&nbsp;&nbsp;
            <span>Tổng $: {{ number_format($total_price_contract, 0, '.', '.') }}đ.</span>&nbsp;&nbsp;
            <span>$ đã thu: {{ number_format($total_received, 0, '.', '.') }}đ.</span>&nbsp;&nbsp;
            <span>$ chưa thu: {{ number_format($total_price_contract - $total_received, 0, '.', '.') }}đ.</span>&nbsp;&nbsp;
        </div>
    </div>

@endsection

@section('custom_head')
    <link type="text/css" rel="stylesheet" charset="UTF-8"
          href="{{ asset(config('core.admin_asset').'/css/list.css') }}">

    <style type="text/css">
        table tr:hover .div-tooltip_info {
            opacity: 1;
            display: block;
        }

        .nha-mang img {
            width: 30px;
        }

        .nha-mang {
            position: absolute;
            right: 0;
            top: 22px;
        }

        @media (max-width: 768px) {
            .kt-portlet {
                display: inherit;
            }
        }
    </style>

    <style>
        /*fix cứng dòng tiêu đề khi cuộn xuống*/
        .kt-portlet__body thead.kt-datatable__head {
            top: 45px;
            z-index: 1;
            background: #ffffff;

        }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(1) {

        }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(2) {

        }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(3) {
            width: 13vw;
        }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(4) {
            width: 15vw;
        }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(5) {
            width: 8vw;
        }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(6) {
            width: 6vw;
        }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(7) {
            width: 7vw;
        }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(8) {
            width: 5vw;
        }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(9) {
            width: 6vw;
        }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(10) {
            width: 10vw;
        }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(11) {
            width: 10vw;
        }

        /*.kt-portlet__body table td {
            width: 100px; !* Set the desired fixed width for the table cells *!
            white-space: nowrap; !* Prevent line breaks within cells *!
            overflow: hidden; !* Hide any overflowing content *!
            text-overflow: ellipsis; !* Show ellipsis (...) for text that overflows *!
        }*/
    </style>

@endsection
@section('custom_footer')
    <script src="{{ asset(config('core.admin_asset').'/js/pages/crud/metronic-datatable/advanced/vertical.js') }}"
            type="text/javascript"></script>
    <script src="{{ asset(config('core.admin_asset').'/js/list.js') }}"></script>
    @include(config('core.admin_theme').'.partials.js_common')
@endsection
@push('scripts')
    @include(config('core.admin_theme').'.partials.js_common_list')

    <script>
        //  fix cứng dòng tiêu đề khi cuộn xuống
        window.addEventListener('scroll', function() {
            var header = document.querySelector('.kt-datatable__head');
            var container = document.querySelector('.table-striped');
            var rect = container.getBoundingClientRect();

            if (rect.top <= 0 && rect.bottom > 0) {
                header.style.position = 'fixed';
                header.style.top = '20';
                container.classList.add('scrolled');
            } else {
                header.style.position = 'static';
                container.classList.remove('scrolled');
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            $('table tr').hover(function () {
                var div = $(this).find('.div-tooltip_info');
                console.log(div.html());
                if (div.html().indexOf('<img class="tooltip_info_loading" src="/images_core/icons/loading.gif">') != -1) {
                    var id = div.parents('tr').find('td:first-child').text();
                    $.ajax({
                        url: '/admin/bill/tooltip-info',
                        type: 'GET',
                        data: {
                            id: id,
                            modal: div.data('modal'),
                            tooltip_info: div.data('tooltip_info'),
                        },
                        success: function (result) {
                            div.html(result);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log('Có lỗi xảy ra!');
                            console.log('Trạng thái: ' + textStatus); // Trạng thái lỗi (timeout, error, abort, parseerror)
                            console.log('Lỗi được ném: ' + errorThrown); // Lỗi cụ thể được máy chủ ném ra (nếu có)
                            console.log('Chi tiết lỗi: ' + jqXHR.responseText); // Nội dung chi tiết của response từ server
                        }
                    });
                } else {
                    console.log(div.html().indexOf('<img class="tooltip_info_loading" src="/images_core/icons/loading.gif">'));
                }
            });


            //  Kiểm tra nhà mạng
            $('.kt-datatable tr td.item-tel').each(function () {
                sdt = $(this).text().trim();
                sdt = '0' + sdt.replace(" ", "").replace(".", "").replace(".", "");
                tel = sdt.substring(0, 3);


                const viettel = ['086', '096', '097', '098', '032', '033', '034', '035', '036', '037', '038', '039'];
                const vinaphone = ['091', '094', '083', '084', '085', '081', '082'];
                const mobiphone = ['090', '093', '012', '012', '012', '012', '012', '089'];
                const vietnamobile = ['092', '056', '058'];

                if (viettel.indexOf(tel) != -1) {
                    $(this).append('<a href="tel:' + sdt + '" title="viettel" class="nha-mang"><img src="https://salt.tikicdn.com/assets/img/vas/viettel.png" alt="Provider"></a>');
                } else if (vinaphone.indexOf(tel) != -1) {
                    $(this).append('<a href="tel:' + sdt + '" title="vinaphone" class="nha-mang"><img src="https://salt.tikicdn.com/assets/img/vas/vinaphone.png" alt="Provider"></a>');
                } else if (mobiphone.indexOf(tel) != -1) {
                    $(this).append('<a href="tel:' + sdt + '" title="mobifone" class="nha-mang"><img src="https://salt.tikicdn.com/assets/img/vas/mobifone.png" alt="Provider"></a>');
                } else if (vietnamobile.indexOf(tel) != -1) {
                    $(this).append('<a href="tel:' + sdt + '" title="vietnammobile" class="nha-mang"><img src="https://salt.tikicdn.com/assets/img/vas/vietnammobile.png" alt="Provider"></a>');
                } else {
                    $(this).append('<span class="nha-mang"></span>');
                }
            });
        });
    </script>
    <script>
        const COOKIE_NAME = 'visible_columns_dhbill';

        function saveVisibleColumnsToCookie(columns) {
            document.cookie = COOKIE_NAME + "=" + JSON.stringify(columns) + ";path=/;expires=Fri, 31 Dec 9999 23:59:59 GMT";

            // $minutes = 60 * 24 * 365 * 10;
            // Cookie::queue(COOKIE_NAME, JSON.stringify(columns), $minutes);
        }

        function getVisibleColumnsFromCookie() {
            const cookies = document.cookie.split(';');
            for (const cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === COOKIE_NAME) {
                    try {
                        return JSON.parse(value);
                    } catch (e) {
                        return null;
                    }
                }
            }
            return null;
        }

        function applyColumnVisibility(columns) {
            $('.toggle-column').each(function () {
                const col = $(this).data('column');
                const visible = columns.includes(col);
                $(this).prop('checked', visible);
                toggleColumn(col, visible);
            });

            const total = $('.toggle-column').length;
            const selected = $('.toggle-column:checked').length;
            $('#toggle-all-columns').prop('checked', total === selected);
        }

        function toggleColumn(column, visible) {
            const display = visible ? '' : 'none';
            $('th[data-column="' + column + '"], td[data-column="' + column + '"]').css('display', display);
        }

        $(document).ready(function () {
            let visibleColumns = getVisibleColumnsFromCookie();

            if (!visibleColumns) {
                const hiddenColumns = ['field_1', 'field_2', 'field_3', 'field_4', 'field_5'];
                visibleColumns = $('.toggle-column').map(function () {
                    const col = $(this).data('column');
                    return hiddenColumns.includes(col) ? null : col;
                }).get();
                saveVisibleColumnsToCookie(visibleColumns);
            }

            applyColumnVisibility(visibleColumns);

            $('.toggle-column').on('change', function () {
                const column = $(this).data('column');
                const isVisible = $(this).is(':checked');

                if (isVisible && !visibleColumns.includes(column)) {
                    visibleColumns.push(column);
                } else {
                    visibleColumns = visibleColumns.filter(col => col !== column);
                }

                saveVisibleColumnsToCookie(visibleColumns);
                toggleColumn(column, isVisible);

                const total = $('.toggle-column').length;
                const selected = $('.toggle-column:checked').length;
                $('#toggle-all-columns').prop('checked', total === selected);
            });

            $('#toggle-all-columns').on('change', function () {
                const checkAll = $(this).is(':checked');
                $('.toggle-column').prop('checked', checkAll).trigger('change');
            });
        });
    </script>
@endpush
