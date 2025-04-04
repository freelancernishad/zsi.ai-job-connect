<?php

namespace App\Http\Middleware;

use Closure;

class IpAddressProtectionMiddleware
{


    protected $allowedIPs = [
        '',
        'http://test.localhost:8000',
        'http://localhost:3000',
        'https://jobconnectusa.com',
        'https://www.jobconnectusa.com',
        'https://job-connect-pi.vercel.app',
        'https://admin.jobconnectusa.com',
        'https://www.admin.jobconnectusa.com',
        'http://localhost:3001',
        'http://localhost:8000',
        'https://dev.d2djobg98sxogp.amplifyapp.com',
        'https://www.dev.d2djobg98sxogp.amplifyapp.com',






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
