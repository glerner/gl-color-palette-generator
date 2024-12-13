<?php
/**
 * Error Reporter Interface Tests
 *
 * @package GLColorPalette
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ErrorReporter;

class ErrorReporterTest extends TestCase {
    private $reporter;

    public function setUp(): void {
        $this->reporter = $this->createMock(ErrorReporter::class);
    }

    public function test_report_error_returns_true_on_success(): void {
        // Arrange
        $error = new \Exception('Test error');
        $context = [
            'component' => 'color_converter',
            'function' => 'rgb_to_hex',
            'input' => ['r' => 255, 'g' => 0, 'b' => 0]
        ];

        $this->reporter
            ->expects($this->once())
            ->method('report_error')
            ->with($error, $context)
            ->willReturn(true);

        // Act
        $result = $this->reporter->report_error($error, $context);

        // Assert
        $this->assertTrue($result);
    }

    public function test_get_error_summary_returns_array(): void {
        // Arrange
        $timeframe = '24h';
        $expected = [
            'total_errors' => 5,
            'error_types' => [
                'InvalidArgumentException' => 3,
                'RuntimeException' => 2
            ],
            'most_affected_components' => [
                'color_converter' => 2,
                'palette_generator' => 3
            ],
            'error_trends' => [
                'increasing' => false,
                'peak_time' => '2024-12-08 18:00:00'
            ]
        ];

        $this->reporter
            ->expects($this->once())
            ->method('get_error_summary')
            ->with($timeframe)
            ->willReturn($expected);

        // Act
        $result = $this->reporter->get_error_summary($timeframe);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_errors', $result);
        $this->assertArrayHasKey('error_types', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_error_details_returns_array(): void {
        // Arrange
        $error_id = 'err_123';
        $expected = [
            'id' => 'err_123',
            'type' => 'InvalidArgumentException',
            'message' => 'Invalid color format',
            'timestamp' => '2024-12-08 18:38:25',
            'stack_trace' => [
                'file' => 'ColorConverter.php',
                'line' => 42,
                'function' => 'validate_color'
            ],
            'context' => [
                'input' => '#GG0000',
                'component' => 'color_converter'
            ]
        ];

        $this->reporter
            ->expects($this->once())
            ->method('get_error_details')
            ->with($error_id)
            ->willReturn($expected);

        // Act
        $result = $this->reporter->get_error_details($error_id);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_clear_error_history_returns_true_on_success(): void {
        // Arrange
        $timeframe = '7d';

        $this->reporter
            ->expects($this->once())
            ->method('clear_error_history')
            ->with($timeframe)
            ->willReturn(true);

        // Act
        $result = $this->reporter->clear_error_history($timeframe);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @dataProvider invalidErrorProvider
     */
    public function test_report_error_throws_exception_for_invalid_error($error): void {
        $this->reporter
            ->expects($this->once())
            ->method('report_error')
            ->with($error, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->reporter->report_error($error, []);
    }

    /**
     * @dataProvider invalidContextProvider
     */
    public function test_report_error_throws_exception_for_invalid_context($context): void {
        $error = new \Exception('Test error');
        
        $this->reporter
            ->expects($this->once())
            ->method('report_error')
            ->with($error, $context)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->reporter->report_error($error, $context);
    }

    /**
     * @dataProvider invalidTimeframeProvider
     */
    public function test_get_error_summary_throws_exception_for_invalid_timeframe($timeframe): void {
        $this->reporter
            ->expects($this->once())
            ->method('get_error_summary')
            ->with($timeframe)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->reporter->get_error_summary($timeframe);
    }

    public function invalidErrorProvider(): array {
        return [
            'string error' => ['error'],
            'array error' => [['error']],
            'null error' => [null],
            'integer error' => [42],
            'boolean error' => [true]
        ];
    }

    public function invalidContextProvider(): array {
        return [
            'string context' => ['context'],
            'numeric context' => [42],
            'null context' => [null],
            'invalid structure' => [['invalid' => null]],
            'missing required' => [['component' => null]]
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
