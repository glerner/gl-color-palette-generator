<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\AIColorService;

class AIColorServiceTest extends TestCase {
    private $service;

    protected function setUp(): void {
        $this->service = $this->createMock(AIColorService::class);
    }

    public function test_generate_palette_returns_valid_structure(): void {
        / Arrange
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

        / Act
        $result = $this->service->generate_palette($criteria);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertArrayHasKey('rationale', $result);
        $this->assertCount(5, $result['colors']);
    }

    public function test_analyze_palette_returns_valid_analysis(): void {
        / Arrange
        $palette = ['#FF0000', '#00FF00', '#0000FF'];
        $expected = [
            'harmony_score' => 0.85,
            'mood_analysis' => [
                'energetic' => 0.9,
                'bold' => 0.8
            ],
            'accessibility' => [
                'wcag_aa' => true,
                'wcag_aaa' => false
            ],
            'suggestions' => [
                'Consider adding a neutral color'
            ]
        ];

        $this->service
            ->expects($this->once())
            ->method('analyze_palette')
            ->with($palette)
            ->willReturn($expected);

        / Act
        $result = $this->service->analyze_palette($palette);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('harmony_score', $result);
        $this->assertArrayHasKey('mood_analysis', $result);
        $this->assertArrayHasKey('accessibility', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertIsFloat($result['harmony_score']);
    }

    public function test_get_color_recommendations_returns_valid_suggestions(): void {
        / Arrange
        $context = [
            'industry' => 'technology',
            'audience' => 'professional',
            'brand' => ['#FF0000', '#00FF00', '#0000FF'],
            'purpose' => 'branding'
        ];

        $expected = [
            'primary' => ['#FF0000', '#00FF00', '#0000FF'],
            'secondary' => ['#FFFF00', '#FF00FF', '#000000'],
            'accent' => ['#00FF00', '#FF0000', '#0000FF'],
            'rationale' => ['Primary color chosen for...', 'Secondary color provides...', 'Accent color provides...']
        ];

        $this->service
            ->expects($this->once())
            ->method('get_color_recommendations')
            ->with($context)
            ->willReturn($expected);

        / Act
        $result = $this->service->get_color_recommendations($context);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('primary', $result);
        $this->assertArrayHasKey('secondary', $result);
        $this->assertArrayHasKey('accent', $result);
        $this->assertArrayHasKey('rationale', $result);
        $this->assertCount(3, $result['primary']);
        $this->assertCount(3, $result['secondary']);
        $this->assertCount(3, $result['accent']);
        $this->assertCount(3, $result['rationale']);
    }

    public function test_validate_service_connection_returns_true(): void {
        / Arrange
        $this->service
            ->expects($this->once())
            ->method('validate_service_connection')
            ->willReturn(true);

        / Act
        $result = $this->service->validate_service_connection();

        / Assert
        $this->assertTrue($result);
    }
} 
