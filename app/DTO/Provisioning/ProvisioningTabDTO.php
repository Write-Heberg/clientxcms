<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Provisioning;

use App\Models\Provisioning\Service;

class ProvisioningTabDTO
{
    /**
     * @var string $uuid - UUID of the tab (for url)
     */
    public string $uuid;
    /**
     * @var bool $active - Is the tab active for current service
     */
    public bool $active = false;
    /**
     * @var bool $popup - Is the tab a popup (NOVNC example)
     */
    public bool $popup = false;
    /**
     * @var string $title - Title of the tab
     */
    public string $title;
    /**
     * @var string $permission - Permission required to access the tab
     */
    public string $permission;
    /**
     * @var string $icon - Icon of the tab
     */
    public string $icon;
    /**
     * @var string $url - URL of the tab
     */
    public ?string $url = null;

    public bool $admin = false;

    public bool $newwindow = false;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->uuid = $data['uuid'];
            $this->active = $data['active'];
            $this->popup = $data['popup'] ?? false;
            $this->title = $data['title'];
            $this->permission = $data['permission'];
            $this->icon = $data['icon'];
            $this->url = $data['url'] ?? null;
            $this->admin = $data['admin'] ?? false;
            $this->newwindow = $data['newwindow'] ?? false;
        }
    }

    public function route(int $serviceId, bool $admin = false): string
    {
        if ($this->uuid == 'services') {
            if ($admin) {
                return route('admin.services.show', ['service' => $serviceId]);
            }
            return route('front.services.show', ['service' => $serviceId]);
        }
        if ($admin) {
            return route('admin.services.tab', ['tab' => $this->uuid, 'service' => $serviceId]);
        }
        return route('front.services.tab', ['tab' => $this->uuid, 'service' => $serviceId]);
    }

    public function renderTab(Service $service){
        $method = 'render' . ucfirst($this->uuid);
        if (method_exists($service->productType()->panel(), $method)) {
            return $service->productType()->panel()->$method($service);
        }
        return 'Tab not found';
    }
}
