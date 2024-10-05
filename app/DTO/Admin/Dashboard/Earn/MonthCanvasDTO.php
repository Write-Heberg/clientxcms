<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Admin\Dashboard\Earn;

use Illuminate\Support\Collection;

class MonthCanvasDTO implements \App\Contracts\CanvasDTOInterface
{
    private Collection $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }

    public function getLabels()
    {
        return $this->items->map(function ($item) {
            return $item['month'];
        })->values()->toJson();
    }

    public function getColors()
    {
        return $this->items->map(function ($item) {
            return '#' . substr(md5($item['month']), 0, 6);
        })->values()->toJson();
    }

    public function isEmpty()
    {
        return count($this->items) == 0;
    }

    public function getValues(bool $statistic = false)
    {
        return $this->items->map(function ($item) {
            return $item['total'];
        })->values()->toJson();
    }
}
