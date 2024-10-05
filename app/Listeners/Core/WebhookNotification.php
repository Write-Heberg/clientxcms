<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Listeners\Core;

use App\DTO\Core\WebhookDTO;
use App\Events\Core\CheckoutCompletedEvent;
use App\Events\Core\Service\ServiceRenewed;
use App\Events\Helpdesk\HelpdeskTicketAnsweredCustomer;
use App\Events\Helpdesk\HelpdeskTicketCreatedEvent;
use App\Models\Core\Invoice;

class WebhookNotification
{
    private array $webhooks = [];
    public function handle($event) :void{
        $this->registerWebhook();
        if ($this->inWebhookList($event)) {
            $this->sendWebhook($event);
        }
    }

    private function inWebhookList($event): bool
    {
        return collect($this->webhooks)->contains('event', get_class($event));
    }

    private function sendWebhook($event): void
    {
        /** @var WebhookDTO $webhook */
        $webhook = collect($this->webhooks)->firstWhere('event', get_class($event));
        if (!$webhook) {
            return;
        }
        if ($webhook->isDisabled()){
            return;
        }
        $webhook->send([$event]);
    }

    private function registerWebhook(): void
    {
        $this->webhooks[] = new WebhookDTO(HelpdeskTicketAnsweredCustomer::class, function () {
            return [
                'content' => null,
                'embeds' => [
                    [
                        'title' => __('webhook.ticket_answer.title'),
                        'description' => __('webhook.ticket_answer.description'),
                        'color' => 0x00ff00,
                        'fields' => [
                            [
                                'name' => __('webhook.domain'),
                                'value' => '[`🌍`]  %appurl%',
                                'inline' => true
                            ],
                            [
                                'name' => __('client.support.subject'),
                                'value' => '[`📝`]  %subject%',
                                'inline' => true
                            ],
                            [
                                'name' => __('global.email'),
                                'value' => '[`📙`]  [%customeremail%](%customer_url%)',
                                'inline' => true
                            ],
                            [
                                'name' => __('client.support.department'),
                                'value' => '[`📂`]  %department%',
                                'inline' => true
                            ],

                            [
                                'name' => __('client.support.priority'),
                                'value' => '[`🔖`]  %priority%',
                                'inline' => true
                            ],

                            [
                                'name' => __('client.support.create.relatedto'),
                                'value' => '[`🔗`]  %relatedto%',
                                'inline' => true
                            ],
                            [
                                'name' => __('client.support.show.reply'),
                                'value' => '[`🔗`]  %__url%',
                                'inline' => true
                            ]
                        ],
                        'footer' => [
                            'text' => config('app.name'),
                            'icon_url' => 'https://ctxhosting.fr/Themes/Nixcloud/assets/images/LogoBlue.png',
                        ],
                        'timestamp' => now()->format('c')
                    ],
                ],
            ];
        }, function(HelpdeskTicketAnsweredCustomer $event) {
            return [
                '%__url%' => route('admin.helpdesk.tickets.show', $event->ticket->id),
                '%ticketid%' => $event->ticket->id,
                '%customer_url%' => route('admin.customers.show', $event->ticket->customer->id),
                '%subject%' => $event->ticket->subject,
                '%customername%' => $event->ticket->customer->fullName,
                '%customeremail%' => $event->ticket->customer->email,
                '%department%' => $event->ticket->department->name,
                '%priority%' => $event->ticket->priorityLabel(),
                '%message%' => $event->message->message,
                '%relatedto%' => $event->ticket->relatedTo != null ? $event->ticket->relatedTo->name : __('client.support.create.relatednone'),
            ];
        }, setting('helpdesk_webhook_url'), ['label' => __('View Ticket')]);
        $this->webhooks[] = new WebhookDTO(HelpdeskTicketCreatedEvent::class, function () {
            return [
                'content' => null,
                'embeds' => [
                    [
                    'title' => __('webhook.ticket.title'),
                    'description' => __('webhook.ticket.description'),
                    'color' => 0x00ff00,
                    'fields' => [
                        [
                            'name' => __('webhook.domain'),
                            'value' => '[`🌍`]  %appurl%',
                            'inline' => true
                        ],
                        [
                            'name' => __('client.support.subject'),
                            'value' => '[`📝`]  %subject%',
                            'inline' => true
                        ],
                        [
                            'name' => __('global.email'),
                            'value' => '[`📙`]  [%customeremail%](%customer_url%)',
                            'inline' => true
                        ],
                        [
                            'name' => __('client.support.department'),
                            'value' => '[`📂`]  %department%',
                            'inline' => true
                        ],
                        [
                            'name' => __('client.support.priority'),
                            'value' => '[`🔖`]  %priority%',
                            'inline' => true
                        ],
                        [
                            'name' => __('client.support.create.relatedto'),
                            'value' => '[`🔗`]  %relatedto%',
                            'inline' => true
                        ],
                        [
                            'name' => __('client.support.show.reply'),
                            'value' => '[`🔗`]  %__url%',
                            'inline' => true
                        ]
                    ],
                    'footer' => [
                        'text' => config('app.name'),
                        'icon_url' => 'https://ctxhosting.fr/Themes/Nixcloud/assets/images/LogoBlue.png',
                    ],
                    'timestamp' => now()->format('c')
                    ],
                    ],
            ];
        }, function(HelpdeskTicketCreatedEvent $event) {
            return [
                '%__url%' => route('admin.helpdesk.tickets.show', $event->ticket->id),
                '%ticketid%' => $event->ticket->id,
                '%customer_url%' => route('admin.customers.show', $event->ticket->customer->id),
                '%subject%' => $event->ticket->subject,
                '%customername%' => $event->ticket->customer->fullName,
                '%customeremail%' => $event->ticket->customer->email,
                '%message%' => $event->message->content,
                '%department%' => $event->ticket->department->name,
                '%priority%' => $event->ticket->priorityLabel(),
                '%relatedto%' => $event->ticket->relatedTo != null ? $event->ticket->relatedTo->name : __('client.support.create.relatednone'),
            ];
        }, setting('helpdesk_webhook_url'), ['label' => __('View Ticket')]);
        $this->webhooks[] = new WebhookDTO(ServiceRenewed::class, function() {
           return [
               'content' => null,
               'embeds' => [
                   [
                       'title' => __('webhook.renew.title'),
                       'description' => __('webhook.renew.description'),
                       'color' => 0x00ff00,
                       'fields' => [
                            [
                                'name' => __('webhook.domain'),
                                'value' => '[`🌍`]  %appurl%',
                                'inline' => true
                            ],
                            [
                                'name' => __('global.service'),
                                'value' => '[`🛠️`]  %serviceid%',
                                'inline' => true
                            ],
                            [
                                'name' => __('global.name'),
                                'value' => '[`🧾`] [%servicename%] (%__url%)',
                                'inline' => true
                            ],
                            [
                                'name' => __('global.email'),
                                'value' => '[`📙`]  [%customeremail%](%customer_url%)',
                                'inline' => true
                            ],
                           [
                               'name' => __('global.customer'),
                               'value' => '[`📗`] %customername%',
                               'inline' => true
                           ],
                           [
                           'name' => __('store.price'),
                           'value' => '[`💰`] %price% %currency%',
                           'inline' => true
                        ]
                       ],
                       'footer' => [
                           'text' => config('app.name'),
                            'icon_url' => 'https://ctxhosting.fr/Themes/Nixcloud/assets/images/LogoBlue.png',
                       ],
                       'timestamp' => now()->format('c')
                   ]
               ]
           ];
        }, function(ServiceRenewed $event) {
                $route = route('admin.services.show', $event->service->id);
                return [
                    '%__url%' => $route,
                    '%servicename%' => $event->service->name,
                    '%serviceid%' => $event->service->id,
                    '%customer_url%' => route('admin.customers.show', $event->service->customer->id),
                    '%expiresat%' => $event->service->expires_at->format('d/m/y'),
                    '%customername%' => $event->service->customer->fullName,
                    '%currency%' => currency_symbol($event->service->currency),
                    '%customeremail%' => $event->service->customer->email,
                    '%serviceurl%' => $route,
                    '%price%' => $event->service->price,
                ];
            },setting('core_services_webhook_url'), ['label' => __('View Service')]);
        $this->webhooks[] = new WebhookDTO(CheckoutCompletedEvent::class, function(){
              return [
                  'content' => null,
                  'embeds' => [
                      [
                          'title' => __('webhook.checkout.title'),
                          'description' => __('webhook.checkout.description'),
                          'color' => 0x00ff00,
                          'fields' => [
                              [
                                  'name' => __('webhook.domain'),
                                  'value' => '[`🌍`]  %appurl%',
                                  'inline' => true
                              ],
                              [
                                  'name' => __('store.basket.title'),
                                  'value' => '[`🛒`] # %basketid%',
                                  'inline' => true
                              ],
                              [
                                  'name' => __('store.total'),
                                  'value' => '[`💰`] %total% %currency% - %gatewayname%',
                                  'inline' => true
                              ],
                              [
                                  'name' => __('global.email'),
                                  'value' => '[`📙`]  [%customeremail%](%customer_url%)',
                                  'inline' => true
                              ],
                              [
                                  'name' => __('global.customer'),
                                  'value' => '[`📗`] %customername%',
                                  'inline' => true
                              ],
                              [
                                  'name' => __('global.products'),
                                  'value' => '[`🛍️`] %productnames%',
                                  'inline' => true
                              ]
                          ],
                          'footer' => [
                              'text' => config('app.name'),
                              'icon_url' => 'https://ctxhosting.fr/Themes/Nixcloud/assets/images/LogoBlue.png',
                          ],
                          'timestamp' => now()->format('c')
                      ]
                  ]
              ];
            },function(CheckoutCompletedEvent $event) {
            $customer = $event->basket->customer;
            if (!$customer) {
                return [];
            }
            if ($event->invoice->status != Invoice::STATUS_PAID){
                return [];
            }
            $route = route('admin.invoices.show', ['invoice' => $event->basket->getMetadata('invoice')]);
            return [
                '__url' => $route,
                '%customername%' => $customer->fullName,
                '%customeremail%' => $customer->email,
                '%basketid%' => $event->basket->id,
                '%customer_url%' => route('admin.customers.show', $customer->id),
                '%total%' => $event->basket->total(),
                '%currency%' => currency_symbol($event->basket->currency()),
                '%gatewayname%' => $event->invoice->gateway->name,
                '%invoiceurl%' => $route,
                '%productnames%' => $event->basket->items->map(function($item){
                    return $item->name();
                })->implode(', '),
            ];
        },setting('store_checkout_webhook_url'), ['label' => __('View Order')]);
    }
}
