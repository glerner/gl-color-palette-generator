<?php
/**
 * Color Palette Cache Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Classes
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Classes\Color_Palette_Cache;
use GL_Color_Palette_Generator\Classes\Color_Palette;


/**
 * Tests for the Color Palette Cache class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Classes
 */
class Test_Color_Palette_Cache extends Unit_Test_Case {
    protected Color_Palette_Cache $cache;
    protected Color_Palette $test_palette;

    public function setUp(): void {
        parent::setUp();
        \Brain\Monkey\setUp();

        $this->cache = new Color_Palette_Cache();
        $this->test_palette = new Color_Palette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'metadata' => ['type' => 'test']
        ]);
    }

    public function tearDown(): void {
        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    public function test_set_palette(): void {
        Functions\expect('wp_cache_set')
            ->once()
            ->andReturn(true);

        $result = $this->cache->set('test_key', $this->test_palette);
        $this->assertTrue($result);
    }

    public function test_get_palette(): void {
        Functions\expect('wp_cache_get')
            ->once()
            ->andReturn([
                'name' => 'Test Palette',
                'colors' => ['#FF0000', '#00FF00', '#0000FF'],
                'metadata' => ['type' => 'test']
            ]);

        $palette = $this->cache->get('test_key');

        $this->assertInstanceOf(Color_Palette::class, $palette);
        $this->assertEquals('Test Palette', $palette->getName());
        $this->assertCount(3, $palette->getColors());
    }

    public function test_get_nonexistent_palette(): void {
        Functions\expect('wp_cache_get')
            ->once()
            ->andReturn(false);

        $palette = $this->cache->get('nonexistent');
        $this->assertNull($palette);
    }

    public function test_delete_palette(): void {
        Functions\expect('wp_cache_delete')
            ->once()
            ->andReturn(true);

        $result = $this->cache->delete('test_key');
        $this->assertTrue($result);
    }

    public function test_has_palette(): void {
        Functions\expect('wp_cache_get')
            ->once()
            ->andReturn(['some' => 'data']);

        $result = $this->cache->has('test_key');
        $this->assertTrue($result);
    }

    public function test_clear_cache(): void {
        Functions\expect('wp_cache_flush')
            ->once()
            ->andReturn(true);

        $result = $this->cache->clear();
        $this->assertTrue($result);
    }

    public function test_get_stats(): void {
        Functions\expect('wp_cache_get')
            ->times(2)
            ->andReturn(['some' => 'data'], false);

        // Generate some stats
        $this->cache->has('exists');
        $this->cache->has('nonexistent');

        $stats = $this->cache->getStats();

        $this->assertArrayHasKey('hits', $stats);
        $this->assertArrayHasKey('misses', $stats);
        $this->assertArrayHasKey('writes', $stats);
        $this->assertArrayHasKey('hit_ratio', $stats);
        $this->assertArrayHasKey('total_operations', $stats);
    }

    public function test_cache_key_sanitization(): void {
        Functions\expect('wp_cache_set')
            ->once()
            ->with('test_key_123', \Mockery::any(), \Mockery::any(), \Mockery::any())
            ->andReturn(true);

        $result = $this->cache->set('Test_Key_123!@#', $this->test_palette);
        $this->assertTrue($result);
    }

    public function test_cache_ttl(): void {
        Functions\expect('wp_cache_set')
            ->once()
            ->with(\Mockery::any(), \Mockery::any(), \Mockery::any(), 7200)
            ->andReturn(true);

        $result = $this->cache->set('test_key', $this->test_palette, 7200);
        $this->assertTrue($result);
    }

    public function test_stats_calculation(): void {
        Functions\expect('wp_cache_get')
            ->times(4)
            ->andReturnValues([
                ['some' => 'data'],  // hit
                false,               // miss
                ['some' => 'data'],  // hit
                ['some' => 'data']   // hit
            ]);

        // Generate some stats
        $this->cache->has('key1');
        $this->cache->has('key2');
        $this->cache->has('key3');
        $this->cache->has('key4');

        $stats = $this->cache->getStats();

        $this->assertEquals(3, $stats['hits']);
        $this->assertEquals(1, $stats['misses']);
        $this->assertEquals(0.75, $stats['hit_ratio']);
    }
}
