<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Provisioning;

use GuzzleHttp\Psr7\Response;

class ConnectionResponse
{
    private Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function toString():string
    {
        return $this->response->getBody()->__toString();
    }

    public function successful():bool
    {
        return $this->response->getStatusCode() >= 200 && $this->response->getStatusCode() < 300;
    }

    public function status():int
    {
        return $this->response->getStatusCode();
    }

    public function toJson():array
    {
        return json_decode($this->toString(), true);
    }

}
