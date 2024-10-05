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

class MaintenanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is(admin_prefix('*')) || $request->is('licensing/return'))
            return $next($request);

        if ($request->is($this->getMaintenanceUrl())){
            \Session::put('maintenance_by_pass', true);
            return redirect()->to('/');
        }
        if ($this->isMaintenanceIsEnabled())
            return $this->renderMaintenancePage($request, $next);
        return $next($request);
    }

    private function isMaintenanceIsEnabled(): bool
    {
        if (\Session::has('maintenance_by_pass'))
            return false;
        return setting('maintenance_enabled', false);
    }

    private function getMaintenanceUrl(): string
    {
        $url = setting('maintenance_url');
        if ($url === null)
            return '';
        if ($url[0] == '/')
            return substr($url, 1);
        return $url;
    }

    private function renderMaintenancePage(Request $request, Closure $next): Response
    {
        if ($request->expectsJson())
            return response()->json(['message' => setting('in_maintenance_message')], 503);
        return response(view('maintenance'), 503, [
            'Retry-After' => 60
        ]);
    }
}
