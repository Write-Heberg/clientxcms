<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Auth;

use App\Rules\Valid2FACodeRule;
use Illuminate\Http\Request;

class TwoFactorAuthenticationController
{
    public function show()
    {
        return view('admin.auth.2fa');
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            '2fa' => ['required', 'string', new Valid2FACodeRule()],
        ]);
        if (auth('admin')->user()->isValidate2FA($request->input('2fa'))){
            \Session::put('2fa_verified', true);
            return redirect()->intended(admin_prefix('dashboard'));
        }
        return redirect()->route('admin.auth.2fa')->withErrors(['2fa' => __('validation.2fa_code')]);
    }
}
