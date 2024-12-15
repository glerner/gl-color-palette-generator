<?php
/**
 * Provider Configuration Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Types
 */

namespace GL_Color_Palette_Generator\Types;

/**
 * Provider Configuration class
 */
class Provider_Config {
    /** @var array */
    private array $config;

    /**
     * Constructor
     *
     * @param array $config Configuration array
     */
    public function __construct(array $config = []) {
        $this->config = $config;
    }

    /**
     * Get API key
     *
     * @return string|null API key
     */
    public function get_api_key(): ?string {
        return $this->config['api_key'] ?? null;
    }

    /**
     * Get endpoint
     *
     * @return string|null Endpoint URL
     */
    public function get_endpoint(): ?string {
        return $this->config['endpoint'] ?? null;
    }

    /**
     * Get deployment ID
     *
     * @return string|null Deployment ID
     */
    public function get_deployment(): ?string {
        return $this->config['deployment'] ?? null;
    }

    /**
     * Get model
     *
     * @return string|null Model name
     */
    public function get_model(): ?string {
        return $this->config['model'] ?? null;
    }

    /**
     * Get organization ID
     *
     * @return string|null Organization ID
     */
    public function get_organization(): ?string {
        return $this->config['organization'] ?? null;
    }

    /**
     * Get base URL
     *
     * @return string|null Base URL
     */
    public function get_base_url(): ?string {
        return $this->config['base_url'] ?? null;
    }

    /**
     * Get timeout
     *
     * @return int|null Timeout in seconds
     */
    public function get_timeout(): ?int {
        return $this->config['timeout'] ?? null;
    }

    /**
     * Get max retries
     *
     * @return int|null Maximum number of retries
     */
    public function get_max_retries(): ?int {
        return $this->config['max_retries'] ?? null;
    }

    /**
     * Get raw config array
     *
     * @return array Raw configuration array
     */
    public function get_raw_config(): array {
        return $this->config;
    }
}
