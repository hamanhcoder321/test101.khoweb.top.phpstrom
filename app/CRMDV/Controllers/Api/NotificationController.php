<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Models\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\DB;
class NotificationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'message'     => 'required|string',
            'type'        => 'sometimes|in:info,warning,promotion,system,order',
            'image'       => 'nullable|url',
            'url'         => 'nullable|url',
            'target'      => 'sometimes|in:all,vip,premium,one', // all / nhóm / 1 người
            'user_id'     => 'required_if:target,one|exists:users,id',
        ]);
        DB::beginTransaction();
        try {
            // 1. Tạo thông báo trong DB
            $noti = Notification::create([
                'title'         => $request->title,
                'message'       => $request->message,
                'type'          => $request->type ?? 'info',
                'image'         => $request->image,
                'url'           => $request->url,
                'data'          => $request->except(['title','message','type','image','url','target','user_id']),
                'target_user_id'=> $request->target === 'one' ? $request->user_id : null,
                'target_roles'  => in_array($request->target, ['vip','premium']) ? json_encode([$request->target]) : null,
            ]);
            $notifyService = new NotificationService();
            // 2. Gửi FCM ngay lập tức
            $this->sendFcmNotification($noti, $request->target, $request->user_id ?? null);
            DB::commit();

            return back()->with('success', 'Đã gửi thông báo thành công cho toàn bộ người dùng!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
    
}