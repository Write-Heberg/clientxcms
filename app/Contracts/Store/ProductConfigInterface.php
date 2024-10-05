<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Contracts\Store;

use App\DTO\Store\ProductDataDTO;
use App\Models\Store\Product;

interface ProductConfigInterface
{
    public function validate():array;

    public function render(Product $product);

    public function storeConfig(Product $product, array $parameters);

    public function updateConfig(Product $product, array $parameters);

    public function deleteConfig(Product $product);

    public function getConfig(int $id, $entity = null);

    public function cloneConfig(Product $oldProduct, Product $newProduct);
}
