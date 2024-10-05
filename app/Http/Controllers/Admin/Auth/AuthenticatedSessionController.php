<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Models\Admin\Admin;
use Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        if ($request->has('redirect'))
            return redirect()->away($request->get('redirect'));
        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function confirmPassword(Request $request)
    {
        return view('admin.auth.confirm-password');
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);
        $hash = \Hash::driver('bcrypt');
        if (!$hash->check($request->password, $request->user('admin')->password)) {
            return back()->withErrors([
                'password' => [__('auth.password')]
            ]);
        }
        $request->session()->passwordConfirmed();
        return redirect()->intended();
    }

    public function autologin(Request $request, string $id, string $token)
    {
        if (!Auth::guard('admin')->guest()) {
            return redirect()->route('admin.dashboard');
        }
        if (!Admin::where('id', $id)->exists()) {
            return redirect()->route('admin.login');
        }
        if (!$request->hasValidSignature()){
            return redirect()->route('admin.login');
        }
        $admin = Admin::findOrFail($id);
        if ($admin->getMetadata('autologin_key') !== $token) {
            return redirect()->route('admin.login');
        }
        if ($admin->getMetadata('autologin_expires_at') < now()) {
            $admin->forgetMetadata('autologin_key');
            $admin->forgetMetadata('autologin_expires_at');
            return redirect()->route('admin.login');
        }
        if ($admin->getMetadata('autologin_unique')) {
            $admin->forgetMetadata('autologin_key');
            $admin->forgetMetadata('autologin_expires_at');
        }
        \Session::put('autologin', true);
        Auth::guard('admin')->login($admin);
        return redirect()->route('admin.dashboard')->with('success', __('admin.dashboard.autologin_success', ['name' => $admin->name]));
    }

}
