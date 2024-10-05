<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Addons\Import\Controllers;

use App\Models\Core\Permission;
use Illuminate\Http\Request;

class ImportController extends \App\Http\Controllers\Controller {

        public function index() {
            staff_aborts_permission(Permission::MANAGE_SETTINGS);
            return view('import_admin::index');
        }
        public function importv1() {
            staff_aborts_permission(Permission::MANAGE_SETTINGS);
            return view('import_admin::import/v1', ['output' => null]);
        }

        public function importWHMCS() {
            staff_aborts_permission(Permission::MANAGE_SETTINGS);
            return view('import_admin::import/whmcs', ['output' => null]);
        }

        public function importDataFromWHMCS(Request $request) {
            staff_aborts_permission(Permission::MANAGE_SETTINGS);
            $request->validate([

                'host' => ['required', 'regex:/^[\w\-.]+$/'],
                'port' => ['required', 'numeric', 'min:1', 'max:65535'],
                'database' => 'required',
                'username' => 'required',
                'password' => 'required',
                'key' => 'required',
            ]);
            $options = [
                '--force' => $request->input('truncate') != null,
                '--dbname' => $request->input('database'),
                '--host' => $request->input('host'),
                '--username' => $request->input('username'),
                '--password' => $request->input('password'),
                '--key' => $request->input('key'),
            ];
            foreach ($request->input('importables') as $table) {
                $options['--' . $table] = 'true';
            }
            \Artisan::call('clientxcms:whmcs-migrate',$options);
            $output = \Artisan::output();
            \Storage::disk('local')->put('import.log', $output);
            return view('import_admin::import/v1', ['output' => $output]);
        }

        public function importDataFromv1(Request $request) {
            staff_aborts_permission(Permission::MANAGE_SETTINGS);
            $request->validate([
                'host' => ['required', 'regex:/^[\w\-.]+$/'],
                'port' => ['required', 'numeric', 'min:1', 'max:65535'],
                'database' => 'required',
                'username' => 'required',
                'password' => 'required',
            ]);
            $options = [
                '--force' => $request->input('truncate') != null,
                '--host' => $request->input('host'),
                '--port' => $request->input('port'),
                '--dbname' => $request->input('database'),
                '--username' => $request->input('username'),
                '--password' => $request->input('password'),
            ];
            foreach ($request->input('importables') as $table) {
                $options['--' . $table] = 'true';
            }
            \Artisan::call('clientxcms:v1-migrate', $options);
            $output = \Artisan::output();
            \Storage::disk('local')->put('import.log', $output);
            return view('import_admin::import/whmcs', ['output' => $output]);
        }

        public function downloadReport() {
            staff_aborts_permission(Permission::MANAGE_SETTINGS);
            $path = \Storage::disk('local')->path('import.log');
            return response()->download($path);
        }
}
