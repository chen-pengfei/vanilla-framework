<?php


namespace Vanilla\Log;

use Monolog\Processor\ProcessorInterface;

class AccessProcessor implements ProcessorInterface
{
    public function __invoke(array $record)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $enterParam = $_REQUEST;
        } else {
            if (strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false || strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false) {
                $enterParam = $_REQUEST;
            } elseif (strpos($_SERVER['CONTENT_TYPE'], 'application/octet-stream') !== false) {
                $enterParam = 'Binary file stream';
            } else {
                $enterParam = json_decode(file_get_contents("php://input"), true);
            }
        }

        //过滤
        $enterParam = $this->_filterSpecialField($enterParam);
        
        $record['arguments'] = json_encode((object)$enterParam,JSON_UNESCAPED_UNICODE);

        $record['status'] = app('response')->getStatusCode();

        $saveResultRate = env('SAVE_RESULT_RATE',200);
        if(rand(1,$saveResultRate) == 1) $record['result'] = app('response')->getBody();
        return $record;
    }


    public function _filterSpecialField($param)
    {
        if (isset($param['password'])) $param['password'] = '****';
        return $param;
    }

}
