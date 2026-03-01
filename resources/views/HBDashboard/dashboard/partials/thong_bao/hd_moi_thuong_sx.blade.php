@if(!isset($_GET['ajax-load']))
    <script>
        $(document).ready(function () {
            $.ajax({
                url: '/admin/dashboard/ajax/load-khoi?ajax-load=true&file=HBDashboard.dashboard.partials.thong_bao.hd_moi_thuong_sx',
                type: 'GET',
                data: {},
                success: function (html) {
                    $('#hd_moi_thuong_sx').html(html);
                },
                error: function () {
                    console.log('lỗi load khối HBDashboard/partials/thong_bao/hd_moi_thuong_sx');
                }
            });
        });
    </script>

    <div class="custom-card p-4 mb-4">
        <h3 class="card-title">
            <i class="bi bi-award text-success me-2"></i>Vinh Danh Team Sản Xuất
        </h3>
        <div class="table-responsive">
            <div id="hd_moi_thuong_sx" class="loading-container">
                <div class="d-flex justify-content-center p-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

@else

        <?php
        $bills = \App\Modules\HBDashboard\Models\Bill::leftJoin('bill_progress', 'bill_progress.bill_id', '=', 'bills.id')
            ->select('bills.id', 'bills.domain', 'bills.total_price', 'bills.service_id', 'bills.saler_id', 'bills.registration_date', 'bills.customer_id',
                'bill_progress.dh_id', 'bill_progress.kt_id')
            ->whereRaw("registration_date >= '" . date('Y-m-01 00:00:00', strtotime('-1 months')) . "'")
            ->whereIn('bills.service_id', [
                1,  //  ldp
                5,  // wp
                10, // wp
                11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21
            ])
            ->orderBy('registration_date', 'desc')->get();
        ?>

    <div class="production-dashboard">
        <div class="contracts-section">
            <div class="section-header">
                <h4 class="section-title">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Hợp đồng gần đây
                </h4>
                <div class="section-stats">
                    <span class="stats-label">Tổng:</span>
                    <span class="stats-value">{{ count($bills) }} HĐ</span>
                </div>
            </div>

            <div class="table-container">
                <table class="production-table">
                    <thead>
                    <tr>
                        <th class="customer-col">Khách hàng</th>
                        <th class="service-col d-none d-lg-table-cell">Dịch vụ</th>
                        <th class="revenue-col">Doanh số</th>
                        <th class="date-col d-none d-md-table-cell">Ngày ký</th>
                        <th class="domain-col d-none d-xl-table-cell">Tên miền</th>
                        <th class="sale-col d-none d-lg-table-cell">Sale</th>
                        <th class="team-col">Đội cũ</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($bills) > 0)
                        @foreach($bills as $bill)
                            <tr class="contract-row">
                                <td class="customer-cell">
                                    <div class="customer-info">
                                        <a href="/admin/user/edit/{{ $bill->customer_id }}" target="_blank" class="customer-name">
                                            {{ @$bill->customer->name }}
                                        </a>
                                        <div class="mobile-details d-lg-none">
                                            <div class="service-mobile">{{ @$bill->service->name_vi }}</div>
                                            <div class="sale-mobile d-lg-none">Sale: {{ @$bill->saler->name }}</div>
                                        </div>
                                        <div class="date-mobile d-md-none">{{ date('d/m/Y', strtotime($bill->registration_date)) }}</div>
                                        <div class="domain-mobile d-xl-none">
                                            <a href="/admin/bill/edit/{{ $bill->id }}" target="_blank" class="domain-link">
                                                {{ $bill->domain }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="service-cell d-none d-lg-table-cell">
                                    <span class="service-badge">{{ @$bill->service->name_vi }}</span>
                                </td>
                                <td class="revenue-cell">
                                    <div class="revenue-info">
                                        <span class="revenue-amount">{{ number_format($bill->total_price, 0, '.', '.') }}</span>
                                        <span class="currency">VNĐ</span>
                                    </div>
                                </td>
                                <td class="date-cell d-none d-md-table-cell">
                                    <span class="date-text">{{ date('d/m/Y', strtotime($bill->registration_date)) }}</span>
                                </td>
                                <td class="domain-cell d-none d-xl-table-cell">
                                    <a href="/admin/bill/edit/{{ $bill->id }}" target="_blank" class="domain-link">
                                        {{ $bill->domain }}
                                    </a>
                                </td>
                                <td class="sale-cell d-none d-lg-table-cell">
                                    <span class="sale-name">{{ @$bill->saler->name }}</span>
                                </td>
                                <td class="team-cell">
                                        <?php
                                        $hd_gan_day = \App\Modules\HBDashboard\Models\Bill::leftJoin('bill_progress', 'bill_progress.bill_id', '=', 'bills.id')
                                            ->select('bills.id', 'bills.domain', 'bill_progress.dh_id', 'bill_progress.kt_id')
                                            ->where('bills.customer_id', $bill->customer_id)
                                            ->where('bills.id', '!=', $bill->id)
                                            ->where(function ($query) {
                                                $query->orWhereNotNull('bill_progress.dh_id');
                                                $query->orWhereNotNull('bill_progress.kt_id');
                                            })
                                            ->orderBy('bills.registration_date', 'desc')
                                            ->limit(1)->first();
                                        ?>
                                    @if(is_object($hd_gan_day))
                                        <div class="team-info">
                                            @if($hd_gan_day->dh_id)
                                                <div class="team-member dh">
                                                    <span class="role-label">ĐH:</span>
                                                    <span class="member-name">{{ @\App\Models\Admin::find($hd_gan_day->dh_id)->name }}</span>
                                                </div>
                                            @endif
                                            @if($hd_gan_day->kt_id)
                                                <div class="team-member kt">
                                                    <span class="role-label">KT:</span>
                                                    <span class="member-name">{{ @\App\Models\Admin::find($hd_gan_day->kt_id)->name }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="no-team">
                                            <span class="text-muted">Chưa có</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="no-data">
                                <div class="no-data-content">
                                    <i class="bi bi-inbox text-muted"></i>
                                    <p class="text-muted mb-0">Chưa có hợp đồng nào trong khoảng thời gian này</p>
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
            Truy vấn HĐ từ ngày 1 tháng trước - Cập nhật: {{ date('d/m/Y H:i') }}
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

        .production-dashboard {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .contracts-section {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .section-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        }

        .section-stats {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .stats-value {
            font-size: 1.1rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .production-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .production-table thead th {
            background: #f0fdf4;
            color: #166534;
            font-weight: 600;
            padding: 1rem 0.75rem;
            text-align: left;
            border-bottom: 2px solid #dcfce7;
            white-space: nowrap;
            font-size: 0.9rem;
        }

        .customer-col { min-width: 180px; }
        .service-col { min-width: 120px; }
        .revenue-col { min-width: 100px; }
        .date-col { width: 100px; }
        .domain-col { min-width: 120px; }
        .sale-col { min-width: 100px; }
        .team-col { min-width: 140px; }

        .contract-row {
            transition: all 0.2s ease;
        }

        .contract-row:hover {
            background: #f0fdf4;
        }

        .contract-row td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        .customer-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .customer-name {
            font-weight: 600;
            color: #1e293b;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .customer-name:hover {
            color: #059669;
        }

        .mobile-details {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .service-mobile, .sale-mobile, .date-mobile {
            font-size: 0.8rem;
            color: #64748b;
        }

        .domain-mobile {
            margin-top: 0.25rem;
        }

        .service-badge {
            background: #e0f2fe;
            color: #0277bd;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .revenue-info {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .revenue-amount {
            font-weight: 700;
            font-size: 0.95rem;
            color: #059669;
        }

        .currency {
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 400;
        }

        .date-text {
            font-size: 0.9rem;
            color: #475569;
        }

        .domain-link {
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .domain-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .sale-name {
            font-size: 0.9rem;
            color: #475569;
            font-weight: 500;
        }

        .team-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .team-member {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .role-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            min-width: 25px;
        }

        .member-name {
            font-size: 0.8rem;
            color: #374151;
            font-weight: 500;
        }

        .team-member.dh .role-label {
            color: #dc2626;
        }

        .team-member.kt .role-label {
            color: #2563eb;
        }

        .no-team {
            display: flex;
            align-items: center;
            justify-content: center;
            font-style: italic;
            font-size: 0.8rem;
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
                gap: 0.5rem;
            }
        }

        @media (max-width: 768px) {
            .section-title {
                font-size: 1rem;
            }

            .stats-value {
                font-size: 1rem;
            }

            .production-table thead th {
                padding: 0.75rem 0.5rem;
                font-size: 0.85rem;
            }

            .contract-row td {
                padding: 0.75rem 0.5rem;
            }

            .customer-col { min-width: 150px; }
            .revenue-col { min-width: 80px; }
            .team-col { min-width: 100px; }
        }

        @media (max-width: 480px) {
            .section-header {
                padding: 0.875rem 1rem;
            }

            .production-table thead th,
            .contract-row td {
                padding: 0.625rem 0.375rem;
            }

            .customer-col { min-width: 130px; }
            .revenue-col { min-width: 70px; }
            .team-col { min-width: 90px; }

            .no-data {
                padding: 2rem 0.5rem !important;
            }

            .no-data-content i {
                font-size: 1.5rem;
            }
        }
    </style>

@endif