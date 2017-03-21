<?php
namespace ApiMonitor;

use DB;

class Analyze
{
    protected $start_time;
    protected $end_time;
    public $http;

    /**
     * 详细记录
     * @param $uri
     * @param $method
     * @param $statusCode
     * @param $time
     * @param string $errorMsg
     * @return bool
     */
    public function log($uri, $method, $statusCode, $time, $errorMsg = '')
    {
        DB::table('api_monitor')->insert([
            'uri' => $uri,
            'method' => $method,
            'time' => $time,
            'status_code' => $statusCode,
            'error_msg' => $errorMsg,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function monitor(Client $httpClient)
    {
        $this->http = $httpClient;
        $this->start_time = $this->http->startTime;
        $this->end_time = microtime();
        return $this->log($this->http->uri, $this->http->method, $this->http->statusCode, $this->_diffTime(), $this->http->errorMsg);
    }


    public function _diffTime()
    {
        $startTime = explode(' ', $this->start_time);
        $endTime = explode(' ', $this->end_time);
        $thisTime = $endTime[0] + $endTime[1] - ($startTime[0] + $startTime[1]);
        return round($thisTime, 3);
    }
}
