<?php

namespace Vanilla\Log;

use Monolog\Processor\ProcessorInterface;

class RequestProcessor implements ProcessorInterface
{
    public function __invoke(array $record)
    {
        $record['requestHost'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; // 记录请求域名
        $record['requestUri'] = $_SERVER['REQUEST_URI'];
        $record['requestMethod'] = $_SERVER['REQUEST_METHOD'];
        $record['requestRoute'] = explode('?',$_SERVER['REQUEST_URI'])[0];
        $record['referer'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $record['contentType'] = $_SERVER['CONTENT_TYPE'];
        }

        $record['clientIp'] = getClientIp();
        $record['hostName'] = php_uname('n');
        $record['hostAddress'] = $_SERVER['SERVER_ADDR'] ?? '';

        return $record;
    }

}

