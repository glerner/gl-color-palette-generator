<?php
namespace GLColorPalette\Interfaces;

/**
 * AI Color Service Interface
 *
 * Defines the contract for AI-powered color palette generation and analysis.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */
interface AIColorService {
    /**
     * Generates a color palette based on provided criteria.
     *
     * @param array $criteria {
     *     Optional. Array of generation criteria.
     *     @type string $mood     The desired mood ('energetic', 'calm', etc.).
     *     @type string $style    Design style ('modern', 'vintage', etc.).
     *     @type string $base     Base color to build palette around.
     *     @type int    $colors   Number of colors in palette (default 5).
     *     @type array  $exclude  Colors to exclude from generation.
     * }
     * @return array {
     *     Generated palette information.
     *     @type array  $colors     Array of generated colors.
     *     @type array  $metadata   Generation metadata and AI confidence scores.
     *     @type string $model      AI model used for generation.
     *     @type array  $rationale  Explanation of color choices.
     * }
     * @throws \InvalidArgumentException If criteria are invalid.
     * @throws \RuntimeException If AI service is unavailable.
     */
    public function generate_palette(array $criteria): array;
    public function analyze_palette(array $palette): array;
    public function get_color_recommendations(array $context): array;
    public function validate_service_connection(): bool;
} 
