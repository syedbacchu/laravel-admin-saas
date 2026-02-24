<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiProtection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        logStore($request->fullUrl(). ' req data =>' , $request->all());
//         logStore($request->fullUrl().' req header =>', $request->header());
        $lang = $request->header('lang') ?? 'en';
        app()->setLocale($lang);

        $allowedOrigins = explode(',', env('CORS_ALLOW_ORIGIN', ''));
        $origin = $request->header('Origin');

        $apiKey = env('USER_API_SECRET_KEY', 'LARAVEL2026AB1183A8D3D1F487BA8E3D97EE6B1E81ED3C3DA5F284FAE7618976469452');

        $allowedIPs = explode(',', env('ALLOWED_IPS', ''));


        $originAllowed = (empty($allowedOrigins) && $origin === null) || in_array($origin, $allowedOrigins);
        $headerKeyMatches = ($request->header('userapisecret') && $request->header('userapisecret') === $apiKey) ||
            ($request->header('userpublickey') && $request->header('userpublickey') === $apiKey);
        $ipAllowed = empty($allowedIPs) || in_array($request->ip(), $allowedIPs);

        if ($originAllowed || $headerKeyMatches || $ipAllowed) {
            return $next($request);
        }

        return response()->json(['status' => 403, 'success' => false, 'message' => __('Access denied'), 'errorMessage' => __('Api Access denied'), 'data' => []], 403);

    }
}
