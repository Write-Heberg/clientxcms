<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class isAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) {
            return $request->expectsJson()
                ? abort(401, 'Unauthorized')
                : redirect()->guest(route('admin.login'));
        }
        if (!Auth::guard('admin')->user()->isActive()){
            Auth::guard('admin')->logout();
            \Session::flash('error', 'Your account is not active');
            return $request->expectsJson()
                ? abort(401, 'Unauthorized')
                : redirect()->guest(route('admin.login'));
        }
        return $next($request);
    }
}
