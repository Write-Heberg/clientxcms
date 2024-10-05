<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Helpdesk;

use App\Models\Admin\Setting;
use App\Models\Core\Permission;
use Illuminate\Http\Request;

class HelpdeskSettingController extends \App\Http\Controllers\Controller
{
    public function showSettings()
    {
        return view('admin.settings.helpdesk.settings');
    }

    public function storeSettings(Request $request)
    {
        staff_aborts_permission(Permission::MANAGE_SETTINGS);
        $data = $request->validate([
            'helpdesk_ticket_auto_close_days' => 'required|integer|min:0',
            'helpdesk_attachments_max_size' => 'required|integer|min:1',
            'helpdesk_attachments_allowed_types' => 'required|string',
            'helpdesk_webhook_url' => 'nullable|url',
            'helpdesk_reopen_days' => 'required|integer|min:-1',
        ]);
        $data['helpdesk_allow_attachments'] = $request->has('helpdesk_allow_attachments');
        Setting::updateSettings($data);
        return back()->with('success', __('admin.helpdesk.settings.success'));
    }
}
