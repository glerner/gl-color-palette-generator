<?php
/**
 * Performance Monitor Interface Tests
 *
 * @package GLColorPalette
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\PerformanceMonitor;

class PerformanceMonitorTest extends TestCase {
    private $monitor;

    protected function setUp(): void {
        $this->monitor = $this->createMock(PerformanceMonitor::class);
    }

    public function test_start_monitoring_returns_true_on_success(): void {
        // Arrange
        $context = 'color_conversion';
        $metadata = ['operation' => 'rgb_to_hex'];

        $this->monitor
            ->expects($this->once())
            ->method('start_monitoring')
            ->with($context, $metadata)
            ->willReturn(true);

        // Act
        $result = $this->monitor->start_monitoring($context, $metadata);

        // Assert
        $this->assertTrue($result);
    }

    public function test_stop_monitoring_returns_metrics_array(): void {
        // Arrange
        $context = 'color_conversion';
        $expected = [
            'duration_ms' => 150,
            'memory_peak' => 2048576,
            'cpu_usage' => 0.5,
            'timestamp' => '2024-12-08 18:30:50'
        ];

        $this->monitor
            ->expects($this->once())
            ->method('stop_monitoring')
            ->with($context)
            ->willReturn($expected);

        // Act
        $result = $this->monitor->stop_monitoring($context);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('duration_ms', $result);
        $this->assertArrayHasKey('memory_peak', $result);
        $this->assertArrayHasKey('cpu_usage', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_metrics_returns_array(): void {
        // Arrange
        $context = 'color_conversion';
        $timeframe = '1h';
        $expected = [
            'summary' => [
                'avg_duration_ms' => 145,
                'max_memory_peak' => 3145728,
                'avg_cpu_usage' => 0.45
            ],
            'samples' => [
                [
                    'timestamp' => '2024-12-08 18:29:50',
                    'duration_ms' => 140,
                    'memory_peak' => 2097152
                ],
                [
                    'timestamp' => '2024-12-08 18:30:50',
                    'duration_ms' => 150,
                    'memory_peak' => 3145728
                ]
            ]
        ];

        $this->monitor
            ->expects($this->once())
            ->method('get_metrics')
            ->with($context, $timeframe)
            ->willReturn($expected);

        // Act
        $result = $this->monitor->get_metrics($context, $timeframe);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('samples', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_clear_metrics_returns_true_on_success(): void {
        // Arrange
        $context = 'color_conversion';

        $this->monitor
            ->expects($this->once())
            ->method('clear_metrics')
            ->with($context)
            ->willReturn(true);

        // Act
        $result = $this->monitor->clear_metrics($context);

        // Assert
        $this->assertTrue($result);
    }

    public function test_get_performance_report_returns_array(): void {
        // Arrange
        $timeframe = '24h';
        $filters = ['min_duration' => 100];
        $expected = [
            'overall_health' => 'good',
            'metrics_summary' => [
                'total_operations' => 1000,
                'avg_response_time' => 145,
                'error_rate' => 0.01
            ],
            'bottlenecks' => [
                [
                    'context' => 'color_conversion',
                    'avg_duration' => 200,
                    'occurrence' => 50
                ]
            ],
            'recommendations' => [
                'Consider caching frequently converted colors',
                'Optimize RGB to LAB conversion algorithm'
            ]
        ];

        $this->monitor
            ->expects($this->once())
            ->method('get_performance_report')
            ->with($timeframe, $filters)
            ->willReturn($expected);

        // Act
        $result = $this->monitor->get_performance_report($timeframe, $filters);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('overall_health', $result);
        $this->assertArrayHasKey('metrics_summary', $result);
        $this->assertArrayHasKey('bottlenecks', $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidContextProvider
     */
    public function test_start_monitoring_throws_exception_for_invalid_context($context): void {
        $this->monitor
            ->expects($this->once())
            ->method('start_monitoring')
            ->with($context, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->monitor->start_monitoring($context, []);
    }

    /**
     * @dataProvider invalidTimeframeProvider
     */
    public function test_get_metrics_throws_exception_for_invalid_timeframe($timeframe): void {
        $this->monitor
            ->expects($this->once())
            ->method('get_metrics')
            ->with('test_context', $timeframe)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->monitor->get_metrics('test_context', $timeframe);
    }

    public function invalidContextProvider(): array {
        return [
            'empty string' => [''],
            'invalid characters' => ['invalid@context'],
            'too long' => [str_repeat('a', 256)],
            'array context' => [[]],
            'object context' => [new \stdClass()]
        ];
    }

    public function invalidTimeframeProvider(): array {
        return [
            'empty string' => [''],
            'invalid format' => ['1x'],
            'negative value' => ['-1h'],
            'too large' => ['999999h'],
            'invalid unit' => ['24x']
        ];
    }
}
