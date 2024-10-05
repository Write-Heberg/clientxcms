<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\DTO;

use App\Exceptions\ExternalApiException;
use App\Models\Account\Customer;
use App\Models\Provisioning\Server;
use App\Models\Provisioning\Service;
use App\Modules\Proxmox\ProxmoxAPI;

class ProxmoxAccountDTO
{

    public string $email;
    public string $comment;
    public string $firstname;
    public string $lastname;
    public ?string $password = null;
    public bool $wasCreated = false;
    public string $userid;

    public function __construct(\stdClass $attributes, int $userid, ?string $password = null)
    {
        $this->email = $attributes->email;
        $this->comment = $attributes->comment;
        $this->firstname = $attributes->firstname;
        $this->lastname = $attributes->lastname;
        $this->wasCreated = $password !== null;
        $this->password = $password;
        $this->userid = "CLIENTXCMS-" . str_pad($userid, 5, '0', STR_PAD_LEFT) . '@pve';;
    }

    public static function getUserAccount(Customer $customer, Server $server, Service $service)
    {
        $userId = "CLIENTXCMS-" . str_pad($customer->id, 5, '0', STR_PAD_LEFT) . '@pve';
        try {
            $response = ProxmoxAPI::callApi($server, 'access/users/' . $userId);

            return new self($response->toJson()->data, $customer->id);
        } catch (ExternalApiException $e){
            $password = \Str::random(16);
            try {
                $data = [
                    'userid' => $userId,
                    'email' => $customer->email,
                    'firstname' => $customer->firstname,
                    'lastname' => $customer->lastname,
                    'password' => $password,
                    'comment' => 'Created by CLIENTXCMS on ' . now()->toDateTimeString() . ' with user id ' . $customer->id,
                ];
                $response = ProxmoxAPI::callApi($server, 'access/users', $data, "POST");
                if ($response->status() == 200) {
                    return new self((object) $data, $customer->id, $password);
                }
            } catch (ExternalApiException $e){

            }
            return new self($response->toJson()->data, $customer->id, $password);
        }
    }

    public function attachVPS(Server $server, int $vmid)
    {
        ProxmoxAPI::callApi($server, 'access/acl', [
            'path' => "/vms/$vmid",
            'roles' => 'PVEVMUser',
            'propagate' => 1,
            'users' => $this->userid,
        ], "PUT");
    }

    public function detachVPS(Server $server, int $vmid)
    {
        ProxmoxAPI::callApi($server, 'access/acl', [
            'path' => "/vms/$vmid",
            'roles' => 'PVEVMUser',
            'propagate' => 1,
            'delete' => 1,
            'users' => $this->userid,
        ], "PUT");
    }
}
