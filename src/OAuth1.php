<?php
namespace WebAuth;

/**
 * Class OAuth1
 * @package WebAuth
 */
class OAuth1
{
    /**
     * Retrieve a signature
     *
     * @param string $consumerKey
     * @param string $clientSecret
     * @param string $method
     * @param string $url
     * @param string $access_token
     * @param string $access_token_secret
     * @param string $nonce
     * @param int $time
     * @return string
     */
    public static function generateSignature($consumerKey, $clientSecret, $method, $url, $access_token, $access_token_secret, $nonce, $time)
    {
        $params = array(
            'oauth_consumer_key' => $consumerKey,
            'oauth_nonce' => $nonce,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => $time,
            'oauth_token' => $access_token,
            'oauth_version' => '1.0'
        );

        $queryString = parse_url($url, PHP_URL_QUERY);
        if ($queryString)
        {
            parse_str($queryString, $output);
            $params = array_merge($output, $params);
            ksort($params);
            $length = strlen($queryString) + 1;
            $url = substr($url, 0, -$length);
        }

        $base = strtoupper($method)
            .'&'.rawurlencode($url)
            .'&'.rawurlencode(http_build_query($params, '','&', PHP_QUERY_RFC3986));

        $key = rawurlencode($clientSecret).'&'.rawurlencode($access_token_secret);

        return base64_encode(hash_hmac("sha1", $base, $key, true));
    }

    /**
     * Retrieve an value used in Authorization header
     *
     * @param string $realm
     * @param string $consumerKey
     * @param string $token
     * @param string $signature
     * @param string $nonce
     * @param int $time
     * @return string
     */
    public static function getAuthorizationHeader($realm, $consumerKey, $token, $signature, $nonce, $time)
    {
        return sprintf('OAuth realm="%s", oauth_consumer_key="%s", oauth_nonce="%s", oauth_signature="%s", oauth_signature_method="HMAC-SHA1", oauth_timestamp="%u", oauth_token="%s", oauth_version="1.0"',
            rawurlencode($realm),
            rawurlencode($consumerKey),
            rawurlencode($nonce),
            rawurlencode($signature),
            rawurlencode($time),
            rawurlencode($token));
    }

    /**
     * Retrieve a nonce (32-bit random string)
     *
     * @return string
     */
    public static function getNonce()
    {
        return md5(uniqid(rand(), true));
    }

    /**
     * Retrieve current timestamp
     *
     * @return int
     */
    public static function getTimestamp()
    {
        return time();
    }
}