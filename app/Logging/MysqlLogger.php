<?php
namespace App\Logging;

class MysqlLogger
{
    public function __invoke(array $config){
        return tap(new \Monolog\Logger('mysql'), function ($logger) {
            $logger->pushHandler(new MysqlLoggerHandler());
        });
    }
}
