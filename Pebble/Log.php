<?php declare (strict_types = 1);

namespace Pebble;

use Exception;

class Log
{

    public function __construct(array $options = [])
    {
        if (!isset($options['log_dir'])) {
            throw new Exception("The \Pebble\Log __construct method expects a 'log_dir' as an option");
        }
        $this->options = $options;
    }

    /**
     * Log types not used yet, but may come in handy at a time
     */
    private $logTypes = [
        'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency',
    ];

    /**
     * Log an message to a log file. Type will log message to a file named `$type`.log
     */
    public function message($message, string $type = 'debug', ?string $custom_log_file = null): void
    {

        $log_dir = $this->options['log_dir'] . '/';

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }

        // Default log file
        $log_file = $log_dir . '/main.log';
        if ($custom_log_file) {
            $log_file = $log_dir . '/' . $custom_log_file;
        }

        $log_message = $this->getMessage($message, $type);
        file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);

        $this->triggerEvents($log_message, $type);

    }

    /**
     * Create log message
     */
    private function getMessage($message, string $type): string
    {
        if (!is_string($message)) {
            $message = var_export($message, true);
        }

        // Generate message
        $time_stamp = date('Y-m-d H:i:s');
        $log_message = $time_stamp . ' ' . strtoupper($type) . ' ' . $message . PHP_EOL;
        return $log_message;
    }

    /**
     * Trigger special log events
     */
    private function triggerEvents($log_message, $type)
    {
        foreach ($this->events as $event) {
            if (in_array($type, $event['types'])) {
                $callable = $event['method'];
                $callable($log_message);
            }
        }
    }

    /**
     * Varaible hold $events
     */
    public $events = [];

    /**
     * Add an event to a log type, e.g. 'alert' or 'emergency' using a callable
     */
    public function on(array $types = [], callable $method = null)
    {

        $event = [
            'types' => $types,
            'method' => $method,
        ];

        $this->events[] = $event;
    }
}
