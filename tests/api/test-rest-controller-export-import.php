<?php
/**
 * Test REST Controller Export/Import Endpoints
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Tests\API;

use GL_Color_Palette_Generator\API\Rest_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Class Test_Rest_Controller_Export_Import
 */
class Test_Rest_Controller_Export_Import extends TestCase {
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * Controller instance
     *
     * @var Rest_Controller
     */
    private $controller;

    /**
     * Setup test environment
     */
    protected function setUp(): void {
        parent::setUp();
        \Brain\Monkey\setUp();
        global $wpdb;
        $wpdb = Mockery::mock('\wpdb');
        $wpdb->prefix = 'wp_';

        $this->controller = new Rest_Controller();
    }

    /**
     * Teardown test environment
     */
    protected function tearDown(): void {
        Mockery::close();
        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test successful JSON export
     */
    public function test_export_palettes_json_success() {
        global $wpdb;

        $test_palettes = [
            [
                'name' => 'Test Palette',
                'colors' => ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff'],
                'created_at' => '2024-01-01 12:00:00'
            ]
        ];

        $wpdb->shouldReceive('get_results')
            ->once()
            ->andReturn($test_palettes);

        $request = new WP_REST_Request('GET', '/gl-cpg/v1/export');
        $request->set_param('format', 'json');

        $response = $this->controller->export_palettes($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertEquals('application/json', $data['mime_type']);
        $this->assertEquals('color-palettes.json', $data['filename']);
    }

    /**
     * Test successful CSV export
     */
    public function test_export_palettes_csv_success() {
        global $wpdb;

        $test_palettes = [
            [
                'name' => 'Test Palette',
                'colors' => ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff'],
                'created_at' => '2024-01-01 12:00:00'
            ]
        ];

        $wpdb->shouldReceive('get_results')
            ->once()
            ->andReturn($test_palettes);

        $request = new WP_REST_Request('GET', '/gl-cpg/v1/export');
        $request->set_param('format', 'csv');

        $response = $this->controller->export_palettes($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertEquals('text/csv', $data['mime_type']);
        $this->assertEquals('color-palettes.csv', $data['filename']);
    }

    /**
     * Test database error during export
     */
    public function test_export_palettes_database_error() {
        global $wpdb;

        $wpdb->shouldReceive('get_results')
            ->once()
            ->andReturn(false);

        $request = new WP_REST_Request('GET', '/gl-cpg/v1/export');
        $request->set_param('format', 'json');

        $response = $this->controller->export_palettes($request);

        $this->assertInstanceOf(WP_Error::class, $response);
        $this->assertEquals('database_error', $response->get_error_code());
    }

    /**
     * Test successful JSON import
     */
    public function test_import_palettes_json_success() {
        global $wpdb;

        $test_file = [
            'file' => [
                'tmp_name' => 'test.json',
                'name' => 'test.json',
                'error' => 0
            ]
        ];

        Functions\when('file_get_contents')->justReturn(json_encode([
            'version' => '1.0.0',
            'palettes' => [
                [
                    'name' => 'Test Palette',
                    'colors' => ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff']
                ]
            ]
        ]));

        $wpdb->shouldReceive('insert')
            ->once()
            ->andReturn(1);

        Functions\when('current_time')->justReturn('2024-01-01 12:00:00');

        $request = new WP_REST_Request('POST', '/gl-cpg/v1/import');
        $request->set_file_params($test_file);

        $response = $this->controller->import_palettes($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertEquals(1, $data['imported_count']);
    }

    /**
     * Test file upload error during import
     */
    public function test_import_palettes_upload_error() {
        $test_file = [
            'file' => [
                'error' => 1
            ]
        ];

        $request = new WP_REST_Request('POST', '/gl-cpg/v1/import');
        $request->set_file_params($test_file);

        $response = $this->controller->import_palettes($request);

        $this->assertInstanceOf(WP_Error::class, $response);
        $this->assertEquals('upload_error', $response->get_error_code());
    }
} 
