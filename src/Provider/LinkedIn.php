<?php
namespace WebAuth\Provider;

use WebAuth\BaseProvider;
use WebAuth\Exception;
use WebAuth\Identity;
use WebAuth\Http;

/**
 * Class LinkedIn
 * @package WebAuth\Provider
 */
class LinkedIn extends BaseProvider
{
    /**
     * @var string
     */
    protected $accessTokenUrl = 'https://www.linkedin.com/oauth/v2/accessToken';
    /**
     * @var string
     */
    protected $authenticateUrl = 'https://www.linkedin.com/oauth/v2/authorization';
    /**
     * @var string
     */
    protected $identityUrl = 'https://api.linkedin.com/v2/me';
    /**
     * @var string
     */
    protected $scope = 'r_liteprofile r_emailaddress';

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
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'scope' => $this->scope,
            'state' => $this->state,
        );

        $queryString = http_build_query($params, '', '&');

        return $this->authenticateUrl . '?' . $queryString;
    }

    /**
     * Perform a request for access token
     *
     * @return LinkedIn
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

        if (in_array($http->getStatus(), array(400, 401, 403, 404, 405, 429, 500, 504)))
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
     * @return LinkedIn
     * @throws Exception
     */
    public function requestIdentity()
    {
        $http = new Http();

        $http
            ->setRequestHeader('Connection', 'Keep-Alive')
            ->setRequestHeader('Authorization', 'Bearer ' . $this->getAccessToken())
            ->request($this->identityUrl);

        if (in_array($http->getStatus(), array(400, 401, 403, 404, 405, 429, 500, 504)))
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
            ->setFirstName($result['localizedFirstName'])
            ->setLastName($result['localizedLastName'])
            ->setDisplayName($result['localizedFirstName'].' '.$result['localizedLastName']);

        $http->request('https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))');
        $result = $http->getDecodedJsonResponse();
        $identity->setEmail(@$result['elements'][0]['handle~']['emailAddress']);

        $this->setIdentity($identity);

        return $this;
    }
}