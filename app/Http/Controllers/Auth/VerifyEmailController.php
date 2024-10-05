<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Store\Basket\Basket;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $basket = Basket::getBasket();
        $redirect = route('front.client.index').'?verified=1';
        if ($basket && $basket->items()->count() != 0) {
            $redirect = route('front.store.basket.checkout');
        }
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(
                $redirect
            );
        }

        if ($request->user()->markEmailAsVerified()) {

            event(new Verified($request->user()));
        }

        return redirect()->intended(
            $redirect
        )->with('success', __('auth.register.success_validation_done'));
    }
}
