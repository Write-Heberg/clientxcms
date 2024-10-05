<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Services\Store;

use App\Contracts\Store\ProductTypeInterface;
use Illuminate\Support\Collection;

class ProductTypeService
{
    private Collection $types;

    public function __construct()
    {
        $this->types = collect();
    }

    public function registerType(ProductTypeInterface $type): void
    {
        $this->types->put($type->uuid(), $type);
    }

    public function has(string $key): bool
    {
        return $this->types->has($key);
    }

    public function forProduct(string $type): ?ProductTypeInterface
    {
        return $this->types->get($type);
    }

    public function all(): Collection
    {
        return $this->types;
    }

    public function addProductType(ProductTypeInterface $productType): void
    {
        $this->types = $this->types->merge([$productType->uuid() => $productType]);
    }
}
