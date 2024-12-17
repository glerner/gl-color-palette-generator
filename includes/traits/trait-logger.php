<?php declare(strict_types=1);

namespace GL_Color_Palette_Generator\Traits;

/**
 * Logger Trait
 *
 * Provides common logging functionality.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Traits
 * @since 1.0.0
 */
trait Logger {
    /**
     * Log messages
     *
     * @var array
     */
    protected $log_messages = [];

    /**
     * Log a message
     *
     * @param string $message Message to log
     * @param string $level Log level (debug, info, warning, error)
     * @return void
     */
    protected function log(string $message, string $level = 'info'): void {
        $this->log_messages[] = [
            'message' => $message,
            'level' => $level,
            'timestamp' => time(),
        ];
    }

    /**
     * Get all log messages
     *
     * @return array Log messages
     */
    public function get_log_messages(): array {
        return $this->log_messages;
    }

    /**
     * Clear log messages
     *
     * @return void
     */
    protected function clear_log(): void {
        $this->log_messages = [];
    }

    /**
     * Get log messages by level
     *
     * @param string $level Log level
     * @return array Log messages for the specified level
     */
    public function get_log_messages_by_level(string $level): array {
        return array_filter($this->log_messages, function($log) use ($level) {
            return $log['level'] === $level;
        });
    }
}
