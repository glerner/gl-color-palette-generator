<?php declare(strict_types=1);
/**
 * Compliance Types Class
 *
 * Defines type constants and structures for accessibility compliance.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Types
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Types;

/**
 * Compliance Types Class
 *
 * @since 1.0.0
 */
class Compliance_Types {
    /**
     * WCAG Compliance Levels
     */
    public const WCAG_LEVEL_A = 'A';
    public const WCAG_LEVEL_AA = 'AA';
    public const WCAG_LEVEL_AAA = 'AAA';

    /**
     * Compliance Status Types
     */
    public const STATUS_PASS = 'pass';
    public const STATUS_FAIL = 'fail';
    public const STATUS_WARNING = 'warning';

    /**
     * Color Blindness Types
     */
    public const COLOR_BLINDNESS_DEUTERANOPIA = 'deuteranopia';
    public const COLOR_BLINDNESS_PROTANOPIA = 'protanopia';
    public const COLOR_BLINDNESS_TRITANOPIA = 'tritanopia';

    /**
     * Text Size Categories
     */
    public const TEXT_SIZE_NORMAL = 'normal_text';
    public const TEXT_SIZE_LARGE = 'large_text';

    /**
     * Get WCAG compliance level requirements
     *
     * @return array
     */
    public static function get_wcag_requirements(): array {
        return [
            self::WCAG_LEVEL_A => [
                'contrast_ratio' => 3.0,
            ],
            self::WCAG_LEVEL_AA => [
                'contrast_ratio' => [
                    self::TEXT_SIZE_NORMAL => 4.5,
                    self::TEXT_SIZE_LARGE => 3.0,
                ],
            ],
            self::WCAG_LEVEL_AAA => [
                'contrast_ratio' => [
                    self::TEXT_SIZE_NORMAL => 7.0,
                    self::TEXT_SIZE_LARGE => 4.5,
                ],
            ],
        ];
    }

    /**
     * Get color blindness simulation types
     *
     * @return array
     */
    public static function get_color_blindness_types(): array {
        return [
            self::COLOR_BLINDNESS_DEUTERANOPIA => [
                'name' => 'Deuteranopia',
                'description' => 'Red-green color blindness (green weak)',
            ],
            self::COLOR_BLINDNESS_PROTANOPIA => [
                'name' => 'Protanopia',
                'description' => 'Red-green color blindness (red weak)',
            ],
            self::COLOR_BLINDNESS_TRITANOPIA => [
                'name' => 'Tritanopia',
                'description' => 'Blue-yellow color blindness',
            ],
        ];
    }

    /**
     * Get compliance status types
     *
     * @return array
     */
    public static function get_status_types(): array {
        return [
            self::STATUS_PASS => [
                'label' => 'Pass',
                'description' => 'Meets accessibility requirements',
            ],
            self::STATUS_FAIL => [
                'label' => 'Fail',
                'description' => 'Does not meet accessibility requirements',
            ],
            self::STATUS_WARNING => [
                'label' => 'Warning',
                'description' => 'Potential accessibility concerns',
            ],
        ];
    }
}
