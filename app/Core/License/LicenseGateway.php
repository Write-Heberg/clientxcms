<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Core\License;

use App\Exceptions\LicenseInvalidException;
use App\Models\Account\Customer;
use App\Models\Provisioning\Service;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;

class LicenseGateway
{

    private string $authorizationUrl;
    private string $accessTokenUrl;
    private string $apiBaseUrl;
    private string $refreshTokenUrl;

    private Client $httpClient;

    public function __construct() {
        $this->authorizationUrl = self::getDomain() . '/oauth2/authorize';
        $this->accessTokenUrl = self::getDomain() . '/oauth2/access_token';
        $this->apiBaseUrl = self::getDomain() . '/oauth2/v1';
        $this->refreshTokenUrl = self::getDomain() . '/oauth2/access_token';
        $this->httpClient = new Client(['timeout' => 10, 'headers' => ['Accept' => 'application/json']]);
    }

    public static function getDomain()
    {
        return env('CTX_DOMAIN') ?? 'https://clientxcms.com';
    }

    public function getAuthorizationUrl() {
        $params = array(
            'client_id' => env('OAUTH_CLIENT_ID'),
            'redirect_uri' => $this->fullUri() . '/licensing/return',
            'response_type' => 'code',
            'scope' => ''
        );

        return $this->authorizationUrl . '?' . http_build_query($params);
    }

    public function getAccessToken(string $code) {
        try {

            $params = array(
                'client_id' => env('OAUTH_CLIENT_ID'),
                'client_secret' => env('OAUTH_CLIENT_SECRET'),
                'redirect_uri' => $this->fullUri() . '/licensing/return',
                'code' => $code,
                'grant_type' => 'authorization_code'
            );

            $response = $this->httpClient->post($this->accessTokenUrl, [
                'form_params' => $params,
            ]);
        } catch (ServerException|RequestException|ClientException|ConnectException $e){
            if ($e->getResponse() == null){
                throw new \Exception($e->getMessage());
            }
            return json_decode($e->getResponse()->getBody(), true);
        }

        return json_decode($response->getBody(), true);
    }

    public function callAPI($accessToken, $endpoint,$params = array()) {
        try {

            $url = $this->apiBaseUrl . $endpoint;

            $headers = array(
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json'
            );

            $options = array(
                'headers' => $headers,
                'form_params' => $params
            );

            $response = $this->httpClient->request('POST', $url, $options);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e){
            dump($e->getResponse()->getBody());
            return json_decode($e->getResponse()->getBody(), true);
        }
    }

    public function download($accessToken, $endpoint, $params,$resource, string $method = 'POST') {
        $url = $this->apiBaseUrl . $endpoint;

        $headers = array(
            'Authorization' => 'Bearer ' . $accessToken,
        );

        $options = array(
            'headers' => $headers,
            'form_params' => $params,
            'sink' => $resource,
        );

        return $this->httpClient->request($method, $url, $options);
    }


    public function refreshAccessToken(string $refreshToken, ?License $license=null) {
        try {
            $params = array(

                'client_id' => env('OAUTH_CLIENT_ID'),
                'client_secret' => env('OAUTH_CLIENT_SECRET'),
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token'
            );

            $response = $this->httpClient->post($this->refreshTokenUrl, [
                'form_params' => $params
            ]);

        } catch (ServerException|RequestException|ClientException|ConnectException $e) {
            if (method_exists($e, 'getResponse') && $e->getResponse() != null)
                $response = json_decode($e->getResponse()->getBody(), true);
            else
                $response = null;
            if ($response == null){
                throw new LicenseInvalidException('Internal error please contact support');
            }
            throw new LicenseInvalidException(array_key_exists('hint', $response) ? $response['hint'] : $response['message']);
        }
        $json = json_decode($response->getBody(), true);
        if ($json == null){
            throw new LicenseInvalidException('Internal error please contact support');
        }
        if ($license != null)
            $license->save($json['refresh_token']);
        return $json['access_token'];
    }

    private function fullUri()
    {
        return \URL::getRequest()->getScheme() . '://' . \URL::getRequest()->getHttpHost();
    }

    public function getLicense(?string $token = null, bool $force = false): License
    {
        $cache = new LicenseCache();
        $license = $cache->getLicense();
        if (!$cache->isHit() || $force) {
            if ($token == null && setting('app.license.refresh_token') != null)
                $token = $this->refreshAccessToken(setting('app.license.refresh_token'), $license);
            if (setting('app.license.refresh_token') == null && $token == null)
                throw new LicenseInvalidException('No refresh token found');
            $response = $this->callAPI($token, '/checker', [
                'ctx-version' => ctx_version(),
                'ctx-php-version' => phpversion(),
                'ctx-themename' => app('theme')->getTheme()->uuid,
                'ctx-max-customers' => $license?->get('max'),
                'ctx-issued-by' => $license ? $license->get('issuedby') : 'CLIENTXCMS-001',
                'ctx-expire-at' => $license ? $license->get('expire') : '31-12-49',
                'ctx-customers' => Service::countCustomers(),
                'ctx-domain' => \URL::getRequest()->getHttpHost()
            ]);
            if ($response == null) {
                throw new LicenseInvalidException('Internal error please contact support');
            }
            $license = $response['license'] ?? null;
            if (array_key_exists('message', $response)) {
                throw new LicenseInvalidException($response['message']);
            }
            if ($license == null) {
                throw new LicenseInvalidException('Internal error please contact support. Licence undefined');
            }
            $license = new License(
                $license['expire_at'],
                $license['currentcustomers'],
                $license['domains'],
                $license['customers'],
                time(),
                $cache->getNextCheck(),
                $license['server'],
                $response['extensions'] ?? [],
                $response['downloads']['data'] ?? [],
            );
            $cache->persist($license);
            if (!$license->isValid()) {
                throw new LicenseInvalidException('License is invalid. Please renew your license.');
            }
            return $license;
        }
        return $cache->getLicense();
    }

    public function getLicenseFile()
    {
        return base_path('bootstrap/cache/license.php');
    }

    public function isExpired(): bool
    {
        $cache = new LicenseCache();
        $license = $cache->getLicense();
        if ($license == null)
            return true;
        if ($license->isValid())
            return false;
        return false;
    }

    public function hasExpiredFile(): bool
    {
        if ($this->isExpired())
            return true;
        return file_exists(storage_path('suspended'));
    }
}
