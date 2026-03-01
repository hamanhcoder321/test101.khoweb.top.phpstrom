@php
    use App\Modules\HBDashboard\Models\Bill;

    // Tổng số hợp đồng
    $tong_hd = $tong_hd ?? 0;

    // Tổng khách hàng
    $tong_khach = $tong_khach ?? 0;

    // Doanh số HBweb
    $doanh_so_hbweb = Bill::whereNotIn('service_id', [23, 24, 25])
        ->whereRaw($whereRegistration)
        ->sum('total_price');

    // Doanh số Hobasoft
    $doanh_so_hobasoft = Bill::whereIn('service_id', [23, 24, 25])
        ->whereRaw($whereRegistration)
        ->sum('total_price');

    // Tổng doanh số = HBweb + Hobasoft
    $doanh_so = $doanh_so_hbweb + $doanh_so_hobasoft;

    // DS duy trì
    $ds_duy_tri = Bill::whereIn('service_id', [7, 25])
        ->whereRaw($whereRegistration)
        ->sum('total_price');

    // DS WP/LDP ký mới
    $ds_wp_ldp_ky_moi = Bill::whereIn('service_id', [
        1, 5, 10, 11, 12, 13, 14, 15,
        16, 17, 18, 19, 20, 21
    ])->whereRaw($whereRegistration)->sum('total_price');

    // Doanh thu dự án
    $doanh_thu_du_an = $doanh_thu_du_an ?? 0;

    // Tổng phiếu thu
    $phieu_thu = $phieu_thu ?? 0;

    // Tổng phiếu chi trong tháng
    $phieu_chi = $phieu_chi ?? 0;
@endphp

<div class="dashboard-stats custom-card mb-4 p-4">
   <div class="section-header" style="background: none !important; padding: 0 !important;">
       <h3 class="section-title" >
           <i class="bi bi-cash-stack text-danger me-2"></i>Số liệu tổng quan
       </h3>
   </div>
    <div class="stats-container">
        <!-- Tổng HĐ -->
        <div class="stat-card stat-card-purple">
            <div class="stat-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Tổng HĐ</div>
                <div class="stat-value">{{ number_format($tong_hd, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- Tổng khách -->
        <div class="stat-card stat-card-orange">
            <div class="stat-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Tổng khách</div>
                <div class="stat-value">{{ number_format($tong_khach, 0, '.', '.') }}</div>

            </div>
        </div>

        <!-- Tổng doanh số -->
        <div class="stat-card stat-card-cyan">
            <div class="stat-icon">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Tổng doanh số</div>
                <div class="stat-value">{{ number_format($doanh_so, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- DS duy trì -->
        <div class="stat-card stat-card-pink">
            <div class="stat-icon">
                <i class="bi bi-arrow-repeat"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">DS duy trì</div>
                <div class="stat-value">{{ number_format($ds_duy_tri, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- DS WP/LDP ký mới -->
        <div class="stat-card stat-card-green">
            <div class="stat-icon">
                <i class="bi bi-plus-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">DS WP/LDP ký mới</div>
                <div class="stat-value">{{ number_format($ds_wp_ldp_ky_moi, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- Doanh thu dự án -->
        <div class="stat-card stat-card-blue">
            <div class="stat-icon">
                <i class="bi bi-briefcase"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Doanh thu dự án</div>
                <div class="stat-value">{{ number_format($doanh_thu_du_an, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- Tổng phiếu thu -->
        <div class="stat-card stat-card-indigo">
            <div class="stat-icon">
                <i class="bi bi-receipt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Tổng phiếu thu</div>
                <div class="stat-value">{{ number_format($phieu_thu, 0, '.', '.') }}</div>
            </div>
        </div>

        <!-- Tổng P.chi trong tháng -->
        <div class="stat-card stat-card-red">
            <div class="stat-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Tổng P.chi trong tháng</div>
                <div class="stat-value">{{ number_format($phieu_chi, 0, '.', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<style>
    .dashboard-stats {
        margin-bottom: 2rem;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        border-radius: 16px 16px 0 0;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .stat-icon {
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

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.75rem;
        line-height: 1.2;
    }

    .stat-action {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .stat-action:hover {
        color: #374151;
    }

    /* Color variants */
    .stat-card-purple::before {
        background: linear-gradient(135deg, #8b5cf6, #a855f7);
    }
    .stat-card-purple .stat-icon {
        background: linear-gradient(135deg, #8b5cf6, #a855f7);
    }

    .stat-card-orange::before {
        background: linear-gradient(135deg, #f59e0b, #f97316);
    }
    .stat-card-orange .stat-icon {
        background: linear-gradient(135deg, #f59e0b, #f97316);
    }

    .stat-card-cyan::before {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
    }
    .stat-card-cyan .stat-icon {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
    }

    .stat-card-pink::before {
        background: linear-gradient(135deg, #ec4899, #db2777);
    }
    .stat-card-pink .stat-icon {
        background: linear-gradient(135deg, #ec4899, #db2777);
    }

    .stat-card-green::before {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    .stat-card-green .stat-icon {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .stat-card-blue::before {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }
    .stat-card-blue .stat-icon {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .stat-card-indigo::before {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
    }
    .stat-card-indigo .stat-icon {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
    }

    .stat-card-red::before {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }
    .stat-card-red .stat-icon {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    @media (max-width: 1200px) {
        .stats-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-container {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .stat-card {
            padding: 1.25rem;
        }

        .stat-value {
            font-size: 1.5rem;
        }
    }
</style>