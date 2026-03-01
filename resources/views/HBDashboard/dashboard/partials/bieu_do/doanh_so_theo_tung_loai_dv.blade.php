<div class="custom-card p-4 mb-4" style="box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-radius: 12px; background: #fff;">
    <div class="kt-portlet kt-portlet--height-fluid kt-portlet--mobile ">
        <div class="kt-portlet__head kt-portlet__head--lg kt-portlet__head--noborder kt-portlet__head--break-sm">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title bold uppercase">
                    DS theo loại DV
                </h3>
            </div>
        </div>
        <div class="kt-portlet__body kt-portlet__body--fit">

            <!--end: Datatable -->
            <canvas id="doanh_so_theo_tung_loai_dich_vu"></canvas>
        </div>
    </div>
</div>
<script>

    <?php


    //  thống kê ký mới HBWEB
    $char_hbweb_ky_moi = \App\Modules\HBDashboard\Models\Bill::selectRaw('Sum(total_price) as total_price, COUNT(id) as total_bill, MONTH(registration_date) as month')
    ->whereNotIn('service_id', [
        3, // tên miền
        4, // mail
        6,  // khác
        7,  // duy trì wp
        8,  //  nâng cấp hosting
        22, // thiết kế ảnh
        23, //  web khác
        24, //  nâng cấp web khác
        25, //  duy trì web khác

    ]);
    if (isset($_GET['start_date']) && $_GET['start_date'] != '') {
        $char_hbweb_ky_moi = $char_hbweb_ky_moi->where('registration_date', '>=', date('Y-m-d 00:00:00', strtotime($_GET['start_date'])));
    } else {
        $char_hbweb_ky_moi = $char_hbweb_ky_moi->where('registration_date', '>=', date('Y-m-d 00:00:00', strtotime('-11 month')));
    }
    if (isset($_GET['end_date']) && $_GET['end_date'] != '') {
        $char_hbweb_ky_moi = $char_hbweb_ky_moi->where('registration_date', '<=', date('Y-m-d 23:59:00', strtotime($_GET['end_date'])));
    } else {
        $char_hbweb_ky_moi = $char_hbweb_ky_moi->where('registration_date', '<=', date('Y-m-d 23:59:00'));
    }

    $char_hbweb_ky_moi = $char_hbweb_ky_moi->groupBy(\DB::raw('MONTH(registration_date)'))->orderBy('registration_date', 'asc')->get();


    //  thống kê ký mới Hobasoft
    $char_hobasoft_ky_moi = \App\Modules\HBDashboard\Models\Bill::selectRaw('Sum(total_price) as total_price, COUNT(id) as total_bill, MONTH(registration_date) as month')
        ->whereIn('service_id', [
            23, //  web khác
            24,
            25,
        ]);
    if (isset($_GET['start_date']) && $_GET['start_date'] != '') {
        $char_hobasoft_ky_moi = $char_hobasoft_ky_moi->where('registration_date', '>=', date('Y-m-d 00:00:00', strtotime($_GET['start_date'])));
    } else {
        $char_hobasoft_ky_moi = $char_hobasoft_ky_moi->where('registration_date', '>=', date('Y-m-d 00:00:00', strtotime('-11 month')));
    }
    if (isset($_GET['end_date']) && $_GET['end_date'] != '') {
        $char_hobasoft_ky_moi = $char_hobasoft_ky_moi->where('registration_date', '<=', date('Y-m-d 23:59:00', strtotime($_GET['end_date'])));
    } else {
        $char_hobasoft_ky_moi = $char_hobasoft_ky_moi->where('registration_date', '<=', date('Y-m-d 23:59:00'));
    }

    $char_hobasoft_ky_moi = $char_hobasoft_ky_moi->groupBy(\DB::raw('MONTH(registration_date)'))->orderBy('registration_date', 'asc')->get();
    foreach ($char_hobasoft_ky_moi as $v) {
        $char_hobasoft[$v->month] = $v;
    }



    ?>
    var lineChartData = {
        labels: [
            @foreach($char_hbweb_ky_moi as $item)
                'T{{ $item->month }}',
            @endforeach
        ],
        datasets: [{
            label: 'DS HBweb ký mới',
            borderColor: window.chartColors.blue,
            backgroundColor: window.chartColors.blue,
            fill: false,
            data: [
                //    dữ liệu của số lượng Hợp đồng
                @foreach($char_hbweb_ky_moi as $item)
                    '{{ round($item->total_price/1000000) }}',
                @endforeach
            ],
            yAxisID: 'y-axis-1',
        }, {
            label: 'DS Hobasoft ký mới',
            borderColor: window.chartColors.red,
            backgroundColor: window.chartColors.red,
            fill: false,
            data: [
                @foreach($char_hbweb_ky_moi as $item)
                    @if(isset($char_hobasoft[$item->month]))
                        '{{ round($char_hobasoft[$item->month]->total_price/1000000) }}',
                    @else
                        '0',
                    @endif
                @endforeach
            ],
            yAxisID: 'y-axis-2'
        }/*, {
            label: 'Số khách mới',
            borderColor: window.chartColors.black,
            backgroundColor: window.chartColors.black,
            fill: false,
            data: [
                @foreach($char_hobasoft_ky_moi as $item)
                    '{{ round($item->total_price/1000000) }}',
                @endforeach
            ],
            yAxisID: 'y-axis-3'
        }*/
        ]
    };


    var ctx = document.getElementById('doanh_so_theo_tung_loai_dich_vu').getContext('2d');

    window.myLine = Chart.Line(ctx, {
        data: lineChartData,
        options: {
            responsive: true,
            hoverMode: 'index',
            stacked: false,
            title: {
                display: true,
                // text: 'Chart.js Line Chart - Multi Axis'
            },
            scales: {
                yAxes: [{
                    type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: true,
                    position: 'left',
                    id: 'y-axis-1',
                    ticks: {
                        beginAtZero: true,
                    }

                }, {
                    type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: true,
                    position: 'right',
                    id: 'y-axis-2',
                    ticks: {
                        beginAtZero: true,
                    },
                    // grid line settings
                    gridLines: {
                        drawOnChartArea: false, // only want the grid lines for one axis to show up
                    },
                }, /*{
                    type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: true,
                    position: 'left',
                    id: 'y-axis-3',
                    ticks: {
                        beginAtZero: true,
                    },
                    // grid line settings
                    gridLines: {
                        drawOnChartArea: false, // only want the grid lines for one axis to show up
                    },
                }*/],
            }
        }
    });

</script>