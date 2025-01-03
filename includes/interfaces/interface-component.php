<?php
/**
 * Component Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface for plugin components
 * 
 * All plugin components should implement this interface to ensure
 * they have the necessary initialization and cleanup methods.
 */
interface Component {
    /**
     * Initialize the component
     *
     * Called when the component is first registered with the plugin.
     * Should set up any necessary hooks, filters, or initial state.
     *
     * @return bool True if initialization was successful
     */
    public function init(): bool;

    /**
     * Clean up the component
     *
     * Called when the component is being removed or the plugin is being deactivated.
     * Should remove any hooks, temporary data, or other cleanup tasks.
     */
    public function cleanup(): void;

    /**
     * Get component name
     *
     * @return string The unique identifier for this component
     */
    public function get_name(): string;

    /**
     * Get component version
     *
     * @return string The version of this component
     */
    public function get_version(): string;
}
