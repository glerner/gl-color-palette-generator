<?php
/**
 * Tests for Rate_Limiter class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Core\Rate_Limiter;
use GL_Color_Palette_Generator\Core\Logger;
use GL_Color_Palette_Generator\Core\Error_Handler;

/**
 * Class Test_Rate_Limiter
 *
 * @coversDefaultClass \GL_Color_Palette_Generator\Core\Rate_Limiter
 */
class Test_Rate_Limiter extends TestCase {
    /**
     * Rate limiter instance
     *
     * @var Rate_Limiter
     */
    protected $rate_limiter;

    /**
     * Mock logger
     *
     * @var Logger|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $logger;

    /**
     * Mock error handler
     *
     * @var Error_Handler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $error_handler;

    /**
     * Test identifier
     *
     * @var string
     */
    protected $test_identifier = 'test_user_123';

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();
        
        $this->logger = $this->createMock(Logger::class);
        $this->error_handler = $this->createMock(Error_Handler::class);
        
        // Create rate limiter with small window and request limit for testing
        $this->rate_limiter = new Rate_Limiter(
            $this->logger,
            $this->error_handler,
            10, // 10 second window
            5   // 5 requests per window
        );
    }

    /**
     * Clean up test environment
     */
    public function tearDown(): void {
        $this->rate_limiter->reset($this->test_identifier);
        parent::tearDown();
    }

    /**
     * Test initial request allowed
     *
     * @covers ::is_allowed
     */
    public function test_initial_request_allowed() {
        $this->assertTrue($this->rate_limiter->is_allowed($this->test_identifier));
    }

    /**
     * Test rate limiting
     *
     * @covers ::is_allowed
     */
    public function test_rate_limiting() {
        // Make 5 requests (should all be allowed)
        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue($this->rate_limiter->is_allowed($this->test_identifier));
        }

        // 6th request should be denied
        $this->assertFalse($this->rate_limiter->is_allowed($this->test_identifier));
    }

    /**
     * Test token replenishment
     *
     * @covers ::is_allowed
     */
    public function test_token_replenishment() {
        // Use up all tokens
        for ($i = 0; $i < 5; $i++) {
            $this->rate_limiter->is_allowed($this->test_identifier);
        }

        // Wait for tokens to replenish (2 seconds should give us 1 token)
        sleep(2);
        $this->assertTrue($this->rate_limiter->is_allowed($this->test_identifier));
    }

    /**
     * Test remaining tokens
     *
     * @covers ::get_remaining_tokens
     */
    public function test_remaining_tokens() {
        // Initial tokens should be maximum
        $this->assertEquals(5, $this->rate_limiter->get_remaining_tokens($this->test_identifier));

        // Use one token
        $this->rate_limiter->is_allowed($this->test_identifier);
        $this->assertEquals(4, $this->rate_limiter->get_remaining_tokens($this->test_identifier));
    }

    /**
     * Test rate limit reset
     *
     * @covers ::reset
     */
    public function test_reset() {
        // Use some tokens
        $this->rate_limiter->is_allowed($this->test_identifier);
        $this->rate_limiter->is_allowed($this->test_identifier);

        // Reset rate limit
        $this->assertTrue($this->rate_limiter->reset($this->test_identifier));

        // Should have full tokens again
        $this->assertEquals(5, $this->rate_limiter->get_remaining_tokens($this->test_identifier));
    }

    /**
     * Test rate limit headers
     *
     * @covers ::get_headers
     */
    public function test_headers() {
        $headers = $this->rate_limiter->get_headers($this->test_identifier);

        $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
        $this->assertArrayHasKey('X-RateLimit-Reset', $headers);

        $this->assertEquals(5, $headers['X-RateLimit-Limit']);
        $this->assertEquals(5, $headers['X-RateLimit-Remaining']);
        $this->assertGreaterThan(time(), $headers['X-RateLimit-Reset']);
    }

    /**
     * Test multiple identifiers
     *
     * @covers ::is_allowed
     */
    public function test_multiple_identifiers() {
        $identifier1 = 'user1';
        $identifier2 = 'user2';

        // Use up all tokens for identifier1
        for ($i = 0; $i < 5; $i++) {
            $this->rate_limiter->is_allowed($identifier1);
        }

        // identifier2 should still have full tokens
        $this->assertTrue($this->rate_limiter->is_allowed($identifier2));
        $this->assertEquals(4, $this->rate_limiter->get_remaining_tokens($identifier2));
    }

    /**
     * Test maximum token cap
     *
     * @covers ::is_allowed
     * @covers ::get_remaining_tokens
     */
    public function test_maximum_token_cap() {
        // Use one token
        $this->rate_limiter->is_allowed($this->test_identifier);

        // Wait longer than needed to replenish all tokens
        sleep(11);

        // Should still be capped at maximum
        $this->assertEquals(5, $this->rate_limiter->get_remaining_tokens($this->test_identifier));
    }
}
