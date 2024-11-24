<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorPaletteNotifier;

class ColorPaletteNotifierTest extends TestCase {
    private $notifier;

    protected function setUp(): void {
        $this->notifier = $this->createMock(ColorPaletteNotifier::class);
    }

    public function test_notify_sends_notification(): void {
        / Arrange
        $event_type = 'palette.updated';
        $data = [
            'palette_id' => 'pal_123',
            'changes' => ['colors' => ['#FF0000']]
        ];

        $options = [
            'recipients' => ['user@example.com'],
            'priority' => 'high',
            'channel' => 'email'
        ];

        $expected = [
            'notification_id' => 'not_abc123',
            'sent' => true,
            'delivery' => [
                'channel' => 'email',
                'timestamp' => '2024-01-20T12:00:00Z',
                'status' => 'delivered'
            ],
            'tracking' => [
                'delivered_to' => 1,
                'opened' => 0,
                'clicked' => 0
            ]
        ];

        $this->notifier
            ->expects($this->once())
            ->method('notify')
            ->with($event_type, $data, $options)
            ->willReturn($expected);

        / Act
        $result = $this->notifier->notify($event_type, $data, $options);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('notification_id', $result);
        $this->assertArrayHasKey('sent', $result);
        $this->assertArrayHasKey('delivery', $result);
        $this->assertArrayHasKey('tracking', $result);
        $this->assertTrue($result['sent']);
    }

    public function test_subscribe_creates_subscription(): void {
        / Arrange
        $subscription = [
            'subscriber_id' => 'usr_456',
            'events' => ['palette.updated', 'palette.deleted'],
            'filters' => ['palette_id' => 'pal_123'],
            'preferences' => [
                'channels' => ['email', 'slack'],
                'frequency' => 'immediate'
            ]
        ];

        $expected = [
            'subscription_id' => 'sub_abc123',
            'active' => true,
            'details' => [
                'subscriber_id' => 'usr_456',
                'events' => ['palette.updated', 'palette.deleted'],
                'created_at' => '2024-01-20T12:00:00Z'
            ],
            'confirmation' => [
                'sent_to' => 'user@example.com',
                'status' => 'confirmed'
            ]
        ];

        $this->notifier
            ->expects($this->once())
            ->method('subscribe')
            ->with($subscription)
            ->willReturn($expected);

        / Act
        $result = $this->notifier->subscribe($subscription);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('subscription_id', $result);
        $this->assertArrayHasKey('active', $result);
        $this->assertArrayHasKey('details', $result);
        $this->assertArrayHasKey('confirmation', $result);
        $this->assertTrue($result['active']);
    }

    public function test_manage_template_handles_template_operations(): void {
        / Arrange
        $action = 'create';
        $template = [
            'name' => 'palette_update',
            'content' => 'Palette {{palette_id}} has been updated',
            'format' => 'text',
            'variables' => ['palette_id', 'changes'],
            'metadata' => ['version' => '1.0']
        ];

        $expected = [
            'template_id' => 'tpl_abc123',
            'success' => true,
            'template' => [
                'name' => 'palette_update',
                'content' => 'Palette {{palette_id}} has been updated',
                'created_at' => '2024-01-20T12:00:00Z'
            ],
            'validation' => [
                'valid' => true,
                'variables_found' => ['palette_id', 'changes']
            ]
        ];

        $this->notifier
            ->expects($this->once())
            ->method('manage_template')
            ->with($action, $template)
            ->willReturn($expected);

        / Act
        $result = $this->notifier->manage_template($action, $template);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('template_id', $result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('template', $result);
        $this->assertArrayHasKey('validation', $result);
        $this->assertTrue($result['success']);
    }

    public function test_get_history_retrieves_notification_history(): void {
        / Arrange
        $criteria = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'event_types' => ['palette.updated'],
            'limit' => 10
        ];

        $expected = [
            'notifications' => [
                [
                    'notification_id' => 'not_abc123',
                    'event_type' => 'palette.updated',
                    'sent_at' => '2024-01-20T12:00:00Z',
                    'status' => 'delivered'
                ]
            ],
            'total' => 1,
            'statistics' => [
                'delivery_rate' => 1.0,
                'open_rate' => 0.75
            ],
            'metadata' => [
                'query_time' => 0.05,
                'filters_applied' => ['date', 'event_type']
            ]
        ];

        $this->notifier
            ->expects($this->once())
            ->method('get_history')
            ->with($criteria)
            ->willReturn($expected);

        / Act
        $result = $this->notifier->get_history($criteria);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('notifications', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('statistics', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * @dataProvider invalidEventTypeProvider
     */
    public function test_notify_validates_event_type(string $event_type): void {
        $data = ['test' => 'data'];

        $this->notifier
            ->expects($this->once())
            ->method('notify')
            ->with($event_type, $data)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->notifier->notify($event_type, $data);
    }

    public function invalidEventTypeProvider(): array {
        return [
            'empty_event' => [''],
            'invalid_format' => ['invalid-event'],
            'unknown_event' => ['palette.unknown'],
            'numeric_event' => ['123']
        ];
    }

    /**
     * @dataProvider invalidTemplateActionProvider
     */
    public function test_manage_template_validates_action(string $action): void {
        $template = ['name' => 'test'];

        $this->notifier
            ->expects($this->once())
            ->method('manage_template')
            ->with($action, $template)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->notifier->manage_template($action, $template);
    }

    public function invalidTemplateActionProvider(): array {
        return [
            'empty_action' => [''],
            'invalid_action' => ['invalid'],
            'unknown_action' => ['modify'],
            'numeric_action' => ['123']
        ];
    }
} 
