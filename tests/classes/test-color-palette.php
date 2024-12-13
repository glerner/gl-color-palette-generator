<?php

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPalette;

class ColorPaletteTest extends TestCase {
    private ColorPalette $palette;

    public function setUp(): void {
        $this->palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'metadata' => ['source' => 'test']
        ]);
    }

    public function test_constructor_with_default_values(): void {
        $palette = new ColorPalette();
        $this->assertEquals('Untitled Palette', $palette->getName());
        $this->assertEmpty($palette->getColors());
        $this->assertEmpty($palette->getMetadata());
    }

    public function test_constructor_with_custom_values(): void {
        $this->assertEquals('Test Palette', $this->palette->getName());
        $this->assertEquals(['#FF0000', '#00FF00', '#0000FF'], $this->palette->getColors());
        $this->assertEquals(['source' => 'test'], $this->palette->getMetadata());
    }

    public function test_setters_and_getters(): void {
        $this->palette
            ->setName('New Name')
            ->setColors(['#FFFFFF'])
            ->setMetadata(['key' => 'value']);

        $this->assertEquals('New Name', $this->palette->getName());
        $this->assertEquals(['#FFFFFF'], $this->palette->getColors());
        $this->assertEquals(['key' => 'value'], $this->palette->getMetadata());
    }

    public function test_add_and_remove_color(): void {
        $this->palette->addColor('#FFFFFF');
        $this->assertContains('#FFFFFF', $this->palette->getColors());

        $this->palette->removeColor('#FFFFFF');
        $this->assertNotContains('#FFFFFF', $this->palette->getColors());
    }

    public function test_color_normalization(): void {
        $this->palette->setColors(['#fff', ' #AAA ', '123456']);
        $expected = ['#FFFFFF', '#AAAAAA', '#123456'];
        $this->assertEquals($expected, $this->palette->getColors());
    }

    public function test_invalid_color_throws_exception(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->palette->addColor('invalid');
    }

    public function test_to_array(): void {
        $array = $this->palette->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('colors', $array);
        $this->assertArrayHasKey('metadata', $array);
    }

    /**
     * @dataProvider colorFormatProvider
     */
    public function test_various_color_formats(string $input, string $expected): void {
        $this->palette->addColor($input);
        $colors = $this->palette->getColors();
        $this->assertEquals($expected, end($colors));
    }

    public function colorFormatProvider(): array {
        return [
            '6-digit hex' => ['#123456', '#123456'],
            '3-digit hex' => ['#123', '#112233'],
            'no hash' => ['123456', '#123456'],
            'lowercase' => ['#abcdef', '#ABCDEF'],
            'with spaces' => [' #123456 ', '#123456'],
        ];
    }
}
