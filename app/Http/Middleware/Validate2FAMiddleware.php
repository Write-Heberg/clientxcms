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
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class Validate2FAMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!is_installed()){
            return $next($request);
        }
        if (auth('web')->user() && !Session::has('autologin')){
            if (auth('web')->user()->twoFactorEnabled() && !auth('web')->user()->twoFactorVerified()) {
                if ($request->route()->uri() !== '2fa') {
                    return redirect()->route('auth.2fa');
                }
            }
        }
        if (auth('admin')->user() && !Session::has('autologin')){
            if (auth('admin')->user()->twoFactorEnabled() && !auth('admin')->user()->twoFactorVerified()) {
                if ($request->route()->uri() !== admin_prefix('2fa')) {
                    return redirect()->route('admin.auth.2fa');
                }
            }
        }
        return $next($request);
    }
}
