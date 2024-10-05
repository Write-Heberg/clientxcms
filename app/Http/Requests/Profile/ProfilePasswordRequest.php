<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Profile;

use App\Helpers\Countries;
use App\Rules\Valid2FACodeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\RequiredIf;

class ProfilePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', Password::default()],
            'currentpassword' => ['required', 'current_password'],
            '2fa' => [new RequiredIf($this->user('web')->twoFactorEnabled()), 'required', 'string', 'size:6', new Valid2FACodeRule()],
        ];
    }
}
