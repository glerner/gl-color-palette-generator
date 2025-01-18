<?php
/**
 * Tests for Palette_Cache class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Core;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Cache\Palette_Cache;
use GL_Color_Palette_Generator\Core\Logger;

/**
 * Class Test_Palette_Cache
 *
 * @coversDefaultClass \GL_Color_Palette_Generator\Cache\Palette_Cache
 */
class Test_Palette_Cache extends Unit_Test_Case {
    /**
     * Cache instance
     *
     * @var Palette_Cache
     */
    private $cache;

    /**
     * Sample palette parameters
     *
     * @var array
     */
    private $sample_params;

    /**
     * Sample palette data
     *
     * @var array
     */
    private $sample_palette;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();

        $logger = $this->createMock(Logger::class);
        $this->cache = new Palette_Cache($logger);

        $this->sample_params = [
            'base_color' => '#FF0000',
            'scheme' => 'complementary',
            'variation' => 'light'
        ];

        $this->sample_palette = [
            ['#FF0000', 'Red'],
            ['#00FF00', 'Green'],
            ['#0000FF', 'Blue']
        ];
    }

    /**
     * Test cache set and get operations
     *
     * @covers ::set
     * @covers ::get
     * @covers ::generate_cache_key
     */
    public function test_set_and_get() {
        // Test setting cache
        $set_result = $this->cache->set($this->sample_params, $this->sample_palette);
        $this->assertTrue($set_result);

        // Test getting cache
        $cached_palette = $this->cache->get($this->sample_params);
        $this->assertEquals($this->sample_palette, $cached_palette);
    }

    /**
     * Test cache miss
     *
     * @covers ::get
     * @covers ::generate_cache_key
     */
    public function test_cache_miss() {
        $non_existent_params = [
            'base_color' => '#FFFFFF',
            'scheme' => 'unknown'
        ];

        $result = $this->cache->get($non_existent_params);
        $this->assertFalse($result);
    }

    /**
     * Test cache deletion
     *
     * @covers ::delete
     * @covers ::get
     * @covers ::generate_cache_key
     */
    public function test_delete() {
        // Set up cache
        $this->cache->set($this->sample_params, $this->sample_palette);

        // Test deletion
        $delete_result = $this->cache->delete($this->sample_params);
        $this->assertTrue($delete_result);

        // Verify deletion
        $cached_palette = $this->cache->get($this->sample_params);
        $this->assertFalse($cached_palette);
    }

    /**
     * Test clearing all cache
     *
     * @covers ::clear_all
     * @covers ::get
     */
    public function test_clear_all() {
        // Set up multiple cache entries
        $this->cache->set($this->sample_params, $this->sample_palette);
        $this->cache->set(['different' => 'params'], ['different' => 'palette']);

        // Clear all cache
        $clear_result = $this->cache->clear_all();
        $this->assertTrue($clear_result);

        // Verify all cache is cleared
        $this->assertFalse($this->cache->get($this->sample_params));
        $this->assertFalse($this->cache->get(['different' => 'params']));
    }

    /**
     * Test cache existence check
     *
     * @covers ::exists
     * @covers ::generate_cache_key
     */
    public function test_exists() {
        // Test non-existent cache
        $this->assertFalse($this->cache->exists($this->sample_params));

        // Set cache and test existence
        $this->cache->set($this->sample_params, $this->sample_palette);
        $this->assertTrue($this->cache->exists($this->sample_params));

        // Delete cache and verify non-existence
        $this->cache->delete($this->sample_params);
        $this->assertFalse($this->cache->exists($this->sample_params));
    }

    /**
     * Test cache expiration
     *
     * @covers ::set
     * @covers ::get
     */
    public function test_cache_expiration() {
        // Set cache with 1 second expiration
        $this->cache->set($this->sample_params, $this->sample_palette, 1);

        // Verify cache exists
        $this->assertTrue($this->cache->exists($this->sample_params));

        // Wait for cache to expire
        sleep(2);

        // Verify cache has expired
        $this->assertFalse($this->cache->exists($this->sample_params));
    }
}
