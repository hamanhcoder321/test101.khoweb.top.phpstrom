<?php

namespace App\Services;

use App\Modules\GoiDichVu\Models\ThongBao;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected  $projectId;
    protected  $serviceAccountPath;
    public function __construct()
    {
        // Lấy đường dẫn file credentials từ .env
        $this->serviceAccountPath = storage_path('app/firebase/fcm-test101-firebase-adminsdk-fbsvc-db9d62bea1.json');

        if (!file_exists($this->serviceAccountPath)) {
            Log::error("Không tìm thấy file Firebase credentials tại: {$this->serviceAccountPath}");
        }
        $jsonKey = json_decode(file_get_contents($this->serviceAccountPath), true);
        $this->projectId = $jsonKey['project_id'] ?? '';
    }


    public function send($user, string $title, string $body, array $extraData = [])
    {
        // 1. Lưu thông báo vào DB
        $thongBao = ThongBao::create([
            'user_id' => $user->id,
            'noi_dung' => "{$title} - {$body}",
            'active' => 0,
        ]);

        // 2. Gửi FCM
        try {
            $accessToken = $this->getAccessToken();
            $deviceToken = $user->fcm ?? null;
//            Log::info("🔔 Gửi FCM tới user_id={$user->id}, token={$deviceToken}");

            if ($deviceToken) {
                $client = new Client();
                $client->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'message' => [
                            'token' => $deviceToken,
                            'notification' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                            'data' => collect(array_merge($extraData, [
                                'thong_bao_id' => (string) $thongBao->id,
                                'user_id'      => (string) $user->id,
                            ]))->mapWithKeys(function ($v, $k) {
                                return [$k => (string) $v];
                            })->toArray(),
//                            'android' => [
//                                'priority' => 'HIGH',
//                                'notification' => [
//                                    'channel_id' => 'noti_sound_channel_v3',
//                                    'sound' => 'noti',
//                                ],
//                            ],



                        ],
                    ],
                ]);
            } else {
                Log::warning("⚠️ User {$user->id} không có device token FCM.");
            }
        } catch (\Throwable $e) {
            Log::error(" Gửi FCM thất bại: " . $e->getMessage());
        }

        return $thongBao;
    }

    public function sendToAll(string $title, string $body, array $extraData = [], string $image = null): ThongBao
    {
        // 1. Lưu 1 bản ghi thông báo chung (không gắn user_id)
        $thongBao = ThongBao::create([
            'user_id'   => null,                    // null = thông báo toàn hệ thống
            'noi_dung'  => $title . ' - ' . $body,
            'active'    => 0,
            'created_at'=> now(),
        ]);

        // 2. Gửi FCM qua Topic (tất cả app Flutter đều phải subscribe topic này)
        try {
            $accessToken = $this->getAccessToken();

            $client = new Client();

            $payload = [
                'message' => [
                    'topic' => 'all_users',    // <<<--- CHỈ 1 DÒNG NÀY GỬI ĐẾN HÀNG TRIỆU NGƯỜI
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                   'data' => collect($extraData)->merge([
                       'thong_bao_id' => (string) $thongBao->id,
                       'type'         => 'global',
                       'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                   ])->mapWithKeys(function ($v, $k) {
                       return [$k => (string)$v];
                   })->toArray(),
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'channel_id'   => 'noti_sound_channel_v3',
                            'sound'        => 'noti',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'noti.caf',
                            ],
                        ],
                    ],
                ]
            ];

            // Nếu có ảnh thì thêm vào
            if ($image) {
                $payload['message']['notification']['image'] = $image;
            }

            $client->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);

            Log::info("Gửi thông báo toàn hệ thống thành công!", [
                'thong_bao_id' => $thongBao->id,
                'title'        => $title,
                'users'        => 'ALL'
            ]);

        } catch (\Throwable $e) {
            Log::error("Gửi thông báo toàn hệ thống thất bại: " . $e->getMessage());
        }

        return $thongBao;
    }

    private function getAccessToken(): string
    {
        $jsonKey = json_decode(file_get_contents($this->serviceAccountPath), true);

        $header = [
            "alg" => "RS256",
            "typ" => "JWT",
        ];

        $now = time();
        $claim = [
            "iss" => $jsonKey['client_email'],
            "scope" => "https://www.googleapis.com/auth/firebase.messaging",
            "aud" => "https://oauth2.googleapis.com/token",
            "exp" => $now + 3600,
            "iat" => $now,
        ];
        // Encode base64url
        $jwtHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $jwtClaim = rtrim(strtr(base64_encode(json_encode($claim)), '+/', '-_'), '=');

        // Ký bằng private key
        $signature = '';
        openssl_sign("{$jwtHeader}.{$jwtClaim}", $signature, $jsonKey['private_key'], "sha256");
        $jwtSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        $jwt = "{$jwtHeader}.{$jwtClaim}.{$jwtSignature}";
        // Gọi OAuth2 để lấy access token
        $client = new Client();
        $response = $client->post("https://oauth2.googleapis.com/token", [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ],
        ]);
        $data = json_decode($response->getBody(), true);
        return $data['access_token'];

    }

    public function sendTest($user, $title, $body, $extraData = [])
    {
        // 1. Lưu DB
        $thongBao = ThongBao::create([
            'user_id'   => $user->id,
            'noi_dung'  => $title . ' - ' . $body,
            'active'    => 0,
        ]);

        // fix cứng cái fcm
        //$deviceToken = 'ej--D8fWS0elDo3wc6ES9E:APA91bGOmb0cBLJGwCorxgwg8UQBzhmfbo5VdhZRV9VCKk5K35uoD2hkSO7nNMZrg5-oMmfhb-0Lnn5rr_SM3vdW1EgbdjH25EFWOq0q3rG07FjtLwFDESQ';
        $deviceToken=  'd663hPcSR9WKQZcrB35Sat:APA91bGIeiPuyQu7EHL74gr_9wgtJVazianY5HrB0tS98ibLdW6rdcB_clZV7CdKHodYgqHEiSyuiPeE_0nCIacc9wQET8yFKDrwcVE3rxBMhhiZZFmICw4';

        // 2. Gửi Firebase
        try {
            $accessToken = $this->getAccessToken();
            $deviceToken =$deviceToken ?? null;
            Log::info("Gửi tới user: {$user->id}, token: {$deviceToken}");
            if ($deviceToken) {
                $client = new Client();
                $client->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => [
                        'message' => [
                            'token' => $deviceToken,
                            'notification' => [
                                'title' => 'chào',

                            ],
                            'data' => array_merge($extraData, [
                                'thong_bao'=>'chào',
                            ]),
                            'android' => [
                                'priority' => 'HIGH',
                                'notification' => [
                                    'channel_id' => 'noti_sound_channel_v3',
                                    'sound' => 'noti',
                                ],
                            ],
                        ],
                    ],
                ]);
            }
        } catch (\Exception $e) {
            Log::error("FCM gửi thất bại: " . $e->getMessage());
        }

        return $thongBao;
    }




}
