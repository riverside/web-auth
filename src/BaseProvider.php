<?php
namespace WebAuth;

/**
 * Class BaseProvider
 * @package WebAuth
 */
class BaseProvider
{
    /**
     * @var string
     */
    protected $clientId;
    /**
     * @var string
     */
    protected $clientSecret;
    /**
     * @var string
     */
    protected $code;
    /**
     * @var string
     */
    protected $redirectUri;
    /**
     * @var string
     */
    protected $state;

    /**
     * Retrieve an access token from session
     *
     * @return string
     */
    public function getAccessToken()
    {
        if (isset($_SESSION['access_token']) && !empty($_SESSION['access_token']))
        {
            return $_SESSION['access_token'];
        }

        return '';
    }

    /**
     * Retrieve an access token secret from session
     *
     * @return string
     */
    public function getAccessTokenSecret()
    {
        if (isset($_SESSION['access_token_secret']) && !empty($_SESSION['access_token_secret']))
        {
            return $_SESSION['access_token_secret'];
        }

        return '';
    }

    /**
     * Retrieve an identity from session
     *
     * @return Identity
     */
    public function getIdentity()
    {
        if (isset($_SESSION['identity']) && !empty($_SESSION['identity']))
        {
            return $_SESSION['identity'];
        }

        return null;
    }

    /**
     *
     */
    public function requestValidateToken()
    {}

    /**
     * Set access token in session
     *
     * @param string $accessToken
     * @return BaseProvider
     */
    public function setAccessToken($accessToken)
    {
        $_SESSION['access_token'] = $accessToken;

        return $this;
    }

    /**
     * Set access token secret in session
     *
     * @param string $accessTokenSecret
     * @return BaseProvider
     */
    public function setAccessTokenSecret($accessTokenSecret)
    {
        $_SESSION['access_token_secret'] = $accessTokenSecret;

        return $this;
    }

    /**
     * Set $clientId
     *
     * @param string $clientId
     * @return BaseProvider
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Set $clientSecret
     *
     * @param string $clientSecret
     * @return BaseProvider
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * Set $code
     *
     * @param string $code
     * @return BaseProvider
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Set identity in session
     *
     * @param Identity $identity
     * @return BaseProvider
     */
    public function setIdentity($identity)
    {
        $_SESSION['identity'] = $identity;

        return $this;
    }

    /**
     * Set $redirectUri
     *
     * @param string $redirectUri
     * @return BaseProvider
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    /**
     * Set $state
     *
     * @param string $state
     * @return BaseProvider
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }
}