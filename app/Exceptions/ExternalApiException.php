<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Exceptions;

class ExternalApiException extends \Exception
{
    private $response;
    private $request;

    public function __construct($message, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
    
    public function setRequest($request)
    {
        $this->request = $request;
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function setResponse($response)
    {
        $this->response = $response;
    }
    
    public function getResponse()
    {
        return $this->response;
    }
}
