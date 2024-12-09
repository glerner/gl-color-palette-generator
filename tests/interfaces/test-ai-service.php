<?php
/**
 * AI Service Interface Tests
 *
 * @package GLColorPalette
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\AIService;

class AIServiceTest extends TestCase {
    private $service;

    protected function setUp(): void {
        $this->service = $this->createMock(AIService::class);
    }

    public function test_generate_color_description_returns_valid_string(): void {
        // Arrange
        $color = '#FF0000';
        $context = ['theme' => 'modern', 'industry' => 'technology'];
        $expected = 'A vibrant, energetic red that conveys power and dynamism';

        $this->service
            ->expects($this->once())
            ->method('generate_color_description')
            ->with($color, $context)
            ->willReturn($expected);

        // Act
        $result = $this->service->generate_color_description($color, $context);

        // Assert
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertEquals($expected, $result);
    }

    public function test_analyze_color_emotion_returns_valid_array(): void {
        // Arrange
        $color = '#FF0000';
        $expected = [
            'primary_emotion' => 'excitement',
            'secondary_emotions' => ['passion', 'energy'],
            'intensity' => 0.85,
            'cultural_associations' => ['danger', 'love', 'power']
        ];

        $this->service
            ->expects($this->once())
            ->method('analyze_color_emotion')
            ->with($color)
            ->willReturn($expected);

        // Act
        $result = $this->service->analyze_color_emotion($color);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('primary_emotion', $result);
        $this->assertArrayHasKey('secondary_emotions', $result);
        $this->assertArrayHasKey('intensity', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_suggest_color_names_returns_valid_array(): void {
        // Arrange
        $color = '#FF0000';
        $context = ['style' => 'professional', 'locale' => 'en_US'];
        $expected = [
            'primary' => 'Crimson Fire',
            'alternatives' => ['Ruby Red', 'Dynamic Red', 'Power Red'],
            'descriptors' => ['vibrant', 'bold', 'energetic']
        ];

        $this->service
            ->expects($this->once())
            ->method('suggest_color_names')
            ->with($color, $context)
            ->willReturn($expected);

        // Act
        $result = $this->service->suggest_color_names($color, $context);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('primary', $result);
        $this->assertArrayHasKey('alternatives', $result);
        $this->assertArrayHasKey('descriptors', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_color_context_returns_valid_array(): void {
        // Arrange
        $color = '#FF0000';
        $expected = [
            'semantic_meaning' => 'action',
            'common_uses' => ['calls-to-action', 'alerts', 'branding'],
            'industry_relevance' => ['retail', 'food', 'entertainment'],
            'seasonal_context' => ['winter holidays', 'valentines']
        ];

        $this->service
            ->expects($this->once())
            ->method('get_color_context')
            ->with($color)
            ->willReturn($expected);

        // Act
        $result = $this->service->get_color_context($color);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('semantic_meaning', $result);
        $this->assertArrayHasKey('common_uses', $result);
        $this->assertArrayHasKey('industry_relevance', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_analyze_color_accessibility_returns_valid_array(): void {
        // Arrange
        $color = '#FF0000';
        $background = '#FFFFFF';
        $expected = [
            'wcag_score' => 4.5,
            'passes_aa' => true,
            'passes_aaa' => false,
            'recommendations' => [
                'text_size' => 'minimum 14px for AA compliance',
                'alternative_colors' => ['#CC0000', '#990000']
            ]
        ];

        $this->service
            ->expects($this->once())
            ->method('analyze_color_accessibility')
            ->with($color, $background)
            ->willReturn($expected);

        // Act
        $result = $this->service->analyze_color_accessibility($color, $background);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('wcag_score', $result);
        $this->assertArrayHasKey('passes_aa', $result);
        $this->assertArrayHasKey('passes_aaa', $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidColorProvider
     */
    public function test_generate_color_description_throws_exception_for_invalid_color(string $color): void {
        $this->service
            ->expects($this->once())
            ->method('generate_color_description')
            ->with($color, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->service->generate_color_description($color, []);
    }

    /**
     * @dataProvider invalidContextProvider
     */
    public function test_suggest_color_names_throws_exception_for_invalid_context(array $context): void {
        $this->service
            ->expects($this->once())
            ->method('suggest_color_names')
            ->with('#FF0000', $context)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->service->suggest_color_names('#FF0000', $context);
    }

    public function invalidColorProvider(): array {
        return [
            'invalid hex' => ['#GG0000'],
            'too short' => ['#F00'],
            'no hash' => ['FF0000'],
            'empty string' => [''],
            'invalid format' => ['rgb(255,0,0)']
        ];
    }

    public function invalidContextProvider(): array {
        return [
            'empty array' => [[]],
            'invalid keys' => [['invalid_key' => 'value']],
            'invalid values' => [['theme' => []]],
            'missing required' => [['style' => 'professional']],
            'invalid types' => [['locale' => 123]]
        ];
    }
}
