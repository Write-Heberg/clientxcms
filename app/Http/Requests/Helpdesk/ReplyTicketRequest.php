<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Helpdesk;

use App\Rules\NoScriptOrPhpTags;
use Illuminate\Foundation\Http\FormRequest;

class ReplyTicketRequest extends FormRequest
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
        $maxFileSize = setting('helpdesk_attachments_max_size', 10);
        $maxFileSize = $maxFileSize * 1024;
        $allowedMimes = setting('helpdesk_attachments_allowed_types', 'jpg,jpeg,png,gif,pdf,doc,docx,txt,zip');

        return [
            'content' => 'required|min:5|string|max:10000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => [
                'file',
                'mimes:' . $allowedMimes,
                'max:' . $maxFileSize,
                new NoScriptOrPhpTags(),
            ],
        ];
    }
}
