<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Admin\Settings;

use Illuminate\Support\Collection;

class SettingsCardDTO
{
    public string $uuid;
    public string $name;
    public string $description;
    public Collection $items;
    public int $order;
    public bool $is_active;

    public function __construct(string $uuid, string $name, string $description,  int $order, Collection $items, bool $is_active = true)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->description = $description;
        $this->items = $items;
        $this->order = $order;
        $this->is_active = $is_active;
    }
}
