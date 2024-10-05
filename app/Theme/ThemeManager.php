<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Theme;

use App\DTO\Core\Extensions\ExtensionThemeDTO;
use App\DTO\Core\Extensions\SectionTypeDTO;
use App\Models\Admin\Setting;
use App\Models\Personalization\MenuLink;
use App\Models\Personalization\Section;
use App\Models\Personalization\SocialNetwork;
use App\Models\Store\Group;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ThemeManager
{
    private ?ExtensionThemeDTO $theme = null;
    private string $themesPath;
    private array $themes;
    private string $themesPublicPath;

    public function __construct()
    {
        $this->themesPath = resource_path('themes/');
        $this->themesPublicPath = public_path('themes/');
        $this->scanThemes();

        if ($this->getTheme() != null){
            app('view')->addLocation($this->themePath('views'));
            app('view')->addLocation($this->themePath());
            if (File::exists($this->themePath('lang'))) {
                app('translator')->addNamespace('theme', $this->themePath('lang'));
            }
        }
    }

    public static function clearCache()
    {
        Cache::forget('theme_configuration');
    }

    public function hasTheme(): bool
    {
        return $this->theme !== 'default';
    }

    public function getTheme(): ExtensionThemeDTO
    {
        return $this->theme;
    }

    public function setTheme(string $theme, bool $save = false): void
    {
        $oldTheme = $this->theme;
        $this->theme = collect($this->themes)->first(function ($item) use ($theme) {
            return $item->uuid == $theme;
        });
        Setting::updateSettings(['theme' => $theme]);
        \Artisan::call('config:clear');
        \Artisan::call('view:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('db:seed', ['--no-interaction' => true]);
        $sections1 = $this->fetchThemeSection($oldTheme);
        $sections2 = $this->fetchThemeSection($this->theme);
        $existing = collect($sections1)->pluck('uuid')->intersect(collect($sections2)->pluck('uuid'));
        foreach ($existing as $uuid) {
            $sections = Section::where('uuid', $uuid)->get();
            foreach ($sections as $section) {
                $section->theme_uuid = $this->theme->uuid;
                $section->save();
                if (File::exists($oldTheme->path . '/views/sections_copy/' . $section->id . '-' . $section->uuid . '.blade.php')) {
                    File::copy($oldTheme->path . '/views/sections_copy/' . $section->id . '-' . $section->uuid . '.blade.php', $this->theme->path . '/views/sections_copy/' . $section->id . '-' . $section->uuid . '.blade.php');
                }
            }
        }
        $this->createAssetsLink($theme);
    }

    public function themePath(string $path = '', ?string $theme = null): ?string
    {
        if ($theme === null) {
            if (!$this->theme) {
                return null;
            }
            $theme = $this->theme->path;
        }
        return "{$theme}/{$path}";
    }

    public function themesPath(string $path = ''): string
    {
        return $this->themesPath.$path;
    }

    public function themesPublicPath(string $path = ''): string
    {
        return $this->themesPublicPath.$path;
    }

    public function getSocialsNetworks()
    {
        return $this->getSetting()['socials'] ?? collect();
    }

    public function getBottomLinks(): \Illuminate\Support\Collection
    {
        if (app()->environment('testing')) {
            return collect();
        }
        $default = new \stdClass();
        $default->items = [];
        return collect(collect($this->getSetting()['footerlinks'] ?? [])->filter(function($item){
            return $item->type == 'bottom';
        })->first(null, $default)->items)->mapWithKeys(function($item){
            return [$item['url'] => $item['name']];
        });
    }

    public function getFrontLinks(): \Illuminate\Support\Collection
    {
        if (app()->environment('testing')) {
            return collect();
        }
        $default = new \stdClass();
        $default->items = [];
        return collect(collect($this->getSetting()['footerlinks'] ?? [])->filter(function($item){
            return $item->type == 'front';
        })->first(null, $default)->items)->mapWithKeys(function($item){
            return [$item['url'] => ['name' => $item['name'], 'icon' => $item['icon'] ?? 'bi bi-link']];
        });
    }

    public function getSections(): Collection
    {
        return $this->getSetting()['sections'] ?? collect();
    }

    public function getSectionsForUrl(string $url): Collection
    {
        $theme_uuid = $this->getTheme()->uuid;
        return $this->getSections()->where('url', $url)->where('theme_uuid', $theme_uuid)->where('is_active', true);
    }

    public function isThemeSectionActive(string $uuid): bool
    {
        $theme_uuid = $this->getTheme()->uuid;
        return $this->getSections()->where('uuid', $uuid)->where('theme_uuid', $theme_uuid)->where('is_active', true)->exists();
    }

    public function getSetting()
    {
        return Cache::remember('theme_configuration', 60 * 60 * 24 * 7, function () {
            return [
                'socials' => SocialNetwork::all()->where('hidden', false),
                'footerlinks' => MenuLink::all(),
                'sections_pages' => $this->getSectionsPages(),
                'sections' => Section::orderBy('order')->get()
            ];
        });
    }


    public function publicPath(string $path = '', ?string $theme = null): ?string
    {
        if ($theme === null) {
            if (! $this->hasTheme()) {
                return null;
            }

            $theme = $this->theme->path;
        }

        return $this->themesPublicPath("{$theme}/{$path}");
    }

    private function scanThemes()
    {
        $this->themes = [];
        if (!empty($this->themes)){
            return;
        }
        foreach (File::directories($this->themesPath) as $theme) {
            if (File::exists($theme.'/theme.json') && !str_contains($theme, 'default')) {
                $this->themes[] = ExtensionThemeDTO::fromJson($theme.'/theme.json');
            }
        }
        if (!is_dir($this->themesPath.'/default')) {
            throw new \Exception('Default theme is missing');
        }
        array_unshift($this->themes, ExtensionThemeDTO::fromJson($this->themesPath.'/default/theme.json'));
        if ($this->theme == null) {
            $currentTheme = \setting('theme', 'default');
            if ($currentTheme && !empty($this->themes)) {
                $this->theme = collect($this->themes)->first(function ($theme) use ($currentTheme) {
                    return $theme->uuid == $currentTheme;
                });
                if ($this->theme == null) {
                    $this->theme = collect($this->themes)->first();
                }
            }
        }

    }

    public function getThemes(): array
    {
        if (empty($this->themes)){
            $this->scanThemes();
        }
        return $this->themes;
    }

    public function getSectionsPages(bool $filter = true)
    {
        $pages['home'] = [
            'title' => __('personalization.sections.pages.page_home'),
            'url' => route('home', [], false),
        ];
        $pages['store'] = [
            'title' => __('personalization.sections.pages.page_store'),
            'url' => route('front.store.index', [], false),
        ];
        $pages['checkout'] = [
            'title' => __('personalization.sections.pages.page_checkout'),
            'url' => route('front.store.basket.checkout', [], false),
        ];
        $pages['basket'] = [
            'title' => __('personalization.sections.pages.page_basket'),
            'url' => route('front.store.basket.show', [], false),
        ];
        $sections = Section::orderBy('order')->get();
        foreach (Group::getAvailable()->get() as $group) {
            $pages['group_' . $group->slug] = [
                'title' => __('personalization.sections.pages.page_group', ['name' => $group->name]),
                'url' => $group->route(false),
            ];
        }
        if ($filter){
            $theme_uuid = $this->getTheme()->uuid;
            foreach ($pages as $uuid => $detail) {
                if ($sections->where('url', $detail['url'])->count() == 0) {
                    $pages = array_filter($pages, function ($key) use ($uuid) {
                        return $key != $uuid;
                    }, ARRAY_FILTER_USE_KEY);
                } else {
                    $pages[$uuid]['sections'] = $sections->where('url', $detail['url'])->where('theme_uuid', $theme_uuid)->sortBy('sort')->values();
                }
            }
        }
        return $pages;
    }

    public function getSectionsTypes()
    {
        return Cache::get('sections_types', function () {
            return collect(Http::get('https://api-nextgen.clientxcms.com/items/theme_sections_types')->json('data'))->map(function ($item) {
                return new SectionTypeDTO($item, $this->getThemeSections());
            });
        });
    }

    public function getThemeSections()
    {
        return Cache::get('themes_sections', function () {
            return $this->fetchThemeSection($this->getTheme());
        });
    }

    private function fetchThemeSection(ExtensionThemeDTO $dto){
        $sections = $dto->getSections();
        $extensions = app('extension')->getAllExtensions();
        foreach ($extensions as $extension) {
            $sections = array_merge($sections, $extension->getSections());
        }
        return $sections;
    }


    protected function createAssetsLink(string $theme): void
    {
        if (File::exists($this->publicPath('', $theme))) {
            return;
        }

        $themeAssetsPath = $this->themePath('assets');
        if (File::exists($themeAssetsPath)) {
            $this->relativeLink($themeAssetsPath, $this->publicPath('', $theme));
        }
    }

    private function relativeLink(string $target, string $link): void
    {
        windows_os() ? File::link($target, $link) : File::relativeLink($target, $link);
    }
}
