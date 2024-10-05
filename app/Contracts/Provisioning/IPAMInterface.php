<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Contracts\Provisioning;

use App\DTO\Provisioning\AddressIPAM;
use App\Models\Provisioning\Server;
use App\Models\Provisioning\Service;
use Illuminate\Pagination\Paginator;

interface IPAMInterface
{
    public static function insertIP(AddressIPAM $address): AddressIPAM;

    public static function updateIP(AddressIPAM $address): AddressIPAM;

    public static function deleteIP(AddressIPAM $address): bool;

    public static function findById(int $id): ?AddressIPAM;

    public static function findByIP(string $ip): ?AddressIPAM;

    public static function findByService(Service $service): array;

    public static function fetchAdresses(int $nb = 1, ?Server $server = null, ?string $node = null): array;

    public static function useAddress(AddressIPAM $address, Service $service): AddressIPAM;

    public static function releaseAddress(AddressIPAM $address): AddressIPAM;

    public static function fetchAll(): Paginator;

}
