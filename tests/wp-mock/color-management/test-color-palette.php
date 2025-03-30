<?php
/**
 * Test Color Palette Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Palette;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Mock;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use Mockery;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;

class Test_Color_Palette extends GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case {
    protected $valid_colors = ['#FF0000', '#00FF00', '#0000FF'];
    protected $valid_metadata = [
        'name' => 'Test Palette',
        'description' => 'A test palette',
        'theme' => 'test',
        'provider' => 'test',
        'tags' => ['test', 'demo']
    ];

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();

        WP_Mock::userFunction('current_time', [
            'args' => ['mysql'],
            'return' => '2024-01-01 00:00:00'
        ]);
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        Mockery::close();
        parent::tearDown();
    }

    public function test_constructor_with_valid_data(): void {
        $palette = new Color_Palette($this->valid_colors, $this->valid_metadata);
        
        $this->assertEquals($this->valid_colors, $palette->get_colors());
        $this->assertEquals($this->valid_metadata['name'], $palette->get_metadata('name'));
    }

    public function test_constructor_with_invalid_color(): void {
        $this->expectException(\InvalidArgumentException::class);
        new Color_Palette(['invalid-color']);
    }

    public function test_set_colors_with_valid_colors(): void {
        $palette = new Color_Palette();
        $palette->set_colors($this->valid_colors);
        
        $this->assertEquals($this->valid_colors, $palette->get_colors());
    }

    public function test_set_colors_with_invalid_color(): void {
        $palette = new Color_Palette();
        $this->expectException(\InvalidArgumentException::class);
        $palette->set_colors(['not-a-color']);
    }

    public function test_set_metadata_with_valid_data(): void {
        $palette = new Color_Palette();
        $palette->set_metadata($this->valid_metadata);
        
        foreach ($this->valid_metadata as $key => $value) {
            $this->assertEquals($value, $palette->get_metadata($key));
        }
    }

    public function test_set_metadata_with_invalid_name(): void {
        $palette = new Color_Palette();
        $this->expectException(\InvalidArgumentException::class);
        $palette->set_metadata(['name' => ['not', 'a', 'string']]);
    }

    public function test_set_metadata_with_invalid_tags(): void {
        $palette = new Color_Palette();
        $this->expectException(\InvalidArgumentException::class);
        $palette->set_metadata(['tags' => 'not-an-array']);
    }

    public function test_set_metadata_with_invalid_tag_type(): void {
        $palette = new Color_Palette();
        $this->expectException(\InvalidArgumentException::class);
        $palette->set_metadata(['tags' => ['valid', 123]]);
    }

    public function test_get_metadata_with_invalid_key(): void {
        $palette = new Color_Palette();
        $this->expectException(\InvalidArgumentException::class);
        $palette->get_metadata('invalid-key');
    }

    public function test_add_color_with_valid_color(): void {
        $palette = new Color_Palette();
        $result = $palette->add_color('#FF0000');
        
        $this->assertTrue($result);
        $this->assertEquals(['#FF0000'], $palette->get_colors());
    }

    public function test_add_color_with_invalid_color(): void {
        $palette = new Color_Palette();
        $this->expectException(\InvalidArgumentException::class);
        $palette->add_color('not-a-color');
    }

    public function test_remove_color_success(): void {
        $palette = new Color_Palette($this->valid_colors);
        $result = $palette->remove_color('#FF0000');
        
        $this->assertTrue($result);
        $this->assertEquals(['#00FF00', '#0000FF'], $palette->get_colors());
    }

    public function test_remove_color_not_found(): void {
        $palette = new Color_Palette($this->valid_colors);
        $result = $palette->remove_color('#FFFFFF');
        
        $this->assertFalse($result);
        $this->assertEquals($this->valid_colors, $palette->get_colors());
    }

    public function test_to_array(): void {
        $palette = new Color_Palette($this->valid_colors, $this->valid_metadata);
        $array = $palette->to_array();
        
        $this->assertArrayHasKey('colors', $array);
        $this->assertArrayHasKey('metadata', $array);
        $this->assertEquals($this->valid_colors, $array['colors']);
        $this->assertEquals($this->valid_metadata['name'], $array['metadata']['name']);
    }

    public function test_from_array_with_valid_data(): void {
        $data = [
            'colors' => $this->valid_colors,
            'metadata' => $this->valid_metadata
        ];
        
        $palette = Color_Palette::from_array($data);
        
        $this->assertEquals($this->valid_colors, $palette->get_colors());
        $this->assertEquals($this->valid_metadata['name'], $palette->get_metadata('name'));
    }

    public function test_from_array_with_invalid_colors_type(): void {
        $this->expectException(\InvalidArgumentException::class);
        Color_Palette::from_array(['colors' => 'not-an-array']);
    }

    public function test_timestamps_are_set(): void {
        $palette = new Color_Palette();
        $metadata = $palette->get_metadata();
        
        $this->assertEquals('2024-01-01 00:00:00', $metadata['created']);
        $this->assertEquals('2024-01-01 00:00:00', $metadata['modified']);
    }
}
