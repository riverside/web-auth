<?php
namespace WebAuth;
/**
 * Class Client
 * @package WebAuth
 */
class Client
{
    /**
     * @var
     */
    protected $provider;

    /**
     * Client constructor.
     * @param $provider
     */
    public function __construct($provider)
    {
        $class = "WebAuth\\Provider\\$provider";
        $this->provider = new $class;
    }

    /**
     * Overloading
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->provider, $name), $arguments);
    }
}