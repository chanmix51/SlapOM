<?php
namespace SlapOM;

use \SlapOM\Exception\SlapOM as Exception;

class FileLogger implements LoggerInterface
{
    protected $handler;
    protected $level;

    public function __construct($file, $loglevel_filter = LoggerInterface::LOGLEVEL_INFO)
    {
        $this->level = $loglevel_filter;
        $this->handler = @fopen($file, 'a+');

        if ($this->handler === false)
        {
            throw new Exception(sprintf("Could not open ldap log file '%s' in write mode (append).", $file));
        }

        if (!@fwrite($this->handler, sprintf("%s\n", str_repeat('+', 32))))
        {
            throw new Exception(sprintf("Could not write in log file '%s'.", $file));
        }
    }

    public function __destruct()
    {
        if ($this->handler !== false)
        {
            @fwrite($this->handler, sprintf("%s\n", str_repeat('-', 32)));
        }

        fclose($this->handler);
    }

    public function setLogLevel($level)
    {
       $this->level = $level;
    }

    public function log($message, $loglevel = self::LOGLEVEL_DEBUG)
    {
        if ($this->level & $loglevel)
        {
            if (@fwrite($this->handler, sprintf("%26s |%s=> %s\n", date('Y-m-d H:i:s', microtime(true)), str_repeat(' ', $loglevel), $message)) === false)
            {
                throw new \Exception(sprintf("Could not write message to file."));
            }
        }
    }
}
