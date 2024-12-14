<?php

namespace GL_Color_Palette_Generator\Tests\Cache;

use GL_Color_Palette_Generator\Tests\Test_Case;
use GL_Color_Palette_Generator\Cache\Palette_Cache;

/**
 * Tests for the Palette Cache
 */
class Test_Palette_Cache extends Test_Case {
    private Palette_Cache $cache;
    private string $test_key = 'test_palette_modern_tech';
    private array $test_palette = ['#2C3E50', '#E74C3C', '#ECF0F1', '#3498DB', '#2ECC71'];
    private int $test_duration = 3600; // 1 hour

    /**
     * Set up the test case
     */
    public function setUp(): void {
        parent::setUp();
        $this->cache = new Palette_Cache();
        
        // Mock WordPress transient functions
        \WP_Mock::userFunction('set_transient')->andReturn(true);
        \WP_Mock::userFunction('get_transient')->andReturn(false);
        \WP_Mock::userFunction('delete_transient')->andReturn(true);
    }

    /**
     * Tear down the test case
     */
    public function tearDown(): void {
        parent::tearDown();
    }

    /**
     * Test setting a palette in the cache
     */
    public function test_set_palette() {
        \WP_Mock::userFunction('set_transient')
            ->once()
            ->with(
                $this->stringContains($this->test_key),
                $this->test_palette,
                $this->test_duration
            )
            ->andReturn(true);

        $result = $this->cache->set($this->test_key, $this->test_palette, $this->test_duration);
        $this->assertTrue($result);
    }

    /**
     * Test getting a palette from the cache
     */
    public function test_get_palette() {
        \WP_Mock::userFunction('get_transient')
            ->once()
            ->with($this->stringContains($this->test_key))
            ->andReturn($this->test_palette);

        $result = $this->cache->get($this->test_key);
        $this->assertEquals($this->test_palette, $result);
    }

    /**
     * Test getting a non-existent palette from the cache
     */
    public function test_get_nonexistent_palette() {
        \WP_Mock::userFunction('get_transient')
            ->once()
            ->with($this->stringContains($this->test_key))
            ->andReturn(false);

        $result = $this->cache->get($this->test_key);
        $this->assertFalse($result);
    }

    /**
     * Test deleting a palette from the cache
     */
    public function test_delete_palette() {
        \WP_Mock::userFunction('delete_transient')
            ->once()
            ->with($this->stringContains($this->test_key))
            ->andReturn(true);

        $result = $this->cache->delete($this->test_key);
        $this->assertTrue($result);
    }

    /**
     * Test cache key generation
     */
    public function test_cache_key_generation() {
        $prompt = 'Modern tech company';
        $count = 5;
        $format = 'hex';
        $provider = 'openai';

        $expected_key = md5($prompt . $count . $format . $provider);
        $actual_key = $this->invoke_method($this->cache, 'generate_cache_key', [
            $prompt, $count, $format, $provider
        ]);

        $this->assertEquals($expected_key, $actual_key);
    }

    /**
     * Test cache expiration
     */
    public function test_cache_expiration() {
        // First set the cache
        \WP_Mock::userFunction('set_transient')
            ->once()
            ->with(
                $this->stringContains($this->test_key),
                $this->test_palette,
                1 // Very short duration
            )
            ->andReturn(true);

        $this->cache->set($this->test_key, $this->test_palette, 1);

        // Then try to get it after expiration
        \WP_Mock::userFunction('get_transient')
            ->once()
            ->with($this->stringContains($this->test_key))
            ->andReturn(false);

        sleep(2); // Wait for expiration
        $result = $this->cache->get($this->test_key);
        $this->assertFalse($result);
    }
}
