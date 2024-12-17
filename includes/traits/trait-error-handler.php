<?php declare(strict_types=1);

namespace GL_Color_Palette_Generator\Traits;

/**
 * Error Handler Trait
 *
 * Provides common error handling functionality.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Traits
 * @since 1.0.0
 */
trait Error_Handler {
    /**
     * Last error message
     *
     * @var string
     */
    protected $last_error = '';

    /**
     * Set the last error message
     *
     * @param string $error Error message
     * @return void
     */
    protected function set_error(string $error): void {
        $this->last_error = $error;
    }

    /**
     * Get the last error message
     *
     * @return string Last error message
     */
    public function get_last_error(): string {
        return $this->last_error;
    }

    /**
     * Check if there was an error
     *
     * @return bool True if there was an error
     */
    public function has_error(): bool {
        return !empty($this->last_error);
    }

    /**
     * Clear the last error
     *
     * @return void
     */
    protected function clear_error(): void {
        $this->last_error = '';
    }
}
