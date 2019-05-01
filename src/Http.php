<?php
namespace WebAuth;
/**
 * Class Http
 * @package WebAuth
 */
class Http
{
    /**
     * @var int The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
     */
    protected $connectTimeout = 30;
    /**
     * @var string
     */
    protected $data = '';
    /**
     * @var int
     */
    protected $errno = 0;
    /**
     * @var string
     */
    protected $error = '';
    /**
     * @var string
     */
    protected $method = 'GET';
    /**
     * @var array
     */
    protected $requestHeaders = array();
    /**
     * @var string
     */
    protected $response = '';
    /**
     * @var array
     */
    protected $responseHeaders = array();
    /**
     * @var int
     */
    protected $status = 0;
    /**
     * @var int
     */
    protected $timeout = 30;
    /**
     * @var string
     */
    protected $userAgent = 'WebAuth Client 1.0';
    /**
     * @var int
     */
    protected $verifyHost = 2;
    /**
     * @var bool
     */
    protected $verifyPeer = false;

    /**
     * Http constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Retrieve the last error number
     *
     * @return int
     */
    public function getErrno()
    {
        return $this->errno;
    }

    /**
     * Retrieve a string containing the last error for the current session
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Retrieve response body
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Retrieve a decoded JSON response
     *
     * @return array
     */
    public function getDecodedJsonResponse()
    {
        return json_decode($this->response, true);
    }

    /**
     * Retrieve parsed String response
     *
     * @return array
     */
    public function getParsedStringResponse()
    {
        parse_str($this->response, $output);

        return $output;
    }

    /**
     * Retrieve all request headers
     *
     * @return array
     */
    public function getRequestHeaders()
    {
        return $this->requestHeaders;
    }

    /**
     * Retrieve specific response header
     *
     * @param string $name
     * @return string
     */
    public function getResponseHeader($name)
    {
        if (array_key_exists($name, $this->responseHeaders))
        {
            return $this->responseHeaders[$name];
        }

        return false;
    }

    /**
     * Retrieve all response headers
     *
     * @return array
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * Retrieve the response HTTP status code
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return Http
     */
    public function initialize()
    {
        $this->setConnectTimeout(30);
        $this->setData('');
        $this->setMethod('GET');
        $this->setRequestHeaders(array());
        $this->setTimeout(30);
        $this->setUserAgent('WebAuth Client 1.0');
        $this->setVerifyHost(2);
        $this->setVerifyPeer(false);

        return $this;
    }

    /**
     * Callback that reads the response headers
     *
     * @param resource $ch
     * @param string $header
     * @return int
     */
    protected function readHeaders($ch, $header)
    {
        $i = strpos($header, ':');
        if (!empty($i))
        {
            $key = strtolower(substr($header, 0, $i));
            $value = trim(substr($header, $i + 2));
            $this->responseHeaders[$key] = $value;
        }
        return strlen($header);
    }

    /**
     * Perform a cURL request
     *
     * @param string $url
     * @return Http
     */
    public function request($url)
    {
        $this->reset();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifyHost);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifyPeer);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'readHeaders'));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        switch (strtoupper($this->method))
        {
            case 'GET':
                curl_setopt($ch,CURLOPT_HTTPGET, TRUE);
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
                break;
            case 'HEAD':
                curl_setopt($ch, CURLOPT_NOBODY, TRUE);
                break;
        }

        if ($this->requestHeaders)
        {
            $headers = array();
            foreach ($this->requestHeaders as $name => $value)
            {
                $headers[] = sprintf("%s: %s", $name, $value);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $this->response = curl_exec($ch);
        $this->errno = curl_errno($ch);
        $this->error = curl_error($ch);
        $this->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $this;
    }

    /**
     * Reset the last response
     *
     * @return Http
     */
    public function reset()
    {
        $this->errno = 0;
        $this->error = '';
        $this->response = '';
        $this->responseHeaders = array();
        $this->status = 0;

        return $this;
    }

    /**
     * Set the number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
     *
     * @param int $timeout
     * @return Http
     */
    public function setConnectTimeout($timeout)
    {
        $this->connectTimeout = $timeout;

        return $this;
    }

    /**
     * Set the full data to post in a HTTP "POST"
     *
     * @param $data
     * @param bool $encode
     * @return Http
     */
    public function setData($data, $encode=true)
    {
        if (is_array($data) && $encode)
        {
            $data = http_build_query($data, '', '&');
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Set HTTP method
     *
     * @param string $method
     * @return Http
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Set a request header
     *
     * @param string $name
     * @param string $value
     * @return Http
     */
    public function setRequestHeader($name, $value)
    {
        $this->requestHeaders[$name] = $value;

        return $this;
    }

    /**
     * Set multiple request headers at once
     *
     * @param array $headers
     * @return Http
     */
    public function setRequestHeaders($headers)
    {
        foreach ($headers as $name => $value)
        {
            $this->setRequestHeader($name, $value);
        }

        return $this;
    }

    /**
     * Set $timeout
     *
     * @param int $timeout
     * @return Http
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Set $userAgent
     *
     * @param string $userAgent
     * @return Http
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Set $verifyHost
     * @param int $verifyHost
     * @return Http
     */
    public function setVerifyHost($verifyHost)
    {
        $this->verifyHost = $verifyHost;

        return $this;
    }

    /**
     * Set $verifyPeer
     * @param bool $verifyPeer
     * @return Http
     */
    public function setVerifyPeer($verifyPeer)
    {
        $this->verifyPeer = $verifyPeer;

        return $this;
    }
}