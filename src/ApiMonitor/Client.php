<?php
namespace ApiMonitor;

use GuzzleHttp\Client as guzzlehttp;

class Client  extends guzzlehttp
{
    public $method;
    public $uri;
    public $statusCode;
    public $errorMsg;
    public $startTime;
    protected $analyze;


    protected function _init()
    {
        !$this->analyze && $this->analyze = new Analyze();
        $this->statusCode = '';
        $this->errorMsg = '';
    }


    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function request($method, $uri = '', array $options = [])
    {
        $this->_init();
        $this->startTime = microtime();
        $this->method = $method;
        $this->uri = $uri;

        try {
            $http_ret = parent::request($method, $uri, $options);
            $this->statusCode = $http_ret->getStatusCode();
        } catch (\Exception $e) {
            $this->errorMsg = $e->getMessage();
        }
        $this->analyze->monitor($this);
        if ($this->errorMsg) {
            throw $e;
        } else {
            return $http_ret;
        }
    }
}
