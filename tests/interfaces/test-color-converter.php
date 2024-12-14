<?php
/**
 * Color Converter Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\Color_Converter;

class Test_Color_Converter extends TestCase {
    private $converter;

    public function setUp(): void {
        // Create a mock implementation of the interface
        $this->converter = $this->createMock(Color_Converter::class);
    }

    public function test_convert_rgb_to_hex_returns_valid_format(): void {
        // Arrange
        $rgb = ['r' => 255, 'g' => 0, 'b' => 0];
        $expected = '#FF0000';

        $this->converter
            ->expects($this->once())
            ->method('convert_rgb_to_hex')
            ->with($rgb)
            ->willReturn($expected);

        // Act
        $result = $this->converter->convert_rgb_to_hex($rgb);

        // Assert
        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^#[A-F0-9]{6}$/', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_convert_hex_to_rgb_returns_valid_array(): void {
        // Arrange
        $hex = '#FF0000';
        $expected = ['r' => 255, 'g' => 0, 'b' => 0];

        $this->converter
            ->expects($this->once())
            ->method('convert_hex_to_rgb')
            ->with($hex)
            ->willReturn($expected);

        // Act
        $result = $this->converter->convert_hex_to_rgb($hex);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('r', $result);
        $this->assertArrayHasKey('g', $result);
        $this->assertArrayHasKey('b', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_convert_rgb_to_hsl_returns_valid_array(): void {
        // Arrange
        $rgb = ['r' => 255, 'g' => 0, 'b' => 0];
        $expected = ['h' => 0, 's' => 100, 'l' => 50];

        $this->converter
            ->expects($this->once())
            ->method('convert_rgb_to_hsl')
            ->with($rgb)
            ->willReturn($expected);

        // Act
        $result = $this->converter->convert_rgb_to_hsl($rgb);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('h', $result);
        $this->assertArrayHasKey('s', $result);
        $this->assertArrayHasKey('l', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_convert_hsl_to_rgb_returns_valid_array(): void {
        // Arrange
        $hsl = ['h' => 0, 's' => 100, 'l' => 50];
        $expected = ['r' => 255, 'g' => 0, 'b' => 0];

        $this->converter
            ->expects($this->once())
            ->method('convert_hsl_to_rgb')
            ->with($hsl)
            ->willReturn($expected);

        // Act
        $result = $this->converter->convert_hsl_to_rgb($hsl);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('r', $result);
        $this->assertArrayHasKey('g', $result);
        $this->assertArrayHasKey('b', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_convert_rgb_to_lab_returns_valid_array(): void {
        // Arrange
        $rgb = ['r' => 255, 'g' => 0, 'b' => 0];
        $expected = ['l' => 53.23, 'a' => 80.11, 'b' => 67.22];

        $this->converter
            ->expects($this->once())
            ->method('convert_rgb_to_lab')
            ->with($rgb)
            ->willReturn($expected);

        // Act
        $result = $this->converter->convert_rgb_to_lab($rgb);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('l', $result);
        $this->assertArrayHasKey('a', $result);
        $this->assertArrayHasKey('b', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_convert_lab_to_rgb_returns_valid_array(): void {
        // Arrange
        $lab = ['l' => 53.23, 'a' => 80.11, 'b' => 67.22];
        $expected = ['r' => 255, 'g' => 0, 'b' => 0];

        $this->converter
            ->expects($this->once())
            ->method('convert_lab_to_rgb')
            ->with($lab)
            ->willReturn($expected);

        // Act
        $result = $this->converter->convert_lab_to_rgb($lab);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('r', $result);
        $this->assertArrayHasKey('g', $result);
        $this->assertArrayHasKey('b', $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidRgbProvider
     */
    public function test_convert_rgb_to_hex_throws_exception_for_invalid_input(array $rgb): void {
        $this->converter
            ->expects($this->once())
            ->method('convert_rgb_to_hex')
            ->with($rgb)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->converter->convert_rgb_to_hex($rgb);
    }

    /**
     * @dataProvider invalidHexProvider
     */
    public function test_convert_hex_to_rgb_throws_exception_for_invalid_input(string $hex): void {
        $this->converter
            ->expects($this->once())
            ->method('convert_hex_to_rgb')
            ->with($hex)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->converter->convert_hex_to_rgb($hex);
    }

    public function invalidRgbProvider(): array {
        return [
            'negative values' => [['r' => -1, 'g' => 0, 'b' => 0]],
            'values too high' => [['r' => 256, 'g' => 0, 'b' => 0]],
            'missing component' => [['r' => 255, 'g' => 0]],
            'invalid component' => [['r' => 255, 'g' => 0, 'x' => 0]],
            'non-numeric values' => [['r' => 'abc', 'g' => 0, 'b' => 0]]
        ];
    }

    public function invalidHexProvider(): array {
        return [
            'too short' => ['#FF'],
            'too long' => ['#FF00001'],
            'invalid characters' => ['#GG0000'],
            'missing hash' => ['FF0000'],
            'empty string' => [''],
            'invalid format' => ['FF00']
        ];
    }
}
