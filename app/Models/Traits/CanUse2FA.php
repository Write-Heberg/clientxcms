<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Str;
use PragmaRX\Google2FAQRCode\Google2FA;

trait CanUse2FA
{
    public function twoFactorEnabled(): bool
    {
        return $this->hasMetadata('2fa_secret');
    }

    public function twoFactorDisable(): void
    {
        Session::put('2fa_verified', true);
        $this->detachMetadata('2fa_secret');
        $this->detachMetadata('2fa_recovery_codes');
    }

    public function twoFactorEnable(string $secret): void
    {
        Session::put('2fa_verified', true);
        $codes = $this->generateRecoveryCodes();
        $this->attachMetadata('2fa_recovery_codes', join(',', $codes));
        $this->attachMetadata('2fa_secret', $secret);
    }

    public function twoFactorRecoveryCodes(): array
    {
        if (! $this->hasMetadata('2fa_recovery_codes')) {
            $codes = $this->generateRecoveryCodes();
            $this->attachMetadata('2fa_recovery_codes', join(',', $codes));
        }
        return explode(',',$this->getMetadata('2fa_recovery_codes'));
    }

    public function isValidRecoveryCode(string $code): bool
    {
        return collect($this->twoFactorRecoveryCodes())
            ->contains(fn ($recoveryCode) => $recoveryCode == $code);
    }

    public function generateRecoveryCodes(): array
    {
        return Collection::times(8, fn () => $this->generateRecoveryCode())->all();
    }

    public function generateRecoveryCode(): string
    {
        return rand(100, 999) . '-' . rand(100, 999) . '-' . rand(100, 999);
    }

    public function useRecoveryCode(string $code): void
    {
        $recoveryCodes = $this->twoFactorRecoveryCodes();
        $recoveryCodes = collect($recoveryCodes)->filter(fn ($recoveryCode) => $recoveryCode != $code)->all();
        $this->attachMetadata('2fa_recovery_codes', join(',', $recoveryCodes));
    }

    public function isValidate2FA(string $code): bool
    {
        $code = str_replace(' ', '', $code);
        $secret = $this->getMetadata('2fa_secret');
        if (! $secret) {
            return false;
        }

        if ((new Google2FA())->verifyKey($secret, $code)) {
            return true;
        }
        if ($this->isValidRecoveryCode($code)) {
            $this->useRecoveryCode($code);
            return true;
        }
        return false;
    }

    public function twoFactorVerified(): bool
    {
        return Session::get('2fa_verified', false);
    }
}
