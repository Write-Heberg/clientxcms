<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@php($pricing = $product->getPriceByCurrency(currency()))

<div class="flex flex-col border-2 border-blue-600 text-center shadow-xl rounded-xl p-8 dark:border-blue-700">
    <p class="mb-3"><span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-lg text-xs uppercase font-semibold bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-white">Most popular</span></p>
    <h4 class="font-medium text-lg text-gray-800 dark:text-gray-200">{{ $product->name }}</h4>
    @if ($product->isPersonalized())
    <span class="mt-5 font-bold text-5xl text-gray-800 dark:text-gray-200">
        {{ __('store.product.personalized') }}
        </span>
    @elseif ($pricing->isFree())
            <span class="mt-5 font-bold text-5xl text-gray-800 dark:text-gray-200">
        {{ __('global.free') }}
      </span>
    @else
        <span class="mt-5 font-bold text-5xl text-gray-800 dark:text-gray-200">
        {{ $pricing->dbprice }}
        <span class="font-bold text-2xl -me-2">{{ $pricing->getSymbol() }}{{ $pricing->taxTitle() }}</span>

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
        <a href="{{ $product->basket_url() }}" class="btn-product-pinned">
        {{ $product->basket_title() }}
            @include("shared.icons.array-right")
        </a>
        @endif
</div>
