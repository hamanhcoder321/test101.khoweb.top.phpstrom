@if(!isset($_GET['ajax-load']))
    <script>
        $(document).ready(function () {
            $.ajax({
                url: '/admin/dashboard/ajax/load-khoi?ajax-load=true&file=CoreERP.dashboard.partials.xep_hang.top_sale_3_thang',
                type: 'GET',
                data: {},
                success: function (html) {
                    $('#top_sale_3_thang').html(html);
                },
                error: function () {
                    console.log('lỗi load khối partials/xep_hang/top_sale_3_thang');
                }
            });
        });
    </script>

    <div class="custom-card mb-4 p-4">
        <div class="section-header" style="background: none !important; padding: 0 !important;">
            <h3 class="section-title">
                <i class="bi bi-trophy text-warning me-2"></i>
                Top Sale
            </h3>
        </div>
        <div class="card-body">
            <div id="top_sale_3_thang" class="loading-container" style="padding: 0 !important">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <span>Đang tải dữ liệu...</span>
                </div>
            </div>
        </div>
    </div>

@else

        <?php
        $saler_ids = \App\Models\RoleAdmin::whereIn('role_id', [
            2, // quyền sale
            182, // quyền trưởng phòng KD
            186, // giám đốc kinh doanh
        ])->pluck('admin_id')->toArray();

        $months_data = [];

// Lấy dữ liệu 3 tháng
        for ($i = 0; $i >= -2; $i--) {
            $month_start = date('Y-m-01 00:00:00', strtotime($i . ' months'));
            $month_end = date('Y-m-t 23:59:00', strtotime($i . ' months'));
            $month_name = date('n', strtotime($i . ' months')); // 1–12 (không số 0 đầu)

            $best_sales = \App\Modules\HBBill\Models\Bill::selectRaw('Sum(total_price) as total_price, saler_id')
                ->whereRaw("registration_date >= '$month_start' AND registration_date <= '$month_end'")
                ->whereNotIn('saler_id', [170])
                ->whereIn('saler_id', $saler_ids)
                ->groupBy('saler_id')
                ->orderBy('total_price', 'desc')
                ->get();

            $tong_doanh_so = 0;
            $ds_phong = [];

            foreach ($best_sales as $sale) {
                $tong_doanh_so += $sale->total_price;
                if (!isset($ds_phong[$sale->saler->room_id])) {
                    $ds_phong[$sale->saler->room_id] = $sale->total_price;
                } else {
                    $ds_phong[$sale->saler->room_id] += $sale->total_price;
                }
            }
            arsort($ds_phong);

            $months_data[] = [
                'month_name' => $month_name,
                'sales' => $best_sales,
                'total' => $tong_doanh_so,
                'departments' => $ds_phong,
                'is_current' => $i == 0
            ];
        }
        ?>

    <div class="sales-dashboard-container">
        <!-- Tab Navigation -->
        <div class="tab-navigation">
            @foreach($months_data as $index => $month_data)
                <button class="tab-btn {{ $index == 0 ? 'active' : '' }}"
                        data-tab="{{ $index }}"
                        onclick="switchTab({{ $index }})">
                    <div class="tab-content">
                        <span class="tab-month">Tháng {{ $month_data['month_name'] }}</span>
                        @if($month_data['is_current'])
                            <span class="current-indicator"></span>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>

        <!-- Tab Content -->
        @foreach($months_data as $index => $month_data)
            <div class="tab-content-panel {{ $index == 0 ? 'active' : '' }}" id="tab-{{ $index }}">
                <!-- Summary Stats -->
                <div class="summary-stats">
                    <div class="stat-item">
                        <div class="stat-label">Tổng doanh số</div>
                        <div class="stat-value">{{ number_format($month_data['total'], 0, '.', '.') }} VNĐ</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Số nhân viên</div>
                        <div class="stat-value">{{ count($month_data['sales']) }}</div>
                    </div>
                </div>

                <!-- Sales Ranking Table -->
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead>
                        <tr>
                            <th class="rank-col">#</th>
                            <th class="name-col">Nhân viên</th>
                            <th class="code-col d-none d-md-table-cell">Mã NV</th>
                            <th class="dept-col d-none d-lg-table-cell">Phòng ban</th>
                            <th class="revenue-col">Doanh số</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $rank = 1; @endphp
                        @foreach($month_data['sales'] as $sale)
                            <tr class="table-row {{ $rank <= 3 ? 'top-performer' : '' }}">
                                <td class="">
                                    <div class="">{{ $rank }}</div>
                                </td>
                                <td class="name-cell">
                                    <div class="employee-info">
                                        <a href="/admin/bill?search=true&saler_id={{ $sale->saler_id }}&from_date={{ date('Y-m-01', strtotime($month_data['month_name'])) }}&registration_date=1"
                                           target="_blank" class="employee-name">
                                            {{ @$sale->saler->name }}
                                        </a>
                                        <div class="mobile-info d-md-none">
                                            <span class="mobile-code">{{ @$sale->saler->code }}</span>
                                            <span class="mobile-dept">{{ @$sale->phong_ban->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="code-cell d-none d-md-table-cell">
                                    <span class="employee-code">{{ @$sale->saler->code }}</span>
                                </td>
                                <td class="dept-cell d-none d-lg-table-cell">
                                    <span class="department">{{ @$sale->phong_ban->name }}</span>
                                </td>
                                <td class="revenue-cell">
                                    <div class="revenue-display">
                                            <span class="revenue-amount {{ ($month_data['is_current'] && date('d')/30 > $sale->total_price/10000000) || (!$month_data['is_current'] && $sale->total_price < 10000000) ? 'below-target' : '' }}">
                                                {{ number_format($sale->total_price, 0, '.', '.') }}
                                            </span>
                                        <span class="currency">VNĐ</span>
                                    </div>
                                </td>
                            </tr>
                            @php $rank++; @endphp
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Department Summary -->
                @if(count($month_data['departments']) > 0)
                    <div class="department-section">
                        <h4 class="section-title">
                            <i class="bi bi-building me-2"></i>
                            Thống kê theo phòng ban
                        </h4>
                        <div class="department-grid">
                            @foreach($month_data['departments'] as $room_id => $revenue)
                                <div class="dept-card">
                                    <div class="dept-name">{{ @$room_ids[$room_id] }}</div>
                                    <div class="dept-revenue">{{ number_format($revenue, 0, '.', '.') }} VNĐ</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="expense-note">
        <i class="bi bi-info-circle me-2"></i>
        <span>        Dữ liệu được cập nhật theo thời gian thực</span>
    </div>

    <script>
        function switchTab(tabIndex) {
            // Remove active class from all tabs and content panels
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content-panel').forEach(panel => panel.classList.remove('active'));

            // Add active class to selected tab and content panel
            document.querySelector(`[data-tab="${tabIndex}"]`).classList.add('active');
            document.getElementById(`tab-${tabIndex}`).classList.add('active');
        }
    </script>

    <style>
        * {
            box-sizing: border-box;
        }

        .modern-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 2rem;
            border: 1px solid rgba(229, 231, 235, 0.8);
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #f1f5f9;
            background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .card-body {
            padding: 0;
        }
        /* sửa ngày 29/08 */
        /*.loading-container {*/
        /*    padding: 3rem 2rem;*/
        /*}*/

        .loading-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            color: #6b7280;
        }

        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* sửa ngày 29/08 */
        .sales-dashboard-container {
            /*    padding: 2rem;*/
            /*}*/

            /* Tab Navigation */
            .tab-navigation {
                display: flex;
                gap: 0.5rem;
                margin-bottom: 2rem;
                border-bottom: 1px solid #e5e7eb;
                overflow-x: auto;
                padding-bottom: 0;
            }

            .tab-btn {
                background: transparent;
                border: none;
                padding: 1rem 1.5rem;
                border-radius: 12px 12px 0 0;
                font-weight: 500;
                color: #6b7280;
                cursor: pointer;
                transition: all 0.3s ease;
                white-space: nowrap;
                position: relative;
                border-bottom: 3px solid transparent;
            }

            .tab-btn:hover {
                background: #f8fafc;
                color: #374151;
            }

            .tab-btn.active {
                background: #f8fafc;
                color: #1f2937;
                border-bottom-color: #3b82f6;
                font-weight: 600;
            }

            .tab-content {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .current-indicator {
                width: 8px;
                height: 8px;
                background: #10b981;
                border-radius: 50%;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }

            /* Tab Content Panels */
            .tab-content-panel {
                display: none;
                animation: fadeIn 0.3s ease;
            }

            .tab-content-panel.active {
                display: block;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Summary Stats */
            .summary-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            .stat-item {
                background: #f8fafc;
                padding: 1.5rem;
                border-radius: 12px;
                text-align: center;
                border: 1px solid #e2e8f0;
            }

            .stat-label {
                font-size: 0.875rem;
                color: #64748b;
                margin-bottom: 0.5rem;
                font-weight: 500;
            }

            .stat-value {
                font-size: 1.5rem;
                font-weight: 700;
                color: #1e293b;
            }

            /* Table Styles */
            .table-wrapper {
                background: #ffffff;
                border-radius: 12px;
                overflow: hidden;
                border: 1px solid #e2e8f0;
                margin-bottom: 2rem;
            }

            .modern-table {
                width: 100%;
                border-collapse: collapse;
            }

            .modern-table thead th {
                background: #f8fafc;
                padding: 1rem 1.5rem;
                text-align: left;
                font-weight: 600;
                color: #374151;
                font-size: 0.875rem;
                border-bottom: 2px solid #e5e7eb;
                vertical-align: middle;
            }

            .rank-col { width: 60px; }
            .name-col { width: 25%; min-width: 200px; }
            .code-col { width: 120px; text-align: center; }
            .dept-col { width: 20%; min-width: 160px; }
            .revenue-col { width: 18%; min-width: 150px; text-align: right !important; vertical-align: middle; }

            .table-row {
                transition: all 0.2s ease;
                border-bottom: 1px solid #f1f5f9;
            }

            .table-row:hover {
                background: #fafbfc;
            }

            .table-row.top-performer {
                background: linear-gradient(90deg, rgba(59, 130, 246, 0.05), transparent);
            }

            .table-row td {
                padding: 1rem 1.5rem;
                vertical-align: middle;
            }

            .rank-cell {
                /* Removed text-align: center */
            }

            .rank-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 32px;
                height: 32px;
                border-radius: 8px;
                font-size: 0.875rem;
            }

            .rank-badge.gold {
                background: linear-gradient(135deg, #fbbf24, #f59e0b);
                color: white;
            }

            .rank-badge.silver {
                background: linear-gradient(135deg, #e5e7eb, #9ca3af);
                color: white;
            }

            .rank-badge.bronze {
                background: linear-gradient(135deg, #d97706, #92400e);
                color: white;
            }

            .rank-number {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 28px;
                height: 28px;
                background: #e5e7eb;
                color: #6b7280;
                border-radius: 6px;
                font-weight: 600;
                font-size: 0.875rem;
            }

            .employee-info {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .employee-name {
                font-weight: 600;
                color: #1e293b;
                text-decoration: none;
                transition: color 0.2s ease;
            }

            .employee-name:hover {
                color: #3b82f6;
            }

            .mobile-info {
                display: flex;
                flex-direction: column;
                gap: 0.125rem;
            }

            .mobile-code, .mobile-dept {
                font-size: 0.75rem;
                color: #64748b;
            }

            .employee-code {
                display: inline-block;
                background: #dbeafe;
                color: #1e40af;
                padding: 0.25rem 0.5rem;
                border-radius: 6px;
                font-size: 0.75rem;
                font-weight: 500;
            }

            .department {
                color: #4b5563;
                font-size: 0.875rem;
            }

            .revenue-display {
                text-align: right;
                display: flex;
                flex-direction: column;
                gap: 0.125rem;
            }

            .revenue-amount {
                font-weight: 700;
                color: #059669;
                font-size: 1rem;
            }

            .revenue-amount.below-target {
                color: #dc2626;
            }

            .currency {
                font-size: 0.75rem;
                color: #6b7280;
            }

            /* Department Section */
            .department-section {
                margin-top: 2rem;
            }

            .section-title {
                font-size: 1.125rem;
                font-weight: 600;
                color: #374151;
                margin-bottom: 1rem;
                display: flex;
                align-items: center;
            }

            .department-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }

            .dept-card {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 1rem;
                text-align: center;
                transition: all 0.2s ease;
            }

            .dept-card:hover {
                border-color: #d1d5db;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .dept-name {
                font-size: 0.875rem;
                color: #64748b;
                margin-bottom: 0.5rem;
                font-weight: 500;
            }

            .dept-revenue {
                font-size: 1rem;
                font-weight: 700;
                color: #1e293b;
            }

            .footer-note {
                text-align: center;
                padding: 1rem;
                color: #64748b;
                font-size: 0.875rem;
                border-top: 1px solid #f1f5f9;
                margin-top: 1rem;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .modern-card {
                    border-radius: 12px;
                    margin: 1rem;
                }

                .card-header {
                    padding: 1rem 1.5rem;
                }

                .card-title {
                    font-size: 1.125rem;
                }

                .sales-dashboard-container {
                    padding: 1rem;
                }

                .tab-btn {
                    padding: 0.75rem 1rem;
                    font-size: 0.875rem;
                }

                .summary-stats {
                    grid-template-columns: 1fr;
                    gap: 1rem;
                }

                .stat-item {
                    padding: 1rem;
                }

                .stat-value {
                    font-size: 1.25rem;
                }

                .modern-table thead th,
                .table-row td {
                    padding: 0.75rem 1rem;
                }

                .rank-col { width: 50px; }
                .name-col { width: 30%; min-width: 150px; }
                .code-col { width: 100px; }
                .dept-col { width: 25%; min-width: 120px; }
                .revenue-col { width: 20%; min-width: 120px; }

                .department-grid {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 480px) {
                .modern-card {
                    margin: 0.5rem;
                    border-radius: 8px;
                }

                .card-header {
                    padding: 1rem;
                }

                .sales-dashboard-container {
                    padding: 0.75rem;
                }

                .tab-navigation {
                    gap: 0.25rem;
                }

                .tab-btn {
                    padding: 0.5rem 0.75rem;
                }

                .modern-table thead th,
                .table-row td {
                    padding: 0.75rem 0.5rem;
                    font-size: 0.875rem;
                }

                .rank-col { width: 45px; }
                .name-col { width: 35%; min-width: 120px; }
                .code-col { width: 80px; }
                .dept-col { width: 25%; min-width: 100px; }
                .revenue-col { width: 25%; min-width: 100px; }

                .rank-badge {
                    width: 24px;
                    height: 24px;
                }

                .rank-number {
                    width: 20px;
                    height: 20px;
                    font-size: 0.75rem;
                }
            }
    </style>

@endif