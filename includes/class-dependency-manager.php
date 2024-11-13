<?php
/**
 * Dependency Manager Class
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

/**
 * Class DependencyManager
 *
 * Handles plugin dependencies and requirements checking.
 *
 * @since 1.0.0
 */
class DependencyManager {
    /**
     * Minimum PHP version required.
     *
     * @var string
     */
    private const MIN_PHP_VERSION = '7.4.0';

    /**
     * Minimum WordPress version required.
     *
     * @var string
     */
    private const MIN_WP_VERSION = '5.8';

    /**
     * Required PHP extensions.
     *
     * @var array
     */
    private const REQUIRED_PHP_EXTENSIONS = [
        'curl',
        'json',
        'mbstring'
    ];

    /**
     * Check if all plugin requirements are met.
     *
     * @since 1.0.0
     * @return bool|WP_Error True if requirements are met, WP_Error otherwise
     */
    public function check_requirements() {
        $php_check = $this->check_php_requirements();
        if (is_wp_error($php_check)) {
            return $php_check;
        }

        $wp_check = $this->check_wp_requirements();
        if (is_wp_error($wp_check)) {
            return $wp_check;
        }

        $extensions_check = $this->check_php_extensions();
        if (is_wp_error($extensions_check)) {
            return $extensions_check;
        }

        return true;
    }

    /**
     * Check PHP version requirements.
     *
     * @since 1.0.0
     * @return bool|WP_Error True if requirements are met, WP_Error otherwise
     */
    private function check_php_requirements() {
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            return new \WP_Error(
                'php_version_error',
                sprintf(
                    __('GL Color Palette Generator requires PHP version %s or higher. Current version is %s', 'gl-color-palette-generator'),
                    self::MIN_PHP_VERSION,
                    PHP_VERSION
                )
            );
        }
        return true;
    }

    /**
     * Check WordPress version requirements.
     *
     * @since 1.0.0
     * @return bool|WP_Error True if requirements are met, WP_Error otherwise
     */
    private function check_wp_requirements() {
        global $wp_version;

        if (version_compare($wp_version, self::MIN_WP_VERSION, '<')) {
            return new \WP_Error(
                'wp_version_error',
                sprintf(
                    __('GL Color Palette Generator requires WordPress version %s or higher. Current version is %s', 'gl-color-palette-generator'),
                    self::MIN_WP_VERSION,
                    $wp_version
                )
            );
        }
        return true;
    }

    /**
     * Check required PHP extensions.
     *
     * @since 1.0.0
     * @return bool|WP_Error True if requirements are met, WP_Error otherwise
     */
    private function check_php_extensions() {
        $missing_extensions = [];

        foreach (self::REQUIRED_PHP_EXTENSIONS as $extension) {
            if (!extension_loaded($extension)) {
                $missing_extensions[] = $extension;
            }
        }

        if (!empty($missing_extensions)) {
            return new \WP_Error(
                'missing_php_extensions',
                sprintf(
                    __('GL Color Palette Generator requires the following PHP extensions: %s', 'gl-color-palette-generator'),
                    implode(', ', $missing_extensions)
                )
            );
        }

        return true;
    }

    /**
     * Display admin notices for requirement errors.
     *
     * @since 1.0.0
     * @param WP_Error $error The error to display
     * @return void
     */
    public function display_requirement_errors(\WP_Error $error): void {
        $message = $error->get_error_message();

        echo '<div class="notice notice-error">';
        echo '<p>' . esc_html($message) . '</p>';
        echo '</div>';
    }
} 
