<?php
/**
 * Base Test Case Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Unit;

use WP_Mock\Tools\TestCase;

/**
 * Base test case class for all tests
 */
class Test_Case extends TestCase {
    /**
     * Output buffer level
     *
     * @var int
     */
    protected $ob_level;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        // Start output buffering before parent setup
        $this->ob_level = ob_get_level();
        ob_start();

        parent::setUp();

        // Load plugin dependencies
        require_once dirname(__DIR__) . '/vendor/autoload.php';

        // Initialize test environment
        $this->init_test_environment();
    }

    /**
     * Clean up test environment
     */
    public function tearDown(): void {
        parent::tearDown();

        // Clean up output buffering
        while (ob_get_level() > $this->ob_level) {
            ob_end_clean();
        }
    }

    /**
     * Initialize test environment
     */
    protected function init_test_environment(): void {
        // Set up error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

        // Set up test constants if needed
        if (!defined('GL_CPG_TESTING')) {
            define('GL_CPG_TESTING', true);
        }

        // Set up test hooks
        add_filter('gl_cpg_is_testing', '__return_true');

        // Initialize WordPress test environment
        global $wp_rewrite;
        $wp_rewrite->init();
        $wp_rewrite->flush_rules();
    }

    /**
     * Assert that two colors have the expected contrast ratio
     *
     * @param string $color1 First color hex code
     * @param string $color2 Second color hex code
     * @param float  $expected_ratio Expected contrast ratio
     * @param float  $delta Allowed variance
     */
    protected function assertContrastRatio($color1, $color2, $expected_ratio, $delta = 0.1) {
        $checker = new \GL_Color_Palette_Generator\Accessibility\Accessibility_Checker();
        $actual_ratio = $checker->calculate_contrast_ratio($color1, $color2);

        $this->assertEqualsWithDelta(
            $expected_ratio,
            $actual_ratio,
            $delta,
            sprintf(
                'Expected contrast ratio between %s and %s to be %f (±%f), but got %f',
                $color1,
                $color2,
                $expected_ratio,
                $delta,
                $actual_ratio
            )
        );
    }

    /**
     * Assert that a color is WCAG AA compliant with another color
     *
     * @param string $color1 First color hex code
     * @param string $color2 Second color hex code
     * @param string $context Context (normal or large text)
     */
    protected function assertWCAGAACompliant($color1, $color2, $context = 'normal') {
        $checker = new \GL_Color_Palette_Generator\Accessibility\Accessibility_Checker();
        $is_compliant = $checker->meets_wcag_aa($color1, $color2, $context);

        $this->assertTrue(
            $is_compliant,
            sprintf(
                'Expected colors %s and %s to be WCAG AA compliant for %s text',
                $color1,
                $color2,
                $context
            )
        );
    }

    /**
     * Assert that a color is WCAG AAA compliant with another color
     *
     * @param string $color1 First color hex code
     * @param string $color2 Second color hex code
     * @param string $context Context (normal or large text)
     */
    protected function assertWCAGAAACompliant($color1, $color2, $context = 'normal') {
        $checker = new \GL_Color_Palette_Generator\Accessibility\Accessibility_Checker();
        $is_compliant = $checker->meets_wcag_aaa($color1, $color2, $context);

        $this->assertTrue(
            $is_compliant,
            sprintf(
                'Expected colors %s and %s to be WCAG AAA compliant for %s text',
                $color1,
                $color2,
                $context
            )
        );
    }
}
