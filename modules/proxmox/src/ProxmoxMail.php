<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox;

use App\DTO\Provisioning\AddressIPAM;
use App\Models\Admin\EmailTemplate;
use App\Modules\Proxmox\DTO\ProxmoxAccountDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class ProxmoxMail extends Notification
{

    use Queueable, SerializesModels;

    private string $ips;
    private string $username;
    private string $password;
    private string $hostname;
    private int $serviceId;
    private ProxmoxAccountDTO $DTO;

    public function __construct(array $ips, array $data, int $serviceId, ProxmoxAccountDTO $DTO)
    {
        $this->ips = collect($ips)->map(function (AddressIPAM $ipam) {
            return $ipam->ip;
        })->implode(', ');
        $this->username = "root";
        $this->password = $data['password'] ?? 'root';
        $this->hostname = $data['hostname'] ?? 'not defined';
        $this->DTO = $DTO;
        $this->serviceId = $serviceId;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $context = [
            'ips' => $this->ips,
            'username' => $this->username,
            'password' => $this->password,
            'hostname' => $this->hostname,
            'proxmox_username' => $this->DTO->userid,
            'proxmox_password' => $this->DTO->password,
        ];
        $serviceUrl = route('front.services.show', ['service' => $this->serviceId]);
        return EmailTemplate::getMailMessage("proxmox", $serviceUrl, $context);
    }
}
