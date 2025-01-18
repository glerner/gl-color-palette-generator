<?php
/**
 * AI Interface Tests
 *
 * Combined tests for AI-related interfaces:
 * - AI_Color_Service
 * - AI_Provider
 * - AI_Service
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\AI_Color_Service;
use GL_Color_Palette_Generator\Interfaces\AI_Provider;
use GL_Color_Palette_Generator\Interfaces\AI_Service;

/**
 * Test AI Color Service Interface implementation
 */
class Test_AI_Color_Service extends Unit_Test_Case {
    private $service;

    public function setUp(): void {
        $this->service = $this->createMock(AI_Color_Service::class);
    }

    public function test_generate_palette_returns_valid_structure(): void {
        // Arrange
        $criteria = [
            'mood' => 'energetic',
            'style' => 'modern',
            'colors' => 5
        ];

        $expected = [
            'colors' => ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF'],
            'metadata' => [
                'confidence' => 0.95,
                'processing_time' => 1.2
            ],
            'model' => 'gpt-4',
            'rationale' => ['Color 1 chosen for...', 'Color 2 provides...']
        ];

        $this->service
            ->expects($this->once())
            ->method('generate_palette')
            ->with($criteria)
            ->willReturn($expected);

        // Act
        $result = $this->service->generate_palette($criteria);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertArrayHasKey('rationale', $result);
        $this->assertCount(5, $result['colors']);
    }

    public function test_analyze_palette_returns_analysis(): void {
        // Arrange
        $palette = ['#FF0000', '#00FF00', '#0000FF'];
        $expected = [
            'harmony' => 'triadic',
            'mood' => 'energetic',
            'style' => 'modern',
            'analysis' => 'This palette uses primary colors...'
        ];

        $this->service
            ->expects($this->once())
            ->method('analyze_palette')
            ->with($palette)
            ->willReturn($expected);

        // Act
        $result = $this->service->analyze_palette($palette);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('harmony', $result);
        $this->assertArrayHasKey('mood', $result);
        $this->assertArrayHasKey('style', $result);
        $this->assertArrayHasKey('analysis', $result);
    }
}

/**
 * Test AI Provider Interface implementation
 */
class Test_AI_Provider extends Unit_Test_Case {
    private $provider;

    public function setUp(): void {
        $this->provider = $this->createMock(AI_Provider::class);
    }

    public function test_generate_response_returns_string(): void {
        // Arrange
        $prompt = 'Describe the color #FF0000';
        $options = [
            'temperature' => 0.7,
            'max_tokens' => 100
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
        foreach ($result as $value) {
            $this->assertIsFloat($value);
        }
    }
}

/**
 * Test AI Service Interface implementation
 */
class Test_AI_Service extends Unit_Test_Case {
    private $service;

    public function setUp(): void {
        $this->service = $this->createMock(AI_Service::class);
    }

    public function test_process_request_returns_response(): void {
        // Arrange
        $request = [
            'type' => 'color_description',
            'data' => ['color' => '#FF0000'],
            'options' => ['format' => 'json']
        ];
        $expected = [
            'description' => 'A bold and energetic red',
            'associations' => ['energy', 'passion', 'strength'],
            'metadata' => ['processing_time' => 0.5]
        ];

        $this->service
            ->expects($this->once())
            ->method('process_request')
            ->with($request)
            ->willReturn($expected);

        // Act
        $result = $this->service->process_request($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('associations', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_validate_request_returns_validation_result(): void {
        // Arrange
        $request = [
            'type' => 'color_description',
            'data' => ['color' => '#FF0000']
        ];
        $expected = [
            'valid' => true,
            'errors' => []
        ];

        $this->service
            ->expects($this->once())
            ->method('validate_request')
            ->with($request)
            ->willReturn($expected);

        // Act
        $result = $this->service->validate_request($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertTrue($result['valid']);
    }
}
