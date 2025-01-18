<?php
declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests\Unit\Generators;

use GL_Color_Palette_Generator\Generators\Name_Generator;
use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;

class Test_Name_Generator extends Unit_Test_Case {
    protected Name_Generator $name_generator;

    public function setUp(): void {
        parent::setUp();
        
        $this->name_generator = new Name_Generator();
    }

    public function tearDown(): void {
        
        parent::tearDown();
    }

    public function test_generate_name(): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $name = $this->name_generator->generate_name($colors);
        
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    public function test_generate_name_with_theme(): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $theme = 'ocean';
        
        $name = $this->name_generator->generate_name($colors, ['theme' => $theme]);
        
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    public function test_generate_name_with_mood(): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $mood = 'calm';
        
        $name = $this->name_generator->generate_name($colors, ['mood' => $mood]);
        
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    public function test_validate_name(): void {
        $valid_name = 'Ocean Breeze';
        $this->assertTrue($this->name_generator->validate_name($valid_name));
        
        $invalid_name = str_repeat('a', 100); // Too long
        $this->assertFalse($this->name_generator->validate_name($invalid_name));
        
        $empty_name = '';
        $this->assertFalse($this->name_generator->validate_name($empty_name));
    }

    public function test_sanitize_name(): void {
        $name = "Test Name with <script>alert('xss')</script>";
        $sanitized = $this->name_generator->sanitize_name($name);
        
        $this->assertIsString($sanitized);
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('alert', $sanitized);
    }

    public function test_generate_description(): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $name = 'Ocean Breeze';
        
        $description = $this->name_generator->generate_description($colors, $name);
        
        $this->assertIsString($description);
        $this->assertNotEmpty($description);
        $this->assertStringContainsString($name, $description);
    }

    public function test_validate_description(): void {
        $valid_description = 'A beautiful palette inspired by ocean waves.';
        $this->assertTrue($this->name_generator->validate_description($valid_description));
        
        $invalid_description = str_repeat('a', 1000); // Too long
        $this->assertFalse($this->name_generator->validate_description($invalid_description));
        
        $empty_description = '';
        $this->assertFalse($this->name_generator->validate_description($empty_description));
    }

    public function test_get_name_suggestions(): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $suggestions = $this->name_generator->get_name_suggestions($colors);
        
        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        foreach ($suggestions as $suggestion) {
            $this->assertIsString($suggestion);
            $this->assertNotEmpty($suggestion);
        }
    }

    public function test_analyze_color_theme(): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $theme = $this->name_generator->analyze_color_theme($colors);
        
        $this->assertIsString($theme);
        $this->assertNotEmpty($theme);
    }
}
