<?php

namespace App\CRMDV\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\CommonHelper;
use App\Models\District;
use App\Models\Ward;
use Auth;
use DB;
use Illuminate\Http\Request;
use Mail;
use App\Http\Controllers\Admin\CURDBaseController;
use App\Http\Controllers\Admin\RoleController;
use App\CRMDV\Models\Admin;
use App\Models\RoleAdmin;
use App\CRMDV\Models\Setting;
use App\CRMDV\Models\Roles;
use App\CRMDV\Models\User;
use Session;
use Validator;
use App\CRMDV\Controllers\Helpers\CRMDVHelper;
use App\CRMDV\Models\Bill;
use App\CRMDV\Models\BillHistory;
use App\CRMDV\Models\BillFinance;
use App\CRMDV\Models\BillProgress;
use App\CRMDV\Models\BillProgressHistory;
use App\CRMDV\Models\Service;
use Carbon\Carbon;
use App\CRMDV\Models\BillReceipts;
use Cache;


class DashboardController extends Controller
{
    protected function checkToken(Request $request)
    {
        $bearer = $request->bearerToken();
        $admin = Admin::where('api_token', $bearer)->first();
        if (!$admin || $admin->status != 1) {
            return null;
        }

        return $admin;
    }
    // API: Thống kê chung (dashboard cho mobile)
    public function getIndex(Request $request)
    {
        // Kiểm tra token
        $admin = $this->checkToken($request);
        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => 'Token không hợp lệ hoặc bạn chưa được kích hoạt'
            ], 403);
        }
        try {
            // ví dụ: đếm số bill
            $totalBills   = Bill::count();
            $totalRevenue = Bill::sum('total_price');

            // nhóm theo dịch vụ
            $serviceStats = Bill::select('service_id', DB::raw('COUNT(*) as total'))
                ->groupBy('service_id')
                ->with('service:id,name_vi')
                ->get()
                ->map(function ($item) {
                    return [
                        'service' => optional($item->service)->name_vi,
                        'total'   => $item->total
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Thống kê dashboard',
                'data' => [
                    'total_bills'   => $totalBills,
                    'total_revenue' => $totalRevenue,
                    'by_service'    => $serviceStats
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Lỗi lấy dữ liệu dashboard',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // API: Upload file (cho mobile)
    public function ajax_up_file(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if (in_array($file->getClientOriginalExtension(), ['jpg','jpeg','png','gif'])) {
                $path = $file->store('uploads/'.date('Y/m/d'), 'public');
                return response()->json([
                    'status' => true,
                    'file_url' => url('storage/'.$path),
                    'file_name' => $path
                ]);
            }
            return response()->json([
                'status' => false,
                'message' => 'Sai định dạng file'
            ], 400);
        }
        return response()->json([
            'status' => false,
            'message' => 'Không có file upload'
        ], 400);
    }

    // API: Lấy dữ liệu location (tỉnh/huyện/xã)
    public function getDataLocation(Request $r, $table) {
        if ($table == 'districts') {
            $items = DB::table('districts')
                ->where('province_id', $r->province_id)
                ->pluck('name', 'id');
        } elseif ($table == 'wards') {
            $items = DB::table('wards')
                ->where('district_id', $r->district_id)
                ->pluck('name', 'id');
        } else {
            $items = [];
        }

        return response()->json([
            'status' => true,
            'data' => $items
        ]);
    }
}
