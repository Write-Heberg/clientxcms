<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use PragmaRX\Google2FAQRCode\Google2FA;

class Valid2FACodeRule implements ValidationRule
{

    private ?string $secret = null;

    public function __construct(?string $secret = null)
    {
        $this->secret = $secret;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $google = new Google2FA();
        $user = auth('web')->user() ?? auth('admin')->user();
        $secret = $this->secret ?: $user->getMetadata('2fa_secret');
        if (! $google->verifyKey($secret, $value)) {
            $fail(__('validation.2fa_code'));
        }
    }
}
