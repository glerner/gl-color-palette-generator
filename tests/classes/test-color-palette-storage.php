<?php
namespace GLColorPalette\Tests;

use GLColorPalette\Color_Palette_Storage;
use GLColorPalette\Color_Palette;
use WP_UnitTestCase;

/**
 * @covers \GLColorPalette\Color_Palette_Storage
 */
class Color_Palette_Storage_Test extends WP_UnitTestCase {
    private $storage;
    private $test_palette;

    public function setUp(): void {
        parent::setUp();
        $this->storage = new Color_Palette_Storage();
        $this->test_palette = new Color_Palette(
            ['#FF0000', '#00FF00', '#0000FF'],
            ['name' => 'Test Palette', 'theme' => 'test']
        );

        // Clean up any test data
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'gl_color_palette_%'");
        $wpdb->query("DELETE FROM {$wpdb->prefix}color_palettes WHERE 1=1");
    }

    /**
     * @test
     */
    public function it_stores_palette_in_options() {
        $id = $this->storage->store($this->test_palette);

        $this->assertNotWPError($id);
        $this->assertNotEmpty($id);

        $option = get_option('gl_color_palette_' . $id);
        $this->assertNotFalse($option);
        $this->assertEquals($this->test_palette->get_colors(), $option['colors']);
    }

    /**
     * @test
     */
    public function it_retrieves_palette_from_options() {
        $id = $this->storage->store($this->test_palette);
        $retrieved = $this->storage->get($id);

        $this->assertInstanceOf(Color_Palette::class, $retrieved);
        $this->assertEquals($this->test_palette->get_colors(), $retrieved->get_colors());
        $this->assertEquals($this->test_palette->get_metadata(), $retrieved->get_metadata());
    }

    /**
     * @test
     */
    public function it_stores_palette_in_database() {
        $this->storage->use_database(true);
        $id = $this->storage->store($this->test_palette);

        global $wpdb;
        $stored = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}color_palettes WHERE id = %s",
                $id
            )
        );

        $this->assertNotNull($stored);
        $this->assertEquals(
            json_encode($this->test_palette->get_colors()),
            $stored->colors
        );
    }

    /**
     * @test
     */
    public function it_handles_missing_palettes() {
        $result = $this->storage->get('non-existent-id');
        $this->assertWPError($result);
        $this->assertEquals('not_found', $result->get_error_code());
    }

    /**
     * @test
     */
    public function it_deletes_palettes() {
        $id = $this->storage->store($this->test_palette);
        $deleted = $this->storage->delete($id);

        $this->assertTrue($deleted);
        $this->assertWPError($this->storage->get($id));
    }

    /**
     * @test
     */
    public function it_uses_cache() {
        $id = $this->storage->store($this->test_palette);

        // First retrieval should cache
        $first = $this->storage->get($id);

        // Delete from options to verify cache usage
        delete_option('gl_color_palette_' . $id);

        // Should still retrieve from cache
        $second = $this->storage->get($id);

        $this->assertEquals($first->get_colors(), $second->get_colors());
    }

    /**
     * @test
     */
    public function it_updates_existing_palettes() {
        $id = $this->storage->store($this->test_palette);

        $updated_palette = new Color_Palette(
            ['#000000', '#FFFFFF'],
            ['name' => 'Updated Palette']
        );
        $updated_id = $this->storage->store($updated_palette, $id);

        $this->assertEquals($id, $updated_id);
        $retrieved = $this->storage->get($id);
        $this->assertEquals($updated_palette->get_colors(), $retrieved->get_colors());
    }

    /**
     * @test
     * @testdox List method returns paginated palettes from options
     */
    public function it_lists_palettes_from_options(): void {
        // Create test palettes
        $palettes = [
            new Color_Palette(['#FF0000'], ['name' => 'Red']),
            new Color_Palette(['#00FF00'], ['name' => 'Green']),
            new Color_Palette(['#0000FF'], ['name' => 'Blue'])
        ];

        foreach ($palettes as $palette) {
            $this->storage->store($palette);
        }

        // Test pagination
        $result = $this->storage->list(['limit' => 2]);
        $this->assertCount(2, $result);

        $result = $this->storage->list(['offset' => 2, 'limit' => 2]);
        $this->assertCount(1, $result);

        // Test ordering
        $result = $this->storage->list(['order' => 'ASC']);
        $this->assertEquals('Red', $result[0]->get_metadata('name'));
    }

    /**
     * @test
     * @testdox List method returns palettes filtered by metadata
     */
    public function it_filters_palettes_by_metadata(): void {
        $palettes = [
            new Color_Palette(['#FF0000'], ['type' => 'warm', 'theme' => 'modern']),
            new Color_Palette(['#00FF00'], ['type' => 'cool', 'theme' => 'modern']),
            new Color_Palette(['#0000FF'], ['type' => 'cool', 'theme' => 'classic'])
        ];

        foreach ($palettes as $palette) {
            $this->storage->store($palette);
        }

        $result = $this->storage->list(['meta' => ['type' => 'cool']]);
        $this->assertCount(2, $result);

        $result = $this->storage->list(['meta' => [
            'type' => 'cool',
            'theme' => 'modern'
        ]]);
        $this->assertCount(1, $result);
    }

    /**
     * @test
     * @testdox List method works with database storage
     */
    public function it_lists_palettes_from_database(): void {
        $this->storage->use_database(true);

        // Create test palettes
        $palettes = [
            new Color_Palette(['#FF0000'], ['name' => 'Red']),
            new Color_Palette(['#00FF00'], ['name' => 'Green']),
            new Color_Palette(['#0000FF'], ['name' => 'Blue'])
        ];

        foreach ($palettes as $palette) {
            $this->storage->store($palette);
        }

        $result = $this->storage->list(['limit' => 2]);
        $this->assertCount(2, $result);

        // Verify order
        $result = $this->storage->list(['order' => 'ASC']);
        $this->assertEquals('Red', $result[0]->get_metadata('name'));
    }

    /**
     * @test
     * @testdox Search finds palettes by metadata
     */
    public function it_searches_palettes_by_metadata(): void {
        $palettes = [
            new Color_Palette(['#FF0000'], ['name' => 'Warm Red Theme', 'theme' => 'modern']),
            new Color_Palette(['#00FF00'], ['name' => 'Cool Green Theme', 'theme' => 'modern']),
            new Color_Palette(['#0000FF'], ['name' => 'Classic Blue', 'theme' => 'classic'])
        ];

        foreach ($palettes as $palette) {
            $this->storage->store($palette);
        }

        // Search by name
        $result = $this->storage->search('Red', ['field' => 'name']);
        $this->assertCount(1, $result);
        $this->assertEquals('Warm Red Theme', $result[0]->get_metadata('name'));

        // Search by theme
        $result = $this->storage->search('modern', ['field' => 'metadata']);
        $this->assertCount(2, $result);
    }

    /**
     * @test
     * @testdox Search works with database storage
     */
    public function it_searches_palettes_in_database(): void {
        $this->storage->use_database(true);

        $palettes = [
            new Color_Palette(['#FF0000'], ['name' => 'Sunset Theme', 'mood' => 'warm']),
            new Color_Palette(['#00FF00'], ['name' => 'Forest Theme', 'mood' => 'calm']),
            new Color_Palette(['#0000FF'], ['name' => 'Ocean Theme', 'mood' => 'calm'])
        ];

        foreach ($palettes as $palette) {
            $this->storage->store($palette);
        }

        // Search by mood
        $result = $this->storage->search('calm', ['field' => 'metadata']);
        $this->assertCount(2, $result);

        // Test limit
        $result = $this->storage->search('Theme', ['field' => 'name', 'limit' => 2]);
        $this->assertCount(2, $result);
    }

    /**
     * @test
     * @testdox Search handles empty and invalid queries
     */
    public function it_handles_invalid_search_queries(): void {
        $palette = new Color_Palette(['#FF0000'], ['name' => 'Test Palette']);
        $this->storage->store($palette);

        // Empty query
        $result = $this->storage->search('');
        $this->assertEmpty($result);

        // Invalid field
        $result = $this->storage->search('test', ['field' => 'invalid_field']);
        $this->assertEmpty($result);

        // SQL injection attempt
        $result = $this->storage->search("' OR '1'='1");
        $this->assertEmpty($result);
    }

    /**
     * @test
     * @testdox Search respects ordering
     */
    public function it_orders_search_results(): void {
        $palettes = [
            new Color_Palette(['#FF0000'], ['name' => 'A Red Theme']),
            new Color_Palette(['#00FF00'], ['name' => 'B Green Theme']),
            new Color_Palette(['#0000FF'], ['name' => 'C Blue Theme'])
        ];

        foreach ($palettes as $palette) {
            $this->storage->store($palette);
        }

        // Test ascending order
        $result = $this->storage->search('Theme', [
            'field' => 'name',
            'order' => 'ASC'
        ]);
        $this->assertEquals('A Red Theme', $result[0]->get_metadata('name'));

        // Test descending order
        $result = $this->storage->search('Theme', [
            'field' => 'name',
            'order' => 'DESC'
        ]);
        $this->assertEquals('C Blue Theme', $result[0]->get_metadata('name'));
    }

    /**
     * @test
     * @testdox Count returns correct total for options storage
     */
    public function it_counts_palettes_in_options(): void {
        // Create test palettes
        for ($i = 0; $i < 5; $i++) {
            $palette = new Color_Palette(
                ['#FF0000'],
                ['theme' => $i < 3 ? 'modern' : 'classic']
            );
            $this->storage->store($palette);
        }

        // Test total count
        $this->assertEquals(5, $this->storage->count());

        // Test filtered count
        $this->assertEquals(3, $this->storage->count(['theme' => 'modern']));
        $this->assertEquals(2, $this->storage->count(['theme' => 'classic']));
    }

    /**
     * @test
     * @testdox Count returns correct total for database storage
     */
    public function it_counts_palettes_in_database(): void {
        $this->storage->use_database(true);

        // Create test palettes
        for ($i = 0; $i < 5; $i++) {
            $palette = new Color_Palette(
                ['#FF0000'],
                [
                    'theme' => $i < 3 ? 'modern' : 'classic',
                    'type' => $i % 2 === 0 ? 'light' : 'dark'
                ]
            );
            $this->storage->store($palette);
        }

        // Test total count
        $this->assertEquals(5, $this->storage->count());

        // Test single filter
        $this->assertEquals(3, $this->storage->count(['theme' => 'modern']));

        // Test multiple filters
        $this->assertEquals(2, $this->storage->count([
            'theme' => 'modern',
            'type' => 'light'
        ]));
    }

    /**
     * @test
     * @testdox Count handles empty database or options
     */
    public function it_handles_empty_storage(): void {
        // Test empty options
        $this->assertEquals(0, $this->storage->count());

        // Test empty database
        $this->storage->use_database(true);
        $this->assertEquals(0, $this->storage->count());
    }

    /**
     * @test
     * @testdox Count handles invalid metadata filters
     */
    public function it_handles_invalid_count_filters(): void {
        $palette = new Color_Palette(
            ['#FF0000'],
            ['theme' => 'modern']
        );
        $this->storage->store($palette);

        // Test non-existent metadata key
        $this->assertEquals(0, $this->storage->count(['nonexistent' => 'value']));

        // Test empty metadata array
        $this->assertEquals(1, $this->storage->count([]));

        // Test with null values
        $this->assertEquals(0, $this->storage->count(['theme' => null]));
    }
}
