<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Factory;

class FCMTestController extends Controller
{
    public function sendTestNotification(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        try {
            // Khởi tạo Firebase Messaging
            $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials.file'));
            $messaging = $factory->createMessaging();

            // Tạo nội dung thông báo
            $notification = Notification::create(
                '🔔 FCM Test',
                '✅ Backend Laravel gửi thông báo thành công!'
            );

            // Tạo message
            $message = CloudMessage::withTarget('token', $request->fcm_token)
                ->withNotification($notification)
                ->withData(['type' => 'test']);

            // Gửi FCM
            $messaging->send($message);

            return response()->json([
                'success' => true,
                'message' => 'FCM gửi thành công!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
