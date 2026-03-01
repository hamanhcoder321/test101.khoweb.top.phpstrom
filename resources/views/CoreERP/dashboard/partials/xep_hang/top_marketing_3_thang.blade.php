@if(!isset($_GET['ajax-load']))
    <script>
        $(document).ready(function () {
            $.ajax({
                url: '/admin/dashboard/ajax/load-khoi?ajax-load=true&file=CoreERP.dashboard.partials.xep_hang.top_marketing_3_thang',
                type: 'GET',
                data: {},
                success: function (html) {
                    $('#top_marketing_3_thang').html(html);
                },
                error: function () {
                    console.log('lỗi load khối partials/xep_hang/top_marketing_3_thang');
                }
            });
        });
    </script>

    <div class="custom-card mb-4 p-4">
        <div class="section-header" style="background: none !important; padding: 0 !important;">
            <h3 class="section-title">
                <i class="bi bi-trophy text-warning me-2"></i>
                Top Marketing
            </h3>
        </div>
        <div class="card-body">
            <div id="top_marketing_3_thang" class="loading-container">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <span>Đang tải dữ liệu...</span>
                </div>
            </div>
        </div>
    </div>

@else

        <?php
        $room_ids = [
            '' => 'Khác',
            1 => 'Phòng kinh doanh 1',
            2 => 'Phòng kinh doanh 2',
            3 => 'Phòng kinh doanh 3',
            4 => 'Phòng kinh doanh 4',
            5 => 'Phòng kinh doanh 5',
            6 => 'Phòng Telesale',
            20 => 'Marketing',
        ];

        $marketer_ids = \App\Models\RoleAdmin::whereIn('role_id', [
            174, // marketing
        ])->pluck('admin_id')->toArray();

        $months_data = [];

// Lấy dữ liệu 3 tháng
        for ($i = 0; $i >= -2; $i--) {
            $month_start = date('Y-m-01 00:00:00', strtotime($i . ' months'));
            $month_end = date('Y-m-t 23:59:00', strtotime($i . ' months'));
            $month_name = date('n', strtotime($i . ' months')); // 1–12 (không số 0 đầu)

            $best_marketing = \App\Modules\HBBill\Models\Bill::selectRaw('Sum(total_price) as total_price, marketer_id')
                ->whereRaw("registration_date >= '$month_start' AND registration_date <= '$month_end'")
                ->whereIn('marketer_id', $marketer_ids)
                ->groupBy('marketer_id')
                ->orderBy('total_price', 'desc')
                ->get();

            $tong_doanh_so = 0;
            $ds_phong = [];

            foreach ($best_marketing as $marketer) {
                $tong_doanh_so += $marketer->total_price;
                if (!isset($ds_phong[$marketer->marketer->room_id])) {
                    $ds_phong[$marketer->marketer->room_id] = $marketer->total_price;
                } else {
                    $ds_phong[$marketer->marketer->room_id] += $marketer->total_price;
                }
            }
            arsort($ds_phong);

            $months_data[] = [
                'month_name' => $month_name,
                'marketers' => $best_marketing,
                'total' => $tong_doanh_so,
                'departments' => $ds_phong,
                'is_current' => $i == 0
            ];
        }
        ?>

    <div class="modern-dashboard">
        <!-- Tab Navigation -->
        <div class="tab-navigation">
            @foreach($months_data as $index => $month_data)
                <button class="tab-btn {{ $index === 0 ? 'active' : '' }}"
                        data-month="{{ $index }}"
                        onclick="switchMonth({{ $index }})">

                    <span class="tab-label">Tháng {{ $month_data['month_name'] }}</span>
                    @if($month_data['is_current'])
                        <span class="current-indicator"></span>
                    @endif
                </button>
            @endforeach
        </div>

        <!-- Content Panels -->
        @foreach($months_data as $index => $month_data)
            <div class="month-panel {{ $index === 0 ? 'active' : '' }}" id="month-panel-{{ $index }}">
                <!-- Summary Header -->
                <div class="summary-header">
                    <div class="summary-item">
                        <span class="summary-label">Tổng doanh số</span>
                        <span class="summary-value">{{ number_format($month_data['total'], 0, '.', '.') }} <small>VNĐ</small></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Số nhân viên</span>
                        <span class="summary-value">{{ count($month_data['marketers']) }} <small>người</small></span>
                    </div>
                </div>

                <!-- Marketing Table -->
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead>
                        <tr>
                            <th class="rank-header">#</th>
                            <th class="name-header">Nhân viên</th>
                            <th class="code-header">Mã NV</th>
                            <th class="dept-header">Phòng ban</th>
                            <th class="revenue-header">Doanh số</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($month_data['marketers']) > 0)
                            @php $rank = 1; @endphp
                            @foreach($month_data['marketers'] as $marketer)
                                <tr class="table-row">
                                    <td class="rank-cell">
                                        <div class="rank-badge rank-{{ $rank <= 3 ? $rank : 'other' }}">
                                            @if($rank == 1)
                                                <i class="bi bi-trophy-fill"></i>
                                            @elseif($rank == 2)
                                                <i class="bi bi-award-fill"></i>
                                            @elseif($rank == 3)
                                                <i class="bi bi-gem"></i>
                                            @else
                                                {{ $rank }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="name-cell">
                                        <div class="employee-info">
                                            <a href="/admin/bill?search=true&marketer_id={{ $marketer->marketer_id }}&from_date={{ date('Y-m-01', strtotime($month_data['month_name'])) }}&registration_date=1"
                                               target="_blank" class="employee-name">
                                                {{ @$marketer->marketer->name }}
                                            </a>
                                            <div class="mobile-info">
                                                <span class="mobile-code">{{ @$marketer->marketer->code }}</span>
                                                <span class="mobile-dept">{{ @$room_ids[$marketer->marketer->room_id] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="code-cell">
                                        <span class="employee-code">{{ @$marketer->marketer->code }}</span>
                                    </td>
                                    <td class="dept-cell">
                                        <span class="dept-name">{{ @$room_ids[$marketer->marketer->room_id] }}</span>
                                    </td>
                                    <td class="revenue-cell">
                                        <div class="revenue-wrapper">
                                            <span class="revenue-amount {{ ($month_data['is_current'] && date('d')/30 > $marketer->total_price/10000000) || (!$month_data['is_current'] && $marketer->total_price < 10000000) ? 'below-target' : '' }}">
                                                {{ number_format($marketer->total_price, 0, '.', '.') }}
                                            </span>
                                            <span class="currency">VNĐ</span>
                                        </div>
                                    </td>
                                </tr>
                                @php $rank++; @endphp
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <div class="empty-content">
                                        <i class="bi bi-inbox"></i>
                                        <p>Chưa có dữ liệu trong tháng này</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>

                <!-- Department Summary -->
                @if(count($month_data['departments']) > 0)
                    <div class="dept-summary">
                        <h4 class="dept-title">
                            <i class="bi bi-building"></i>
                            Theo phòng ban
                        </h4>
                        <div class="dept-grid">
                            @foreach($month_data['departments'] as $room_id => $revenue)
                                <div class="dept-card">
                                    <div class="dept-card-name">{{ $room_ids[$room_id] }}</div>
                                    <div class="dept-card-revenue">{{ number_format($revenue, 0, '.', '.') }} VNĐ</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <script>
        function switchMonth(monthIndex) {
            // Update tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-month="${monthIndex}"]`).classList.add('active');

            // Update content panels
            document.querySelectorAll('.month-panel').forEach(panel => {
                panel.classList.remove('active');
            });
            document.getElementById(`month-panel-${monthIndex}`).classList.add('active');
        }
    </script>

    <style>
        * {
            box-sizing: border-box;
        }

        .modern-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            margin-bottom: 24px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header {

            border-bottom: 1px solid #f1f5f9;
        }

        .card-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            padding-bottom: 16px;
        }

        .card-title i {
            color: #10b981;
            font-size: 22px;
        }

        .card-content {
            padding: 0;
        }

        /*.loading-container {*/
        /*    padding: 60px 24px;*/
        /*}*/

        .loading-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f3f4f6;
            border-top: 3px solid #10b981;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading-spinner span {
            color: #64748b;
            font-size: 14px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

       

        .tab-navigation {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 0;
        }

        .tab-btn {
            position: relative;
            padding: 16px 24px 12px;
            border: none;
            background: transparent;
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border-radius: 8px 8px 0 0;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            background: #f8fafc;
            color: #374151;
        }

        .tab-btn.active {
            background: #10b981;
            color: white;
            transform: translateY(-2px);
        }

        .current-indicator {
            width: 6px;
            height: 6px;
            background: #fbbf24;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .tab-btn.active .current-indicator {
            background: #fef3c7;
        }

        .month-panel {
            display: none;
        }

        .month-panel.active {
            display: block;
        }

        .summary-header {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .summary-item {
            text-align: center;
        }

        .summary-label {
            display: block;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
        }

        .summary-value small {
            font-size: 12px;
            color: #64748b;
            font-weight: 400;
        }

        .table-wrapper {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .modern-table thead {
            background: #f8fafc;
        }

        .modern-table th {
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e2e8f0;
        }

        .rank-header { width: 60px; text-align: center; }
        .code-header, .dept-header { width: 120px; }
        .revenue-header { text-align: right; min-width: 140px; }

        .table-row {
            transition: background-color 0.2s ease;
        }

        .table-row:hover {
            background: #f8fafc;
        }

        .table-row td {
            padding: 16px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        .rank-1 {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
        }

        .rank-2 {
            background: linear-gradient(135deg, #9ca3af, #6b7280);
            color: white;
        }

        .rank-3 {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .rank-other {
            background: #f1f5f9;
            color: #64748b;
        }

        .employee-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .employee-name {
            font-weight: 600;
            color: #1e293b;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .employee-name:hover {
            color: #10b981;
        }

        .mobile-info {
            display: none;
            gap: 12px;
            font-size: 12px;
            color: #64748b;
        }

        .employee-code {
            display: inline-block;
            background: #dbeafe;
            color: #1d4ed8;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }

        .dept-name {
            color: #64748b;
        }

        .revenue-wrapper {
            text-align: right;
        }

        .revenue-amount {
            display: block;
            font-weight: 700;
            color: #10b981;
            font-size: 16px;
        }

        .revenue-amount.below-target {
            color: #ef4444;
        }

        .currency {
            font-size: 11px;
            color: #9ca3af;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .empty-content i {
            font-size: 32px;
            color: #d1d5db;
        }

        .empty-content p {
            color: #9ca3af;
            margin: 0;
        }

        .dept-summary {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
        }

        .dept-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 16px;
        }

        .dept-title i {
            color: #10b981;
        }

        .dept-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 12px;
        }

        .dept-card {
            background: white;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .dept-card:hover {
            border-color: #10b981;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1);
        }

        .dept-card-name {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .dept-card-revenue {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modern-dashboard {
                padding: 16px;
            }

            .tab-navigation {
                flex-wrap: wrap;
            }

            .tab-btn {
                padding: 12px 16px 8px;
                font-size: 13px;
            }

            .summary-header {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 16px;
            }

            .summary-value {
                font-size: 20px;
            }

            .modern-table th,
            .modern-table td {
                padding: 12px 8px;
            }

            .code-header,
            .dept-header {
                display: none;
            }

            .code-cell,
            .dept-cell {
                display: none;
            }

            .mobile-info {
                display: flex;
            }

            .dept-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .dept-card {
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            .card-title {
                font-size: 18px;
            }

            .tab-btn {
                padding: 10px 12px 6px;
                min-width: auto;
                flex: 1;
                justify-content: center;
            }

            .tab-label {
                font-size: 12px;
            }

            .summary-header {
                padding: 12px;
            }

            .summary-value {
                font-size: 18px;
            }

            .rank-badge {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }

            .revenue-amount {
                font-size: 14px;
            }

        }
    </style>

@endif