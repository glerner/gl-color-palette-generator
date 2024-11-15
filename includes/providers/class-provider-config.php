<?php
/**
 * Provider Configuration Base Class
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette\Providers;

/**
 * Abstract base class for provider configurations
 */
abstract class Provider_Config {
    /**
     * Get provider name
     *
     * @return string
     */
    abstract public function get_name(): string;

    /**
     * Get provider models
     *
     * @return array
     */
    abstract public function get_models(): array;

    /**
     * Get default model
     *
     * @return string
     */
    abstract public function get_default_model(): string;

    /**
     * Validate API key
     *
     * @param string $api_key API key to validate
     * @return bool Whether the API key is valid
     */
    abstract public function validate_api_key(string $api_key): bool;

    /**
     * Get API endpoint
     *
     * @return string
     */
    abstract public function get_api_endpoint(): string;
} 
