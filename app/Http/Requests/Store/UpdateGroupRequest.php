<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupRequest extends FormRequest
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
            'name' => 'string|max:255',
            'description' => 'string',
            'status' => 'string|in:active,hidden,unreferenced',
            'slug' => ['string', 'max:255', Rule::unique('groups')->ignore($this->route('group'))],
            'sort_order' => 'integer',
            'pinned' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'parent_id' => ['nullable','integer'],
            'remove_image' => 'nullable|string|in:true,false',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'parent_id' => $this->parent_id == 'none' ? null : (int)$this->parent_id,
            'pinned' => $this->pinned == 'true' ? '1' : '0',
        ]);
    }

}
