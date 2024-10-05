<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox;

use Illuminate\Http\Client\Response;

class ProxmoxResponse
{

    private Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function toArray()
    {
        return [
            'data' => json_decode($this->response->body()),
            'status' => $this->status(),
            'successful' => $this->successful(),
        ];
    }

    public function toJson()
    {
        return json_decode($this->response->body());
    }

    public function successful()
    {
        return $this->response->status() >= 200 && $this->response->status() < 300;
    }

    public function status()
    {
        return $this->response->status();
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function formattedErrors()
    {
        return ProxmoxAPI::formatBody($this->response->body());
    }

    public function toGuzzleResponse()
    {
        return new \GuzzleHttp\Psr7\Response($this->status(), [], $this->response->body());
    }
}
