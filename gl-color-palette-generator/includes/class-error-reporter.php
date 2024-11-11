<?php

class ErrorReporter {
    private $errors = [];
    private $warnings = [];
    private $notices = [];
    private $debug_logs = [];
    private $error_codes = [
        'invalid_color' => 100,
        'contrast_ratio' => 200,
        'api_error' => 300,
        'validation_error' => 400,
        'permission_error' => 500,
        'file_system_error' => 600,
        'performance_warning' => 700,
        'accessibility_warning' => 800
    ];

    /**
     * Add an error with context
     */
    public function add_error($message, $code, $context = []) {
        $error = $this->format_message('error', $message, $code, $context);
        $this->errors[] = $error;
        $this->log_error($error);

        return $this;
    }

    /**
     * Add a warning with context
     */
    public function add_warning($message, $code, $context = []) {
        $warning = $this->format_message('warning', $message, $code, $context);
        $this->warnings[] = $warning;
        $this->log_warning($warning);

        return $this;
    }

    /**
     * Add a notice with context
     */
    public function add_notice($message, $context = []) {
        $notice = $this->format_message('notice', $message, 0, $context);
        $this->notices[] = $notice;

        return $this;
    }

    /**
     * Add debug information
     */
    public function add_debug($message, $data = []) {
        if (WP_DEBUG) {
            $this->debug_logs[] = [
                'message' => $message,
                'data' => $data,
                'timestamp' => current_time('mysql'),
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
            ];
        }

        return $this;
    }

    /**
     * Format error message with context
     */
    private function format_message($type, $message, $code, $context) {
        return [
            'type' => $type,
            'message' => $message,
            'code' => $code,
            'context' => $context,
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id()
        ];
    }

    /**
     * Get all errors
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Get all warnings
     */
    public function get_warnings() {
        return $this->warnings;
    }

    /**
     * Get all notices
     */
    public function get_notices() {
        return $this->notices;
    }

    /**
     * Get debug logs
     */
    public function get_debug_logs() {
        return $this->debug_logs;
    }

    /**
     * Check if there are any errors
     */
    public function has_errors() {
        return !empty($this->errors);
    }

    /**
     * Get formatted messages for display
     */
    public function get_formatted_messages($types = ['error', 'warning', 'notice']) {
        $messages = [];

        if (in_array('error', $types) && !empty($this->errors)) {
            foreach ($this->errors as $error) {
                $messages[] = $this->format_display_message($error);
            }
        }

        if (in_array('warning', $types) && !empty($this->warnings)) {
            foreach ($this->warnings as $warning) {
                $messages[] = $this->format_display_message($warning);
            }
        }

        if (in_array('notice', $types) && !empty($this->notices)) {
            foreach ($this->notices as $notice) {
                $messages[] = $this->format_display_message($notice);
            }
        }

        return $messages;
    }

    /**
     * Format message for display
     */
    private function format_display_message($message) {
        $context_html = '';
        if (!empty($message['context'])) {
            $context_html = '<ul class="error-context">';
            foreach ($message['context'] as $key => $value) {
                $context_html .= sprintf(
                    '<li><strong>%s:</strong> %s</li>',
                    esc_html($key),
                    esc_html($this->format_context_value($value))
                );
            }
            $context_html .= '</ul>';
        }

        return sprintf(
            '<div class="notice notice-%1$s is-dismissible">
                <p><strong>%2$s:</strong> %3$s</p>
                %4$s
                <p class="error-meta">
                    <span class="error-code">Code: %5$s</span>
                    <span class="error-time">Time: %6$s</span>
                </p>
            </div>',
            esc_attr($message['type']),
            esc_html(ucfirst($message['type'])),
            esc_html($message['message']),
            $context_html,
            esc_html($message['code']),
            esc_html($message['timestamp'])
        );
    }

    /**
     * Format context value for display
     */
    private function format_context_value($value) {
        if (is_array($value)) {
            return json_encode($value);
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return (string) $value;
    }

    /**
     * Log error to WordPress error log
     */
    private function log_error($error) {
        $log_message = sprintf(
            '[Color Palette Generator] Error %d: %s | Context: %s',
            $error['code'],
            $error['message'],
            json_encode($error['context'])
        );

        error_log($log_message);

        // Log to custom error log if enabled
        $this->log_to_file('error', $error);
    }

    /**
     * Log warning to WordPress error log
     */
    private function log_warning($warning) {
        if (WP_DEBUG) {
            $log_message = sprintf(
                '[Color Palette Generator] Warning %d: %s | Context: %s',
                $warning['code'],
                $warning['message'],
                json_encode($warning['context'])
            );

            error_log($log_message);

            // Log to custom warning log if enabled
            $this->log_to_file('warning', $warning);
        }
    }

    /**
     * Log message to custom file
     */
    private function log_to_file($type, $message) {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/color-palette-logs';

        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);

            // Protect the logs directory
            $htaccess = $log_dir . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, 'Deny from all');
            }
        }

        $log_file = $log_dir . '/' . $type . '-' . date('Y-m') . '.log';

        $log_entry = sprintf(
            "[%s] [%s] [User: %d] %s | Context: %s\n",
            current_time('mysql'),
            $message['code'],
            $message['user_id'],
            $message['message'],
            json_encode($message['context'])
        );

        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }

    /**
     * Clear all messages
     */
    public function clear() {
        $this->errors = [];
        $this->warnings = [];
        $this->notices = [];
        $this->debug_logs = [];

        return $this;
    }

    /**
     * Get error code description
     */
    public function get_error_description($code) {
        $descriptions = [
            100 => __('Invalid color format or value', 'color-palette-generator'),
            200 => __('Contrast ratio does not meet accessibility standards', 'color-palette-generator'),
            300 => __('API communication error', 'color-palette-generator'),
            400 => __('Input validation error', 'color-palette-generator'),
            500 => __('Permission or capability error', 'color-palette-generator'),
            600 => __('File system operation error', 'color-palette-generator'),
            700 => __('Performance-related warning', 'color-palette-generator'),
            800 => __('Accessibility compliance warning', 'color-palette-generator')
        ];

        return isset($descriptions[$code]) ? $descriptions[$code] : __('Unknown error', 'color-palette-generator');
    }
} 
