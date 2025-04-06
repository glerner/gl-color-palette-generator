<?php
/**
 * Plugin Lifecycle Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface for plugin lifecycle management
 */
interface Plugin_Lifecycle {
	/**
	 * Activate the plugin
	 *
	 * Called when the plugin is activated in WordPress.
	 * Should handle database table creation, default option setup, etc.
	 */
	public function activate(): void;

	/**
	 * Deactivate the plugin
	 *
	 * Called when the plugin is deactivated in WordPress.
	 * Should clean up temporary data, clear scheduled events, etc.
	 */
	public function deactivate(): void;

	/**
	 * Uninstall the plugin
	 *
	 * Called when the plugin is uninstalled from WordPress.
	 * Should remove all plugin data including database tables and options.
	 */
	public function uninstall(): void;
}
