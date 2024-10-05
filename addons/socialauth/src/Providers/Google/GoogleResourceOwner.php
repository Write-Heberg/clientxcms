<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Addons\SocialAuth\Providers\Google;

use App\Addons\SocialAuth\Contracts\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class GoogleResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * @var array
     */
    protected array $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response['sub'];
    }

    /**
     * Get preferred display name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->response['name'];
    }

    /**
     * Get preferred first name.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->getResponseValue('given_name');
    }

    /**
     * Get preferred last name.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->getResponseValue('family_name');
    }

    /**
     * Get locale.
     *
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->getResponseValue('locale');
    }

    /**
     * Get email address.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getResponseValue('email');
    }

    /**
     * Get hosted domain.
     *
     * @return string|null
     */
    public function getHostedDomain(): ?string
    {
        return $this->getResponseValue('hd');
    }

    /**
     * Get avatar image URL.
     *
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        return $this->getResponseValue('picture');
    }

    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }

    private function getResponseValue($key)
    {
        return $this->getValueByKey($this->response, $key);
    }

    public function getUsername(): string
    {
        return $this->getName();
    }
}
