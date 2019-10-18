<?php

namespace Vanilla\Log;


use Monolog\Formatter\FormatterInterface;

class ZhiXingFormatter implements FormatterInterface
{


    public function __construct()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        unset($record['extra']);
        unset($record['datetime']);

        $record['logType'] = 'run';
        if($record['level_name'] == 'INFO' && $record['message'] == 'access log record'){
            $record['logType'] = 'access';
        }

        if($record['level_name'] == 'ERROR'){
            $record['logType'] = 'error';
            $record['throwable'] = '';
            if(isset($record['context']['throwable'])){
                try{
                    $record['throwable'] = json_encode($record['context'],JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $record['context'] = [];
                }catch (\Exception $e){
                    //
                }
            }
        }

        //api请求
        if(!empty($record['context']['apiRequest'])){
            $record['apiRequest'] = $record['context']['apiRequest'];
            unset($record['context']['apiRequest']);
        }

        $record['level'] = $record['level_name'];
        unset($record['level_name']);

        if (php_sapi_name() == 'cli') {
            $record['channel'] = $record['channel'].'_cli';
        }

        $record['context'] = json_encode((object)$record['context'],JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return json_encode($record,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        $formatted = array();

        foreach ($records as $record) {
            $formatted[] = $this->format($record);
        }

        return $formatted;
    }
}
