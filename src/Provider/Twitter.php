<?php
namespace WebAuth\Provider;

use WebAuth\BaseProvider;
use WebAuth\Exception;
use WebAuth\Identity;
use WebAuth\OAuth1;
use WebAuth\Http;

/**
 * Class Twitter
 * @package WebAuth\Provider
 */
class Twitter extends BaseProvider
{
    /**
     * @var string
     */
    protected $accessTokenUrl = 'https://api.twitter.com/oauth/access_token';
    /**
     * @var string
     */
    protected $authenticateUrl = 'https://api.twitter.com/oauth/authenticate';
    /**
     * @var string
     */
    protected $identityUrl = 'https://api.twitter.com/1.1/account/verify_credentials.json';
    /**
     * @var string
     */
    protected $requestTokenUrl = 'https://api.twitter.com/oauth/request_token';

    /**
     * Retrieve an authorization URL to which the user must be redirected
     *
     * @return string
     * @throws Exception
     */
    public function getAuthUrl()
    {
        return $this->authenticateUrl . '?oauth_token=' . $this->requestRequestToken();
    }

    /**
     * Perform a request for access token
     *
     * @param string $oauth_verifier
     * @return Twitter
     * @throws Exception
     */
    public function requestAccessToken($oauth_verifier)
    {
        $nonce = OAuth1::getNonce();
        $time = OAuth1::getTimestamp();

        $signature = OAuth1::generateSignature(
            $this->clientId,
            $this->clientSecret,
            'POST',
            $this->accessTokenUrl,
            $_SESSION['Twitter_OAuth_Token'],
            $_SESSION['Twitter_OAuth_Token_Secret'],
            $nonce,
            $time);

        $authorization = sprintf('OAuth oauth_consumer_key="%s", oauth_nonce="%s", oauth_signature="%s", oauth_signature_method="HMAC-SHA1", oauth_timestamp="%u", oauth_token="%s", oauth_version="1.0"',
            rawurlencode($this->clientId),
            rawurlencode($nonce),
            rawurlencode($signature),
            rawurlencode($time),
            rawurlencode($_SESSION['Twitter_OAuth_Token']));

        $http = new Http();

        $http
            ->setMethod('POST')
            ->setData(array(
                'oauth_verifier' => $oauth_verifier,
            ))
            ->setRequestHeader('Authorization', $authorization)
            ->request($this->accessTokenUrl);

        if ($http->getStatus() != 200)
        {
            throw new Exception('Request for an access token failed (1).');
        }

        if (!$http->getResponse())
        {
            throw new Exception('Request for an access token failed (2).');
        }

        $result = $http->getParsedStringResponse();
        if (!isset($result['oauth_token'], $result['oauth_token_secret']))
        {
            throw new Exception('Request for an access token failed (3).');
        }

        $this->setAccessToken($result['oauth_token']);
        $this->setAccessTokenSecret($result['oauth_token_secret']);

        return $this;
    }

    /**
     * Perform a request for person identity
     *
     * @return Twitter
     * @throws Exception
     */
    public function requestIdentity()
    {
        $params = array(
            'include_entities' => 'false',
            'skip_status' => 'true',
            'include_email' => 'true',
        );
        $queryString = http_build_query($params, '', '&');

        $nonce = OAuth1::getNonce();
        $time = OAuth1::getTimestamp();

        $signature = OAuth1::generateSignature(
            $this->clientId,
            $this->clientSecret,
            'GET',
            $this->identityUrl . '?' . $queryString,
            $this->getAccessToken(),
            $this->getAccessTokenSecret(),
            $nonce,
            $time);

        $authorization = sprintf('OAuth oauth_consumer_key="%s", oauth_nonce="%s", oauth_signature="%s", oauth_signature_method="HMAC-SHA1", oauth_timestamp="%u", oauth_token="%s", oauth_version="1.0"',
            rawurlencode($this->clientId),
            rawurlencode($nonce),
            rawurlencode($signature),
            rawurlencode($time),
            rawurlencode($this->getAccessToken())
        );

        $http = new Http();

        $http
            ->setRequestHeader('Authorization', $authorization)
            ->request($this->identityUrl . '?' . $queryString);

        if ($http->getStatus() != 200)
        {
            throw new Exception('Request for an identity failed (1).' . $http->getStatus());
        }

        if (!$http->getResponse())
        {
            throw new Exception('Request for an identity failed (3).');
        }

        $result = $http->getDecodedJsonResponse();

        $identity = new Identity();
        $identity
            ->setId($result['id'])
            ->setFirstName($result['name'])
            ->setLastName($result['name'])
            ->setDisplayName($result['screen_name'])
            ->setEmail(@$result['email']);

        $this->setIdentity($identity);

        return $this;
    }

    /**
     * Perform a request for request token
     *
     * @return string
     * @throws Exception
     */
    protected function requestRequestToken()
    {
        $nonce = OAuth1::getNonce();
        $time = OAuth1::getTimestamp();

        $accessToken = '16345131-KUAbqq7buTsOSL9HJDh0XSWxwX5XebuxxMQUHUIqz';
        $accessTokenSecret = 'EwFXvAxZEgsRbg5CgtHwXkgFRtleNS6nFeg2v78CCK5Qm';

        $signature = OAuth1::generateSignature(
            $this->clientId,
            $this->clientSecret,
            'POST',
            $this->requestTokenUrl,
            $accessToken,
            $accessTokenSecret,
            $nonce,
            $time);

        $http = new Http();

        $http
            ->setMethod('POST')
            ->setRequestHeader('Accept', '*/*')
            ->setRequestHeader('Authorization', OAuth1::getAuthorizationHeader($this->redirectUri, $this->clientId, $accessToken, $signature, $nonce, $time))
            ->request($this->requestTokenUrl);

        if ($http->getStatus() != 200)
        {
            throw new Exception('Request token failed to be retrieved (1).');
        }

        $result = $http->getParsedStringResponse();
        if (!(isset($result['oauth_token'], $result['oauth_token_secret'], $result['oauth_callback_confirmed']) && $result['oauth_callback_confirmed'] == "true"))
        {
            throw new Exception('Request token failed to be retrieved (2).');
        }

        $_SESSION['Twitter_OAuth_Token'] = $result['oauth_token'];
        $_SESSION['Twitter_OAuth_Token_Secret'] = $result['oauth_token_secret'];

        return $result['oauth_token'];
    }
}