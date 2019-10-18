<?php


namespace Vanilla\Log;

use Monolog\Processor\ProcessorInterface;

class TimeProcessor implements ProcessorInterface
{
    public function __invoke(array $record)
    {
        $startTime = BEGIN_TIME;
        $endTime = sprintf('%.3F', microtime(TRUE));

        $record['elapsed'] = intval(bcmul(bcsub($endTime, $startTime, 3), 1000));

        $start = explode('.',$startTime);
        $end = explode('.',$endTime);

        $record['serviceStart'] = date('Y-m-d H:i:s.',$start[0]) . $start[1];
        $record['serviceEnd'] = date('Y-m-d H:i:s.',$end[0]) . $end[1];
        return $record;
    }
}
