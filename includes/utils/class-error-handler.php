<?php
/**
 * Error Handler Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Utils;

/**
 * Class Error_Handler
 */
class Error_Handler {
	/**
	 * Array of error messages
	 *
	 * @var array
	 */
	private array $errors = array();

	/**
	 * Initialize error handling
	 */
	public function init() {
		set_error_handler( array( $this, 'handle_error' ) );
		set_exception_handler( array( $this, 'handle_exception' ) );
		register_shutdown_function( array( $this, 'handle_shutdown' ) );
	}

	/**
	 * Handle PHP errors
	 *
	 * @param int    $errno   Error number.
	 * @param string $errstr  Error message.
	 * @param string $errfile File where error occurred.
	 * @param int    $errline Line number where error occurred.
	 * @return bool
	 */
	public function handle_error( int $errno, string $errstr, string $errfile, int $errline ): bool {
		if ( ! (bool) ( error_reporting() & $errno ) ) {
			return false;
		}

		$error_message = sprintf(
			'Error [%s]: %s in %s on line %d',
			$this->get_error_type( $errno ),
			$errstr,
			$errfile,
			$errline
		);

		$this->log_error( $error_message );

		if ( $errno == E_USER_ERROR ) {
			exit( 1 );
		}

		return true;
	}

	/**
	 * Handle uncaught exceptions
	 *
	 * @param \Throwable $exception The thrown exception.
	 */
	public function handle_exception( $exception ) {
		$error_message = sprintf(
			'Exception: %s in %s on line %d',
			$exception->getMessage(),
			$exception->getFile(),
			$exception->getLine()
		);

		$this->log_error( $error_message );
	}

	/**
	 * Handle fatal errors
	 */
	public function handle_shutdown() {
		$error = error_get_last();

		if ( $error !== null && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ), true ) ) {
			$error_message = sprintf(
				'Fatal Error [%s]: %s in %s on line %d',
				$this->get_error_type( $error['type'] ),
				$error['message'],
				$error['file'],
				$error['line']
			);

			$this->log_error( $error_message );
		}
	}

	/**
	 * Get error type string
	 *
	 * @param int $type Error type number.
	 * @return string
	 */
	public function get_error_type( $type ) {
		switch ( $type ) {
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
	 * Log error message
	 *
	 * @param string $message Error message to log.
	 */
	public function log_error( $message ) {
		$this->errors[] = $message;
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( '[GL Color Palette Generator] %s', $message ) );
		}
	}

	/**
	 * Get all logged errors
	 *
	 * @return array Array of error messages
	 */
	public function get_errors(): array {
		return $this->errors;
	}

	/**
	 * Clear all logged errors
	 */
	public function clear_errors(): void {
		$this->errors = array();
	}
}
