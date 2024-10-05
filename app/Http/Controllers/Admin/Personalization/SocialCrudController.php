<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Personalization;

use App\Models\Personalization\SocialNetwork;
use App\Theme\ThemeManager;
use Illuminate\Http\Request;

class SocialCrudController extends \App\Http\Controllers\Admin\AbstractCrudController
{
    protected string $viewPath = 'admin.personalization.socials';
    protected string $routePath = 'admin.personalization.socials';
    protected string $translatePrefix = 'personalization.social';
    protected string $model = SocialNetwork::class;

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'url' => 'required|string|max:255',
        ]);
        $model = $this->model::create($data);
        ThemeManager::clearCache();

        return $this->storeRedirect($model);
    }

    public function update(Request $request, SocialNetwork $social)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'url' => 'required|string|max:255',
        ]);
        $social->update($data);
        ThemeManager::clearCache();
        return $this->updateRedirect($social);
    }

    public function getCreateParams()
    {
        $params = parent::getCreateParams();

        $params['current_card'] = app('settings')->getCurrentCard('personalization');
        $params['current_item'] = app('settings')->getCurrentItem('personalization', 'social');
        return $params;
    }

    public function showView(array $params)
    {
        $params =  parent::showView($params);
        $params['current_card'] = app('settings')->getCurrentCard('personalization');
        $params['current_item'] = app('settings')->getCurrentItem('personalization', 'social');
        return $params;
    }

    public function show(SocialNetwork $social)
    {
        return $this->showView([
            'item' => $social,
        ]);
    }

    public function destroy(SocialNetwork $social)
    {
        $social->delete();
        ThemeManager::clearCache();
        return $this->deleteRedirect($social);
    }
}
