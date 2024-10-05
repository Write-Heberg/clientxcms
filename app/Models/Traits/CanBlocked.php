<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Traits;

trait CanBlocked
{

    public function isBanned()
    {
        if ($this->hasMetadata('banned')) {
            return true;
        }
    }

    public function isSuspended()
    {
        if ($this->hasMetadata('suspended')) {
            return true;
        }
    }

    public function isBlocked()
    {
        return $this->isBanned() || $this->isSuspended();
    }

    public function ban(string $reason, bool $suspendServices = true)
    {
        $this->attachMetadata('banned', 'true');
        $this->attachMetadata('banned_reason', $reason);
        $this->attachMetadata('banned_at', now()->format('d/m/Y H:i'));
        $this->attachMetadata('banned_by', auth('admin')->user()->username);
        try {
            $servicesOnlines = $this->services()->where('status', 'online')->get();
            foreach ($servicesOnlines as $service){
                $service->expire(true);
            }
        } catch (\Exception $e) {
            \Session::flash('error', $e->getMessage());
        }
    }

    public function suspend(string $reason, bool $suspendServices = true)
    {
        $this->attachMetadata('suspended', 'true');
        $this->attachMetadata('suspended_reason', $reason);
        $this->attachMetadata('suspended_at', now()->format('d/m/Y H:i'));
        $this->attachMetadata('suspended_by', auth('admin')->user()->username);

        try {
            if ($suspendServices){
                $servicesOnlines = $this->services()->where('status', 'online')->get();
                foreach ($servicesOnlines as $service){
                    $service->suspend('Customer suspended');
                }
            }
        } catch (\Exception $e) {
            \Session::flash('error', $e->getMessage());
        }
    }

    public function reactivate()
    {
        $this->detachMetadata('banned');
        $this->detachMetadata('banned_reason');
        $this->detachMetadata('banned_at');
        $this->detachMetadata('banned_by');
        $this->detachMetadata('suspended');
        $this->detachMetadata('suspended_reason');
        $this->detachMetadata('suspended_at');
        $this->detachMetadata('suspended_by');
        try {
            $servicesSuspended = $this->services()->where('status', 'suspended')->get();
            foreach ($servicesSuspended as $service){
                $service->unsuspend();
            }
        } catch (\Exception $e) {
            \Session::flash('error', $e->getMessage());
        }

    }

    public function getBlockedMessage()
    {
        if ($this->isBanned()){
            return __('admin.customers.show.is_banned', ['reason' => $this->getMetadata('banned_reason'), 'username' => $this->getMetadata('banned_by'), 'date' => $this->getMetadata('banned_at')]);
        }
        if ($this->isSuspended()){
            return __('admin.customers.show.is_suspended', ['reason' => $this->getMetadata('suspended_reason'), 'username' => $this->getMetadata('suspended_by'), 'date' => $this->getMetadata('suspended_at')]);
        }
        return null;
    }
}
