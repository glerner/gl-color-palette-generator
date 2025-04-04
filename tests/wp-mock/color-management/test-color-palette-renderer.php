<?php
/**
 * Test Color Palette Renderer UI
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock\Color_Management
 */

namespace GL_Color_Palette_Generator\Tests\WP_Mock\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Palette_Renderer;
use GL_Color_Palette_Generator\Color_Management\Color_Palette;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Mock;
use Mockery;

/**
 * Test class for Color_Palette_Renderer UI functionality
 *
 * @covers GL_Color_Palette_Generator\Color_Management\Color_Palette_Renderer
 */
class Test_Color_Palette_Renderer_UI extends WP_Mock_Test_Case {
    private $renderer;
    private $test_palette;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        
        // Mock WordPress functions used in the renderer
        WP_Mock::userFunction('wp_enqueue_style', [
            'times' => 'any',
            'return' => true
        ]);
        
        WP_Mock::userFunction('wp_add_inline_style', [
            'times' => 'any',
            'return' => true
        ]);
        
        WP_Mock::userFunction('esc_attr', [
            'times' => 'any',
            'return_arg' => 0
        ]);
        
        WP_Mock::userFunction('esc_html', [
            'times' => 'any',
            'return_arg' => 0
        ]);
        
        WP_Mock::userFunction('wp_kses_post', [
            'times' => 'any',
            'return_arg' => 0
        ]);
        
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
        
        // Mock update_option and get_option
        WP_Mock::userFunction('update_option', [
            'args' => ['gl_cpg_last_palette_description', $description],
            'times' => 1,
            'return' => true
        ]);
        
        WP_Mock::userFunction('get_option', [
            'args' => ['gl_cpg_last_palette_description'],
            'times' => 'any',
            'return' => $description
        ]);
        
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

    /**
     * Tests from Palette_Renderer interface
     */
    public function test_render_palette_with_custom_format(): void {
        $format = [
            'container' => '<div class="custom-palette">%s</div>',
            'color' => '<div class="custom-color" style="background-color: %s">%s</div>'
        ];

        $result = $this->renderer->render_palette($this->test_palette, $format);

        $this->assertStringContainsString('custom-palette', $result);
        $this->assertStringContainsString('custom-color', $result);
        $this->assertStringContainsString('#2C3E50', $result);
    }

    public function test_render_palette_with_metadata(): void {
        $metadata = [
            'title' => 'Test Palette',
            'description' => 'A test color palette',
            'author' => 'Test Author',
            'created' => '2024-01-17'
        ];

        $result = $this->renderer->render_palette($this->test_palette, null, $metadata);

        $this->assertStringContainsString('Test Palette', $result);
        $this->assertStringContainsString('Test Author', $result);
        $this->assertStringContainsString('2024-01-17', $result);
    }

    public function test_render_palette_with_accessibility_info(): void {
        $result = $this->renderer->render_palette_with_accessibility($this->test_palette);

        $this->assertStringContainsString('contrast-ratio', $result);
        $this->assertStringContainsString('wcag-compliance', $result);
        foreach ($this->test_palette->get_colors() as $color) {
            $this->assertStringContainsString($color, $result);
        }
    }

    public function test_render_palette_with_color_names(): void {
        $names = [
            '#2C3E50' => 'Midnight Blue',
            '#E74C3C' => 'Cinnabar Red',
            '#3498DB' => 'Curious Blue',
            '#2ECC71' => 'Emerald Green'
        ];

        $result = $this->renderer->render_palette_with_names($this->test_palette, $names);

        foreach ($names as $color => $name) {
            $this->assertStringContainsString($color, $result);
            $this->assertStringContainsString($name, $result);
        }
    }
    
    public function tearDown(): void {
        WP_Mock::tearDown();
        Mockery::close();
        parent::tearDown();
    }
}
