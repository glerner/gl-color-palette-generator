<?php
/**
 * WordPress Integration Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface for WordPress integration
 */
interface WordPress_Integration {
    /**
     * Register WordPress hooks
     *
     * Should register all necessary action and filter hooks
     * for the plugin to function.
     */
    public function register_hooks(): void;

    /**
     * Register REST API routes
     *
     * Should register all REST API routes needed by
     * the plugin's components.
     */
    public function register_rest_routes(): void;
}
