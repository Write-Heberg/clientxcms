<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models;

use App\Models\Account\Customer;
use App\Models\Admin\Admin;
use Arr;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    use HasFactory;

    const SETTINGS_UPDATED = 'settings_updated';
    const NEW_LOGIN = 'new_login';
    const RESOURCE_CREATED = 'resource_created';
    const RESOURCE_UPDATED = 'resource_updated';
    const RESOURCE_DELETED = 'resource_deleted';
    const RESOURCE_CLONED = 'resource_cloned';
    const CHECKOUT_COMPLETED = 'checkout_completed';
    const SERVICE_DELIVERED = 'service_delivered';
    const SERVICE_CANCELLED = 'service_cancelled';
    const SERVICE_UNCANCELLED = 'service_uncancelled';
    const SERVICE_SUSPENDED = 'service_suspended';
    const SERVICE_UNSUSPENDED = 'service_unsuspended';
    const INVOICE_PAID = 'invoice_paid';
    const SERVICE_RENEWED = 'service_renewed';
    const OTHER = 'other';
    const SERVICE_EXPIRED = 'service_expired';
    const EXTENSION_ENABLED = 'extension_enabled';
    const EXTENSION_DISABLED = 'extension_disabled';
    const EXTENSION_INSTALLED = 'extension_installed';
    const THEME_CHANGED = 'theme_changed';
    const ALL_ACTIONS = [
        self::SETTINGS_UPDATED,
        self::RESOURCE_CREATED,
        self::RESOURCE_UPDATED,
        self::RESOURCE_DELETED,
        self::RESOURCE_CLONED,
        self::CHECKOUT_COMPLETED,
        self::SERVICE_DELIVERED,
        self::SERVICE_CANCELLED,
        self::SERVICE_UNCANCELLED,
        self::SERVICE_SUSPENDED,
        self::SERVICE_UNSUSPENDED,
        self::INVOICE_PAID,
        self::SERVICE_RENEWED,
        self::SERVICE_EXPIRED,
        self::EXTENSION_ENABLED,
        self::EXTENSION_DISABLED,
        self::EXTENSION_INSTALLED,
        self::THEME_CHANGED,
        self::NEW_LOGIN,
    ];

    protected static array $ignoreKeys = [];

    protected $fillable = [
        'customer_id',
        'staff_id',
        'action',
        'model',
        'model_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff()
    {
        return $this->belongsTo(Admin::class);
    }

    public function getIcon()
    {
        switch ($this->action) {
            case self::SETTINGS_UPDATED:
            case self::RESOURCE_UPDATED:
                return 'bi bi-gear';
            case self::RESOURCE_CREATED:
                return 'bi bi-plus';
            case self::RESOURCE_DELETED:
                return 'bi bi-trash';
            case self::RESOURCE_CLONED:
                return 'bi bi-clipboard';
            case self::CHECKOUT_COMPLETED:
                return 'bi bi-cart-check';
            case self::SERVICE_DELIVERED:
                return 'bi bi-check-circle';
            case self::SERVICE_CANCELLED:
                return 'bi bi-x-circle';
            case self::SERVICE_RENEWED:
            case self::SERVICE_UNCANCELLED:
                return 'bi bi-arrow-repeat';
            case self::SERVICE_SUSPENDED:
                return 'bi bi-pause-circle';
            case self::SERVICE_UNSUSPENDED:
                return 'bi bi-play-circle';
            case self::INVOICE_PAID:
                return 'bi bi-cash-coin';
            case self::SERVICE_EXPIRED:
                return 'bi bi-clock';
            case self::EXTENSION_ENABLED:
                return 'bi bi-box-arrow-in-up';
            case self::EXTENSION_DISABLED:
                return 'bi bi-box-arrow-down';
            case self::EXTENSION_INSTALLED:
                return 'bi bi-box-arrow-in-down';
            case self::THEME_CHANGED:
                return 'bi bi-palette';
                case self::NEW_LOGIN:
                return 'bi bi-door-open';
                default:
                return 'bi bi-question-circle';
        }
    }

    public static function log($action, $model, $modelId, $staffId = null, $customerId = null, $payload = [], $old = [], $new = [])
    {
        if (collect($old)->keys()->filter(fn ($key) => in_array($key, self::$ignoreKeys ?? []))->isNotEmpty()) {
            return null;
        }
        $log = self::create([
            'customer_id' => $customerId,
            'staff_id' => $staffId,
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'payload' => $payload,
        ]);

        if ($old && $new) {
            $log->createEntries($old, $new);
        }

        return $log;
    }

    public function entries()
    {
        return $this->hasMany(ActionLogEntries::class);
    }

    public function getFormattedName()
    {
        $parameters = $this->getParameters();
        $action = __("actionslog.actions.{$this->action}", $parameters);
        return $action;
    }

    private function getParameters()
    {
        if (str_starts_with($this->action, 'resource_')) {
            $modelClass = explode('\\', $this->model);
            $model = strtolower(end($modelClass));

            return [
                'model' => $model . ' #' . $this->model_id,
            ];
        }
        if (str_starts_with($this->action, 'service_') || $this->action === self::INVOICE_PAID) {
            return [
                'id' => $this->model_id,
            ];
        }
        if (str_starts_with($this->action, 'extension_')) {
            return [
                'extension' => $this->model_id,
            ];
        }
        return $this->payload;

    }

    public function userlink()
    {
        if ($this->customer_id) {
            return route('admin.customers.show', $this->customer_id);
        }
        if ($this->staff_id) {
            return route('admin.staffs.show', $this->staff_id);
        }
        return '#';
    }

    public function username()
    {
        if ($this->customer_id) {
            if ($this->customer == null)
                return 'Deleted Customer';
            return $this->customer->fullName;
        }
        if ($this->staff_id) {
            if ($this->staff == null)
                return 'Deleted Staff';
            return $this->staff->username;
        }
        return 'System';
    }

    public function createEntries(array $old, array $new): void
    {
        foreach ($old as $attribute => $oldValue) {
            $newValue = Arr::get($new, $attribute);
            if ($oldValue !== $newValue && !in_array($attribute, self::$ignoreLogAttributes ?? [])) {
                $this->entries()->create([
                    'attribute' => $attribute,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                ]);
            }
        }
    }

}
