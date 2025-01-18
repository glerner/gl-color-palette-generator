<?php
/**
 * Color Theme Manager Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Theme_Manager;

class Test_Color_Theme_Manager extends Unit_Test_Case {
    private $manager;

    public function setUp(): void {
        $this->manager = $this->createMock(Color_Theme_Manager::class);
    }

    /**
     * Test that create_theme returns a valid theme structure
     */
    public function test_create_theme_returns_valid_structure(): void {
        // Arrange
        $scheme = [
            'primary' => '#FF0000',
            'secondary' => '#00FF00',
            'accent' => '#0000FF'
        ];

        $options = [
            'name' => 'Modern Light',
            'platform' => 'web',
            'dark_variant' => true
        ];

        $expected = [
            'id' => 'theme_001',
            'light' => [
                'primary' => '#FF0000',
                'secondary' => '#00FF00',
                'background' => '#FFFFFF'
            ],
            'dark' => [
                'primary' => '#FF3333',
                'secondary' => '#33FF33',
                'background' => '#1A1A1A'
            ],
            'breakpoints' => [
                'mobile' => '768px',
                'tablet' => '1024px'
            ],
            'metadata' => [
                'created' => '2024-12-08T19:10:46-07:00',
                'version' => '1.0'
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('create_theme')
            ->with($scheme, $options)
            ->willReturn($expected);

        // Act
        $result = $this->manager->create_theme($scheme, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('light', $result);
        $this->assertArrayHasKey('dark', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertStringStartsWith('theme_', $result['id']);
        $this->assertArrayHasKey('primary', $result['light']);
        $this->assertArrayHasKey('secondary', $result['light']);
    }

    /**
     * Test that apply_theme returns formatted output for the specified platform
     */
    public function test_apply_theme_returns_formatted_output(): void {
        // Arrange
        $theme = [
            'light' => [
                'primary' => '#FF0000',
                'secondary' => '#00FF00'
            ]
        ];

        $platform = 'web';
        $options = [
            'format' => 'css',
            'minify' => true
        ];

        $expected = [
            'content' => ':root{--primary:#FF0000;--secondary:#00FF00}',
            'filename' => 'theme.min.css',
            'variables' => [
                'primary' => '--primary',
                'secondary' => '--secondary'
            ],
            'metadata' => [
                'format' => 'css',
                'timestamp' => '2024-12-08T19:10:46-07:00'
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('apply_theme')
            ->with($theme, $platform, $options)
            ->willReturn($expected);

        // Act
        $result = $this->manager->apply_theme($theme, $platform, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('variables', $result);
        $this->assertStringContainsString('--primary', $result['content']);
        $this->assertStringContainsString('--secondary', $result['content']);
        $this->assertStringEndsWith('.css', $result['filename']);
    }

    /**
     * Test that validate_theme performs comprehensive validation of theme structure
     */
    public function test_validate_theme_performs_comprehensive_validation(): void {
        // Arrange
        $theme = [
            'light' => ['primary' => '#FF0000'],
            'dark' => ['primary' => '#CC0000']
        ];

        $platforms = ['web', 'mobile'];

        $expected = [
            'is_valid' => true,
            'compatibility' => [
                'web' => ['compatible' => true],
                'mobile' => ['compatible' => true]
            ],
            'issues' => [],
            'suggestions' => [
                'Consider adding secondary colors'
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('validate_theme')
            ->with($theme, $platforms)
            ->willReturn($expected);

        // Act
        $result = $this->manager->validate_theme($theme, $platforms);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('is_valid', $result);
        $this->assertArrayHasKey('compatibility', $result);
        $this->assertArrayHasKey('issues', $result);
        $this->assertIsBool($result['is_valid']);
        $this->assertTrue($result['is_valid']);
        $this->assertEmpty($result['issues']);
    }

    /**
     * Test that generate_variations returns valid theme variants
     */
    public function test_generate_variations_returns_valid_variants(): void {
        // Arrange
        $theme = [
            'light' => ['primary' => '#FF0000']
        ];

        $options = [
            'contrast_levels' => ['high', 'low'],
            'seasonal' => true
        ];

        $expected = [
            'variants' => [
                'high_contrast' => [
                    'light' => ['primary' => '#FF0000'],
                    'dark' => ['primary' => '#000000']
                ],
                'seasonal_summer' => [
                    'light' => ['primary' => '#FF6633']
                ]
            ],
            'relationships' => [
                'high_contrast' => 'accessibility_variant',
                'seasonal_summer' => 'seasonal_variant'
            ],
            'metadata' => [
                'generation_method' => 'contrast_based',
                'timestamp' => '2024-12-08T19:10:46-07:00'
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('generate_variations')
            ->with($theme, $options)
            ->willReturn($expected);

        // Act
        $result = $this->manager->generate_variations($theme, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('variants', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('high_contrast', $result['variants']);
        $this->assertArrayHasKey('seasonal_summer', $result['variants']);
    }

    /**
     * Test that validate_theme identifies invalid theme structures
     * 
     * @dataProvider invalidThemeProvider
     */
    public function test_validate_theme_identifies_invalid_themes($theme): void {
        // Arrange
        $expected = [
            'is_valid' => false,
            'compatibility' => [],
            'issues' => ['Invalid theme structure'],
            'suggestions' => ['Add required color variables']
        ];

        $this->manager
            ->expects($this->once())
            ->method('validate_theme')
            ->with($theme)
            ->willReturn($expected);

        // Act
        $result = $this->manager->validate_theme($theme);

        // Assert
        $this->assertFalse($result['is_valid']);
        $this->assertNotEmpty($result['issues']);
        $this->assertContains('Invalid theme structure', $result['issues']);
    }

    /**
     * Test that apply_theme handles invalid platform specifications
     * 
     * @dataProvider invalidPlatformProvider
     */
    public function test_apply_theme_handles_invalid_platforms($theme, $platform): void {
        $this->manager
            ->expects($this->once())
            ->method('apply_theme')
            ->with($theme, $platform)
            ->willThrowException(new \InvalidArgumentException('Invalid platform specified'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid platform specified');
        $this->manager->apply_theme($theme, $platform);
    }

    /**
     * Data provider for invalid theme tests
     */
    public function invalidThemeProvider(): array {
        return [
            'empty theme' => [[]],
            'missing light mode' => [['dark' => ['primary' => '#000000']]],
            'invalid color code' => [['light' => ['primary' => 'invalid']]],
            'incomplete theme' => [['light' => []]],
            'null theme' => [null],
            'non-array theme' => ['invalid']
        ];
    }

    /**
     * Data provider for invalid platform tests
     */
    public function invalidPlatformProvider(): array {
        return [
            'empty platform' => [['light' => ['primary' => '#FF0000']], ''],
            'invalid platform' => [['light' => ['primary' => '#FF0000']], 'invalid'],
            'unsupported platform' => [['light' => ['primary' => '#FF0000']], 'legacy'],
            'numeric platform' => [['light' => ['primary' => '#FF0000']], '123'],
            'null platform' => [['light' => ['primary' => '#FF0000']], null]
        ];
    }
}