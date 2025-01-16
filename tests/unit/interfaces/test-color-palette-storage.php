<?php

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Interfaces\Color_Palette_Storage;
use PHPUnit\Framework\TestCase;

class Color_Palette_Storage_Test extends TestCase {
    private $storage;

    public function setUp(): void {
        $this->storage = $this->createMock(Color_Palette_Storage::class);
    }

    public function test_store_in_db_saves_palette(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $options = [
            'table' => 'palettes',
            'indexes' => ['name'],
            'versioning' => true
        ];

        $expected = [
            'id' => 123,
            'success' => true,
            'version' => [
                'number' => 1,
                'created_at' => '2024-01-20T12:00:00Z'
            ],
            'metadata' => [
                'stored_at' => '2024-01-20T12:00:00Z',
                'storage_type' => 'mysql'
            ]
        ];

        $this->storage
            ->expects($this->once())
            ->method('store_in_db')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->storage->store_in_db($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['success']);
    }

    public function test_retrieve_from_db_loads_palette(): void {
        // Arrange
        $id = 123;
        $options = [
            'fields' => ['name', 'colors'],
            'version' => 'latest'
        ];

        $expected = [
            'palette' => [
                'name' => 'Test Palette',
                'colors' => ['#FF0000', '#00FF00']
            ],
            'version' => [
                'number' => 1,
                'is_latest' => true
            ],
            'related' => [
                'tags' => ['modern', 'bright']
            ],
            'metadata' => [
                'retrieved_at' => '2024-01-20T12:00:00Z',
                'source' => 'primary_db'
            ]
        ];

        $this->storage
            ->expects($this->once())
            ->method('retrieve_from_db')
            ->with($id, $options)
            ->willReturn($expected);

        // Act
        $result = $this->storage->retrieve_from_db($id, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palette', $result);
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayHasKey('related', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_store_in_cache_caches_palette(): void {
        // Arrange
        $key = 'palette:123';
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $options = [
            'ttl' => 3600,
            'group' => 'palettes',
            'tags' => ['active']
        ];

        $expected = [
            'cached' => true,
            'key' => 'palette:123',
            'info' => [
                'expires_at' => '2024-01-20T13:00:00Z',
                'size' => 256
            ],
            'metadata' => [
                'cached_at' => '2024-01-20T12:00:00Z',
                'cache_driver' => 'redis'
            ]
        ];

        $this->storage
            ->expects($this->once())
            ->method('store_in_cache')
            ->with($key, $palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->storage->store_in_cache($key, $palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('cached', $result);
        $this->assertArrayHasKey('key', $result);
        $this->assertArrayHasKey('info', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['cached']);
    }

    public function test_retrieve_from_cache_loads_cached_palette(): void {
        // Arrange
        $key = 'palette:123';
        $options = [
            'refresh' => false,
            'metadata' => true
        ];

        $expected = [
            'palette' => [
                'name' => 'Test Palette',
                'colors' => ['#FF0000', '#00FF00']
            ],
            'hit' => true,
            'source' => [
                'type' => 'cache',
                'driver' => 'redis'
            ],
            'metadata' => [
                'cached_at' => '2024-01-20T12:00:00Z',
                'ttl_remaining' => 3000
            ]
        ];

        $this->storage
            ->expects($this->once())
            ->method('retrieve_from_cache')
            ->with($key, $options)
            ->willReturn($expected);

        // Act
        $result = $this->storage->retrieve_from_cache($key, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palette', $result);
        $this->assertArrayHasKey('hit', $result);
        $this->assertArrayHasKey('source', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['hit']);
    }

    /**
     * @dataProvider invalidPaletteDataProvider
     */
    public function test_store_in_db_validates_palette(array $palette): void {
        $this->storage
            ->expects($this->once())
            ->method('store_in_db')
            ->with($palette)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->storage->store_in_db($palette);
    }

    public function invalidPaletteDataProvider(): array {
        return [
            'empty_palette' => [[]],
            'missing_name' => [['colors' => ['#FF0000']]],
            'missing_colors' => [['name' => 'Test']],
            'invalid_colors' => [['name' => 'Test', 'colors' => 'not-array']]
        ];
    }

    /**
     * @dataProvider invalidCacheKeyProvider
     */
    public function test_store_in_cache_validates_key(string $key): void {
        $palette = ['name' => 'Test', 'colors' => ['#FF0000']];

        $this->storage
            ->expects($this->once())
            ->method('store_in_cache')
            ->with($key, $palette)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->storage->store_in_cache($key, $palette);
    }

    public function invalidCacheKeyProvider(): array {
        return [
            'empty_key' => [''],
            'invalid_chars' => ['palette:*123'],
            'too_long' => [str_repeat('a', 256)],
            'spaces' => ['palette 123']
        ];
    }
} 
