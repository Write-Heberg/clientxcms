<?php

namespace App\Http\Controllers\Admin\Personalization;

use App\DTO\Core\Extensions\ThemeSectionDTO;
use App\Events\Resources\ResourceUpdatedEvent;
use App\Http\Controllers\Admin\AbstractCrudController;
use App\Models\Core\Permission;
use App\Models\Personalization\Section;
use App\Theme\ThemeManager;
use Illuminate\Http\Request;

class SectionController extends AbstractCrudController
{
    protected ?string $managedPermission = Permission::MANAGE_PERSONALIZATION;
    protected string $model = Section::class;
    protected string $routePath = 'admin.personalization.sections';
    protected string $viewPath = 'admin.personalization.sections';
    protected string $translatePrefix = 'personalization.sections';

    protected function getIndexParams($items, string $translatePrefix)
    {
        $params = parent::getIndexParams($items, $translatePrefix);
        $params['pages'] = app('theme')->getSectionsPages();
        $params['sectionTypes'] = app('theme')->getSectionsTypes();
        $params['themeSections'] = app('theme')->getThemeSections();
        return $params;

    }

    public function show(Section $section)
    {
        $pages = app('theme')->getSectionsPages(false);
        $sectionTypes = app('theme')->getSectionsTypes();
        $content = ThemeSectionDTO::fromModel($section)->render();
        $pages = collect($pages)->mapWithKeys(function ($item) {
            return [$item['url'] => $item['title']];
        })->toArray();
        $themes = app('theme')->getThemes();
        $themes = collect($themes)->mapWithKeys(function ($item) {
            return [$item->uuid => $item->name];
        })->toArray();
        return $this->showView(['item' => $section, 'content' => $content, 'pages' => $pages, 'sectionTypes' => $sectionTypes, 'themes' => $themes]);
    }

    public function destroy(Section $section)
    {
        $section->delete();
        ThemeManager::clearCache();
        return $this->deleteRedirect($section);
    }

    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'content' => 'required',
            'url' => 'required',
            'theme_uuid' => 'required',
        ]);
        $validated['is_active'] = $request->has('is_active');
        unset($validated['content']);
        $section->saveContent($request->get('content'));
        $section->update($validated);
        ThemeManager::clearCache();
        event(new ResourceUpdatedEvent($section));
        return redirect()->route($this->routePath . '.index')->with('success', __($this->flashs['updated']));
    }

    public function switch(Section $section)
    {
        $section->is_active = !$section->is_active;
        $section->save();
        ThemeManager::clearCache();
        return back();
    }

    public function restore(Section $section)
    {
        $section->restore();
        ThemeManager::clearCache();
        return $this->updateRedirect($section);
    }

    public function sort(Request $request)
    {
        $items = $request->get('items');
        $i = 0;
        foreach ($items as $id) {
            Section::where('id', $id)->update(['order' => $i]);
            $i++;
        }
        ThemeManager::clearCache();
        return response()->json(['success' => true]);
    }

    public function clone(Section $section)
    {
        $newSection = $section->cloneSection();
        ThemeManager::clearCache();
        return $this->storeRedirect($newSection);
    }

    public function cloneSection(string $uuid)
    {
        $sections = app('theme')->getThemeSections();
        $section = collect($sections)->firstWhere('uuid', $uuid);
        if (!view()->exists($section->json['path'])){
            if (\Str::start($section->json['path'], 'advanced_personalization')){
                return back()->with('error', __('personalization.sections.errors.advanced_personalization'));
            }
            return back()->with('error', __('personalization.sections.errors.notfound'));
        }
        $theme = app('theme')->getTheme();
        Section::insert([
            'uuid' => $section->uuid,
            'path' => $section->json['path'],
            'order' => 0,
            'theme_uuid' => $theme->uuid,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        ThemeManager::clearCache();
        return back();
    }
}
