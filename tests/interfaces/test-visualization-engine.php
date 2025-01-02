<?php
/**
 * Visualization Engine Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\VisualizationEngine;

class VisualizationEngineTest extends TestCase {
    private $engine;

    public function setUp(): void {
        $this->engine = $this->createMock(VisualizationEngine::class);
    }

    public function test_generate_preview_returns_string(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $options = [
            'format' => 'svg',
            'size' => ['width' => 800, 'height' => 400],
            'layout' => 'grid'
        ];
        $expected = '<svg width="800" height="400">...</svg>';

        $this->engine
            ->expects($this->once())
            ->method('generate_preview')
            ->with($colors, $options)
            ->willReturn($expected);

        // Act
        $result = $this->engine->generate_preview($colors, $options);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_create_color_wheel_returns_string(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $options = [
            'size' => 400,
            'show_labels' => true,
            'highlight_primary' => true
        ];
        $expected = '<svg width="400" height="400">...</svg>';

        $this->engine
            ->expects($this->once())
            ->method('create_color_wheel')
            ->with($colors, $options)
            ->willReturn($expected);

        // Act
        $result = $this->engine->create_color_wheel($colors, $options);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_generate_harmonics_visualization_returns_string(): void {
        // Arrange
        $base_color = '#FF0000';
        $harmony_type = 'complementary';
        $options = [
            'show_connections' => true,
            'include_labels' => true
        ];
        $expected = '<svg width="600" height="400">...</svg>';

        $this->engine
            ->expects($this->once())
            ->method('generate_harmonics_visualization')
            ->with($base_color, $harmony_type, $options)
            ->willReturn($expected);

        // Act
        $result = $this->engine->generate_harmonics_visualization($base_color, $harmony_type, $options);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_create_palette_comparison_returns_string(): void {
        // Arrange
        $palettes = [
            ['#FF0000', '#00FF00', '#0000FF'],
            ['#FF00FF', '#FFFF00', '#00FFFF']
        ];
        $options = [
            'layout' => 'horizontal',
            'show_metrics' => true
        ];
        $expected = '<div class="palette-comparison">...</div>';

        $this->engine
            ->expects($this->once())
            ->method('create_palette_comparison')
            ->with($palettes, $options)
            ->willReturn($expected);

        // Act
        $result = $this->engine->create_palette_comparison($palettes, $options);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_export_visualization_returns_string(): void {
        // Arrange
        $visualization = '<svg>...</svg>';
        $format = 'png';
        $options = [
            'quality' => 90,
            'background' => 'transparent'
        ];
        $expected = 'data:image/png;base64,...';

        $this->engine
            ->expects($this->once())
            ->method('export_visualization')
            ->with($visualization, $format, $options)
            ->willReturn($expected);

        // Act
        $result = $this->engine->export_visualization($visualization, $format, $options);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidColorsProvider
     */
    public function test_generate_preview_throws_exception_for_invalid_colors($colors): void {
        $this->engine
            ->expects($this->once())
            ->method('generate_preview')
            ->with($colors, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->engine->generate_preview($colors, []);
    }

    /**
     * @dataProvider invalidHarmonyTypeProvider
     */
    public function test_generate_harmonics_visualization_throws_exception_for_invalid_harmony_type($harmony_type): void {
        $this->engine
            ->expects($this->once())
            ->method('generate_harmonics_visualization')
            ->with('#FF0000', $harmony_type, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->engine->generate_harmonics_visualization('#FF0000', $harmony_type, []);
    }

    /**
     * @dataProvider invalidVisualizationProvider
     */
    public function test_export_visualization_throws_exception_for_invalid_visualization($visualization): void {
        $this->engine
            ->expects($this->once())
            ->method('export_visualization')
            ->with($visualization, 'png', [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->engine->export_visualization($visualization, 'png', []);
    }

    public function invalidColorsProvider(): array {
        return [
            'empty array' => [[]],
            'invalid hex colors' => [['#GG0000', '#00FF00']],
            'mixed formats' => [['#FF0000', 'rgb(0,255,0)']],
            'non-array input' => ['#FF0000'],
            'null input' => [null]
        ];
    }

    public function invalidHarmonyTypeProvider(): array {
        return [
            'empty string' => [''],
            'invalid type' => ['invalid'],
            'numeric type' => [42],
            'array type' => [[]],
            'null type' => [null]
        ];
    }

    public function invalidVisualizationProvider(): array {
        return [
            'empty string' => [''],
            'invalid svg' => ['<invalid>'],
            'non-string input' => [42],
            'array input' => [[]],
            'null input' => [null]
        ];
    }
}
