<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Core\Menu;

abstract class AbstractMenuItem
{
    public string $uuid;
    public string $route;
    public string $icon;
    public string $translation;
    public ?string $permission = null;
    public array $children;
    public int $position;

    public function __construct(string $uuid, string $route, string $icon, string $translation, int $position, ?string $permission = null, array $children = [])
    {
        $this->uuid = $uuid;
        $this->route = $route;
        $this->icon = $icon;
        $this->permission = $permission;
        $this->children = $children;
        $this->translation = $translation;
        $this->position = $position;
    }
}
