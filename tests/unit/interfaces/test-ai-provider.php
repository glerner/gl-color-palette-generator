<?php
/**
 * AI Provider Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\AIProvider;

class AIProviderTest extends TestCase {
    private $provider;

    public function setUp(): void {
        $this->provider = $this->createMock(AIProvider::class);
    }

    public function test_generate_response_returns_string(): void {
        // Arrange
        $prompt = 'Describe the color #FF0000';
        $options = [
            'temperature' => 0.7,
            'max_tokens' => 100,
            'context' => 'color description'
        ];
        $expected = 'A vibrant, energetic red that commands attention';

        $this->provider
            ->expects($this->once())
            ->method('generate_response')
            ->with($prompt, $options)
            ->willReturn($expected);

        // Act
        $result = $this->provider->generate_response($prompt, $options);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_embeddings_returns_array(): void {
        // Arrange
        $text = 'Vibrant red color';
        $expected = [0.1, -0.3, 0.5, 0.2];

        $this->provider
            ->expects($this->once())
            ->method('get_embeddings')
            ->with($text)
            ->willReturn($expected);

        // Act
        $result = $this->provider->get_embeddings($text);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function test_analyze_sentiment_returns_array(): void {
        // Arrange
        $text = 'This color is perfect for creating a warm, inviting atmosphere';
        $expected = [
            'sentiment' => 'positive',
            'score' => 0.85,
            'aspects' => [
                'warmth' => 0.9,
                'inviting' => 0.8
            ]
        ];

        $this->provider
            ->expects($this->once())
            ->method('analyze_sentiment')
            ->with($text)
            ->willReturn($expected);

        // Act
        $result = $this->provider->analyze_sentiment($text);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('sentiment', $result);
        $this->assertArrayHasKey('score', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_model_info_returns_array(): void {
        // Arrange
        $expected = [
            'name' => 'gpt-4',
            'version' => '1.0',
            'capabilities' => [
                'text_generation',
                'embeddings',
                'sentiment_analysis'
            ],
            'limits' => [
                'max_tokens' => 4096,
                'requests_per_minute' => 60
            ]
        ];

        $this->provider
            ->expects($this->once())
            ->method('get_model_info')
            ->willReturn($expected);

        // Act
        $result = $this->provider->get_model_info();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('capabilities', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_is_available_returns_boolean(): void {
        // Arrange
        $this->provider
            ->expects($this->once())
            ->method('is_available')
            ->willReturn(true);

        // Act
        $result = $this->provider->is_available();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @dataProvider invalidPromptProvider
     */
    public function test_generate_response_throws_exception_for_invalid_prompt($prompt): void {
        $this->provider
            ->expects($this->once())
            ->method('generate_response')
            ->with($prompt, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->provider->generate_response($prompt, []);
    }

    /**
     * @dataProvider invalidOptionsProvider
     */
    public function test_generate_response_throws_exception_for_invalid_options($options): void {
        $this->provider
            ->expects($this->once())
            ->method('generate_response')
            ->with('valid prompt', $options)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->provider->generate_response('valid prompt', $options);
    }

    /**
     * @dataProvider invalidTextProvider
     */
    public function test_get_embeddings_throws_exception_for_invalid_text($text): void {
        $this->provider
            ->expects($this->once())
            ->method('get_embeddings')
            ->with($text)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->provider->get_embeddings($text);
    }

    public function invalidPromptProvider(): array {
        return [
            'empty string' => [''],
            'too long' => [str_repeat('a', 10001)],
            'array input' => [[]],
            'null input' => [null],
            'numeric input' => [42]
        ];
    }

    public function invalidOptionsProvider(): array {
        return [
            'invalid temperature' => [['temperature' => 2.0]],
            'invalid max_tokens' => [['max_tokens' => -1]],
            'invalid type' => [['temperature' => 'hot']],
            'non-array options' => ['invalid'],
            'null options' => [null]
        ];
    }

    public function invalidTextProvider(): array {
        return [
            'empty string' => [''],
            'too long' => [str_repeat('a', 10001)],
            'array input' => [[]],
            'null input' => [null],
            'numeric input' => [42]
        ];
    }
}
