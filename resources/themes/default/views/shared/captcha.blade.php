<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@php($dark = is_darkmode())
@if(! isset($dark))
    @push('scripts')
        <script>
            const currentTheme = document.body.classList.contains('dark') === true ? 'dark' : 'light';

            document.querySelectorAll('[data-theme]').forEach((element) => {
                element.setAttribute('data-theme', currentTheme ? currentTheme : 'light')
            })
        </script>
    @endpush
@endif

@if(setting('captcha_driver') === 'recaptcha')
    @section('scripts')
        <script src="https://www.recaptcha.net/recaptcha/api.js?hl={{ app()->getLocale() }}" async defer></script>
        <script>
            const captchaForm = document.getElementById('captcha-form');

            function submitCaptchaForm() {
                captchaForm.submit();
            }

            if (captchaForm) {
                captchaForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    grecaptcha.execute();
                });
            }
        </script>
    @endsection

    <div class="g-recaptcha" data-sitekey="{{ setting('captcha_site_key') }}" data-callback="submitCaptchaForm" data-size="invisible"></div>

@elseif(setting('captcha_driver') === 'hcaptcha')
    @section('scripts')
        <script src="https://hcaptcha.com/1/api.js?hl={{ app()->getLocale() }}" async defer></script>
        <script>
            const captchaForm = document.getElementById('captcha-form');

            if (captchaForm) {
                captchaForm.addEventListener('submit', function (e) {
                    const hCaptchaInput = captchaForm.querySelector('[name="h-captcha-response"]');

                    if (hCaptchaInput && hCaptchaInput.value === '') {
                        e.preventDefault();

                        hcaptcha.execute();
                    }
                });
            }
        </script>
    @endsection

    <div class="h-captcha mb-2 @if($center ?? true) text-center @endif" data-sitekey="{{ setting('captcha_site_key') }}" data-theme="{{ ($dark ?? false) ? 'dark' : 'light' }}"></div>
@elseif(setting('captcha_driver') === 'cloudflare')
    @section('scripts')
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endsection

    <div class="cf-turnstile mb-2 @if($center ?? true) text-center @endif" data-sitekey="{{ setting('captcha_site_key') }}" data-theme="{{ ($dark ?? false) ? 'dark' : 'light' }}"></div>
@endif
