@extends(config('core.admin_theme').'.template')
@section('main')
    <?php
    // --- XỬ LÝ LOGIC PHP (GIỮ NGUYÊN) ---
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

    $day = isset($_GET['day']) ? $_GET['day'] : date('Y-m-d');

    // --- TÍNH TOÁN CHỈ SỐ (GIỮ NGUYÊN) ---
    $count_leads_created = \App\CRMDV\Models\Lead::whereRaw($whereSale)->whereRaw($whereCreated_at)->count();
    $coun_lead_contacted_logs = \App\CRMDV\Models\LeadContactedLog::whereRaw($whereSale)->whereRaw($whereCreated_at)->whereNotIn('note', ['Không nghe', 'Sai số'])->count();
    $count_khqt = \App\CRMDV\Models\Lead::whereRaw($whereLikeSale)->whereIn('rate', ['Đang tìm hiểu', 'Care dài'])->whereNotIn('status', ['Thả nổi', 'Đã ký HĐ'])->count();
    $quanTamMoi = \App\CRMDV\Models\LeadContactedLog::whereRaw($whereSale)->where('created_at', '>', date('Y-m-d 00:00:00'))->where('type', 'lead_quan_tam_lai')->count();
    $count_khqt_cao = \App\CRMDV\Models\Lead::whereRaw($whereLikeSale)->whereIn('rate', ['Quan tâm cao'])->whereNotIn('status', ['Thả nổi', 'Đã ký HĐ'])->count();
    $count_co_hoi = \App\CRMDV\Models\Lead::whereRaw($whereLikeSale)->whereIn('rate', ['Cơ hội'])->whereNotIn('status', ['Thả nổi', 'Đã ký HĐ'])->where('created_at', '>=', date('Y-' . date('m', strtotime($day)) . '-01 00:00:00'))->where('created_at', '<=', date('Y-' . date('m', strtotime($day)) . '-t 23:59:59', strtotime($day)))->count();

    $plan = \App\CRMDV\Models\Plan::whereRaw($whereSale)->orderBy('id', 'desc')->first();
    $ds_tuan = \App\CRMDV\Models\Bill::whereRaw($whereSaler)->where('registration_date', '>=', date('Y-m-d 00:00:00', strtotime('-' . date('w') . ' days')))->sum('total_price');
    $ds_thang = \App\CRMDV\Models\Bill::whereRaw($whereSaler)->where('registration_date', '>=', date('Y-m-01 00:00:00', time()))->sum('total_price');
    $hd = \App\CRMDV\Models\Bill::whereRaw($whereSaler)->where('registration_date', '>=', date('Y-m-d 00:00:00', strtotime('-' . date('w') . ' days')))->count();
    ?>

    <div class="kt-container kt-container--fluid kt-grid__item kt-grid__item--fluid">

        {{-- PHẦN THỐNG KÊ (GIỮ NGUYÊN NỘI DUNG NHƯNG STYLE LẠI CHÚT CHO GỌN) --}}
        <div class="lead-chart" style="margin-bottom: 15px;">
            <label style="color: red; font-weight: bold;">Mục tiêu tuần @if(is_object($plan)) ({{ date('d/m', strtotime(@$plan->updated_at)) }}) @endif</label>
            <span>Tạo mới: <b>{{ $count_leads_created }}/60</b></span>
            <span><a target="_blank" href="/admin/timekeeping/add">Tương tác: <b>{{ $coun_lead_contacted_logs }}/60</b></a></span>
            <span>KHQT mới: <b>{{ $quanTamMoi }}/{{ @$plan->khqt_moi }}</b></span>
            <span>KHQT: <b>{{ $count_khqt }}/{{ @$plan->khqt }}</b></span>
            <span>KHQT cao: <b>{{ $count_khqt_cao }}/{{ @$plan->khqt_cao }}</b></span>
            <span>Cơ hội: <b>{{ $count_co_hoi }}/{{ @$plan->co_hoi }}</b></span>
            <span>HĐ: <b>{{ $hd }}/{{ @$plan->hd }}</b></span>
            <span title="Doanh số thực / mục tiêu tuần">DS Tuần: <b>{{ number_format($ds_tuan/1000000, 1, ',', '.') }}/@if(is_object($plan)){{ number_format(@$plan->ds_tuan, 0, '.', '.') }}@endif</b></span>
            <span title="Doanh số thực / mục tiêu tháng" style="margin-left: 10px; border-left: 1px solid #ccc; padding-left: 10px;">
                <label style="color: red;">Tháng:</label>
                <b>{{ number_format($ds_thang/1000000, 1, ',', '.') }}/@if(is_object($plan)){{ number_format(@$plan->ds_thang, 0, '.', '.') }}@endif</b>
            </span>
        </div>

        <div class="kt-portlet kt-portlet--mobile">
            <div class="kt-portlet__head kt-portlet__head--lg">
                <div class="kt-portlet__head-label">
                     <span class="kt-portlet__head-icon">
                       <i class="kt-font-brand flaticon2-avatar"></i>
                    </span>
                    <h3 class="kt-portlet__head-title">
                        {{ $module['label'] }}
                    </h3>
                </div>
                @php
                    $extendFields = \DB::table('tags')->where('type', 'leads_field_extend')->pluck('name', 'slug')->toArray();
                @endphp
                <div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-wrapper">
                        {{-- DROPDOWN CHỌN CỘT --}}
                        <div class="dropdown show-columns-dropdown mr-1">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                <span class="kt-menu__link-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><path d="M5,8.6862915 L5,5 L8.6862915,5 L11.5857864,2.10050506 L14.4852814,5 L19,5 L19,9.51471863 L21.4852814,12 L19,14.4852814 L19,19 L14.4852814,19 L11.5857864,21.8994949 L8.6862915,19 L5,19 L5,15.3137085 L1.6862915,12 L5,8.6862915 Z M12,15 C13.6568542,15 15,13.6568542 15,12 C15,10.3431458 13.6568542,9 12,9 C10.3431458,9 9,10.3431458 9,12 C9,13.6568542 10.3431458,15 12,15 Z" fill="#000000"></path></g></svg></span>
                            </button>
                            <div class="dropdown-menu">
                                <label class="dropdown-item"><strong>Chọn cột hiển thị</strong></label>
                                <hr class="dropdown-divider">
                                <label class="dropdown-item cursor-pointer">
                                    <input type="checkbox" class="mr-2" id="toggle-all-columns" checked>
                                    <strong>Chọn tất cả</strong>
                                </label>
                                <hr class="dropdown-divider">
                                @php $defaultFields = ['name', 'rate', 'tel', 'sales']; @endphp
                                @foreach($module['list'] as $field)
                                    @if(!in_array($field['name'], $defaultFields))
                                        <label class="dropdown-item cursor-pointer">
                                            <input type="checkbox" class="toggle-column mr-2" data-column="{{ $field['name'] }}" checked>
                                            {{ $extendFields[$field['name']] ?? $field['label'] }}
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- FILTER NHANH (NẾU CÓ) --}}
                        @if(in_array('lead_float_view', $permissions))
                            <div>
                                <a href="/admin/lead?limit=20&lead_status=%C4%90%E1%BA%BFn+ng%C3%A0y+TT" class="btn {{ @$_GET['lead_status'] == 'Đến ngày TT' ? 'btn-primary' : 'btn-default' }}">Đến ngày TT</a>
                                <a href="/admin/lead" class="btn {{ strpos($_SERVER['REQUEST_URI'], '/tha-noi') == false && strpos($_SERVER['REQUEST_URI'], '/quan-tam-moi') == false ? 'btn-primary' : 'btn-default' }}">Khách của tôi</a>
                                <a href="/admin/lead/tha-noi" class="btn {{ strpos($_SERVER['REQUEST_URI'], '/tha-noi') != false ? 'btn-primary' : 'btn-default' }}">Khách thả nổi</a>
                            </div>
                        @endif

                        <div class="">
                            <input type="text" name="quick_search" value="{{ @$_GET['quick_search'] }}" class="form-control" title="Chỉ cần enter để thực hiện tìm kiếm" placeholder="Tìm kiếm nhanh">
                        </div>

                        <div class="kt-portlet__head-actions">
                            <button type="button" class="btn btn-default btn-icon-sm dropdown-toggle btn-closed-search" onclick="$('.form-search').slideToggle(100); $('.kt-portlet-search').toggleClass('no-padding');">
                                <i class="la la-search"></i> Tìm kiếm
                            </button>
                        </div>

                        {{-- ACTION DROPDOWN --}}
                        <div class="dropdown dropdown-inline">
                            <button type="button" class="btn btn-default btn-icon-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="la la-download"></i> Hành động
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <ul class="kt-nav">
                                    <li class="kt-nav__section kt-nav__section--first">
                                        <span class="kt-nav__section-text">Chọn hành động</span>
                                    </li>
                                    @if (\Auth::guard('admin')->user()->super_admin == 1)
                                        <li class="kt-nav__item">
                                            <a class="kt-nav__link export-excel" title="Xuất excel" onclick="$('input[name=export]').click();">
                                                <i class="kt-nav__link-icon la la-file-excel-o"></i>
                                                <span class="kt-nav__link-text">Xuất Excel</span>
                                            </a>
                                        </li>
                                    @endif
                                    <li class="kt-nav__item">
                                        <a href="/admin/import/add?table=leads&table_label=Đầu mối" class="kt-nav__link" title="Import excel">
                                            <i class="kt-nav__link-icon la la-copy"></i>
                                            <span class="kt-nav__link-text">Import excel</span>
                                        </a>
                                    </li>
                                    @if(in_array($module['code'] . '_edit', $permissions))
                                        <li class="kt-nav__item">
                                            <a href="javascript:void(0);" class="kt-nav__link" onclick="releaseLeads();" title="Thả nổi">
                                                <i class="kt-nav__link-icon flaticon-refresh"></i>
                                                <span class="kt-nav__link-text">Thả nổi</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if(in_array($module['code'] . '_delete', $permissions))
                                        <li class="kt-nav__item">
                                            <a href="#" class="kt-nav__link" onclick="multiDelete();" title="Xóa nhiều">
                                                <i class="kt-nav__link-icon la la-copy"></i>
                                                <span class="kt-nav__link-text">Xóa nhiều</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if(in_array($module['code'] . '_assign', $permissions))
                                        <li class="kt-nav__item">
                                            <a href="#" class="kt-nav__link" onclick="leadAssign();" title="Chuyển lead">
                                                <i class="kt-nav__link-icon la la-copy"></i>
                                                <span class="kt-nav__link-text">Chuyển lead</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        @if(in_array($module['code'] . '_add', $permissions))
                            @php
                                $url_add = url('/admin/'.$module['code'].'/add/');
                                if (strpos($_SERVER['REQUEST_URI'], 'doi-tac') !== false) {
                                    $url_add .= '?doi_tac_cua_toi=on';
                                }
                            @endphp
                            <a href="{{ $url_add }}" class="btn btn-brand btn-elevate btn-icon-sm">
                                <i class="la la-plus"></i> Tạo mới
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- SEARCH FORM --}}
            <div class="kt-portlet__body kt-portlet-search @if(!isset($_GET['search'])) no-padding @endif">
                <form class="kt-form kt-form--fit kt-margin-b-20 form-search" id="form-search" method="GET" action="" @if(!isset($_GET['search'])) style="display: none;" @endif>
                    <input name="search" type="hidden" value="true">
                    <input name="limit" type="hidden" value="{{ $limit }}">
                    <input type="hidden" name="quick_search" value="{{ @$_GET['quick_search'] }}" id="quick_search_hidden">

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
                                <span><i class="la la-search"></i><span>Lọc</span></span>
                            </button>
                            &nbsp;&nbsp;
                            <a class="btn btn-secondary btn-secondary--icon" id="kt_reset" title="Xóa bỏ bộ lọc" href="/admin/{{ $module['code'] }}">
                                <span><i class="la la-close"></i><span>Reset</span></span>
                            </a>
                        </div>
                    </div>
                    <input name="export" type="submit" value="export" style="display: none;">
                    @foreach($module['list'] as $k => $field)
                        <input name="sorts[]" value="{{ @$_GET['sorts'][$k] }}" class="sort sort-{{ $field['name'] }}" type="hidden">
                    @endforeach
                </form>
            </div>

            <div class="kt-separator kt-separator--md kt-separator--dashed" style="margin: 0;"></div>

            <div class="kt-portlet__body kt-portlet__body--fit">
                <div class="kt-datatable kt-datatable--default kt-datatable--brand kt-datatable--scroll kt-datatable--loaded" id="scrolling_vertical" style="">
                    <table class="table table-striped">
                        <thead class="kt-datatable__head">
                        <tr class="kt-datatable__row" style="left: 0px;">
                            <th style="display: none;"></th>
                            <th data-field="id" class="kt-datatable__cell--center kt-datatable__cell kt-datatable__cell--check">
                                    <span style="width: 20px;">
                                        <label class="kt-checkbox kt-checkbox--single kt-checkbox--all kt-checkbox--solid">
                                            <input type="checkbox" class="checkbox-master">&nbsp;<span></span>
                                        </label>
                                    </span>
                            </th>

                            @php $count_sort = 0; @endphp
                            @foreach($module['list'] as $field)
                                <th data-field="{{ $field['name'] }}" data-column="{{ $field['name'] }}"
                                    class="kt-datatable__cell dieptv-thead-th kt-datatable__cell--sort {{ @$_GET['sorts'][$count_sort] != '' ? 'kt-datatable__cell--sorted' : '' }}"
                                    @if(isset($field['sort'])) onclick="sort('{{ $field['name'] }}')" @endif>
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

                            <th data-field="company_id" data-column="sales" class="kt-datatable__cell kt-datatable__cell--sort">Sales</th>
                        </tr>
                        </thead>
                        <tbody class="kt-datatable__body ps ps--active-y" style="max-height: 496px;">
                            <?php $sdt_arr = []; ?>
                        @foreach($listItem as $item)
                                <?php $sdt_arr[] = $item->tel; ?>
                            <tr data-row="0" class="kt-datatable__row" style="left: 0px;">
                                <td style="display: none;" class="id id-{{ $item->id }}">{{ $item->id }}</td>

                                <td class="kt-datatable__cell--center kt-datatable__cell kt-datatable__cell--check" data-field="ID">
                                        <span style="width: 20px;">
                                            <label class="kt-checkbox kt-checkbox--single kt-checkbox--solid">
                                                <input name="id[]" type="checkbox" class="ids" value="{{ $item->id }}">&nbsp;<span></span>
                                            </label>
                                        </span>
                                    <div style="width: 500px; left: 356px;" id="tooltip-info-{{$field['name']}}" class="dropdown-menu div-tooltip_info" data-modal="{{ $module['modal'] }}" data-tooltip_info="{{ json_encode(@$field['tooltip_info']) }}">
                                        <img class="tooltip_info_loading" src="/images_core/icons/loading.gif">
                                    </div>
                                </td>

                                @foreach($module['list'] as $field)
                                    <td data-field="{{ @$field['name'] }}" data-column="{{ $field['name'] }}" class="kt-datatable__cell item-{{ @$field['name'] }}">
                                        @if($field['type'] == 'custom')
                                            @include($field['td'], ['field' => $field])
                                        @else
                                            @include(config('core.admin_theme').'.list.td.'.$field['type'])
                                        @endif
                                    </td>
                                @endforeach

                                <td data-field="company_id" data-column="sales" class="kt-datatable__cell kt-datatable__cell--sort">
                                        <?php $field = ['name' => 'saler_ids', 'type' => 'admins', 'label' => 'Sales', 'model' => \App\Models\Admin::class, 'object' => 'admin', 'display_field' => 'name', 'display_field2' => 'code']; ?>
                                    @include(config('core.admin_theme').'.list.td.admins')
                                </td>
                            </tr>
                        @endforeach
                        <div class="ps__rail-x" style="left: 0px; bottom: 0px;"><div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div></div>
                        <div class="ps__rail-y" style="top: 0px; height: 496px; right: 0px;"><div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 207px;"></div></div>
                        </tbody>
                    </table>

                    {{-- KIỂM TRA SỐ ĐIỆN THOẠI (NẾU CÓ) --}}
                    @if(@$_GET['check_tel'] != null)
                        <div style="padding: 10px;">
                            <label>Các số điện thoại chưa có trên hệ thống: </label>
                                <?php
                                $check_tel = $_GET['check_tel'];
                                $tels = preg_split('/\r\n|[\r\n]/', $check_tel);
                                foreach ($tels as $k => $v) {
                                    $v = trim($v);
                                    $v = str_replace([' ', '.', ','], '', $v);
                                    if ($v != '' && mb_substr($v, 0, 1) != '0') $v = '0' . $v;
                                    if (!in_array($v, $sdt_arr)) echo '<span>' . $v . ' , </span>';
                                }
                                ?>
                        </div>
                    @endif

                    <div class="kt-datatable__pager kt-datatable--paging-loaded">
                        {!! $listItem->appends(isset($param_url) ? $param_url : '')->links() != '' ? $listItem->appends(isset($param_url) ? $param_url : '')->links() : '<ul class="pagination page-numbers nav-pagination links text-center"></ul>' !!}
                        <div class="kt-datatable__pager-info">
                            <div class="dropdown bootstrap-select kt-datatable__pager-size" style="width: 60px;">
                                <select class="selectpicker kt-datatable__pager-size select-page-size" onchange="$('input[name=limit]').val($(this).val());$('#form-search').submit();" title="Chọn số bản ghi hiển thị" data-width="60px" data-selected="20" tabindex="-98">
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
            </div>
        </div>
    </div>

    @if(in_array($module['code'] . '_assign', $permissions))
        @include('CRMDV.lead.list.partials.lead_assign')
    @endif

    @if(in_array(CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name'), ['telesale', 'sale', 'truong_phong_sale']))
        @include('CRMDV.lead.list.partials.nhac_nho_telesale')
    @endif
@endsection

@section('custom_head')
    <link type="text/css" rel="stylesheet" charset="UTF-8" href="{{ asset(config('core.admin_asset').'/css/list.css') }}">
    <style type="text/css">
        /* Cursor pointer global */
        .cursor-pointer { cursor: pointer; }
        .ct-1 { display: inline-block; float: right; }

        .kt-datatable__body td.item-rate span {
            border-radius: 50px !important; /* Bo tròn tuyệt đối */
            padding: 4px 12px !important;   /* Tăng khoảng cách nội dung để nút trông thoáng hơn */
            font-weight: 500;               /* Làm đậm chữ một chút cho dễ đọc */
            white-space: nowrap;            /* Giữ chữ trên 1 dòng */
        }

        /* Tooltip info */
        table tr:hover .div-tooltip_info { opacity: 1; display: block; }

        /* Nhà mạng icons */
        .nha-mang img { width: 30px; }
        .nha-mang { position: absolute; right: 0; top: 13px; }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .kt-portlet { display: inherit; }
            .kt-portlet__head-label { position: relative; padding-bottom: 15px!important; }
            .show-columns-dropdown { position: absolute !important; top: 3%; left: 0 !important; }
            .kt-portlet__head-label h3 { margin-right: 10px; }
            .lead-chart {
                display: flex; flex-wrap: nowrap; overflow-x: auto; white-space: nowrap;
                -webkit-overflow-scrolling: touch; align-items: center; gap: 12px; padding: 5px 0;
            }
            .lead-chart label, .lead-chart span, .lead-chart a { flex: 0 0 auto; font-size: 13px; margin-bottom: 0; }
        }
    </style>

    <style>
        /* FIX CỨNG HEADER KHI CUỘN (Sticky Header) - GIỐNG BẢNG HỢP ĐỒNG */
        .kt-portlet__body thead.kt-datatable__head {
            top: 45px;
            z-index: 1;
            background: #ffffff;
        }

        /* Định nghĩa chiều rộng các cột khi Header bị dính (scrolled) */
        /* Cột 1, 2 là ID và Checkbox */

        /* Cột 3: Tên khách (Cần rộng) */
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(3) { width: 15vw; min-width: 200px; }

        /* Cột 4: Số điện thoại (Cần cố định) */
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(4) { width: 135px; }

        /* Các cột tiếp theo (Email, Nguồn, Trạng thái...) - Điều chỉnh theo thực tế bảng Lead */
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(5) { width: 10vw; }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(6) { width: 8vw; }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(7) { width: 8vw; }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(8) { width: 8vw; }
        .scrolled thead.kt-datatable__head .kt-datatable__row th:nth-child(9) { width: 8vw; }

        /* Style đặc biệt cho cột Số điện thoại trong body để không bị xuống dòng */
        .kt-datatable__body td.item-tel {
            width: 135px !important;
            min-width: 135px !important;
            max-width: 150px !important;
            white-space: nowrap;
            position: relative; /* Để icon nhà mạng căn absolute theo td này */
        }

        .kt-datatable__cell { padding: 10px; }
    </style>
@endsection

@section('custom_footer')
    <script src="{{ asset(config('core.admin_asset').'/js/pages/crud/metronic-datatable/advanced/vertical.js') }}" type="text/javascript"></script>
    <script src="{{ asset(config('core.admin_asset').'/js/list.js') }}"></script>
    @include(config('core.admin_theme').'.partials.js_common')
@endsection

@push('scripts')
    @include(config('core.admin_theme').'.partials.js_common_list')

{{--    <script>--}}
{{--        // SCRIPT STICKY HEADER - GIỐNG BẢNG HỢP ĐỒNG--}}
{{--        window.addEventListener('scroll', function() {--}}
{{--            var header = document.querySelector('.kt-datatable__head');--}}
{{--            var container = document.querySelector('.table-striped');--}}
{{--            var rect = container.getBoundingClientRect();--}}

{{--            if (rect.top <= 0 && rect.bottom > 0) {--}}
{{--                header.style.position = 'fixed';--}}
{{--                header.style.top = '20'; // Điều chỉnh top tùy vào thanh menu trên cùng--}}
{{--                container.classList.add('scrolled');--}}
{{--            } else {--}}
{{--                header.style.position = 'static';--}}
{{--                container.classList.remove('scrolled');--}}
{{--            }--}}
{{--        });--}}
{{--    </script>--}}

    <script>
        $(document).ready(function () {
            // Tooltip loading info
            $('table tr').hover(function () {
                var div = $(this).find('.div-tooltip_info');
                if (div.html().indexOf('<img class="tooltip_info_loading" src="/images_core/icons/loading.gif">') != -1) {
                    var id = div.parents('tr').find('td:first-child').text();
                    $.ajax({
                        url: '/admin/lead/tooltip-info',
                        type: 'GET',
                        data: {
                            id: id,
                            modal: div.data('modal'),
                            tooltip_info: div.data('tooltip_info'),
                        },
                        success: function (result) {
                            div.html(result);
                        },
                        error: function () {
                            console.log('tooltip-info error');
                        }
                    });
                }
            });

            // Kiểm tra nhà mạng
            $('.kt-datatable tr td.item-tel').each(function () {
                var sdt = $(this).text().trim();
                sdt = '0' + sdt.replace(/ /g, "").replace(/\./g, "");
                var tel = sdt.substring(0, 3);

                const viettel = ['086', '096', '097', '098', '032', '033', '034', '035', '036', '037', '038', '039'];
                const vinaphone = ['091', '094', '083', '084', '085', '081', '082'];
                const mobiphone = ['090', '093', '012', '089']; // Cần check lại đầu 012 đã đổi
                const vietnamobile = ['092', '056', '058'];

                var html = '';
                if (viettel.indexOf(tel) != -1) {
                    html = '<a href="tel:' + sdt + '" title="viettel" class="nha-mang"><img src="https://salt.tikicdn.com/assets/img/vas/viettel.png" alt="Provider"></a>';
                } else if (vinaphone.indexOf(tel) != -1) {
                    html = '<a href="tel:' + sdt + '" title="vinaphone" class="nha-mang"><img src="https://salt.tikicdn.com/assets/img/vas/vinaphone.png" alt="Provider"></a>';
                } else if (mobiphone.indexOf(tel) != -1) {
                    html = '<a href="tel:' + sdt + '" title="mobifone" class="nha-mang"><img src="https://salt.tikicdn.com/assets/img/vas/mobifone.png" alt="Provider"></a>';
                } else if (vietnamobile.indexOf(tel) != -1) {
                    html = '<a href="tel:' + sdt + '" title="vietnammobile" class="nha-mang"><img src="https://salt.tikicdn.com/assets/img/vas/vietnammobile.png" alt="Provider"></a>';
                }
                $(this).append(html);
            });
        });

        // --- COOKIE LƯU CỘT HIỂN THỊ (Logic giống hệt bảng hợp đồng) ---
        const COOKIE_NAME = 'visible_columns_lead';

        function saveVisibleColumnsToCookie(columns) {
            document.cookie = COOKIE_NAME + "=" + JSON.stringify(columns) + ";path=/;expires=Fri, 31 Dec 9999 23:59:59 GMT";
        }

        function getVisibleColumnsFromCookie() {
            const cookies = document.cookie.split(';');
            for (const cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === COOKIE_NAME) {
                    try { return JSON.parse(value); } catch (e) { return null; }
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
                // Mặc định ẩn 1 số trường nếu chưa có cookie
                const hiddenColumns = ['field_1', 'field_2'];
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

        // Các hàm xử lý action (Thả nổi, Xóa...)
        function releaseLeads() {
            var ids = [];
            $('.ids:checked').each(function () { ids.push($(this).val()); });
            if (ids.length === 0) { toastr.warning('Vui lòng chọn ít nhất 1 bản ghi để thả nổi!'); return; }
            if (!confirm('Bạn có chắc chắn muốn chuyển trạng thái ' + ids.length + ' khách hàng đã chọn sang "Thả nổi"?')) return;

            $.ajax({
                url: '/admin/lead/multi-release',
                type: 'POST',
                data: { ids: ids, _token: $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    if (res.status) { toastr.success(res.msg); location.reload(); } else { toastr.error(res.msg); }
                },
                error: function (e) { toastr.error('Có lỗi xảy ra, vui lòng thử lại sau.'); }
            });
        }
    </script>
@endpush