<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Admin\Dashboard\Earn;

use App\Contracts\CanvasDTOInterface;
use App\Models\Core\Gateway;
use Illuminate\Support\Collection;

class GatewaysCanvasDTO implements CanvasDTOInterface
{
    private Collection $items;

    public function __construct(Collection $data)
    {
        $this->items = $data;
    }

    public function getLabels()
    {
        return $this->items->map(function ($item) {
            $gateway = Gateway::where('uuid', $item['paymethod'])->first();
            $percent = $item['count'] / $this->items->sum('count') * 100;
            if ($gateway) {
                return $gateway->name . ' (' . number_format($percent, 2) . '%)';
            }
            return $item['paymethod'] . ' (' . number_format($percent, 2) . '%)';
        })->values()->toJson();
    }

    public function getColors()
    {
        return $this->items->map(function ($item) {
            return '#' . substr(md5($item['paymethod']), 0, 6);
        })->values()->toJson();
    }

    public function isEmpty()
    {
        return count($this->items) == 0;
    }

    public function getValues(bool $statistic = false)
    {
        return $this->items->map(function ($item) {
            return $item['count'];
        })->values()->toJson();
    }
}
