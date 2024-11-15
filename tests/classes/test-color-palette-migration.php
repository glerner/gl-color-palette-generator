<?php
/**
 * Color Palette Migration Tests
 *
 * @package GLColorPalette
 * @subpackage Tests
 */

namespace GLColorPalette\Tests;

use GLColorPalette\Color_Palette_Migration;
use GLColorPalette\Color_Palette_Storage;
use GLColorPalette\Color_Palette;
use WP_UnitTestCase;

/**
 * Class Color_Palette_Migration_Test
 *
 * @group migration
 * @group storage
 * @covers \GLColorPalette\Color_Palette_Migration
 */
class Color_Palette_Migration_Test extends WP_UnitTestCase {
    /**
     * Storage instance
     * @var Color_Palette_Storage
     */
    private Color_Palette_Storage $storage;

    /**
     * Migration instance
     * @var Color_Palette_Migration
     */
    private Color_Palette_Migration $migration;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();

        global $wpdb;
        // Clean up any existing test data
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}color_palettes");
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options WHERE option_name LIKE %s",
                $wpdb->esc_like('gl_color_palette_') . '%'
            )
        );

        $this->storage = new Color_Palette_Storage();
        $this->migration = new Color_Palette_Migration($this->storage);
    }

    /**
     * @test
     * @testdox Migration creates database table with correct schema
     */
    public function it_creates_database_table(): void {
        $this->assertTrue($this->migration->create_table());

        global $wpdb;
        $table_name = $wpdb->prefix . 'color_palettes';

        $this->assertTableExists($table_name);
        $this->assertTableHasColumn($table_name, 'id');
        $this->assertTableHasColumn($table_name, 'colors');
        $this->assertTableHasColumn($table_name, 'metadata');
    }

    /**
     * @test
     * @testdox Migration detects when migration is needed
     */
    public function it_detects_needed_migration(): void {
        // Create some test palettes in options
        $palette = new Color_Palette(['#FF0000', '#00FF00']);
        $this->storage->store($palette);

        $this->assertTrue($this->migration->needs_migration());
    }

    /**
     * @test
     * @testdox Migration successfully moves palettes from options to database
     */
    public function it_migrates_palettes_to_database(): void {
        // Create test palettes in options
        $palettes = [
            new Color_Palette(['#FF0000', '#00FF00'], ['name' => 'Test 1']),
            new Color_Palette(['#0000FF', '#FFFF00'], ['name' => 'Test 2'])
        ];

        foreach ($palettes as $palette) {
            $this->storage->store($palette);
        }

        $stats = $this->migration->migrate_to_database();

        $this->assertEquals(2, $stats['success']);
        $this->assertEquals(0, $stats['failed']);

        // Verify palettes are in database
        global $wpdb;
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}color_palettes");
        $this->assertEquals(2, $count);

        // Verify options were cleaned up
        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
                $wpdb->esc_like('gl_color_palette_') . '%'
            )
        );
        $this->assertEmpty($options);
    }

    /**
     * @test
     * @testdox Migration handles invalid data gracefully
     */
    public function it_handles_invalid_data(): void {
        // Create an invalid palette option
        update_option('gl_color_palette_invalid', 'not an array');

        $stats = $this->migration->migrate_to_database();

        $this->assertEquals(0, $stats['success']);
        $this->assertEquals(1, $stats['failed']);
    }

    /**
     * @test
     * @testdox Migration preserves palette metadata
     */
    public function it_preserves_metadata(): void {
        $original = new Color_Palette(
            ['#FF0000', '#00FF00'],
            ['name' => 'Test', 'created_by' => 'user1']
        );
        $this->storage->store($original);

        $this->migration->migrate_to_database();

        // Retrieve from database
        $this->storage->use_database(true);
        $migrated = $this->storage->get($original->get_metadata('id'));

        $this->assertInstanceOf(Color_Palette::class, $migrated);
        $this->assertEquals($original->get_metadata(), $migrated->get_metadata());
    }

    /**
     * Helper method to assert table exists
     */
    private function assertTableExists(string $table): void {
        global $wpdb;
        $this->assertNotEmpty($wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $wpdb->esc_like($table)
            )
        ));
    }

    /**
     * Helper method to assert column exists
     */
    private function assertTableHasColumn(string $table, string $column): void {
        global $wpdb;
        $this->assertNotEmpty($wpdb->get_var(
            $wpdb->prepare(
                "SHOW COLUMNS FROM $table LIKE %s",
                $wpdb->esc_like($column)
            )
        ));
    }
}
