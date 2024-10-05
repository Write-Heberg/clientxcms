<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Store;

use App\Rules\PricingValidation;
use App\Traits\PricingRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    use PricingRequestTrait;
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
        $types = app('extension')->getProductTypes()->keys()->merge(['none'])->toArray();
        return array_merge([
            'name' => 'string|max:255',
            'description' => 'string',
            'status' => 'string|in:active,hidden,unreferenced',
            'group_id' => 'integer|exists:groups,id',
            'stock' => 'integer',
            'type' => ['string', Rule::in($types)],
            'pinned' => 'nullable|boolean',
        ], $this->pricingRules());
    }

    protected function prepareForValidation()
    {
        $pricing = $this->input('pricing', []);

        $convertedPricing = $this->prepareForPricing($pricing);

        $this->merge([
            'pricing' => $convertedPricing,
            'pinned' => $this->pinned == 'true' ? '1' : '0',
        ]);
    }
}
