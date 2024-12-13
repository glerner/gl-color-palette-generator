<?php
/**
 * Tests for Error_Handler class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Core;

use GL_Color_Palette_Generator\Core\Error_Handler;
use GL_Color_Palette_Generator\Core\Logger;
use WP_UnitTestCase;

/**
 * Class Test_Error_Handler
 *
 * @coversDefaultClass \GL_Color_Palette_Generator\Core\Error_Handler
 */
class Test_Error_Handler extends WP_UnitTestCase {
    /**
     * Error handler instance
     *
     * @var Error_Handler
     */
    private $error_handler;

    /**
     * Mock logger
     *
     * @var Logger|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();
        $this->logger = $this->createMock(Logger::class);
        $this->error_handler = new Error_Handler($this->logger);
    }

    /**
     * Test error handling
     *
     * @covers ::handle_error
     * @covers ::get_error_type
     */
    public function test_handle_error() {
        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->equalTo('Error occurred'),
                $this->callback(function($error) {
                    return $error['type'] === 'E_USER_WARNING' &&
                           $error['message'] === 'Test error' &&
                           isset($error['file']) &&
                           isset($error['line']);
                })
            );

        $result = $this->error_handler->handle_error(
            E_USER_WARNING,
            'Test error',
            __FILE__,
            __LINE__
        );

        $this->assertTrue($result);
    }

    /**
     * Test exception handling
     *
     * @covers ::handle_exception
     */
    public function test_handle_exception() {
        $exception = new \Exception('Test exception');

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->equalTo('Error occurred'),
                $this->callback(function($error) {
                    return $error['type'] === 'Exception' &&
                           $error['message'] === 'Test exception' &&
                           isset($error['file']) &&
                           isset($error['line']) &&
                           isset($error['trace']);
                })
            );

        try {
            $this->error_handler->handle_exception($exception);
        } catch (\Exception $e) {
            $this->assertEquals('Test exception', $e->getMessage());
        }
    }

    /**
     * Test error details retrieval
     *
     * @covers ::get_error_details
     */
    public function test_get_error_details() {
        $details = $this->error_handler->get_error_details('INVALID_COLOR');
        
        $this->assertIsArray($details);
        $this->assertEquals(1001, $details['code']);
        $this->assertEquals('Invalid color format provided', $details['message']);

        $this->assertNull($this->error_handler->get_error_details('NON_EXISTENT_CODE'));
    }

    /**
     * Test last error management
     *
     * @covers ::get_last_error
     * @covers ::clear_last_error
     */
    public function test_last_error_management() {
        // Trigger an error
        $this->error_handler->handle_error(
            E_USER_WARNING,
            'Test error',
            __FILE__,
            __LINE__
        );

        $last_error = $this->error_handler->get_last_error();
        
        $this->assertIsArray($last_error);
        $this->assertEquals('E_USER_WARNING', $last_error['type']);
        $this->assertEquals('Test error', $last_error['message']);

        $this->error_handler->clear_last_error();
        $this->assertNull($this->error_handler->get_last_error());
    }

    /**
     * Test throwing plugin-specific errors
     *
     * @covers ::throw_error
     */
    public function test_throw_error() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid color format provided: #XYZ');
        $this->expectExceptionCode(1001);

        $this->error_handler->throw_error('INVALID_COLOR', '#XYZ');
    }

    /**
     * Test throwing error with unknown code
     *
     * @covers ::throw_error
     */
    public function test_throw_error_unknown_code() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown error code: UNKNOWN_CODE');

        $this->error_handler->throw_error('UNKNOWN_CODE');
    }

    /**
     * Test error type mapping
     *
     * @covers ::get_error_type
     * @dataProvider provide_error_types
     */
    public function test_error_type_mapping($error_constant, $expected_string) {
        $result = $this->error_handler->handle_error(
            $error_constant,
            'Test error',
            __FILE__,
            __LINE__
        );

        $last_error = $this->error_handler->get_last_error();
        $this->assertEquals($expected_string, $last_error['type']);
    }

    /**
     * Data provider for error types
     */
    public function provide_error_types() {
        return [
            [E_ERROR, 'E_ERROR'],
            [E_WARNING, 'E_WARNING'],
            [E_NOTICE, 'E_NOTICE'],
            [E_USER_ERROR, 'E_USER_ERROR'],
            [E_USER_WARNING, 'E_USER_WARNING'],
            [E_USER_NOTICE, 'E_USER_NOTICE'],
            [E_STRICT, 'E_STRICT'],
            [E_DEPRECATED, 'E_DEPRECATED'],
            [99999, 'UNKNOWN'], // Test unknown error type
        ];
    }
}
