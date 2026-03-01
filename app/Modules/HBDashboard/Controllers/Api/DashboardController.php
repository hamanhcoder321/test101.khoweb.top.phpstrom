<?php

namespace App\Modules\HBDashboard\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Library\JWT\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Auth;
use App\CRMDV\Models\BillReceipts;
use Illuminate\Http\Request;
use Mail;
use Carbon\Carbon;
use App\CRMDV\Models\Bill;

class DashboardController extends Controller
{

    public function baoCao4ThangGanNhat(Request $request)
    {
        try {
            $now = Carbon::now();
            $monthsData = [];

            // Lặp qua 4 tháng gần nhất
            for ($i = 3; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $startDate = $month->copy()->startOfMonth();
                $endDate = $month->copy()->endOfMonth();

                // Tổng khách mới trong tháng
                $newLeads = DB::table('leads')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                // Khách thả nổi trong tháng
                $floatingLeads = DB::table('leads')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where(function($query) {
                        $query->whereIn('status', ['tha_noi', 'thả nổi', 'Thả nổi'])
                            ->orWhereNull('status');
                    })
                    ->count();

                $monthsData[] = [
                    'month' => $month->format('m/Y'),
                    'year' => $month->format('Y'),
                    'new_leads' => $newLeads,
                    'floating_leads' => $floatingLeads,

                ];
            }
            return response()->json([
                'success' => true,
                'msg' => 'Báo cáo khách hàng 4 tháng gần nhất',
                'data' => $monthsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Đã có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getLeadStatus(Request $request)
    {
        // --- Lấy tham số filter ---
        $start = $request->query('start');
        $end = $request->query('end');
        $month = $request->query('month');
        $year = $request->query('year');

        // --- Build query ---
        $query = DB::table('leads')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status');

        // --- Áp dụng điều kiện theo thời gian ---
        if ($start && $end) {
            $query->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
        } elseif ($month && $year) {
            $query->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);
        } elseif ($year) {
            $query->whereYear('created_at', $year);
        }

        // --- Lấy top 4 status ---
        $topStatuses = (clone $query)
            ->orderByDesc('total')
            ->limit(4)
            ->pluck('status')
            ->toArray();

        // --- Lấy tất cả leads sau filter ---
        $leads = $query->get();

        // --- Gom nhóm ---
        $statusCounts = [];
        foreach ($leads as $lead) {
            $statusKey = in_array($lead->status, $topStatuses) ? $lead->status : 'Khác';
            if (!isset($statusCounts[$statusKey])) {
                $statusCounts[$statusKey] = 0;
            }
            $statusCounts[$statusKey] += $lead->total;
        }

        // --- Tính % tổng ---
        $totalCount = array_sum($statusCounts);
        $statusData = [];
        $statusPercentages = [];
        foreach ($statusCounts as $name => $total) {
            $percentage = $totalCount > 0 ? round(($total / $totalCount) * 100, 1) : 0;
            $statusData[] = [
                'name' => $name,
                'total' => $total,
                'percentage' => $percentage
            ];
            $statusPercentages[$name] = $percentage;
        }

        // --- Chuẩn hóa tổng % = 100 ---
        $diff = 100 - array_sum(array_column($statusData, 'percentage'));
        if ($diff != 0) {
            end($statusCounts);
            $lastKey = key($statusCounts);
            $statusData[count($statusData) - 1]['percentage'] += $diff;
            $statusPercentages[$lastKey] += $diff;
        }

        return [
            'status'=>'success',
            'msg'=>'Thống kê trạng thái khách hàng',
            'data' => $statusData,
            'percent' => $statusPercentages
        ];
    }


    public function baoCaoKhachHang(Request $request)
    {
        try {
            $sourceFilter = $request->query('source'); // Facebook, Google, Zalo,...

            // --- 1. Xác định khoảng thời gian hợp lệ ---
            $dateRange = $this->getDateRange($request);

            // --- 2. Tổng quan ---
            $newLeads = DB::table('leads')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count();

            $interestedLeads = DB::table('leads')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->whereIn('status', ['quan_tam', 'quan tâm', 'Quan tâm'])
                ->count();

            $floatingLeads = DB::table('leads')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->where(function ($q) {
                    $q->whereIn('status', ['tha_noi', 'thả nổi', 'Thả nổi'])
                        ->orWhereNull('status');
                })
                ->count();

            // --- 3. Thống kê nguồn ---
            $mainSources = ['Facebook', 'Google', 'Zalo'];
            $sourceCounts = ['Facebook' => 0, 'Google' => 0, 'Zalo' => 0, 'Khác' => 0];

            $leadsBySource = DB::table('leads')
                ->select('source', DB::raw('COUNT(*) as total'))
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->whereNotNull('source')
                ->groupBy('source')
                ->get();

            foreach ($leadsBySource as $item) {
                if (in_array($item->source, $mainSources)) {
                    $sourceCounts[$item->source] += $item->total;
                } else {
                    $sourceCounts['Khác'] += $item->total;
                }
            }

            $totalWithOther = array_sum($sourceCounts);
            $sourceData = [];
            $sourcePercentages = [];

            foreach ($sourceCounts as $name => $total) {
                $percentage = $totalWithOther > 0 ? round(($total / $totalWithOther) * 100, 1) : 0;
                $sourcePercentages[$name] = $percentage;
                $sourceData[] = [
                    'name' => $name,
                    'total' => $total,
                    'percentage' => $percentage
                ];
            }

            // Chuẩn hóa tổng % = 100
            $diff = 100 - array_sum(array_column($sourceData, 'percentage'));
            if ($diff != 0) {
                $sourceData[count($sourceData) - 1]['percentage'] += $diff;
                $sourcePercentages['Khác'] += $diff;
            }

            // --- 4. Chuẩn bị response ---
            $response = [
                'success' => true,
                'data' => [
                    'period' => [
                        'start' => $dateRange['start']->format('Y-m-d'),
                        'end' => $dateRange['end']->format('Y-m-d')
                    ],
                    'summary' => [
                        'new_leads' => ['total' => $newLeads, 'label' => 'Khách mới tạo'],
                        'interested_leads' => ['total' => $interestedLeads, 'label' => 'Khách quan tâm'],
                        'floating_leads' => ['total' => $floatingLeads, 'label' => 'Khách thả nổi'],
                    ],
                    'source_chart' => [
                        'title' => 'Tổng khách mới theo nguồn',
                        'total' => $totalWithOther,
                        'data' => $sourceData,
                        'percentages' => $sourcePercentages
                    ]
                ]
            ];

            // --- 5. Chi tiết khách theo source nếu truyền query source ---
            if ($sourceFilter) {
                $leads = DB::table('leads')
                    ->select('id', 'name', 'tel', 'email', 'status', 'created_at')
                    ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('source', $sourceFilter)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $response['data']['source_details'] = [
                    'source' => $sourceFilter,
                    'total' => $leads->count(),
                    'leads' => $leads
                ];
            }

            return response()->json($response);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy khoảng thời gian hợp lệ
     */
    private function getDateRange(Request $request)
    {
        $now = Carbon::now();

        // 1. Nếu truyền start và end
        if ($request->filled('start') && $request->filled('end')) {
            return [
                'start' => Carbon::parse($request->query('start')),
                'end' => Carbon::parse($request->query('end'))
            ];
        }

        // 2. Nếu truyền month + year
        if ($request->filled('month') && $request->filled('year')) {
            $month = (int)$request->query('month');
            $year = (int)$request->query('year');

            if ($month < 1 || $month > 12) {
                throw new \InvalidArgumentException("Month không hợp lệ");
            }

            return [
                'start' => Carbon::create($year, $month, 1)->startOfMonth(),
                'end' => Carbon::create($year, $month, 1)->endOfMonth()
            ];
        }

        // 3. Nếu truyền year
        if ($request->filled('year')) {
            $year = (int)$request->query('year');
            return [
                'start' => Carbon::create($year, 1, 1)->startOfYear(),
                'end' => Carbon::create($year, 1, 1)->endOfYear()
            ];
        }

        // 4. Tham số không hợp lệ → lỗi
        throw new \InvalidArgumentException("Tham số filter không hợp lệ. Vui lòng sử dụng start+end, month+year hoặc year.");
    }

    public function tongQuanChiPhi(Request $request)
    {
        $start_date = $request->query('start_date', date('Y-m-01'));
        $end_date   = $request->query('end_date', date('Y-m-d 23:59:00'));

        // Xác định tháng sau
        $thang_sau = date("m", strtotime($start_date)) + 1;
        if ($thang_sau < 10) $thang_sau = '0' . $thang_sau;

        // Tính khoảng thời gian lương (21 tháng trước → 20 tháng sau)
        $luong_start_date = date("Y-m-21 00:00:01", strtotime($start_date));
        $luong_end_date   = date("Y-" . $thang_sau . "-20 00:00:01", strtotime($start_date));

        $whereDate = "date >= '" . $start_date . " 00:00:00' AND date <= '" . $end_date . " 23:59:59'";

        // --- Tính toán ---
        $tong_luong = 0;

        $luong_kd = BillReceipts::where('price', '<', 0)
            ->whereRaw("date >= '$luong_start_date' AND date <= '$luong_end_date'")
            ->whereIn('type', ['luong_kd'])
            ->sum('price');
        $tong_luong += $luong_kd;

        $luong_kt = BillReceipts::where('price', '<', 0)
            ->whereRaw("date >= '$luong_start_date' AND date <= '$luong_end_date'")
            ->whereIn('type', ['luong_kt'])
            ->sum('price');
        $tong_luong += $luong_kt;

        $luong_khac = BillReceipts::where('price', '<', 0)
            ->whereRaw("date >= '$luong_start_date' AND date <= '$luong_end_date'")
            ->whereIn('type', ['luong'])
            ->sum('price');
        $tong_luong += $luong_khac;

        $phuc_loi = BillReceipts::where('price', '<', 0)
            ->whereRaw("date >= '$luong_start_date' AND date <= '$luong_end_date'")
            ->whereIn('type', ['phuc_loi'])
            ->sum('price');
        $tong_luong += $phuc_loi;

        $dau_tu = BillReceipts::where('price', '<', 0)
            ->whereRaw($whereDate)
            ->whereIn('type', ['dt'])
            ->sum('price');

        $tong_chi = BillReceipts::where('price', '<', 0)
            ->whereRaw($whereDate)
            ->sum('price');

        return response()->json([
            'status' => true,
            'msg' => 'Tổng hợp chi phí',
            'data' => [
                'tong_chi'   => abs($tong_chi),
                'tong_luong' => abs($tong_luong),
                'dau_tu'     => abs($dau_tu),
                'phuc_loi'   => abs($phuc_loi),
            ]
        ]);
    }
    public function top3Sale(Request $request)
    {
        // ✅ Lấy month & year từ request
        $month = $request->input('month');
        $year = $request->input('year');

        // ✅ Nếu không có thì báo lỗi
        if (!$month || !$year) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng truyền đủ tháng (month) và năm (year). Ví dụ: ?month=10&year=2025'
            ], 400);
        }

        // ✅ Truy vấn Top 3 Sale theo tháng và năm
        $topSales = DB::table('bills as b')
            ->leftJoin('admin as a', 'a.id', '=', 'b.saler_id')
            ->select(
                'b.saler_id',
                DB::raw('COALESCE(a.name, "Không xác định") as sale_name'),
                DB::raw('SUM(b.total_price) as tong_doanh_so')
            )
            ->whereNull('b.deleted_at')
            ->whereMonth('b.created_at', $month)
            ->whereYear('b.created_at', $year)
            ->groupBy('b.saler_id', 'a.name')
            ->orderByDesc('tong_doanh_so')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                // Ép kiểu tong_doanh_so về int
                $item->tong_doanh_so = (int) $item->tong_doanh_so;
                return $item;
            });
        ;

        // ✅ Kiểm tra nếu không có dữ liệu
        if ($topSales->isEmpty()) {
            return response()->json([
                'status' => true,
                'msg' => "Không có dữ liệu cho tháng $month/$year",
                'data' => []
            ]);
        }

        // ✅ Trả kết quả JSON
        return response()->json([
            'status' => true,
            'msg' => "Top 3 nhân viên sale có doanh số cao nhất trong tháng $month/$year",
            'data' => $topSales
        ]);
    }
    public function apiThongKeTongHop(Request $request)
    {
        $sale = JWTAuth::parseToken();

        /* ================= TOÀN HỆ THỐNG ================= */
        $tongHopDong = Bill::count();

        $tongKhach = Bill::distinct('customer_id')
            ->count('customer_id');

        $tongDoanhSo = Bill::sum('total_price');

        $tongDoanhThu = Bill::sum('total_received');

        /* ================= RIÊNG SALE ================= */
        $tongHopDongSale = Bill::where('saler_id', $sale->id)->count();

        $tongKhachSale = Bill::where('saler_id', $sale->id)
            ->distinct('customer_id')
            ->count('customer_id');

        $tongDoanhSoSale = Bill::where('saler_id', $sale->id)
            ->sum('total_price');

        $tongDoanhThuSale = Bill::where('saler_id', $sale->id)
            ->sum('total_received');

        return response()->json([
            'status' => true,
            'msg'    => 'Thống kê tổng hợp',
            'data'   => [
                'tong' => [
                    'tong_hop_dong'  => $tongHopDong,
                    'tong_khach'     => $tongKhach,
                    'tong_doanh_so'  => (int) $tongDoanhSo,
                    'tong_doanh_thu' => (int) $tongDoanhThu,
                ],
                'sale' => [
                    'saler_id'        => $sale->id,
                    'tong_hop_dong'  => $tongHopDongSale,
                    'tong_khach'     => $tongKhachSale,
                    'tong_doanh_so'  => (int) $tongDoanhSoSale,
                    'tong_doanh_thu' => (int) $tongDoanhThuSale,
                ]
            ]
        ]);
    }



    public function top3maketing(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year  = $request->get('year', date('Y'));

        $topMarketer = Bill::select('marketer_id', DB::raw('SUM(total_price) as tong_doanh_so'))
            ->whereYear('registration_date', $year)
            ->whereMonth('registration_date', $month)
            ->where('marketer_id', '>', 0) // 🔥 loại bỏ marketer_id = 0
            ->groupBy('marketer_id')
            ->orderByDesc('tong_doanh_so')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                $name = \App\Models\Admin::where('id', $item->marketer_id)->value('name');
                return [
                    'marketer_id'   => (int) $item->marketer_id,
                    'name'          => $name ?? 'Không xác định',
                    'tong_doanh_so' => (int) $item->tong_doanh_so,
                ];
            });

        return response()->json([
            'status' => true,
            'msg'    => "Top 3 marketer tháng $month/$year",
            'data'   => $topMarketer
        ]);
    }

    public function top3kythuat(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year  = $request->get('year', date('Y'));

        $topStaff = Bill::join('admin', 'bills.staff_care', '=', 'admin.id')
            ->select(
                'admin.name as staff_care',
                DB::raw('SUM(bills.total_price) as tong_doanh_so')
            )
            ->whereYear('bills.registration_date', $year)
            ->whereMonth('bills.registration_date', $month)
            ->groupBy('admin.id', 'admin.name')
            ->orderByDesc('tong_doanh_so')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                $item->tong_doanh_so = (int) $item->tong_doanh_so;
                return $item;
            });
        if($topStaff->isEmpty()){
            return response()->json([
                'status' => true,
                'msg' => "Không có thông tin top kỹ thuật viên tháng $month/$year",

            ]);
        }

        return response()->json([
            'status' => true,
            'msg' => "Top 3 kỹ thuật viên tháng $month/$year",
            'data' => $topStaff
        ]);
    }

    public function hopDongMoi(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year  = $request->get('year', date('Y'));

        $contracts = Bill::whereYear('registration_date', $year)
            ->whereMonth('registration_date', $month)
            ->with([
                'customer:id,name',
                'service:id,name_vi' // lấy tên service qua quan hệ
            ])
            ->select('id', 'customer_id', 'service_id', 'total_price', 'registration_date')
            ->get()
            ->map(function ($item) {
                return [
                    'customer_name' => $item->customer->name ?? '',
                    'service_name'  => $item->service->name_vi ?? '',
                    'doanh_so'      => (int) $item->total_price,
                    'ngay_ky_hd'    => $item->registration_date ?? '',
                ];
            });

        return response()->json([
            'status' => true,
            'msg' => "Hợp đồng mới tháng $month/$year",
            'data' => $contracts
        ]);
    }
    public function topDichVu(Request $request)
    {
        $topServices = Bill::with('service')
            ->whereNotNull('service_id')
            ->select('service_id', DB::raw('COUNT(id) as so_hop_dong'), DB::raw('SUM(total_price) as tong_doanh_so'))
            ->groupBy('service_id')
            ->orderByDesc('tong_doanh_so')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return [
                    'service_name'   => $item->service->name_vi ?? '', // giống ở hopDongMoi
                    'so_hop_dong'    => (int) $item->so_hop_dong,
                    'tong_doanh_so'  => (int) $item->tong_doanh_so,
                ];
            });

        return response()->json([
            'status' => true,
            'msg' => 'Top 3 dịch vụ có doanh số cao nhất',
            'data' => $topServices
        ]);
    }


    public function baoCaoSanPham(Request $request)
    {
        try {
            // --- Xử lý tham số thời gian ---
            $start = $request->query('start');
            $end = $request->query('end');
            $month = $request->query('month');
            $year = $request->query('year');

            $query = Bill::query();

            // --- Mặc định giá trị ngày trả về ---
            $startDate = null;
            $endDate = null;

            // Lọc theo khoảng start-end
            if ($start && $end) {
                $query->whereBetween(DB::raw('DATE(created_at)'), [$start, $end]);
                $startDate = $start;
                $endDate = $end;
            }
            // Lọc theo tháng + năm
            elseif ($month && $year) {
                $query->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year);
                $startDate = date("Y-m-01", strtotime("$year-$month-01"));
                $endDate   = date("Y-m-t", strtotime("$year-$month-01"));
            }
            // Lọc theo năm
            elseif ($year) {
                $query->whereYear('created_at', $year);
                $startDate = "$year-01-01";
                $endDate   = "$year-12-31";
            }

            // --- Tổng hợp doanh số, doanh thu ---
            $tongHopDong = (clone $query)->count();
            $tongDoanhSo = (clone $query)->sum('total_price');
            $tongDoanhThu = (clone $query)->sum('total_received');

            $totalAll = $tongDoanhSo > 0 ? $tongDoanhSo : 1;

            // --- Lấy top 5 sản phẩm ---
            $top5 = (clone $query)
                ->select('product_or_service', 'image_product', DB::raw('SUM(total_price) as tong_doanh_thu'))
                ->groupBy('product_or_service', 'image_product')
                ->orderByDesc('tong_doanh_thu')
                ->limit(5)
                ->get();

            // --- Tính doanh thu phần "Khác" ---
            $topIds = $top5->pluck('product_or_service')->toArray();
            $khacDoanhThu = (clone $query)->whereNotIn('product_or_service', $topIds)->sum('total_price');

            // --- Map dữ liệu top 5 ---
            $topSanPham = $top5->map(function ($item) use ($totalAll) {
                return [
                    'san_pham'   => $item->product_or_service ?? '',
                    'doanh_thu'  => (int)($item->tong_doanh_thu ?? 0),
                    'phan_tram'  => round(($item->tong_doanh_thu / $totalAll) * 100, 2),
                    'image'      => $item->image_product
                        ? 'https://test101.khoweb.top/filemanager/userfiles/product/' . $item->image_product
                        : '',
                ];
            })->toArray();

            // --- Thêm "Khác" ---
            if ($khacDoanhThu > 0) {
                $topSanPham[] = [
                    'san_pham'  => 'Khác',
                    'doanh_thu' => (int) $khacDoanhThu,
                    'phan_tram' => round(($khacDoanhThu / $totalAll) * 100, 2),
                    'image'     => '',
                ];
            }

            // --- Trả kết quả ---
            return response()->json([
                'status' => true,
                'msg'    => 'Báo cáo sản phẩm',
                'data'   => [
                    'start_date'     => $startDate,
                    'end_date'       => $endDate,
                    'tong_hop_dong'  => $tongHopDong,
                    'tong_doanh_so'  => (int) $tongDoanhSo,
                    'tong_doanh_thu' => (int) $tongDoanhThu,
                    'top_san_pham'   => $topSanPham,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sanPhamBanTrongKy(Request $request)
{
    $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now()->subDays(120);
    $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : now();

    $bills = Bill::whereBetween('created_at', [$startDate, $endDate])->get();

    $productData = [];

    foreach ($bills as $bill) {
        if ($bill->product_or_service) {
            $products = explode(',', $bill->product_or_service);
            foreach ($products as $product) {
                $product = trim($product);
                if (!isset($productData[$product])) {
                    $productData[$product] = [
                        'name' => $product,
                        'group' => $bill->service_name2 ?? 'Khác', // nhóm sản phẩm
                        'so_luong_ban' => 0,
                        'doanh_so' => 0,
                        'doanh_thu' => 0,
                        'loi_nhuan' => 0
                    ];
                }

                // Mỗi sản phẩm tính 1 cái
                $productData[$product]['so_luong_ban'] += 1;

                // Doanh số = tổng tiền bill chia đều cho các sản phẩm
                $pricePerProduct = $bill->total_price / count($products);
                $receivedPerProduct = $bill->total_received / count($products);
                $costPerProduct = $bill->exp_price / count($products); // giả sử chi phí = exp_price

                $productData[$product]['doanh_so'] += $pricePerProduct;
                $productData[$product]['doanh_thu'] += $receivedPerProduct;
                $productData[$product]['loi_nhuan'] += $receivedPerProduct - $costPerProduct;
            }
        }
    }

    // Sắp xếp giảm dần theo số lượng bán
    usort($productData, function($a, $b) {
        return $b['so_luong_ban'] <=> $a['so_luong_ban'];
    });
   $number= 5;
    // Lấy Top 3 sản phẩm bán chạy
    $top = array_slice($productData, 0, $number);

    return response()->json([
        'status' => true,
        'msg' => 'Top '.$number.' sản phẩm bán trong kỳ',
        'data' => [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'top_san_pham' => $top
        ]
    ]);
}








}