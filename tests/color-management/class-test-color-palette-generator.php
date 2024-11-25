<?php
/**
 * Test Color Palette Generator Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator;
use GL_Color_Palette_Generator\Settings\Settings_Manager;
use GL_Color_Palette_Generator\AI\AI_Provider_Interface;
use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Class Test_Color_Palette_Generator
 */
class Test_Color_Palette_Generator extends TestCase {
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * Settings manager mock
     *
     * @var Mockery\MockInterface
     */
    private $settings;

    /**
     * AI provider mock
     *
     * @var Mockery\MockInterface
     */
    private $ai_provider;

    /**
     * Setup test environment
     */
    protected function setUp(): void {
        parent::setUp();
        \Brain\Monkey\setUp();

        // Mock settings
        $this->settings = Mockery::mock(Settings_Manager::class);
        $this->settings->shouldReceive('get_setting')
            ->with('ai_provider', 'openai')
            ->andReturn('openai');
        $this->settings->shouldReceive('get_setting')
            ->with('api_key')
            ->andReturn('test_key');

        // Mock cache functions
        Functions\when('wp_cache_get')->justReturn(false);
        Functions\when('wp_cache_set')->justReturn(true);
    }

    /**
     * Teardown test environment
     */
    protected function tearDown(): void {
        Mockery::close();
        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test successful palette generation
     */
    public function test_generate_from_prompt_success() {
        $expected_colors = [
            '#ff0000',
            '#00ff00',
            '#0000ff',
            '#ffff00',
            '#ff00ff'
        ];

        // Mock AI response
        $ai_response = implode("\n", $expected_colors);
        $this->ai_provider = Mockery::mock(AI_Provider_Interface::class);
        $this->ai_provider->shouldReceive('generate_response')
            ->once()
            ->andReturn($ai_response);

        // Create generator with mocked dependencies
        $generator = $this->createGeneratorWithMocks();

        $palette = $generator->generate_from_prompt('Create a vibrant color scheme');

        $this->assertCount(5, $palette);
        $this->assertEquals($expected_colors, $palette);
    }

    /**
     * Test palette generation with invalid AI response
     */
    public function test_generate_from_prompt_invalid_response() {
        $this->ai_provider = Mockery::mock(AI_Provider_Interface::class);
        $this->ai_provider->shouldReceive('generate_response')
            ->once()
            ->andReturn('Invalid response without proper hex codes');

        $generator = $this->createGeneratorWithMocks();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid AI response format');

        $generator->generate_from_prompt('Create a color scheme');
    }

    /**
     * Test palette generation with similar colors
     */
    public function test_generate_from_prompt_similar_colors() {
        $similar_colors = [
            '#ff0000',
            '#ff0505',
            '#ff0a0a',
            '#ff0f0f',
            '#ff1414'
        ];

        $this->ai_provider = Mockery::mock(AI_Provider_Interface::class);
        $this->ai_provider->shouldReceive('generate_response')
            ->once()
            ->andReturn(implode("\n", $similar_colors));

        $generator = $this->createGeneratorWithMocks();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Generated colors are not visually distinct enough');

        $generator->generate_from_prompt('Create a red color scheme');
    }

    /**
     * Test palette generation with cached result
     */
    public function test_generate_from_prompt_cached() {
        $cached_palette = [
            '#ff0000',
            '#00ff00',
            '#0000ff',
            '#ffff00',
            '#ff00ff'
        ];

        Functions\when('wp_cache_get')->justReturn(json_encode($cached_palette));

        $generator = $this->createGeneratorWithMocks();

        $palette = $generator->generate_from_prompt('Create a vibrant color scheme');

        $this->assertEquals($cached_palette, $palette);
    }

    /**
     * Test system prompt formatting
     */
    public function test_system_prompt_format() {
        $this->ai_provider = Mockery::mock(AI_Provider_Interface::class);
        $this->ai_provider->shouldReceive('generate_response')
            ->once()
            ->with(
                Mockery::type('string'),
                Mockery::pattern('/Generate a color palette for:.*\nProvide exactly 5 colors/')
            )
            ->andReturn("#ff0000\n#00ff00\n#0000ff\n#ffff00\n#ff00ff");

        $generator = $this->createGeneratorWithMocks();
        $generator->generate_from_prompt('Test prompt');
    }

    /**
     * Test error handling for AI provider failure
     */
    public function test_ai_provider_failure() {
        $this->ai_provider = Mockery::mock(AI_Provider_Interface::class);
        $this->ai_provider->shouldReceive('generate_response')
            ->once()
            ->andThrow(new \Exception('AI service unavailable'));

        $generator = $this->createGeneratorWithMocks();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to generate palette: AI service unavailable');

        $generator->generate_from_prompt('Test prompt');
    }

    /**
     * Create generator instance with mocked dependencies
     *
     * @return Color_Palette_Generator
     */
    private function createGeneratorWithMocks() {
        $reflection = new \ReflectionClass(Color_Palette_Generator::class);

        $generator = $reflection->newInstanceWithoutConstructor();

        $settings_prop = $reflection->getProperty('settings');
        $settings_prop->setAccessible(true);
        $settings_prop->setValue($generator, $this->settings);

        $ai_provider_prop = $reflection->getProperty('ai_provider');
        $ai_provider_prop->setAccessible(true);
        $ai_provider_prop->setValue($generator, $this->ai_provider);

        return $generator;
    }
}
