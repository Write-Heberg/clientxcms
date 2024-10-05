<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account\Customer;
use App\Services\Account\AccountEditService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = AccountEditService::rules($request->country, true, true);
        if (setting('register_toslink')) {
            $rules['accept_tos'] = ['accepted'];
        }
        $request->validate($rules);

        if (setting('allow_registration', true) === false) {
            return back()->with('error', __('auth.register.error_registration_disabled'));
        }
        $bannedEmails = collect(explode(',', setting('banned_emails', '')));
        if ($bannedEmails->contains($request->email) || $bannedEmails->contains(explode('@', $request->email)[1] ?? '')) {
            return back()->with('error', __('auth.register.error_banned_email'));
        }
        $user = Customer::create([
            'email' => strtolower($request->email),
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'address' => $request->address,
            'address2' => $request->address2,
            'city' => $request->city,
            'zipcode' => $request->zipcode,
            'region' => $request->region,
            'phone' => $request->phone,
            'country' => $request->country,
            'password' => Hash::make($request->password),
        ]);

        if (setting('auto_confirm_registration', false) === true) {
            $user->markEmailAsVerified();
        }
        event(new Registered($user));
        Auth::login($user);

        if ($request->wantsJson()){
            return response()->noContent();
        }
        if ($request->has('redirect')){
            $user->attachMetadata('origin_url', $request->get('redirect'));
            return redirect()->away($request->get('redirect'));
        }
        if (setting('auto_confirm_registration', false) === true)
            return redirect()->route('front.client.index')->with('success', __('auth.register.success'));
        else
            return redirect()->route('front.client.index')->with('success', __('auth.register.success_need_validation'));

    }
}
