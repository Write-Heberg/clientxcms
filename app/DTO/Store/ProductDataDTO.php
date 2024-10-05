<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Store;

use App\Models\Store\Product;

class ProductDataDTO
{
    public Product $product;
    public array $data;
    public array $options;
    public array $parameters;

    /**
     * @param Product $product
     * @param array $data - Data already stored in the database (if any) (basket or invoice item)
     * @param array $parameters - Data from the request
     * @param array $options - Additional options (if any)
     */
    public function __construct(Product $product, array $data, array $parameters, array $options = [])
    {
        $this->product = $product;
        $this->data = $data;
        $this->options = $options;
        $this->parameters = $parameters;
    }
}
