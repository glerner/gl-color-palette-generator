<?php
/**
 * Color Palette Analytics Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Class Color_Palette_Analytics
 * Analyzes color palettes for trends, usage patterns, and statistics
 */
class Color_Palette_Analytics implements \GL_Color_Palette_Generator\Interfaces\Color_Palette_Analytics {
    /**
     * Database table name
     */
    const TABLE_NAME = 'gl_color_palette_analytics';

    /**
     * Color Utility instance
     *
     * @var Color_Utility
     */
    private $color_utility;

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
    public function track_generation($palette, $source, $params = []) {
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
    public function track_usage($palette, $context) {
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
    public function get_generation_stats($filters = []) {
        global $wpdb;

        $where = [];
        $params = [];

        if (!empty($filters['start_date'])) {
            $where[] = 'created_at >= %s';
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = 'created_at <= %s';
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['source'])) {
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

        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Get color usage trends
     *
     * @param int $limit Optional limit of results.
     * @return array Color trends.
     */
    public function get_color_trends($limit = 10) {
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

        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Analyze palette characteristics
     *
     * @param array $palette Color palette.
     * @return array Analysis results.
     */
    public function analyze_palette_characteristics($palette) {
        $analysis = [
            'color_distribution' => $this->analyze_color_distribution($palette),
            'harmony_metrics' => $this->analyze_harmony_metrics($palette),
            'contrast_analysis' => $this->analyze_contrast_relationships($palette),
            'temperature' => $this->analyze_temperature($palette),
            'complexity' => $this->calculate_complexity($palette)
        ];

        return $analysis;
    }

    /**
     * Get popular combinations
     *
     * @param int $limit Optional limit of results.
     * @return array Popular combinations.
     */
    public function get_popular_combinations($limit = 10) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT
                colors,
                COUNT(*) as usage_count
            FROM " . self::TABLE_NAME . "
            GROUP BY colors
            ORDER BY usage_count DESC
            LIMIT %d",
            $limit
        );

        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Generate palette hash
     *
     * @param array $palette Color palette.
     * @return string Hash.
     */
    private function generate_palette_hash($palette) {
        sort($palette); // Ensure consistent ordering
        return md5(implode('', $palette));
    }

    /**
     * Analyze color distribution
     *
     * @param array $palette Color palette.
     * @return array Distribution analysis.
     */
    private function analyze_color_distribution($palette) {
        $distribution = [
            'hue_distribution' => [],
            'saturation_distribution' => [],
            'lightness_distribution' => []
        ];

        foreach ($palette as $color) {
            $hsl = $this->color_utility->hex_to_hsl($color);

            // Hue categories (in 30° intervals)
            $hue_category = floor($hsl['h'] / 30) * 30;
            $distribution['hue_distribution'][$hue_category] =
                ($distribution['hue_distribution'][$hue_category] ?? 0) + 1;

            // Saturation categories (in 20% intervals)
            $sat_category = floor($hsl['s'] / 20) * 20;
            $distribution['saturation_distribution'][$sat_category] =
                ($distribution['saturation_distribution'][$sat_category] ?? 0) + 1;

            // Lightness categories (in 20% intervals)
            $light_category = floor($hsl['l'] / 20) * 20;
            $distribution['lightness_distribution'][$light_category] =
                ($distribution['lightness_distribution'][$light_category] ?? 0) + 1;
        }

        return $distribution;
    }

    /**
     * Analyze harmony metrics
     *
     * @param array $palette Color palette.
     * @return array Harmony metrics.
     */
    private function analyze_harmony_metrics($palette) {
        $hsl_colors = array_map([$this->color_utility, 'hex_to_hsl'], $palette);

        // Calculate hue angles between adjacent colors
        $hue_angles = [];
        for ($i = 0; $i < count($hsl_colors); $i++) {
            $next = ($i + 1) % count($hsl_colors);
            $angle = abs($hsl_colors[$next]['h'] - $hsl_colors[$i]['h']);
            if ($angle > 180) {
                $angle = 360 - $angle;
            }
            $hue_angles[] = $angle;
        }

        return [
            'average_hue_angle' => array_sum($hue_angles) / count($hue_angles),
            'hue_variance' => $this->calculate_variance($hue_angles),
            'saturation_coherence' => $this->calculate_coherence(
                array_column($hsl_colors, 's')
            ),
            'lightness_coherence' => $this->calculate_coherence(
                array_column($hsl_colors, 'l')
            )
        ];
    }

    /**
     * Analyze contrast relationships
     *
     * @param array $palette Color palette.
     * @return array Contrast analysis.
     */
    private function analyze_contrast_relationships($palette) {
        $relationships = [];

        for ($i = 0; $i < count($palette); $i++) {
            for ($j = $i + 1; $j < count($palette); $j++) {
                $contrast = $this->color_utility->calculate_contrast_ratio(
                    $palette[$i],
                    $palette[$j]
                );

                $relationships[] = [
                    'colors' => [$palette[$i], $palette[$j]],
                    'contrast_ratio' => $contrast,
                    'meets_wcag_aa' => $contrast >= 4.5,
                    'meets_wcag_aaa' => $contrast >= 7
                ];
            }
        }

        return $relationships;
    }

    /**
     * Analyze color temperature
     *
     * @param array $palette Color palette.
     * @return array Temperature analysis.
     */
    private function analyze_temperature($palette) {
        $temperatures = array_map(
            [$this->color_utility, 'calculate_temperature'],
            $palette
        );

        return [
            'average_temperature' => array_sum($temperatures) / count($temperatures),
            'temperature_range' => [
                'min' => min($temperatures),
                'max' => max($temperatures)
            ],
            'dominant_temperature' => $this->get_dominant_temperature($temperatures)
        ];
    }

    /**
     * Calculate palette complexity
     *
     * @param array $palette Color palette.
     * @return float Complexity score.
     */
    private function calculate_complexity($palette) {
        $hsl_colors = array_map([$this->color_utility, 'hex_to_hsl'], $palette);

        $hue_variance = $this->calculate_variance(array_column($hsl_colors, 'h'));
        $sat_variance = $this->calculate_variance(array_column($hsl_colors, 's'));
        $light_variance = $this->calculate_variance(array_column($hsl_colors, 'l'));

        return ($hue_variance + $sat_variance + $light_variance) / 3;
    }

    /**
     * Calculate variance of values
     *
     * @param array $values Array of numbers.
     * @return float Variance.
     */
    private function calculate_variance($values) {
        $mean = array_sum($values) / count($values);
        $squares = array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values);

        return array_sum($squares) / count($squares);
    }

    /**
     * Calculate coherence of values
     *
     * @param array $values Array of numbers.
     * @return float Coherence score.
     */
    private function calculate_coherence($values) {
        $range = max($values) - min($values);
        $variance = $this->calculate_variance($values);

        return 1 - ($variance / pow($range, 2));
    }

    /**
     * Get dominant temperature
     *
     * @param array $temperatures Array of temperature values.
     * @return string Temperature category.
     */
    private function get_dominant_temperature($temperatures) {
        $avg_temp = array_sum($temperatures) / count($temperatures);

        if ($avg_temp < 5000) {
            return 'warm';
        } elseif ($avg_temp > 7000) {
            return 'cool';
        } else {
            return 'neutral';
        }
    }
} 
