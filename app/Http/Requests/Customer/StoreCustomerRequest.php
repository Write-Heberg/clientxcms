<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Customer;

use App\Helpers\Countries;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use Illuminate\Validation\Rules;

class StoreCustomerRequest extends FormRequest
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:customers'],
            'password' => ['nullable', Rules\Password::defaults()],
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'zipcode' => ['required', 'string', 'max:255', 'regex:/^(?:(\d{5})(?:[ \-](\d{4}))?)$/i'],
            'phone' => ['required', 'phone:FR', 'max:255','unique:customers,phone'],
            'region' => ['required', 'string', 'max:255'],
            'verified' => ['nullable', 'boolean'],
            'balance' => ['numeric', 'min:0', 'max:9999999999'],
            'country' => ['required', 'string', 'max:255', Rule::in(array_keys(Countries::names()))],
        ];
    }
}
