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
use Illuminate\Support\Facades\Auth;

class ForceLoginMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (setting('force_login_client') == 'true') {
            if (!$request->is($this->allowedRoutes())) {
                if (!Auth::check()) {
                    return redirect('/login');
                }
            }
        }
        return $next($request);
    }

    private function allowedRoutes()
    {
        return [
            'login',
            'register',
            'reset-password',
            'admin*',
            'verify-email',
            'forgot-password',
            'password/reset*',
            'password/email',
            'licensing/return'
        ];
    }
}
