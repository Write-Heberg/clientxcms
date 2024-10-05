<?php

namespace App\Http\Controllers\Admin\Personalization;

use App\Http\Controllers\Admin\AbstractCrudController;
use App\Models\Admin\EmailTemplate;
use App\Services\Core\LocaleService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class EmailTemplateController extends AbstractCrudController
{
    protected string $model = EmailTemplate::class;
    protected string $viewPath = 'admin.personalization.email_templates';
    protected string $translatePrefix = 'personalization.email_templates';
    protected string $routePath = 'admin.personalization.email_templates';
    protected array $filters = ['locale'];
    protected string $filterField = 'locale';
    protected string $searchField = 'name';

    protected function queryIndex(): LengthAwarePaginator
    {
        return parent::queryIndex();
    }

    protected function getIndexParams($items, $translatePrefix): array
    {
        $params = parent::getIndexParams($items, $translatePrefix);
        $translations = __('personalization.email_templates.names');
        $params['translations'] = $translations;
        $params['locales'] = LocaleService::getLocalesNames();
        return $params;
    }

    protected function getIndexFilters()
    {
        $locales = LocaleService::getLocalesNames();
        $locales_db = EmailTemplate::pluck('locale')->unique()->toArray();
        $fields = [];
        foreach ($locales_db as $db) {
            $fields[$db] = $locales[$db];
        }
        return $fields;
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required',
            'subject' => 'required',
            'content' => 'required',
            'button_text' => 'required',
        ]);
        $validated['hidden'] = $request->has('hidden');
        $this->checkPermission('update');
        $emailTemplate->update($validated);
        $emailTemplate->save();
        return $this->updateRedirect($emailTemplate);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'subject' => 'required',
            'content' => 'required',
            'button_text' => 'required',
        ]);
        $this->checkPermission('create');
        $validated['hidden'] = $request->has('hidden');
        $emailTemplate = null;
        foreach (LocaleService::getLocalesNames() as $locale => $name) {
            $validated['locale'] = $locale;
            $emailTemplate = EmailTemplate::create($validated);
        }
        return $this->storeRedirect($emailTemplate);
    }

    public function getCreateParams()
    {
        $createParams = parent::getCreateParams();
        $createParams['locales'] = LocaleService::getLocalesNames();
        return $createParams;
    }

    public function showView(array $params)
    {
        $params['translations'] = __('personalization.email_templates.names');
        $params['locales'] = LocaleService::getLocalesNames();
        return parent::showView($params);
    }

    public function show(EmailTemplate $emailTemplate)
    {
        return $this->showView(['item' => $emailTemplate]);
    }
}
