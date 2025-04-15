<?php
/**
 * Unit Tests for Color Palette Validator
 *
 * Tests the non-WordPress dependent functionality of the Color_Palette_Validator class.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Classes
 * @author  George Lerner
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Classes;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test class for Color_Palette_Validator non-WordPress functionality
 *
 * @covers GL_Color_Palette_Generator\Color_Management\Color_Palette_Validator
 */
class Test_Color_Palette_Validator extends Unit_Test_Case {
	/**
	 * Color utility mock
	 *
	 * @var MockObject|Color_Utility
	 */
	private $color_utility;

	/**
	 * Set up the test environment
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Create a mock for Color_Utility to test color format validation
		$this->color_utility = $this->createMock(Color_Utility::class);
	}

	/**
	 * Test validate_color_format with valid hex colors
	 *
	 * @return void
	 */
	public function test_validate_color_format_with_valid_hex(): void {
		// Set up the mock to validate hex colors
		$this->color_utility->method('is_valid_hex_color')
			->willReturnCallback(function($color) {
				return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color) === 1;
			});

		// Create a test validator that uses our mocked color utility
		$validator = $this->getMockBuilder('GL_Color_Palette_Generator\Color_Management\Color_Palette_Validator')
			->disableOriginalConstructor()
			->onlyMethods(['get_last_errors'])
			->getMock();

		// Use reflection to set the color_utility property
		$reflection = new \ReflectionClass($validator);
		$property = $reflection->getProperty('color_utility');
		$property->setAccessible(true);
		$property->setValue($validator, $this->color_utility);

		// Test with valid hex colors
		$this->assertTrue($validator->validate_color_format('#FF0000'));
		$this->assertTrue($validator->validate_color_format('#00FF00'));
		$this->assertTrue($validator->validate_color_format('#0000FF'));
		$this->assertTrue($validator->validate_color_format('#fff'));
		$this->assertTrue($validator->validate_color_format('#000'));
	}

	/**
	 * Test validate_color_format with invalid hex colors
	 *
	 * @return void
	 */
	public function test_validate_color_format_with_invalid_hex(): void {
		// Set up the mock to validate hex colors
		$this->color_utility->method('is_valid_hex_color')
			->willReturnCallback(function($color) {
				return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color) === 1;
			});

		// Create a test validator that uses our mocked color utility
		$validator = $this->getMockBuilder('GL_Color_Palette_Generator\Color_Management\Color_Palette_Validator')
			->disableOriginalConstructor()
			->onlyMethods(['get_last_errors'])
			->getMock();

		// Use reflection to set the color_utility property
		$reflection = new \ReflectionClass($validator);
		$property = $reflection->getProperty('color_utility');
		$property->setAccessible(true);
		$property->setValue($validator, $this->color_utility);

		// Test with invalid hex colors
		$this->assertFalse($validator->validate_color_format('FF0000')); // Missing #
		$this->assertFalse($validator->validate_color_format('#GGGGGG')); // Invalid characters
		$this->assertFalse($validator->validate_color_format('#FF00')); // Wrong length
		$this->assertFalse($validator->validate_color_format('invalid')); // Not a hex color
		$this->assertFalse($validator->validate_color_format('')); // Empty string
	}

	/**
	 * Test get_last_errors returns an array
	 *
	 * @return void
	 */
	public function test_get_last_errors(): void {
		// Create a test validator
		$validator = $this->getMockBuilder('GL_Color_Palette_Generator\Color_Management\Color_Palette_Validator')
			->disableOriginalConstructor()
			->onlyMethods([])
			->getMock();

		// Use reflection to set the last_errors property
		$reflection = new \ReflectionClass($validator);
		$property = $reflection->getProperty('last_errors');
		$property->setAccessible(true);
		$property->setValue($validator, []);

		// Test that get_last_errors returns an array
		$this->assertIsArray($validator->get_last_errors());
		$this->assertEmpty($validator->get_last_errors());

		// Set some errors and test again
		$property->setValue($validator, ['Error 1', 'Error 2']);
		$this->assertCount(2, $validator->get_last_errors());
		$this->assertEquals(['Error 1', 'Error 2'], $validator->get_last_errors());
	}
}
