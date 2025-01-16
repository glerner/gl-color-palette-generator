<?php

/**
 * Settings Manager Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\SettingsManager;

class SettingsManagerTest extends TestCase {
    private $manager;

    public function setUp(): void {
        $this->manager = $this->createMock(SettingsManager::class);
    }

    public function test_get_setting_returns_valid_value(): void {
        // Arrange
        $key = 'test_setting';
        $default = 'default_value';
        $expected = 'test_value';

        $this->manager
            ->expects($this->once())
            ->method('get_setting')
            ->with($key, $default)
            ->willReturn($expected);

        // Act
        $result = $this->manager->get_setting($key, $default);

        // Assert
        $this->assertEquals($expected, $result);
    }

    public function test_update_setting_returns_true_on_success(): void {
        // Arrange
        $key = 'test_setting';
        $value = 'new_value';

        $this->manager
            ->expects($this->once())
            ->method('update_setting')
            ->with($key, $value)
            ->willReturn(true);

        // Act
        $result = $this->manager->update_setting($key, $value);

        // Assert
        $this->assertTrue($result);
    }

    public function test_delete_setting_returns_true_on_success(): void {
        // Arrange
        $key = 'test_setting';

        $this->manager
            ->expects($this->once())
            ->method('delete_setting')
            ->with($key)
            ->willReturn(true);

        // Act
        $result = $this->manager->delete_setting($key);

        // Assert
        $this->assertTrue($result);
    }

    public function test_get_all_settings_returns_valid_array(): void {
        // Arrange
        $expected = [
            'setting1' => [
                'value' => 'test_value',
                'last_updated' => '2024-12-08 18:06:04',
                'updated_by' => 'test_user',
                'is_protected' => false
            ],
            'setting2' => [
                'value' => 123,
                'last_updated' => '2024-12-08 18:06:04',
                'updated_by' => 'test_user',
                'is_protected' => true
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('get_all_settings')
            ->willReturn($expected);

        // Act
        $result = $this->manager->get_all_settings();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('setting1', $result);
        $this->assertArrayHasKey('setting2', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_validate_setting_returns_true_for_valid_input(): void {
        // Arrange
        $key = 'test_setting';
        $value = 'valid_value';

        $this->manager
            ->expects($this->once())
            ->method('validate_setting')
            ->with($key, $value)
            ->willReturn(true);

        // Act
        $result = $this->manager->validate_setting($key, $value);

        // Assert
        $this->assertTrue($result);
    }

    public function test_get_setting_metadata_returns_valid_array(): void {
        // Arrange
        $key = 'test_setting';
        $expected = [
            'type' => 'string',
            'description' => 'Test setting description',
            'allowed_values' => ['value1', 'value2'],
            'is_required' => true,
            'default_value' => 'value1'
        ];

        $this->manager
            ->expects($this->once())
            ->method('get_setting_metadata')
            ->with($key)
            ->willReturn($expected);

        // Act
        $result = $this->manager->get_setting_metadata($key);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('allowed_values', $result);
        $this->assertArrayHasKey('is_required', $result);
        $this->assertArrayHasKey('default_value', $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidSettingKeyProvider
     */
    public function test_get_setting_throws_exception_for_invalid_key(string $key): void {
        $this->manager
            ->expects($this->once())
            ->method('get_setting')
            ->with($key)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->manager->get_setting($key);
    }

    public function invalidSettingKeyProvider(): array {
        return [
            'empty key' => [''],
            'invalid characters' => ['invalid@key'],
            'too long' => [str_repeat('a', 256)],
            'reserved prefix' => ['wp_test_setting']
        ];
    }

    /**
     * @dataProvider invalidSettingValueProvider
     */
    public function test_update_setting_throws_exception_for_invalid_value($value): void {
        $this->manager
            ->expects($this->once())
            ->method('update_setting')
            ->with('test_setting', $value)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->manager->update_setting('test_setting', $value);
    }

    public function invalidSettingValueProvider(): array {
        return [
            'invalid object' => [new \stdClass()],
            'invalid resource' => [fopen('php://memory', 'r')],
            'invalid callable' => [function() {}],
            'invalid binary' => [pack('H*', 'deadbeef')]
        ];
    }
}
