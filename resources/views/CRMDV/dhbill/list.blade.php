@extends(config('core.admin_theme').'.template')
@section('main')
<?php
if (@$_GET['saler_id'] != null) {
    //  Nếu có lọc theo sale
    $whereSale = 'saler_id = ' . $_GET['saler_id'];
    $whereLikeSale = "saler_id LIKE '%|".$_GET['saler_id']."|%'";
} else {
    //  Nếu ko lọc theo sale
    if(in_array('view_all_data',$permissions)) {
        //  Nếu được xem toàn bộ data
        $whereSale = $whereLikeSale = '1=1';
    } else {
        //  Nếu ko được xem toàn bộ data thì truy vấn ra data của mình thôi
        $whereSale = 'saler_id = ' . \Auth::guard('admin')->user()->id;
        $whereLikeSale = "saler_id LIKE '%|".\Auth::guard('admin')->user()->id."|%'";
    }
}

$where_hoan_thanh_date = '1=1';
if (!is_null(@$_GET['from_date']) && @$_GET['from_date'] != '') {
    $where_hoan_thanh_date .= " AND created_at >= '" . date('Y-m-d 00:00:00', strtotime($_GET['from_date'])) . "'";
}
if (!is_null(@$_GET['to_date']) && @$_GET['to_date'] != '') {
    $where_hoan_thanh_date .= " AND created_at <= '" . date('Y-m-d 23:59:59', strtotime($_GET['to_date'])) . "'";
}

?>
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <div class="lead-chart">
            <label style="color: red;">Tổng điểm: {{ $tong_diem }}</label>&nbsp;&nbsp;&nbsp;&nbsp;
            <span>Điểm kỹ thuật: 0</span>&nbsp;&nbsp;&nbsp;&nbsp;
            <span>Điểm điều hành: 0</span>&nbsp;&nbsp;&nbsp;&nbsp;
            <span>Điểm theo giờ: 0</span>&nbsp;&nbsp;&nbsp;&nbsp;
        </div>

        <div class="kt-portlet kt-portlet--mobile">
            <div class="kt-portlet__head kt-portlet__head--lg">
                <div class="kt-portlet__head-label">
			<span class="kt-portlet__head-icon">
                <i class="kt-font-brand flaticon2-avatar"></i>
			</span>
                    <h3 class="kt-portlet__head-title">
                        {!! @$module['label'] !!}
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
                                <label class="dropdown-item cursor-pointer">
                                    <input type="checkbox" class="toggle-column mr-2" data-column="sales" checked>
                                    Sales
                                </label>
                            </div>
                        </div>

                        @if(in_array('lead_float_view', $permissions))
                            <div>
                                <a href="/admin/lead?limit=20&lead_status=%C4%90%E1%BA%BFn+ng%C3%A0y+TT"
                                   class="btn {{ @$_GET['lead_status'] == 'Đến ngày TT' ? 'btn-primary' : 'btn-default' }}">Đến ngày TT</a>
                                <a href="/admin/lead"
                                   class="btn {{ strpos($_SERVER['REQUEST_URI'], '/tha-noi') == false && strpos($_SERVER['REQUEST_URI'], '/quan-tam-moi') == false ? 'btn-primary' : 'btn-default' }}">Khách
                                    của tôi</a>
                                <a href="/admin/lead/tha-noi"
                                   class="btn {{ strpos($_SERVER['REQUEST_URI'], '/tha-noi') != false ? 'btn-primary' : 'btn-default' }}">Khách
                                    thả nổi</a>

                            </div>
                        @endif
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
                                        <li class="kt-nav__item">
                                            <a class="kt-nav__link export-excel"
                                               title="Xuất các bản ghi đang lọc ra file excel"
                                               onclick="$('input[name=export]').click();">
                                                <i class="kt-nav__link-icon la la-file-excel-o"></i>
                                                <span class="kt-nav__link-text">Xuất Excel</span>
                                            </a>
                                        </li>
                                        @if(in_array('super_admin', $permissions))
                                            <li class="kt-nav__item">
                                                <a href="#" class="kt-nav__link" onclick="billProcessChangeStatus();"
                                                   title="Chuyển trạng thái">
                                                    <i class="kt-nav__link-icon la la-copy"></i>
                                                    <span class="kt-nav__link-text">Chuyển Trạng thái</span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            &nbsp;

                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-portlet__body kt-portlet-search @if(!isset($_GET['search'])) no-padding @endif">
                <!--begin: Search Form -->
                <form class="kt-form kt-form--fit kt-margin-b-20 form-search" id="form-search" method="GET" action=""
                      @if(!isset($_GET['search'])) style="display: none;" @endif>
                    <input name="search" type="hidden" value="true">
                    <input name="limit" type="hidden" value="{{ $limit }}"><input type="hidden" name="quick_search"
                                                                                  value="{{ @$_GET['quick_search'] }}"
                                                                                  id="quick_search_hidden"
                                                                                  class="form-control"
                                                                                  placeholder="Tìm kiếm nhanh">
                    <div class="row">

                        @foreach($filter as $filter_name => $field)
                            <div class="col-sm-6 col-lg-3 kt-margin-b-10-tablet-and-mobile list-filter-item">
                                <label>{{ @$field['label'] }}:</label>
                                @include(config('core.admin_theme').'.list.filter.' . $field['type'], ['name' => $filter_name, 'field'  => $field])
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
                            <th>STT</th>
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

                        </tr>
                        </thead>

                        <tbody class="kt-datatable__body ps ps--active-y" style="max-height: 496px;">
                        <?php
                        $sdt_arr = [];
                        $k=0;
                        ?>
                        @foreach($listItem as $item)
                                <?php
                                $sdt_arr[] = $item->tel;
                                $k++;
                                ?>
                            <tr data-row="0" class="kt-datatable__row" style="left: 0px;">
                                <td style="display: none;"
                                    class="id id-{{ $item->id }}">{{ $item->id }}</td>
                                <td>{{ $k }}</td>
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
                                            @include(config('core.admin_theme').'.list.td.'.$field['type'])
                                        @endif
                                    </td>
                                @endforeach


                                <!-- Nếu được xem hết dữ liệu thì hiển thị ra cột sale phụ trách -->
                                <td data-field="company_id" data-column="sales"
                                    class="kt-datatable__cell kt-datatable__cell--sort">

                                        <?php
                                        $field = ['name' => 'saler_ids', 'type' => 'admins', 'label' => 'Sales', 'model' => \App\Models\Admin::class, 'object' => 'admin', 'display_field' => 'name', 'display_field2' => 'code'];
                                        ?>
                                    @include(config('core.admin_theme').'.list.td.admins')
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
    </div>

    @include('CRMDV.dhbill.partials.change_status_bill_process')
@endsection

@section('custom_head')
    <link type="text/css" rel="stylesheet" charset="UTF-8"
          href="{{ asset(config('core.admin_asset').'/css/list.css') }}">
    <style type="text/css">
        table tr:hover .div-tooltip_info {
            opacity: 1;
            display: block;
        }
        .kt-datatable .table-striped tbody tr.ket-thuc {
            background-color: #ccc !important;
        }
        .kt-datatable .table-striped tbody tr.khach-xac-nhan-xong {
            background-color: #e1fff6 !important;
        }
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
    $(document).ready(function () {
        $('table tr').hover(function () {
            var div = $(this).find('.div-tooltip_info');
            if (div.html().trim() == '<img class="tooltip_info_loading" src="/images_core/icons/loading.gif">') {
                var id = div.parents('tr').find('td:first-child').text();
                $.ajax({
                    url: '/admin/bill_progress_history/ajax-lich-su-trang-thai',
                    type: 'GET',
                    data: {
                        bill_id: id,
                    },
                    success: function (result) {
                        div.html(result);
                    },
                    error: function () {
                        console.log('tooltip-info Có lỗi xảy ra!');
                    }
                });
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
