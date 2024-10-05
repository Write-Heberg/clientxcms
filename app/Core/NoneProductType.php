<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Core;

use App\Abstracts\AbstractProductType;

class NoneProductType extends AbstractProductType {

    protected string $uuid = 'none';
    protected string $title = 'None';
    protected string $type = 'service';

    public function server(): ?\App\Contracts\Provisioning\ServerTypeInterface
    {
        return new NoneServerType();
    }
}
