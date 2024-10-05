<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Admin;

use App\Models\ActionLog;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Str;


class Setting extends Model
{
    use HasFactory;

    public static $ignoreLogAttributes = [
        'app_license_refresh_token',
        'app_cron_last_run',
    ];

    protected $fillable = [
        'name',
        'value',
    ];
    public $timestamps = false;

    private $encrypt = [
        'mail.smtp.password',
        'mail.smtp.username',
    ];

    protected static array $ignoreKeys = [
        'app_cron_last_run',
        'app_license_refresh_token',
    ];

    /**
     * Modify a given settings values and return the previous values.
     */
    public static function updateSettings(string|array $key, mixed $value = null, bool $log = true): array
    {
        $keys = is_array($key) ? $key : [$key => $value];
        $old = collect($keys)->mapWithKeys(fn ($value, $name) => [
            $name => setting($name),
        ])->all();

        foreach ($keys as $name => $val) {
            if ($val !== null) {
                self::updateOrCreate(['name' => $name], ['value' => $val]);
            } else {
                self::where('name', $name)->delete();
            }

            setting()->set($name, $val);
        }
        if ($log) {
            ActionLog::log(ActionLog::SETTINGS_UPDATED, Setting::class, null, auth('admin')->id(), null,[], $old, $keys);
        }

        \Cache::forget('settings');

        return $old;
    }


    public function getValueAttribute(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (Str::is($this->encrypted, $this->name)) {
            try {
                return decrypt($value, false);
            } catch (DecryptException $e) {
                return null;
            }
        }

        return $value;
    }
}
