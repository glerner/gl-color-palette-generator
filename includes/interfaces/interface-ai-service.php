<?php
/**
 * AI Service Interface
 *
 * Defines the contract for AI-powered color services.
 *
 * @package GLColorPalette
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GLColorPalette\Interfaces;

/**
 * Interface AIService
 *
 * Provides methods for AI-powered color analysis and suggestions.
 */
interface AIService {
    /**
     * Initialize the AI service
     *
     * @return bool True if initialization was successful
     * @throws \Exception If service initialization fails
     */
    public function initialize_service(): bool;

    /**
     * Get color suggestions based on specified criteria
     *
     * @param array $criteria {
     *     Criteria for color suggestions
     *     @type string $context     Usage context (e.g., 'web', 'print', 'branding')
     *     @type string $mood        Desired mood or emotion
     *     @type array  $constraints Color constraints or requirements
     *     @type int    $count       Number of suggestions to return
     * }
     * @return array {
     *     Color suggestions
     *     @type array  $colors      List of suggested colors
     *     @type array  $rationale   Explanation for each suggestion
     *     @type float  $confidence  Confidence score for suggestions (0-1)
     * }
     * @throws \InvalidArgumentException If criteria is invalid
     */
    public function get_color_suggestions(array $criteria): array;

    /**
     * Analyze a combination of colors
     *
     * @param array $colors List of colors to analyze
     * @return array {
     *     Analysis results
     *     @type float  $harmony_score    Color harmony score (0-1)
     *     @type array  $relationships    Color relationships analysis
     *     @type array  $improvements     Suggested improvements
     *     @type array  $accessibility    Accessibility considerations
     * }
     * @throws \InvalidArgumentException If colors array is invalid
     */
    public function analyze_color_combination(array $colors): array;

    /**
     * Get current service status
     *
     * @return array {
     *     Service status information
     *     @type bool   $available    Whether service is available
     *     @type string $status       Current status ('ready', 'busy', 'error')
     *     @type array  $limits       Rate limiting information
     *     @type array  $capabilities Available service capabilities
     * }
     */
    public function get_service_status(): array;
} 
