<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Core\Admin\Dashboard;

class AdminCountWidgetTooltips
{
    public string $uuid;
    public string $value;
    public string $color;
    public string $icon;
    public string $tooltip;

    public function __construct(string $uuid, string $tooltip,string $value, ?int $count = null, ?string $color = null, ?string $icon=null)
    {
        $this->uuid = $uuid;
        $this->value = $value;
        $this->icon = $icon ?? ($count > 0 ? 'bi bi-graph-down-arrow' : 'bi bi-graph-up-arrow');
        $this->color = $color ?? ($count > 0 ? 'green' : 'red');
        $this->tooltip = $tooltip;
    }
}
