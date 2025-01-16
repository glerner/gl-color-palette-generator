<?php
/**
 * Test Color Palette Renderer UI
 *
 * @package GL_Color_Palette_Generator
 */

namespace GL_Color_Palette_Generator\Tests\Integration\UI;

use GL_Color_Palette_Generator\Color_Management\Color_Palette_Renderer;
use GL_Color_Palette_Generator\Color_Management\Color_Palette;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_UnitTestCase;

class Test_Color_Palette_Renderer_UI extends WP_UnitTestCase {
    private $renderer;
    private $test_palette;

    public function setUp(): void {
        parent::setUp();
        $this->renderer = new Color_Palette_Renderer();
        $this->test_palette = new Color_Palette([
            'primary' => '#2C3E50',
            'secondary' => '#E74C3C',
            'tertiary' => '#3498DB',
            'accent' => '#2ECC71'
        ]);
    }

    public function test_render_palette_with_story() {
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

        $output = $this->renderer->render($this->test_palette, [
            'show_story' => true,
            'show_descriptions' => true
        ]);

        // Check if palette story is rendered
        $this->assertStringContainsString('palette-story', $output);
        $this->assertStringContainsString('A modern and professional palette', $output);

        // Check if color descriptions are rendered
        $this->assertStringContainsString('Midnight Ocean', $output);
        $this->assertStringContainsString('Deep trust and stability', $output);
    }

    public function test_render_palette_grid() {
        $output = $this->renderer->render($this->test_palette, [
            'layout' => 'grid',
            'columns' => 2
        ]);

        $this->assertStringContainsString('gl-color-palette--grid', $output);
        $this->assertStringContainsString('grid-template-columns: repeat(2, 1fr)', $output);
    }

    public function test_render_palette_cards() {
        $output = $this->renderer->render($this->test_palette, [
            'layout' => 'cards',
            'show_descriptions' => true
        ]);

        $this->assertStringContainsString('gl-color-palette--cards', $output);
        $this->assertStringContainsString('gl-color-card', $output);
    }

    public function test_render_color_info() {
        $output = $this->renderer->render_color_info(
            '#2C3E50',
            'primary',
            [
                'name' => 'Midnight Ocean',
                'emotion' => 'Deep trust and stability'
            ]
        );

        $this->assertStringContainsString('Midnight Ocean', $output);
        $this->assertStringContainsString('Deep trust and stability', $output);
        $this->assertStringContainsString('#2C3E50', $output);
    }

    public function test_render_color_swatch() {
        $output = $this->renderer->render_swatch(
            '#2C3E50',
            'primary',
            [
                'size' => 'large',
                'show_label' => true
            ]
        );

        $this->assertStringContainsString('gl-color-swatch', $output);
        $this->assertStringContainsString('gl-color-swatch--large', $output);
        $this->assertStringContainsString('Primary', $output);
    }

    public function test_render_palette_controls() {
        $output = $this->renderer->render($this->test_palette, [
            'show_controls' => true,
            'controls' => ['copy', 'download']
        ]);

        $this->assertStringContainsString('gl-color-palette__controls', $output);
        $this->assertStringContainsString('copy-color', $output);
        $this->assertStringContainsString('download-palette', $output);
    }

    public function test_render_accessibility_info() {
        $output = $this->renderer->render($this->test_palette, [
            'show_accessibility' => true
        ]);

        $this->assertStringContainsString('wcag-contrast', $output);
        $this->assertStringContainsString('contrast-ratio', $output);
    }
}
