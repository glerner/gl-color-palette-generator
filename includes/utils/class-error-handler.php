<?php
namespace GLColorPalette;

class ErrorHandler {
    private $error_reporter;
    private $last_error;
    private $error_stack = [];
    private $recovery_attempts = 0;
    private $max_recovery_attempts = 3;

    public function __construct() {
        $this->error_reporter = new ErrorReporter();
        $this->register_error_handlers();
    }

    /**
     * Register custom error handlers
     */
    private function register_error_handlers() {
        set_error_handler([$this, 'handle_php_error']);
        set_exception_handler([$this, 'handle_uncaught_exception']);
        register_shutdown_function([$this, 'handle_fatal_error']);
    }

    /**
     * Main error handling method
     */
    public function handle_error($code, $message, $context = [], $attempt_recovery = true) {
        $this->last_error = [
            'code' => $code,
            'message' => $message,
            'context' => $context,
            'timestamp' => current_time('mysql'),
            'severity' => ErrorCodes::get_severity($code),
            'category' => ErrorCodes::get_category($code)
        ];

        $this->error_stack[] = $this->last_error;

        // Log the error
        $this->log_error($this->last_error);

        // Attempt recovery if appropriate
        if ($attempt_recovery && $this->can_attempt_recovery($code)) {
            return $this->attempt_recovery($code, $context);
        }

        // Handle based on severity
        switch ($this->last_error['severity']) {
            case 'critical':
                $this->handle_critical_error();
                break;
            case 'error':
                $this->handle_standard_error();
                break;
            case 'warning':
                $this->handle_warning();
                break;
            case 'notice':
                $this->handle_notice();
                break;
        }

        return $this->format_error_response();
    }

    /**
     * Handle PHP errors
     */
    public function handle_php_error($errno, $errstr, $errfile, $errline) {
        $code = $this->map_php_error_to_code($errno);
        $context = [
            'file' => $errfile,
            'line' => $errline,
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ];

        return $this->handle_error($code, $errstr, $context);
    }

    /**
     * Handle uncaught exceptions
     */
    public function handle_uncaught_exception($exception) {
        $code = $exception instanceof ColorPaletteException
            ? $exception->getCode()
            : ErrorCodes::VALIDATION_TYPE_MISMATCH;

        $context = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace()
        ];

        return $this->handle_error($code, $exception->getMessage(), $context);
    }

    /**
     * Handle fatal errors
     */
    public function handle_fatal_error() {
        $error = error_get_last();

        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_COMPILE_ERROR])) {
            $code = $this->map_php_error_to_code($error['type']);
            $context = [
                'file' => $error['file'],
                'line' => $error['line']
            ];

            $this->handle_error($code, $error['message'], $context, false);
        }
    }

    /**
     * Attempt to recover from error
     */
    private function attempt_recovery($code, $context) {
        if ($this->recovery_attempts >= $this->max_recovery_attempts) {
            return $this->handle_error(
                ErrorCodes::PERF_CONCURRENT_LIMIT,
                'Maximum recovery attempts exceeded',
                $context,
                false
            );
        }

        $this->recovery_attempts++;

        switch ($code) {
            case ErrorCodes::COLOR_INVALID_HEX:
                return $this->recover_invalid_hex($context);

            case ErrorCodes::API_RATE_LIMIT_EXCEEDED:
                return $this->handle_rate_limit($context);

            case ErrorCodes::ACCESS_CONTRAST_RATIO_LOW:
                return $this->adjust_contrast($context);

            case ErrorCodes::FILE_PERMISSION_DENIED:
                return $this->fix_permissions($context);

            default:
                return false;
        }
    }

    /**
     * Handle critical errors
     */
    private function handle_critical_error() {
        // Rollback any pending changes
        $this->rollback_changes();

        // Clear cache if necessary
        $this->clear_cache();

        // Notify admin if configured
        $this->notify_admin();

        // Log detailed debug information
        if (WP_DEBUG) {
            $this->log_debug_info();
        }
    }

    /**
     * Handle standard errors
     */
    private function handle_standard_error() {
        // Add error to WordPress admin notices
        add_action('admin_notices', [$this, 'display_admin_notice']);

        // Log error for debugging
        if (WP_DEBUG) {
            $this->log_debug_info();
        }
    }

    /**
     * Format error response
     */
    private function format_error_response() {
        $error = $this->last_error;

        return [
            'success' => false,
            'error' => [
                'code' => $error['code'],
                'message' => $error['message'],
                'severity' => $error['severity'],
                'category' => $error['category'],
                'is_fixable' => ErrorCodes::is_user_fixable($error['code']),
                'suggested_fix' => ErrorCodes::get_suggested_fix($error['code'])
            ],
            'debug' => WP_DEBUG ? [
                'context' => $error['context'],
                'stack' => $this->error_stack,
                'recovery_attempts' => $this->recovery_attempts
            ] : null
        ];
    }

    /**
     * Recovery methods for specific errors
     */
    private function recover_invalid_hex($context) {
        if (empty($context['color'])) {
            return false;
        }

        $color = $context['color'];

        // Try to fix common hex color issues
        if (strlen($color) === 3) {
            // Convert 3-digit hex to 6-digit
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }

        // Add missing hash
        if (strlen($color) === 6 && strpos($color, '#') !== 0) {
            $color = '#' . $color;
        }

        // Validate fixed color
        if (preg_match('/^#[a-f0-9]{6}$/i', $color)) {
            return [
                'success' => true,
                'recovered' => true,
                'value' => $color,
                'original' => $context['color']
            ];
        }

        return false;
    }

    /**
     * Handle rate limit errors
     */
    private function handle_rate_limit($context) {
        $wait_time = isset($context['retry_after']) ? $context['retry_after'] : 60;

        // Add to queue for later processing
        $this->add_to_processing_queue($context, $wait_time);

        return [
            'success' => true,
            'queued' => true,
            'retry_after' => $wait_time
        ];
    }

    /**
     * Adjust contrast for accessibility
     */
    private function adjust_contrast($context) {
        if (empty($context['foreground']) || empty($context['background'])) {
            return false;
        }

        $color_adjuster = new ColorAdjuster();
        $adjusted_colors = $color_adjuster->adjust_for_contrast(
            $context['foreground'],
            $context['background'],
            4.5 // WCAG AA standard
        );

        if ($adjusted_colors) {
            return [
                'success' => true,
                'recovered' => true,
                'adjusted_colors' => $adjusted_colors,
                'original_colors' => [
                    'foreground' => $context['foreground'],
                    'background' => $context['background']
                ]
            ];
        }

        return false;
    }

    /**
     * Fix file permissions
     */
    private function fix_permissions($context) {
        if (empty($context['file'])) {
            return false;
        }

        $file = $context['file'];
        $success = false;

        if (file_exists($file)) {
            if (is_dir($file)) {
                $success = chmod($file, 0755);
            } else {
                $success = chmod($file, 0644);
            }
        }

        return [
            'success' => $success,
            'recovered' => $success,
            'file' => $file,
            'permissions' => $success ? decoct(fileperms($file) & 0777) : null
        ];
    }

    /**
     * Display admin notice
     */
    public function display_admin_notice() {
        $error = $this->last_error;
        $class = 'notice notice-' . $error['severity'];
        $message = sprintf(
            __('%s: %s', 'color-palette-generator'),
            ucfirst($error['severity']),
            $error['message']
        );

        printf(
            '<div class="%1$s"><p>%2$s</p></div>',
            esc_attr($class),
            esc_html($message)
        );
    }

    /**
     * Utility methods
     */
    private function can_attempt_recovery($code) {
        return ErrorCodes::is_user_fixable($code) &&
               $this->recovery_attempts < $this->max_recovery_attempts;
    }

    private function map_php_error_to_code($php_error) {
        $map = [
            E_ERROR => ErrorCodes::VALIDATION_TYPE_MISMATCH,
            E_WARNING => ErrorCodes::PERF_MEMORY_WARNING,
            E_PARSE => ErrorCodes::VALIDATION_SYNTAX_ERROR,
            E_NOTICE => ErrorCodes::VALIDATION_NOTICE,
            E_CORE_ERROR => ErrorCodes::SYSTEM_CORE_ERROR,
            E_CORE_WARNING => ErrorCodes::SYSTEM_CORE_WARNING,
            E_COMPILE_ERROR => ErrorCodes::SYSTEM_COMPILE_ERROR,
            E_COMPILE_WARNING => ErrorCodes::SYSTEM_COMPILE_WARNING,
            E_USER_ERROR => ErrorCodes::VALIDATION_USER_ERROR,
            E_USER_WARNING => ErrorCodes::VALIDATION_USER_WARNING,
            E_USER_NOTICE => ErrorCodes::VALIDATION_USER_NOTICE,
            E_STRICT => ErrorCodes::VALIDATION_STRICT_NOTICE,
            E_RECOVERABLE_ERROR => ErrorCodes::SYSTEM_RECOVERABLE_ERROR,
            E_DEPRECATED => ErrorCodes::SYSTEM_DEPRECATED,
            E_USER_DEPRECATED => ErrorCodes::VALIDATION_USER_DEPRECATED
        ];

        return isset($map[$php_error]) ? $map[$php_error] : ErrorCodes::SYSTEM_UNKNOWN_ERROR;
    }

    // Add error code registration
    public function register_error_codes() {
        $this->error_codes = new ErrorCodes();
        $this->error_reporter = new ErrorReporter();
    }
}
