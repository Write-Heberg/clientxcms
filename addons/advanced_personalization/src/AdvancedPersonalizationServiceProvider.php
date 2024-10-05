<?php

namespace App\Addons\AdvancedPersonalization;

use \App\Extensions\BaseAddonServiceProvider;

class AdvancedPersonalizationServiceProvider extends BaseAddonServiceProvider
{
    protected string $uuid = 'advanced_personalization';
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadViews();
    }
}
