<?php
/**
 * Palette Manager Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\PaletteManager;

class PaletteManagerTest extends TestCase {
    private $manager;

    public function setUp(): void {
        $this->manager = $this->createMock(PaletteManager::class);
    }

    public function test_create_palette_returns_array(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $metadata = [
            'name' => 'RGB Primary Colors',
            'created' => '2024-12-08 18:38:25'
        ];
        $expected = [
            'id' => 'pal_123',
            'colors' => $colors,
            'metadata' => $metadata
        ];

        $this->manager
            ->expects($this->once())
            ->method('create_palette')
            ->with($colors, $metadata)
            ->willReturn($expected);

        // Act
        $result = $this->manager->create_palette($colors, $metadata);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_palette_returns_array(): void {
        // Arrange
        $palette_id = 'pal_123';
        $expected = [
            'id' => 'pal_123',
            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'metadata' => [
                'name' => 'RGB Primary Colors',
                'created' => '2024-12-08 18:38:25'
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('get_palette')
            ->with($palette_id)
            ->willReturn($expected);

        // Act
        $result = $this->manager->get_palette($palette_id);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_update_palette_returns_true_on_success(): void {
        // Arrange
        $palette_id = 'pal_123';
        $updates = [
            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'metadata' => ['name' => 'Updated RGB Colors']
        ];

        $this->manager
            ->expects($this->once())
            ->method('update_palette')
            ->with($palette_id, $updates)
            ->willReturn(true);

        // Act
        $result = $this->manager->update_palette($palette_id, $updates);

        // Assert
        $this->assertTrue($result);
    }

    public function test_delete_palette_returns_true_on_success(): void {
        // Arrange
        $palette_id = 'pal_123';

        $this->manager
            ->expects($this->once())
            ->method('delete_palette')
            ->with($palette_id)
            ->willReturn(true);

        // Act
        $result = $this->manager->delete_palette($palette_id);

        // Assert
        $this->assertTrue($result);
    }

    public function test_list_palettes_returns_array(): void {
        // Arrange
        $filters = ['category' => 'primary'];
        $expected = [
            [
                'id' => 'pal_123',
                'colors' => ['#FF0000', '#00FF00', '#0000FF'],
                'metadata' => ['name' => 'RGB Primary Colors']
            ],
            [
                'id' => 'pal_124',
                'colors' => ['#FF0000', '#FFFF00', '#0000FF'],
                'metadata' => ['name' => 'RYB Primary Colors']
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('list_palettes')
            ->with($filters)
            ->willReturn($expected);

        // Act
        $result = $this->manager->list_palettes($filters);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidColorsProvider
     */
    public function test_create_palette_throws_exception_for_invalid_colors($colors): void {
        $this->manager
            ->expects($this->once())
            ->method('create_palette')
            ->with($colors, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->manager->create_palette($colors, []);
    }

    /**
     * @dataProvider invalidPaletteIdProvider
     */
    public function test_get_palette_throws_exception_for_invalid_id($palette_id): void {
        $this->manager
            ->expects($this->once())
            ->method('get_palette')
            ->with($palette_id)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->manager->get_palette($palette_id);
    }

    /**
     * @dataProvider invalidUpdatesProvider
     */
    public function test_update_palette_throws_exception_for_invalid_updates($updates): void {
        $this->manager
            ->expects($this->once())
            ->method('update_palette')
            ->with('pal_123', $updates)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->manager->update_palette('pal_123', $updates);
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

    public function invalidPaletteIdProvider(): array {
        return [
            'empty string' => [''],
            'invalid format' => ['invalid_id'],
            'numeric id' => [42],
            'array id' => [[]],
            'null id' => [null]
        ];
    }

    public function invalidUpdatesProvider(): array {
        return [
            'empty array' => [[]],
            'invalid colors' => [['colors' => ['invalid']]],
            'non-array input' => ['invalid'],
            'null input' => [null],
            'invalid structure' => [['invalid' => null]]
        ];
    }
}
