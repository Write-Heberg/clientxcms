<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Contracts\Store;

use App\DTO\Store\ProductDataDTO;
use Illuminate\Validation\Validator;

interface ProductDataInterface
{
    public function primary(ProductDataDTO $productDataDTO):string;
    public function validate():array;
    public function parameters(ProductDataDTO $productDataDTO):array;
    public function render(ProductDataDTO $productDataDTO);

}
