<?php


namespace Vanilla\Log;

use Monolog\Processor\ProcessorInterface;

class TraceIdProcessor implements ProcessorInterface
{
    public function __invoke(array $record)
    {
        $record['traceId'] = traceId();
        $record['gwTraceId'] = gatewayTraceId();

        if (php_sapi_name() == 'cli') {
            if(!empty($_SERVER['CLI_TRACEID'])){
                $record['traceId'] = $_SERVER['CLI_TRACEID'];
            }
        }

        return $record;
    }

}
