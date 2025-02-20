<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\LogRecord;

class LogFormatter
{
    /**
     * Customize the given logger instance.
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message%\n",
                'Y-m-d H:i:s',
                true,
                true
            ));
        }
    }
}
