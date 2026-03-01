<?php

namespace App\CRMDV\Controllers\Api;

use App\CRMDV\Models\Room;
use App\Http\Controllers\Admin\CURDBaseController;
use App\CRMDV\Models\AttendanceLog;
use App\Library\JWT\Facades\JWTAuth;
use App\CRMDV\Models\Admin;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends CURDBaseController
{
    protected $module = [
        'code'       => 'attendance',
        'table_name' => 'attendance_logs',
        'label'      => 'Chấm công',
        'modal'      => '\App\CRMDV\Models\AttendanceLog',
        'list'       => [
            ['name' => 'user_name',      'type' => 'custom', 'td' => 'CRMDV.attendance.list.user_name', 'label' => 'Nhân viên'],
            ['name' => 'department',     'type' => 'text',    'label' => 'Phòng ban'],
            ['name' => 'check_in',       'type' => 'datetime', 'label' => 'Check-in'],
            ['name' => 'check_out',      'type' => 'datetime', 'label' => 'Check-out'],
            ['name' => 'is_late',        'type' => 'boolean',  'label' => 'Đi muộn'],
            ['name' => 'is_offsite',     'type' => 'boolean',  'label' => 'Công tác'],
            ['name' => 'check_in_address','type' => 'text',    'label' => 'Địa điểm check-in'],
            ['name' => 'reason_status',  'type' => 'custom',  'td' => 'CRMDV.attendance.list.reason_status', 'label' => 'Lý do'],
        ],
        'form' => []
    ];

    protected static $officeStartTime = '08:30:00';

    public function checkIn(Request $request)
    {
        $request->validate([
            'lat'     => 'required|numeric',
            'lng'     => 'required|numeric',
            'address' => 'required|string|max:500',
            'offsite' => 'sometimes|boolean'
        ]);

        $user  = JWTAuth::parseToken();
        $today = Carbon::today()->toDateString();

        $log = AttendanceLog::where('user_id', $user->id)
            ->whereDate('check_in', $today)
            ->first();

        if ($log && $log->check_in) {  // ← LỖI Ở ĐÂY!
            return $this->error('Bạn đã check-in hôm nay rồi!');
        }
        $now    = Carbon::now();
        $isLate = $now->format('H:i:s') > self::$officeStartTime;

        $log = $log ?? new AttendanceLog();
        $log->user_id           = $user->id;
        $log->check_in          = $now;
        $log->check_in_lat      = $request->lat;
        $log->check_in_lng      = $request->lng;
        $log->check_in_address  = $request->address;
        $log->is_late           = $isLate;
        $log->is_offsite        = (bool)$request->offsite;
        $log->save();

        return $this->success('Check-in thành công lúc ' . $now->format('H:i'), [
            'check_in_time' => $now->format('H:i'),
            'is_late'       => $isLate
        ]);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'lat'     => 'required|numeric',
            'lng'     => 'required|numeric',
            'address' => 'required|string|max:500',
        ]);

        $user  = JWTAuth::parseToken();
        $today = Carbon::today()->toDateString();

        $log = AttendanceLog::where('user_id', $user->id)
            ->whereDate('check_in', $today)
            ->first();

        if (!$log->check_in) return $this->error('Bạn chưa check-in hôm nay!');
        if ($log->check_out)   return $this->error('Bạn đã check-out rồi!');

        $now = Carbon::now();
        $log->check_out          = $now;
        $log->check_out_lat      = $request->lat;
        $log->check_out_lng      = $request->lng;
        $log->check_out_address  = $request->address;
        $log->save();

        return $this->success('Check-out thành công lúc ' . $now->format('H:i'));
    }
    public function myHistory(Request $request)
    {
        $user = JWTAuth::parseToken();

        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $fromDate = $request->get('start');
        $toDate   = $request->get('end');

        $perPage  = $request->get('limit', 10);

        $query = AttendanceLog::where('user_id', $user->id);

        if ($fromDate && $toDate) {
            $query->whereBetween('check_in', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } else {
            // 👉 Nếu không truyền from_date & to_date thì lọc theo tháng/năm
            $query->whereMonth('check_in', $month)
                ->whereYear('check_in', $year);
        }

        $logs = $query
            ->orderBy('check_in', 'desc')
            ->paginate($perPage);

        // Format dữ liệu
        $logs->getCollection()

            ->transform(function ($log) {
            return $this->formatLogResponse($log);
        });
        return $this->success('Lịch sử chấm công', [
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
                'last_page'    => $logs->lastPage(),
            ]
        ]);
    }

    public function formatLogResponse($log, $isAdmin = false)
    {
        return [
            'id' => $log->id,
          //  'user_name' => $log->user_id ?? '',
           // 'department' => $log->department ?? '',
          //  'image'=>$log->user_image ? url('/').'/'.$log->user_image : null,
          //  'user_code' => $log->user_code ?? '',
        //    'tel' => $log->tel ?? '',
            'late'=> $log->is_late == 1 ? "Đi muộn":"Đúng giờ",
            'ngay_check'=> $log->check_in ? Carbon::parse($log->check_in)->format('d/m/Y') : null,
            'dia_chi'=> $log->check_in_address??"",
            'check_in' => $log->check_in ? Carbon::parse($log->check_in)->format('H:i') : null,
            'check_out' => $log->check_out ? Carbon::parse($log->check_out)->format('H:i') : null,
        ];
    }
    public  function worktime($id)
    {
        switch ($id)
        {
            case 0:
                return "parttime";
    case 1:
             return "fulltime";
  case 2:
             return "online";
  case 3:
                return "khác";
            default:
                return 'không xác định';

        }
    }

    public function myHistoryId(Request $request,$id)
    {
        $logs = AttendanceLog::where('id', $id)->first();
        $user = $logs->user;

        if(!$user){
            return $this->error('Không tìm thấy người dùng !');
        }
        $room = Room::where('id', $user->room_id)->first();
        $room_name = $room ? $room->name : "";

//       return $logs;


        return $this->success('Lịch sử chấm công - detail ', [
            'check_in'=>$logs->check_in,
            'check_out'=>$logs->check_out,
            'check_in_address'=>$logs->check_in_address,
            'check_out_address'=>$logs->check_out_address,
            'reason'=>$logs->reason??"",
            'name_user'=>$user->name,
            'tel_user'=>$user->tel,
            'work_time'=>$this->worktime($user->work_time),
            'room_name'=>$room_name,
            'image'=> $user->image,
        ]);
    }


    // ===================================================================
    //    CHI TIẾT 1 NGÀY
    // ===================================================================
    public function detail($id)
    {
        $currentUser = JWTAuth::parseToken();
        $log = AttendanceLog::with('user')->findOrFail($id);

        if (!$log) return $this->error('Không tìm thấy bản ghi');
        if ($log->user_id != $currentUser->id && !$currentUser->is_admin) {
            return $this->error('Không có quyền xem!', 403);
        }

        $in  = $in  = $log->check_in ? Carbon::parse($log->check_in) : null;
        $out = $log->check_out ? Carbon::parse($log->check_out) : null;
        $worked = $in && $out ? $out->diffInMinutes($in) : 0;

        return $this->success('Chi tiết chấm công', [
            'date'              => $in->format('d/m/Y (l)'),
            'user_name'         => $log->user->name ?? '',
            'phone'             => $log->user->phone ?? '',
            'department'        => $log->user->department ?? '',
            'check_in'          => $in->format('H:i:s') ?? '-',
            'check_in_address'  => $log->check_in_address ?? '',
            'check_out'         => $out->format('H:i:s') ?? '-',
            'check_out_address' => $log->check_out_address ?? '',
            'is_late'          => (bool)$log->is_late,
            'is_offsite'       => (bool)$log->is_offsite,
            'worked_hours'      => $worked ? round($worked/60, 1).' giờ' : '-',
            'status'            => $worked >= 480 ? 'Full day' : ($worked > 0 ? 'Part time' : 'Không chấm'),
            'reason'            => $log->reason ?? '',
            'reason_approved'   => (bool)$log->reason_approved,
            'approved_by'       => $log->reason_approved_by ? Admin::find($log->reason_approved_by)->name ?? '' : '',
            'updated_by'        => $log->updated_by_name ?? '',
        ]);
    }
    //    USER GỬI LÝ DO

    public function storeReason(Request $request, $logId)
    {
//        $request->validate(['reason' => 'required|string|max:1000']);
        $user = JWTAuth::parseToken();
        $log = AttendanceLog::where('id', $logId)->first();
        $user_seen_id =$log->user_id;
        $log->reason = $request->reason;
        $log->reason_approved = $request->accept ? true:false;
        $log->reason_approved_by =$request->accept ? (($user->id == $user_seen_id)?$user->id:null) :null;
        $log->is_late = 1;
        $log->save();
        if($request->accept){
            return $this->success('Admin đã duyệt li do đi muộn ');
        }

       else return $this->success('User Đã gửi lý do, đang chờ duyệt');
    }

    // ===================================================================
    //    ADMIN: SỬA VỊ TRÍ
    // ===================================================================
    public function updateLocation(Request $request, $logId)
    {
        $request->validate([
            'type' => 'required|in:check_in,check_out',
            'lat'      => 'required|numeric',
            'lng'      => 'required|numeric',
            'address'   => 'required|string|max:500',
        ]);
        $admin = JWTAuth::parseToken();
        $log = AttendanceLog::findOrFail($logId);

        if ($request->type === 'check_in') {
            $log->check_in_lat     = $request->lat;
            $log->check_in_lng     = $request->lng;
            $log->check_in_address  = $request->address;
        } else {
            $log->check_out_lat     = $request->lat;
            $log->check_out_lng     = $request->lng;
            $log->check_out_address = $request->address;
        }

        $log->updated_by_name = $admin->name ?? $admin->email;
        $log->save();

        return $this->success('Cập nhật vị trí thành công');
    }

    // ===================================================================
    //    ADMIN: DUYỆT LÝ DO
    // ===================================================================
    public function approveReason($logId)
    {
        $admin = JWTAuth::parseToken();
        $log = AttendanceLog::findOrFail($logId);
        $log->reason_approved    = 1;
        $log->reason_approved_by = $admin->id;
        $log->reason_approved_at = now();
        $log->is_late           = false; // bỏ đi muộn nếu hợp lệ
        $log->save();
        return $this->success('Admin Đã duyệt lý do ở list');
    }

    // ===================================================================
    //    ADMIN: DANH SÁCH TẤT CẢ + LỌC CHUẨN
    // ===================================================================
    public function adminAll(Request $request, $id = null)
    {
        $officeStart     = '08:30:00';
        $perPage         = $request->get('limit', 10);
        $department_name = $request->get('phong');
        $keyword         = $request->get('keyword');
        $from            = $request->get('from');
        $to              = $request->get('to');

        $query = AttendanceLog::query()
            ->leftJoin('admin', 'attendance_logs.user_id', '=', 'admin.id')
            ->select([
                'attendance_logs.*',
                'admin.name',
                'admin.may_cham_cong_id',
                'admin.code',
                'admin.room_id',
            ])
            ->orderBy('attendance_logs.check_in', 'desc');

        // 🔹 lọc theo nhân viên
        if ($id) {
            $query->where('attendance_logs.user_id', $id);
        }

        // 🔹 tìm theo tên nhân viên
        if ($keyword) {
            $query->where('admin.name', 'like', "%$keyword%");
        }

        // 🔹 lọc theo phòng ban
        if ($department_name) {
            $departmentId = Room::where('name', $department_name)->value('id');
            if ($departmentId) {
                $query->where('admin.room_id', $departmentId);
            }
        }

        // 🔹 lọc theo khoảng thời gian
        if ($from && $to) {
            $query->whereBetween('attendance_logs.check_in', [$from, $to]);
        } elseif ($from) {
            $query->where('attendance_logs.check_in', '>=', $from);
        } elseif ($to) {
            $query->where('attendance_logs.check_in', '<=', $to);
        }

        $logs = $query->paginate($perPage);

        $data = [];

        foreach ($logs as $log) {

            $checkIn = $log->check_in;
            $lateMinutes = 0;

            if ($checkIn) {
                $timeIn = date('H:i:s', strtotime($checkIn));
                if ($timeIn > $officeStart) {
                    $lateMinutes = round(
                        (strtotime($timeIn) - strtotime($officeStart)) / 60
                    );
                }
            }

            $data[] = [
                'id'               => $log->id,
                'name'             => $log->name ?? '',
                'code'             => $log->code ?? '',
                'may_cham_cong_id' => (int) $log->may_cham_cong_id,
                'check_time'       => $checkIn
                    ? date('H:i:s d/m/Y', strtotime($checkIn))
                    : null,
                'late_time'        => $lateMinutes > 0
                    ? $lateMinutes . ' phút'
                    : '0 phút',
                'reason'           => $lateMinutes > 0 ? ($log->reason ?? '') : '',
                'approved'         => $lateMinutes > 0
                    ? ($log->reason_approved == 1 ? 'Đã duyệt' : 'Chưa duyệt')
                    : '',
            ];
        }

        return response()->json([
            'status' => 'success',
            'msg'    => 'Danh sách chấm công',
            'data'   => $data,
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
                'last_page'    => $logs->lastPage(),
            ]
        ]);
    }




    // Helper format chung


    public function getLateReasons(Request $request)
    {
        $month = $request->get('month');
        $year  = $request->get('year');

        $query = AttendanceLog::query()
            ->leftJoin('admin', 'attendance_logs.user_id', '=', 'admin.id')
            ->leftJoin('rooms', 'admin.room_id', '=', 'rooms.id')
            ->select([
                'attendance_logs.*',
                'admin.name as user_name',
                'admin.tel',
                'rooms.name as room',
            ])
            ->where(function ($q) {
                $q->where('attendance_logs.is_late', true)
                    ->orWhereNotNull('attendance_logs.reason');
            });

        // Lọc tháng/năm
        if ($month) {
            $query->whereMonth('attendance_logs.check_in', $month);
        }
        if ($year) {
            $query->whereYear('attendance_logs.check_in', $year);
        }

        $logs = $query->orderBy('attendance_logs.check_in', 'desc')->get();

        $data = $logs->map(function ($log) {
            return [
                'id'        => $log->id,
                'user_name' => $log->user_name,
                'tel'       => $log->tel,
                'room'=> $log->room,
                'date'      => $log->check_in ? Carbon::parse($log->check_in)->format('d/m/Y') : null,
                'check_in'  => $log->check_in ? Carbon::parse($log->check_in)->format('H:i') : null,
                'is_late'   => (bool)$log->is_late,

                // Lý do
                'reason' => $log->reason,

                // Trạng thái duyệt
                'reason_status' => $log->reason
                    ? ($log->reason_approved ? 'Đã duyệt' : 'Chờ duyệt')
                    : 'Không có',

                'reason_approved'    => (bool)$log->reason_approved,
                'reason_approved_by' => $log->reason_approved_by,
                'reason_approved_at' => $log->reason_approved_at,
            ];
        });

        return $this->success('Danh sách lý do đi muộn', $data);
    }

    // Helper trả JSON nhanh
    private function success($msg, $data = [], $extra = [])
    {
        return response()->json(array_merge([
            'status' => true,
            'msg'    => $msg,
            'data'   => $data
        ], $extra));
    }

    private function error($msg, $code = 400)
    {
        return response()->json(['status' => false, 'msg' => $msg], $code);
    }
    public function mySummary(Request $request)
    {
        $user = JWTAuth::parseToken();

        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        // Log hôm nay
        $todayLog = AttendanceLog::where('user_id', $user->id)
            ->whereDate('check_in', now())
            ->orderBy('check_in', 'desc')
            ->first();
        $todayCheckIn  = $todayLog->check_in??'';
        $todayCheckOut = $todayLog->check_out??'';

        // Tổng check in
        $totalCheckIn = AttendanceLog::where('user_id', $user->id)
            ->whereMonth('check_in', $month)
            ->whereYear('check_in', $year)
            ->whereNotNull('check_in')
            ->count();

        // Tổng check out
        $totalCheckOut = AttendanceLog::where('user_id', $user->id)
            ->whereMonth('check_out', $month)
            ->whereYear('check_out', $year)
            ->whereNotNull('check_out')
            ->count();

        $totalWorkLogs = $totalCheckIn + $totalCheckOut;
        $totalWorkDays = $totalWorkLogs / 2;

        $lateLogs = AttendanceLog::where('user_id', $user->id)
            ->whereMonth('check_in', $month)
            ->whereYear('check_in', $year)
            ->where('is_late', true)
            ->count();
        $lateWithPermission = AttendanceLog::where('user_id', $user->id)
            ->whereMonth('check_in', $month)
            ->whereYear('check_in', $year)
            ->where('is_late', true)
            ->whereNotNull('reason')
            ->where('reason_approved', true)
            ->count();
        return $this->success('Tóm tắt chấm công', [
            'today' => [
                'check_in'  => $todayCheckIn??0,
                'check_out' => $todayCheckOut??0,
            ],
            'late_days'            => $lateLogs??0,
            'late_with_permission' => $lateWithPermission??0,
            'total_work_logs'      => $totalWorkLogs??0,
            'total_work_days'      => $totalWorkDays??0,
        ]);

    }

}