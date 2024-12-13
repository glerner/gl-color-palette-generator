<?php
/**
 * Rate Limiter Class
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
     * @param Logger       $logger       Logger instance
     * @param Error_Handler $error_handler Error handler instance
     * @param int         $window       Rate limit window in seconds
     * @param int         $max_requests Maximum requests per window
     */
    public function __construct(
        Logger $logger,
        Error_Handler $error_handler,
        int $window = self::DEFAULT_WINDOW,
        int $max_requests = self::DEFAULT_MAX_REQUESTS
    ) {
        $this->logger = $logger;
        $this->error_handler = $error_handler;
        $this->window = $window;
        $this->max_requests = $max_requests;
    }

    /**
     * Check if request is allowed
     *
     * @param string $identifier Unique identifier for the rate limit (e.g., IP address or user ID)
     * @return bool Whether request is allowed
     */
    public function is_allowed(string $identifier): bool {
        $key = $this->get_cache_key($identifier);
        $current_time = time();
        
        $bucket = $this->get_bucket($key);
        if ($bucket === false) {
            $bucket = [
                'tokens' => $this->max_requests - 1,
                'last_update' => $current_time
            ];
            $this->save_bucket($key, $bucket);
            return true;
        }

        $time_passed = $current_time - $bucket['last_update'];
        $tokens_to_add = floor($time_passed * ($this->max_requests / $this->window));
        
        $bucket['tokens'] = min(
            $this->max_requests,
            $bucket['tokens'] + $tokens_to_add
        );
        
        if ($bucket['tokens'] < 1) {
            $this->logger->warning('Rate limit exceeded', [
                'identifier' => $identifier,
                'window' => $this->window,
                'max_requests' => $this->max_requests
            ]);
            return false;
        }

        $bucket['tokens']--;
        $bucket['last_update'] = $current_time;
        $this->save_bucket($key, $bucket);

        return true;
    }

    /**
     * Get remaining tokens
     *
     * @param string $identifier Unique identifier
     * @return int Number of remaining tokens
     */
    public function get_remaining_tokens(string $identifier): int {
        $bucket = $this->get_bucket($this->get_cache_key($identifier));
        if ($bucket === false) {
            return $this->max_requests;
        }

        $current_time = time();
        $time_passed = $current_time - $bucket['last_update'];
        $tokens_to_add = floor($time_passed * ($this->max_requests / $this->window));
        
        return min(
            $this->max_requests,
            $bucket['tokens'] + $tokens_to_add
        );
    }

    /**
     * Reset rate limit for identifier
     *
     * @param string $identifier Unique identifier
     * @return bool Success status
     */
    public function reset(string $identifier): bool {
        return wp_cache_delete($this->get_cache_key($identifier));
    }

    /**
     * Get cache key for identifier
     *
     * @param string $identifier Unique identifier
     * @return string Cache key
     */
    private function get_cache_key(string $identifier): string {
        return self::CACHE_PREFIX . md5($identifier);
    }

    /**
     * Get bucket from cache
     *
     * @param string $key Cache key
     * @return array|false Bucket data or false if not found
     */
    private function get_bucket(string $key) {
        return wp_cache_get($key);
    }

    /**
     * Save bucket to cache
     *
     * @param string $key Cache key
     * @param array $bucket Bucket data
     * @return bool Success status
     */
    private function save_bucket(string $key, array $bucket): bool {
        return wp_cache_set($key, $bucket, '', $this->window);
    }

    /**
     * Get rate limit headers
     *
     * @param string $identifier Unique identifier
     * @return array Rate limit headers
     */
    public function get_headers(string $identifier): array {
        $remaining = $this->get_remaining_tokens($identifier);
        
        return [
            'X-RateLimit-Limit' => $this->max_requests,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => time() + $this->window
        ];
    }
}
