<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Admin\Metadata;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMetadataRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'metadata_key.*' => 'required|string|max:100',
            'metadata_value.*' => 'nullable|string|max:1000',
            'model' => 'required|string|max:100',
            'model_id' => 'required|integer',
        ];
    }

    public function getRedirectUrl()
    {
        return $this->redirector->getUrlGenerator()->previous();
    }
}
