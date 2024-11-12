<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorPaletteHistory;

class ColorPaletteHistoryTest extends TestCase {
    private $history;

    protected function setUp(): void {
        $this->history = $this->createMock(ColorPaletteHistory::class);
    }

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
                'created_at' => '2024-01-20T12:00:00Z'
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
                    'created_at' => '2024-01-20T12:00:00Z'
                ],
                [
                    'id' => 1,
                    'number' => '1.0',
                    'created_at' => '2024-01-19T12:00:00Z'
                ]
            ],
            'timeline' => [
                '2024-01-20' => ['version' => '1.1', 'changes' => 1],
                '2024-01-19' => ['version' => '1.0', 'changes' => 3]
            ],
            'statistics' => [
                'total_versions' => 2,
                'total_changes' => 4
            ],
            'metadata' => [
                'retrieved_at' => '2024-01-20T12:00:00Z',
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
                'restored_at' => '2024-01-20T12:00:00Z',
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
                'compared_at' => '2024-01-20T12:00:00Z',
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
     * @dataProvider invalidPaletteDataProvider
     */
    public function test_record_version_validates_palette(array $palette): void {
        $this->history
            ->expects($this->once())
            ->method('record_version')
            ->with($palette)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->history->record_version($palette);
    }

    public function invalidPaletteDataProvider(): array {
        return [
            'empty_palette' => [[]],
            'missing_name' => [['colors' => ['#FF0000']]],
            'missing_colors' => [['name' => 'Test']],
            'invalid_colors' => [['name' => 'Test', 'colors' => 'not-array']]
        ];
    }

    /**
     * @dataProvider invalidVersionIdProvider
     */
    public function test_restore_version_validates_version_id(int $version_id): void {
        $palette_id = 123;

        $this->history
            ->expects($this->once())
            ->method('restore_version')
            ->with($palette_id, $version_id)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->history->restore_version($palette_id, $version_id);
    }

    public function invalidVersionIdProvider(): array {
        return [
            'negative_id' => [-1],
            'zero_id' => [0]
        ];
    }
} 
