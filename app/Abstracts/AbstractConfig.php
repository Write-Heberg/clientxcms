<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Abstracts;

use App\DTO\Store\ProductDataDTO;
use App\Models\Provisioning\Server;
use App\Models\Store\Product;
use Illuminate\Database\Eloquent\Collection;

class AbstractConfig implements \App\Contracts\Store\ProductConfigInterface
{
    protected string $model;
    protected string $type;
    protected Collection $servers;

    public function __construct()
    {
        if ($this->type != null) {
            $this->servers = Server::where('type', $this->type)->where('status', 'active')->get();
        }
    }

    public function validate(): array
    {
        return [];
    }

    public function render(Product $product)
    {
        return 'Not implemented';
    }

    public function storeConfig(Product $product, array $parameters)
    {
        $this->model::insert($parameters + ['product_id' => $product->id]);
    }

    public function updateConfig(Product $product, array $parameters)
    {
        $this->model::where('product_id', $product->id)->update($parameters);
    }

    public function deleteConfig(Product $product)
    {
        $this->model::where('product_id', $product->id)->delete();
    }

    public function getConfig(int $id, $entity = null)
    {
        return $this->model::where('product_id', $id)->first() ?? $entity;
    }

    public function cloneConfig(Product $old, Product $new)
    {
        $config = $this->getConfig($old->id);
        if ($config) {
            $config = $config->toArray();
            unset($config['id']);
            unset($config['product_id']);
            unset($config['created_at']);
            unset($config['updated_at']);
            $config['product_id'] = $new->id;
            $this->storeConfig($new, $config);
        }
    }
}
