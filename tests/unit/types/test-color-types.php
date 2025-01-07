<?php
/**
 * Tests for Color_Types class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Types
 * @since 1.0.0
 */

 namespace GL_Color_Palette_Generator\Tests\Unit\Types;

 use GL_Color_Palette_Generator\Tests\Unit_Test_Case;
 use GL_Color_Palette_Generator\Types\Color_Types;

 class Test_Color_Types extends Unit_Test_Case {

    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        parent::tearDown();
    }






    /**
     * Test hex color validation
     *
     * @return void
     */
    public function test_is_valid_hex_color(): void {
        // Valid colors
        $this->assertTrue(Color_Types::is_valid_hex_color('#000000'));
        $this->assertTrue(Color_Types::is_valid_hex_color('#FFFFFF'));
        $this->assertTrue(Color_Types::is_valid_hex_color('#123'));
        $this->assertTrue(Color_Types::is_valid_hex_color('#ABC'));

        // Invalid colors
        $this->assertFalse(Color_Types::is_valid_hex_color('000000'));
        $this->assertFalse(Color_Types::is_valid_hex_color('#12345'));
        $this->assertFalse(Color_Types::is_valid_hex_color('#GHIJKL'));
        $this->assertFalse(Color_Types::is_valid_hex_color('not a color'));
        $this->assertFalse(Color_Types::is_valid_hex_color(''));
    }

    /**
     * Test metadata validation
     *
     * @return void
     */
    public function test_is_valid_metadata(): void {
        // Valid metadata
        $valid_metadata = [
            'name' => 'Test Palette',
            'description' => 'A test palette',
            'theme' => 'Test',
            'created' => '2024-12-08',
            'modified' => '2024-12-08',
            'provider' => 'test',
            'tags' => ['test1', 'test2']
        ];
        $this->assertTrue(Color_Types::is_valid_metadata($valid_metadata));

        // Invalid metadata - missing field
        $invalid_metadata1 = [
            'name' => 'Test Palette',
            'description' => 'A test palette',
            // missing theme
            'created' => '2024-12-08',
            'modified' => '2024-12-08',
            'provider' => 'test',
            'tags' => ['test1', 'test2']
        ];
        $this->assertFalse(Color_Types::is_valid_metadata($invalid_metadata1));

        // Invalid metadata - wrong type
        $invalid_metadata2 = [
            'name' => 'Test Palette',
            'description' => 'A test palette',
            'theme' => 123, // should be string
            'created' => '2024-12-08',
            'modified' => '2024-12-08',
            'provider' => 'test',
            'tags' => ['test1', 'test2']
        ];
        $this->assertFalse(Color_Types::is_valid_metadata($invalid_metadata2));

        // Invalid metadata - invalid tags
        $invalid_metadata3 = [
            'name' => 'Test Palette',
            'description' => 'A test palette',
            'theme' => 'Test',
            'created' => '2024-12-08',
            'modified' => '2024-12-08',
            'provider' => 'test',
            'tags' => ['test1', 123] // should all be strings
        ];
        $this->assertFalse(Color_Types::is_valid_metadata($invalid_metadata3));
    }

    /**
     * Test provider options validation
     *
     * @return void
     */
    public function test_is_valid_provider_options(): void {
        // Valid options
        $valid_options = [
            'model' => 'gpt-4',
            'temperature' => 0.7,
            'max_tokens' => 150,
            'top_p' => 1.0,
            'frequency_penalty' => 0.0,
            'presence_penalty' => 0.0
        ];
        $this->assertTrue(Color_Types::is_valid_provider_options($valid_options));

        // Invalid options - wrong type
        $invalid_options1 = [
            'model' => 123, // should be string
            'temperature' => 0.7
        ];
        $this->assertFalse(Color_Types::is_valid_provider_options($invalid_options1));

        // Invalid options - out of range
        $invalid_options2 = [
            'model' => 'gpt-4',
            'temperature' => 1.5 // should be between 0 and 1
        ];
        $this->assertFalse(Color_Types::is_valid_provider_options($invalid_options2));

        // Invalid options - unknown field
        $invalid_options3 = [
            'model' => 'gpt-4',
            'unknown_field' => 'value'
        ];
        $this->assertFalse(Color_Types::is_valid_provider_options($invalid_options3));
    }
}
