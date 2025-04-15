<?php declare(strict_types=1);

/**
 * Color Palette Exporter WP_Mock Tests
 *
 * Tests for WordPress-dependent methods in the Color_Palette_Exporter class.
 * Note: there is also a Unit test file for this class.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock\Classes
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\WP_Mock\Classes;

use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use GL_Color_Palette_Generator\Color_Management\Color_Palette_Exporter;
use GL_Color_Palette_Generator\Color_Management\Color_Palette_Formatter;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Classes\Color_Palette;
use Brain\Monkey\Functions;
use WP_Mock;
use WP_Error;

/**
 * Tests for WordPress-dependent methods in the Color_Palette_Exporter class
 */
class Test_Color_Palette_Exporter extends WP_Mock_Test_Case {
    /**
     * Exporter instance
     *
     * @var Color_Palette_Exporter
     */
    protected $exporter;

    /**
     * Test palette
     *
     * @var Color_Palette
     */
    protected $test_palette;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();
        \Brain\Monkey\setUp();

        // Mock the formatter
        $formatter = $this->createMock(Color_Palette_Formatter::class);

        // Create the exporter with mocked dependencies
        $this->exporter = new Color_Palette_Exporter($formatter);

        // Create a test palette
        $this->test_palette = new Color_Palette(
            array(
                'name'     => 'Test Palette',
                'colors'   => array('#FF0000', '#00FF00', '#0000FF'),
                'metadata' => array(
                    'type'       => 'test',
                    'created_at' => '2024-03-14T12:00:00Z',
                    'version'    => '1.0.0',
                ),
            )
        );
    }

    /**
     * Tear down test environment
     */
    public function tearDown(): void {
        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test export_to_json method with WordPress dependencies
     */
    public function test_export_to_json(): void {
        $palettes = array(
            array(
                'name'     => 'Test Palette',
                'colors'   => array('#FF0000', '#00FF00', '#0000FF'),
                'created_at' => '2024-03-14 12:00:00',
            )
        );

        // Mock WordPress functions
        Functions\expect('current_time')
            ->once()
            ->with('mysql')
            ->andReturn('2024-04-12 12:00:00');

        Functions\expect('wp_json_encode')
            ->once()
            ->andReturnUsing(function($data, $options) {
                return json_encode($data, $options);
            });

        // Define GL_CPG_VERSION if not defined
        if (!defined('GL_CPG_VERSION')) {
            define('GL_CPG_VERSION', '1.0.0');
        }

        $result = $this->exporter->export_to_json($palettes);

        $this->assertIsString($result);
        $data = json_decode($result, true);
        $this->assertIsArray($data);
        $this->assertEquals('1.0.0', $data['version']);
        $this->assertEquals('2024-04-12 12:00:00', $data['exported_at']);
        $this->assertCount(1, $data['palettes']);
    }

    /**
     * Test to_svg method with WordPress esc_attr dependency
     */
    public function test_to_svg(): void {
        // Mock esc_attr
        Functions\expect('esc_attr')
            ->atLeast(1)
            ->andReturnUsing(function($text) {
                return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            });

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->exporter);
        $method = $reflection->getMethod('to_svg');
        $method->setAccessible(true);

        $palette = array('#FF0000', '#00FF00', '#0000FF');
        $options = array('size' => 50, 'gap' => 5);

        $result = $method->invokeArgs($this->exporter, array($palette, $options));

        $this->assertIsString($result);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('fill="#FF0000"', $result);
        $this->assertStringContainsString('fill="#00FF00"', $result);
        $this->assertStringContainsString('fill="#0000FF"', $result);
    }

    /**
     * Test to_bootstrap method with WordPress sanitize_title dependency
     */
    public function test_to_bootstrap(): void {
        // Mock sanitize_title
        Functions\expect('sanitize_title')
            ->atLeast(1)
            ->andReturnUsing(function($title) {
                return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
            });

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->exporter);
        $method = $reflection->getMethod('to_bootstrap');
        $method->setAccessible(true);

        $palette = array(
            'primary' => '#FF0000',
            'secondary' => '#00FF00',
            'accent' => '#0000FF'
        );
        $options = array();

        $result = $method->invokeArgs($this->exporter, array($palette, $options));

        $this->assertIsString($result);
        $this->assertStringContainsString('// Custom color variables', $result);
        $this->assertStringContainsString('$theme-colors:', $result);
        $this->assertStringContainsString('"primary":', $result);
        $this->assertStringContainsString('#FF0000', $result);
    }

    /**
     * Test import_from_csv method with WordPress WP_Error dependency
     */
    public function test_import_from_csv_file_not_found(): void {
        // Mock file_exists
        Functions\expect('file_exists')
            ->once()
            ->andReturn(false);

        $result = $this->exporter->import_from_csv('/nonexistent/file.csv');

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('file_not_found', $result->get_error_code());
    }

    /**
     * Test import_from_csv method with successful import
     */
    public function test_import_from_csv_success(): void {
        // Mock file_exists
        Functions\expect('file_exists')
            ->once()
            ->andReturn(true);

        // Mock fopen, fgetcsv, fclose
        $this->mock_csv_functions(array(
            array('Primary', '#FF0000'),
            array('Secondary', '#00FF00'),
            array('Accent', '#0000FF')
        ));

        // Mock is_valid_hex_color
        $color_utility = $this->getMockBuilder(Color_Utility::class)
            ->getMock();
        $color_utility->method('is_valid_hex_color')
            ->willReturn(true);

        // Set the mocked color utility
        $reflection = new \ReflectionClass($this->exporter);
        $property = $reflection->getProperty('color_utility');
        $property->setAccessible(true);
        $property->setValue($this->exporter, $color_utility);

        $result = $this->exporter->import_from_csv('/path/to/file.csv');

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals('#FF0000', $result['Primary']);
        $this->assertEquals('#00FF00', $result['Secondary']);
        $this->assertEquals('#0000FF', $result['Accent']);
    }

    /**
     * Helper to mock CSV file functions
     */
    private function mock_csv_functions(array $csv_data): void {
        // Mock fopen
        Functions\expect('fopen')
            ->once()
            ->andReturn(true);

        // Mock fgetcsv to return each row in sequence
        Functions\expect('fgetcsv')
            ->andReturnUsing(function() use (&$csv_data) {
                static $index = 0;
                if ($index < count($csv_data)) {
                    return $csv_data[$index++];
                }
                return false;
            });

        // Mock fclose
        Functions\expect('fclose')
            ->once()
            ->andReturn(true);
    }
}
