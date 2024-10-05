<?php

namespace App\DTO\Core\Extensions;

use App\Exceptions\ThemeInvalidException;
use File;
use Illuminate\Validation\ValidationException;
use Validator;

class ExtensionThemeDTO
{
    public string $path;
    public string $theme_file;
    public bool $enabled;
    public array $json = [];
    public string $uuid;
    public string $name;
    public string $description;
    public string $version;
    public array $author;
    public ?string $demo = null;
    public array $api;
    public bool $hasConfig;
    public ?string $configFile = null;
    public array $configRules = [];
    public array $config = [];

    public static function fromJson(string $theme_file)
    {
        $json = json_decode(File::get($theme_file), true);
        if ($json === null) {
            throw new ThemeInvalidException("Invalid JSON in theme file: {$theme_file}");
        }
        $dto = new self();
        if ($error = $dto->verifyJson($json)) {
            throw new ThemeInvalidException("Invalid JSON in theme file: {$theme_file} : {$error}");
        }
        $dto->theme_file = $theme_file;
        $dto->json = $json;
        $dto->path = dirname($theme_file);
        $dto->uuid = $json['uuid'];
        $dto->name = $json['name'];
        $dto->description = $json['description'];
        $dto->version = $json['version'];
        $dto->author = $json['author'];
        $dto->demo = $json['demo'] ?? null;
        $dto->hasConfig = file_exists($dto->path.'/config/config.blade.php');
        if ($dto->hasConfig) {
            $dto->configFile = $dto->path.'/config/config.php';
            if (file_exists($dto->path .'/config/rules.php')){
                $dto->configRules = require $dto->path .'/config/rules.php';
            }
            if (file_exists($dto->path.'/config/config.json')){
                $dto->config = json_decode(file_get_contents($dto->path.'/config/config.json'), true);
            }
        }
        return $dto;
    }

    private function verifyJson(array $json)
    {
        $required = ['uuid', 'name', 'description', 'version', 'author'];
        foreach ($required as $key) {
            if (!isset($json[$key])) {
                return "Missing required key: {$key}";
            }
            if (!is_string($json[$key]) && !is_array($json[$key])) {
                return "{$key} must be a string";
            }
        }
        if (!is_array($json['author'])) {
            return "author must be an array";
        }
        if (!isset($json['author']['name']) || !is_string($json['author']['name'])) {
            return "author.name must be a string";
        }
        if (!isset($json['author']['email']) || !is_string($json['author']['email'])) {
            return "author.email must be a string";
        }
    }

    public function isOfficial(): bool
    {
        return $this->api != null;
    }

    public function isEnabled(): bool
    {
        return app('settings')->get('theme.'.$this->uuid.'.enabled', false);
    }

    public function demoUrl(): string
    {
        return $this->demo ?? 'https://demo.clientxcms.com';
    }

    public function hasConfig(): bool
    {
        return $this->hasConfig;
    }

    public function configRules(): array
    {
        return $this->configRules;
    }

    public function configView(array $params): string
    {
        if (!$this->hasConfig){
            return '';
        }
        return view()->file($this->path.'/config/config.blade.php', array_merge($params, ['config' => $this->config]))->render();
    }

    public function storeConfig(array $data): void
    {
        $rules = $this->configRules();
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $this->config = $validator->validated();
        file_put_contents($this->path.'/config/config.json', json_encode($validator->validated(), JSON_PRETTY_PRINT));
    }

    public function hasScreenshot(): bool
    {
        return file_exists($this->path.'/screenshot.png');
    }

    public function screenshotUrl(): string
    {
        return 'resources/themes/'.$this->uuid.'/screenshot.png';
    }

    public function hasSection(string $path): bool
    {
        return file_exists($this->path.'/sections/'.$path.'.blade.php');
    }

    public function hasSections(): bool
    {
        return file_exists($this->path.'/views/sections');
    }

    public function scanSections(): array
    {
        $sections = [];
        if ($this->hasSections()) {
            $files = File::allFiles($this->path.'/views/sections');
            foreach ($files as $file) {
                if (!str_contains($file->getFilename(), '_copy')){
                    $path = str_replace($this->path . '/views/', '', $file->getPathname());
                    $sections[] = ThemeSectionDTO::fromPathInfo(pathinfo($file), $path,$this->uuid);
                }
            }
        }
        return $sections;
    }

    public function getSections(){
        $file = $this->path . '/views/sections/sections.json';
        if (!\File::exists($file)){
            return [];
        }
        $sectionFile = json_decode(file_get_contents($file), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $sectionFile = [];
        }
        $sections = [];
        foreach ($sectionFile as $section) {
            $sections[] = new ThemeSectionDTO($section);
        }
        return $sections;
    }
}
