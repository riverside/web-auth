<?php
namespace WebAuth\Provider;

use WebAuth\BaseProvider;
use WebAuth\Exception;
use WebAuth\Identity;
use WebAuth\Http;

/**
 * Class Facebook
 * @package WebAuth\Provider
 */
class Facebook extends BaseProvider
{
    /**
     * @var string
     */
    protected $accessTokenUrl = 'https://graph.facebook.com/v3.3/oauth/access_token';
    /**
     * @var string
     */
    protected $authenticateUrl = 'https://www.facebook.com/v3.3/dialog/oauth';
    /**
     * @var string
     */
    protected $identityUrl = 'https://graph.facebook.com/v3.3/me';
    /**
     * @var string
     */
    protected $scope = 'email';
    /**
     * @var string
     */
    protected $validateTokenUrl = 'https://graph.facebook.com/debug_token';

    /**
     * Retrieve an authorization URL to which the user must be redirected
     *
     * @return string
     */
    public function getAuthUrl()
    {
        $params = array(
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'state' => $this->state,
            'scope' => $this->scope,
        );

        $queryString = http_build_query($params, '', '&');

        return $this->authenticateUrl . '?' . $queryString;
    }

    /**
     * Perform a request for access token
     *
     * @return Facebook
     * @throws Exception
     */
    public function requestAccessToken()
    {
        $queryString = http_build_query(array(
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $this->code,
        ), '', '&');

        $http = new Http();

        $http->request($this->accessTokenUrl . '?' . $queryString);

        if ($http->getStatus() != 200)
        {
            throw new Exception('Request for an access token failed (1).');
        }

        if (!$http->getResponse())
        {
            throw new Exception('Request for an access token failed (2).');
        }

        $result = $http->getDecodedJsonResponse();
        if (!isset($result['access_token']))
        {
            throw new Exception('Request for an access token failed (3).');
        }

        $this->setAccessToken($result['access_token']);

        return $this;
    }

    /**
     * Perform a request for token validation
     *
     * @return Facebook
     * @throws Exception
     */
    public function requestValidateToken()
    {
        $queryString = http_build_query(array(
            'input_token' => $this->getAccessToken(),
            'access_token' => $this->clientId.'|'.$this->clientSecret
        ), '', '&');

        $http = new Http();

        $http->request($this->validateTokenUrl . '?' . $queryString);

        if ($http->getStatus() != 200)
        {
            throw new Exception('Token validation failed (1).');
        }

        if (!$http->getResponse())
        {
            throw new Exception('Token validation failed (2).');
        }

        $result = $http->getDecodedJsonResponse();

        if (!isset($result['data']['app_id']))
        {
            throw new Exception('Token validation failed (3).');
        }

        if ($result['data']['app_id'] != $this->clientId)
        {
            throw new Exception('Token validation failed (4).');
        }

        return $this;
    }

    /**
     * Perform a request for person identity
     *
     * @return Facebook
     * @throws Exception
     */
    public function requestIdentity()
    {
        $queryString = http_build_query(array(
            'access_token' => $this->getAccessToken(),
            'fields' => 'id,email,first_name,last_name,name',
        ), '', '&');

        $http = new Http();

        $http->request($this->identityUrl . '?' . $queryString);

        if ($http->getStatus() != 200)
        {
            throw new Exception('Request for an identity failed (1).');
        }

        if (!$http->getResponse())
        {
            throw new Exception('Request for an identity failed (3).');
        }

        $result = $http->getDecodedJsonResponse();

        $identity = new Identity();
        $identity
            ->setId($result['id'])
            ->setFirstName($result['first_name'])
            ->setLastName($result['last_name'])
            ->setDisplayName($result['name'])
            ->setEmail($result['email']);

        $this->setIdentity($identity);

        return $this;
    }
}