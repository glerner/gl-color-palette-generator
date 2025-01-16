<?php

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\Analytics;

class AnalyticsTest extends TestCase {
    private $analytics;

    public function setUp(): void {
        $this->analytics = $this->createMock(Analytics::class);
    }

    public function test_track_usage_handles_valid_feature(): void {
        // Arrange
        $feature = 'palette_generation';
        $context = ['colors' => 5, 'style' => 'modern'];

        $this->analytics
            ->expects($this->once())
            ->method('track_usage')
            ->with($feature, $context);

        // Act & Assert
        $this->analytics->track_usage($feature, $context);
    }

    public function test_generate_report_returns_valid_structure(): void {
        // Arrange
        $criteria = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'features' => ['palette_generation', 'color_analysis']
        ];

        $expected = [
            'usage_stats' => [
                'palette_generation' => 150,
                'color_analysis' => 75
            ],
            'trends' => [
                'daily_usage' => [/* ... */],
                'peak_times' => [/* ... */]
            ],
            'popular_colors' => [
                '#FF0000' => 45,
                '#00FF00' => 32
            ],
            'user_segments' => [
                'professional' => 60,
                'hobbyist' => 40
            ]
        ];

        $this->analytics
            ->expects($this->once())
            ->method('generate_report')
            ->with($criteria)
            ->willReturn($expected);

        // Act
        $result = $this->analytics->generate_report($criteria);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('usage_stats', $result);
        $this->assertArrayHasKey('trends', $result);
        $this->assertArrayHasKey('popular_colors', $result);
        $this->assertArrayHasKey('user_segments', $result);
    }

    public function test_get_metrics_returns_current_data(): void {
        // Arrange
        $expected = [
            'total_palettes' => 1000,
            'active_users' => 150,
            'popular_features' => [
                'palette_generation' => 500,
                'color_analysis' => 300
            ],
            'color_trends' => [
                'blue' => 'increasing',
                'green' => 'stable'
            ]
        ];

        $this->analytics
            ->expects($this->once())
            ->method('get_metrics')
            ->willReturn($expected);

        // Act
        $result = $this->analytics->get_metrics();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_palettes', $result);
        $this->assertArrayHasKey('active_users', $result);
        $this->assertArrayHasKey('popular_features', $result);
        $this->assertArrayHasKey('color_trends', $result);
        $this->assertIsInt($result['total_palettes']);
        $this->assertIsInt($result['active_users']);
    }

    public function test_export_analytics_returns_valid_format(): void {
        // Arrange
        $format = 'json';
        $options = ['include_trends' => true];
        $expected = '{"data": "sample"}';

        $this->analytics
            ->expects($this->once())
            ->method('export_analytics')
            ->with($format, $options)
            ->willReturn($expected);

        // Act
        $result = $this->analytics->export_analytics($format, $options);

        // Assert
        $this->assertIsString($result);
        $this->assertJson($result);
    }

    /**
     * @dataProvider invalidFormatProvider
     */
    public function test_export_analytics_throws_exception_for_invalid_format(string $format): void {
        $this->analytics
            ->expects($this->once())
            ->method('export_analytics')
            ->with($format)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->analytics->export_analytics($format);
    }

    public function invalidFormatProvider(): array {
        return [
            'empty_format' => [''],
            'invalid_format' => ['invalid'],
            'unsupported_format' => ['xml']
        ];
    }

    /**
     * @dataProvider invalidDateRangeProvider
     */
    public function test_generate_report_validates_date_range(array $criteria): void {
        $this->analytics
            ->expects($this->once())
            ->method('generate_report')
            ->with($criteria)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->analytics->generate_report($criteria);
    }

    public function invalidDateRangeProvider(): array {
        return [
            'future_dates' => [['start_date' => '2025-01-01']],
            'invalid_range' => [['start_date' => '2024-01-31', 'end_date' => '2024-01-01']],
            'invalid_format' => [['start_date' => 'invalid']]
        ];
    }
} 
