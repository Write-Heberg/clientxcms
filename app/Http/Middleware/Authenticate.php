<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Closure;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }
        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                if ($this->auth->guard($guard)->user() instanceof MustVerifyEmail &&
                    ! $this->auth->guard($guard)->user()->hasVerifiedEmail()) {
                    $request->session()->flash('warning', __('client.alerts.email_not_verified', ['url' => route('front.emails.resend')]));
                }
                return $this->auth->shouldUse($guard);
            }
        }
        $request->session()->put('url.intended', $request->fullUrl());
        $this->unauthenticated($request, $guards);
    }
}
