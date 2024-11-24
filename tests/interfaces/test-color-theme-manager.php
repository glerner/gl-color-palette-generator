<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorThemeManager;

class ColorThemeManagerTest extends TestCase {
    private $manager;

    protected function setUp(): void {
        $this->manager = $this->createMock(ColorThemeManager::class);
    }

    public function test_create_theme_returns_valid_structure(): void {
        / Arrange
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
                'created' => '2024-01-20',
                'version' => '1.0'
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('create_theme')
            ->with($scheme, $options)
            ->willReturn($expected);

        / Act
        $result = $this->manager->create_theme($scheme, $options);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('light', $result);
        $this->assertArrayHasKey('dark', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_apply_theme_returns_formatted_output(): void {
        / Arrange
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
                'timestamp' => '2024-01-20'
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('apply_theme')
            ->with($theme, $platform, $options)
            ->willReturn($expected);

        / Act
        $result = $this->manager->apply_theme($theme, $platform, $options);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('variables', $result);
    }

    public function test_validate_theme_performs_comprehensive_validation(): void {
        / Arrange
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

        / Act
        $result = $this->manager->validate_theme($theme, $platforms);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('is_valid', $result);
        $this->assertArrayHasKey('compatibility', $result);
        $this->assertArrayHasKey('issues', $result);
        $this->assertIsBool($result['is_valid']);
    }

    public function test_generate_variations_returns_valid_variants(): void {
        / Arrange
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
                'timestamp' => '2024-01-20'
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('generate_variations')
            ->with($theme, $options)
            ->willReturn($expected);

        / Act
        $result = $this->manager->generate_variations($theme, $options);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('variants', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * @dataProvider invalidThemeProvider
     */
    public function test_validate_theme_identifies_invalid_themes(array $theme): void {
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

        $result = $this->manager->validate_theme($theme);
        $this->assertFalse($result['is_valid']);
        $this->assertNotEmpty($result['issues']);
    }

    public function invalidThemeProvider(): array {
        return [
            'empty_theme' => [[]],
            'missing_light' => [['dark' => ['primary' => '#000000']]],
            'invalid_color' => [['light' => ['primary' => 'invalid']]],
            'incomplete_theme' => [['light' => []]]
        ];
    }

    /**
     * @dataProvider invalidPlatformProvider
     */
    public function test_apply_theme_handles_invalid_platforms(
        array $theme,
        string $platform
    ): void {
        $this->manager
            ->expects($this->once())
            ->method('apply_theme')
            ->with($theme, $platform)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->manager->apply_theme($theme, $platform);
    }

    public function invalidPlatformProvider(): array {
        return [
            'empty_platform' => [['light' => []], ''],
            'invalid_platform' => [['light' => []], 'invalid'],
            'unsupported_platform' => [['light' => []], 'legacy']
        ];
    }
} 
