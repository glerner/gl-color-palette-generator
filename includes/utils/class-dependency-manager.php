<?php
/**
 * Dependency Manager Class
 *
 * @package    GLColorPalette
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */

namespace GL_Color_Palette_Generator;

/**
 * Manages plugin dependencies and requirements
 *
 * @package    GLColorPalette
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */
class DependencyManager {
    /**
     * @var DependencyManager|null Singleton instance
     */
    private static $instance = null;

    /**
     * Minimum PHP version required
     *
     * @since 1.0.0
     * @var string
     */
    const MIN_PHP_VERSION = '8.0';

    /**
     * Minimum WordPress version required
     *
     * @since 1.0.0
     * @var string
     */
    const MIN_WP_VERSION = '6.2';

    /**
     * Get singleton instance
     */
    public static function get_instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check if system meets all requirements
     *
     * @return bool|WP_Error True if requirements met, WP_Error otherwise
     */
    public function check_system_requirements() {
        if (!$this->check_php_version()) {
            return new \WP_Error(
                'invalid_php_version',
                sprintf(
                    __('GL Color Palette Generator requires PHP version %s or higher.', 'gl-color-palette-generator'),
                    self::MIN_PHP_VERSION
                )
            );
        }

        if (!$this->check_wp_version()) {
            return new \WP_Error(
                'invalid_wp_version',
                sprintf(
                    __('GL Color Palette Generator requires WordPress version %s or higher.', 'gl-color-palette-generator'),
                    self::MIN_WP_VERSION
                )
            );
        }

        if (!$this->check_required_extensions()) {
            return new \WP_Error(
                'missing_extensions',
                __('GL Color Palette Generator requires the GD or Imagick PHP extension.', 'gl-color-palette-generator')
            );
        }

        return true;
    }

    /**
     * Check PHP version requirement
     */
    private function check_php_version(): bool {
        return version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '>=');
    }

    /**
     * Check WordPress version requirement
     */
    private function check_wp_version(): bool {
        global $wp_version;
        return version_compare($wp_version, self::MIN_WP_VERSION, '>=');
    }

    /**
     * Check required PHP extensions
     */
    private function check_required_extensions(): bool {
        return extension_loaded('gd') || extension_loaded('imagick');
    }
}
