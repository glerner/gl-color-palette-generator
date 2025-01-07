<?php
/**
 * Tests for REST Controller Accessibility
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\API
 */

namespace GL_Color_Palette_Generator\Tests\API;

use GL_Color_Palette_Generator\Tests\WP_Mock_Test_Case;
use GL_Color_Palette_Generator\API\Rest_Controller_Accessibility;
use WP_Mock;

/**
 * Test REST Controller Accessibility functionality
 */
class Test_Rest_Controller_Accessibility extends WP_Mock_Test_Case {
    /** @var Rest_Controller_Accessibility */
    private $controller;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->controller = new Rest_Controller_Accessibility();
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function test_register_routes() {
        WP_Mock::userFunction('register_rest_route')->once();
        $this->controller->register_routes();
    }

    public function test_check_contrast() {
        $request = $this->createMock('WP_REST_Request');
        $request->expects($this->once())
                ->method('get_param')
                ->willReturn(['#FFFFFF', '#000000']);

        $response = $this->controller->check_contrast($request);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('contrast_ratio', $response);
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
