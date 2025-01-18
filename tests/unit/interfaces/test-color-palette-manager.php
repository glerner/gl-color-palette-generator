<?php

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Manager;

class Test_Color_Palette_Manager extends Unit_Test_Case {
    protected $manager;

    public function setUp(): void {
        $this->manager = $this->createMock(Color_Palette_Manager::class);
    }

    public function test_create_palette_stores_new_palette(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $options = [
            'validation' => ['required' => ['name', 'colors']],
            'defaults' => ['created_at' => '2024-01-20']
        ];

        $expected = [
            'palette' => [
                'id' => 'pal_123',
                'name' => 'Test Palette',
                'colors' => ['#FF0000', '#00FF00'],
                'created_at' => '2024-01-20'
            ],
            'validation' => [
                'valid' => true,
                'errors' => []
            ],
            'metadata' => [
                'created_at' => '2024-01-20T12:00:00Z',
                'version' => '1.0'
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('create_palette')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->manager->create_palette($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palette', $result);
        $this->assertArrayHasKey('validation', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_update_palette_modifies_existing_palette(): void {
        // Arrange
        $id = 'pal_123';
        $updates = [
            'name' => 'Updated Palette',
            'colors' => ['#FF0000', '#0000FF']
        ];

        $options = [
            'validation' => true,
            'merge' => 'deep'
        ];

        $expected = [
            'palette' => [
                'id' => 'pal_123',
                'name' => 'Updated Palette',
                'colors' => ['#FF0000', '#0000FF'],
                'updated_at' => '2024-01-20'
            ],
            'changes' => [
                'name' => ['from' => 'Test Palette', 'to' => 'Updated Palette'],
                'colors' => ['changed' => [1]]
            ],
            'metadata' => [
                'updated_at' => '2024-01-20T12:00:00Z',
                'version' => '1.1'
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('update_palette')
            ->with($id, $updates, $options)
            ->willReturn($expected);

        // Act
        $result = $this->manager->update_palette($id, $updates, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palette', $result);
        $this->assertArrayHasKey('changes', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_delete_palette_removes_palette(): void {
        // Arrange
        $id = 'pal_123';
        $options = [
            'force' => true,
            'backup' => true
        ];

        $expected = [
            'success' => true,
            'backup' => [
                'id' => 'bak_123',
                'palette' => ['id' => 'pal_123', 'name' => 'Test Palette']
            ],
            'metadata' => [
                'deleted_at' => '2024-01-20T12:00:00Z',
                'backup_created' => true
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('delete_palette')
            ->with($id, $options)
            ->willReturn($expected);

        // Act
        $result = $this->manager->delete_palette($id, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('backup', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['success']);
    }

    public function test_list_palettes_returns_collection(): void {
        // Arrange
        $filters = ['tag' => 'featured'];
        $options = [
            'pagination' => ['page' => 1, 'per_page' => 10],
            'sorting' => ['field' => 'created_at', 'order' => 'desc']
        ];

        $expected = [
            'palettes' => [
                ['id' => 'pal_123', 'name' => 'Test Palette 1'],
                ['id' => 'pal_124', 'name' => 'Test Palette 2']
            ],
            'pagination' => [
                'total' => 2,
                'page' => 1,
                'per_page' => 10
            ],
            'metadata' => [
                'retrieved_at' => '2024-01-20T12:00:00Z',
                'filter_count' => 1
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('list_palettes')
            ->with($filters, $options)
            ->willReturn($expected);

        // Act
        $result = $this->manager->list_palettes($filters, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palettes', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_get_palette_retrieves_palette(): void {
        // Arrange
        $id = 'pal_123';
        $options = [
            'fields' => ['name', 'colors'],
            'relations' => ['variations']
        ];

        $expected = [
            'palette' => [
                'id' => 'pal_123',
                'name' => 'Test Palette',
                'colors' => ['#FF0000', '#00FF00']
            ],
            'relations' => [
                'variations' => [
                    ['id' => 'var_1', 'type' => 'light'],
                    ['id' => 'var_2', 'type' => 'dark']
                ]
            ],
            'metadata' => [
                'retrieved_at' => '2024-01-20T12:00:00Z',
                'version' => '1.0'
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('get_palette')
            ->with($id, $options)
            ->willReturn($expected);

        // Act
        $result = $this->manager->get_palette($id, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palette', $result);
        $this->assertArrayHasKey('relations', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_search_palettes_finds_matches(): void {
        // Arrange
        $criteria = [
            'query' => 'blue',
            'tags' => ['modern', 'cool']
        ];

        $options = [
            'pagination' => ['page' => 1],
            'sorting' => ['relevance' => 'desc']
        ];

        $expected = [
            'palettes' => [
                ['id' => 'pal_123', 'name' => 'Blue Sky', 'relevance' => 0.95],
                ['id' => 'pal_124', 'name' => 'Ocean Blue', 'relevance' => 0.85]
            ],
            'pagination' => [
                'total' => 2,
                'page' => 1
            ],
            'metadata' => [
                'searched_at' => '2024-01-20T12:00:00Z',
                'query_time' => '0.1s'
            ]
        ];

        $this->manager
            ->expects($this->once())
            ->method('search_palettes')
            ->with($criteria, $options)
            ->willReturn($expected);

        // Act
        $result = $this->manager->search_palettes($criteria, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palettes', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * @dataProvider invalidPaletteDataProvider
     */
    public function test_create_palette_validates_input(array $palette): void {
        $this->manager
            ->expects($this->once())
            ->method('create_palette')
            ->with($palette)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->manager->create_palette($palette);
    }

    public function invalidPaletteDataProvider(): array {
        return [
            'empty_palette' => [[]],
            'missing_name' => [['colors' => ['#FF0000']]],
            'missing_colors' => [['name' => 'Test']],
            'invalid_colors' => [['name' => 'Test', 'colors' => 'not-array']]
        ];
    }
} 
