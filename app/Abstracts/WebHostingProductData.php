<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Abstracts;

use App\DTO\Store\ProductDataDTO;
use App\Models\Provisioning\Subdomain;
use App\Rules\DomainIsNotRegisted;
use App\Rules\FQDN;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

class WebHostingProductData extends AbstractProductData
{
    protected array $parameters = [
        'domain',
        'domain_subdomain',
        'subdomain',
    ];


    public function primary(\App\DTO\Store\ProductDataDTO $productDataDTO): string
    {
        return $productDataDTO->data['domain'] ?? '';
    }

    public function validate(): array
    {
        return [
            'domain' => ['nullable', 'max:255', new FQDN(), new DomainIsNotRegisted(), new RequiredIf(function() {
                return request()->input('domain_subdomain') == null;
            })],
            'domain_subdomain' => ['nullable', 'string', 'max:255', new DomainIsNotRegisted(true), new RequiredIf(function() {
                return request()->input('domain') == null && Subdomain::count() > 0;
            })],
            'subdomain' => ['nullable','string', 'max:255', Rule::in(Subdomain::all()->pluck('domain')->toArray()), new RequiredIf(function() {
                return request()->input('domain') == null && Subdomain::count() > 0;
            })],
        ];
    }

    public function parameters(ProductDataDTO $productDataDTO): array
    {
        $parameters = parent::parameters($productDataDTO);
        if (request()->input('domain') == null) {
            $parameters['domain'] = request()->input('domain_subdomain') . request()->input('subdomain');
        } else {
            $parameters['domain'] = request()->input('domain');
        }
        $parameters['domain'] = strtolower($parameters['domain']);
        return $parameters;
    }

    public function render(ProductDataDTO $productDataDTO)
    {
        $subdomains = Subdomain::all();
        return view('front.store.basket.webhosting', [
            'productData' => $productDataDTO,
            'data' => $productDataDTO->data,
            'subdomains' => $subdomains,
        ]);
    }
}
