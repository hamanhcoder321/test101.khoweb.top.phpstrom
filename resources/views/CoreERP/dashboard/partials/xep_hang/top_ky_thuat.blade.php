@if(!isset($_GET['ajax-load']))
    <script>
        $(document).ready(function () {
            $.ajax({
                url: '/admin/dashboard/ajax/load-khoi?ajax-load=true&file=CoreERP.dashboard.partials.xep_hang.top_ky_thuat',
                type: 'GET',
                data: {},
                success: function (html) {
                    $('#top_ky_thuat').html(html);
                },
                error: function () {
                    console.log('lỗi load khối partials/xep_hang/top_ky_thuat');
                }
            });
        });
    </script>

    <div class="custom-card p-4 mb-4">
        <h3 class="card-title">
            <i class="bi bi-tools text-primary me-2"></i>Top Kỹ Thuật
        </h3>
        <div class="table-responsive">
            <div id="top_ky_thuat" class="loading-container">
                <div class="d-flex justify-content-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

@else

        <?php
        require base_path('resources/views/HBBill/dhbill/partials/du_an_quy_diem.php');

        $admins = \App\Models\Admin::leftJoin('role_admin', 'role_admin.admin_id', '=', 'admin.id')
            ->select(['admin.name', 'admin.id', 'admin.code'])
            ->whereIn('role_admin.role_id', [
                173,      //  kỹ thuật
                188,    //  trưởng phòng KT
                178,    //  điều hành
            ])->groupBy('role_admin.admin_id')->get();

        $tong_diem = 0;
        $ds_admin = [];

        foreach($admins as $admin){
            $bill_progress = \App\Modules\HBBill\Models\Bill::rightJoin('bill_progress', 'bill_progress.bill_id', '=', 'bills.id')
                ->select(['bills.service_id', 'bill_progress.dh_id', 'bill_progress.kt_id'])
                ->whereIn('bill_progress.status', [
                    'Khách xác nhận xong'   //  chỉ lấy dự án đã xác nhận xong
                ])
                ->where(function ($query) use ($admin) {
                    $query->orWhere('bill_progress.dh_id', $admin->id);   //  lấy dự án mình là điều hành
                    $query->orWhere('bill_progress.kt_id', $admin->id);   //  lấy dự án mình là kỹ thuật
                })->get();

            $diem = 0;
            foreach($bill_progress as $bill_progres) {
                if ($bill_progres->dh_id == $admin->id) {
                    //  nếu dự án do mình điều hành thì tính điểm điều hành
                    if (isset($diem_dh[$bill_progres->service_id])) {
                        $diem += (float) @$diem_dh[$bill_progres->service_id];
                    }
                }
                if ($bill_progres->kt_id == $admin->id) {
                    //  nếu dự án do mình triển khai thì tính điểm kỹ thuật
                    if (isset($diem_kt[$bill_progres->service_id])) {
                        $diem += (float) @$diem_kt[$bill_progres->service_id];
                    }
                }
            }
            $tong_diem += $diem;
            if($diem > 0) {
                $ds_admin[$diem][] = $admin;
            }
        }

        krsort($ds_admin);
        ?>

    <div class="technical-dashboard">
        <div class="month-section">
            <div class="month-header current-month">
                <h4 class="month-title">
                    <i class="bi bi-calendar-event me-2"></i>
                    Tháng {{ date('m') }}
                    <span class="current-badge">Hiện tại</span>
                </h4>
                <div class="month-total">
                    <span class="total-label">Tổng điểm:</span>
                    <span class="total-value">{{ number_format($tong_diem, 1, '.', '.') }}</span>
                </div>
            </div>

            <div class="table-container">
                <table class="technical-table">
                    <thead>
                    <tr>
                        <th class="rank-col">#</th>
                        <th class="name-col">Nhân viên</th>
                        <th class="code-col d-none d-md-table-cell">Mã NV</th>
                        <th class="score-col">Điểm số</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($ds_admin) > 0)
                        @php $rank = 1; @endphp
                        @foreach($ds_admin as $diem => $val)
                            @foreach($val as $admin)
                                <tr class="employee-row {{ $rank <= 3 ? 'top-performer' : '' }}">
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
                                    <td class="name-cell">
                                        <div class="employee-info">
                                            <div class="employee-name">{{ $admin->name }}</div>
                                            <div class="employee-code-mobile d-md-none">
                                                Mã: {{ $admin->code }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="code-cell d-none d-md-table-cell">
                                        <span class="code-badge technical">{{ $admin->code }}</span>
                                    </td>
                                    <td class="score-cell">
                                        <div class="score-info">
                                            <span class="score-amount">{{ number_format($diem, 1, '.', '.') }}</span>
                                            <span class="score-unit">điểm</span>
                                        </div>
                                    </td>
                                </tr>
                                @php $rank++; @endphp
                            @endforeach
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="no-data">
                                <div class="no-data-content">
                                    <i class="bi bi-inbox text-muted"></i>
                                    <p class="text-muted mb-0">Chưa có dữ liệu kỹ thuật trong tháng này</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="expense-note">

            <i class="bi bi-info-circle me-2"></i>
            <span>Truy vấn theo tháng hiện tại - Cập nhật: {{ date('d/m/Y H:i') }}</span>

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

        .technical-dashboard {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .month-section {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .month-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .month-header.current-month {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .month-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            flex: 1;
        }

        .current-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        .month-total {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .total-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .total-value {
            font-size: 1.1rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .technical-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .technical-table thead th {
            background: #eff6ff;
            color: #1e40af;
            font-weight: 600;
            padding: 1rem 0.75rem;
            text-align: left;
            border-bottom: 2px solid #dbeafe;
            white-space: nowrap;
        }

        .rank-col { width: 60px; text-align: center; }
        .name-col { min-width: 150px; }
        .code-col { width: 100px; }
        .score-col { min-width: 120px; text-align: right; }

        .employee-row {
            transition: all 0.2s ease;
        }

        .employee-row:hover {
            background: #eff6ff;
        }

        .employee-row.top-performer {
            background: linear-gradient(90deg, #dbeafe, transparent);
        }

        .employee-row td {
            padding: 0.875rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
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
            background: #dbeafe;
            border-radius: 50%;
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e40af;
        }

        .employee-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .employee-name {
            font-weight: 600;
            color: #1e293b;
        }

        .employee-code-mobile {
            font-size: 0.8rem;
            color: #64748b;
        }

        .code-badge {
            background: #e0f2fe;
            color: #0277bd;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .code-badge.technical {
            background: #dbeafe;
            color: #1e40af;
        }

        .score-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.125rem;
        }

        .score-amount {
            font-weight: 700;
            font-size: 1rem;
            color: #1d4ed8;
        }

        .score-unit {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 400;
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
        @media (max-width: 768px) {
            .month-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .month-title {
                font-size: 1rem;
            }

            .total-value {
                font-size: 1rem;
            }

            .technical-table thead th {
                padding: 0.75rem 0.5rem;
                font-size: 0.9rem;
            }

            .employee-row td {
                padding: 0.75rem 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .month-header {
                padding: 0.875rem 1rem;
            }

            .technical-table thead th,
            .employee-row td {
                padding: 0.625rem 0.375rem;
            }

            .rank-col { width: 50px; }
            .name-col { min-width: 120px; }
            .score-col { min-width: 100px; }

            .no-data {
                padding: 2rem 0.5rem !important;
            }

            .no-data-content i {
                font-size: 1.5rem;
            }
        }
    </style>

@endif