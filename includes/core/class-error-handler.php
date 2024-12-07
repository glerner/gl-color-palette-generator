<?php
/**
 * Error Handler Class
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
 * Class Error_Handler
 * 
 * Handles error management and reporting for the plugin
 *
 * @since 1.0.0
 */
class Error_Handler {
    /**
     * Error codes and messages
     *
     * @var array
     */
    private const ERROR_MESSAGES = [
        'INVALID_COLOR' => [
            'code' => 1001,
            'message' => 'Invalid color format provided'
        ],
        'INVALID_SCHEME' => [
            'code' => 1002,
            'message' => 'Invalid color scheme specified'
        ],
        'API_ERROR' => [
            'code' => 2001,
            'message' => 'Error communicating with AI service'
        ],
        'RATE_LIMIT' => [
            'code' => 2002,
            'message' => 'API rate limit exceeded'
        ],
        'CACHE_ERROR' => [
            'code' => 3001,
            'message' => 'Cache operation failed'
        ],
        'VALIDATION_ERROR' => [
            'code' => 4001,
            'message' => 'Input validation failed'
        ],
        'SYSTEM_ERROR' => [
            'code' => 5001,
            'message' => 'Internal system error occurred'
        ]
    ];

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Last error details
     *
     * @var array|null
     */
    private $last_error = null;

    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     */
    public function __construct(Logger $logger) {
        $this->logger = $logger;
        $this->register_error_handlers();
    }

    /**
     * Register custom error handlers
     *
     * @return void
     */
    private function register_error_handlers() {
        set_error_handler([$this, 'handle_error']);
        set_exception_handler([$this, 'handle_exception']);
        register_shutdown_function([$this, 'handle_shutdown']);
    }

    /**
     * Custom error handler
     *
     * @param int    $errno   Error number
     * @param string $errstr  Error message
     * @param string $errfile File where error occurred
     * @param int    $errline Line number where error occurred
     * @return bool Whether the error was handled
     */
    public function handle_error($errno, $errstr, $errfile, $errline): bool {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $error = [
            'type' => $this->get_error_type($errno),
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ];

        $this->log_error($error);
        $this->last_error = $error;

        if ($errno === E_USER_ERROR) {
            exit(1);
        }

        return true;
    }

    /**
     * Custom exception handler
     *
     * @param \Throwable $exception Exception object
     * @return void
     */
    public function handle_exception(\Throwable $exception) {
        $error = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];

        $this->log_error($error);
        $this->last_error = $error;

        if (defined('WP_DEBUG') && WP_DEBUG) {
            throw $exception;
        } else {
            wp_die(
                esc_html__('An error occurred while processing your request.', 'gl-color-palette-generator'),
                esc_html__('Error', 'gl-color-palette-generator'),
                ['response' => 500]
            );
        }
    }

    /**
     * Shutdown handler for fatal errors
     *
     * @return void
     */
    public function handle_shutdown() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handle_error($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Get error type string from error number
     *
     * @param int $type Error type number
     * @return string Error type string
     */
    private function get_error_type(int $type): string {
        switch($type) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
            default:
                return 'UNKNOWN';
        }
    }

    /**
     * Log error details
     *
     * @param array $error Error details
     * @return void
     */
    private function log_error(array $error) {
        $this->logger->error('Error occurred', $error);
    }

    /**
     * Get error details by code
     *
     * @param string $code Error code
     * @return array|null Error details or null if not found
     */
    public function get_error_details(string $code): ?array {
        return self::ERROR_MESSAGES[$code] ?? null;
    }

    /**
     * Get last error
     *
     * @return array|null Last error details or null if no error
     */
    public function get_last_error(): ?array {
        return $this->last_error;
    }

    /**
     * Clear last error
     *
     * @return void
     */
    public function clear_last_error() {
        $this->last_error = null;
    }

    /**
     * Throw a plugin-specific error
     *
     * @param string $code Error code
     * @param string $additional_info Additional error information
     * @throws \Exception
     * @return void
     */
    public function throw_error(string $code, string $additional_info = '') {
        $error = $this->get_error_details($code);
        
        if (!$error) {
            throw new \Exception('Unknown error code: ' . $code);
        }

        $message = $error['message'];
        if ($additional_info) {
            $message .= ': ' . $additional_info;
        }

        throw new \Exception($message, $error['code']);
    }
}
