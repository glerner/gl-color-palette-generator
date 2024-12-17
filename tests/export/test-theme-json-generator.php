<?php

namespace GL_Color_Palette_Generator\Tests\Export;

use GL_Color_Palette_Generator\Export\Theme_JSON_Generator;
use PHPUnit\Framework\TestCase;

class Test_Theme_JSON_Generator extends TestCase {
    private $generator;
    private $temp_dir;

    public function setUp(): void {
        parent::setUp();
        $contrast_checker = new ContrastChecker();
        $this->generator = new Theme_JSON_Generator($contrast_checker);
        $this->temp_dir = get_temp_dir() . 'theme-json-test-' . uniqid();
        mkdir($this->temp_dir);
    }

    public function tearDown(): void {
        // Clean up temporary files
        $this->removeDirectory($this->temp_dir);
        parent::tearDown();
    }

    private function removeDirectory($dir) {
        if (!file_exists($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function test_generate_theme_json() {
        $colors = [
            ['name' => 'Primary', 'hex' => '#0066cc'],
            ['name' => 'Secondary', 'hex' => '#33cc33'],
            ['name' => 'Accent', 'hex' => '#ff6600']
        ];

        $theme_json = $this->generator->generate_theme_json($colors);

        $this->assertIsArray($theme_json);
        $this->assertArrayHasKey('settings', $theme_json);
        $this->assertArrayHasKey('color', $theme_json['settings']);
        $this->assertArrayHasKey('palette', $theme_json['settings']['color']);

        $palette = $theme_json['settings']['color']['palette'];
        $this->assertCount(3, $palette);
        $this->assertEquals('primary', $palette[0]['slug']);
        $this->assertEquals('Primary', $palette[0]['name']);
        $this->assertEquals('#0066cc', $palette[0]['color']);
    }

    public function test_generate_style_variations() {
        $base_colors = [
            '#FF0000', // Red
            '#00FF00', // Green
            '#0000FF', // Blue
            '#800080'  // Purple
        ];
        
        $variations = $this->generator->generate_style_variations($base_colors);

        // Should have 24 variations (4! permutations)
        $this->assertCount(24, $variations);

        // Test first variation structure
        $first_variation = reset($variations);
        $this->assertArrayHasKey('title', $first_variation);
        $this->assertArrayHasKey('settings', $first_variation);
        $this->assertArrayHasKey('color', $first_variation['settings']);
        $this->assertArrayHasKey('palette', $first_variation['settings']['color']);
        $this->assertArrayHasKey('gradients', $first_variation['settings']['color']);

        // Test palette structure
        $palette = $first_variation['settings']['color']['palette'];
        
        // Should have 19 colors: 4 roles * 4 variations + base + white + black + transparent
        $this->assertCount(23, $palette);

        // Check for color variations
        $primary_variations = array_filter($palette, function($color) {
            return str_contains($color['name'], 'Primary');
        });
        $this->assertCount(5, $primary_variations); // lighter, light, base, dark, darker

        // Check for standard colors
        $standard_colors = ['White', 'Black', 'Transparent'];
        foreach ($standard_colors as $color) {
            $found = false;
            foreach ($palette as $item) {
                if ($item['name'] === $color) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Standard color $color not found in palette");
        }

        // Test gradients
        $gradients = $first_variation['settings']['color']['gradients'];
        $this->assertNotEmpty($gradients);
        
        // Test first gradient structure
        $first_gradient = reset($gradients);
        $this->assertArrayHasKey('name', $first_gradient);
        $this->assertArrayHasKey('slug', $first_gradient);
        $this->assertArrayHasKey('gradient', $first_gradient);
        
        // Should have linear and radial gradients
        $has_linear = false;
        $has_radial = false;
        foreach ($gradients as $gradient) {
            if (str_contains($gradient['gradient'], 'linear-gradient')) {
                $has_linear = true;
            }
            if (str_contains($gradient['gradient'], 'radial-gradient')) {
                $has_radial = true;
            }
        }
        $this->assertTrue($has_linear, 'No linear gradients found');
        $this->assertTrue($has_radial, 'No radial gradients found');
    }

    public function test_invalid_base_colors() {
        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate_style_variations(['#FF0000', '#00FF00']); // Only 2 colors
    }

    public function test_color_variations() {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('generate_color_variations');
        $method->setAccessible(true);

        $variations = $method->invoke($this->generator, '#FF0000');
        
        $this->assertCount(5, $variations);
        $this->assertArrayHasKey('lighter', $variations);
        $this->assertArrayHasKey('light', $variations);
        $this->assertArrayHasKey('base', $variations);
        $this->assertArrayHasKey('dark', $variations);
        $this->assertArrayHasKey('darker', $variations);
        
        // Base color should be unchanged
        $this->assertEquals('#FF0000', $variations['base']);
        
        // Variations should be different from base
        $this->assertNotEquals($variations['base'], $variations['lighter']);
        $this->assertNotEquals($variations['base'], $variations['light']);
        $this->assertNotEquals($variations['base'], $variations['dark']);
        $this->assertNotEquals($variations['base'], $variations['darker']);
    }

    public function test_variation_naming() {
        $base_colors = [
            '#FF0000', // Red
            '#00FF00', // Green
            '#0000FF', // Blue
            '#800080'  // Purple
        ];
        
        $variations = $this->generator->generate_style_variations($base_colors);
        
        // Check that variation names include color names
        $first_variation = array_key_first($variations);
        $this->assertMatchesRegularExpression('/Red|Green|Blue|Purple/', $first_variation);
        $this->assertMatchesRegularExpression('/Primary|Secondary|Tertiary|Accent/', $first_variation);
    }

    public function test_save_style_variations() {
        $base_colors = ['#0066cc', '#33cc33'];
        $variations = $this->generator->generate_style_variations($base_colors);

        $result = $this->generator->save_style_variations($variations, $this->temp_dir);
        $this->assertTrue($result);

        // Check that files were created
        $styles_dir = $this->temp_dir . '/styles';
        $this->assertDirectoryExists($styles_dir);

        $files = glob($styles_dir . '/*.json');
        $this->assertNotEmpty($files);

        // Verify content of first file
        $first_file = reset($files);
        $content = file_get_contents($first_file);
        $json = json_decode($content, true);
        $this->assertIsArray($json);
        $this->assertArrayHasKey('title', $json);
        $this->assertArrayHasKey('settings', $json);
    }

    public function test_create_variations_archive() {
        $base_colors = ['#0066cc', '#33cc33'];
        $variations = $this->generator->generate_style_variations($base_colors);

        $zip_path = $this->generator->create_variations_archive($variations, $this->temp_dir);
        $this->assertNotFalse($zip_path);
        $this->assertFileExists($zip_path);

        // Verify ZIP contents
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($zip_path) === true);
        $this->assertGreaterThan(0, $zip->numFiles);

        // Check first file in ZIP
        $stat = $zip->statIndex(0);
        $this->assertNotFalse($stat);
        $this->assertStringEndsWith('.json', $stat['name']);

        $zip->close();
    }

    public function test_color_combinations() {
        $base_colors = ['#0066cc', '#33cc33', '#ff6600'];
        $variations = $this->generator->generate_style_variations($base_colors);

        // Count number of variations (should be all possible combinations)
        $expected_count = 6; // 3P3 = 6 permutations
        $this->assertCount($expected_count, $variations);

        // Verify each variation has unique color arrangement
        $arrangements = [];
        foreach ($variations as $variation) {
            $palette = $variation['settings']['color']['palette'];
            $primary_color = '';
            foreach ($palette as $color) {
                if ($color['name'] === 'Primary') {
                    $primary_color = $color['color'];
                    break;
                }
            }
            $arrangements[] = $primary_color;
        }
        $unique_arrangements = array_unique($arrangements);
        $this->assertCount($expected_count, $unique_arrangements);
    }

    /**
     * Test color variations meet accessibility requirements
     */
    public function test_color_variations_accessibility() {
        $base_colors = [
            '#2C3338', // Dark gray
            '#135E96', // Blue
            '#D63638', // Red
            '#00A32A'  // Green
        ];
        
        $variations = $this->generator->generate_style_variations($base_colors);
        
        foreach ($variations as $variation) {
            $palette = $variation['settings']['color']['palette'];
            
            // Test each color against white and black backgrounds
            foreach ($palette as $color) {
                if ($color['slug'] === 'transparent') {
                    continue;
                }
                
                // Test against white background
                $white_contrast = $this->generator->get_contrast_checker()->get_contrast_ratio($color['color'], '#FFFFFF');
                $this->assertGreaterThanOrEqual(4.5, $white_contrast, 
                    sprintf("Color %s (%s) fails contrast with white background", $color['name'], $color['color']));
                
                // Test against black background
                $black_contrast = $this->generator->get_contrast_checker()->get_contrast_ratio($color['color'], '#000000');
                $this->assertGreaterThanOrEqual(4.5, $black_contrast,
                    sprintf("Color %s (%s) fails contrast with black background", $color['name'], $color['color']));
            }
        }
    }

    /**
     * Test color variations maintain semantic relationships
     */
    public function test_color_variations_semantics() {
        $base_colors = [
            '#1E40AF', // Primary
            '#15803D', // Secondary
            '#B91C1C', // Accent
            '#1F2937'  // Background
        ];
        
        $variations = $this->generator->generate_style_variations($base_colors);
        
        foreach ($variations as $variation) {
            $palette = $variation['settings']['color']['palette'];
            
            // Group colors by role
            $color_groups = [];
            foreach ($palette as $color) {
                $role = explode('-', $color['slug'])[0];
                if (!isset($color_groups[$role])) {
                    $color_groups[$role] = [];
                }
                $color_groups[$role][] = $color;
            }
            
            // Test primary color variations
            if (isset($color_groups['primary'])) {
                $primary_colors = $color_groups['primary'];
                usort($primary_colors, function($a, $b) {
                    return $this->generator->get_contrast_checker()->calculate_relative_luminance($a['color']) 
                         - $this->generator->get_contrast_checker()->calculate_relative_luminance($b['color']);
                });
                
                // Test that variations get progressively lighter
                for ($i = 1; $i < count($primary_colors); $i++) {
                    $this->assertGreaterThan(
                        $this->generator->get_contrast_checker()->calculate_relative_luminance($primary_colors[$i-1]['color']),
                        $this->generator->get_contrast_checker()->calculate_relative_luminance($primary_colors[$i]['color']),
                        sprintf(
                            "Color %s should be lighter than %s",
                            $primary_colors[$i]['name'],
                            $primary_colors[$i-1]['name']
                        )
                    );
                }
            }
        }
    }

    /**
     * Test theme.json gradient generation
     */
    public function test_generate_gradients() {
        $base_colors = [
            '#1E40AF', // Blue
            '#15803D'  // Green
        ];
        
        $variations = $this->generator->generate_style_variations($base_colors);
        $first_variation = reset($variations);
        
        $gradients = $first_variation['settings']['color']['gradients'];
        
        $this->assertNotEmpty($gradients);
        foreach ($gradients as $gradient) {
            $this->assertArrayHasKey('name', $gradient);
            $this->assertArrayHasKey('slug', $gradient);
            $this->assertArrayHasKey('gradient', $gradient);
            $this->assertStringContainsString('linear-gradient', $gradient['gradient']);
        }
    }
}
