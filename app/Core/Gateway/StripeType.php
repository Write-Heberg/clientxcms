<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Core\Gateway;

use App\Abstracts\AbstractGatewayType;
use App\DTO\Core\Gateway\GatewayUriDTO;
use App\Exceptions\WrongPaymentException;
use App\Helpers\EnvEditor;
use App\Models\Account\Customer;
use App\Models\Core\Gateway;
use App\Models\Core\Invoice;
use App\Models\Core\InvoiceItem;
use App\Services\Store\TaxesService;
use Illuminate\Http\Request;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeType extends AbstractGatewayType
{
    const UUID = 'stripe';
    const VERSION = '2023-10-16';
    protected string $name = 'Stripe';
    protected string $uuid = self::UUID;
    private ?StripeClient $stripe = null;
    protected string $image = 'stripe-icon.png';
    protected string $icon = 'bi bi-stripe';

    public function createPayment(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto)
    {
        $rate = $this->getStripeRate($invoice->customer);
        $items = $invoice->items->map(function (InvoiceItem $item) use ($invoice, $rate) {
            $price = $item->unit_original_price + $item->unit_original_setupfees;
            $discount = 0;
            if ($item->hasDiscount()){
                $discount = $item->getDiscount()->discount_unit_price + $item->getDiscount()->discount_unit_setup;
            }
            return [
                    'price_data' => [
                        'currency' => $invoice->currency,
                        'unit_amount' => (int)(($price - $discount) * 100),
                        'product_data' => [
                            'name' => $item->name,
                        ],
                    ],
                    'tax_rates' => [$rate],
                    'quantity' => $item->quantity,
                ];
        })->toArray();
        $this->initStripe();
        $customer = $this->getCustomerStripe($invoice->customer);
        try {
            $session = \Stripe\Checkout\Session::create([
                'customer' => $customer->id,
                'payment_method_types' => $this->getPaymentMethodTypes(),
                'line_items' => $items,
                'mode' => 'payment',
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->customer->id,
                ],
                'success_url' => $dto->returnUri,
                'cancel_url' => $dto->cancelUri
            ]);
        } catch (InvalidRequestException $e){
            throw new WrongPaymentException('Payment method type is invalid. : ' . $e->getMessage());
        }

        return redirect($session->url);
    }

    public function processPayment(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto)
    {
        return redirect()->route('front.invoices.show', $invoice->id);
    }

    public function notification(Gateway $gateway, Request $request)
    {
        $signature = $request->header('Stripe-Signature');
        if (env('STRIPE_WEBHOOK_SECRET') == null){
            return response()->json(['error' => 'Stripe webhook secret not found'], 400);
        }
        try {
            $event = \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $signature,
                env('STRIPE_WEBHOOK_SECRET')
            );
            $this->initStripe();
            if ($event->type == 'checkout.session.completed') {

                $object = $event->data->object;
                $id = $object->metadata->invoice_id ?? 0;
                $invoice = Invoice::find($id);
                if ($invoice == null) {
                    return response()->json(['error' => 'Invoice not found'], 400);
                }
                $intent = \Stripe\PaymentIntent::retrieve($object->payment_intent);

                $invoice->update(['external_id' => $object->id, 'fees' => $intent->application_fee_amount / 100]);
                $invoice->complete();
                return response()->json(['success' => 'Invoice paid', 'fees' => $intent->application_fee_amount / 100]);
            }
        } catch (\UnexpectedValueException|SignatureVerificationException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function saveConfig(array $data)
    {
        EnvEditor::updateEnv([
            'STRIPE_PRIVATE_KEY' => $data['private_key'],
            'STRIPE_PUBLIC_KEY' => $data['public_key'],
            'STRIPE_WEBHOOK_SECRET' => $data['webhook_secret'],
            'STRIPE_PAYMENT_TYPES' => implode(',', $data['payment_types']),
        ]);
    }

    public function validate(): array
    {
        return [
            'private_key' => 'required|string',
            'public_key' => 'required|string',
            'webhook_secret' => 'required|string',
            'payment_types' => 'required|array',
        ];
    }

    public function configForm(array $context = [])
    {
        $context['options'] = ["acss_debit","affirm","paypal","afterpay_clearpay","alipay","au_becs_debit","bacs_debit","bancontact","blik","boleto","card","customer_balance","eps","fpx","giropay","grabpay","ideal","klarna","konbini","link","oxxo","p24","paynow","pix","promptpay","sepa_debit","sofort","us_bank_account","wechat_pay"];
        $context['options'] = collect($context['options'])->mapWithKeys(fn ($option) => [$option => $option])->toArray();
        return view('admin.settings.store.gateways.stripe', $context);
    }

    /**
     * @throws WrongPaymentException
     * @return StripeClient
     */
    private function initStripe(): StripeClient
    {
        if ($this->stripe == null) {
            $privateKey = env('STRIPE_PRIVATE_KEY');
            $publicKey = env('STRIPE_PUBLIC_KEY');
            if ($privateKey == null || $publicKey == null) {
                throw new WrongPaymentException('Stripe keys not found');
            }
            $stripe = new StripeClient($privateKey);
            Stripe::setApiKey($privateKey);
            Stripe::setApiVersion(self::VERSION);
            $this->stripe = $stripe;
        }
        return $this->stripe;
    }

    private function getCustomerStripe(Customer $customer):\Stripe\Customer
    {
        $customers = $this->stripe->customers->search([
            'query' => "email:" . '"'.$customer->email . '"',
        ]);
        if(empty($customers->data)) {
            return $this->stripe->customers->create([
                'email' => $customer->email,
                'name' => $customer->firstname . ' ' . $customer->lastname,
                'phone' => $customer->phone,
                'address' => [
                    'line1' => $customer->address,
                    'city' => $customer->city,
                    'postal_code' => $customer->zipcode,
                    'country' => $customer->country,
                ],
                'metadata' => [
                    'id' => $customer->id,
                ],
            ]);
        } else {
            return $customers->data[0];
        }
    }

    private function getPaymentMethodTypes()
    {
        return explode(',', env('STRIPE_PAYMENT_TYPES', 'card'));
    }

    private function getStripeRate(Customer $customer): ?string
    {
        $this->initStripe();
        $rates = $this->stripe->taxRates->all();
        foreach ($rates as $rate) {
            if ($rate->country == $customer->country && $rate->active && $rate->inclusive == !is_tax_excluded()){
                return $rate->id;
            }
        }
        return $this->stripe->taxRates->create([
            'display_name' => 'TVA',
            'description' => 'VAT '. $customer->country . ' '  . !is_tax_excluded() ? 'Included' : 'Excluded',
            'country' => $customer->country,
            'percentage' => tax_percent(),
            'inclusive' => !is_tax_excluded(),
        ])->id;
    }
}
