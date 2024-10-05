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
use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\Response;

class RecaptchaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $driver = setting('captcha_driver', 'none');
        if (!$this->isCaptchaIsUsable($request)) {
            return $next($request);
        } else {
            if (!$this->verifyIsValidate($request, $driver)) {
                return back()->with('error', 'Invalid captcha');
            }
        }
        if ($driver == 'recaptcha') {
            $recaptcha = new ReCaptcha(setting('captcha_secret_key'));
            $response = $recaptcha->verify($request->input('g-recaptcha-response'), $request->ip());
            if (!$response->isSuccess()) {
                return back()->with('error', 'Invalid captcha');
            }
        }
        if ($driver == 'hcaptcha' || $driver == 'cloudflare'){
            if (!$this->verifyCaptcha($request)){
                return back()->with('error', 'Invalid captcha');
            }
        }
        return $next($request);
    }

    private function verifyCaptcha(Request $request)
    {
        $key = setting('captcha_secret_key');
        $postKey = $key == 'hcaptcha' ? 'h-captcha-response' : 'cf-turnstile-response';
        $url = $key == 'hcaptcha' ? 'hcaptcha.com' : 'challenges.cloudflare.com/turnstile/v0';

        $code = $request->input($postKey);
        if (!$code) {
            return false;
        }
        $response = \Http::asForm()->post("https://$url/siteverify", [
            'secret' => $key,
            'response' => $code,
        ]);
        return $response->successful() && $response->json('success');
    }

    private function getProtectedUrls()
    {
        return [
            route('login'),
            route('register'),
            route('password.request'),
            route('auth.2fa')
        ];
    }

    private function isCaptchaIsUsable(Request $request): bool
    {
        if (setting('captcha_driver', 'none') == 'none') {
            return false;
        }
        if ($request->method() == 'GET') {
            return false;
        }
        if (!in_array($request->url(), $this->getProtectedUrls())) {
            return false;
        }
        return true;
    }

    private function verifyIsValidate(Request $request, string $driver)
    {
        if ($driver == 'recaptcha') {
            return $request->has('g-recaptcha-response');
        }
        if ($driver == 'hcaptcha' || $driver == 'cloudflare') {
            $key = $driver == 'hcaptcha' ? 'h-captcha-response' : 'cf-turnstile-response';
            return $request->has($key);
        }
        return false;
    }
}
