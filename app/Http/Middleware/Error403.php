<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class EnsureAdminActive
{
    public function handle(Request $request, Closure $next)
    {
        // Xác thực Basic Auth
        $user = Auth::guard('admin')->onceBasic();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Kiểm tra active
        if ($user->status != 1) {
            return response()->json([
                'status' => false,
                'message' => 'Admin không active'
            ], 403);
        }

        return $next($request);
    }
}
