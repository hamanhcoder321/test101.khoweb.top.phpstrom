<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Signature
{
    private $apiSecret;

    public function __construct()
    {
        $this->apiSecret = config('app.api_signature_secret'); // lấy từ .env
    }

    public function handle(Request $request, Closure $next)
    {
        $signature = $request->header('X-Signature');
        $timestamp = $request->header('X-Timestamp');

        // --- 1️⃣ Kiểm tra headers ---
        if (!$signature || !$timestamp) {
            return response()->json(['msg' => 'Thiếu header xác thực'], 401);
        }

        // --- 2️⃣ Kiểm tra timestamp ±5 phút ---
        if (!is_numeric($timestamp) || abs(time() - (int)$timestamp) > 300) {
            return response()->json(['msg' => 'Request hết hạn'], 401);
        }

        // --- 3️⃣ Sinh HMAC server ---
        $body = $request->getContent() ?: '';
        $dataToSign = $body . $timestamp;
        $expectedSignature = hash_hmac('sha256', $dataToSign, $this->apiSecret);

        // --- 4️⃣ So sánh HMAC ---
        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['msg' => 'Chữ ký không hợp lệ'], 403);
        }

        return $next($request);
    }
}
