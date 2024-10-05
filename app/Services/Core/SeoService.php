<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Services\Core;

class SeoService
{

    public function addHead(string $content): void
    {
        $head = \Cache::get('seo_head', function () {
            return '';
        });
        $head .= $content;
        \Cache::put('seo_head', $head);
    }

    public function replaceInHead(string $search, string $replace): void
    {
        $head = \Cache::get('seo_head', function () {
            return '';
        });
        $head = str_replace($search, $replace, $head);
        \Cache::put('seo_head', $head);
    }

    public function replaceInFooter(string $search, string $replace): void
    {
        $footer = \Cache::get('seo_footer', function () {
            return '';
        });
        $footer = str_replace($search, $replace, $footer);
        \Cache::put('seo_footer', $footer);
    }

    public function addFooter(string $content): void
    {
        $footer = \Cache::get('seo_footer', function () {
            return '';
        });
        $footer .= $content;
        \Cache::put('seo_footer', $footer);
    }

    public function head(?string $append = null): string
    {
        return \Cache::get('seo_head', function () {
            return $this->generateHead();
        }) . $append;
    }

    public function foot(?string $append = null): string
    {
        return \Cache::get('seo_footer', function () {
            return setting('seo_footerscripts');
        }) . $append;
    }

    public function favicon(): string
    {
        return '<link rel="icon" type="image/png" href="' . setting('app_favicon') . '">';
    }

    private function generateHead(): string
    {
        $head = '';
        if (setting('seo_description'))
            $head .= '<meta name="description" content="' . setting('seo_description') . '">';
        if (setting('seo_keywords'))
            $head .= '<meta name="keywords" content="' . setting('seo_keywords') . '">';
        if (setting('seo_theme_color'))
            $head .= '<meta name="theme-color" content="' . setting('seo_theme_color') . '">';
        if (setting('seo_headscripts'))
            $head .= setting('seo_headscripts');
        if (setting('seo_disablereferencement'))
            $head .= '<meta name="robots" content="noindex, nofollow">';
        return $head;
    }
}

