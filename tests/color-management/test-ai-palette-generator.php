<?php
namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use PHPUnit\Framework\TestCase;

class Test_AI_Palette_Generator extends TestCase {
    private $generator;

    protected function setUp(): void {
        $this->generator = new Color_Palette_Generator();
    }

    public function test_generate_ai_palette_requires_context() {
        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate_palette(['scheme_type' => Color_Constants::SCHEME_AI_GENERATED]);
    }

    public function test_generate_ai_palette_with_business_context() {
        $criteria = [
            'scheme_type' => Color_Constants::SCHEME_AI_GENERATED,
            'business_context' => [
                'description' => 'Professional law firm specializing in corporate law',
                'industry' => 'legal',
                'target_audience' => 'Corporate executives and business owners',
                'mood' => 'Professional, trustworthy, established'
            ]
        ];

        $palette = $this->generator->generate_palette($criteria);

        $this->assertIsArray($palette);
        $this->assertArrayHasKey('colors', $palette);
        $this->assertArrayHasKey('primary', $palette['colors']);
        $this->assertArrayHasKey('hex', $palette['colors']['primary']);
        $this->assertArrayHasKey('name', $palette['colors']['primary']);
        $this->assertArrayHasKey('emotion', $palette['colors']['primary']);
        $this->assertEquals('business', $palette['inspiration']['type']);
    }

    public function test_generate_palette_from_image_extract() {
        $criteria = [
            'scheme_type' => Color_Constants::SCHEME_AI_GENERATED,
            'image_data' => [
                'image_path' => __DIR__ . '/fixtures/desert-sunset.jpg',
                'context_type' => 'extract'
            ]
        ];

        $palette = $this->generator->generate_palette($criteria);

        $this->assertIsArray($palette);
        $this->assertArrayHasKey('colors', $palette);
        $this->assertArrayHasKey('primary', $palette['colors']);
        $this->assertArrayHasKey('hex', $palette['colors']['primary']);
        $this->assertArrayHasKey('name', $palette['colors']['primary']);
        $this->assertEquals('Extracted from image', $palette['colors']['primary']['emotion']);
        $this->assertEquals('image', $palette['inspiration']['type']);
    }

    public function test_generate_palette_from_image_inspire() {
        $criteria = [
            'scheme_type' => Color_Constants::SCHEME_AI_GENERATED,
            'image_data' => [
                'image_path' => __DIR__ . '/fixtures/desert-sunset.jpg',
                'context_type' => 'inspire'
            ],
            'business_context' => [
                'description' => 'Professional law firm in Arizona',
                'industry' => 'legal',
                'mood' => 'Professional yet warm and approachable'
            ]
        ];

        $palette = $this->generator->generate_palette($criteria);

        $this->assertIsArray($palette);
        $this->assertArrayHasKey('colors', $palette);
        $this->assertArrayHasKey('primary', $palette['colors']);
        $this->assertArrayHasKey('hex', $palette['colors']['primary']);
        $this->assertArrayHasKey('name', $palette['colors']['primary']);
        $this->assertArrayHasKey('emotion', $palette['colors']['primary']);
        $this->assertEquals('image', $palette['inspiration']['type']);
    }

    public function test_get_available_algorithms() {
        $algorithms = $this->generator->get_available_algorithms();
        
        $this->assertIsArray($algorithms);
        $this->assertContains(Color_Constants::SCHEME_MONOCHROMATIC, $algorithms);
        $this->assertContains(Color_Constants::SCHEME_AI_GENERATED, $algorithms);
    }
}
