<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Providers;

use App\Events\Core\CheckoutCompletedEvent;
use App\Events\Core\Invoice\InvoiceCompleted;
use App\Events\Core\Invoice\InvoiceCreated;
use App\Events\Core\Service\ServiceRenewed;
use App\Events\Helpdesk\HelpdeskTicketAnsweredCustomer;
use App\Events\Helpdesk\HelpdeskTicketCreatedEvent;
use App\Events\Resources\ResourceCloneEvent;
use App\Events\Resources\ResourceCreatedEvent;
use App\Events\Resources\ResourceDeletedEvent;
use App\Events\Resources\ResourceUpdatedEvent;
use App\Listeners\Core\CreateServiceListener;
use App\Listeners\Core\LastCronRunSaved;
use App\Listeners\Core\RenewServiceListerner;
use App\Listeners\Core\SendInvoiceNotification;
use App\Listeners\Core\WebhookNotification;
use App\Listeners\LogSentMessage;
use App\Listeners\NewLoginAccount;
use App\Listeners\Resources\ResourceListener;
use App\Listeners\Store\Basket\BasketMerge;
use App\Listeners\Store\Basket\CouponUsageListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSending;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            BasketMerge::class,
            NewLoginAccount::class,
        ],
        MessageSending::class => [
            LogSentMessage::class,
        ],
        InvoiceCompleted::class => [
            CreateServiceListener::class,
            RenewServiceListerner::class,
            SendInvoiceNotification::class,
        ],
        ServiceRenewed::class => [
            WebhookNotification::class,
        ],
        InvoiceCreated::class => [
            SendInvoiceNotification::class,
        ],
        CheckoutCompletedEvent::class => [
            WebhookNotification::class,
            CouponUsageListener::class,
        ],
        ScheduledTaskStarting::class => [
            LastCronRunSaved::class
        ],
        HelpdeskTicketCreatedEvent::class => [
            WebhookNotification::class
        ],
        HelpdeskTicketAnsweredCustomer::class => [
            WebhookNotification::class
        ],
        ResourceCloneEvent::class => [
            ResourceListener::class
        ],
        ResourceUpdatedEvent::class => [
            ResourceListener::class
        ],
        ResourceCreatedEvent::class => [
            ResourceListener::class
        ],
        ResourceDeletedEvent::class => [
            ResourceListener::class
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
