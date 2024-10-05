<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Provisioning;

use App\DTO\Core\Extensions\ExtensionDTO;
use App\Http\Controllers\Admin\AbstractCrudController;
use App\Models\Provisioning\Subdomain;
use Illuminate\Http\Request;

class SubdomainController extends AbstractCrudController
{
    protected string $model = Subdomain::class;
    protected string $viewPath = 'admin.provisioning.subdomains';
    protected string $routePath = 'admin.subdomains';
    protected string $translatePrefix = 'admin.subdomains';

    public function getIndexParams($items, string $translatePrefix, $filter = null, $filters = [])
    {
        $card = app('settings')->getCards()->firstWhere('uuid', 'core');
        if (!$card) {
            abort(404);
        }
        $item = $card->items->firstWhere('uuid', 'subdomains');
        \View::share('current_card', $card);
        \View::share('current_item', $item);
        return parent::getIndexParams($items, $translatePrefix, $filter, $filters);
    }

    public function show(Subdomain $subdomain)
    {
        $card = app('settings')->getCards()->firstWhere('uuid', 'core');
        if (!$card) {
            abort(404);
        }
        $item = $card->items->firstWhere('uuid', 'subdomains');
        \View::share('current_card', $card);
        \View::share('current_item', $item);
        return $this->showView([
            'item' => $subdomain,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'domain' => 'required|string|unique:subdomains',
        ]);
        $subdomain = Subdomain::create($data);
        return $this->storeRedirect($subdomain);
    }

    public function update(Request $request, Subdomain $subdomain)
    {
        $data = $request->validate([
            'domain' => 'required|string|unique:subdomains',
        ]);
        $subdomain->update($data);
        return $this->updateRedirect($subdomain);
    }

    public function destroy(Subdomain $subdomain)
    {
        $subdomain->delete();
        return $this->deleteRedirect($subdomain);
    }

    public function getCreateParams()
    {
        $card = app('settings')->getCards()->firstWhere('uuid', 'core');
        if (!$card) {
            abort(404);
        }
        $item = $card->items->firstWhere('uuid', 'subdomains');
        \View::share('current_card', $card);
        \View::share('current_item', $item);
        return parent::getCreateParams();
    }
}
