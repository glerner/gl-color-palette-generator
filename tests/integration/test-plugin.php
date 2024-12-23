<?php declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests\Integration;

use WP_UnitTestCase;

/**
 * Basic integration test to verify plugin setup
 */
class Test_Plugin extends WP_UnitTestCase {
    /**
     * Test that the plugin is loaded and functions exist
     */
    public function test_plugin_loaded() {
        $this->assertTrue(function_exists('gl_color_palette_generator_init'));
    }
}
