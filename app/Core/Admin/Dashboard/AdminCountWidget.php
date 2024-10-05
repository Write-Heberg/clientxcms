<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Core\Admin\Dashboard;

class AdminCountWidget
{
    public string $uuid;
    public string $title;
    public string $value;
    public ?AdminCountWidgetTooltips $tooltip = null;
    public string $icon;
    public string $permission;

    public function __construct(string $uuid, string $icon, string $title, string $value,string $permission,?AdminCountWidgetTooltips $tooltip = null)
    {
        $this->uuid = $uuid;
        $this->title = $title;
        $this->value = $value;
        $this->tooltip = $tooltip;
        $this->icon = $icon;
        $this->permission = $permission;
    }
}
