<?php

namespace App\Http\Middleware;

use Closure;

class IpAddressProtectionMiddleware
{


    protected $allowedIPs = [
        '',
        'http://test.localhost:8000',
      





    ];


    public function handle($request, Closure $next)
    {
       $requestIP = $request->header('Origin');
        if (!in_array($requestIP, $this->allowedIPs)) {
            return response()->json([
                'message' => 'Access denied. Your IP is not allowed.',
            ], 403);
        }

        return $next($request);
    }
}