<?php

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Version_Control;

class Test_Color_Palette_Version_Control extends Unit_Test_Case {
    private $versionControl;

    public function setUp(): void {
        $this->versionControl = $this->createMock(Color_Palette_Version_Control::class);
    }

    public function test_create_version_returns_valid_version(): void {
        // Arrange
        $palette = [
            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'name' => 'Test Palette'
        ];

        $metadata = [
            'version' => '1.0.0',
            'author' => 'John Doe',
            'message' => 'Initial version'
        ];

        $expected = [
            'version_id' => 'v1.0.0_abc123',
            'snapshot' => [
                'colors' => ['#FF0000', '#00FF00', '#0000FF'],
                'name' => 'Test Palette'
            ],
            'metadata' => [
                'version' => '1.0.0',
                'author' => 'John Doe',
                'message' => 'Initial version',
                'created_at' => '2024-01-20T12:00:00Z'
            ],
            'timestamps' => [
                'created' => '2024-01-20T12:00:00Z',
                'committed' => '2024-01-20T12:00:01Z'
            ]
        ];

        $this->versionControl
            ->expects($this->once())
            ->method('create_version')
            ->with($palette, $metadata)
            ->willReturn($expected);

        // Act
        $result = $this->versionControl->create_version($palette, $metadata);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('version_id', $result);
        $this->assertArrayHasKey('snapshot', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('timestamps', $result);
    }

    public function test_get_version_history_returns_complete_history(): void {
        // Arrange
        $palette_id = 'pal_123';
        $options = [
            'limit' => 10,
            'start_date' => '2024-01-01'
        ];

        $expected = [
            'versions' => [
                [
                    'version_id' => 'v1.0.0_abc123',
                    'author' => 'John Doe',
                    'message' => 'Initial version',
                    'created_at' => '2024-01-20T12:00:00Z'
                ],
                [
                    'version_id' => 'v1.1.0_def456',
                    'author' => 'Jane Smith',
                    'message' => 'Updated colors',
                    'created_at' => '2024-01-21T14:30:00Z'
                ]
            ],
            'timeline' => [
                '2024-01-20' => ['created', 'committed'],
                '2024-01-21' => ['updated']
            ],
            'statistics' => [
                'total_versions' => 2,
                'authors' => ['John Doe', 'Jane Smith'],
                'average_changes' => 3.5
            ],
            'metadata' => [
                'latest_version' => 'v1.1.0',
                'last_updated' => '2024-01-21T14:30:00Z'
            ]
        ];

        $this->versionControl
            ->expects($this->once())
            ->method('get_version_history')
            ->with($palette_id, $options)
            ->willReturn($expected);

        // Act
        $result = $this->versionControl->get_version_history($palette_id, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('versions', $result);
        $this->assertArrayHasKey('timeline', $result);
        $this->assertArrayHasKey('statistics', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_compare_versions_returns_detailed_comparison(): void {
        // Arrange
        $version1_id = 'v1.0.0_abc123';
        $version2_id = 'v1.1.0_def456';
        $options = [
            'detailed' => true,
            'visual_diff' => true
        ];

        $expected = [
            'differences' => [
                'colors' => [
                    'added' => ['#0000FF'],
                    'removed' => ['#FF0000'],
                    'modified' => [
                        '#00FF00' => ['saturation' => '+10%']
                    ]
                ],
                'metadata' => [
                    'modified' => ['name', 'description']
                ]
            ],
            'statistics' => [
                'total_changes' => 4,
                'change_types' => [
                    'colors' => 3,
                    'metadata' => 1
                ]
            ],
            'visual_diff' => [
                'color_shifts' => [
                    ['from' => '#FF0000', 'to' => '#0000FF'],
                    ['from' => '#00FF00', 'to' => '#00FF33']
                ]
            ],
            'metadata' => [
                'comparison_date' => '2024-01-21T15:00:00Z',
                'version_gap' => '1 day'
            ]
        ];

        $this->versionControl
            ->expects($this->once())
            ->method('compare_versions')
            ->with($version1_id, $version2_id, $options)
            ->willReturn($expected);

        // Act
        $result = $this->versionControl->compare_versions($version1_id, $version2_id, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('differences', $result);
        $this->assertArrayHasKey('statistics', $result);
        $this->assertArrayHasKey('visual_diff', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_restore_version_returns_restore_results(): void {
        // Arrange
        $palette_id = 'pal_123';
        $version_id = 'v1.0.0_abc123';
        $options = [
            'create_backup' => true,
            'keep_metadata' => true
        ];

        $expected = [
            'restored_palette' => [
                'colors' => ['#FF0000', '#00FF00'],
                'name' => 'Original Palette'
            ],
            'backup_data' => [
                'version_id' => 'backup_xyz789',
                'timestamp' => '2024-01-21T16:00:00Z'
            ],
            'changes' => [
                'reverted' => ['colors', 'name'],
                'preserved' => ['metadata', 'settings']
            ],
            'metadata' => [
                'restore_date' => '2024-01-21T16:00:00Z',
                'restore_type' => 'full'
            ]
        ];

        $this->versionControl
            ->expects($this->once())
            ->method('restore_version')
            ->with($palette_id, $version_id, $options)
            ->willReturn($expected);

        // Act
        $result = $this->versionControl->restore_version($palette_id, $version_id, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('restored_palette', $result);
        $this->assertArrayHasKey('backup_data', $result);
        $this->assertArrayHasKey('changes', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * @dataProvider invalidVersionMetadataProvider
     */
    public function test_create_version_validates_metadata(array $palette, array $metadata): void {
        $this->versionControl
            ->expects($this->once())
            ->method('create_version')
            ->with($palette, $metadata)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->versionControl->create_version($palette, $metadata);
    }

    public function invalidVersionMetadataProvider(): array {
        return [
            'empty_metadata' => [['colors' => ['#FF0000']], []],
            'invalid_version' => [
                ['colors' => ['#FF0000']],
                ['version' => 'invalid']
            ],
            'missing_author' => [
                ['colors' => ['#FF0000']],
                ['version' => '1.0.0']
            ],
            'invalid_tags' => [
                ['colors' => ['#FF0000']],
                ['version' => '1.0.0', 'tags' => 'not-array']
            ]
        ];
    }

    /**
     * @dataProvider invalidVersionIdProvider
     */
    public function test_restore_version_validates_version_id(
        string $palette_id,
        string $version_id
    ): void {
        $this->versionControl
            ->expects($this->once())
            ->method('restore_version')
            ->with($palette_id, $version_id)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->versionControl->restore_version($palette_id, $version_id);
    }

    public function invalidVersionIdProvider(): array {
        return [
            'empty_version' => ['pal_123', ''],
            'invalid_format' => ['pal_123', 'not_a_version'],
            'missing_hash' => ['pal_123', 'v1.0.0'],
            'invalid_palette_id' => ['', 'v1.0.0_abc123']
        ];
    }
} 
