<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Customer;

use App\Helpers\Countries;
use App\Rules\ZipCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use Illuminate\Validation\Rules;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['string', 'lowercase', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($this->id)],
            'firstname' => ['required','string', 'max:255'],
            'lastname' => ['string', 'max:255'],
            'address' => ['string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'city' => ['string', 'max:255'],
            'zipcode' => ['required', 'string', 'max:255', new ZipCode($this->country ?? 'FR')],
            'phone' => ['max:255', Rule::unique('customers', 'phone')->ignore($this->id)],
            'region' => ['string', 'max:255'],
            'verified' => ['nullable', 'boolean'],
            'balance' => ['numeric', 'min:0', 'max:9999999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'password' => ['nullable', 'string', 'min:8', Rules\Password::defaults()],
            'country' => ['string', 'max:255', Rule::in(array_keys(Countries::names()))],
        ];
    }
}
