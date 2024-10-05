<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Admin\Settings;

class SettingsItemDTO
{
    public string $uuid;
    public string $name;
    public string $description;
    public string $icon;

    public $action;
    public bool $active;
    public string $card_uuid;
    public string $permission;

    public function __construct(string $card_uuid, string $uuid, string $name, string $description, string $icon, $action, string $permission)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->description = $description;
        $this->icon = $icon;
        $this->action = $action;
        $this->permission = $permission;
        $this->card_uuid = $card_uuid;
        $this->active = true;
    }

    public function url()
    {
        if (filter_var($this->action, FILTER_VALIDATE_URL)) {
            return $this->action;
        }
        return route('admin.settings.show', ['uuid' => $this->uuid, 'card' => $this->card_uuid]);
    }

    public function isActive()
    {
        return staff_has_permission($this->permission) && $this->active;
    }

    public function isSetting()
    {
        return str_contains($this->url(), admin_prefix('settings'));
    }
}
