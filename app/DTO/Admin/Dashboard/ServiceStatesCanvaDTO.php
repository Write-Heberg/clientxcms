<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Admin\Dashboard;

use App\Contracts\CanvasDTOInterface;

class ServiceStatesCanvaDTO implements CanvasDTOInterface
{
    public array $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function getPendings()
    {
        $item = collect($this->items)->firstWhere('status', 'pending');
        return $item ? $item['count'] : 0;
    }

    public function getActives()
    {
        $item = collect($this->items)->firstWhere('status', 'active');
        return $item ? $item['count'] : 0;
    }

    public function getExpireds()
    {
        $item = collect($this->items)->firstWhere('status', 'expired');
        return $item ? $item['count'] : 0;
    }

    public function getCancelleds()
    {
        $item = collect($this->items)->firstWhere('status', 'cancelled');
        return $item ? $item['count'] : 0;
    }

    public function getSuspendeds()
    {
        $item = collect($this->items)->firstWhere('status', 'suspended');
        return $item ? $item['count'] : 0;
    }
    public function getLabels()
    {
        return collect($this->getNotZeroValues())->map(fn($status) => __('global.states.'.$status))->values()->toJson();
    }

    public function getColors()
    {
        $colors = [
            'pending' => '#9f9f9f',
            'active' => '#00a65a',
            'expired' => '#f56954',
            'cancelled' => '#f39c12',
            'suspended' => '#f39c12',
        ];
        return collect($this->getNotZeroValues())->map(fn($status) => $colors[$status])->values()->toJson();
    }

    public function isEmpty()
    {
        return collect($this->items)->isEmpty();
    }

    public function getValues(bool $statistic = false)
    {
        if ($statistic) {
            return collect($this->getNotZeroValues())->mapWithKeys(function($status) { return [$status => $this->{'get'.ucfirst($status).'s'}()]; })->toArray();
        }
        return collect($this->getNotZeroValues())->map(fn($status) => $this->{'get'.ucfirst($status).'s'}())->values()->toJson();
    }

    private function getNotZeroValues()
    {
        return collect(['pending', 'active', 'expired', 'cancelled', 'suspended'])->filter(fn($status) => $this->{'get'.ucfirst($status).'s'}() > 0);
    }



}
