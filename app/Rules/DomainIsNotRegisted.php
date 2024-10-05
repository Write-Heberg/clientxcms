<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Rules;

class DomainIsNotRegisted implements \Illuminate\Contracts\Validation\Rule
{

    public function __construct(private bool $subdomain = false)
    {

    }
    public function passes($attribute, $value): bool
    {
        if ($value == null) {
            return true;
        }
        if ($this->subdomain) {
            $value = $value . '.' . request()->input('subdomain');
        }
        $types = app('extension')->getProductTypes();
        foreach ($types as $type) {
            if ($type->server() != null) {
                $server = $type->server();
                if ($server->isDomainRegistered($value)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function message(): string
    {
        return 'This domain is already registered.';
    }
}
