<?php
/**
 * Test Theme_JSON_Generator class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Integration\Export
 */

namespace GL_Color_Palette_Generator\Tests\Integration\Export;
use GL_Color_Palette_Generator\Tests\Base\Integration_Test_Case;

use GL_Color_Palette_Generator\Export\Theme_JSON_Generator;
use GL_Color_Palette_Generator\Color_Management\Color_Validator;
use GL_Color_Palette_Generator\Color_Management\Color_Variation_Generator;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Localization\Theme_Namer;

class Test_Theme_JSON_Generator extends Integration_Test_Case implements Color_Constants {
	/** @var Theme_JSON_Generator */
	private $instance;
	/** @var string */
	private $temp_dir;

	public function setUp(): void {
		parent::setUp();

		$color_validator     = new Color_Validator();
		$variation_generator = new Color_Variation_Generator();
		$theme_namer         = new Theme_Namer();

		$this->instance = new Theme_JSON_Generator(
			$color_validator,
			$variation_generator,
			$theme_namer
		);

		$this->temp_dir = sys_get_temp_dir() . '/theme-json-test-' . uniqid();
		mkdir( $this->temp_dir );
	}

	public function tearDown(): void {
		// Clean up temporary files
		$this->removeDirectory( $this->temp_dir );
		parent::tearDown();
	}

	private function removeDirectory( $dir ) {
		if ( ! file_exists( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), array( '.', '..' ) );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			is_dir( $path ) ? $this->removeDirectory( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}

	/**
	 * Test theme.json generation with all color schemes
	 */
	public function test_generate_theme_json(): void {
		foreach ( self::COLOR_SCHEMES as $scheme_type ) {
			// Get required roles for this scheme
			$required_roles = self::REQUIRED_ROLES[ $scheme_type ];

			// Generate test colors for each required role
			$test_colors = array();
			foreach ( $required_roles as $role ) {
				$test_colors[ $role ] = '#' . dechex( mt_rand( 0, 0xFFFFFF ) );
			}

			// Generate theme.json
			$theme_json = $this->instance->generate_theme_json( $test_colors, $scheme_type );

			// Basic structure checks
			$this->assertIsArray( $theme_json );
			$this->assertEquals( 2, $theme_json['version'] );
			$this->assertArrayHasKey( 'settings', $theme_json );
			$this->assertArrayHasKey( 'color', $theme_json['settings'] );
			$this->assertArrayHasKey( 'palette', $theme_json['settings']['color'] );

			// Get palette for easier testing
			$palette = $theme_json['settings']['color']['palette'];

			// Check that all required roles are present with their variations
			foreach ( $required_roles as $role ) {
				foreach ( self::COLOR_VARIATIONS as $variation => $label ) {
					$slug  = $variation === 'base' ? $role : "{$role}-{$variation}";
					$found = false;
					foreach ( $palette as $color ) {
						if ( $color['slug'] === $slug ) {
							$found = true;
							break;
						}
					}
					$this->assertTrue( $found, "Missing color variation: $slug in scheme: $scheme_type" );
				}
			}

			// Check that standard colors are at the end
			$last_three = array_slice( $palette, -3 );
			$this->assertEquals( 'white', $last_three[0]['slug'] );
			$this->assertEquals( 'black', $last_three[1]['slug'] );
			$this->assertEquals( 'transparent', $last_three[2]['slug'] );
		}
	}

	/**
	 * Test invalid scheme type
	 */
	public function test_invalid_scheme_type(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid scheme type: invalid_scheme' );
		$this->instance->generate_theme_json( array( 'primary' => '#000000' ), 'invalid_scheme' );
	}

	/**
	 * Test missing required colors
	 */
	public function test_missing_required_colors(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Missing required color role: primary for scheme: complementary' );
		$this->instance->generate_theme_json( array( 'secondary' => '#000000' ), 'complementary' );
	}

	public function test_generate_style_variations(): void {
		$base_colors = array(
			'#1E40AF', // Primary
			'#15803D', // Secondary
			'#B91C1C', // Tertiary
			'#9333EA',  // Accent
		);

		$variations = $this->instance->generate_style_variations( $base_colors );

		// Should have both light and dark versions of each combination
		$this->assertCount(
			self::VARIATIONS_PER_MODE * 2,
			$variations,
			'Should generate both light and dark versions of each combination'
		);

		$light_variations = 0;
		$dark_variations  = 0;

		foreach ( $variations as $variation ) {
			$palette = $variation['settings']['color']['palette'];

			// Find base color
			$base_color = null;
			foreach ( $palette as $color ) {
				if ( $color['slug'] === 'base' ) {
					$base_color = $color['color'];
					break;
				}
			}

			$this->assertNotNull( $base_color, 'Theme should have a base color' );

			$contrast_checker = $this->instance->get_contrast_checker();
			$base_luminance   = $contrast_checker->calculate_relative_luminance( $base_color );
			$is_light_mode    = $base_luminance > self::LIGHT_LUMINANCE_THRESHOLD;

			// Count light/dark variations
			if ( $base_luminance > self::LIGHT_LUMINANCE_THRESHOLD ) {
				++$light_variations;
			} elseif ( $base_luminance < self::DARK_LUMINANCE_THRESHOLD ) {
				++$dark_variations;
			} else {
				$this->fail( 'Base color should be either very light or very dark' );
			}

			// Test CSS variable usage
			$this->assertEquals(
				'var(--wp--preset--color--base)',
				$variation['styles']['color']['background'],
				'Theme should use base as background'
			);
			$this->assertEquals(
				'var(--wp--preset--color--contrast)',
				$variation['styles']['color']['text'],
				'Theme should use contrast as text color'
			);

			// Test that each variation has all four main colors represented
			$found_colors = array(
				'primary'   => false,
				'secondary' => false,
				'tertiary'  => false,
				'accent'    => false,
			);
			foreach ( $palette as $color ) {
				foreach ( array_keys( $found_colors ) as $role ) {
					if ( strpos( $color['slug'], $role ) === 0 ) {
						$found_colors[ $role ] = true;
					}
				}
			}
			foreach ( $found_colors as $role => $found ) {
				$this->assertTrue( $found, "Variation should include $role color" );
			}
		}

		// Verify we have equal numbers of light and dark variations
		$this->assertEquals(
			self::VARIATIONS_PER_MODE,
			$light_variations,
			'Should have correct number of light variations'
		);
		$this->assertEquals(
			self::VARIATIONS_PER_MODE,
			$dark_variations,
			'Should have correct number of dark variations'
		);
	}

	/**
	 * Test invalid color inputs for style variations
	 */
	public function test_invalid_style_variation_colors(): void {
		// Test with insufficient colors
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Insufficient colors provided' );
		$this->instance->generate_style_variations( array( '#FF0000' ) );

		// Test with invalid color format
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid color format' );
		$this->instance->generate_style_variations( array( 'not-a-color', '#00FF00' ) );

		// Test with empty array
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'No colors provided' );
		$this->instance->generate_style_variations( array() );
	}

	/**
	 * Test invalid color inputs for theme.json generation
	 */
	public function test_invalid_theme_json_colors(): void {
		// Test with invalid color format
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid color format' );
		$this->instance->generate_theme_json( array( 'primary' => 'not-a-color' ), 'complementary' );

		// Test with empty array
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'No colors provided' );
		$this->instance->generate_theme_json( array(), 'complementary' );
	}

	public function test_color_variations() {
		$reflection = new \ReflectionClass( $this->instance );
		$method     = $reflection->getMethod( 'generate_color_variations' );
		$method->setAccessible( true );

		$variations = $method->invoke( $this->instance, self::COLOR_NEAR_BLACK );

		$this->assertCount( 5, $variations );
		$this->assertArrayHasKey( 'lighter', $variations );
		$this->assertArrayHasKey( 'light', $variations );
		$this->assertArrayHasKey( 'base', $variations );
		$this->assertArrayHasKey( 'dark', $variations );
		$this->assertArrayHasKey( 'darker', $variations );

		// Base color should be unchanged
		$this->assertEquals( self::COLOR_NEAR_BLACK, $variations['base'] );

		// Variations should be different from base
		$this->assertNotEquals( $variations['base'], $variations['lighter'] );
		$this->assertNotEquals( $variations['base'], $variations['light'] );
		$this->assertNotEquals( $variations['base'], $variations['dark'] );
		$this->assertNotEquals( $variations['base'], $variations['darker'] );
	}

	public function test_variation_naming() {
		$base_colors = array(
			self::COLOR_NEAR_BLACK,  // Dark
			self::COLOR_OFF_WHITE,   // Light
			self::COLOR_DARK_GRAY,   // Medium
			self::COLOR_MID_GRAY,     // Lower
		);

		$variations = $this->instance->generate_style_variations( $base_colors );

		// Check that variation names include color names
		$first_variation = array_key_first( $variations );
		$this->assertMatchesRegularExpression( '/Dark|Light|Medium|Lower/', $first_variation );
		$this->assertMatchesRegularExpression( '/Primary|Secondary|Tertiary|Accent/', $first_variation );
	}

	public function test_save_style_variations() {
		$base_colors = array( self::COLOR_NEAR_BLACK, self::COLOR_OFF_WHITE );
		$variations  = $this->instance->generate_style_variations( $base_colors );

		$result = $this->instance->save_style_variations( $variations, $this->temp_dir );
		$this->assertTrue( $result );

		// Check that files were created
		$styles_dir = $this->temp_dir . '/styles';
		$this->assertDirectoryExists( $styles_dir );

		$files = glob( $styles_dir . '/*.json' );
		$this->assertNotEmpty( $files );

		// Verify content of first file
		$first_file = reset( $files );
		$content    = file_get_contents( $first_file );
		$json       = json_decode( $content, true );
		$this->assertIsArray( $json );
		$this->assertArrayHasKey( 'title', $json );
		$this->assertArrayHasKey( 'settings', $json );
	}

	public function test_create_variations_archive() {
		$base_colors = array( self::COLOR_NEAR_BLACK, self::COLOR_OFF_WHITE );
		$variations  = $this->instance->generate_style_variations( $base_colors );

		$zip_path = $this->instance->create_variations_archive( $variations, $this->temp_dir );
		$this->assertNotFalse( $zip_path );
		$this->assertFileExists( $zip_path );

		// Verify ZIP contents
		$zip = new \ZipArchive();
		$this->assertTrue( $zip->open( $zip_path ) === true );
		$this->assertGreaterThan( 0, $zip->numFiles );

		// Check first file in ZIP
		$stat = $zip->statIndex( 0 );
		$this->assertNotFalse( $stat );
		$this->assertStringEndsWith( '.json', $stat['name'] );

		$zip->close();
	}

	/**
	 * Test color combinations and role assignments
	 */
	public function test_color_combinations(): void {
		$base_colors = array(
			'#1E40AF', // Primary
			'#15803D', // Secondary
			'#B91C1C', // Tertiary
			'#9333EA',  // Accent
		);

		$variations = $this->instance->generate_style_variations( $base_colors );
		$this->assertCount(
			self::VARIATIONS_PER_MODE * 2,
			$variations,
			'Should generate light and dark versions of all possible role permutations'
		);

		$roles = array( 'primary', 'secondary', 'tertiary', 'accent' );

		// Each color should appear in each role across variations
		// Note: A color might appear in multiple roles if colors are similar enough
		// (e.g., if secondary & tertiary are similar shades)
		foreach ( $base_colors as $color ) {
			$roles_used = array_fill_keys( $roles, false );
			foreach ( $variations as $variation ) {
				foreach ( $variation['settings']['color']['palette'] as $palette_color ) {
					foreach ( $roles as $role ) {
						if ( strpos( $palette_color['slug'], $role ) === 0 &&
							$palette_color['color'] === $color ) {
							$roles_used[ $role ] = true;
						}
					}
				}
			}
			foreach ( $roles_used as $role => $used ) {
				$this->assertTrue( $used, "Color $color should be used as $role in at least one variation" );
			}
		}

		// Test mode-specific colors and contrast roles
		foreach ( $variations as $variation ) {
			$palette = $variation['settings']['color']['palette'];

			// Find base color
			$base_color = null;
			foreach ( $palette as $color ) {
				if ( $color['slug'] === 'base' ) {
					$base_color = $color['color'];
					break;
				}
			}

			$contrast_checker = $this->instance->get_contrast_checker();
			$base_luminance   = $contrast_checker->calculate_relative_luminance( $base_color );
			$is_light_mode    = $base_luminance > self::LIGHT_LUMINANCE_THRESHOLD;

			foreach ( $palette as $color ) {
				switch ( $color['slug'] ) {
					case 'base':
						$base_luminance = $contrast_checker->calculate_relative_luminance( $color['color'] );
						if ( $is_light_mode ) {
							// Light mode: very light color
							$this->assertGreaterThan(
								self::LIGHT_LUMINANCE_THRESHOLD,
								$base_luminance,
								'Base color should be very light in light mode'
							);
						} else {
							// Dark mode: very dark color
							$this->assertLessThan(
								self::DARK_LUMINANCE_THRESHOLD,
								$base_luminance,
								'Base color should be very dark in dark mode'
							);
						}
						break;

					case 'base-2':
						if ( $is_light_mode ) {
							$this->assertEquals(
								self::COLOR_WHITE,
								$color['color'],
								'base-2 should be white in light mode'
							);
						} else {
							$this->assertEquals(
								self::COLOR_OFF_WHITE,
								$color['color'],
								'base-2 should be off-white in dark mode'
							);
						}
						break;

					case 'contrast':
					case 'contrast-2':
					case 'contrast-3':
						// Contrast colors should be opposite of base: dark in light mode, light in dark mode
						$luminance = $contrast_checker->calculate_relative_luminance( $color['color'] );
						if ( $base_luminance > self::LIGHT_LUMINANCE_THRESHOLD ) {
							// Light mode: contrast should be very dark
							$this->assertLessThan(
								self::DARK_LUMINANCE_THRESHOLD,
								$luminance,
								"$color[slug] should be very dark in light mode"
							);
						} else {
							// Dark mode: contrast should be very light
							$this->assertGreaterThan(
								self::LIGHT_LUMINANCE_THRESHOLD,
								$luminance,
								"$color[slug] should be very light in dark mode"
							);
						}
						$this->assertTrue(
							$contrast_checker->get_contrast_ratio( $color['color'], self::COLOR_WHITE ) >= self::CONTRAST_MIN ||
							$contrast_checker->get_contrast_ratio( $color['color'], self::COLOR_NEAR_BLACK ) >= self::CONTRAST_MIN,
							"$color[slug] should maintain WCAG contrast requirements"
						);
						break;

					case 'accent-1':
					case 'accent-2':
					case 'accent-3':
					case 'accent-4':
					case 'accent-5':
						// Should be variations of our accent colors
						$found_match = false;
						foreach ( $roles as $role ) {
							if ( strpos( $color['name'], $role ) !== false &&
								( strpos( $color['name'], 'dark' ) !== false ||
								strpos( $color['name'], 'light' ) !== false ) ) {
								$found_match = true;
								break;
							}
						}
						$this->assertTrue(
							$found_match,
							'accent-N should use variations of role colors'
						);
						break;
				}
			}
		}
	}

	public function test_theme_style_variations_accessibility() {
		$base_colors = array(
			self::COLOR_NEAR_BLACK,  // Dark color
			self::COLOR_OFF_WHITE,   // Light color
			self::COLOR_DARK_GRAY,   // Medium contrast
			self::COLOR_MID_GRAY,     // Lower contrast
		);

		$variations = $this->instance->generate_style_variations( $base_colors );

		foreach ( $variations as $variation ) {
			$palette = $variation['settings']['color']['palette'];

			// Test each color against white and black backgrounds
			foreach ( $palette as $color ) {
				if ( $color['slug'] === 'transparent' ) {
					continue;
				}

				// Test contrast with standard colors
				$white_contrast = $this->instance->get_contrast_checker()->get_contrast_ratio( $color['color'], self::COLOR_WHITE );
				$black_contrast = $this->instance->get_contrast_checker()->get_contrast_ratio( $color['color'], self::COLOR_NEAR_BLACK );

				$this->assertTrue(
					$white_contrast >= self::WCAG_CONTRAST_MIN ||
					$black_contrast >= self::WCAG_CONTRAST_MIN,
					"Color {$color['color']} should have sufficient contrast with either white or black"
				);
			}
		}
	}

	/**
	 * Test color variation generation in light mode
	 */
	public function test_create_variations_light_mode() {
		$generator  = new Color_Variation_Generator( $this->instance->get_contrast_checker() );
		$variations = $generator->generate_tints_and_shades(
			self::COLOR_NEAR_BLACK,
			array(
				'is_dark_mode' => false,
			)
		);

		$this->assertArrayHasKey( 'base', $variations['variations'] );
		$this->assertArrayHasKey( 'contrast', $variations['variations'] );

		// Base color should be very light in light mode
		$base_luminance = $this->get_contrast_checker()->calculate_relative_luminance( $variations['variations']['base'] );
		$this->assertGreaterThan( self::LIGHT_LUMINANCE_THRESHOLD, $base_luminance );

		// Contrast color should be very dark in light mode
		$contrast_luminance = $this->get_contrast_checker()->calculate_relative_luminance( $variations['variations']['contrast'] );
		$this->assertLessThan( self::DARK_LUMINANCE_THRESHOLD, $contrast_luminance );
	}

	/**
	 * Test color variation generation in dark mode
	 */
	public function test_create_variations_dark_mode() {
		$generator  = new Color_Variation_Generator( $this->instance->get_contrast_checker() );
		$variations = $generator->generate_tints_and_shades(
			self::COLOR_WHITE,
			array(
				'is_dark_mode' => true,
			)
		);

		$this->assertArrayHasKey( 'base', $variations['variations'] );
		$this->assertArrayHasKey( 'contrast', $variations['variations'] );

		// Base color should be very dark in dark mode
		$base_luminance = $this->get_contrast_checker()->calculate_relative_luminance( $variations['variations']['base'] );
		$this->assertLessThan( self::DARK_LUMINANCE_THRESHOLD, $base_luminance );

		// Contrast color should be very light in dark mode
		$contrast_luminance = $this->get_contrast_checker()->calculate_relative_luminance( $variations['variations']['contrast'] );
		$this->assertGreaterThan( self::LIGHT_LUMINANCE_THRESHOLD, $contrast_luminance );
	}

	/**
	 * Test that generated shades meet accessibility requirements
	 */
	public function test_accessible_shades() {
		$generator  = new Color_Variation_Generator( $this->instance->get_contrast_checker() );
		$variations = $generator->generate_tints_and_shades(
			self::COLOR_MID_GRAY,
			array(
				'is_dark_mode' => false,
			)
		);

		foreach ( $variations['variations'] as $key => $color ) {
			if ( $key !== 'base' ) {
				$contrast_ratio = $this->get_contrast_ratio(
					$variations['variations']['base'],
					$color
				);
				$this->assertGreaterThanOrEqual( self::WCAG_CONTRAST_MIN, $contrast_ratio );
			}
		}
	}

	/**
	 * Helper function to calculate luminance
	 */
	private function calculate_luminance( string $hex ): float {
		$hex             = ltrim( $hex, '#' );
		list($r, $g, $b) = sscanf( $hex, '%02x%02x%02x' );

		$r = $r / 255;
		$g = $g / 255;
		$b = $b / 255;

		$r = $r <= 0.03928 ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
		$g = $g <= 0.03928 ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
		$b = $b <= 0.03928 ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}

	/**
	 * Helper function to calculate contrast ratio
	 */
	private function get_contrast_ratio( string $color1, string $color2 ): float {
		$l1 = $this->calculate_luminance( $color1 );
		$l2 = $this->calculate_luminance( $color2 );

		$lighter = max( $l1, $l2 );
		$darker  = min( $l1, $l2 );

		return ( $lighter + 0.05 ) / ( $darker + 0.05 );
	}

	/**
	 * Test theme.json color slug compatibility with TwentyTwentyFour
	 */
	public function test_theme_json_color_slug_compatibility(): void {
		$base_colors = array(
			'#1E40AF', // Primary
			'#15803D', // Secondary
			'#B91C1C', // Tertiary
			'#9333EA',  // Accent
		);

		$variations      = $this->instance->generate_style_variations( $base_colors );
		$first_variation = reset( $variations );
		$palette         = $first_variation['settings']['color']['palette'];

		// Check for required base color slugs
		$required_slugs = array( 'primary', 'secondary', 'tertiary', 'accent' );

		foreach ( $required_slugs as $slug ) {
			$this->assertArrayHasKey(
				$slug,
				array_column( $palette, 'color', 'slug' ),
				"Theme.json should contain a '$slug' color"
			);
		}

		// Test TwentyTwentyFour compatibility
		$tt4_slugs = array(
			'base',      // Off-white with hint of primary
			'base-2',    // Pure white
			'contrast',  // Pure black
			'contrast-2', // Dark gray (for borders/decorative)
			'contrast-3', // Medium gray (for borders/decorative)
			'accent',     // From accent variations
			'accent-2',
			'accent-3',
			'accent-4',
			'accent-5',
		);

		foreach ( $tt4_slugs as $slug ) {
			$found = false;
			foreach ( $palette as $color ) {
				if ( $color['slug'] === $slug ) {
					$found = true;

					// Additional checks for specific colors
					switch ( $slug ) {
						case 'base':
							// Should be off-white with a hint of primary (for light mode)
							$base_luminance = $this->instance->get_contrast_checker()->calculate_relative_luminance( $color['color'] );
							if ( $base_luminance > self::LIGHT_LUMINANCE_THRESHOLD ) {
								// Light mode: very light color
								$this->assertGreaterThan(
									self::LIGHT_LUMINANCE_THRESHOLD,
									$base_luminance,
									'Base color should be very light in light mode'
								);
							} else {
								// Dark mode: very dark color
								$this->assertLessThan(
									self::DARK_LUMINANCE_THRESHOLD,
									$base_luminance,
									'Base color should be very dark in dark mode'
								);
							}
							break;

						case 'base-2':
							$this->assertEquals(
								self::COLOR_WHITE,
								$color['color'],
								'Base-2 should be the "white" color'
							);
							break;

						case 'contrast':
							$this->assertEquals(
								self::COLOR_NEAR_BLACK,
								$color['color'],
								'Contrast should be near-black'
							);
							break;

						case 'contrast-2':
						case 'contrast-3':
							// Contrast colors follow light/dark mode patterns while maintaining WCAG contrast
							$luminance = $this->instance->get_contrast_checker()->calculate_relative_luminance( $color['color'] );
							if ( $luminance > self::LIGHT_LUMINANCE_THRESHOLD ) {
								// Light mode: very light color
								$this->assertGreaterThan(
									self::LIGHT_LUMINANCE_THRESHOLD,
									$luminance,
									"$slug should be very light in light mode"
								);
							} else {
								// Dark mode: very dark color
								$this->assertLessThan(
									self::DARK_LUMINANCE_THRESHOLD,
									$luminance,
									"$slug should be very dark in dark mode"
								);
							}
							$this->assertTrue(
								$this->instance->get_contrast_checker()->get_contrast_ratio( $color['color'], self::COLOR_WHITE ) >= self::WCAG_CONTRAST_MIN ||
								$this->instance->get_contrast_checker()->get_contrast_ratio( $color['color'], self::COLOR_NEAR_BLACK ) >= self::WCAG_CONTRAST_MIN,
								"$slug should maintain WCAG contrast requirements"
							);
							break;

						case 'accent':
							$this->assertEquals(
								$base_colors[3],
								$color['color'],
								'Accent color should match 3rd test color'
							);
							break;
					}
					break;
				}
			}
			$this->assertTrue( $found, "Missing TwentyTwentyFour-compatible slug: $slug" );
		}

		// Test color ordering
		$last_colors = array_slice( $palette, -2, 2 );
		$this->assertEquals( 'White', $last_colors[0]['name'], 'White should be second to last' );
		$this->assertEquals( 'Black', $last_colors[1]['name'], 'Black should be last' );
	}

	/**
	 * Test theme compatibility
	 */
	public function test_theme_compatibility(): void {
		$base_colors = array(
			'#1E40AF', // Primary
			'#15803D', // Secondary
			'#B91C1C', // Tertiary
			'#9333EA',  // Accent
		);

		$theme_json = $this->instance->generate_theme_json( $base_colors );
		$palette    = $theme_json['settings']['color']['palette'];

		// Combined slugs from 2023, 2024, and 2025 themes
		$theme_slugs = array(
			// TwentyTwentyThree style
			'primary',
			'primary-lighter',
			'primary-light',
			'primary-dark',
			'primary-darker',
			'secondary',
			'secondary-lighter',
			'secondary-light',
			'secondary-dark',
			'secondary-darker',
			'tertiary',
			'tertiary-lighter',
			'tertiary-light',
			'tertiary-dark',
			'tertiary-darker',
			'accent',
			'accent-lighter',
			'accent-light',
			'accent-dark',
			'accent-darker',

			// TwentyTwentyFour/Five style
			'base',
			'base-2',
			'contrast',
			'contrast-2',
			'contrast-3',
			'accent-1',
			'accent-2',
			'accent-3',
			'accent-4',
			'accent-5',
			'accent-6',
		);

		foreach ( $theme_slugs as $slug ) {
			$found = false;
			foreach ( $palette as $color ) {
				if ( $color['slug'] === $slug ) {
					$found = true;
					$this->assertNotEmpty( $color['name'], 'Color should have a name' );
					break;
				}
			}
			$this->assertTrue( $found, "Missing theme-compatible slug: $slug" );
		}
	}

	/**
	 * Test base and contrast colors are appropriate and all colors have sufficient contrast
	 */
	public function test_base_and_contrast_colors(): void {
		$base_colors = array(
			'#1E40AF', // Primary
			'#15803D', // Secondary
			'#B91C1C', // Tertiary
			'#9333EA',  // Accent
		);

		$variations = $this->instance->generate_style_variations( $base_colors );

		foreach ( $variations as $variation ) {
			$palette = $variation['settings']['color']['palette'];

			// Find base and contrast colors
			$base_color     = null;
			$contrast_color = null;
			foreach ( $palette as $color ) {
				if ( $color['slug'] === 'base' ) {
					$base_color = $color['color'];
				}
				if ( $color['slug'] === 'contrast' ) {
					$contrast_color = $color['color'];
				}
			}

			$this->assertNotNull( $base_color, 'Theme should have a base color' );
			$this->assertNotNull( $contrast_color, 'Theme should have a contrast color' );

			// Test base is very light or very dark
			$contrast_checker = $this->instance->get_contrast_checker();
			$base_luminance   = $contrast_checker->calculate_relative_luminance( $base_color );
			$this->assertTrue(
				$base_luminance > self::LIGHT_LUMINANCE_THRESHOLD ||
				$base_luminance < self::DARK_LUMINANCE_THRESHOLD,
				'Base color should be very light or very dark'
			);

			// Test contrast is opposite of base
			$contrast_luminance = $contrast_checker->calculate_relative_luminance( $contrast_color );
			$this->assertTrue(
				( $base_luminance > self::LIGHT_LUMINANCE_THRESHOLD &&
				$contrast_luminance < self::DARK_LUMINANCE_THRESHOLD ) ||
				( $base_luminance < self::DARK_LUMINANCE_THRESHOLD &&
				$contrast_luminance > self::LIGHT_LUMINANCE_THRESHOLD ),
				'Contrast color should be opposite of base color'
			);

			// Test all colors have sufficient contrast with either base or contrast
			foreach ( $palette as $color ) {
				if ( $color['slug'] === 'base' || $color['slug'] === 'contrast' ) {
					continue;
				}

				$base_contrast     = $contrast_checker->get_contrast_ratio( $color['color'], $base_color );
				$contrast_contrast = $contrast_checker->get_contrast_ratio( $color['color'], $contrast_color );

				$this->assertTrue(
					$base_contrast >= self::WCAG_CONTRAST_MIN ||
					$contrast_contrast >= self::WCAG_CONTRAST_MIN,
					"Color {$color['color']} should have sufficient contrast with either base or contrast color"
				);
			}
		}
	}

	/**
	 * Test theme style variations maintain semantic relationships
	 */
	public function test_theme_style_variations_semantics(): void {
		$base_colors = array(
			'#1E40AF', // Primary
			'#15803D', // Secondary
			'#B91C1C', // Tertiary
			'#9333EA',  // Accent
		);

		$variations = $this->instance->generate_style_variations( $base_colors );

		foreach ( $variations as $variation ) {
			$palette = $variation['settings']['color']['palette'];

			// Group colors by role
			$color_groups = array();
			foreach ( $palette as $color ) {
				$base_slug = explode( '-', $color['slug'] )[0];
				if ( ! isset( $color_groups[ $base_slug ] ) ) {
					$color_groups[ $base_slug ] = array();
				}
				$color_groups[ $base_slug ][] = $color;
			}

			// Test each color role's variations
			foreach ( array( 'primary', 'secondary', 'tertiary', 'accent' ) as $role ) {
				if ( isset( $color_groups[ $role ] ) ) {
					$role_colors = $color_groups[ $role ];
					usort(
						$role_colors,
						function ( $a, $b ) {
							return $this->instance->get_contrast_checker()->calculate_relative_luminance( $a['color'] )
							- $this->instance->get_contrast_checker()->calculate_relative_luminance( $b['color'] );
						}
					);

					// Test that variations get progressively lighter
					for ( $i = 1; $i < count( $role_colors ); $i++ ) {
						$this->assertGreaterThan(
							$this->instance->get_contrast_checker()->calculate_relative_luminance( $role_colors[ $i - 1 ]['color'] ),
							$this->instance->get_contrast_checker()->calculate_relative_luminance( $role_colors[ $i ]['color'] ),
							sprintf(
								'Color %s should be lighter than %s',
								$role_colors[ $i ]['name'],
								$role_colors[ $i - 1 ]['name']
							)
						);
					}
				}
			}
		}
	}

	/**
	 * Helper method to count variations that are visually distinct from base color
	 */
	private function count_variations_different_from_base( array $palette, string $base_color, string $role ): int {
		$count = 0;
		foreach ( $palette as $color ) {
			if ( strpos( $color['slug'], $role ) === 0 &&
				$this->instance->get_contrast_checker()->are_colors_visually_distinct( $color['color'], $base_color ) ) {
				++$count;
			}
		}
		return $count;
	}

	public function test_gradients_exist(): void {
		$base_colors = array(
			'#1E40AF', // Primary
			'#15803D', // Secondary
			'#B91C1C', // Tertiary
			'#9333EA',  // Accent
		);

		$variations      = $this->instance->generate_style_variations( $base_colors );
		$first_variation = reset( $variations );

		$this->assertArrayHasKey( 'gradients', $first_variation['settings']['color'] );
		$this->assertNotEmpty( $first_variation['settings']['color']['gradients'] );

		// Basic structure check only
		$first_gradient = reset( $first_variation['settings']['color']['gradients'] );
		$this->assertArrayHasKey( 'name', $first_gradient );
		$this->assertArrayHasKey( 'slug', $first_gradient );
		$this->assertArrayHasKey( 'gradient', $first_gradient );
	}

	/**
	 * Test theme.json color compatibility with both TwentyTwentyThree and TwentyTwentyFour
	 */
	public function test_theme_json_color_compatibility(): void {
		$base_colors = array(
			'#1E40AF', // Primary
			'#15803D', // Secondary
			'#B91C1C', // Tertiary
			'#9333EA',  // Accent
		);

		$variations      = $this->instance->generate_style_variations( $base_colors );
		$first_variation = reset( $variations );
		$palette         = $first_variation['settings']['color']['palette'];

		// TwentyTwentyThree-style slugs (semantic roles)
		$tt3_slugs = array(
			'primary',
			'primary-lighter',
			'primary-light',
			'primary-dark',
			'primary-darker',
			'secondary',
			'secondary-lighter',
			'secondary-light',
			'secondary-dark',
			'secondary-darker',
			'tertiary',
			'tertiary-lighter',
			'tertiary-light',
			'tertiary-dark',
			'tertiary-darker',
			'accent',
			'accent-lighter',
			'accent-light',
			'accent-dark',
			'accent-darker',
		);

		// TwentyTwentyFour-style slugs
		$tt4_slugs = array(
			'base',      // Off-white with hint of primary
			'base-2',    // Pure white
			'contrast',  // Pure black
			'contrast-2', // Dark gray (for borders/decorative)
			'contrast-3', // Medium gray (for borders/decorative)
			'accent',     // From accent variations
			'accent-2',
			'accent-3',
			'accent-4',
			'accent-5',
		);

		// Test TwentyTwentyThree compatibility
		foreach ( $tt3_slugs as $slug ) {
			$found = false;
			foreach ( $palette as $color ) {
				if ( $color['slug'] === $slug ) {
					$found = true;

					// Additional checks for specific colors
					switch ( $slug ) {
						case 'primary':
							$this->assertEquals(
								$base_colors[0],
								$color['color'],
								'Primary color should match input color'
							);
							break;

						case 'secondary':
							$this->assertEquals(
								$base_colors[1],
								$color['color'],
								'Secondary color should match input color'
							);
							break;

						case 'tertiary':
							$this->assertEquals(
								$base_colors[2],
								$color['color'],
								'Tertiary color should match input color'
							);
							break;

						case 'accent':
							$this->assertEquals(
								$base_colors[3],
								$color['color'],
								'Accent color should match input color'
							);
							break;
					}
					break;
				}
			}
			$this->assertTrue( $found, "Missing TwentyTwentyThree-compatible slug: $slug" );
		}

		// Test TwentyTwentyFour compatibility
		foreach ( $tt4_slugs as $slug ) {
			$found = false;
			foreach ( $palette as $color ) {
				if ( $color['slug'] === $slug ) {
					$found = true;

					// Additional checks for specific colors
					switch ( $slug ) {
						case 'base':
							// Should be off-white with a hint of primary
							$base_luminance = $this->instance->get_contrast_checker()->calculate_relative_luminance( $color['color'] );
							if ( $base_luminance > self::LIGHT_LUMINANCE_THRESHOLD ) {
								// Light mode: very light color
								$this->assertGreaterThan(
									self::LIGHT_LUMINANCE_THRESHOLD,
									$base_luminance,
									'Base color should be very light in light mode'
								);
							} else {
								// Dark mode: very dark color
								$this->assertLessThan(
									self::DARK_LUMINANCE_THRESHOLD,
									$base_luminance,
									'Base color should be very dark in dark mode'
								);
							}
							break;

						case 'base-2':
							$this->assertEquals(
								self::COLOR_WHITE,
								$color['color'],
								'Base-2 should be pure white'
							);
							break;

						case 'contrast':
							$this->assertEquals(
								self::COLOR_NEAR_BLACK,
								$color['color'],
								'Contrast should be near-black'
							);
							break;

						case 'contrast-2':
						case 'contrast-3':
							// Contrast colors follow light/dark mode patterns while maintaining WCAG contrast
							$luminance = $this->instance->get_contrast_checker()->calculate_relative_luminance( $color['color'] );
							if ( $luminance > self::LIGHT_LUMINANCE_THRESHOLD ) {
								// Light mode: very light color
								$this->assertGreaterThan(
									self::LIGHT_LUMINANCE_THRESHOLD,
									$luminance,
									"$slug should be very light in light mode"
								);
							} else {
								// Dark mode: very dark color
								$this->assertLessThan(
									self::DARK_LUMINANCE_THRESHOLD,
									$luminance,
									"$slug should be very dark in dark mode"
								);
							}
							$this->assertTrue(
								$this->instance->get_contrast_checker()->get_contrast_ratio( $color['color'], self::COLOR_WHITE ) >= self::WCAG_CONTRAST_MIN ||
								$this->instance->get_contrast_checker()->get_contrast_ratio( $color['color'], self::COLOR_NEAR_BLACK ) >= self::WCAG_CONTRAST_MIN,
								"$slug should maintain WCAG contrast requirements"
							);
							break;

						case 'accent':
							$this->assertEquals(
								$base_colors[3],
								$color['color'],
								'Accent color should match input color'
							);
							break;
					}
					break;
				}
			}
			$this->assertTrue( $found, "Missing TwentyTwentyFour-compatible slug: $slug" );
		}

		// Test color ordering
		$last_colors = array_slice( $palette, -2, 2 );
		$this->assertEquals( 'White', $last_colors[0]['name'], 'White should be second to last' );
		$this->assertEquals( 'Black', $last_colors[1]['name'], 'Black should be last' );
	}

	/**
	 * Test that contrast ratios meet WCAG AAA when possible, but never fall below AA
	 */
	public function test_contrast_ratios() {
		$generator = new Color_Variation_Generator( $this->instance->get_contrast_checker() );

		// Test with different base colors
		$test_colors = array(
			self::COLOR_MID_GRAY,
			self::COLOR_NEAR_BLACK,
			self::COLOR_WHITE,
		);

		foreach ( $test_colors as $base_color ) {
			$variations = $generator->generate_tints_and_shades(
				$base_color,
				array(
					'is_dark_mode' => false,
				)
			);

			foreach ( $variations['variations'] as $key => $color ) {
				if ( $key === 'contrast' ) {
					$contrast_ratio = $this->get_contrast_ratio(
						$variations['variations']['base'],
						$color
					);

					// Must meet at least AA
					$this->assertGreaterThanOrEqual(
						self::WCAG_CONTRAST_MIN,
						$contrast_ratio,
						'Color contrast must meet WCAG AA minimum'
					);

					// Should not exceed maximum contrast
					$this->assertLessThanOrEqual(
						self::CONTRAST_MAX,
						$contrast_ratio,
						'Color contrast should not be too harsh'
					);

					// Should aim for AAA when possible
					if ( $contrast_ratio < self::WCAG_CONTRAST_TARGET ) {
						// If we don't meet AAA, ensure it's because we're preventing harsh contrast
						$this->assertGreaterThanOrEqual(
							self::WCAG_CONTRAST_MIN,
							$contrast_ratio,
							'Color contrast should meet WCAG AA if AAA is not achievable'
						);
					}
				}
			}
		}
	}

	/**
	 * Test that shades maintain proper contrast relationships
	 */
	public function test_shade_contrast_relationships() {
		$generator  = new Color_Variation_Generator( $this->instance->get_contrast_checker() );
		$variations = $generator->generate_tints_and_shades(
			self::COLOR_MID_GRAY,
			array(
				'is_dark_mode' => false,
			)
		);

		$base_color        = $variations['variations']['base'];
		$previous_contrast = 0;

		// Test that darker shades maintain increasing contrast
		foreach ( array( 'darker20', 'darker10' ) as $shade ) {
			if ( isset( $variations['variations'][ $shade ] ) ) {
				$current_contrast = $this->get_contrast_ratio(
					$variations['variations']['base'],
					$variations['variations'][ $shade ]
				);
				$this->assertGreaterThan(
					$previous_contrast,
					$current_contrast,
					'Darker shades should have increasing contrast with base'
				);

				$previous_contrast = $current_contrast;
			}
		}

		// Reset for lighter shades
		$previous_contrast = 0;

		// Test that lighter shades maintain increasing contrast
		foreach ( array( 'lighter10', 'lighter20' ) as $shade ) {
			if ( isset( $variations['variations'][ $shade ] ) ) {
				$current_contrast = $this->get_contrast_ratio(
					$variations['variations']['base'],
					$variations['variations'][ $shade ]
				);
				$this->assertGreaterThan(
					$previous_contrast,
					$current_contrast,
					'Lighter shades should have increasing contrast with base'
				);

				$previous_contrast = $current_contrast;
			}
		}
	}

	/**
	 * Test generating theme.json variations with invalid scheme
	 */
	public function test_generate_theme_json_variations_invalid_scheme() {
		$colors = array(
			'primary'   => '#1E40AF',
			'secondary' => '#15803D',
		);

		$result = $this->instance->generate_theme_json_variations( $colors, 'invalid' );
		$this->assertWPError( $result );
		$this->assertEquals( 'invalid_scheme', $result->get_error_code() );
	}

	/**
	 * Test generating theme.json variations with missing required color
	 */
	public function test_generate_theme_json_variations_missing_required_color() {
		$colors = array(
			'primary' => '#1E40AF',
		);

		$result = $this->instance->generate_theme_json_variations( $colors, 'complementary' );
		$this->assertWPError( $result );
		$this->assertEquals( 'missing_color', $result->get_error_code() );
	}

	/**
	 * Test generating theme.json variations with monochromatic scheme
	 */
	public function test_generate_theme_json_variations_monochromatic() {
		$colors = array(
			'primary' => '#1E40AF',
		);

		$variations = $this->instance->generate_theme_json_variations( $colors, 'monochromatic' );
		$this->assertIsArray( $variations );
		$this->assertNotEmpty( $variations );
	}

	/**
	 * Test generating theme.json variations with triadic scheme
	 */
	public function test_generate_theme_json_variations_triadic() {
		$colors = array(
			'primary'   => '#1E40AF',
			'secondary' => '#15803D',
			'tertiary'  => '#9333EA',
		);

		$variations = $this->instance->generate_theme_json_variations( $colors, 'triadic' );
		$this->assertIsArray( $variations );
		$this->assertNotEmpty( $variations );
	}

	public function test_theme_json_color_roles() {
		$colors = array(
			'primary'   => '#1E40AF',
			'secondary' => '#15803D',
		);

		$variations = $this->instance->generate_theme_json_variations( $colors, 'complementary' );

		foreach ( $variations as $variation ) {
			$palette = $variation['settings']['color']['palette'];

			// Test that each variation has all required colors represented
			$found_colors = array_fill_keys( self::COLOR_ROLES, false );
			foreach ( $palette as $color ) {
				foreach ( self::COLOR_ROLES as $role ) {
					if ( strpos( $color['slug'], $role ) === 0 ) {
						$found_colors[ $role ] = true;
					}
				}
			}

			foreach ( $found_colors as $role => $found ) {
				$this->assertTrue( $found, "Missing required role: $role" );
			}

			// Test that colors are visually distinct
			$colors_to_check = array();
			foreach ( $palette as $color ) {
				$colors_to_check[] = $color['color'];
			}
			$this->assertTrue(
				$this->instance->get_color_utility()->are_colors_distinct( $colors_to_check ),
				'Colors should be visually distinct'
			);
		}
	}

	/**
	 * Test base and contrast color generation
	 */
	public function test_base_and_contrast_color_generation(): void {
		$colors = array(
			'primary'   => self::COLOR_NEAR_BLACK,
			'secondary' => self::COLOR_DARK_GRAY,
		);

		$variations = $this->instance->generate_theme_json_variations( $colors, 'complementary' );

		// Test light mode
		$light_theme = reset( $variations );
		$palette     = $light_theme['settings']['color']['palette'];

		// Find base and contrast colors
		$base_color     = null;
		$contrast_color = null;
		foreach ( $palette as $color ) {
			if ( $color['slug'] === 'base' ) {
				$base_color = $color['color'];
			}
			if ( $color['slug'] === 'contrast' ) {
				$contrast_color = $color['color'];
			}
		}

		$this->assertNotNull( $base_color, 'Base color should be generated' );
		$this->assertNotNull( $contrast_color, 'Contrast color should be generated' );

		// Test contrast ratio meets WCAG requirements
		$contrast_ratio = $this->get_contrast_ratio( $base_color, $contrast_color );
		$this->assertGreaterThanOrEqual(
			self::WCAG_CONTRAST_MIN,
			$contrast_ratio,
			'Base and contrast colors should meet WCAG contrast requirements'
		);
	}

	/**
	 * Test neutral color generation
	 */
	public function test_neutral_color_generation(): void {
		$colors = array(
			'primary'   => self::COLOR_NEAR_BLACK,
			'secondary' => self::COLOR_DARK_GRAY,
		);

		$variations = $this->instance->generate_theme_json_variations( $colors, 'complementary' );

		// Test light mode
		$light_theme = reset( $variations );
		$palette     = $light_theme['settings']['color']['palette'];

		// Find neutral colors
		$neutral_colors = array();
		foreach ( $palette as $color ) {
			if ( strpos( $color['slug'], 'neutral-' ) === 0 ) {
				$neutral_colors[] = $color['color'];
			}
		}

		$this->assertNotEmpty( $neutral_colors, 'Neutral colors should be generated' );

		// Test that neutral colors form a gradient between base and contrast
		$this->assertGreaterThan(
			2,
			count( $neutral_colors ),
			'Should generate multiple neutral colors'
		);

		// Test contrast ratios between adjacent neutral colors
		for ( $i = 0; $i < count( $neutral_colors ) - 1; $i++ ) {
			$contrast = $this->get_contrast_ratio(
				$neutral_colors[ $i ],
				$neutral_colors[ $i + 1 ]
			);
			$this->assertGreaterThanOrEqual(
				self::READABLE_CONTRAST_MIN,
				$contrast,
				'Adjacent neutral colors should have sufficient contrast'
			);
		}
	}
}
