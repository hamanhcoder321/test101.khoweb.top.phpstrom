@extends(config('core.admin_theme').'.template')
@section('main')
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <div class="row">
            <div class="col-xl-12 order-lg-2 order-xl-1">
                <div class="kt-portlet kt-portlet--height-fluid kt-portlet--mobile ">
                    <div class="kt-portlet__head kt-portlet__head--lg kt-portlet__head--noborder kt-portlet__head--break-sm">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title bold uppercase">
                                Sinh nhật nhân sự
                            </h3>
                        </div>
                    </div>

                    <div class="kt-portlet__body kt-portlet__body--fit">
                        <!--begin: Datatable -->
                        <div class="kt-datatable kt-datatable--default kt-datatable--brand kt-datatable--scroll kt-datatable--loaded"
                             id="kt_datatable_latest_orders" style="">
                            <table class="kt-datatable__table" style=" width: 100%;">
                                <thead class="kt-datatable__head" style="overflow: unset;">
                                <tr class="kt-datatable__row" style="left: 0px;">
                                    <th class="kt-datatable__cell kt-datatable__cell--sort" style="width: 15%;">Tháng</th>
                                    <th class="kt-datatable__cell kt-datatable__cell--sort" style="width: 35%;">Họ tên</th>
                                    <th class="kt-datatable__cell kt-datatable__cell--sort" style="width: 12%;">Mã</th>
                                    <th class="kt-datatable__cell kt-datatable__cell--sort" style="width: 12%;">Phòng ban</th>
                                    <th class="kt-datatable__cell kt-datatable__cell--sort" style="width: 12%;">Số điện thoại</th>
                                    <th class="kt-datatable__cell kt-datatable__cell--sort" style="width: 12%;">Ngày sinh</th>
                                </tr>
                                </thead>
                                <tbody class="kt-datatable__body ps ps--active-y">
                                @php
                                    $lastMonth = null;
                                    $roomOptions = [
                                        1 => 'Phòng kinh doanh 1',
                                        2 => 'Phòng kinh doanh 2',
                                        3 => 'Phòng kinh doanh 3',
                                        4 => 'Phòng kinh doanh 4',
                                        5 => 'Phòng kinh doanh 5',
                                        6 => 'Phòng Telesale',
                                        10 => 'Kỹ thuật',
                                        15 => 'Điều hành',
                                        20 => 'Marketing',
                                        25 => 'Tuyển dụng',
                                        30 => 'CSKH',
                                    ];
                                    foreach ($data as $item) {
                                        $item->room_name = $roomOptions[$item->room_id] ?? '';
                                    }
                                @endphp
                                @foreach($data as $item)
                                    <tr class="kt-datatable__row">
                                        @if($lastMonth !== $item->month)
                                            <td class="kt-datatable__cell" style="width: 15%;">Tháng {{ $item->month }}</td>
                                            @php $lastMonth = $item->month; @endphp
                                        @else
                                            <td class="kt-datatable__cell" style="width: 15%;"></td>
                                        @endif
                                        <td class="kt-datatable__cell" style="width: 35%;">{{ $item->name }}</td>
                                        <td class="kt-datatable__cell" style="width: 12%;">{{ $item->code }}</td>
                                        <td class="kt-datatable__cell" style="width: 12%;">{{ $item->room_name }}</td>
                                        <td class="kt-datatable__cell" style="width: 12%;">{{ $item->tel }}</td>
                                        <td class="kt-datatable__cell" style="width: 12%;">{{ \Carbon\Carbon::parse($item->birthday)->format('d/m') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!--end: Datatable -->
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('custom_head')
    <style type="text/css">
        .kt-datatable__cell > span > a.cate {
            color: #5867dd;
            margin-bottom: 3px;
            background: rgba(88, 103, 221, 0.1);
            height: auto;
            display: inline-block;
            width: auto;
            padding: 0.15rem 0.75rem;
            border-radius: 2px;
        }

        .paginate > ul.pagination > li {
            padding: 5px 10px;
            border: 1px solid #ccc;
            margin: 0 5px;
            cursor: pointer;
        }

        .paginate > ul.pagination span {
            color: #000;
        }

        .paginate > ul.pagination > li.active {
            background: #0b57d5;
            color: #fff !important;
        }

        .paginate > ul.pagination > li.active span {
            color: #fff !important;
        }

        .kt-widget12__desc, .kt-widget12__value {
            text-align: center;
        }

        @-webkit-keyframes chartjs-render-animation {
            from {
                opacity: 0.99 list_user
            }
            to {
                opacity: 1
            }
        }

        @keyframes chartjs-render-animation {
            from {
                opacity: 0.99
            }
            to {
                opacity: 1
            }
        }

        .chartjs-render-monitor {
            -webkit-animation: chartjs-render-animation 0.001s;
            animation: chartjs-render-animation 0.001s;
        }

        canvas {
            -moz-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
        }
    </style>
    <style type="text/css">
        @-webkit-keyframes chartjs-render-animation {
            from {
                opacity: 0.99
            }
            to {
                opacity: 1
            }
        }

        @keyframes chartjs-render-animation {
            from {
                opacity: 0.99
            }
            to {
                opacity: 1
            }
        }

        .chartjs-render-monitor {
            -webkit-animation: chartjs-render-animation 0.001s;
            animation: chartjs-render-animation 0.001s;
        }

        canvas {
            -moz-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
        }


        @media (max-width: 768px) {
            div#kt_datatable_latest_orders {
                overflow: auto;
            }
            table.kt-datatable__table {
                width: unset !important;
                display: inline-block !important;
            }
            .kt-widget12 .kt-widget12__content .thong_ke_so {
                display: inline-block;
            }

            .thong_ke_so .col-sm-3 {
                display: inline-block;
                width: 50%;
                float: left;
                padding: 0;
                margin-bottom: 20px;
            }
        }
    </style>

@endsection
@push('scripts')

@endpush

