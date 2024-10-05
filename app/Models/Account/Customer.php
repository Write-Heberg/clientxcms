<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Account;

use App\Contracts\Helpdesk\SupportRelateItemInterface;
use App\Mail\Auth\ResetPasswordEmail;
use App\Mail\Auth\VerifyEmail;
use App\Models\Core\Invoice;
use App\Models\Helpdesk\SupportTicket;
use App\Models\Metadata;
use App\Models\Provisioning\Service;
use App\Models\Traits\CanBlocked;
use App\Models\Traits\CanUse2FA;
use App\Models\Traits\HasMetadata;
use App\Models\Traits\Loggable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *      schema="Customer",
 *     title="Customer",
 *     description="Customer model"
 * )
 */
class Customer extends Authenticatable implements \Illuminate\Contracts\Auth\MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, MustVerifyEmail, HasMetadata, CanBlocked, Loggable, CanUse2FA;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     *
     * @OA\Property(
     *     property="email",
     *     type="string",
     *     description="Customer email"
     * )
     * @OA\Property(
     *     property="password",
     *     type="string",
     *     description="Customer password"
     * )
     * @OA\Property(
     *     property="firstname",
     *     type="string",
     *     description="Customer firstname"
     * )
     * @OA\Property(
     *     property="lastname",
     *     type="string",
     *     description="Customer lastname"
     * )
     * @OA\Property(
     *     property="phone",
     *     type="string",
     *     description="Customer phone"
     * )
     * @OA\Property(
     *     property="address",
     *     type="string",
     *     description="Customer address"
     * )
     * @OA\Property(
     *     property="address2",
     *     type="string",
     *     description="Customer address line 2"
     * )
     * @OA\Property(
     *     property="city",
     *     type="string",
     *     description="Customer city"
     * )
     * @OA\Property(
     *     property="country",
     *     type="string",
     *     description="Customer country"
     * )
     * @OA\Property(
     *     property="region",
     *     type="string",
     *     description="Customer region"
     * )
     * @OA\Property(
     *     property="zipcode",
     *     type="string",
     *     description="Customer zipcode"
     * )
     * @OA\Property(
     *     property="email_verified_at",
     *     type="string",
     *     format="date-time",
     *     description="Customer email verification timestamp"
     * )
     * @OA\Property(
     *     property="is_confirmed",
     *     type="boolean",
     *     description="Customer confirmation status"
     * )
     * @OA\Property(
     *     property="is_deleted",
     *     type="boolean",
     *     description="Customer deletion status"
     * )
     *
     * @OA\Property(
     *     property="dark_mode",
     *     type="boolean",
     *     description="Customer theme mode"
     * )
     * @OA\Property(
     *     property="last_login",
     *     type="string",
     *     format="date-time",
     *     description="Last login timestamp"
     * )
     * @OA\Property(
     *     property="last_ip",
     *     type="string",
     *     description="Last login IP address"
     * )
     */
    protected $fillable = [
        'email',
        'password',
        'firstname',
        'lastname',
        'email',
        'phone',
        'address',
        'address2',
        'city',
        'country',
        'region',
        'zipcode',
        'email_verified_at',
        'is_confirmed',
        'is_deleted',
        'top_secret',
        'last_login',
        'dark_mode',
        'last_ip',
        'notes',
        'balance',
    ];

    protected $attributes = [
        'dark_mode' => false,
        'balance' => 0,
        'country' => 'FR',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'totp_secret',
        'updated_at',
        'last_ip',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login' => 'datetime',
        'totp_secret' => 'encrypted',
    ];

    public static function sumCustomers()
    {
        return Service::countCustomers();
    }


    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail($this));
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordEmail($token));
    }


    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'is_confirmed' => true,
        ])->save();
    }

    public function emails()
    {
        return $this->hasMany(EmailMessage::class, 'recipient_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'customer_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    public function tickets()
    {
        return $this->hasMany(SupportTicket::class, 'customer_id');
    }

    protected static function newFactory()
    {
        return \Database\Factories\Core\CustomerFactory::new();
    }

    public function getFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function hasServicePermission(Service $service, string $permission)
    {
        return $service->customer_id == $this->id;
    }

    public function getConfirmationUrl()
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $this->getKey(),
                'hash' => sha1($this->getEmailForVerification()),
            ]
        );
    }

    public function supportRelatedItems()
    {
        return $this->invoices->merge($this->services)->mapWithKeys(function ($item) {
            return [$item->relatedType() . '-' . $item->relatedId() => $item->relatedName()];
        })->put('none', __('client.support.create.relatednone'));
    }

    public function initials()
    {
        return $this->firstname[0] . $this->lastname[0];
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
