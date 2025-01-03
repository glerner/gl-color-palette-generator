<?php
/**
 * Component Registry Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface for component management
 */
interface Component_Registry {
    /**
     * Register a component
     *
     * @param string $name Component identifier
     * @param object $instance Component instance
     */
    public function register_component(string $name, object $instance): void;

    /**
     * Get a registered component
     *
     * @param string $name Component identifier
     * @return object|null Component instance or null if not found
     */
    public function get_component(string $name): ?object;

    /**
     * Check if a component is registered
     *
     * @param string $name Component identifier
     * @return bool True if component exists
     */
    public function has_component(string $name): bool;
}
