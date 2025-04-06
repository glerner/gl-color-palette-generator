<?php
namespace GL_Color_Palette_Generator;

class ErrorCodes {
	/**
	 * Color-related errors (1000-1999)
	 */
	const COLOR_INVALID_HEX        = 1001;
	const COLOR_INVALID_RGB        = 1002;
	const COLOR_INVALID_HSL        = 1003;
	const COLOR_NAME_NOT_FOUND     = 1004;
	const COLOR_CONVERSION_FAILED  = 1005;
	const COLOR_SCHEME_INVALID     = 1006;
	const COLOR_VARIATION_FAILED   = 1007;
	const COLOR_HARMONY_INVALID    = 1008;
	const COLOR_PALETTE_INCOMPLETE = 1009;
	const COLOR_IMPORT_FAILED      = 1010;

	/**
	 * Accessibility errors (2000-2999)
	 */
	const ACCESS_CONTRAST_RATIO_LOW = 2001;
	const ACCESS_WCAG_AA_FAILED     = 2002;
	const ACCESS_WCAG_AAA_FAILED    = 2003;
	const ACCESS_COLOR_BLIND_UNSAFE = 2004;
	const ACCESS_TEXT_READABILITY   = 2005;
	const ACCESS_LINK_CONTRAST      = 2006;
	const ACCESS_FOCUS_CONTRAST     = 2007;
	const ACCESS_PATTERN_CONTRAST   = 2008;
	const ACCESS_MOTION_UNSAFE      = 2009;
	const ACCESS_SEMANTIC_COLOR     = 2010;

	/**
	 * API-related errors (3000-3999)
	 */
	const API_CONNECTION_FAILED     = 3001;
	const API_AUTHENTICATION_FAILED = 3002;
	const API_RATE_LIMIT_EXCEEDED   = 3003;
	const API_INVALID_RESPONSE      = 3004;
	const API_TIMEOUT               = 3005;
	const API_QUOTA_EXCEEDED        = 3006;
	const API_INVALID_REQUEST       = 3007;
	const API_SERVICE_UNAVAILABLE   = 3008;
	const API_INVALID_ENDPOINT      = 3009;
	const API_VERSION_MISMATCH      = 3010;

	/**
	 * Validation errors (4000-4999)
	 */
	const VALIDATION_REQUIRED_FIELD    = 4001;
	const VALIDATION_INVALID_FORMAT    = 4002;
	const VALIDATION_OUT_OF_RANGE      = 4003;
	const VALIDATION_DUPLICATE_VALUE   = 4004;
	const VALIDATION_TYPE_MISMATCH     = 4005;
	const VALIDATION_PATTERN_MISMATCH  = 4006;
	const VALIDATION_DEPENDENCY_FAILED = 4007;
	const VALIDATION_UNIQUE_CONSTRAINT = 4008;
	const VALIDATION_LENGTH_EXCEEDED   = 4009;
	const VALIDATION_SCHEMA_INVALID    = 4010;

	/**
	 * Permission errors (5000-5999)
	 */
	const PERM_UNAUTHORIZED_ACCESS   = 5001;
	const PERM_INSUFFICIENT_ROLE     = 5002;
	const PERM_FILE_ACCESS_DENIED    = 5003;
	const PERM_SETTING_ACCESS_DENIED = 5004;
	const PERM_API_ACCESS_DENIED     = 5005;
	const PERM_NONCE_VERIFICATION    = 5006;
	const PERM_INVALID_CAPABILITY    = 5007;
	const PERM_USER_NOT_LOGGED       = 5008;
	const PERM_OPERATION_FORBIDDEN   = 5009;
	const PERM_TOKEN_EXPIRED         = 5010;

	/**
	 * File system errors (6000-6999)
	 */
	const FILE_NOT_FOUND         = 6001;
	const FILE_WRITE_FAILED      = 6002;
	const FILE_READ_FAILED       = 6003;
	const FILE_DELETE_FAILED     = 6004;
	const FILE_PERMISSION_DENIED = 6005;
	const FILE_SIZE_EXCEEDED     = 6006;
	const FILE_TYPE_INVALID      = 6007;
	const FILE_ALREADY_EXISTS    = 6008;
	const FILE_UPLOAD_FAILED     = 6009;
	const FILE_CORRUPT           = 6010;

	/**
	 * Performance warnings (7000-7999)
	 */
	const PERF_SLOW_OPERATION      = 7001;
	const PERF_MEMORY_WARNING      = 7002;
	const PERF_CACHE_MISS          = 7003;
	const PERF_QUERY_TIMEOUT       = 7004;
	const PERF_RESOURCE_LIMIT      = 7005;
	const PERF_BATCH_SIZE_EXCEEDED = 7006;
	const PERF_CONCURRENT_LIMIT    = 7007;
	const PERF_THROTTLE_LIMIT      = 7008;
	const PERF_INDEX_MISSING       = 7009;
	const PERF_OPTIMIZATION_NEEDED = 7010;

	/**
	 * Theme compatibility errors (8000-8999)
	 */
	const THEME_INCOMPATIBLE_VERSION = 8001;
	const THEME_MISSING_FEATURE      = 8002;
	const THEME_STYLE_CONFLICT       = 8003;
	const THEME_DEPENDENCY_MISSING   = 8004;
	const THEME_INVALID_STRUCTURE    = 8005;
	const THEME_BLOCK_INCOMPATIBLE   = 8006;
	const THEME_TEMPLATE_MISSING     = 8007;
	const THEME_ASSET_MISSING        = 8008;
	const THEME_CONFIG_INVALID       = 8009;
	const THEME_UPDATE_REQUIRED      = 8010;

	/**
	 * Get error message template
	 */
	public static function get_message( $code ) {
		$messages = array(
			/**
			 * Color errors
			 */
			self::COLOR_INVALID_HEX         => __( 'Invalid hexadecimal color code: %s', 'color-palette-generator' ),
			self::COLOR_INVALID_RGB         => __( 'Invalid RGB color value: %s', 'color-palette-generator' ),
			self::COLOR_INVALID_HSL         => __( 'Invalid HSL color value: %s', 'color-palette-generator' ),
			self::COLOR_NAME_NOT_FOUND      => __( 'Color name not found in database: %s', 'color-palette-generator' ),
			self::COLOR_CONVERSION_FAILED   => __( 'Color conversion failed: %1$s to %2$s', 'color-palette-generator' ),
			self::COLOR_SCHEME_INVALID      => __( 'Invalid color scheme configuration: %s', 'color-palette-generator' ),
			self::COLOR_VARIATION_FAILED    => __( 'Failed to generate color variation: %s', 'color-palette-generator' ),
			self::COLOR_HARMONY_INVALID     => __( 'Invalid color harmony type: %s', 'color-palette-generator' ),
			self::COLOR_PALETTE_INCOMPLETE  => __( 'Incomplete color palette: missing %s', 'color-palette-generator' ),
			self::COLOR_IMPORT_FAILED       => __( 'Failed to import color palette: %s', 'color-palette-generator' ),

			/**
			 * Accessibility errors
			 */
			self::ACCESS_CONTRAST_RATIO_LOW => __( 'Insufficient contrast ratio %1$s:1 (minimum required: %2$s:1)', 'color-palette-generator' ),
			self::ACCESS_WCAG_AA_FAILED     => __( 'Does not meet WCAG AA standards: %s', 'color-palette-generator' ),
			self::ACCESS_WCAG_AAA_FAILED    => __( 'Does not meet WCAG AAA standards: %s', 'color-palette-generator' ),
			self::ACCESS_COLOR_BLIND_UNSAFE => __( 'Color combination unsafe for %s color blindness', 'color-palette-generator' ),
			self::ACCESS_TEXT_READABILITY   => __( 'Text readability issues detected: %s', 'color-palette-generator' ),
			self::ACCESS_LINK_CONTRAST      => __( 'Insufficient link contrast: %s', 'color-palette-generator' ),
			self::ACCESS_FOCUS_CONTRAST     => __( 'Focus indicator contrast too low: %s', 'color-palette-generator' ),
			self::ACCESS_PATTERN_CONTRAST   => __( 'Pattern contrast issues detected: %s', 'color-palette-generator' ),
			self::ACCESS_MOTION_UNSAFE      => __( 'Motion/animation accessibility issue: %s', 'color-palette-generator' ),
			self::ACCESS_SEMANTIC_COLOR     => __( 'Semantic color usage issue: %s', 'color-palette-generator' ),

			/**
			 * API errors
			 */
			self::API_CONNECTION_FAILED     => __( 'Failed to connect to API: %s', 'color-palette-generator' ),
			self::API_AUTHENTICATION_FAILED => __( 'API authentication failed: %s', 'color-palette-generator' ),
			self::API_RATE_LIMIT_EXCEEDED   => __( 'API rate limit exceeded. Try again in %s seconds', 'color-palette-generator' ),
			self::API_INVALID_RESPONSE      => __( 'Invalid API response: %s', 'color-palette-generator' ),
			self::API_TIMEOUT               => __( 'API request timed out after %s seconds', 'color-palette-generator' ),
			self::API_QUOTA_EXCEEDED        => __( 'API quota exceeded for %s', 'color-palette-generator' ),
			self::API_INVALID_REQUEST       => __( 'Invalid API request: %s', 'color-palette-generator' ),
			self::API_SERVICE_UNAVAILABLE   => __( 'API service unavailable: %s', 'color-palette-generator' ),
			self::API_INVALID_ENDPOINT      => __( 'Invalid API endpoint: %s', 'color-palette-generator' ),
			self::API_VERSION_MISMATCH      => __( 'API version mismatch: expected %1$s, got %2$s', 'color-palette-generator' ),

			/**
			 * ... (additional error messages for other categories)
			 */
		);

		return isset( $messages[ $code ] ) ? $messages[ $code ] : __( 'Unknown error occurred', 'color-palette-generator' );
	}

	/**
	 * Get error severity level
	 */
	public static function get_severity( $code ) {
		$severity_ranges = array(
			'critical' => array( 1001, 2001, 3001, 4001, 5001, 6001 ),
			'error'    => array( 1002, 2002, 3002, 4002, 5002, 6002 ),
			'warning'  => array( 7001, 7002, 7003, 7004, 7005 ),
			'notice'   => array( 8001, 8002, 8003, 8004, 8005 ),
		);

		foreach ( $severity_ranges as $severity => $ranges ) {
			foreach ( $ranges as $range ) {
				if ( $code >= $range && $code < $range + 1000 ) {
					return $severity;
				}
			}
		}

		return 'unknown';
	}

	/**
	 * Get error category
	 */
	public static function get_category( $code ) {
		$categories = array(
			1000 => 'color',
			2000 => 'accessibility',
			3000 => 'api',
			4000 => 'validation',
			5000 => 'permission',
			6000 => 'filesystem',
			7000 => 'performance',
			8000 => 'theme',
		);

		$base = floor( $code / 1000 ) * 1000;
		return isset( $categories[ $base ] ) ? $categories[ $base ] : 'unknown';
	}

	/**
	 * Check if error is user-fixable
	 */
	public static function is_user_fixable( $code ) {
		$user_fixable = array(
			self::COLOR_INVALID_HEX,
			self::COLOR_INVALID_RGB,
			self::COLOR_INVALID_HSL,
			self::COLOR_SCHEME_INVALID,
			self::COLOR_PALETTE_INCOMPLETE,
			self::ACCESS_CONTRAST_RATIO_LOW,
			self::ACCESS_COLOR_BLIND_UNSAFE,
			self::VALIDATION_REQUIRED_FIELD,
			self::VALIDATION_INVALID_FORMAT,
			self::VALIDATION_OUT_OF_RANGE,
		);

		return in_array( $code, $user_fixable );
	}

	/**
	 * Get suggested fix for error
	 */
	public static function get_suggested_fix( $code ) {
		$fixes = array(
			self::COLOR_INVALID_HEX         => __( 'Enter a valid hexadecimal color code (e.g., #FF0000)', 'color-palette-generator' ),
			self::ACCESS_CONTRAST_RATIO_LOW => __( 'Try using a darker/lighter color to improve contrast', 'color-palette-generator' ),
			self::API_RATE_LIMIT_EXCEEDED   => __( 'Wait for the rate limit to reset or upgrade your API plan', 'color-palette-generator' ),
			/**
			 * ... additional fixes
			 */
		);

		return isset( $fixes[ $code ] ) ? $fixes[ $code ] : null;
	}

	/**
	 * Register error codes
	 */
	public function register_error_codes() {
		$this->error_codes = array(
			'CPG001' => array(
				'message'  => __( 'Invalid color format', 'color-palette-generator' ),
				'severity' => 'error',
				'solution' => __( 'Please provide a valid hex color code', 'color-palette-generator' ),
			),
			'CPG002' => array(
				'message'  => __( 'API request failed', 'color-palette-generator' ),
				'severity' => 'error',
				'solution' => __( 'Check your API credentials and try again', 'color-palette-generator' ),
			),
			'CPG003' => array(
				'message'  => __( 'Cache operation failed', 'color-palette-generator' ),
				'severity' => 'warning',
				'solution' => __( 'Clear the cache and try again', 'color-palette-generator' ),
			),
			/**
			 * ... more error codes
			 */
		);
	}

	/**
	 * Get error details
	 */
	public function get_error_details( $code ) {
		if ( ! isset( $this->error_codes[ $code ] ) ) {
			return array(
				'message'  => __( 'Unknown error code', 'color-palette-generator' ),
				'severity' => 'error',
				'solution' => __( 'Contact support for assistance', 'color-palette-generator' ),
			);
		}

		return $this->error_codes[ $code ];
	}

	/**
	 * Generate error response
	 */
	public function generate_error_response( $code, $context = array() ) {
		$error_details = $this->get_error_details( $code );

		return array(
			'code'      => $code,
			'message'   => $error_details['message'],
			'severity'  => $error_details['severity'],
			'solution'  => $error_details['solution'],
			'context'   => $context,
			'timestamp' => current_time( 'mysql' ),
		);
	}
}
