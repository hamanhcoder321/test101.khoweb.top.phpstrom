<?php
namespace App\Library\JWT;

use Exception;
use App\Models\Admin; 

class JWTAuth
{
    protected $secret;
    protected $ttl = 3600*5;
    

    public function __construct()
    {
        $this->secret = config('app.jwt_secret'); // JWT_SECRET trong .env

    }

    // Tạo token từ user
    public function fromUser($user)
    {
        $payload = [
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + $this->ttl,
        ];
        return $this->encode($payload);
    }

    // Lấy user từ token
    public function parseToken()
    {
        $header = request()->header('Authorization');
        if (!$header) throw new Exception('Token không được cung cấp');

        if (!preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            throw new Exception('Token không hợp lệ');
        }

        $token = $matches[1];
        $payload = $this->decode($token);

        $user = Admin::find($payload['sub']); // Hoặc User::find()
        if (!$user) throw new Exception('Người dùng không tồn tại');
        return $user;
    }

    // Encode payload thành JWT
    protected function encode($payload)
    {
        $header = json_encode(['typ'=>'JWT','alg'=>'HS256']);
        $base64UrlHeader = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $base64UrlPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->secret, true);
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    // Decode JWT
    protected function decode($jwt)
    {
        $parts = explode('.', $jwt);
        if(count($parts) != 3) throw new Exception('Token không hợp lệ');

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
        $payload = json_decode(base64_decode(strtr($base64UrlPayload, '-_', '+/')), true);

        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->secret, true);
        $check = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        if($check !== $base64UrlSignature) throw new Exception('Chữ ký token không hợp lệ');
        if(time() > $payload['exp']) throw new Exception('Token đã hết hạn');

        return $payload;
    }
}
