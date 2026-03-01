@if(!isset($_GET['ajax-load']))
    <script>
        $(document).ready(function () {
            $.ajax({
                url: '/admin/dashboard/ajax/load-khoi?ajax-load=true&file=CoreERP.dashboard.partials.xep_hang.xep_hang_dich_vu',
                type: 'GET',
                data: {},
                success: function (html) {
                    $('#xep_hang_dich_vu').html(html);
                },
                error: function () {
                    console.log('lỗi load khối partials/xep_hang/xep_hang_dich_vu');
                }
            });
        });
    </script>

    <div class="custom-card p-4 mb-4">
        <h3 class="card-title">
            <i class="bi bi-bar-chart text-warning me-2"></i>Top Dịch Vụ
        </h3>
        <div class="table-responsive">
            @if($doanh_so > 0)
                <div id="xep_hang_dich_vu" class="loading-container">
                    <div class="d-flex justify-content-center p-4">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="no-data-state">
                    <div class="no-data-content">
                        <i class="bi bi-pie-chart text-muted"></i>
                        <p class="text-muted mb-0">Chưa có dữ liệu doanh số trong khoảng thời gian này</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

@else
        <?php
        //  Mặc định lấy ngày đầu tháng
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01 00:00:00');
        //  Mặc định lấy ngày hôm nay
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d 23:59:00');

        $where = "created_at >= '" . $start_date . " 00:00:00' AND created_at <= '" . $end_date . " 23:59:59'";
        $whereRegistration = "registration_date >= '" . $start_date . " 00:00:00' AND registration_date <= '" . $end_date . " 23:59:59'";
        $whereCreated_at = "created_at >= '" . $start_date . " 00:00:00' AND created_at <= '" . $end_date . " 23:59:59'";
        $whereDate = "date >= '" . $start_date . " 00:00:00' AND date <= '" . $end_date . " 23:59:59'";

        if (isset($_GET['admin_id']) && $_GET['admin_id'] != '') {
            $where .= " AND admin_id = " . $_GET['admin_id'];
            $whereRegistration .= " AND admin_id = " . $_GET['admin_id'];
        }

        $doanh_so = \App\Modules\HBBill\Models\Bill::whereRaw($whereRegistration)->sum('total_price');
        $total_contracts = \App\Modules\HBBill\Models\Bill::whereRaw($whereRegistration)->count();
        $service_ids = \App\Modules\HBBill\Models\Bill::whereRaw($whereRegistration)->groupBy('service_id')->pluck('service_id')->toArray();
        $services = \App\Modules\HBBill\Models\Service::select('id', 'name_vi')->whereIn('id', $service_ids)->get();

        //  Sắp xếp dịch vụ nào nhiều doanh số nhất lên đầu
        $service_arr = [];
        foreach ($services as $service) {
            $service->ds_dv = @\App\Modules\HBBill\Models\Bill::whereRaw($whereRegistration)->where('service_id', $service->id)->where('status', 1)->sum('total_price');
            $service->so_hd = @\App\Modules\HBBill\Models\Bill::whereRaw($whereRegistration)->where('service_id', $service->id)->count();
            if($service->ds_dv > 0) {
                $service_arr[$service->ds_dv] = $service;
            }
        }
        krsort($service_arr);
        ?>

    <div class="services-dashboard">
        <div class="services-section">
            <div class="section-header">
                <h4 class="section-title">
                    <i class="bi bi-graph-up me-2"></i>
                    Xếp hạng dịch vụ
                    <span class="time-badge">{{ date('d/m', strtotime($start_date)) }} - {{ date('d/m', strtotime($end_date)) }}</span>
                </h4>

            </div>

            <div class="table-container">
                <table class="services-table">
                    <thead>
                    <tr>
                        <th class="rank-col">#</th>
                        <th class="service-col">Dịch vụ</th>
                        <th class="contracts-col d-none d-md-table-cell">Số HĐ</th>
                        <th class="revenue-col">Doanh số</th>
                        <th class="percent-col">Tỷ lệ</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($service_arr) > 0)
                        @php $rank = 1; @endphp
                        @foreach($service_arr as $service)
                            <tr class="service-row {{ $rank <= 3 ? 'top-performer' : '' }}">
                                <td class="rank-cell">
                                    @if($rank == 1)
                                        <i class="bi bi-trophy-fill text-warning"></i>
                                    @elseif($rank == 2)
                                        <i class="bi bi-award-fill text-secondary"></i>
                                    @elseif($rank == 3)
                                        <i class="bi bi-gem text-success"></i>
                                    @else
                                        <span class="rank-number">{{ $rank }}</span>
                                    @endif
                                </td>
                                <td class="service-cell">
                                    <div class="service-info">
                                        <div class="service-name">{{ $service->name_vi }}</div>
                                        <div class="contracts-mobile d-md-none">
                                            <i class="bi bi-file-earmark me-1"></i>{{ number_format($service->so_hd) }} hợp đồng
                                        </div>
                                    </div>
                                </td>
                                <td class="contracts-cell d-none d-md-table-cell">
                                    <div class="contracts-info">
                                        <span class="contracts-number">{{ number_format($service->so_hd) }}</span>
                                        <span class="contracts-label">HĐ</span>
                                    </div>
                                </td>
                                <td class="revenue-cell">
                                    <div class="revenue-info">
                                        <span class="revenue-amount">{{ number_format($service->ds_dv, 0, '.', '.') }}</span>
                                        <span class="currency">VNĐ</span>
                                    </div>
                                </td>
                                <td class="percent-cell">
                                    <div class="percent-container">
                                        <div class="percent-text">{{ round(($service->ds_dv / $doanh_so)*100, 1) }}%</div>
                                        <div class="percent-bar">
                                            <div class="percent-fill" style="width: {{ round(($service->ds_dv / $doanh_so)*100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @php $rank++; @endphp
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="no-data">
                                <div class="no-data-content">
                                    <i class="bi bi-pie-chart text-muted"></i>
                                    <p class="text-muted mb-0">Chưa có dữ liệu dịch vụ trong khoảng thời gian này</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="note-section mt-3">
        <small class="text-muted">
            <i class="bi bi-info-circle me-1"></i>
            Truy vấn theo bộ lọc thời gian - Cập nhật: {{ date('d/m/Y H:i') }}
        </small>
    </div>

    <style>
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .no-data-state {
            padding: 3rem 1rem;
            text-align: center;
        }

        .no-data-state .no-data-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .no-data-state .no-data-content i {
            font-size: 2rem;
        }

        .services-dashboard {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .services-section {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .section-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            flex: 1;
            gap: 0.5rem;
        }

        .time-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }

        .stats-label {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .stats-value {
            font-size: 1rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .services-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .services-table thead th {
            background: #fef3c7;
            color: #92400e;
            font-weight: 600;
            padding: 1rem 0.75rem;
            text-align: left;
            border-bottom: 2px solid #fde68a;
            white-space: nowrap;
            font-size: 0.9rem;
        }

        .rank-col { width: 60px; text-align: center; }
        .service-col { min-width: 180px; }
        .contracts-col { width: 100px; text-align: center; }
        .revenue-col { min-width: 120px; }
        .percent-col { min-width: 120px; }

        .service-row {
            transition: all 0.2s ease;
        }

        .service-row:hover {
            background: #fef3c7;
        }

        .service-row.top-performer {
            background: linear-gradient(90deg, #fde68a, transparent);
        }

        .service-row td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .rank-cell {
            text-align: center;
            font-size: 1.1rem;
        }

        .rank-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: #fde68a;
            border-radius: 50%;
            font-size: 0.8rem;
            font-weight: 600;
            color: #92400e;
        }

        .service-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .service-name {
            font-weight: 600;
            color: #1e293b;
        }

        .contracts-mobile {
            font-size: 0.8rem;
            color: #64748b;
        }

        .contracts-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.125rem;
        }

        .contracts-number {
            font-weight: 600;
            color: #374151;
            font-size: 1rem;
        }

        .contracts-label {
            font-size: 0.75rem;
            color: #64748b;
        }

        .revenue-info {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .revenue-amount {
            font-weight: 700;
            font-size: 0.95rem;
            color: #d97706;
        }

        .currency {
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 400;
        }

        .percent-container {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
            min-width: 80px;
        }

        .percent-text {
            font-weight: 700;
            color: #92400e;
            text-align: center;
            font-size: 0.9rem;
        }

        .percent-bar {
            width: 100%;
            height: 6px;
            background: #f3f4f6;
            border-radius: 3px;
            overflow: hidden;
        }

        .percent-fill {
            height: 100%;
            background: linear-gradient(90deg, #f59e0b, #d97706);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .no-data {
            padding: 3rem 1rem !important;
            text-align: center;
        }

        .no-data-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .no-data-content i {
            font-size: 2rem;
        }

        .note-section {
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }

        .loading-container .spinner-border {
            width: 2rem;
            height: 2rem;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .section-stats {
                gap: 0.75rem;
            }
        }

        @media (max-width: 768px) {
            .section-title {
                font-size: 1rem;
            }

            .section-stats {
                flex-direction: row;
                justify-content: space-around;
                width: 100%;
                gap: 0.5rem;
            }

            .services-table thead th {
                padding: 0.75rem 0.5rem;
                font-size: 0.85rem;
            }

            .service-row td {
                padding: 0.75rem 0.5rem;
            }

            .service-col { min-width: 150px; }
            .revenue-col { min-width: 100px; }
            .percent-col { min-width: 100px; }
        }

        @media (max-width: 480px) {
            .section-header {
                padding: 0.875rem 1rem;
            }

            .services-table thead th,
            .service-row td {
                padding: 0.625rem 0.375rem;
            }

            .service-col { min-width: 130px; }
            .revenue-col { min-width: 80px; }
            .percent-col { min-width: 80px; }

            .section-stats {
                flex-direction: column;
                gap: 0.5rem;
                align-items: center;
            }

            .percent-container {
                min-width: 60px;
            }

            .no-data {
                padding: 2rem 0.5rem !important;
            }

            .no-data-content i {
                font-size: 1.5rem;
            }
        }
    </style>

@endif