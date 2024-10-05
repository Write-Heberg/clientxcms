<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Admin\Invoice;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceDraftRequest extends FormRequest
{


    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'unit_price' => 'required|numeric|min:0',
            'unit_setupfees' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'related_id' => 'required|int',
            'related' => 'required|string|in:product,service,custom_item',
            'billing' => ['nullable', 'string']
            ];
        if ($this->related_id && $this->related == 'product'){
            $rules['related_id'] = 'exists:products,id';
        } else if ($this->related_id && $this->related == 'service'){
            $rules['related_id'] = 'exists:services,id';
        }
        return $rules;
    }

    public function failedValidation(Validator $validator)
    {
        \Session::flash('error', collect($validator->errors())->map(function ($item) {
            return $item[0];
        })->implode('<br>'));
        return parent::failedValidation($validator);
    }
}
