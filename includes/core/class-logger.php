<?php
/**
 * Logger class for handling all plugin logging functionality
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Core
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Logger
 * 
 * Handles all logging operations for the plugin with different log levels
 * and output formats.
 *
 * @since 1.0.0
 */
class Logger {
    /**
     * Log levels
     *
     * @var array
     */
    private const LOG_LEVELS = [
        'emergency' => 0,
        'alert'     => 1,
        'critical'  => 2,
        'error'     => 3,
        'warning'   => 4,
        'notice'    => 5,
        'info'      => 6,
        'debug'     => 7
    ];

    /**
     * Log file path
     *
     * @var string
     */
    private $log_file;

    /**
     * Current minimum log level
     *
     * @var int
     */
    private $min_level;

    /**
     * Constructor
     *
     * @param string $log_file Path to log file
     * @param string $min_level Minimum log level to record
     */
    public function __construct($log_file = '', $min_level = 'info') {
        $this->log_file = $log_file ?: GL_CPG_PLUGIN_DIR . 'logs/plugin.log';
        $this->min_level = self::LOG_LEVELS[$min_level] ?? self::LOG_LEVELS['info'];
        
        $this->ensure_log_directory();
    }

    /**
     * Ensure log directory exists
     *
     * @return void
     */
    private function ensure_log_directory() {
        $dir = dirname($this->log_file);
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
    }

    /**
     * Log a message
     *
     * @param string $level Log level
     * @param string $message Message to log
     * @param array $context Additional context
     * @return bool Success status
     */
    public function log($level, $message, array $context = []): bool {
        if (!isset(self::LOG_LEVELS[$level]) || self::LOG_LEVELS[$level] > $this->min_level) {
            return false;
        }

        $timestamp = current_time('c');
        $formatted_message = $this->format_message($timestamp, $level, $message, $context);
        
        return error_log($formatted_message . PHP_EOL, 3, $this->log_file);
    }

    /**
     * Format log message
     *
     * @param string $timestamp Timestamp
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context array
     * @return string Formatted message
     */
    private function format_message($timestamp, $level, $message, array $context): string {
        $replaced = $this->interpolate($message, $context);
        return sprintf(
            '[%s] %s: %s %s',
            $timestamp,
            strtoupper($level),
            $replaced,
            !empty($context) ? json_encode($context) : ''
        );
    }

    /**
     * Interpolate context values into message placeholders
     *
     * @param string $message Message with placeholders
     * @param array $context Context values
     * @return string Interpolated message
     */
    private function interpolate($message, array $context = []): string {
        $replace = [];
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }

    /**
     * Log emergency message
     *
     * @param string $message Message to log
     * @param array $context Additional context
     * @return bool Success status
     */
    public function emergency($message, array $context = []): bool {
        return $this->log('emergency', $message, $context);
    }

    /**
     * Log alert message
     *
     * @param string $message Message to log
     * @param array $context Additional context
     * @return bool Success status
     */
    public function alert($message, array $context = []): bool {
        return $this->log('alert', $message, $context);
    }

    /**
     * Log critical message
     *
     * @param string $message Message to log
     * @param array $context Additional context
     * @return bool Success status
     */
    public function critical($message, array $context = []): bool {
        return $this->log('critical', $message, $context);
    }

    /**
     * Log error message
     *
     * @param string $message Message to log
     * @param array $context Additional context
     * @return bool Success status
     */
    public function error($message, array $context = []): bool {
        return $this->log('error', $message, $context);
    }

    /**
     * Log warning message
     *
     * @param string $message Message to log
     * @param array $context Additional context
     * @return bool Success status
     */
    public function warning($message, array $context = []): bool {
        return $this->log('warning', $message, $context);
    }

    /**
     * Log notice message
     *
     * @param string $message Message to log
     * @param array $context Additional context
     * @return bool Success status
     */
    public function notice($message, array $context = []): bool {
        return $this->log('notice', $message, $context);
    }

    /**
     * Log info message
     *
     * @param string $message Message to log
     * @param array $context Additional context
     * @return bool Success status
     */
    public function info($message, array $context = []): bool {
        return $this->log('info', $message, $context);
    }

    /**
     * Log debug message
     *
     * @param string $message Message to log
     * @param array $context Additional context
     * @return bool Success status
     */
    public function debug($message, array $context = []): bool {
        return $this->log('debug', $message, $context);
    }
}
