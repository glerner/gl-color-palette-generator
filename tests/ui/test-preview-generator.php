<?php
/**
 * Test Preview Generator
 *
 * @package GL_Color_Palette_Generator
 */

namespace GL_Color_Palette_Generator\Tests\UI;

use GL_Color_Palette_Generator\UI\Preview_Generator;
use GL_Color_Palette_Generator\Color_Management\Color_Palette;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_UnitTestCase;

class Test_Preview_Generator extends WP_UnitTestCase {
    private $preview_generator;
    private $test_palette;

    public function setUp(): void {
        parent::setUp();
        $this->preview_generator = new Preview_Generator();
        $this->test_palette = new Color_Palette([
            'primary' => '#2C3E50',
            'secondary' => '#E74C3C',
            'tertiary' => '#3498DB',
            'accent' => '#2ECC71'
        ]);
    }

    public function test_generate_preview() {
        // Set up mock AI description
        $description = [
            'colors' => [
                'primary' => [
                    'hex' => '#2C3E50',
                    'name' => 'Midnight Ocean',
                    'emotion' => 'Deep trust and stability'
                ],
                'secondary' => [
                    'hex' => '#E74C3C',
                    'name' => 'Energetic Coral',
                    'emotion' => 'Dynamic and engaging'
                ],
                'tertiary' => [
                    'hex' => '#3498DB',
                    'name' => 'Clear Sky',
                    'emotion' => 'Innovation and clarity'
                ],
                'accent' => [
                    'hex' => '#2ECC71',
                    'name' => 'Growth Green',
                    'emotion' => 'Progress and success'
                ]
            ],
            'palette_story' => 'A modern and professional palette that combines trust and innovation'
        ];
        update_option('gl_cpg_last_palette_description', $description);

        $preview = $this->preview_generator->generate_preview($this->test_palette);
        
        // Check basic structure
        $this->assertIsString($preview);
        $this->assertStringContainsString('gl-color-preview', $preview);
        
        // Check if AI descriptions are included
        $this->assertStringContainsString('Midnight Ocean', $preview);
        $this->assertStringContainsString('Deep trust and stability', $preview);
        $this->assertStringContainsString('A modern and professional palette', $preview);
        
        // Check if all colors are used
        $this->assertStringContainsString('#2C3E50', $preview);
        $this->assertStringContainsString('#E74C3C', $preview);
        $this->assertStringContainsString('#3498DB', $preview);
        $this->assertStringContainsString('#2ECC71', $preview);
    }

    public function test_generate_preview_without_descriptions() {
        delete_option('gl_cpg_last_palette_description');
        
        $preview = $this->preview_generator->generate_preview($this->test_palette);
        
        // Should still work without AI descriptions
        $this->assertIsString($preview);
        $this->assertStringContainsString('gl-color-preview', $preview);
        $this->assertStringContainsString('#2C3E50', $preview);
    }

    public function test_generate_preview_with_components() {
        $preview = $this->preview_generator->generate_preview($this->test_palette, [
            'show_buttons' => true,
            'show_cards' => true,
            'show_text' => true
        ]);
        
        // Check if preview components are included
        $this->assertStringContainsString('gl-button', $preview);
        $this->assertStringContainsString('gl-card', $preview);
        $this->assertStringContainsString('gl-text', $preview);
    }

    public function test_generate_preview_with_custom_styles() {
        $preview = $this->preview_generator->generate_preview($this->test_palette, [
            'custom_styles' => [
                'font-family' => 'Arial',
                'border-radius' => '8px'
            ]
        ]);
        
        $this->assertStringContainsString('font-family: Arial', $preview);
        $this->assertStringContainsString('border-radius: 8px', $preview);
    }

    public function test_generate_component_preview() {
        $component = 'button';
        $preview = $this->preview_generator->generate_component_preview(
            $component,
            $this->test_palette
        );
        
        $this->assertStringContainsString("gl-$component", $preview);
        $this->assertStringContainsString('#2C3E50', $preview);
    }
}
