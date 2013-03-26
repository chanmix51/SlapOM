<?php
namespace SlapOM;

interface LoggerInterface 
{
    const LOGLEVEL_CRITICAL = 1;
    const LOGLEVEL_ERROR    = 2;
    const LOGLEVEL_WARNING  = 4;
    const LOGLEVEL_INFO     = 8;
    const LOGLEVEL_DEBUG    = 16;
    const LOGLEVEL_ALL      = 255;

    public function setLogLevel($loglevel);

    /**
     * log
     * Add a message to the logger
     * @param message String the log message
     * @loglevel Integer (default static::LOGLEVEL_DEBUG) the message log level
     **/
    public function log($message, $loglevel = self::LOGLEVEL_DEBUG);
}
