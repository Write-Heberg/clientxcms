<?php

namespace App\DTO\Core\Extensions;

class SectionTypeDTO
{
    public string $uuid;

    public array $translates;

    public $sections;

    public int $id;

    public function __construct(array $json, array $sections)
    {
        $this->uuid = $json['uuid'];
        $this->translates = $json['translates'];
        $this->id = $json['id'];
        $this->sections = collect($sections)->filter(function ($section) {
            return $section->json['section_type'] == $this->id;
        });
    }

    public function name()
    {
        $locale = app()->getLocale();
        return collect($this->translates)->filter(function ($translate) use ($locale) {
            return $translate['locale'] == $locale;
        })->first()['name'];
    }
}
