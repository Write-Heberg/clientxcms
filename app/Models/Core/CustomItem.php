<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Core;

use App\Contracts\Store\ProductTypeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomItem extends Model
{
    use HasFactory;

    const CUSTOM_ITEM = 'custom_item';
    protected $fillable = [
        'name',
        'description',
        'unit_price',
        'unit_setupfees'
    ];

    public function productType(): ProductTypeInterface
    {
        return new NoneProductType();
    }
}
