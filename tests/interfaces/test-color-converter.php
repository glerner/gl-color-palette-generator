<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorConverter;

class ColorConverterTest extends TestCase {
    private $converter;

    protected function setUp(): void {
        // Create a mock implementation of the interface
        $this->converter = $this->createMock(ColorConverter::class);
    }

    public function test_rgb_to_hex_returns_valid_format(): void {
        // Arrange
        $rgb = [255, 0, 0];
        $expected = '#FF0000';

        $this->converter
            ->expects($this->once())
            ->method('rgb_to_hex')
            ->with($rgb)
            ->willReturn($expected);

        // Act
        $result = $this->converter->rgb_to_hex($rgb);

        // Assert
        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^#[A-F0-9]{6}$/', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_hex_to_rgb_returns_valid_array(): void {
        // Arrange
        $hex = '#FF0000';
        $expected = [255, 0, 0];

        $this->converter
            ->expects($this->once())
            ->method('hex_to_rgb')
            ->with($hex)
            ->willReturn($expected);

        // Act
        $result = $this->converter->hex_to_rgb($hex);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals($expected, $result);
    }

    public function test_rgb_to_hsl_returns_valid_array(): void {
        // Arrange
        $rgb = [255, 0, 0];
        $expected = [0, 100, 50];

        $this->converter
            ->expects($this->once())
            ->method('rgb_to_hsl')
            ->with($rgb)
            ->willReturn($expected);

        // Act
        $result = $this->converter->rgb_to_hsl($rgb);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals($expected, $result);
    }

    public function test_hsl_to_rgb_returns_valid_array(): void {
        // Arrange
        $hsl = [0, 100, 50];
        $expected = [255, 0, 0];

        $this->converter
            ->expects($this->once())
            ->method('hsl_to_rgb')
            ->with($hsl)
            ->willReturn($expected);

        // Act
        $result = $this->converter->hsl_to_rgb($hsl);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidRgbProvider
     */
    public function test_rgb_to_hex_throws_exception_for_invalid_input(array $rgb): void {
        $this->converter
            ->expects($this->once())
            ->method('rgb_to_hex')
            ->with($rgb)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->converter->rgb_to_hex($rgb);
    }

    public function invalidRgbProvider(): array {
        return [
            'negative values' => [[-1, 0, 0]],
            'values too high' => [[256, 0, 0]],
            'wrong array size' => [[255, 0]],
            'non-numeric values' => [['255', '0', '0']]
        ];
    }
} 
