<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Profile;

use App\Helpers\Countries;
use App\Rules\ZipCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'firstname' => ['required','string', 'max:255'],
            'lastname' => ['string', 'max:255'],
            'address' => ['string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'city' => ['string', 'max:255'],
            'zipcode' => ['string', 'max:255', new ZipCode($this->country)],
            'phone' => ['max:255',Countries::rule(), Rule::unique('customers', 'phone')->ignore($this->user('web')->id)],
            'region' => ['string', 'max:255'],
            'country' => ['string', 'max:255', Rule::in(array_keys(Countries::names()))],
        ];
    }
}
