<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Library\JWT\JWTAuth;

class JWTAndSignature
{
    private $apiSecret;

    public function __construct()
    {
        $this->apiSecret = config('app.api_signature_secret');;// từ .env
    }

    public function handle(Request $request, Closure $next)
    {

     //  return response()->json(['dataa'=>$request->all()]);

        $token     = $request->bearerToken();
        $signature = $request->header('X-Signature');
        $timestamp = $request->header('X-Timestamp');

        // --- 1️⃣ Kiểm tra headers ---
//        if (!$token || !$signature || !$timestamp) {
//            return response()->json(['msg' => 'Thiếu header xác thực'], 401);
//        }

        if (!$token ) {
            return response()->json(['msg' => 'Thiếu Token xác thực'], 401);
        }
        if (!$signature ) {
            return response()->json(['msg' => 'Thiếu signature xác thực'], 401);
        }
        if (!$timestamp ) {
            return response()->json(['msg' => 'Thiếu timestamp xác thực'], 401);
        }

        // --- 2️⃣ Xác thực JWT ---
        try {
            $jwt = new JWTAuth();
            $user = $jwt->parseToken();
            if (!$user) return response()->json(['msg' => 'Người dùng không tồn tại'], 401);
            $request->merge(['currentUser' => $user]);
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage()], 401);
        }

        // ---  Kiểm tra timestamp ±5 phút ---
        if (!is_numeric($timestamp) || abs(time() - (int)$timestamp) > 300) {
            return response()->json(['msg' => 'Request hết hạn'], 401);
        }

        // ---  Sinh HMAC server ---
        $body = $request->getContent() ?: '';
//        $body = file_get_contents('php://input') ?: '';
        $path = $request->getPathInfo();
        $dataToSign =$body . $timestamp;
        $expectedSignature = hash_hmac('sha256', $dataToSign, $this->apiSecret);

//         --- 6 So sánh HMAC ---
        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['msg' => 'Chữ ký không hợp lệ',"status"=>"403"], 403);
        }

        return $next($request);
    }
}
