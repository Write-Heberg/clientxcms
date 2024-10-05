<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BannedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user('web')){
            if ($request->user('web')->isBanned()) {
                auth()->logout();
                return redirect()->route('login')->with('error', __('client.alerts.account_blocked'));
            }
            if ($request->user('web')->isSuspended()) {
                auth()->logout();
                return redirect()->route('login')->with('error', __('client.alerts.account_suspended'));
            }
        }
        return $next($request);
    }
}
