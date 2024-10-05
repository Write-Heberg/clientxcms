<?php

namespace App\DTO\Core\Extensions;

use App\Models\Personalization\Section;
use File;

class ThemeSectionDTO
{
    public array $json;
    public string $uuid;

    public function __construct(array $json)
    {
        $this->json = $json;
        $this->uuid = $json['uuid'];

    }

    public static function fromModel(Section $section)
    {
        $api = (new Section(['uuid' => $section->uuid]))->api();
        $api['path'] = $section->path;
        return new self($api);
    }

    public function isActivable(): bool
    {
        $extension_needed = $this->json['extension_needed'] ?? false;
        if ($extension_needed) {
            return app('extension')->extensionIsEnabled($extension_needed);
        }
        return true;
    }

    public function thumbnail(): string
    {
        return $this->json['thumbnail'] ?? 'https://via.placeholder.com/1000x250';
    }

    public function render(): string
    {
        $path = $this->json['path'];
        try {
            return view($path, $this->getContextFromUuid())->render();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getContent(): string
    {
        $path = $this->json['path'];
        return File::get(app('view')->getFinder()->find($path));
    }

    public function isDefault(): bool
    {
        return $this->json['default'] ?? false;
    }

    private function getContextFromUuid()
    {
        $extension = app('extension');
        return $extension->getSectionsContexts()->get($this->uuid, []);
    }

}
