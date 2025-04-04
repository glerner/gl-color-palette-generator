<?php declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests\Integration;
use GL_Color_Palette_Generator\Tests\Base\Integration_Test_Case;


/**
 * Basic integration test to verify plugin setup
 */
class Test_Plugin extends Integration_Test_Case {
    /**
     * Test that the plugin is loaded and functions exist
     */
    public function test_plugin_loaded() {
        $this->assertTrue(function_exists('gl_color_palette_generator_init'));
    }
}
