<?php
/**
 * Color Palette Analytics Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 */

namespace GL_Color_Palette_Generator\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Analytics;

/**
 * Test Color Palette Analytics Interface implementation
 */
class Test_Color_Palette_Analytics extends TestCase {
    private $analytics;

    public function setUp(): void {
        $this->analytics = $this->createMock(Color_Palette_Analytics::class);
    }

    public function test_analyze_usage_returns_comprehensive_analysis(): void {
        // Arrange
        $palette_id = 'pal_123';
        $options = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'granularity' => 'daily'
        ];

        $expected = [
            'usage_stats' => [
                'total_views' => 1500,
                'unique_users' => 750,
                'peak_usage' => '2024-01-15'
            ],
            'color_usage' => [
                '#FF0000' => ['count' => 500, 'contexts' => ['buttons', 'headers']],
                '#00FF00' => ['count' => 300, 'contexts' => ['backgrounds']]
            ],
            'trends' => [
                'daily_trend' => [
                    '2024-01-01' => 45,
                    '2024-01-02' => 52
                ],
                'growth_rate' => 0.15
            ],
            'segments' => [
                'by_platform' => [
                    'web' => 60,
                    'mobile' => 40
                ],
                'by_context' => [
                    'ui_elements' => 70,
                    'content' => 30
                ]
            ]
        ];

        $this->analytics
            ->expects($this->once())
            ->method('analyze_usage')
            ->with($palette_id, $options)
            ->willReturn($expected);

        // Act
        $result = $this->analytics->analyze_usage($palette_id, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('usage_stats', $result);
        $this->assertArrayHasKey('color_usage', $result);
        $this->assertArrayHasKey('trends', $result);
        $this->assertArrayHasKey('segments', $result);
    }

    public function test_generate_metrics_returns_performance_data(): void {
        // Arrange
        $palette_id = 'pal_123';
        $metrics = [
            'accessibility' => true,
            'load_time' => true
        ];

        $expected = [
            'scores' => [
                'accessibility' => 0.92,
                'performance' => 0.88,
                'consistency' => 0.95
            ],
            'benchmarks' => [
                'industry_avg' => [
                    'accessibility' => 0.85,
                    'performance' => 0.82
                ]
            ],
            'impact' => [
                'load_time' => '+0.2s',
                'memory_usage' => '+50KB'
            ],
            'recommendations' => [
                'Optimize color definitions for faster loading',
                'Consider using CSS variables for better performance'
            ]
        ];

        $this->analytics
            ->expects($this->once())
            ->method('generate_metrics')
            ->with($palette_id, $metrics)
            ->willReturn($expected);

        // Act
        $result = $this->analytics->generate_metrics($palette_id, $metrics);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('scores', $result);
        $this->assertArrayHasKey('benchmarks', $result);
        $this->assertArrayHasKey('impact', $result);
        $this->assertArrayHasKey('recommendations', $result);
    }

    public function test_track_usage_event_records_event_data(): void {
        // Arrange
        $event = [
            'palette_id' => 'pal_123',
            'color' => '#FF0000',
            'context' => 'button_click',
            'platform' => 'web',
            'metadata' => [
                'user_agent' => 'Chrome/98.0',
                'timestamp' => '2024-01-20T12:00:00Z'
            ]
        ];

        $expected = [
            'event_id' => 'evt_abc123',
            'tracked' => true,
            'metadata' => [
                'processed_at' => '2024-01-20T12:00:01Z',
                'tracking_source' => 'web_sdk'
            ],
            'analytics' => [
                'real_time_updates' => [
                    'total_events' => 1501,
                    'active_users' => 75
                ]
            ]
        ];

        $this->analytics
            ->expects($this->once())
            ->method('track_usage_event')
            ->with($event)
            ->willReturn($expected);

        // Act
        $result = $this->analytics->track_usage_event($event);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('event_id', $result);
        $this->assertArrayHasKey('tracked', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('analytics', $result);
        $this->assertTrue($result['tracked']);
    }

    public function test_generate_report_returns_complete_report(): void {
        // Arrange
        $palette_id = 'pal_123';
        $options = [
            'format' => 'detailed',
            'include_viz' => true
        ];

        $expected = [
            'summary' => [
                'period' => 'January 2024',
                'key_metrics' => [
                    'total_usage' => 1500,
                    'performance_score' => 0.92
                ]
            ],
            'metrics' => [
                'usage' => ['daily_avg' => 48.3],
                'performance' => ['load_time' => '0.3s']
            ],
            'trends' => [
                'usage_growth' => '+15%',
                'performance_trend' => 'improving'
            ],
            'visualizations' => [
                'usage_chart' => [
                    'type' => 'line',
                    'data' => [/* chart data */]
                ],
                'heatmap' => [
                    'type' => 'heatmap',
                    'data' => [/* heatmap data */]
                ]
            ]
        ];

        $this->analytics
            ->expects($this->once())
            ->method('generate_report')
            ->with($palette_id, $options)
            ->willReturn($expected);

        // Act
        $result = $this->analytics->generate_report($palette_id, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('metrics', $result);
        $this->assertArrayHasKey('trends', $result);
        $this->assertArrayHasKey('visualizations', $result);
    }

    /**
     * @dataProvider invalidAnalyticsOptionsProvider
     */
    public function test_analyze_usage_validates_options(array $options): void {
        $palette_id = 'pal_123';

        $this->analytics
            ->expects($this->once())
            ->method('analyze_usage')
            ->with($palette_id, $options)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->analytics->analyze_usage($palette_id, $options);
    }

    public function invalidAnalyticsOptionsProvider(): array {
        return [
            'invalid_dates' => [
                ['start_date' => 'invalid', 'end_date' => '2024-01-31']
            ],
            'future_date' => [
                ['start_date' => '2025-01-01']
            ],
            'invalid_granularity' => [
                ['granularity' => 'invalid']
            ],
            'invalid_segments' => [
                ['segments' => 'not-array']
            ]
        ];
    }

    /**
     * @dataProvider invalidEventDataProvider
     */
    public function test_track_usage_event_validates_event_data(array $event): void {
        $this->analytics
            ->expects($this->once())
            ->method('track_usage_event')
            ->with($event)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->analytics->track_usage_event($event);
    }

    public function invalidEventDataProvider(): array {
        return [
            'missing_palette_id' => [
                ['color' => '#FF0000', 'context' => 'test']
            ],
            'invalid_color' => [
                ['palette_id' => 'pal_123', 'color' => 'invalid']
            ],
            'missing_context' => [
                ['palette_id' => 'pal_123', 'color' => '#FF0000']
            ],
            'invalid_metadata' => [
                ['palette_id' => 'pal_123', 'metadata' => 'not-array']
            ]
        ];
    }
}
