<?php
/**
 * Rate Limiter Class
 *
 * Handles API rate limiting using a token bucket algorithm
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Core
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;

/**
 * Class Rate_Limiter
 *
 * Handles API rate limiting using a token bucket algorithm
 *
 * @since 1.0.0
 */
class Rate_Limiter {
	/**
	 * Cache key prefix for rate limiting
	 *
	 * @var string
	 */
	private const CACHE_PREFIX = 'gl_cpg_rate_limit_';

	/**
	 * Default rate limit window in seconds
	 *
	 * @var int
	 */
	private const DEFAULT_WINDOW = 3600; // 1 hour

	/**
	 * Default maximum requests per window
	 *
	 * @var int
	 */
	private const DEFAULT_MAX_REQUESTS = 1000;

	/**
	 * Logger instance
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Error handler instance
	 *
	 * @var Error_Handler
	 */
	protected $error_handler;

	/**
	 * Rate limit window in seconds
	 *
	 * @var int
	 */
	private $window;

	/**
	 * Maximum requests per window
	 *
	 * @var int
	 */
	private $max_requests;

	/**
	 * Constructor
	 *
	 * @param Logger        $logger       Logger instance
	 * @param Error_Handler $error_handler Error handler instance
	 * @param int           $window       Rate limit window in seconds
	 * @param int           $max_requests Maximum requests per window
	 */
	public function __construct(
		Logger $logger,
		Error_Handler $error_handler,
		int $window = self::DEFAULT_WINDOW,
		int $max_requests = self::DEFAULT_MAX_REQUESTS
	) {
		$this->logger        = $logger;
		$this->error_handler = $error_handler;
		$this->window        = $window;
		$this->max_requests  = $max_requests;
	}

	/**
	 * Check if request is allowed
	 *
	 * @param string $identifier Unique identifier for the rate limit (e.g., IP address or user ID)
	 * @return bool Whether request is allowed
	 */
	public function is_allowed( string $identifier ): bool {
		return $this->get_remaining_tokens( $identifier ) > 0;
	}

	/**
	 * Get remaining tokens
	 *
	 * @param string $identifier Unique identifier
	 * @return int Number of remaining tokens
	 */
	public function get_remaining_tokens( string $identifier ): int {
		$used = $this->get_request_count( $identifier );
		return max( 0, $this->max_requests - $used );
	}

	/**
	 * Get current request count
	 *
	 * @param string $identifier Rate limit identifier
	 * @return int Current request count
	 */
	public function get_request_count( string $identifier ): int {
		$key    = $this->get_cache_key( $identifier );
		$bucket = $this->get_bucket( $key );
		if ( $bucket === false ) {
			return 0;
		}

		return $this->max_requests - $bucket['tokens'];
	}

	/**
	 * Increment request count
	 *
	 * @param string $identifier Rate limit identifier
	 * @return bool True on success
	 */
	private function increment_request_count( string $identifier ): bool {
		$key    = $this->get_cache_key( $identifier );
		$bucket = $this->get_bucket( $key );
		if ( $bucket === false ) {
			$bucket = array(
				'tokens'      => $this->max_requests - 1,
				'last_update' => time(),
			);
			$this->save_bucket( $key, $bucket );
			return true;
		}

		$time_passed   = time() - $bucket['last_update'];
		$tokens_to_add = floor( $time_passed * ( $this->max_requests / $this->window ) );

		$bucket['tokens'] = min(
			$this->max_requests,
			$bucket['tokens'] + $tokens_to_add
		);

		if ( $bucket['tokens'] < 1 ) {
			$this->logger->warning(
				'Rate limit exceeded',
				array(
					'identifier'   => $identifier,
					'window'       => $this->window,
					'max_requests' => $this->max_requests,
				)
			);
			return false;
		}

		--$bucket['tokens'];
		$bucket['last_update'] = time();
		$this->save_bucket( $key, $bucket );

		return true;
	}

	/**
	 * Check if request is within rate limit
	 *
	 * @param string $identifier Rate limit identifier
	 * @return bool|WP_Error True if within limit, WP_Error if exceeded
	 */
	public function check_rate_limit( string $identifier ): bool|WP_Error {
		if ( ! $this->is_allowed( $identifier ) ) {
			return new WP_Error(
				'rate_limit_exceeded',
				'Rate limit exceeded. Please try again later.',
				array( 'status' => 429 )
			);
		}

		$this->increment_request_count( $identifier );
		return true;
	}

	/**
	 * Get cache key for identifier
	 *
	 * @param string $identifier Unique identifier
	 * @return string Cache key
	 */
	private function get_cache_key( string $identifier ): string {
		return self::CACHE_PREFIX . md5( $identifier );
	}

	/**
	 * Get bucket from cache
	 *
	 * @param string $key Cache key
	 * @return array|false Bucket data or false if not found
	 */
	private function get_bucket( string $key ) {
		return wp_cache_get( $key );
	}

	/**
	 * Save bucket to cache
	 *
	 * @param string $key Cache key
	 * @param array  $bucket Bucket data
	 * @return bool Success status
	 */
	private function save_bucket( string $key, array $bucket ): bool {
		return wp_cache_set( $key, $bucket, '', $this->window );
	}

	/**
	 * Get rate limit headers
	 *
	 * @param string $identifier Unique identifier
	 * @return array Rate limit headers
	 */
	public function get_headers( string $identifier ): array {
		return array(
			'X-RateLimit-Limit'     => (string) $this->max_requests,
			'X-RateLimit-Remaining' => (string) $this->get_remaining_tokens( $identifier ),
			'X-RateLimit-Reset'     => (string) ( time() + $this->window ),
		);
	}

	/**
	 * Get rate limit window
	 *
	 * @return int Time window in seconds
	 */
	public function get_window(): int {
		return $this->window;
	}

	/**
	 * Get maximum requests per window
	 *
	 * @return int Maximum requests
	 */
	public function get_max_requests(): int {
		return $this->max_requests;
	}
}
