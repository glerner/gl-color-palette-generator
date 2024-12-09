<?php
declare(strict_types=1);

/**
 * Palette Generation Exception Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Exceptions
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Exceptions;

/**
 * Exception thrown when palette generation fails
 */
class PaletteGenerationException extends \Exception {
    /**
     * Constructor
     *
     * @param string          $message  Error message
     * @param int            $code     Error code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * String representation of the exception
     *
     * @return string
     */
    public function __toString(): string {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    /**
     * Get error details for logging
     *
     * @return array{
     *     message: string,
     *     code: int,
     *     file: string,
     *     line: int,
     *     trace: string
     * }
     */
    public function getErrorDetails(): array {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString()
        ];
    }
}
