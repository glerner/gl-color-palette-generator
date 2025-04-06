<?php
/**
 * Plugin Information Class
 *
 * @package    GL_Color_Palette_Generator
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */

namespace GL_Color_Palette_Generator;

/**
 * Static class to hold plugin information
 *
 * @package    GL_Color_Palette_Generator
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */
class PluginInfo {
	/**
	 * Plugin version
	 */
	const VERSION = '1.0.0';

	/**
	 * Plugin minimum PHP version
	 */
	const MIN_PHP_VERSION = '8.0';

	/**
	 * Plugin minimum WordPress version
	 */
	const MIN_WP_VERSION = '6.2';

	/**
	 * Plugin author
	 */
	const AUTHOR = 'George Lerner';

	/**
	 * Plugin author URI
	 */
	const AUTHOR_URI = 'https://website-tech.glerner.com/';

	/**
	 * Plugin URI
	 */
	const PLUGIN_URI = 'https://github.com/GeorgeLerner/gl-color-palette-generator';

	/**
	 * Get plugin metadata
	 *
	 * @return array Plugin metadata
	 */
	public static function get_metadata(): array {
		return array(
			'version'    => self::VERSION,
			'min_php'    => self::MIN_PHP_VERSION,
			'min_wp'     => self::MIN_WP_VERSION,
			'author'     => self::AUTHOR,
			'author_uri' => self::AUTHOR_URI,
			'plugin_uri' => self::PLUGIN_URI,
		);
	}
}
