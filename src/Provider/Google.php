<?php
namespace WebAuth\Provider;

use WebAuth\BaseProvider;
use WebAuth\Exception;
use WebAuth\Identity;
use WebAuth\Http;

/**
 * Class Google
 * @package WebAuth\Provider
 */
class Google extends BaseProvider
{
    /**
     * @var string
     */
	protected $accessTokenUrl = 'https://www.googleapis.com/oauth2/v4/token';
    /**
     * @var string
     */
	protected $authenticateUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    /**
     * @var string
     */
	protected $identityUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
    /**
     * @var string
     */
	protected $scope = 'profile email openid';

    /**
     * Retrieve an authorization URL to which the user must be redirected
     *
     * @return string
     */
	public function getAuthUrl()
    {
        $queryString = http_build_query(array(
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => $this->scope,
            'access_type' => 'offline',
            'state' => $this->state,
            'prompt' => 'consent select_account',
            'response_type' => 'code',
        ), '', '&');

        return $this->authenticateUrl . '?' . $queryString;
    }

    /**
     * Perform a request for access token
     *
     * @return Google
     * @throws Exception
     */
    public function requestAccessToken()
    {
        $http = new Http();

        $http
            ->setMethod('POST')
            ->setData(array(
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'code' => $this->code,
            ))
            ->request($this->accessTokenUrl);

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
     * Perform a request for person identity
     *
     * @return Google
     * @throws Exception
     */
    public function requestIdentity()
    {
        $http = new Http();

        $http->request($this->identityUrl . '?access_token=' . $this->getAccessToken());

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
            ->setFirstName($result['given_name'])
            ->setLastName($result['family_name'])
            ->setDisplayName($result['name'])
            ->setEmail($result['email']);

        $this->setIdentity($identity);

        return $this;
    }
}