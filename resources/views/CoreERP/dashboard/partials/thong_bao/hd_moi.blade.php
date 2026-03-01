@if(!isset($_GET['ajax-load']))
    <script>
        $(document).ready(function () {
            $.ajax({
                url: '/admin/dashboard/ajax/load-khoi?ajax-load=true&file=CoreERP.dashboard.partials.thong_bao.hd_moi',
                type: 'GET',
                data: {},
                success: function (html) {
                    $('#hd_moi').html(html);
                },
                error: function () {
                    console.log('lỗi load khối partials/thong_bao/hd_moi');
                }
            });
        });
    </script>

    <div class="custom-card p-4 mb-4">
        <h3 class="card-title">
            <i class="bi bi-file-earmark-plus text-info me-2"></i>Hợp Đồng Mới 
        </h3>
        <div class="table-responsive">
            <div id="hd_moi" class="loading-container">
                <div class="d-flex justify-content-center p-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

@else

        <?php
        $bills = \App\Modules\HBBill\Models\Bill::leftJoin('bill_progress', 'bill_progress.bill_id', '=', 'bills.id')
            ->select('bills.id', 'bills.domain', 'bills.total_price', 'bills.service_id', 'bills.saler_id', 'bills.registration_date', 'bills.customer_id',
                'bill_progress.dh_id', 'bill_progress.kt_id')
            ->whereRaw("registration_date >= '" . date('Y-m-01 00:00:00', strtotime('-1 months')) . "'")
            ->orderBy('registration_date', 'desc')
            ->limit(10)
            ->get();

        $total_revenue = $bills->sum('total_price');
        ?>

    <div class="contracts-dashboard">
        <div class="new-contracts-section">
            <div class="section-header">
                <h4 class="section-title">
                    <i class="bi bi-calendar-plus me-2"></i>
                    Hợp đồng gần đây
                    <span class="time-badge">Tháng {{ date('m') }}</span>
                </h4>

            </div>

            <div class="table-container">
                <table class="contracts-table">
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
                        @foreach($bills as $index => $bill)
                            <tr class="contract-row">
                                <td class="customer-cell">
                                    <div class="customer-info">
                                        <div class="customer-details">
                                                <?php
                                                // ƯU TIÊN 1: Dùng cách cũ (Relationship) - Vì cái này đang hiển thị tốt cho các khách cũ (Nguyễn Thành Long, v.v.)
                                                $hien_thi_ten = $bill->customer->name ?? null;

                                                // ƯU TIÊN 2: Nếu cách 1 bị lỗi (ra NULL/Rỗng) -> Dùng cách Query trực tiếp (Để sửa lỗi cho khách mới Hoàng Hiệu)
                                                if (empty($hien_thi_ten)) {
                                                    $hien_thi_ten = \Illuminate\Support\Facades\DB::table('users')
                                                        ->where('id', $bill->customer_id)
                                                        ->value('name');
                                                }

                                                // ƯU TIÊN 3: (Dự phòng) Nếu vẫn không có, thử lấy tên lưu cứng trong bảng bills (nếu code cũ có lưu)
                                                if (empty($hien_thi_ten)) {
                                                    $hien_thi_ten = $bill->user_name ?? null;
                                                }
                                                ?>

                                            <a href="/admin/user/edit/{{ $bill->customer_id }}" target="_blank" class="customer-name">
                                                @if(!empty($hien_thi_ten))
                                                    {{ $hien_thi_ten }}
                                                @else
                                                    {{-- Chỉ khi nào cả 3 cách trên đều thua thì mới hiện ID --}}
                                                    <span class="text-danger">Review ID: {{ $bill->customer_id }}</span>
                                                @endif
                                            </a>

                                            <div class="mobile-details d-lg-none">
                                                <div class="service-mobile">{{ @$bill->service->name_vi }}</div>
                                                <div class="sale-mobile d-lg-none">Sale: {{ @$bill->saler->name }}</div>
                                            </div>
                                            <div class="date-mobile d-md-none">
                                                <i class="bi bi-calendar3 me-1"></i>{{ date('d/m/Y', strtotime($bill->registration_date)) }}
                                            </div>
                                            <div class="domain-mobile d-xl-none">
                                                <a href="/admin/bill/edit/{{ $bill->id }}" target="_blank" class="domain-link">
                                                    <i class="bi bi-globe me-1"></i>{{ $bill->domain }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="service-cell d-none d-lg-table-cell">
                                    <span class="service-badge new">{{ @$bill->service->name_vi }}</span>
                                </td>
                                <td class="revenue-cell">
                                    <div class="revenue-info">
                                        <span class="revenue-amount">{{ number_format($bill->total_price, 0, '.', '.') }}</span>
                                        <span class="currency">VNĐ</span>
                                    </div>
                                </td>
                                <td class="date-cell d-none d-md-table-cell">
                                    <div class="date-info">
                                        <span class="date-text">{{ date('d/m/Y', strtotime($bill->registration_date)) }}</span>
                                        <span class="time-ago">{{ \Carbon\Carbon::parse($bill->registration_date)->diffForHumans() }}</span>
                                    </div>
                                </td>
                                <td class="domain-cell d-none d-xl-table-cell">
                                    <a href="/admin/bill/edit/{{ $bill->id }}" target="_blank" class="domain-link">
                                        <i class="bi bi-globe me-1"></i>{{ $bill->domain }}
                                    </a>
                                </td>
                                <td class="sale-cell d-none d-lg-table-cell">
                                    <div class="sale-info">
                                        <span class="sale-name">{{ @$bill->saler->name }}</span>
                                        <span class="sale-code">{{ @$bill->saler->code }}</span>
                                    </div>
                                </td>
                                <td class="team-cell">
                                        <?php
                                        $hd_gan_day = \App\Modules\HBBill\Models\Bill::leftJoin('bill_progress', 'bill_progress.bill_id', '=', 'bills.id')
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
                                            <i class="bi bi-dash-circle text-muted"></i>
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
                                    <p class="text-muted mb-0">Chưa có hợp đồng mới nào trong khoảng thời gian này</p>
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
            Truy vấn 10 HĐ gần nhất từ tháng trước - Cập nhật: {{ date('d/m/Y H:i') }}
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

        .contracts-dashboard {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .new-contracts-section {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .section-header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
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

        .contracts-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .contracts-table thead th {
            background: #ecfeff;
            color: #0e7490;
            font-weight: 600;
            padding: 1rem 0.75rem;
            text-align: left;
            border-bottom: 2px solid #cffafe;
            white-space: nowrap;
            font-size: 0.9rem;
        }

        .customer-col { min-width: 200px; }
        .service-col { min-width: 120px; }
        .revenue-col { min-width: 100px; }
        .date-col { min-width: 120px; }
        .domain-col { min-width: 140px; }
        .sale-col { min-width: 100px; }
        .team-col { min-width: 140px; }

        .contract-row {
            transition: all 0.2s ease;
        }

        .contract-row:hover {
            background: #ecfeff;
        }

        .contract-row.recent-highlight {
            background: linear-gradient(90deg, #cffafe, transparent);
            border-left: 3px solid #06b6d4;
        }

        .contract-row td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        .customer-info {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .new-indicator {
            color: #f59e0b;
            font-size: 0.8rem;
            margin-top: 0.125rem;
        }

        .customer-details {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            flex: 1;
        }

        .customer-name {
            font-weight: 600;
            color: #1e293b;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .customer-name:hover {
            color: #0891b2;
        }

        .mobile-details {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .service-mobile, .sale-mobile, .date-mobile, .domain-mobile {
            font-size: 0.8rem;
            color: #64748b;
        }

        .service-badge {
            background: #e0f2fe;
            color: #0277bd;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .service-badge.new {
            background: #cffafe;
            color: #0e7490;
        }

        .revenue-info {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .revenue-amount {
            font-weight: 700;
            font-size: 0.95rem;
            color: #0891b2;
        }

        .currency {
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 400;
        }

        .date-info {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .date-text {
            font-size: 0.9rem;
            color: #475569;
            font-weight: 500;
        }

        .time-ago {
            font-size: 0.75rem;
            color: #64748b;
            font-style: italic;
        }

        .domain-link {
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
            display: flex;
            align-items: center;
        }

        .domain-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .sale-info {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .sale-name {
            font-size: 0.9rem;
            color: #475569;
            font-weight: 500;
        }

        .sale-code {
            font-size: 0.75rem;
            color: #64748b;
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
            gap: 0.25rem;
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
            }

            .contracts-table thead th {
                padding: 0.75rem 0.5rem;
                font-size: 0.85rem;
            }

            .contract-row td {
                padding: 0.75rem 0.5rem;
            }

            .customer-col { min-width: 160px; }
            .revenue-col { min-width: 80px; }
            .team-col { min-width: 100px; }
        }

        @media (max-width: 480px) {
            .section-header {
                padding: 0.875rem 1rem;
            }

            .contracts-table thead th,
            .contract-row td {
                padding: 0.625rem 0.375rem;
            }

            .customer-col { min-width: 140px; }
            .revenue-col { min-width: 70px; }
            .team-col { min-width: 90px; }

            .section-stats {
                flex-direction: column;
                gap: 0.5rem;
                align-items: center;
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