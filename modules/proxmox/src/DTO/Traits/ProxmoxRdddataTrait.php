<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\DTO\Traits;

use App\Modules\Proxmox\ProxmoxAPI;

trait ProxmoxRdddataTrait
{

    public function rdddata(string $key, bool $json = true): array|string
    {
        $tmp = [];
        if (empty($this->rdddata)) {
            $this->rdddata = (array) (ProxmoxAPI::callApi($this->server, "/nodes/{$this->node}/{$this->type}/{$this->vmid}/rrddata", [
                'timeframe' => $this->timeframe ?? 'hour',
            ])->toArray()['data']->data);
        }
        foreach ($this->rdddata as $line) {
            foreach ($line as $k => $v) {
                if ($k === $key) {
                    $tmp[] = $this->rdddataFormatValue($k, $v);
                }
            }
        }
        if ($json) {
            return collect($tmp)->toJson();
        }
        return $tmp;
    }

    private function rdddataFormatValue(string $key, string $value)
    {
        if ($key === 'time') {
            if ($this->timeframe === 'hour')
                return (new \DateTime())->setTimestamp($value)->format('H:i');
            if ($this->timeframe === 'day')
                return (new \DateTime())->setTimestamp($value)->format('d/m H:i');
            if ($this->timeframe === 'week')
                return (new \DateTime())->setTimestamp($value)->format('d/m');
            if ($this->timeframe === 'month')
                return (new \DateTime())->setTimestamp($value)->format('d/m');
            if ($this->timeframe === 'year')
                return (new \DateTime())->setTimestamp($value)->format('d/m');
        }
        if ($key === 'mem' || $key == 'disk' || $key == 'maxdisk') {
            return number_format($value / 1024 / 1024, 3);
        }
        if ($key === 'cpu') {
            return number_format($value * 100, 3);
        }
        return $value;
    }
}
