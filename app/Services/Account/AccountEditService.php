<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Services\Account;

use App\Helpers\Countries;
use App\Rules\ZipCode;
use Illuminate\Validation\Rule;

class AccountEditService
{

    public static function rules(string $country, bool $email = false, bool $password = false, ?int $except = null): array
    {
        $rules = [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'phone' => ['required', Countries::rule(), 'max:255',Rule::unique('customers')->ignore($except), 'regex:/^[0-9\+\-\(\)\/\s]*$/'],
            'zipcode' => ['required', 'string', 'max:255', new ZipCode($country)],
            'region' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255', Rule::in(array_keys(Countries::names()))],
        ];
        if ($email) {
            $rules['email'] = ['required', 'string', 'lowercase','email', 'max:255', Rule::unique('customers')->ignore($except),
                function($attribute, $value, $fail) {
                    if (str_contains($value, '+')) {
                        $fail('The :attribute must not contain the character "+".');
                    }
                },];
        }
        if ($password) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }
        return $rules;
    }

    public static function saveCurrentCustomer(array $all): bool
    {
        $filtred = [
            'firstname' => $all['firstname'],
            'lastname' => $all['lastname'],
            'address' => $all['address'],
            'address2' => $all['address2'],
            'city' => $all['city'],
            'zipcode' => $all['zipcode'],
            'phone' => $all['phone'],
            'region' => $all['region'],
            'country' => $all['country'],
        ];
        $customer = auth('web')->user();
        return $customer->update($filtred);
    }
}
