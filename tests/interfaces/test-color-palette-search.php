<?php

namespace GL_Color_Palette_Generator\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\ColorPaletteSearch;

class ColorPaletteSearchTest extends TestCase {
    private $search;

    public function setUp(): void {
        $this->search = $this->createMock(ColorPaletteSearch::class);
    }

    public function test_search_returns_matching_palettes(): void {
        // Arrange
        $criteria = [
            'colors' => ['#FF0000', '#00FF00'],
            'tags' => ['modern', 'bright'],
            'name' => 'vibrant',
            'limit' => 10
        ];

        $expected = [
            'palettes' => [
                [
                    'id' => 'pal_123',
                    'name' => 'Vibrant Spring',
                    'colors' => ['#FF0000', '#00FF00', '#0000FF']
                ],
                [
                    'id' => 'pal_456',
                    'name' => 'Vibrant Summer',
                    'colors' => ['#FF0000', '#FFFF00', '#00FF00']
                ]
            ],
            'total' => 2,
            'facets' => [
                'tags' => [
                    'modern' => 2,
                    'bright' => 2
                ],
                'color_groups' => [
                    'red' => 2,
                    'green' => 2
                ]
            ],
            'metadata' => [
                'search_time' => 0.05,
                'filters_applied' => ['colors', 'tags', 'name']
            ]
        ];

        $this->search
            ->expects($this->once())
            ->method('search')
            ->with($criteria)
            ->willReturn($expected);

        // Act
        $result = $this->search->search($criteria);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palettes', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('facets', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_find_similar_returns_similar_palettes(): void {
        // Arrange
        $palette_id = 'pal_123';
        $options = [
            'threshold' => 0.8,
            'include_score' => true
        ];

        $expected = [
            'matches' => [
                [
                    'id' => 'pal_456',
                    'similarity' => 0.85,
                    'matching_aspects' => ['colors', 'style']
                ],
                [
                    'id' => 'pal_789',
                    'similarity' => 0.82,
                    'matching_aspects' => ['colors']
                ]
            ],
            'scores' => [
                'color_similarity' => 0.9,
                'style_similarity' => 0.8
            ],
            'analysis' => [
                'comparison_method' => 'color_distance',
                'threshold_applied' => 0.8
            ],
            'metadata' => [
                'comparison_time' => 0.15,
                'algorithms_used' => ['euclidean', 'style_match']
            ]
        ];

        $this->search
            ->expects($this->once())
            ->method('find_similar')
            ->with($palette_id, $options)
            ->willReturn($expected);

        // Act
        $result = $this->search->find_similar($palette_id, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('matches', $result);
        $this->assertArrayHasKey('scores', $result);
        $this->assertArrayHasKey('analysis', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_suggest_returns_palette_suggestions(): void {
        // Arrange
        $preferences = [
            'colors' => ['#FF0000'],
            'style' => ['modern', 'minimal'],
            'context' => 'web-design'
        ];

        $expected = [
            'suggestions' => [
                [
                    'id' => 'pal_123',
                    'confidence' => 0.95,
                    'reasoning' => ['color_match', 'style_match']
                ],
                [
                    'id' => 'pal_456',
                    'confidence' => 0.88,
                    'reasoning' => ['style_match']
                ]
            ],
            'reasoning' => [
                'color_based' => 'Matches primary color preference',
                'style_based' => 'Fits modern minimal aesthetic'
            ],
            'alternatives' => [
                [
                    'id' => 'pal_789',
                    'type' => 'similar_style'
                ]
            ],
            'metadata' => [
                'suggestion_time' => 0.1,
                'factors_considered' => ['color', 'style', 'context']
            ]
        ];

        $this->search
            ->expects($this->once())
            ->method('suggest')
            ->with($preferences)
            ->willReturn($expected);

        // Act
        $result = $this->search->suggest($preferences);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('reasoning', $result);
        $this->assertArrayHasKey('alternatives', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_index_processes_palette_for_search(): void {
        // Arrange
        $palette = [
            'id' => 'pal_123',
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $options = [
            'async' => false,
            'fields' => ['name', 'colors', 'tags']
        ];

        $expected = [
            'index_id' => 'idx_abc123',
            'indexed' => true,
            'status' => [
                'fields_indexed' => ['name', 'colors', 'tags'],
                'timestamp' => '2024-01-20T12:00:00Z'
            ],
            'metadata' => [
                'index_time' => 0.05,
                'index_size' => '2.5KB'
            ]
        ];

        $this->search
            ->expects($this->once())
            ->method('index')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->search->index($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('index_id', $result);
        $this->assertArrayHasKey('indexed', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['indexed']);
    }

    /**
     * @dataProvider invalidSearchCriteriaProvider
     */
    public function test_search_validates_criteria(array $criteria): void {
        $this->search
            ->expects($this->once())
            ->method('search')
            ->with($criteria)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->search->search($criteria);
    }

    public function invalidSearchCriteriaProvider(): array {
        return [
            'empty_criteria' => [[]],
            'invalid_colors' => [['colors' => ['invalid-color']]],
            'invalid_limit' => [['limit' => -1]],
            'invalid_offset' => [['offset' => -1]]
        ];
    }

    /**
     * @dataProvider invalidPreferencesProvider
     */
    public function test_suggest_validates_preferences(array $preferences): void {
        $this->search
            ->expects($this->once())
            ->method('suggest')
            ->with($preferences)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->search->suggest($preferences);
    }

    public function invalidPreferencesProvider(): array {
        return [
            'empty_preferences' => [[]],
            'invalid_colors' => [['colors' => ['not-a-color']]],
            'invalid_style' => [['style' => 123]],
            'invalid_context' => [['context' => []]]
        ];
    }
} 
