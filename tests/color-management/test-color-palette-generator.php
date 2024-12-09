<?php
declare(strict_types=1);

/**
 * Tests for Color_Palette_Generator class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Color_Management
 */

namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator;
use GL_Color_Palette_Generator\Exceptions\PaletteGenerationException;
use GL_Color_Palette_Generator\Settings\Settings_Manager;
use GL_Color_Palette_Generator\AI\AI_Provider_Interface;
use PHPUnit\Framework\TestCase;

/**
 * Color Palette Generator test case
 */
class Test_Color_Palette_Generator extends TestCase {
    /**
     * Mock settings manager
     *
     * @var Settings_Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $settings;

    /**
     * Mock AI provider
     *
     * @var AI_Provider_Interface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $ai_provider;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();

        // Mock settings
        $this->settings = $this->createMock(Settings_Manager::class);
        $this->settings->method('get_setting')
            ->willReturnMap([
                ['ai_provider', 'openai', 'openai'],
                ['api_key', null, 'test_key'],
                ['cache_duration', 3600, 3600]
            ]);

        // Mock AI provider
        $this->ai_provider = $this->createMock(AI_Provider_Interface::class);
    }

    /**
     * Test successful palette generation
     */
    public function test_generate_from_prompt_success(): void {
        $prompt = 'Test prompt';
        $expected_colors = [
            '#FF0000',
            '#00FF00',
            '#0000FF',
            '#FFFF00',
            '#FF00FF'
        ];

        $this->ai_provider->method('generate_response')
            ->willReturn(implode("\n", $expected_colors));

        $generator = new Color_Palette_Generator();
        $result = $generator->generate_from_prompt($prompt);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertEquals($expected_colors, $result['colors']);
    }

    /**
     * Test empty prompt validation
     */
    public function test_generate_from_prompt_empty_prompt(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Prompt cannot be empty');

        $generator = new Color_Palette_Generator();
        $generator->generate_from_prompt('');
    }

    /**
     * Test invalid color response
     */
    public function test_generate_from_prompt_invalid_colors(): void {
        $prompt = 'Test prompt';
        $invalid_colors = [
            'not a color',
            '#GG0000',
            '#12345',
            'RGB(255,0,0)',
            'red'
        ];

        $this->ai_provider->method('generate_response')
            ->willReturn(implode("\n", $invalid_colors));

        $this->expectException(PaletteGenerationException::class);
        $this->expectExceptionMessage('Invalid color format');

        $generator = new Color_Palette_Generator();
        $generator->generate_from_prompt($prompt);
    }

    /**
     * Test AI provider error handling
     */
    public function test_generate_from_prompt_ai_error(): void {
        $prompt = 'Test prompt';
        $error_message = 'API error';

        $this->ai_provider->method('generate_response')
            ->willThrowException(new \Exception($error_message));

        $this->expectException(PaletteGenerationException::class);
        $this->expectExceptionMessage('Failed to generate colors: ' . $error_message);

        $generator = new Color_Palette_Generator();
        $generator->generate_from_prompt($prompt);
    }

    /**
     * Test cache functionality
     */
    public function test_generate_from_prompt_cache(): void {
        $prompt = 'Test prompt';
        $cached_palette = [
            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'metadata' => ['theme' => 'test']
        ];

        // Mock cache functions
        global $wp_object_cache;
        $wp_object_cache = $this->createMock(\WP_Object_Cache::class);
        $wp_object_cache->method('get')
            ->willReturn(json_encode($cached_palette));

        $generator = new Color_Palette_Generator();
        $result = $generator->generate_from_prompt($prompt);

        $this->assertEquals($cached_palette, $result);
        // Verify AI provider was not called
        $this->ai_provider->expects($this->never())
            ->method('generate_response');
    }
}
