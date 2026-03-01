@extends(config('core.admin_theme').'.template')
@section('main')
    <script src="{{ url('libs/chartjs/js/Chart.bundle.js') }}"></script>
    <script src="{{ url('libs/chartjs/js/utils.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>

    <?php
    if (\Auth::guard('admin')->user()->super_admin != 1) {
        $whereCompany = '1 = 1';
    } else {
        $whereCompany = '1 = 1';
    }

    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01 00:00:00');
    $end_date   = isset($_GET['end_date'])   ? $_GET['end_date']   : date('Y-m-d 23:59:00');

    $where              = "created_at >= '".$start_date." 00:00:00' AND created_at <= '".$end_date." 23:59:59'";
    $whereRegistration  = "registration_date >= '".$start_date." 00:00:00' AND registration_date <= '".$end_date." 23:59:59'";
    $whereCreated_at    = "created_at >= '".$start_date." 00:00:00' AND created_at <= '".$end_date." 23:59:59'";
    $whereDate          = "date >= '".$start_date." 00:00:00' AND date <= '".$end_date." 23:59:59'";

    if (isset($_GET['admin_id']) && $_GET['admin_id'] != '') {
        $where .= " AND admin_id = ".$_GET['admin_id'];
        $whereRegistration .= " AND admin_id = ".$_GET['admin_id'];
    }

    $tong_hd          = @\App\Modules\HBDashboard\Models\Bill::whereRaw($whereRegistration)->count();
    $tong_khach       = @\App\Modules\HBDashboard\Models\Bill::whereRaw($where)->select('id')->get()->count();
    $doanh_so         = \App\Modules\HBDashboard\Models\Bill::whereRaw($whereRegistration)->sum('total_price');
    $doanh_thu_du_an  = \App\Modules\HBDashboard\Models\Bill::whereRaw($whereRegistration)->sum('total_received');
    $phieu_thu        = \App\Modules\HBDashboard\Models\BillReceipts::where('price', '>', 0)->whereRaw($whereDate)->sum('price');
    $phieu_chi        = \App\Modules\HBDashboard\Models\BillReceipts::where('price', '<', 0)->whereRaw($whereDate)->sum('price');
    ?>
    <div class="container-fluid p-3 dashboard-container">


        <div class="container">
            {{-- Bộ lọc --}}
            @if(in_array(CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name'), ['super_admin']))
                @include('HBDashboard.dashboard.partials.bo_loc.bo_loc_chung')
                {{--            <div class="card shadow-sm border-0 mb-4">--}}
                {{--                <div class="card-body p-3">--}}
                {{--                    @include('HBDashboard.dashboard.partials.bo_loc.bo_loc_chung')--}}
                {{--                </div>--}}
                {{--            </div>--}}
            @endif
            @include('HBDashboard.dashboard.partials.so_lieu.so_lieu_tong_quan')
            @include('HBDashboard.dashboard.partials.so_lieu.chi_tiet_chi_phi')
            @include('CoreERP.dashboard.partials.xep_hang.top_sale_3_thang')
            @include('CoreERP.dashboard.partials.xep_hang.top_marketing_3_thang')
            @include('CoreERP.dashboard.partials.xep_hang.top_ky_thuat')
            @include('HBDashboard.dashboard.partials.thong_bao.hd_moi_thuong_sx')
            @include('CoreERP.dashboard.partials.thong_bao.hd_moi')
            @include('CoreERP.dashboard.partials.xep_hang.xep_hang_dich_vu')
            @include('HBDashboard.dashboard.partials.nhac_nho.hd_no_tien')
            @include('HBDashboard.dashboard.partials.nhac_nho.hd_cham_tien_do')
            @include('HBDashboard.dashboard.partials.nhac_nho.hd_sap_het_han')
            @include('HBDashboard.dashboard.partials.bieu_do.hop_dong_doanh_so_khach_hang')
            @include('HBDashboard.dashboard.partials.bieu_do.doanh_so_theo_tung_loai_dv')
            @include('HBDashboard.dashboard.partials.bieu_do.gia_han')
        </div>
    </div>
{{--        --}}{{-- Top sale & marketing --}}
{{--        <div class="row g-4 mt-1">--}}
{{--            <div class="col-12 col-lg-6">--}}
{{--                <div class="card shadow-sm border-0 h-100">--}}
{{--                    <div class="card-body">--}}
{{--                        @include('CoreERP.dashboard.partials.xep_hang.top_sale_3_thang')--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-12 col-lg-6">--}}
{{--                <div class="card shadow-sm border-0 h-100">--}}
{{--                    <div class="card-body">--}}
{{--                        @include('CoreERP.dashboard.partials.xep_hang.top_marketing_3_thang')--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

        {{-- Top kỹ thuật --}}
{{--        <div class="row g-4 mt-1">--}}
{{--            <div class="col-12">--}}
{{--                <div class="card shadow-sm border-0">--}}
{{--                    <div class="card-body">--}}
{{--                        @include('CoreERP.dashboard.partials.xep_hang.top_ky_thuat')--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        --}}{{-- Thông báo hợp đồng --}}
{{--        <div class="row g-4 mt-1">--}}
{{--            <div class="col-12 col-lg-6">--}}
{{--                <div class="card shadow-sm border-0 h-100">--}}
{{--                    <div class="card-body">--}}
{{--                        @include('HBDashboard.dashboard.partials.thong_bao.hd_moi_thuong_sx')--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-12 col-lg-6">--}}
{{--                <div class="card shadow-sm border-0 h-100">--}}
{{--                    <div class="card-body">--}}
{{--                        @include('CoreERP.dashboard.partials.thong_bao.hd_moi')--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        --}}{{-- Xếp hạng dịch vụ --}}
{{--        <div class="row g-4 mt-1">--}}
{{--            <div class="col-12">--}}
{{--                <div class="card shadow-sm border-0">--}}
{{--                    <div class="card-body">--}}
{{--                        @include('CoreERP.dashboard.partials.xep_hang.xep_hang_dich_vu')--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        --}}{{-- Nhắc nhở & cảnh báo --}}
{{--        <div class="row g-4 mt-1">--}}
{{--            <div class="col-12 col-lg-8">--}}
{{--                <div class="card shadow-sm border-0 h-100">--}}
{{--                    <div class="card-body">--}}
{{--                        @include('HBDashboard.dashboard.partials.nhac_nho.hd_no_tien')--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-12 col-lg-4">--}}
{{--                <div class="card shadow-sm border-0 h-100">--}}
{{--                    <div class="card-body">--}}
{{--                        @include('HBDashboard.dashboard.partials.nhac_nho.hd_cham_tien_do')--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}


{{--        <div class="row g-4 mt-1">--}}
{{--            <div class="col-12">--}}
{{--                <div class="card shadow-sm border-0 card-accent-warning">--}}
{{--                    <div class="card-header py-2 d-flex align-items-center">--}}
{{--                        <strong class="mb-0">Danh sách hợp đồng sắp hết hạn</strong>--}}
{{--                    </div>--}}
{{--                    <div class="card-body">--}}
{{--                        @include('HBDashboard.dashboard.partials.nhac_nho.hd_sap_het_han')--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}



{{--        --}}{{-- Biểu đồ --}}
{{--        <div class="row g-4 mt-1">--}}
{{--            <div class="col-12">--}}
{{--                <div class="card shadow-sm border-0">--}}
{{--                    <div class="card-body">--}}
{{--                        @include('HBDashboard.dashboard.partials.bieu_do.hop_dong_doanh_so_khach_hang')--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-12">--}}
{{--                <div class="card shadow-sm border-0">--}}
{{--                    <div class="card-body">--}}
{{--                        @include('HBDashboard.dashboard.partials.bieu_do.doanh_so_theo_tung_loai_dv')--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-12">--}}
{{--                <div class="card shadow-sm border-0">--}}
{{--                    <div class="card-body">--}}
{{--                        @include('HBDashboard.dashboard.partials.bieu_do.gia_han')--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
@endsection

@section('custom_head')
    <style>
        .dashboard-container {
            background-color: #f9fafb;
        }
        .card {
            border-radius: 10px;
            transition: 0.3s all ease;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }
        .card-body {
            padding: 1rem 1.25rem;
        }
        /* Mobile bỏ padding container */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0 !important;
            }

            .dashboard-container .container {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
        }
    </style>
@endsection
