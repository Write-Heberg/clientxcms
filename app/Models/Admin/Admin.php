<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Admin;


use App\Mail\Auth\ResetPasswordEmail;
use App\Models\Core\Role;
use App\Models\Traits\CanUse2FA;
use App\Models\Traits\HasMetadata;
use App\Models\Traits\Loggable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;
    use Notifiable;
    use HasMetadata;
    use Loggable;
    use CanUse2FA;

    protected $fillable = [
        'email',
        'password',
        'username',
        'firstname',
        'lastname',
        'email_verified_at',
        'top_secret',
        'last_login',
        'last_login_ip',
        'signature',
        'dark_mode',
        'expires_at',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'last_login' => 'datetime',
    ];


    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordEmail($token));
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isActive()
    {
        return $this->expires_at == null || $this->expires_at->isFuture();
    }

    public function initials()
    {
        return $this->firstname[0] . $this->lastname[0];
    }

    public function can($abilities, $arguments = [])
    {
        return $this->role->hasPermission($abilities);
    }

    public function notify($instance)
    {
        try {
            app(Dispatcher::class)->send($this, $instance);
            \Cache::forget('notification_error');
        } catch (\Exception $e) {
            \Cache::put('notification_error', $e->getMessage(), 3600 * 24);
        }
    }

}
