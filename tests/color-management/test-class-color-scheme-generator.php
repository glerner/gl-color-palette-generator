<?php
/**
 * Tests for Color_Scheme_Generator class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Scheme_Generator;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use WP_Error;
use WP_UnitTestCase;
use Mockery;

/**
 * Class Test_Color_Scheme_Generator
 */
class Test_Color_Scheme_Generator extends WP_UnitTestCase {
    /**
     * Test instance
     *
     * @var Color_Scheme_Generator
     */
    private $instance;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();
        $this->instance = new Color_Scheme_Generator();
    }

    /**
     * Tear down test environment
     */
    public function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test generate_scheme method
     */
    public function test_generate_scheme() {
        $base_color = '#ff0000';
        
        // Test default options
        $result = $this->instance->generate_scheme($base_color);
        $this->assertIsArray($result);
        $this->assertCount(5, $result);
        
        // Test with specific scheme type
        $result = $this->instance->generate_scheme($base_color, ['type' => 'complementary', 'count' => 4]);
        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        
        // Test invalid color
        $result = $this->instance->generate_scheme('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
        
        // Test invalid scheme type
        $result = $this->instance->generate_scheme($base_color, ['type' => 'invalid']);
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test generate_monochromatic method
     */
    public function test_generate_monochromatic() {
        $base_color = '#ff0000';
        
        // Test default count
        $result = $this->instance->generate_monochromatic($base_color);
        $this->assertIsArray($result);
        $this->assertCount(5, $result);
        
        // Test custom count
        $result = $this->instance->generate_monochromatic($base_color, 3);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        
        // Test invalid color
        $result = $this->instance->generate_monochromatic('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test generate_analogous method
     */
    public function test_generate_analogous() {
        $base_color = '#ff0000';
        
        // Test default count
        $result = $this->instance->generate_analogous($base_color);
        $this->assertIsArray($result);
        $this->assertCount(5, $result);
        
        // Test custom count
        $result = $this->instance->generate_analogous($base_color, 3);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        
        // Test invalid color
        $result = $this->instance->generate_analogous('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test generate_complementary method
     */
    public function test_generate_complementary() {
        $base_color = '#ff0000';
        
        // Test default count
        $result = $this->instance->generate_complementary($base_color);
        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        
        // Test custom count
        $result = $this->instance->generate_complementary($base_color, 2);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        // Test invalid color
        $result = $this->instance->generate_complementary('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test generate_split_complementary method
     */
    public function test_generate_split_complementary() {
        $base_color = '#ff0000';
        
        // Test default count
        $result = $this->instance->generate_split_complementary($base_color);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        
        // Test custom count
        $result = $this->instance->generate_split_complementary($base_color, 2);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        // Test invalid color
        $result = $this->instance->generate_split_complementary('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test generate_triadic method
     */
    public function test_generate_triadic() {
        $base_color = '#ff0000';
        
        // Test default count
        $result = $this->instance->generate_triadic($base_color);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        
        // Test custom count
        $result = $this->instance->generate_triadic($base_color, 2);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        // Test invalid color
        $result = $this->instance->generate_triadic('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test generate_tetradic method
     */
    public function test_generate_tetradic() {
        $base_color = '#ff0000';
        
        // Test default count
        $result = $this->instance->generate_tetradic($base_color);
        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        
        // Test custom count
        $result = $this->instance->generate_tetradic($base_color, 2);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        // Test invalid color
        $result = $this->instance->generate_tetradic('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test generate_custom_scheme method
     */
    public function test_generate_custom_scheme() {
        $base_color = '#ff0000';
        $rules = [
            ['type' => 'hue_shift', 'value' => 30],
            ['type' => 'saturation_shift', 'value' => -20],
            ['type' => 'value_shift', 'value' => 10],
        ];
        
        // Test valid rules
        $result = $this->instance->generate_custom_scheme($base_color, $rules);
        $this->assertIsArray($result);
        $this->assertCount(4, $result); // Base color + 3 rules
        
        // Test empty rules
        $result = $this->instance->generate_custom_scheme($base_color, []);
        $this->assertInstanceOf(WP_Error::class, $result);
        
        // Test invalid color
        $result = $this->instance->generate_custom_scheme('invalid', $rules);
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test generate_from_image method
     */
    public function test_generate_from_image() {
        // Create a test image
        $image_path = sys_get_temp_dir() . '/test_image.png';
        $image = imagecreatetruecolor(100, 100);
        imagefilledrectangle($image, 0, 0, 50, 100, imagecolorallocate($image, 255, 0, 0));
        imagefilledrectangle($image, 51, 0, 100, 100, imagecolorallocate($image, 0, 0, 255));
        imagepng($image, $image_path);
        imagedestroy($image);

        // Test with valid image
        $result = $this->instance->generate_from_image($image_path);
        $this->assertIsArray($result);
        $this->assertCount(5, $result); // Default count

        // Test with custom count
        $result = $this->instance->generate_from_image($image_path, ['count' => 3]);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        // Test with invalid path
        $result = $this->instance->generate_from_image('invalid/path');
        $this->assertInstanceOf(WP_Error::class, $result);

        // Clean up
        unlink($image_path);
    }

    /**
     * Test generate_theme_scheme method
     */
    public function test_generate_theme_scheme() {
        // Test valid themes
        $themes = ['warm', 'cool', 'natural', 'elegant', 'vibrant', 'pastel'];
        foreach ($themes as $theme) {
            $result = $this->instance->generate_theme_scheme($theme);
            $this->assertIsArray($result);
            $this->assertCount(5, $result); // Default count
        }

        // Test with custom count
        $result = $this->instance->generate_theme_scheme('warm', ['count' => 3]);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        // Test with custom type
        $result = $this->instance->generate_theme_scheme('cool', ['type' => 'complementary']);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Test invalid theme
        $result = $this->instance->generate_theme_scheme('invalid_theme');
        $this->assertInstanceOf(WP_Error::class, $result);
    }
}
