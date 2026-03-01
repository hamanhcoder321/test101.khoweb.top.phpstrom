@php
    $thang_sau = date("m", strtotime($start_date)) + 1;
    if ($thang_sau < 10) $thang_sau = '0' . $thang_sau;
    $luong_start_date = date("Y-m-21 00:00:01", strtotime($start_date));    // từ ngày 21 tháng trước
    $luong_end_date   = date("Y-" . $thang_sau . "-20 00:00:01", strtotime($start_date)); // đến ngày 20 tháng sau
    $end_date         = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d 23:59:00');

    $whereDate = "date >= '" . $start_date . " 00:00:00' AND date <= '" . $end_date . " 23:59:59'";
    $tong_luong = 0;

    // Lấy dữ liệu lương và chi phí
    $luong_kd   = \App\Modules\HBDashboard\Models\BillReceipts::where('price', '<', 0)
                    ->whereRaw("date >= '" . $luong_start_date . "' AND date <= '" . $luong_end_date . "'")
                    ->whereIn('type', ['luong_kd'])
                    ->sum('price');
    $tong_luong += $luong_kd;

    $luong_kt   = \App\Modules\HBDashboard\Models\BillReceipts::where('price', '<', 0)
                    ->whereRaw("date >= '" . $luong_start_date . "' AND date <= '" . $luong_end_date . "'")
                    ->whereIn('type', ['luong_kt'])
                    ->sum('price');
    $tong_luong += $luong_kt;

    $luong_khac = \App\Modules\HBDashboard\Models\BillReceipts::where('price', '<', 0)
                    ->whereRaw("date >= '" . $luong_start_date . "' AND date <= '" . $luong_end_date . "'")
                    ->whereIn('type', ['luong'])
                    ->sum('price');
    $tong_luong += $luong_khac;

    $phuc_loi   = \App\Modules\HBDashboard\Models\BillReceipts::where('price', '<', 0)
                    ->whereRaw("date >= '" . $luong_start_date . "' AND date <= '" . $luong_end_date . "'")
                    ->whereIn('type', ['phuc_loi'])
                    ->sum('price');
    $tong_luong += $phuc_loi;

    $co_so      = \App\Modules\HBDashboard\Models\BillReceipts::whereRaw($whereDate)
                    ->where('price', '<', 0)
                    ->whereIn('type', ['co_so'])
                    ->sum('price');

    $co_so_so   = \App\Modules\HBDashboard\Models\BillReceipts::whereRaw($whereDate)
                    ->where('price', '<', 0)
                    ->whereIn('type', ['co_so_so'])
                    ->sum('price');

    $khac       = \App\Modules\HBDashboard\Models\BillReceipts::whereRaw($whereDate)
                    ->where('price', '<', 0)
                    ->whereIn('type', ['khac'])
                    ->sum('price');

    $chi_ko_gom_luong = \App\Modules\HBDashboard\Models\BillReceipts::where('price', '<', 0)
                    ->whereRaw($whereDate)
                    ->whereNotIn('type', ['luong', 'luong_kd', 'luong_kt', 'phuc_loi'])
                    ->sum('price');

    $dau_tu     = \App\Modules\HBDashboard\Models\BillReceipts::whereRaw($whereDate)
                    ->where('price', '<', 0)
                    ->whereRaw("date >= '" . $luong_start_date . "' AND date <= '" . $luong_end_date . "'")
                    ->whereIn('type', ['dt'])
                    ->sum('price');
@endphp

<div class="dashboard-expense-stats custom-card mb-4 p-4">
    <div class="section-header" style="background: none !important; padding: 0 !important;">
        <h3 class="section-title">
            <i class="bi bi-cash-stack text-danger me-2"></i>Chi phí
        </h3>
    </div>

    <div class="expense-stats-container">
        <!-- Lương KD -->
        <div class="expense-card expense-card-red">
            <div class="expense-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="expense-content">
                <div class="expense-label">Lương KD</div>
                <div class="expense-value">{{ number_format($luong_kd, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- Lương KT -->
        <div class="expense-card expense-card-orange">
            <div class="expense-icon">
                <i class="bi bi-calculator"></i>
            </div>
            <div class="expense-content">
                <div class="expense-label">Lương KT</div>
                <div class="expense-value">{{ number_format($luong_kt, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- Lương khác -->
        <div class="expense-card expense-card-yellow">
            <div class="expense-icon">
                <i class="bi bi-person-lines-fill"></i>
            </div>
            <div class="expense-content">
                <div class="expense-label">Lương khác</div>
                <div class="expense-value">{{ number_format($luong_khac, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- Phúc lợi -->
        <div class="expense-card expense-card-green">
            <div class="expense-icon">
                <i class="bi bi-heart-fill"></i>
            </div>
            <div class="expense-content">
                <div class="expense-label">Phúc lợi</div>
                <div class="expense-value">{{ number_format($phuc_loi, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- Cơ sở VC -->
        <div class="expense-card expense-card-blue">
            <div class="expense-icon">
                <i class="bi bi-building"></i>
            </div>
            <div class="expense-content">
                <div class="expense-label">Cơ sở VC</div>
                <div class="expense-value">{{ number_format($co_so, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- Cơ sở số -->
        <div class="expense-card expense-card-indigo">
            <div class="expense-icon">
                <i class="bi bi-server"></i>
            </div>
            <div class="expense-content">
                <div class="expense-label">Cơ sở số</div>
                <div class="expense-value">{{ number_format($co_so_so, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- Khác -->
        <div class="expense-card expense-card-purple">
            <div class="expense-icon">
                <i class="bi bi-three-dots"></i>
            </div>
            <div class="expense-content">
                <div class="expense-label">Khác</div>
                <div class="expense-value">{{ number_format($khac, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- Đầu tư -->
        <div class="expense-card expense-card-teal">
            <div class="expense-icon">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div class="expense-content">
                <div class="expense-label">Đầu tư</div>
                <div class="expense-value">{{ number_format($dau_tu, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- Tổng lương -->
        <div class="expense-card expense-card-pink expense-highlight">
            <div class="expense-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="expense-content">
                <div class="expense-label">Tổng lương</div>
                <div class="expense-value">{{ number_format($tong_luong, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- Tổng chi -->
        <div class="expense-card expense-card-dark expense-highlight">
            <div class="expense-icon">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="expense-content">
                <div class="expense-label">Tổng chi</div>
                <div class="expense-value">{{ number_format($chi_ko_gom_luong + $tong_luong, 0, '.', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="expense-note">
        <i class="bi bi-info-circle me-2"></i>
        <span>Truy vấn theo bộ lọc thời gian, lương kỳ 1 + 15 tháng sau của ngày bắt đầu</span>
    </div>
</div>

<style>
    .dashboard-expense-stats {
        margin-bottom: 2rem;
    }

    .section-header {
        margin-bottom: 1.5rem;
    }

    .section-title {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        margin: 0 !important;
        padding-bottom: 0.5rem !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }
    .section-title i{
        font-size:22px !important;
    }

    .expense-stats-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 1rem;
    }

    .expense-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .expense-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        border-radius: 16px 16px 0 0;
    }

    .expense-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .expense-highlight {
        box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.15), 0 4px 8px -2px rgba(0, 0, 0, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .expense-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        font-size: 1.25rem;
        color: white;
    }

    .expense-content {
        flex: 1;
    }

    .expense-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }

    .expense-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.75rem;
        line-height: 1.2;
    }

    .expense-action {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .expense-action:hover {
        color: #374151;
    }

    .expense-note {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        color: #475569;
        display: flex;
        align-items: center;
    }

    /* Color variants for expense cards */
    .expense-card-red::before {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }
    .expense-card-red .expense-icon {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .expense-card-orange::before {
        background: linear-gradient(135deg, #f97316, #ea580c);
    }
    .expense-card-orange .expense-icon {
        background: linear-gradient(135deg, #f97316, #ea580c);
    }

    .expense-card-yellow::before {
        background: linear-gradient(135deg, #eab308, #ca8a04);
    }
    .expense-card-yellow .expense-icon {
        background: linear-gradient(135deg, #eab308, #ca8a04);
    }

    .expense-card-green::before {
        background: linear-gradient(135deg, #22c55e, #16a34a);
    }
    .expense-card-green .expense-icon {
        background: linear-gradient(135deg, #22c55e, #16a34a);
    }

    .expense-card-blue::before {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }
    .expense-card-blue .expense-icon {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .expense-card-indigo::before {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
    }
    .expense-card-indigo .expense-icon {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
    }

    .expense-card-purple::before {
        background: linear-gradient(135deg, #a855f7, #9333ea);
    }
    .expense-card-purple .expense-icon {
        background: linear-gradient(135deg, #a855f7, #9333ea);
    }

    .expense-card-teal::before {
        background: linear-gradient(135deg, #14b8a6, #0d9488);
    }
    .expense-card-teal .expense-icon {
        background: linear-gradient(135deg, #14b8a6, #0d9488);
    }

    .expense-card-pink::before {
        background: linear-gradient(135deg, #ec4899, #db2777);
    }
    .expense-card-pink .expense-icon {
        background: linear-gradient(135deg, #ec4899, #db2777);
    }

    .expense-card-dark::before {
        background: linear-gradient(135deg, #374151, #1f2937);
    }
    .expense-card-dark .expense-icon {
        background: linear-gradient(135deg, #374151, #1f2937);
    }

    @media (max-width: 1200px) {
        .expense-stats-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .expense-stats-container {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .expense-card {
            padding: 1.25rem;
        }

        .expense-value {
            font-size: 1.5rem;
        }
    }
</style>