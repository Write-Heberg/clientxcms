<?php

namespace App\DTO\Core\Extensions;

trait ExtensionSectionTrait
{
    protected array $api;

    public function api()
    {
        if (empty($this->api)) {
            $this->api = collect(app('theme')->getThemeSections())->filter(function ($page) {
                return $page->uuid == $this->uuid;
            })->first()->json ?? [];
        }
        return $this->api;
    }

    public function thumbnail()
    {
        if (array_key_exists('thumbnail', $this->api())) {
            return $this->api()['thumbnail'];
        }
        return 'https://via.placeholder.com/1000x250';
    }

    public function isModifiable()
    {
        $premium = $this->api()['premium'] ?? false;
        if (app('extension')->extensionIsEnabled($this->api()['extension_needed'] ?? '')){
            return true;
        }
        return !$premium;
    }

    public function isPremium()
    {
        return $this->api()['extension_needed'] ?? '' == 'advanced_personalization';
    }
}
