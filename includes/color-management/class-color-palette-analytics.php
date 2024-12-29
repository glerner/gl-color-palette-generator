<?php
/**
 * Color Palette Analytics Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Interfaces\ColorPaletteAnalytics;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;

/**
 * Class Color_Palette_Analytics
 * Analyzes color palettes for trends, usage patterns, and statistics
 */
class Color_Palette_Analytics implements ColorPaletteAnalytics {
    /**
     * Database table name
     */
    private const TABLE_NAME = 'gl_color_palette_analytics';

    /**
     * Color Utility instance
     */
    private Color_Utility $color_utility;

    /**
     * Constructor
     */
    public function __construct() {
        $this->color_utility = new Color_Utility();
    }

    /**
     * Track palette generation
     *
     * @param array  $palette Generated palette.
     * @param string $source Source of generation (ai, random, imported).
     * @param array  $params Optional parameters used in generation.
     * @return bool Success status.
     */
    public function track_generation(array $palette, string $source, array $params = []): bool {
        global $wpdb;

        $data = [
            'palette_hash' => $this->generate_palette_hash($palette),
            'colors' => wp_json_encode($palette),
            'source' => $source,
            'parameters' => wp_json_encode($params),
            'created_at' => current_time('mysql'),
            'user_id' => get_current_user_id()
        ];

        return $wpdb->insert(self::TABLE_NAME, $data) !== false;
    }

    /**
     * Track palette usage
     *
     * @param array  $palette Used palette.
     * @param string $context Usage context.
     * @return bool Success status.
     */
    public function track_usage(array $palette, string $context): bool {
        global $wpdb;

        $data = [
            'palette_hash' => $this->generate_palette_hash($palette),
            'context' => $context,
            'used_at' => current_time('mysql'),
            'user_id' => get_current_user_id()
        ];

        return $wpdb->insert(self::TABLE_NAME . '_usage', $data) !== false;
    }

    /**
     * Get generation statistics
     *
     * @param array $filters Optional filters.
     * @return array Statistics.
     */
    public function get_generation_stats(array $filters = []): array {
        global $wpdb;

        $where = [];
        $params = [];

        if (isset($filters['start_date'])) {
            $where[] = 'created_at >= %s';
            $params[] = $filters['start_date'];
        }

        if (isset($filters['end_date'])) {
            $where[] = 'created_at <= %s';
            $params[] = $filters['end_date'];
        }

        if (isset($filters['source'])) {
            $where[] = 'source = %s';
            $params[] = $filters['source'];
        }

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = $wpdb->prepare(
            "SELECT
                COUNT(*) as total_palettes,
                source,
                DATE(created_at) as date
            FROM " . self::TABLE_NAME . "
            $where_clause
            GROUP BY source, DATE(created_at)
            ORDER BY date DESC",
            $params
        );

        return $wpdb->get_results($query, ARRAY_A) ?: [];
    }

    /**
     * Get color usage trends
     *
     * @param int $limit Optional limit of results.
     * @return array Color trends.
     */
    public function get_color_trends(int $limit = 10): array {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT
                JSON_UNQUOTE(JSON_EXTRACT(colors, '$[*]')) as color,
                COUNT(*) as usage_count
            FROM " . self::TABLE_NAME . "
            GROUP BY color
            ORDER BY usage_count DESC
            LIMIT %d",
            $limit
        );

        return $wpdb->get_results($query, ARRAY_A) ?: [];
    }

    /**
     * Analyzes palette usage patterns.
     *
     * {@inheritDoc}
     */
    public function analyze_usage(string $palette_id, array $options = []): array {
        return [
            'usage_stats' => $this->get_generation_stats($options),
            'color_usage' => $this->get_color_trends(),
            'trends' => [],
            'segments' => []
        ];
    }

    /**
     * Generates performance metrics.
     *
     * {@inheritDoc}
     */
    public function generate_metrics(string $palette_id, array $metrics = []): array {
        $palette = $this->get_palette_by_id($palette_id);
        if ($palette === null) {
            return [];
        }

        return [
            'scores' => $this->analyze_palette_characteristics($palette),
            'benchmarks' => [],
            'impact' => [],
            'recommendations' => []
        ];
    }

    /**
     * Tracks color usage events.
     *
     * {@inheritDoc}
     */
    public function track_usage_event(array $event): array {
        if (!isset($event['palette_id']) || !isset($event['color'])) {
            return [
                'event_id' => '',
                'tracked' => false,
                'metadata' => [],
                'analytics' => []
            ];
        }

        $success = $this->track_usage($event['color'], $event['context'] ?? '');
        return [
            'event_id' => uniqid('evt_', true),
            'tracked' => $success,
            'metadata' => $event,
            'analytics' => []
        ];
    }

    /**
     * Generates analytics report.
     *
     * {@inheritDoc}
     */
    public function generate_report(string $palette_id, array $options = []): array {
        return [
            'summary' => [],
            'metrics' => $this->generate_metrics($palette_id),
            'trends' => $this->analyze_usage($palette_id),
            'visualizations' => []
        ];
    }

    /**
     * Get palette by ID
     *
     * @param string $palette_id Palette ID
     * @return array|null Palette colors or null if not found
     */
    private function get_palette_by_id(string $palette_id): ?array {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT colors FROM " . self::TABLE_NAME . " WHERE palette_hash = %s",
            $palette_id
        );

        $result = $wpdb->get_var($query);
        if ($result === null) {
            return null;
        }

        return json_decode($result, true);
    }

    /**
     * Generate palette hash
     *
     * @param array $palette Palette colors
     * @return string Hash
     */
    private function generate_palette_hash(array $palette): string {
        sort($palette); // Ensure consistent ordering
        return md5(implode('', $palette));
    }

    /**
     * Analyze color distribution
     *
     * @param array $palette Palette colors
     * @return array Distribution metrics
     */
    private function analyze_distribution(array $palette): array {
        $distribution = [
            'hue_distribution' => [],
            'saturation_distribution' => [],
            'lightness_distribution' => []
        ];

        foreach ($palette as $color) {
            $hsl = $this->color_utility->hex_to_hsl($color);

            // Hue categories using constant for interval size
            $hue_category = floor($hsl['h'] / Color_Constants::COLOR_WHEEL_CONFIG['hue_category_size'])
                * Color_Constants::COLOR_WHEEL_CONFIG['hue_category_size'];
            $distribution['hue_distribution'][$hue_category] =
                ($distribution['hue_distribution'][$hue_category] ?? 0) + 1;

            // Saturation categories
            $sat_category = floor($hsl['s'] / 10) * 10;
            $distribution['saturation_distribution'][$sat_category] =
                ($distribution['saturation_distribution'][$sat_category] ?? 0) + 1;

            // Lightness categories
            $light_category = floor($hsl['l'] / 10) * 10;
            $distribution['lightness_distribution'][$light_category] =
                ($distribution['lightness_distribution'][$light_category] ?? 0) + 1;
        }

        return $distribution;
    }

    /**
     * Analyze harmony metrics
     *
     * @param array $palette Palette colors
     * @return array Harmony metrics
     */
    private function analyze_harmony_metrics(array $palette): array {
        $metrics = [];
        for ($i = 0; $i < count($palette); $i++) {
            for ($j = $i + 1; $j < count($palette); $j++) {
                $contrast = $this->color_utility->get_contrast_ratio($palette[$i], $palette[$j]);
                $metrics[] = [
                    'color1' => $palette[$i],
                    'color2' => $palette[$j],
                    'contrast' => $contrast
                ];
            }
        }
        return $metrics;
    }

    /**
     * Analyze contrast relationships
     *
     * @param array $palette Palette colors
     * @return array Contrast relationships
     */
    private function analyze_contrast_relationships(array $palette): array {
        $relationships = [];
        foreach ($palette as $color1) {
            foreach ($palette as $color2) {
                if ($color1 !== $color2) {
                    $relationships[] = [
                        'pair' => [$color1, $color2],
                        'contrast' => $this->color_utility->get_contrast_ratio($color1, $color2)
                    ];
                }
            }
        }
        return $relationships;
    }

    /**
     * Analyze temperature
     *
     * @param array $palette Palette colors
     * @return array Temperature analysis
     */
    private function analyze_temperature(array $palette): array {
        $temperatures = [];
        foreach ($palette as $color) {
            $rgb = $this->color_utility->hex_to_rgb($color);
            $temperatures[] = [
                'color' => $color,
                'temperature' => ($rgb['r'] * 2 + $rgb['g'] + $rgb['b']) / 4
            ];
        }
        return $temperatures;
    }

    /**
     * Calculate complexity
     *
     * @param array $palette Palette colors
     * @return float Complexity score
     */
    private function calculate_complexity(array $palette): float {
        if (count($palette) < 2) {
            return 0.0;
        }

        $total_contrast = 0;
        $pairs = 0;
        foreach ($palette as $i => $color1) {
            foreach (array_slice($palette, $i + 1) as $color2) {
                $total_contrast += $this->color_utility->get_contrast_ratio($color1, $color2);
                $pairs++;
            }
        }

        return $pairs > 0 ? $total_contrast / $pairs : 0.0;
    }

    /**
     * Analyze palette characteristics
     *
     * @param array $palette Color palette
     * @return array Analysis results
     */
    public function analyze_palette_characteristics(array $palette): array {
        return [
            'color_distribution' => $this->analyze_distribution($palette),
            'harmony_metrics' => $this->analyze_harmony_metrics($palette),
            'contrast_analysis' => $this->analyze_contrast_relationships($palette),
            'temperature' => $this->analyze_temperature($palette),
            'complexity' => $this->calculate_complexity($palette)
        ];
    }
}
