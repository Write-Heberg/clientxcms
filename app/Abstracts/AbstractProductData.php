<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Abstracts;

use App\Contracts\Store\ProductDataInterface;
use App\DTO\Store\ProductDataDTO;

abstract class AbstractProductData implements ProductDataInterface
{
    protected array $parameters = [];

    public function primary(ProductDataDTO $productDataDTO): string
    {
        return $this->parameters[0] ?? '';
    }

    public function validate():array
    {
        return [];
    }

    public function parameters(ProductDataDTO $productDataDTO): array
    {
        return collect($productDataDTO->parameters)->filter(function ($value, $key)  {
            return in_array($key, $this->parameters);
        })->toArray();
    }

    public function render(ProductDataDTO $productDataDTO)
    {
        return 'Please override render method in your product data class';
    }
}
