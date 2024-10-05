<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Admin\Dashboard\Earn;

class EarnStatisticsItemDTO
{
    public string $icon;
    public string $title;
    public string $value;
    public string $description;
    public string $color;

    public function __construct(string $icon, string $title, string $value, string $color)
    {
        $this->icon = $icon;
        $this->title = $title;
        $this->value = $value;
        $this->color = $color;
    }
}
