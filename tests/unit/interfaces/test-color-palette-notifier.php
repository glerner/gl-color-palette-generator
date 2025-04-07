<?php
/**
 * Color Palette Notifier Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Notifier;

class Test_Color_Palette_Notifier extends Unit_Test_Case {
	private $notifier;

	public function setUp(): void {
		$this->notifier = $this->createMock( Color_Palette_Notifier::class );
	}

	/**
	 * Test that notify sends a notification
	 */
	public function test_notify_sends_notification(): void {
		// Arrange
		$event_type = 'palette.updated';
		$data       = array(
			'palette_id' => 'pal_123',
			'changes'    => array( 'colors' => array( '#FF0000' ) ),
		);

		$options = array(
			'recipients' => array( 'user@example.com' ),
			'priority'   => 'high',
			'channel'    => 'email',
		);

		$expected = array(
			'notification_id' => 'not_abc123',
			'sent'            => true,
			'delivery'        => array(
				'channel'   => 'email',
				'timestamp' => '2024-12-08T19:04:25-07:00',
				'status'    => 'delivered',
			),
			'tracking'        => array(
				'delivered_to' => 1,
				'opened'       => 0,
				'clicked'      => 0,
			),
		);

		$this->notifier
			->expects( $this->once() )
			->method( 'notify' )
			->with( $event_type, $data, $options )
			->willReturn( $expected );

		// Act
		$result = $this->notifier->notify( $event_type, $data, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'notification_id', $result );
		$this->assertArrayHasKey( 'sent', $result );
		$this->assertArrayHasKey( 'delivery', $result );
		$this->assertArrayHasKey( 'tracking', $result );
		$this->assertTrue( $result['sent'] );
		$this->assertEquals( 'delivered', $result['delivery']['status'] );
	}

	/**
	 * Test that subscribe creates a subscription
	 */
	public function test_subscribe_creates_subscription(): void {
		// Arrange
		$subscription = array(
			'subscriber_id' => 'usr_456',
			'events'        => array( 'palette.updated', 'palette.deleted' ),
			'filters'       => array( 'palette_id' => 'pal_123' ),
			'preferences'   => array(
				'channels'  => array( 'email', 'slack' ),
				'frequency' => 'immediate',
			),
		);

		$expected = array(
			'subscription_id' => 'sub_abc123',
			'active'          => true,
			'details'         => array(
				'subscriber_id' => 'usr_456',
				'events'        => array( 'palette.updated', 'palette.deleted' ),
				'created_at'    => '2024-12-08T19:04:25-07:00',
			),
			'confirmation'    => array(
				'sent_to' => 'user@example.com',
				'status'  => 'confirmed',
			),
		);

		$this->notifier
			->expects( $this->once() )
			->method( 'subscribe' )
			->with( $subscription )
			->willReturn( $expected );

		// Act
		$result = $this->notifier->subscribe( $subscription );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'subscription_id', $result );
		$this->assertArrayHasKey( 'active', $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'confirmation', $result );
		$this->assertTrue( $result['active'] );
		$this->assertEquals( 'confirmed', $result['confirmation']['status'] );
	}

	/**
	 * Test that manage_template handles template operations
	 */
	public function test_manage_template_handles_template_operations(): void {
		// Arrange
		$action   = 'create';
		$template = array(
			'name'      => 'palette_update',
			'content'   => 'Palette {{palette_id}} has been updated',
			'format'    => 'text',
			'variables' => array( 'palette_id', 'changes' ),
			'metadata'  => array( 'version' => '1.0' ),
		);

		$expected = array(
			'template_id' => 'tpl_abc123',
			'success'     => true,
			'template'    => array(
				'name'       => 'palette_update',
				'content'    => 'Palette {{palette_id}} has been updated',
				'created_at' => '2024-12-08T19:04:25-07:00',
			),
			'validation'  => array(
				'valid'           => true,
				'variables_found' => array( 'palette_id', 'changes' ),
			),
		);

		$this->notifier
			->expects( $this->once() )
			->method( 'manage_template' )
			->with( $action, $template )
			->willReturn( $expected );

		// Act
		$result = $this->notifier->manage_template( $action, $template );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'template_id', $result );
		$this->assertArrayHasKey( 'success', $result );
		$this->assertArrayHasKey( 'template', $result );
		$this->assertArrayHasKey( 'validation', $result );
		$this->assertTrue( $result['success'] );
		$this->assertTrue( $result['validation']['valid'] );
	}

	/**
	 * Test that get_history retrieves notification history
	 */
	public function test_get_history_retrieves_notification_history(): void {
		// Arrange
		$criteria = array(
			'start_date'  => '2024-12-01',
			'end_date'    => '2024-12-31',
			'event_types' => array( 'palette.updated' ),
			'limit'       => 10,
		);

		$expected = array(
			'notifications' => array(
				array(
					'notification_id' => 'not_abc123',
					'event_type'      => 'palette.updated',
					'sent_at'         => '2024-12-08T19:04:25-07:00',
					'status'          => 'delivered',
				),
			),
			'total'         => 1,
			'statistics'    => array(
				'delivery_rate' => 1.0,
				'open_rate'     => 0.75,
			),
			'metadata'      => array(
				'query_time'      => 0.05,
				'filters_applied' => array( 'date', 'event_type' ),
			),
		);

		$this->notifier
			->expects( $this->once() )
			->method( 'get_history' )
			->with( $criteria )
			->willReturn( $expected );

		// Act
		$result = $this->notifier->get_history( $criteria );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'notifications', $result );
		$this->assertArrayHasKey( 'total', $result );
		$this->assertArrayHasKey( 'statistics', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertEquals( 1, $result['total'] );
		$this->assertEquals( 1.0, $result['statistics']['delivery_rate'] );
	}

	/**
	 * @dataProvider invalidEventTypeProvider
	 */
	public function test_notify_throws_exception_for_invalid_event_type( $event_type ): void {
		$data = array( 'test' => 'data' );

		$this->notifier
			->expects( $this->once() )
			->method( 'notify' )
			->with( $event_type, $data, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->notifier->notify( $event_type, $data, array() );
	}

	/**
	 * @dataProvider invalidSubscriptionProvider
	 */
	public function test_subscribe_throws_exception_for_invalid_subscription( $subscription ): void {
		$this->notifier
			->expects( $this->once() )
			->method( 'subscribe' )
			->with( $subscription )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->notifier->subscribe( $subscription );
	}

	/**
	 * @dataProvider invalidTemplateActionProvider
	 */
	public function test_manage_template_throws_exception_for_invalid_action( $action ): void {
		$template = array( 'name' => 'test' );

		$this->notifier
			->expects( $this->once() )
			->method( 'manage_template' )
			->with( $action, $template )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->notifier->manage_template( $action, $template );
	}

	public function invalidEventTypeProvider(): array {
		return array(
			'empty event type'   => array( '' ),
			'invalid event type' => array( 'invalid.event' ),
			'numeric event type' => array( '123' ),
			'null event type'    => array( null ),
			'special chars'      => array( 'event@!' ),
		);
	}

	public function invalidSubscriptionProvider(): array {
		return array(
			'empty array'        => array( array() ),
			'missing subscriber' => array( array( 'events' => array( 'test' ) ) ),
			'invalid events'     => array(
				array(
					'subscriber_id' => 'test',
					'events'        => 'not-array',
				),
			),
			'non-array input'    => array( 'invalid' ),
			'null input'         => array( null ),
		);
	}

	public function invalidTemplateActionProvider(): array {
		return array(
			'empty action'   => array( '' ),
			'invalid action' => array( 'invalid' ),
			'numeric action' => array( '123' ),
			'null action'    => array( null ),
			'special chars'  => array( 'action@!' ),
		);
	}
}
