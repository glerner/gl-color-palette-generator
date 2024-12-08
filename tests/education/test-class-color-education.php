<?php

namespace GLColorPalette\Tests\Education;

use GLColorPalette\ColorEducation;
use WP_UnitTestCase;

class Test_Color_Education extends WP_UnitTestCase {
    private $education;

    public function setUp(): void {
        parent::setUp();
        $this->education = new ColorEducation();
    }

    public function test_get_color_relationships(): void {
        $relationships = $this->education->get_color_relationships();
        
        $this->assertIsArray($relationships);
        $this->assertArrayHasKey('analogous', $relationships);
        $this->assertArrayHasKey('complementary', $relationships);
        $this->assertNotEmpty($relationships['analogous']);
    }

    public function test_get_color_meaning(): void {
        $blue_meaning = $this->education->get_color_meaning('blue');
        
        $this->assertIsArray($blue_meaning);
        $this->assertArrayHasKey('emotion', $blue_meaning);
        $this->assertArrayHasKey('business_types', $blue_meaning);
        $this->assertArrayHasKey('usage_tips', $blue_meaning);
        
        // Test case insensitivity
        $this->assertEquals(
            $this->education->get_color_meaning('BLUE'),
            $this->education->get_color_meaning('blue')
        );
        
        // Test non-existent color
        $this->assertNull($this->education->get_color_meaning('nonexistent'));
    }

    public function test_get_palette_documentation(): void {
        $palette = [
            'primary' => [
                'name' => 'Blue Ocean',
                'hex' => '#0066cc'
            ],
            'secondary' => [
                'name' => 'Green Forest',
                'hex' => '#33cc33'
            ]
        ];
        
        $docs = $this->education->get_palette_documentation($palette);
        
        $this->assertIsArray($docs);
        $this->assertArrayHasKey('primary', $docs);
        $this->assertArrayHasKey('secondary', $docs);
        $this->assertArrayHasKey('meaning', $docs['primary']);
        $this->assertArrayHasKey('usage_tips', $docs['primary']);
    }
}
