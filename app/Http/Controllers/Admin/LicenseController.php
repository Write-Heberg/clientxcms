<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin;
use App\Core\License\License;
use App\Exceptions\LicenseInvalidException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class LicenseController
{
    public function return(Request $request)
    {
        $code = $request->get('code');
        if (!$code) {
            return redirect()->route('admin.license.index')->with('error', __('admin.license.invalid'));
        }
        $response = app('license')->getAccessToken($code);
        if (array_key_exists('error_description', $response)) {
            \Session::flash('error', $response['error_description']);
            return $this->redirect();
        }
        try {
            $license = app('license')->getLicense($response['access_token'], true);
        } catch (LicenseInvalidException $e) {
            \Session::flash('error', $e->getMessage());
            return $this->redirect();
        } catch (ClientException $e){
            $message = json_decode($e->getResponse()->getBody()->__toString(), true);
            \Session::flash('error', $message);
            return $this->redirect();
        }
        if (array_key_exists('error_description', $response)) {
            \Session::flash('error', $response['error_description']);
            return $this->redirect();
        }
        \Artisan::call('db:seed', ['--force' => true]);
        $license->save($response['refresh_token']);
        return $this->redirect();

    }

    public function index()
    {
        return view('admin.license', ['license' => app('license')->getLicense(), 'client_id' => $_ENV['OAUTH_CLIENT_ID'], 'oauth' => app('license')->getAuthorizationUrl()]);
    }

    private function redirect()
    {
        return is_installed() ? redirect()->route('admin.dashboard') : redirect()->route('install.settings');
    }
}
