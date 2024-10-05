<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\View\Components\Provisioning;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ServiceDaysRemaining extends Component
{

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.provisioning.service-days-remaining');
    }
}
