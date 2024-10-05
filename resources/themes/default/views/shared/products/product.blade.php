<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@php($pricing = $product->getPriceByCurrency(currency()))

<div class="flex flex-col border border-gray-200 text-center rounded-xl p-8 dark:border-gray-700">
    <h4 class="font-medium text-lg text-gray-800 dark:text-gray-200">{{ $product->name }}</h4>
    @if ($pricing->isFree())

        <span class="mt-5 font-bold text-5xl text-gray-800 dark:text-gray-200">
        {{ __('global.free') }}
      </span>

    @elseif ($product->isPersonalized())
        <span class="mt-5 font-bold text-5xl text-gray-800 dark:text-gray-200">
        {{ __('store.product.personalized') }}
        </span>
        @else
    <span class="mt-5 font-bold text-5xl text-gray-800 dark:text-gray-200">
        {{ $pricing->dbprice }}
        <span class="font-bold text-2xl -me-2">{{ $pricing->getSymbol() }}{{ is_tax_included() ? __('store.ttc') : '' }}</span>

      </span>

    @if ($pricing->hasSetup())
        <p class="mt-2 text-sm text-gray-500">{{ $pricing->pricingMessage() }}</p>
    @endif
    @endif
    <ul class="mt-7 space-y-2.5 text-sm">
        {!!  $product->description !!}
    </ul>

    @if ($product->isOutOfStock())
        <button class="btn-product-pinned">
            {{ __('store.product.outofstock') }}
            @include("shared.icons.slash")
        </button>
    @else
        <a href="{{ $product->basket_url() }}" class="btn-product">
            {{ $product->basket_title() }}
            @include("shared.icons.array-right")
        </a>
    @endif
</div>
