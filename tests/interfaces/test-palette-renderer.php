<?php
/**
 * Palette Renderer Interface Tests
 *
 * @package GLColorPalette
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\PaletteRenderer;

class PaletteRendererTest extends TestCase {
    private $renderer;

    public function setUp(): void {
        $this->renderer = $this->createMock(PaletteRenderer::class);
    }

    public function test_render_palette_preview_returns_string(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $options = [
            'width' => 300,
            'height' => 100,
            'format' => 'svg'
        ];
        $expected = '<svg width="300" height="100">...</svg>';

        $this->renderer
            ->expects($this->once())
            ->method('render_palette_preview')
            ->with($colors, $options)
            ->willReturn($expected);

        // Act
        $result = $this->renderer->render_palette_preview($colors, $options);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_render_color_swatch_returns_string(): void {
        // Arrange
        $color = '#FF0000';
        $size = 50;
        $format = 'svg';
        $expected = '<svg width="50" height="50">...</svg>';

        $this->renderer
            ->expects($this->once())
            ->method('render_color_swatch')
            ->with($color, $size, $format)
            ->willReturn($expected);

        // Act
        $result = $this->renderer->render_color_swatch($color, $size, $format);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_render_gradient_preview_returns_string(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $dimensions = ['width' => 300, 'height' => 50];
        $direction = 'horizontal';
        $expected = '<svg width="300" height="50">...</svg>';

        $this->renderer
            ->expects($this->once())
            ->method('render_gradient_preview')
            ->with($colors, $dimensions, $direction)
            ->willReturn($expected);

        // Act
        $result = $this->renderer->render_gradient_preview($colors, $dimensions, $direction);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_render_color_wheel_returns_string(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $size = 200;
        $options = [
            'show_connections' => true,
            'highlight_primary' => true
        ];
        $expected = '<svg width="200" height="200">...</svg>';

        $this->renderer
            ->expects($this->once())
            ->method('render_color_wheel')
            ->with($colors, $size, $options)
            ->willReturn($expected);

        // Act
        $result = $this->renderer->render_color_wheel($colors, $size, $options);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_render_palette_comparison_returns_string(): void {
        // Arrange
        $palette1 = ['#FF0000', '#00FF00', '#0000FF'];
        $palette2 = ['#FF3300', '#33FF00', '#0033FF'];
        $options = [
            'width' => 600,
            'height' => 200,
            'show_labels' => true
        ];
        $expected = '<svg width="600" height="200">...</svg>';

        $this->renderer
            ->expects($this->once())
            ->method('render_palette_comparison')
            ->with($palette1, $palette2, $options)
            ->willReturn($expected);

        // Act
        $result = $this->renderer->render_palette_comparison($palette1, $palette2, $options);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_supported_formats_returns_array(): void {
        // Arrange
        $expected = ['svg', 'png', 'jpg', 'webp'];

        $this->renderer
            ->expects($this->once())
            ->method('get_supported_formats')
            ->willReturn($expected);

        // Act
        $result = $this->renderer->get_supported_formats();

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidColorsProvider
     */
    public function test_render_palette_preview_throws_exception_for_invalid_colors($colors): void {
        $this->renderer
            ->expects($this->once())
            ->method('render_palette_preview')
            ->with($colors, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->renderer->render_palette_preview($colors, []);
    }

    /**
     * @dataProvider invalidColorProvider
     */
    public function test_render_color_swatch_throws_exception_for_invalid_color($color): void {
        $this->renderer
            ->expects($this->once())
            ->method('render_color_swatch')
            ->with($color, 50, 'svg')
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->renderer->render_color_swatch($color, 50, 'svg');
    }

    /**
     * @dataProvider invalidDimensionsProvider
     */
    public function test_render_gradient_preview_throws_exception_for_invalid_dimensions($dimensions): void {
        $colors = ['#FF0000', '#00FF00'];

        $this->renderer
            ->expects($this->once())
            ->method('render_gradient_preview')
            ->with($colors, $dimensions, 'horizontal')
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->renderer->render_gradient_preview($colors, $dimensions, 'horizontal');
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

    public function invalidColorProvider(): array {
        return [
            'empty string' => [''],
            'invalid hex' => ['#GG0000'],
            'wrong format' => ['rgb(0,255,0)'],
            'numeric input' => [42],
            'null input' => [null]
        ];
    }

    public function invalidDimensionsProvider(): array {
        return [
            'empty array' => [[]],
            'missing width' => [['height' => 50]],
            'missing height' => [['width' => 300]],
            'negative values' => [['width' => -300, 'height' => -50]],
            'non-numeric values' => [['width' => 'invalid', 'height' => 'invalid']]
        ];
    }
}
