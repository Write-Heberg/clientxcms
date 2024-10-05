<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Provisioning\Traits;

use App\Core\NoneServerType;
use App\DTO\Provisioning\ServiceStateChangeDTO;
use App\Events\Core\Service\ServiceCancelled;
use App\Events\Core\Service\ServiceDelivered;
use App\Events\Core\Service\ServiceDeliveryFailed;
use App\Events\Core\Service\ServiceExpired;
use App\Events\Core\Service\ServiceRenewed;
use App\Events\Core\Service\ServiceSuspended;
use App\Events\Core\Service\ServiceUnsuspended;
use App\Exceptions\ExternalApiException;
use App\Exceptions\ProductConfigNotFoundException;
use App\Mail\Core\Service\ServiceSuspendedEmail;
use App\Models\ActionLog;
use App\Models\Core\InvoiceItem;
use App\Models\Store\Product;
use App\Services\Store\RecurringService;

trait ServerTypeTrait
{
    public function deliver()
    {
        $success = false;
        $message = '';
        try {
            $this->delivery_attempts++;
            $server = $this->productType()->server();
            if ($server == null || $this->type != 'none' && $server instanceof NoneServerType){
                $message = sprintf('No server type %s found for service %d', $this->type, $this->id);
            } else {
                $result = $server->createAccount($this);
                if ($result->success) {
                    $this->status = self::STATUS_ACTIVE;
                    $this->delivery_errors = NULL;
                    event(new ServiceDelivered($this, $result));
                    $success = true;
                    $message = $message->message ?? 'Service delivered';
                    ActionLog::log(ActionLog::SERVICE_DELIVERED, get_class($this), $this->id, auth('admin')->id(), null, ['message' => $message]);
                    if ($this->product_id != null){
                        Product::removeStock($this->product_id);
                    }
                } else {
                    $this->delivery_errors = $result->message;
                    $message = $result->message;
                    event(new ServiceDeliveryFailed($this, $result));
                }
            }
        } catch (ExternalApiException|ProductConfigNotFoundException $e) {
            $this->delivery_errors = $e->getMessage();
            $result = new ServiceStateChangeDTO($this, false, $e->getMessage());
            event(new ServiceDeliveryFailed($this, $result));
        }
        $this->save();
        return new ServiceStateChangeDTO($this, $success, $message);

    }

    public function renew() {
        try {
            $this->renewals++;
            $this->invoice_id = null;
            $this->last_expires_at = $this->expires_at;
            $this->expires_at = app(RecurringService::class)->addFrom($this->expires_at, $this->billing);
            if ($this->expires_at > now()) {
                $this->unsuspend();
            }
            ActionLog::log(ActionLog::SERVICE_RENEWED, get_class($this), $this->id, auth('admin')->id(), null, ['expires_at' => $this->expires_at, 'last_expires_at' => $this->last_expires_at]);
            $this->save();

            event(new ServiceRenewed($this));
            $server = $this->productType()->server();
            if ($server != null){
                $server->onRenew($this);
            }
        } catch (ExternalApiException $e){
            $this->delivery_errors = $e->getMessage();
            $result = new ServiceStateChangeDTO($this, false, $e->getMessage());
            event(new ServiceDeliveryFailed($this, $result));
        }
    }

    public function expire(bool $force = false): ServiceStateChangeDTO
    {
        try {
            $server = $this->productType()->server();
            if ($server != null){
                $result = $server->expireAccount($this);
                if ($result->success) {
                    $this->status = self::STATUS_EXPIRED;
                    if ($this->expires_at && $this->expires_at->isFuture()){
                        $this->expires_at = now();
                    }
                    $this->save();
                    ActionLog::log(ActionLog::SERVICE_EXPIRED, get_class($this), $this->id, auth('admin')->id(), null, ['message' => 'Service expired']);
                    event(new ServiceExpired($this));
                    if ($this->product_id != null){
                        Product::addStock($this->product_id);
                    }
                }
            } else {
                $result = new ServiceStateChangeDTO($this, true, 'Server '. $this->id .' expired');
                $this->status = self::STATUS_EXPIRED;
                if ($this->expires_at && $this->expires_at->isFuture()){
                    $this->expires_at = now();
                }
                ActionLog::log(ActionLog::SERVICE_EXPIRED, get_class($this), $this->id, auth('admin')->id(), null, ['message' => 'Service expired']);
                $this->save();
                event(new ServiceExpired($this));
            }
        } catch (ExternalApiException $e) {

            if ($force){
                $this->status = self::STATUS_EXPIRED;
                $this->save();
                event(new ServiceExpired($this));
            }
            $this->delivery_errors = $e->getMessage();
            $result = new ServiceStateChangeDTO($this, false, $e->getMessage());
            event(new ServiceDeliveryFailed($this, $result));
        }
        return $result;
    }

    public function suspend($reason = null, bool $notify = true): ServiceStateChangeDTO
    {
        try {
            $server = $this->productType()->server();
            if ($server != null){
                $result = $server->suspendAccount($this);
                if ($result->success){
                    $result = new ServiceStateChangeDTO($this, true, 'Server suspended');
                    $this->status = self::STATUS_SUSPENDED;
                    $this->suspended_at = now();
                    $this->suspend_reason = $reason;
                    ActionLog::log(ActionLog::SERVICE_SUSPENDED, get_class($this), $this->id, auth('admin')->id(), null, ['reason' => $reason, 'notify' => $notify]);
                    $this->save();
                    event(new ServiceSuspended($this));
                    if ($notify){
                        $this->customer->notify(new ServiceSuspendedEmail($this, $reason));
                    }
                }
            } else {
                $result = new ServiceStateChangeDTO($this, true, 'Server '. $this->id .' suspended');
                $this->status = self::STATUS_SUSPENDED;
                $this->suspended_at = now();
                $this->save();
                ActionLog::log(ActionLog::SERVICE_SUSPENDED, get_class($this), $this->id, auth('admin')->id(), null, ['reason' => $reason, 'notify' => $notify]);
                event(new ServiceSuspended($this));
                if ($notify) {
                    $this->customer->notify(new ServiceSuspendedEmail($this, $reason));
                }
            }
        } catch (ExternalApiException $e) {
            $this->delivery_errors = $e->getMessage();
            $result = new ServiceStateChangeDTO($this, false, $e->getMessage());
            event(new ServiceDeliveryFailed($this, $result));
        }
        return $result;
    }

    public function unsuspend(): ServiceStateChangeDTO
    {
        try {
            $server = $this->productType()->server();
            if ($server != null){
                $result = $server->unsuspendAccount($this);
                if ($result->success){
                    ActionLog::log(ActionLog::SERVICE_UNSUSPENDED, get_class($this), $this->id, auth('admin')->id(), null, ['message' => 'Server unsuspended']);
                    $this->status = self::STATUS_ACTIVE;
                    $this->suspended_at = null;
                    $this->save();
                    event(new ServiceUnsuspended($this));
                }
            } else {
                ActionLog::log(ActionLog::SERVICE_UNSUSPENDED, get_class($this), $this->id, auth('admin')->id(), null, ['message' => 'Server unsuspended']);
                $result = new ServiceStateChangeDTO($this, true, 'Server '. $this->id .' unsuspended');
                $this->status = self::STATUS_ACTIVE;
                $this->suspended_at = null;
                $this->save();
                event(new ServiceUnsuspended($this));
            }
        } catch (ExternalApiException $e) {
            $this->delivery_errors = $e->getMessage();
            $result = new ServiceStateChangeDTO($this, false, $e->getMessage());
            event(new ServiceDeliveryFailed($this, $result));
        }
        return $result;
    }

    public function canCancel(): bool
    {
        if ($this->cancelled_at != null){
            return false;
        }
        return $this->status == self::STATUS_ACTIVE || $this->status == self::STATUS_SUSPENDED;
    }

    public function cancel(string $reason, \DateTime $date, bool $changeState): ServiceStateChangeDTO
    {
        if ($this->cancelled_at != null){
            return new ServiceStateChangeDTO($this, false, 'Service already cancelled');
        }
        $this->cancelled_at = $date;
        $this->cancelled_reason = $reason;
        if ($changeState){
            $this->status = self::STATUS_CANCELLED;
            ActionLog::log(ActionLog::SERVICE_CANCELLED, get_class($this), $this->id, auth('admin')->id(), auth('web')->id(), ['reason' => $reason, 'date' => $date->format('Y-m-d H:i:s')]);
        }
        $this->save();
        return new ServiceStateChangeDTO($this, true, 'Service cancelled');
    }

    public function uncancel(): ServiceStateChangeDTO
    {
        if ($this->cancelled_at == null){
            return new ServiceStateChangeDTO($this, false, 'Service not cancelled');
        }
        $this->cancelled_at = null;
        $this->cancelled_reason = null;
        $this->status = self::STATUS_ACTIVE;
        ActionLog::log(ActionLog::SERVICE_UNCANCELLED, get_class($this), $this->id, auth('admin')->id(), auth('web')->id(), ['message' => 'Service uncanceled']);
        $this->save();
        return new ServiceStateChangeDTO($this, true, 'Service uncanceled');
    }

    public function canUncancel(): bool
    {
        if ($this->status != self::STATUS_ACTIVE && $this->status != self::STATUS_SUSPENDED){
            return false;
        }
        if ($this->cancelled_at == null){
            return false;
        }
        if ($this->cancelled_at->isPast()){
            return false;
        }
        return true;
    }

    public function markAsCancelled()
    {
        $this->status = self::STATUS_CANCELLED;
        $this->invoice_id = null;
        $this->is_cancelled = true;
        if ($this->notes != null){
            $this->notes = $this->notes . ' | Service was cancelled '. $this->cancelled_reason .' on ' . date('Y-m-d H:i:s');;
        } else {
            $this->notes = 'Service was cancelled '. $this->cancelled_reason .' on ' . date('Y-m-d H:i:s');
        }
        $reason = $this->cancelled_reason ?? 'Service expired';
        $date = $this->cancelled_at ?? now();
        ActionLog::log(ActionLog::SERVICE_CANCELLED, get_class($this), $this->id, auth('admin')->id(), auth('web')->id(), ['reason' => $reason, 'date' => $date->format('Y-m-d H:i:s')]);
        $this->save();

        try {
            $server = $this->productType()->server();
            if ($server != null){
                $result = $server->expireAccount($this);
                if ($result->success) {
                    if ($this->product_id != null){
                        Product::addStock($this->product_id);
                    }
                }
            }
        } catch (ExternalApiException $e) {
        }
        event(new ServiceCancelled($this));
    }

    public function addDays(int $days): ServiceStateChangeDTO
    {
        if ($this->expires_at == null){
            return new ServiceStateChangeDTO($this, false, 'Service has no expiration date');
        }
        $this->last_expires_at = $this->expires_at;
        $this->expires_at = $this->expires_at->addDays($days);
        $server = $this->productType()->server();
        if ($server != null){
            $server->onRenew($this);
        }
        $this->save();
        return new ServiceStateChangeDTO($this, true, 'Service expiration date updated');
    }

    public function subDays(int $days): ServiceStateChangeDTO
    {
        if ($this->expires_at == null){
            return new ServiceStateChangeDTO($this, false, 'Service has no expiration date');
        }
        $this->last_expires_at = $this->expires_at;
        $this->expires_at = $this->expires_at->subDays($days);
        $server = $this->productType()->server();
        if ($server != null){
            $server->onRenew($this);
        }
        $this->save();
        return new ServiceStateChangeDTO($this, true, 'Service expiration date updated');
    }
}
