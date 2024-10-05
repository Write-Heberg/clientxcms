<?php

namespace App\Models\Personalization;

use App\DTO\Core\Extensions\ExtensionSectionTrait;
use App\DTO\Core\Extensions\ExtensionThemeDTO;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    use ExtensionSectionTrait;

    protected $table = "theme_sections";

    protected $fillable = [
        'uuid',
        'theme_uuid',
        'path',
        'is_active',
        'url',
    ];

    public static function scanSections()
    {
        /** @var \App\DTO\Core\Extensions\ThemeSectionDTO[] $sections */
        $sections = app('theme')->getThemeSections();
        $theme = app('theme')->getTheme();
        foreach ($sections as $section) {
            if (!$section->isDefault()){
                continue;
            }
            if (Section::where('uuid', $section->uuid)->exists()) {
                continue;
            }
            Section::insert([
                'uuid' => $section->uuid,
                'theme_uuid' => $theme->uuid,
                'path' => $section->json['path'],
                'is_active' => true,
                'url' => $section->json['default_url'] ?? '/',
            ]);
        }
    }

    public function getUrlAttribute($value)
    {
        return $value ?? '/';
    }

    public function toDTO(): \App\DTO\Core\Extensions\ThemeSectionDTO
    {
        return \App\DTO\Core\Extensions\ThemeSectionDTO::fromModel($this);
    }

    public function saveContent(string $content)
    {
        $theme = app('theme')->getTheme();
        $path = $theme->path . '/views/sections_copy/'. $this->id . '-'. $this->uuid . '.blade.php';
        $this->path = 'sections_copy/'. $this->id . '-'. $this->uuid;
        if (!file_exists($theme->path . '/views/sections_copy')) {
            mkdir($theme->path . '/views/sections_copy', 0777, true);
        }
        file_put_contents($path, $content);
        $this->save();
    }

    public function restore()
    {
        $theme = app('theme')->getTheme();
        $path = 'sections/' . $this->uuid;
        $newPath = $theme->path . 'views/'. $this->path . '.blade.php';
        unset($newPath);
        $this->path = $path;
        $this->save();
    }

    public function cloneSection()
    {
        $clone = $this->replicate();
        $clone->save();
        $theme = app('theme')->getTheme();
        if (!file_exists($theme->path . '/views/sections_copy')) {
            mkdir($theme->path . '/views/sections_copy', 0777, true);
        }
        $path = $theme->path . '/views/sections_copy/'. $clone->id . '-'. $clone->uuid . '.blade.php';
        $clone->path = 'sections_copy/'. $clone->id . '-'. $clone->uuid;
        $clone->save();
        if (file_exists($theme->path . '/views/' . $this->path . '.blade.php')) {
            $content = file_get_contents($theme->path . '/views/' . $this->path . '.blade.php');
        } else {
            $content = file_get_contents(app('view')->getFinder()->find($this->path));
        }
        file_put_contents($path, $content);
        return $clone;
    }

    public function delete()
    {
        $theme = app('theme')->getTheme();
        $path = $theme->path . '/views/' . $this->path . '.blade.php';
        if (file_exists($path) && str_contains($path, 'sections_copy')) {
            unlink($path);
        }
        parent::delete();
    }

}
