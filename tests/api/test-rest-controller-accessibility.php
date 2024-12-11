<?php
/**
 * Test REST Controller Accessibility Endpoint
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
use Mockery;

/**
 * Class Test_Rest_Controller_Accessibility
 */
class Test_Rest_Controller_Accessibility extends TestCase {
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
     * Test successful contrast check
     */
    public function test_check_contrast_success() {
        $request = new WP_REST_Request('POST', '/gl-cpg/v1/check-contrast');
        $request->set_param('text_color', '#000000');
        $request->set_param('bg_color', '#ffffff');

        $response = $this->controller->check_contrast($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertArrayHasKey('contrast_ratio', $data);
        $this->assertArrayHasKey('aa_large', $data);
        $this->assertArrayHasKey('aa_small', $data);
        $this->assertArrayHasKey('aaa_large', $data);
        $this->assertArrayHasKey('aaa_small', $data);
        $this->assertArrayHasKey('recommendations', $data);
    }

    /**
     * Test missing colors error
     */
    public function test_check_contrast_missing_colors() {
        $request = new WP_REST_Request('POST', '/gl-cpg/v1/check-contrast');
        $request->set_param('text_color', '#000000');
        // Missing bg_color

        $response = $this->controller->check_contrast($request);

        $this->assertInstanceOf(WP_Error::class, $response);
        $this->assertEquals('missing_colors', $response->get_error_code());
    }

    /**
     * Test invalid color format
     */
    public function test_check_contrast_invalid_color_format() {
        $request = new WP_REST_Request('POST', '/gl-cpg/v1/check-contrast');
        $request->set_param('text_color', 'invalid');
        $request->set_param('bg_color', '#ffffff');

        $response = $this->controller->check_contrast($request);

        $this->assertInstanceOf(WP_Error::class, $response);
    }

    /**
     * Test high contrast combination
     */
    public function test_check_contrast_high_contrast() {
        $request = new WP_REST_Request('POST', '/gl-cpg/v1/check-contrast');
        $request->set_param('text_color', '#000000');
        $request->set_param('bg_color', '#ffffff');

        $response = $this->controller->check_contrast($request);
        $data = $response->get_data();

        $this->assertTrue($data['aa_large']);
        $this->assertTrue($data['aa_small']);
        $this->assertGreaterThan(4.5, $data['contrast_ratio']);
    }

    /**
     * Test low contrast combination
     */
    public function test_check_contrast_low_contrast() {
        $request = new WP_REST_Request('POST', '/gl-cpg/v1/check-contrast');
        $request->set_param('text_color', '#777777');
        $request->set_param('bg_color', '#888888');

        $response = $this->controller->check_contrast($request);
        $data = $response->get_data();

        $this->assertFalse($data['aa_small']);
        $this->assertLessThan(4.5, $data['contrast_ratio']);
        $this->assertNotEmpty($data['recommendations']);
    }
} 
