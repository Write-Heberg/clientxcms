<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Core\Admin\Dashboard;

class AdminCardWidget
{
    public string $uuid;

    public $render;
    public int $cols;
    public ?string $after;
    public string $permission;

    public function __construct(string $uuid, callable $render, string $permission, int $cols = 1, ?string $after = null)
    {
        $this->uuid = $uuid;
        $this->render = $render;
        $this->cols = $cols;
        $this->after = $after;
        $this->permission = $permission;
    }
    public function render()
    {
        return call_user_func($this->render);
    }
}
