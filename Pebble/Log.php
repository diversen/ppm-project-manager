<?php declare (strict_types = 1);

namespace Pebble;

use Exception;

class Log
{

    public function __construct(array $options = [])
    {

        if (!isset($options['log_dir']) && !isset($options['stream'])) {
            throw new Exception("The \Pebble\Log __construct method expects a log dir -> 'log_dir' => './logs' (log into a file) or a stream, e.g: 'stream' => 'php://stderr' ");
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
     * Get log file from configuration
     */
    private function getLogFile(?string $custom_log_file = null) {
        $log_dir = $this->options['log_dir'] . '/';

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }

        // Default log file
        $log_file = $log_dir . '/main.log';
        if ($custom_log_file) {
            $log_file = $log_dir . '/' . $custom_log_file;
        }

        return $log_file;
    }

    /**
     * Log a message to a log file or a stream
     */
    public function message($message, string $type = 'debug', ?string $custom_log_file = null): void
    {

        $log_message = $this->getMessage($message, $type);
        if (isset($this->options['log_dir'])) {
            $log_file = $this->getLogFile($custom_log_file);
            file_put_contents($log_file, $log_message, FILE_APPEND);
        }

        if (isset($this->options['stream'])) {
            file_put_contents($this->options['stream'], $log_message, FILE_APPEND);
        }
        

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
