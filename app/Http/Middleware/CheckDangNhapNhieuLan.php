<?php

namespace App\Http\Middleware;

use Closure;

class CheckDangNhapNhieuLan
{
    /**
     * người làm:Phương
     * ngày 25/08/2025
     * Hàm này để check xem người dùng này còn có dữ liệu trong bảng session hay không nếu còn thì cho đăng nhập vào còn không có thì cho vào trang login
     * Các bước thực hiện:
     * Admin hay người nào đó có quyền vào admin -> đăng xuất tất cả -> cho đăng xuất tất  cả các thiết bị ra ->nếu đăng  xuất rồi ấn vào trang nào đó thì nó sẽ bắt đăng nhập
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(\Auth::guard('admin')->check()){
          $id=\Auth::guard('admin')->id();
            $currentSessionId = request()->session()->getId();
          $existSession = \App\Models\Sessions::where('user_id',$id)
              ->orderBy('last_activity', 'desc')
              ->where('ip_address', '=',  request()->ip())
              ->first();
            if($existSession){
//                dd($existSession);
                return $next($request);
            }else{
//                dd(123);
                \Auth::guard('admin')->logout();
                return \Redirect::guest('admin/login');
            }
        }else{
            return \Redirect::guest('admin/login');
        }
    }
}
