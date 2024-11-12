<?php

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPalette;

class ColorPaletteTest extends TestCase {
    private $palette;

    protected function setUp(): void {
        $this->palette = new ColorPalette([
            'id' => 'test_pal_123',
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'metadata' => [
                'created_at' => '2024-01-20 12:00:00',
                'updated_at' => '2024-01-20 12:00:00',
                'version' => '1.0'
            ]
        ]);
    }

    public function test_constructor_creates_palette_with_defaults(): void {
        // Arrange & Act
        $palette = new ColorPalette();

        // Assert
        $this->assertNotEmpty($palette->get_id());
        $this->assertStringStartsWith('pal_', $palette->get_id());
        $this->assertEmpty($palette->get_name());
        $this->assertEmpty($palette->get_colors());
        $this->assertNotEmpty($palette->get_metadata());
    }

    public function test_constructor_creates_palette_with_data(): void {
        // Assert
        $this->assertEquals('test_pal_123', $this->palette->get_id());
        $this->assertEquals('Test Palette', $this->palette->get_name());
        $this->assertCount(3, $this->palette->get_colors());
        $this->assertArrayHasKey('version', $this->palette->get_metadata());
    }

    public function test_get_color_returns_correct_color(): void {
        // Act & Assert
        $this->assertEquals('#FF0000', $this->palette->get_color(0));
        $this->assertEquals('#00FF00', $this->palette->get_color(1));
        $this->assertEquals('#0000FF', $this->palette->get_color(2));
        $this->assertNull($this->palette->get_color(3));
    }

    public function test_add_color_appends_color(): void {
        // Arrange
        $new_color = '#FFFF00';

        // Act
        $this->palette->add_color($new_color);

        // Assert
        $this->assertCount(4, $this->palette->get_colors());
        $this->assertEquals($new_color, $this->palette->get_color(3));
        $this->assertNotEquals(
            $this->palette->get_metadata()['created_at'],
            $this->palette->get_metadata()['updated_at']
        );
    }

    public function test_update_color_modifies_existing_color(): void {
        // Arrange
        $new_color = '#FFFF00';

        // Act
        $this->palette->update_color(1, $new_color);

        // Assert
        $this->assertEquals($new_color, $this->palette->get_color(1));
        $this->assertNotEquals(
            $this->palette->get_metadata()['created_at'],
            $this->palette->get_metadata()['updated_at']
        );
    }

    public function test_remove_color_deletes_color(): void {
        // Act
        $this->palette->remove_color(1);

        // Assert
        $this->assertCount(2, $this->palette->get_colors());
        $this->assertEquals('#0000FF', $this->palette->get_color(1));
        $this->assertNotEquals(
            $this->palette->get_metadata()['created_at'],
            $this->palette->get_metadata()['updated_at']
        );
    }

    public function test_set_name_updates_name(): void {
        // Arrange
        $new_name = 'Updated Test Palette';

        // Act
        $this->palette->set_name($new_name);

        // Assert
        $this->assertEquals($new_name, $this->palette->get_name());
        $this->assertNotEquals(
            $this->palette->get_metadata()['created_at'],
            $this->palette->get_metadata()['updated_at']
        );
    }

    public function test_update_metadata_field_modifies_metadata(): void {
        // Arrange
        $key = 'test_key';
        $value = 'test_value';

        // Act
        $this->palette->update_metadata_field($key, $value);

        // Assert
        $metadata = $this->palette->get_metadata();
        $this->assertArrayHasKey($key, $metadata);
        $this->assertEquals($value, $metadata[$key]);
        $this->assertNotEquals($metadata['created_at'], $metadata['updated_at']);
    }

    public function test_to_array_returns_complete_data(): void {
        // Act
        $data = $this->palette->to_array();

        // Assert
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('colors', $data);
        $this->assertArrayHasKey('metadata', $data);
        $this->assertEquals('test_pal_123', $data['id']);
        $this->assertEquals('Test Palette', $data['name']);
        $this->assertCount(3, $data['colors']);
    }

    /**
     * @dataProvider invalidColorProvider
     */
    public function test_add_color_validates_input(string $invalid_color): void {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        $this->palette->add_color($invalid_color);
    }

    public function invalidColorProvider(): array {
        return [
            'empty_color' => [''],
            'invalid_hex' => ['#XYZ'],
            'no_hash' => ['FF0000'],
            'wrong_length' => ['#FF'],
            'invalid_chars' => ['#GG0000']
        ];
    }

    public function test_update_color_validates_index(): void {
        // Assert
        $this->expectException(\OutOfRangeException::class);

        // Act
        $this->palette->update_color(99, '#FF0000');
    }

    public function test_remove_color_validates_index(): void {
        // Assert
        $this->expectException(\OutOfRangeException::class);

        // Act
        $this->palette->remove_color(99);
    }

    /**
     * @dataProvider validColorProvider
     */
    public function test_add_color_accepts_valid_colors(string $valid_color): void {
        // Act
        $this->palette->add_color($valid_color);
        $colors = $this->palette->get_colors();

        // Assert
        $this->assertContains($valid_color, $colors);
    }

    public function validColorProvider(): array {
        return [
            'three_digit_hex' => ['#FFF'],
            'six_digit_hex' => ['#FF00FF'],
            'lowercase_hex' => ['#ff00ff'],
            'mixed_case_hex' => ['#Ff00Ff']
        ];
    }

    public function test_metadata_timestamps_are_updated(): void {
        // Arrange
        $original_updated_at = $this->palette->get_metadata()['updated_at'];
        sleep(1); // Ensure timestamp difference

        // Act
        $this->palette->add_color('#FFFF00');
        $new_updated_at = $this->palette->get_metadata()['updated_at'];

        // Assert
        $this->assertNotEquals($original_updated_at, $new_updated_at);
    }
} 
