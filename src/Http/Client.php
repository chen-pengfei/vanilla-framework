<?php
/**
 * Created by PhpStorm.
 * User: heyanlong
 * Date: 2018/7/24
 * Time: 下午2:25
 */

namespace Vanilla\Http;

use GuzzleHttp\TransferStats;

class Client extends \GuzzleHttp\Client
{

    protected $recordArgument = true;//日志记录参数
    protected $recordResult = true;//日志记录结果

    public function __construct(array $config = [])
    {
        $config = array_merge(['timeout' => 10, 'verify' => false], $config);
        if (isset($config['recordResult'])) {
            $this->recordResult = $config['recordResult'];
        }
        if (isset($config['recordArgument'])) {
            $this->recordArgument = $config['recordArgument'];
        }
        parent::__construct($config);
    }

    public function request($method, $uri = '', array $options = [])
    {
        $log = [
            'apiStart' => (new \DateTime())->format('Y-m-d H:i:s.u')
        ];
        $options['headers']['X-Ca-Traceid'] = traceId();
        $options = array_merge(
            [
                'on_stats' => function (TransferStats $stats) use (&$log, $uri) {
                    $log['elapsed'] = (int)bcmul($stats->getHandlerStat('total_time'), 1000);
                    $log['namelookupTime'] = $stats->getHandlerStat('namelookup_time');
                    $log['connectTime'] = $stats->getHandlerStat('connect_time');
                    $log['requestUri'] = $uri;
                },
                'force_ip_resolve' => 'v4'
            ]
            , $options);

        if (isset($options['query'])) {
            $log['arguments']['query'] = $options['query'];
        }
        if (isset($options['form_params'])) {
            $log['arguments']['form_params'] = $options['form_params'];
        }

        if (isset($options['headers'])) {
            $headers = $options['headers'];
            if (isset($headers['Authorization'])) {
                unset($headers['Authorization']);
            }
            $log['arguments']['headers'] = $headers;
        }

        if (isset($options['json'])) {
            $log['arguments']['json'] = $options['json'];
        }
        if (!$this->recordArgument) {//不记录参数
            $log['arguments'] = [];
        }
        $log['arguments'] = json_encode($log['arguments'], JSON_UNESCAPED_UNICODE);
        $log['method'] = $method;

        try {
            $response = parent::request($method, $uri, $options);
            $log['apiEnd'] = (new \DateTime())->format('Y-m-d H:i:s.u');
            $log['status'] = $response->getStatusCode();
            if ($this->recordResult) {
                $log['result'] = $response->getBody()->getContents();
            }
            $response->getBody()->rewind();

            info("http-client", ['apiRequest' => $log]);
            return $response;
        } catch (\Exception $e) {
            $log['exception'] = $e->getMessage();
            info("http-client-error", ['apiRequest' => $log]);
            throw $e;
        }
    }
}