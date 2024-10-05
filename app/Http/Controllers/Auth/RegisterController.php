<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Auth;

use App\Helpers\Countries;
use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
    public function showForm()
    {
        if (setting('allow_registration', true) === false) {
            return back()->with('error', __('auth.register.error_registration_disabled'));
        }
        if (app('extension')->extensionIsEnabled('socialauth')) {
            $providers = \App\Addons\SocialAuth\Models\ProviderEntity::where('enabled', true)->get();
        } else {
            $providers = collect([]);
        }
        return view('front.auth.register', ['countries' => Countries::names(), 'providers' => $providers]);
    }
}
