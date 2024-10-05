<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:active,hidden,unreferenced',
            'slug' => 'required|string|max:255|unique:groups,slug',
            'sort_order' => 'required|integer',
            'pinned' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'parent_id' => 'nullable|integer|exists:groups,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'parent_id' => $this->parent_id == 'none' ? null : $this->parent_id,
            'pinned' => $this->pinned == 'true' ? '1' : '0',
        ]);
    }
}
