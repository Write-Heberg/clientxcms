<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DarkModeController extends Controller
{
    public function darkmode()
    {
        if (!auth()->check()){
            if (auth('admin')->check()){
                auth('admin')->user()->dark_mode = !auth('admin')->user()->dark_mode;
                auth('admin')->user()->save();
                return response()->noContent();
            }
            $last = \Cookie::get('dark_mode', false);
            \Cookie::queue('dark_mode', !$last, 60 * 24 * 365);
            return response()->noContent();
        }
        if (auth()->user()->dark_mode == 0) {
            if (auth('admin')->check()){
                auth('admin')->user()->dark_mode = 1;
                auth('admin')->user()->save();
            }
            auth()->user()->dark_mode = 1;
            auth()->user()->save();
            return response()->noContent();
        }
        auth()->user()->dark_mode = 0;
        auth()->user()->save();
        if (auth('admin')->check()){
            auth('admin')->user()->dark_mode = 0;
            auth('admin')->user()->save();
        }
        return response()->noContent();
    }
}
