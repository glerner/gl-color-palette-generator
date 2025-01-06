<?php
namespace GL_Color_Palette_Generator\Tests\Unit;

use WP_Mock\Tools\TestCase;

class Unit_Test extends TestCase {
    public function setUp(): void {
        parent::setUp();
        \WP_Mock::setUp();
    }

    public function tearDown(): void {
        \WP_Mock::tearDown();
        parent::tearDown();
    }
}
