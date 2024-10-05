<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Middleware;

use App\Exceptions\LicenseInvalidException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LicenseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!is_installed() || $request->is('licensing/return')){
            return $next($request);
        }
        try {
            if (app('license')->getLicense(null, true) && app('license')->hasExpiredFile() && !\App::runningUnitTests()) {
                \Session::flash('error', 'Your license is expired. Please renew your license.');
                $oauth_url = app('license')->getAuthorizationUrl();
                return new \Illuminate\Http\Response(view('admin.auth.license', ['oauth_url' => $oauth_url]), 401);
            }
        } catch (LicenseInvalidException $e) {
            if (auth('admin')->check() && !\App::runningUnitTests()) {
                \Session::flash('error', $e->getMessage());
                $oauth_url = app('license')->getAuthorizationUrl();
                return new \Illuminate\Http\Response(view('admin.auth.license', ['oauth_url' => $oauth_url]), 401);
            }
        }
        return $next($request);
    }
}
