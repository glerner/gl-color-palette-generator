<?php
namespace GLColorPalette\Tests;

use GLColorPalette\Color_Palette_REST_Controller;
use GLColorPalette\Color_Palette;
use WP_REST_Request;
use WP_REST_Server;
use WP_UnitTestCase;

/**
 * @covers \GLColorPalette\Color_Palette_REST_Controller
 */
class Color_Palette_REST_Controller_Test extends WP_UnitTestCase {
    private $server;
    private $namespace = 'gl-color-palette/v1';
    private $controller;

    public function setUp(): void {
        parent::setUp();

        global $wp_rest_server;
        $this->server = $wp_rest_server = new WP_REST_Server;
        $this->controller = new Color_Palette_REST_Controller();
        $this->controller->register_routes();

        do_action('rest_api_init');
    }

    /**
     * @test
     */
    public function it_registers_routes() {
        $routes = $this->server->get_routes();

        $this->assertArrayHasKey("/{$this->namespace}/palettes", $routes);
        $this->assertArrayHasKey("/{$this->namespace}/palettes/(?P<id>[a-zA-Z0-9-]+)/analyze", $routes);
        $this->assertArrayHasKey("/{$this->namespace}/palettes/(?P<id>[a-zA-Z0-9-]+)/optimize", $routes);
        $this->assertArrayHasKey("/{$this->namespace}/palettes/(?P<id>[a-zA-Z0-9-]+)/export", $routes);
    }

    /**
     * @test
     */
    public function it_generates_palette() {
        $request = new WP_REST_Request('POST', "/{$this->namespace}/palettes");
        $request->set_param('theme', 'modern');
        $request->set_param('count', 5);

        $response = $this->server->dispatch($request);
        $data = $response->get_data();

        $this->assertEquals(200, $response->get_status());
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('colors', $data);
        $this->assertCount(5, $data['colors']);
    }

    /**
     * @test
     */
    public function it_analyzes_palette() {
        // Create a test palette first
        $palette = new Color_Palette(['#FF0000', '#00FF00', '#0000FF']);
        $palette_id = $this->store_test_palette($palette);

        $request = new WP_REST_Request('GET', "/{$this->namespace}/palettes/{$palette_id}/analyze");
        $request->set_param('aspects', ['contrast', 'harmony']);

        $response = $this->server->dispatch($request);
        $data = $response->get_data();

        $this->assertEquals(200, $response->get_status());
        $this->assertArrayHasKey('contrast', $data);
        $this->assertArrayHasKey('harmony', $data);
    }

    /**
     * @test
     */
    public function it_optimizes_palette() {
        $palette = new Color_Palette(['#FF0000', '#00FF00', '#0000FF']);
        $palette_id = $this->store_test_palette($palette);

        $request = new WP_REST_Request('POST', "/{$this->namespace}/palettes/{$palette_id}/optimize");
        $request->set_param('target_wcag', 'AA');

        $response = $this->server->dispatch($request);
        $data = $response->get_data();

        $this->assertEquals(200, $response->get_status());
        $this->assertArrayHasKey('colors', $data);
    }

    /**
     * @test
     */
    public function it_exports_palette() {
        $palette = new Color_Palette(['#FF0000', '#00FF00', '#0000FF']);
        $palette_id = $this->store_test_palette($palette);

        $request = new WP_REST_Request('GET', "/{$this->namespace}/palettes/{$palette_id}/export");
        $request->set_param('format', 'css');

        $response = $this->server->dispatch($request);
        $data = $response->get_data();

        $this->assertEquals(200, $response->get_status());
        $this->assertArrayHasKey('format', $data);
        $this->assertArrayHasKey('content', $data);
    }

    /**
     * @test
     * @testdox List endpoint returns paginated results
     */
    public function it_lists_palettes_with_pagination(): void {
        // Create test palettes
        $palettes = [
            new Color_Palette(['#FF0000'], ['name' => 'Red Theme']),
            new Color_Palette(['#00FF00'], ['name' => 'Green Theme']),
            new Color_Palette(['#0000FF'], ['name' => 'Blue Theme'])
        ];

        foreach ($palettes as $palette) {
            $this->storage->store($palette);
        }

        $request = new WP_REST_Request('GET', "/{$this->namespace}/palettes");
        $request->set_param('per_page', 2);
        $request->set_param('page', 1);

        $response = $this->server->dispatch($request);
        $data = $response->get_data();

        $this->assertEquals(200, $response->get_status());
        $this->assertCount(2, $data);
        $this->assertEquals('Blue Theme', $data[0]['metadata']['name']);

        // Test second page
        $request->set_param('page', 2);
        $response = $this->server->dispatch($request);
        $data = $response->get_data();

        $this->assertCount(1, $data);
    }

    /**
     * @test
     * @testdox List endpoint filters by metadata
     */
    public function it_filters_list_by_metadata(): void {
        $palettes = [
            new Color_Palette(['#FF0000'], ['theme' => 'modern']),
            new Color_Palette(['#00FF00'], ['theme' => 'modern']),
            new Color_Palette(['#0000FF'], ['theme' => 'classic'])
        ];

        foreach ($palettes as $palette) {
            $this->storage->store($palette);
        }

        $request = new WP_REST_Request('GET', "/{$this->namespace}/palettes");
        $request->set_param('meta', ['theme' => 'modern']);

        $response = $this->server->dispatch($request);
        $data = $response->get_data();

        $this->assertCount(2, $data);
        $this->assertEquals('modern', $data[0]['metadata']['theme']);
    }

    /**
     * @test
     * @testdox Search endpoint returns matching palettes
     */
    public function it_searches_palettes(): void {
        $palettes = [
            new Color_Palette(['#FF0000'], ['name' => 'Warm Red Theme']),
            new Color_Palette(['#00FF00'], ['name' => 'Cool Green Theme']),
            new Color_Palette(['#0000FF'], ['name' => 'Cool Blue Theme'])
        ];

        foreach ($palettes as $palette) {
            $this->storage->store($palette);
        }

        $request = new WP_REST_Request('GET', "/{$this->namespace}/palettes/search");
        $request->set_param('query', 'Cool');
        $request->set_param('field', 'name');

        $response = $this->server->dispatch($request);
        $data = $response->get_data();

        $this->assertEquals(200, $response->get_status());
        $this->assertCount(2, $data);
    }

    /**
     * @test
     * @testdox Search endpoint validates parameters
     */
    public function it_validates_search_parameters(): void {
        $request = new WP_REST_Request('GET', "/{$this->namespace}/palettes/search");

        // Missing required query parameter
        $response = $this->server->dispatch($request);
        $this->assertEquals(400, $response->get_status());

        // Invalid field parameter
        $request->set_param('query', 'test');
        $request->set_param('field', 'invalid');
        $response = $this->server->dispatch($request);
        $this->assertEquals(400, $response->get_status());
    }

    /**
     * @test
     * @testdox Response includes correct pagination headers
     */
    public function it_includes_pagination_headers(): void {
        // Create 25 test palettes
        for ($i = 0; $i < 25; $i++) {
            $palette = new Color_Palette(
                ['#FF0000'],
                ['name' => "Palette {$i}"]
            );
            $this->storage->store($palette);
        }

        $request = new WP_REST_Request('GET', "/{$this->namespace}/palettes");
        $request->set_param('per_page', 10);
        $request->set_param('page', 2);

        $response = $this->server->dispatch($request);

        // Test response headers
        $this->assertEquals(25, $response->get_headers()['X-WP-Total']);
        $this->assertEquals(3, $response->get_headers()['X-WP-TotalPages']);

        // Test navigation links
        $links = $response->get_headers()['Link'];
        $this->assertStringContainsString('rel="prev"', $links);
        $this->assertStringContainsString('rel="next"', $links);

        // Test last page
        $request->set_param('page', 3);
        $response = $this->server->dispatch($request);
        $links = $response->get_headers()['Link'];
        $this->assertStringContainsString('rel="prev"', $links);
        $this->assertStringNotContainsString('rel="next"', $links);
    }

    /**
     * @test
     * @testdox Pagination headers work with filtered results
     */
    public function it_paginates_filtered_results(): void {
        // Create test palettes with different themes
        for ($i = 0; $i < 15; $i++) {
            $theme = $i < 10 ? 'modern' : 'classic';
            $palette = new Color_Palette(
                ['#FF0000'],
                ['name' => "Palette {$i}", 'theme' => $theme]
            );
            $this->storage->store($palette);
        }

        $request = new WP_REST_Request('GET', "/{$this->namespace}/palettes");
        $request->set_param('per_page', 5);
        $request->set_param('page', 1);
        $request->set_param('meta', ['theme' => 'modern']);

        $response = $this->server->dispatch($request);

        // Test filtered totals
        $this->assertEquals(10, $response->get_headers()['X-WP-Total']);
        $this->assertEquals(2, $response->get_headers()['X-WP-TotalPages']);
    }

    /**
     * Helper method to store test palette
     */
    private function store_test_palette(Color_Palette $palette): string {
        // Implementation depends on storage mechanism
        return 'test-palette-id';
    }
}
