<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Services;

use App\DTO\Admin\Settings\SettingsCardDTO;
use App\DTO\Admin\Settings\SettingsItemDTO;
use App\Models\Admin\Setting;
use Illuminate\Support\Collection;

class SettingsService
{

    protected Collection $settings;
    protected Collection $cards;

    public function __construct(Collection $settings = null, Collection $cards = null)
    {
        $this->cards = $cards ?? collect();
        $this->settings = $settings ?? collect();
    }
    public function has(string $key): bool
    {
        return $this->settings->has($key);
    }
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->settings->get($key, $default);
        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        return $value;
    }
    public function set(array|string $key, mixed $value = null): void
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $name => $val) {
            $this->settings->put($name, $val);
        }
    }

    public function save(): void
    {
        Setting::updateSettings($this->settings->all());
    }

    public function addCard(string $uuid, string $name, string $description, int $order, ?Collection $items = null, bool $is_active = true): void
    {
        $this->cards->push(new SettingsCardDTO($uuid, $name, $description, $order, $items ?? collect(), $is_active));
    }

    public function getCards(): Collection
    {
        return collect($this->cards)->sort(fn($a, $b) => $a->order <=> $b->order);
    }

    public function addCardItem(string $card_uuid, string $uuid, string $name, string $description, string $icon, $action, ?string $permission = null): void
    {
        if ($permission == null)
            $permission = 'admin.settings.'. $card_uuid . '_' . $uuid;
        $card = $this->cards->firstWhere('uuid', $card_uuid);
        if ($card) {
            $card->items->push(new SettingsItemDTO($card_uuid, $uuid, $name, $description, $icon, $action, $permission));
        }
    }

    public function getCurrentCard(string $uuid): ?SettingsCardDTO
    {
        return $this->cards->firstWhere('uuid', $uuid);
    }

    public function getCurrentItem(string $card_uuid, string $uuid): ?SettingsItemDTO
    {
        $card = $this->cards->firstWhere('uuid', $card_uuid);
        if ($card) {
            return $card->items->firstWhere('uuid', $uuid);
        }
        return null;
    }
}
