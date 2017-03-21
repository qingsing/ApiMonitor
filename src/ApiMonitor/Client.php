<?php
namespace ApiMonitor;

use GuzzleHttp\Client as guzzlehttp;

class Client extends guzzlehttp
{
    public $method;
    public $uri;
    public $statusCode;
    public $errorMsg;
    public $startTime;
    protected $analyzeObj;
    public $retSuccess = 0;
    const RET_FAIL = 1;
    const RET_SUCCESS = 2;


    protected function _init()
    {
        !$this->analyzeObj && $this->analyzeObj = new Analyze();
        $this->statusCode = '';
        $this->errorMsg = '';
        $this->retSuccess = 0;
    }


    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @param array $successTag [key,value]
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function request($method, $uri = '', array $options = [], array $successTag = [])
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

        if ($successTag) {
            $this->_retSuccess($http_ret, $successTag);
        }

        $this->analyzeObj->monitor($this);
        if ($this->errorMsg) {
            throw $e;
        } else {
            return $http_ret;
        }
    }


    protected function _retSuccess($http_ret, $successTag)
    {
        if ($this->errorMsg) {
            $this->retSuccess = self::RET_FAIL;
            return true;
        }

        $content = $http_ret->getBody()->getContents();
        if ($content) {
            if (isset($content[$successTag[0]]) && $content[$successTag[0]] == $successTag[1]) {
                $this->retSuccess = self::RET_SUCCESS;
            } else {
                $this->retSuccess = self::RET_FAIL;
            }
        } else {
            $this->retSuccess = self::RET_FAIL;
        }
    }
}
