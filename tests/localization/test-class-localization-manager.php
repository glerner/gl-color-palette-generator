<?php

namespace GLColorPalette\Tests\Localization;

use GLColorPalette\Localization\Localization_Manager;
use WP_UnitTestCase;

class Test_Localization_Manager extends WP_UnitTestCase {
    private $localization_manager;

    public function setUp(): void {
        parent::setUp();
        $this->localization_manager = new Localization_Manager();
    }

    public function test_load_textdomain(): void {
        // Test that the textdomain is loaded correctly
        $result = $this->localization_manager->load_textdomain();
        $this->assertTrue($result);
        
        $this->assertTrue(is_textdomain_loaded('gl-color-palette-generator'));
    }

    public function test_get_available_languages(): void {
        $languages = $this->localization_manager->get_available_languages();
        
        // Should at least have English
        $this->assertContains('en_US', $languages);
        
        // Check structure of language data
        foreach ($languages as $code => $data) {
            $this->assertArrayHasKey('name', $data);
            $this->assertArrayHasKey('native_name', $data);
            $this->assertArrayHasKey('translation_status', $data);
        }
    }

    public function test_get_current_language(): void {
        $current = $this->localization_manager->get_current_language();
        
        $this->assertNotEmpty($current);
        $this->assertArrayHasKey('code', $current);
        $this->assertArrayHasKey('name', $current);
        $this->assertArrayHasKey('direction', $current);
    }

    public function test_switch_language(): void {
        // Test switching to a valid language
        $result = $this->localization_manager->switch_language('es_ES');
        $this->assertTrue($result);
        
        // Verify the switch
        $current = $this->localization_manager->get_current_language();
        $this->assertEquals('es_ES', $current['code']);
        
        // Test switching to invalid language
        $result = $this->localization_manager->switch_language('invalid_code');
        $this->assertFalse($result);
    }

    public function test_register_strings(): void {
        $strings = [
            'test_key' => 'Test String',
            'another_key' => 'Another String'
        ];
        
        $result = $this->localization_manager->register_strings($strings);
        $this->assertTrue($result);
        
        // Verify strings are registered
        foreach ($strings as $key => $value) {
            $this->assertEquals(
                $value,
                $this->localization_manager->get_string($key)
            );
        }
    }

    public function test_rtl_support(): void {
        // Test RTL detection for Arabic
        $result = $this->localization_manager->switch_language('ar');
        $this->assertTrue($result);
        
        $current = $this->localization_manager->get_current_language();
        $this->assertEquals('rtl', $current['direction']);
        
        // Test LTR detection for English
        $result = $this->localization_manager->switch_language('en_US');
        $this->assertTrue($result);
        
        $current = $this->localization_manager->get_current_language();
        $this->assertEquals('ltr', $current['direction']);
    }

    public function test_translation_fallbacks(): void {
        // Test fallback to default language
        $this->localization_manager->switch_language('fr_FR');
        
        // Should fall back to English if French translation doesn't exist
        $test_string = $this->localization_manager->get_string('nonexistent_key');
        $this->assertNotEmpty($test_string);
    }

    public function test_language_switching_hooks(): void {
        // Test that hooks are fired when switching languages
        $hook_fired = false;
        add_action('gl_color_palette_language_switched', function($new_lang, $old_lang) use (&$hook_fired) {
            $hook_fired = true;
            $this->assertEquals('es_ES', $new_lang);
            $this->assertEquals('en_US', $old_lang);
        }, 10, 2);
        
        $this->localization_manager->switch_language('es_ES');
        $this->assertTrue($hook_fired);
    }

    public function tearDown(): void {
        parent::tearDown();
    }
}
