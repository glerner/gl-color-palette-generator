<?php
/**
 * Abstract Component Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Core
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Core;

use GL_Color_Palette_Generator\Interfaces\Component;

/**
 * Abstract base class for plugin components
 * 
 * Provides common functionality for all plugin components
 */
abstract class Abstract_Component implements Component {
    /**
     * Component version
     *
     * @var string
     */
    protected string $version = '1.0.0';

    /**
     * Component name
     *
     * @var string
     */
    protected string $name;

    /**
     * Initialize the component
     *
     * @return bool True if initialization was successful
     */
    public function init(): bool {
        return true;
    }

    /**
     * Clean up the component
     */
    public function cleanup(): void {
        // Default implementation does nothing
    }

    /**
     * Get component name
     *
     * @return string The unique identifier for this component
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * Get component version
     *
     * @return string The version of this component
     */
    public function get_version(): string {
        return $this->version;
    }

    /**
     * Set component name
     *
     * @param string $name The unique identifier for this component
     */
    protected function set_name(string $name): void {
        $this->name = $name;
    }

    /**
     * Set component version
     *
     * @param string $version The version of this component
     */
    protected function set_version(string $version): void {
        $this->version = $version;
    }
}
