<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Listeners;

use App\Models\ActionLog;
use Illuminate\Auth\Events\Login;

class NewLoginAccount
{
    public function handle(Login $event): void
    {
        if (\Session::has('autologin'))
            return;
        if ($event->guard == 'web' || $event->guard == 'admin') {
            if ($event->guard == 'web'){
                $event->user->last_ip = request()->ip();
                ActionLog::log(ActionLog::NEW_LOGIN, get_class($event->user), $event->user->getKey(), null, $event->user->getKey(), ['ip' => request()->ip()]);
            } else {
                $event->user->last_login_ip = request()->ip();
                ActionLog::log(ActionLog::NEW_LOGIN, get_class($event->user), $event->user->getKey(), $event->user->getKey(), null, ['ip' => request()->ip()]);
            }
            $event->user->last_login = now();
            $event->user->save();
        }
    }
}
