<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorPaletteCache;

class ColorPaletteCacheTest extends TestCase {
    private $cache;

    public function setUp(): void {
        $this->cache = $this->createMock(ColorPaletteCache::class);
    }

    public function test_store_caches_palette_data(): void {
        // Arrange
        $key = 'palette:123';
        $data = [
            'colors' => ['#FF0000', '#00FF00'],
            'metadata' => ['name' => 'Test Palette']
        ];

        $options = [
            'ttl' => 3600,
            'group' => 'palettes',
            'tags' => ['active', 'production']
        ];

        $expected = [
            'cached' => true,
            'cache_key' => 'palette:123',
            'metadata' => [
                'created_at' => '2024-01-20T12:00:00Z',
                'expires_at' => '2024-01-20T13:00:00Z',
                'size' => 256
            ],
            'diagnostics' => [
                'storage_type' => 'memory',
                'compression' => false,
                'operation_time' => 0.003
            ]
        ];

        $this->cache
            ->expects($this->once())
            ->method('store')
            ->with($key, $data, $options)
            ->willReturn($expected);

        // Act
        $result = $this->cache->store($key, $data, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('cached', $result);
        $this->assertArrayHasKey('cache_key', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('diagnostics', $result);
        $this->assertTrue($result['cached']);
    }

    public function test_retrieve_returns_cached_data(): void {
        // Arrange
        $key = 'palette:123';
        $options = [
            'refresh' => false,
            'with_meta' => true
        ];

        $expected = [
            'data' => [
                'colors' => ['#FF0000', '#00FF00'],
                'metadata' => ['name' => 'Test Palette']
            ],
            'found' => true,
            'metadata' => [
                'created_at' => '2024-01-20T12:00:00Z',
                'accessed_at' => '2024-01-20T12:30:00Z',
                'hits' => 5
            ],
            'performance' => [
                'lookup_time' => 0.001,
                'transfer_time' => 0.002
            ]
        ];

        $this->cache
            ->expects($this->once())
            ->method('retrieve')
            ->with($key, $options)
            ->willReturn($expected);

        // Act
        $result = $this->cache->retrieve($key, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('found', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('performance', $result);
        $this->assertTrue($result['found']);
    }

    public function test_invalidate_removes_cached_data(): void {
        // Arrange
        $identifier = 'palette:123';
        $options = [
            'scope' => 'key',
            'cascade' => true
        ];

        $expected = [
            'invalidated' => true,
            'affected' => 3,
            'details' => [
                'invalidated_keys' => ['palette:123', 'palette:123:variants'],
                'cascade_depth' => 1
            ],
            'errors' => []
        ];

        $this->cache
            ->expects($this->once())
            ->method('invalidate')
            ->with($identifier, $options)
            ->willReturn($expected);

        // Act
        $result = $this->cache->invalidate($identifier, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('invalidated', $result);
        $this->assertArrayHasKey('affected', $result);
        $this->assertArrayHasKey('details', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertTrue($result['invalidated']);
    }

    public function test_manage_returns_cache_statistics(): void {
        // Arrange
        $operation = 'stats';
        $params = [
            'metrics' => ['hits', 'size', 'efficiency'],
            'detailed' => true
        ];

        $expected = [
            'statistics' => [
                'total_entries' => 150,
                'total_size' => '2.5MB',
                'hit_ratio' => 0.85
            ],
            'health' => [
                'fragmentation' => 0.05,
                'efficiency_score' => 0.92
            ],
            'actions' => [
                'analyzed_entries' => 150,
                'computed_metrics' => ['hits', 'size']
            ],
            'recommendations' => [
                'Consider increasing TTL for frequently accessed items',
                'Run cleanup for expired entries'
            ]
        ];

        $this->cache
            ->expects($this->once())
            ->method('manage')
            ->with($operation, $params)
            ->willReturn($expected);

        // Act
        $result = $this->cache->manage($operation, $params);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('statistics', $result);
        $this->assertArrayHasKey('health', $result);
        $this->assertArrayHasKey('actions', $result);
        $this->assertArrayHasKey('recommendations', $result);
    }

    /**
     * @dataProvider invalidCacheKeyProvider
     */
    public function test_store_validates_cache_key(string $key): void {
        $data = ['test' => 'data'];

        $this->cache
            ->expects($this->once())
            ->method('store')
            ->with($key, $data)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->cache->store($key, $data);
    }

    public function invalidCacheKeyProvider(): array {
        return [
            'empty_key' => [''],
            'invalid_chars' => ['invalid:*key'],
            'too_long' => [str_repeat('a', 300)],
            'spaces' => ['invalid key']
        ];
    }
} 
