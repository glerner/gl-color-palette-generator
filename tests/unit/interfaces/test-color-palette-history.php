<?php
/**
 * Color Palette History Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_History;

class Test_Color_Palette_History extends TestCase {
    private $history;

    public function setUp(): void {
        $this->history = $this->createMock(Color_Palette_History::class);
    }

    /**
     * Test that record_version creates a new version
     */
    public function test_record_version_creates_version(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $options = [
            'type' => 'minor',
            'author' => 'test_user',
            'changes' => ['Added new color']
        ];

        $expected = [
            'version_id' => 2,
            'version_info' => [
                'number' => '1.1',
                'type' => 'minor',
                'created_at' => '2024-12-08T19:04:25-07:00'
            ],
            'diff' => [
                'added' => ['#00FF00'],
                'removed' => [],
                'modified' => []
            ],
            'metadata' => [
                'author' => 'test_user',
                'comment' => 'Added new color'
            ]
        ];

        $this->history
            ->expects($this->once())
            ->method('record_version')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->history->record_version($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('version_id', $result);
        $this->assertArrayHasKey('version_info', $result);
        $this->assertArrayHasKey('diff', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * Test that get_history retrieves version history
     */
    public function test_get_history_retrieves_versions(): void {
        // Arrange
        $palette_id = 123;
        $options = [
            'limit' => 10,
            'sort' => ['created_at' => 'DESC']
        ];

        $expected = [
            'versions' => [
                [
                    'id' => 2,
                    'number' => '1.1',
                    'created_at' => '2024-12-08T19:04:25-07:00'
                ],
                [
                    'id' => 1,
                    'number' => '1.0',
                    'created_at' => '2024-12-08T18:04:25-07:00'
                ]
            ],
            'timeline' => [
                '2024-12-08 19:04:25' => ['version' => '1.1', 'changes' => 1],
                '2024-12-08 18:04:25' => ['version' => '1.0', 'changes' => 3]
            ],
            'statistics' => [
                'total_versions' => 2,
                'total_changes' => 4
            ],
            'metadata' => [
                'retrieved_at' => '2024-12-08T19:04:25-07:00',
                'filter_applied' => false
            ]
        ];

        $this->history
            ->expects($this->once())
            ->method('get_history')
            ->with($palette_id, $options)
            ->willReturn($expected);

        // Act
        $result = $this->history->get_history($palette_id, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('versions', $result);
        $this->assertArrayHasKey('timeline', $result);
        $this->assertArrayHasKey('statistics', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * Test that restore_version reverts to a previous version
     */
    public function test_restore_version_reverts_to_previous(): void {
        // Arrange
        $palette_id = 123;
        $version_id = 1;
        $options = [
            'create_backup' => true,
            'validate' => true
        ];

        $expected = [
            'success' => true,
            'restored' => [
                'version' => '1.0',
                'palette' => ['#FF0000']
            ],
            'backup' => [
                'created' => true,
                'version_id' => 3
            ],
            'metadata' => [
                'restored_at' => '2024-12-08T19:04:25-07:00',
                'restored_by' => 'system'
            ]
        ];

        $this->history
            ->expects($this->once())
            ->method('restore_version')
            ->with($palette_id, $version_id, $options)
            ->willReturn($expected);

        // Act
        $result = $this->history->restore_version($palette_id, $version_id, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('restored', $result);
        $this->assertArrayHasKey('backup', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['success']);
    }

    /**
     * Test that compare_versions shows differences between versions
     */
    public function test_compare_versions_shows_differences(): void {
        // Arrange
        $palette_id = 123;
        $version_1 = 1;
        $version_2 = 2;
        $options = [
            'fields' => ['colors'],
            'format' => 'detailed'
        ];

        $expected = [
            'differences' => [
                'colors' => [
                    'added' => ['#00FF00'],
                    'removed' => [],
                    'modified' => []
                ]
            ],
            'summary' => [
                'total_changes' => 1,
                'change_type' => 'addition'
            ],
            'visualize' => [
                'before' => ['#FF0000'],
                'after' => ['#FF0000', '#00FF00']
            ],
            'metadata' => [
                'compared_at' => '2024-12-08T19:04:25-07:00',
                'comparison_type' => 'detailed'
            ]
        ];

        $this->history
            ->expects($this->once())
            ->method('compare_versions')
            ->with($palette_id, $version_1, $version_2, $options)
            ->willReturn($expected);

        // Act
        $result = $this->history->compare_versions($palette_id, $version_1, $version_2, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('differences', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('visualize', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * @dataProvider invalidPaletteProvider
     */
    public function test_record_version_throws_exception_for_invalid_palette($palette): void {
        $this->history
            ->expects($this->once())
            ->method('record_version')
            ->with($palette, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->history->record_version($palette, []);
    }

    /**
     * @dataProvider invalidVersionProvider
     */
    public function test_restore_version_throws_exception_for_invalid_version($version_id): void {
        $palette_id = 123;
        
        $this->history
            ->expects($this->once())
            ->method('restore_version')
            ->with($palette_id, $version_id, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->history->restore_version($palette_id, $version_id, []);
    }

    public function invalidPaletteProvider(): array {
        return [
            'empty array' => [[]],
            'missing colors' => [['name' => 'Test']],
            'invalid colors' => [['name' => 'Test', 'colors' => ['invalid']]],
            'non-array input' => ['invalid'],
            'null input' => [null]
        ];
    }

    public function invalidVersionProvider(): array {
        return [
            'negative version' => [-1],
            'zero version' => [0],
            'non-numeric version' => ['invalid'],
            'null version' => [null],
            'float version' => [1.5]
        ];
    }
}
