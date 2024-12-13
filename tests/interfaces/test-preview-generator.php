<?php
/**
 * Preview Generator Interface Tests
 *
 * @package GLColorPalette
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\PreviewGenerator;

class PreviewGeneratorTest extends TestCase {
    private $generator;

    public function setUp(): void {
        $this->generator = $this->createMock(PreviewGenerator::class);
    }

    public function test_generate_preview_returns_valid_string(): void {
        // Arrange
        $palette = [
            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'metadata' => ['theme' => 'primary']
        ];
        $expected = '<div class="palette-preview">...</div>';

        $this->generator
            ->expects($this->once())
            ->method('generate_preview')
            ->with($palette)
            ->willReturn($expected);

        // Act
        $result = $this->generator->generate_preview($palette);

        // Assert
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_preview_formats_returns_valid_array(): void {
        // Arrange
        $expected = [
            'formats' => ['html', 'svg', 'png'],
            'capabilities' => [
                'html' => ['responsive', 'interactive'],
                'svg' => ['scalable', 'editable'],
                'png' => ['fixed-size', 'compressed']
            ],
            'constraints' => [
                'html' => [],
                'svg' => ['max-elements' => 1000],
                'png' => ['max-dimension' => 2048]
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('get_preview_formats')
            ->willReturn($expected);

        // Act
        $result = $this->generator->get_preview_formats();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('formats', $result);
        $this->assertArrayHasKey('capabilities', $result);
        $this->assertArrayHasKey('constraints', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_customize_preview_accepts_valid_options(): void {
        // Arrange
        $options = [
            'format' => 'html',
            'layout' => 'grid',
            'dimensions' => ['width' => 800, 'height' => 600],
            'typography' => [
                'font' => 'Arial',
                'size' => '14px'
            ],
            'show_labels' => true,
            'show_values' => true,
            'custom_css' => [
                '.palette-preview' => [
                    'border-radius' => '8px'
                ]
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('customize_preview')
            ->with($options);

        // Act & Assert
        $this->generator->customize_preview($options);
    }

    public function test_generate_mock_preview_returns_valid_string(): void {
        // Arrange
        $palette = ['#FF0000', '#00FF00', '#0000FF'];
        $content_type = 'website';
        $expected = '<div class="mock-preview website">...</div>';

        $this->generator
            ->expects($this->once())
            ->method('generate_mock_preview')
            ->with($palette, $content_type)
            ->willReturn($expected);

        // Act
        $result = $this->generator->generate_mock_preview($palette, $content_type);

        // Assert
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_preview_as_returns_valid_string(): void {
        // Arrange
        $palette = ['#FF0000', '#00FF00', '#0000FF'];
        $format = 'png';
        $options = ['quality' => 90];
        $expected = 'data:image/png;base64,...';

        $this->generator
            ->expects($this->once())
            ->method('get_preview_as')
            ->with($palette, $format, $options)
            ->willReturn($expected);

        // Act
        $result = $this->generator->get_preview_as($palette, $format, $options);

        // Assert
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidPaletteProvider
     */
    public function test_generate_preview_throws_exception_for_invalid_palette(array $palette): void {
        $this->generator
            ->expects($this->once())
            ->method('generate_preview')
            ->with($palette)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate_preview($palette);
    }

    public function invalidPaletteProvider(): array {
        return [
            'empty palette' => [[]],
            'invalid colors' => [['colors' => ['invalid']]],
            'missing colors' => [['metadata' => []]],
            'invalid metadata' => [['colors' => ['#FF0000'], 'metadata' => 'invalid']]
        ];
    }

    /**
     * @dataProvider invalidOptionsProvider
     */
    public function test_customize_preview_throws_exception_for_invalid_options(array $options): void {
        $this->generator
            ->expects($this->once())
            ->method('customize_preview')
            ->with($options)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->generator->customize_preview($options);
    }

    public function invalidOptionsProvider(): array {
        return [
            'invalid format' => [['format' => 'invalid']],
            'invalid dimensions' => [['dimensions' => 'invalid']],
            'invalid typography' => [['typography' => 'invalid']],
            'invalid show_labels' => [['show_labels' => 'invalid']],
            'invalid custom_css' => [['custom_css' => 'invalid']]
        ];
    }
}
