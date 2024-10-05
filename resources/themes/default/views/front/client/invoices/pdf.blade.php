<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('client.invoices.details') }} - {{ $invoice->identifier() }}</title>

    <style type="text/css">
        * {
            font-family: Verdana, Arial, sans-serif;
        }
        table{
            font-size: x-small;
        }
        .bordered {
            border: 1px solid lightgray;
            padding: 10px;
        }
        .bordered-sm {
            border: 1px solid lightgray;
            padding: 5px;
        }
        tfoot tr td{
            font-weight: bold;
            font-size: x-small;
        }

        .gray {
            background-color: lightgray
        }
        .thead {
            background-color: lightgray;
        }
        .thead th {
            padding: 20px;
        }
        h2 {
            margin-bottom: 0;
        }
        body {
            font-size: 14px;

            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            overflow: hidden;
        }
        .invoice-badge {
            padding: 5px;
            border-radius: 5px;
            color: white;
        }
        .invoice-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .invoice-pending, .invoice-draft {
            background-color: #fff3cd;
            color: #856404;
        }
        .invoice-refunded, .invoice-cancelled {
            background-color: #cce5ff;
            color: #004085;
        }
        .invoice-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
        .detail-label {
            font-weight: bold;
        }
        .invoice-alert {
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 2em;
            text-align: center;
            position: absolute;
            top: 55%;
            left: 70%;
            z-index: 10;
        }
    </style>
</head>
<body>

<div class="invoice-alert invoice-{{ $invoice->status }}">
    {{ __('global.states.' . $invoice->status) }}
</div>
<table width="100%">
    <tr>

        <td>
            <h3>{{ __('client.invoices.details') }}</h3>
            <span><span class="detail-label">{{ __('client.invoices.invoice_date') }}</span>: {{ $invoice->created_at->format('d/m/Y') }}<br/><span class="detail-label">{{ __('client.invoices.due_date') }}</span>: {{ $invoice->due_date->format('d/m/Y') }}<br><span class="detail-label">{{ __('client.invoices.paymethod') }}</span>: {{ $invoice->gateway != null ? $invoice->gateway->name : $invoice->paymethod }}@if ($invoice->paymethod == 'bank_transfert' && $invoice->status != 'paid')<br/>{{ __('client.invoices.banktransfer.title') }}:<br/>{!! nl2br(setting("bank_transfert_details", "You can change this details in Bank transfer configuration.")) !!} @endif<br><br/><span class="invoice-badge invoice-{{ $invoice->status }}">{{ __('global.states.'. $invoice->status) }}</span><br/>
            </span>
        </td>

        <td colspan="">
            <h2>
                {{ __('global.invoice') }} #{{ $invoice->identifier() }}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="display: block;">
            <h3>{{ setting('app.name') }}</h3>
            <pre>{!! setting('app.address') !!}</pre>
        </td>
        <td>
            <h3>{{ __('client.invoices.billto', ['name' => $customer->firstname . ' ' . $customer->lastname]) }}</h3>
            <pre>{{ $customer->email }}<br>{{ $customer->address }} {{ $customer->address2 != null ? ',' . $customer->address2 : '' }}<br>{{ $customer->region }}, {{ $customer->city }} , {{ $customer->zipcode }}<br/>{{ $countries[$customer->country] }}<br>
            </pre>
        </td>
    </tr>

</table>
<br/>

<table width="100%" class="table">
    <thead class="thead">
    <tr>
        <th class="bordered">#</th>
        <th class="bordered">{{ __('client.invoices.itemname') }}</th>
        <th class="bordered">{{ __('client.invoices.qty') }}</th>
        <th class="bordered">{{ __('store.unit_price') }}</th>
        <th class="bordered">{{ __('store.setup_price') }}</th>
        <th class="bordered" >{{ __('store.price') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($invoice->items as $item)

        <tr>
        <th scope="row" class="bordered">{{ $item->id }}</th>
        <td class="bordered">{{ $item->name }}
            @if($item->canDisplayDescription())
                <br/><small>{{ $item->description }}</small>
            @endif
            <br/>
            @if ($item->getDiscount(false))
                <small>{{ $item->getDiscountLabel() }}</small>
            @endif
        </td>
        <td align="right" class="bordered">{{ $item->quantity }}</td>
        <td align="right" class="bordered">{{ formatted_price($item->unit_price, $invoice->currency) }}
            @if ($item->getDiscount() != null && $item->getDiscount()->discount_price > 0)
                <br/><small>-{{ formatted_price($item->getDiscount()->discount_unit_price, $invoice->currency) }}</small>
            @endif
        </td>
            <td align="right" class="bordered">{{ formatted_price($item->unit_setupfees, $invoice->currency) }}
                @if ($item->getDiscount() != null && $item->getDiscount()->discount_setup > 0)

                    <br/><small>-{{ formatted_price($item->getDiscount()->discount_unit_setup, $invoice->currency) }}</small>
                @endif
            </td>
            <td align="right" class="bordered">{{ formatted_price($item->price(), $invoice->currency) }}
                @if ($item->getDiscount() != null && $item->getDiscount()->discount_price > 0)
                    <br/><small>-{{ formatted_price($item->getDiscount()->discount_price + $item->getDiscount()->discount_setup, $invoice->currency) }}</small>
                @endif
            </td>
    </tr>
    @endforeach
    </tbody>

    <tfoot>
    @if ($invoice->getDiscountTotal() > 0)
    <tr>
        <td colspan="4"></td>
        <td align="right" class="bordered-sm">{{ __('coupon.coupon') }}</td>
        <td align="right" class="bordered-sm">-{{ formatted_price($invoice->getDiscountTotal(), $invoice->currency) }}</td>
    </tr>
    @endif
    <tr>
        <td colspan="4"></td>
        <td align="right" class="bordered-sm">{{ __('store.subtotal') }}</td>
        <td align="right" class="bordered-sm">{{ formatted_price($invoice->subtotal, $invoice->currency) }}</td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td align="right" class="bordered-sm">{{ __('store.vat') }}</td>
        <td align="right" class="bordered-sm">{{ formatted_price($invoice->tax, $invoice->currency) }}</td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td align="right" class="bordered-sm">{{ __('store.total') }}</td>
        <td align="right" class="gray bordered-sm">{{ formatted_price($invoice->total, $invoice->currency) }}</td>
    </tr>
    </tfoot>
</table>
<table width="100%">
    <tr>
        <td>
            @if ($invoice->paymethod == 'bank_transfert' && $invoice->status != 'paid')
                <h3>{{ __('client.invoices.banktransfer.title') }}</h3>
                <span>
                    {!! nl2br(setting("bank_transfert_details", "You can change this details in Bank transfer configuration.")) !!}
                </span>
            @elseif ($invoice->status == 'paid')
                <h3>{{ __('client.invoices.thank') }}</h3>
                <span>
                    {{ __('client.invoices.thankmessage') }}
                </span>
            @endif
        </td>
    </tr>

    <table width="100%">
        @if (!empty(setting("invoice_terms")))
        <tr>
            <td>
                <h3>{{ __('client.invoices.terms') }}</h3>
                <span>
                    {!! nl2br(setting("invoice_terms", "You can change this details in Invoice configuration.")) !!}
                </span>
            </td>
        </tr>
        @endif
        <tr>
            <td>
                    <h3>{{ date('Y') }} {{ config('app.name') }}.</h3>
            </td>
        </tr>
    </table>
</body>
</html>
