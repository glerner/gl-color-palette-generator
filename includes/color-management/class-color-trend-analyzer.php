<?php
/**
 * Color Trend Analyzer Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Class Color_Trend_Analyzer
 * Analyzes color trends, popularity, and combinations
 */
class Color_Trend_Analyzer {
    /**
     * Get historical usage data
     *
     * @param string $color Hex color code.
     * @return array Historical usage data.
     */
    public function get_historical_usage($color) {
        return [
            'popularity_trends' => $this->analyze_popularity_trends($color),
            'industry_adoption' => $this->analyze_industry_adoption($color),
            'historical_context' => $this->get_historical_context($color),
            'usage_patterns' => $this->analyze_usage_patterns($color)
        ];
    }

    /**
     * Get seasonal relevance
     *
     * @param string $color Hex color code.
     * @return array Seasonal relevance data.
     */
    public function get_seasonal_relevance($color) {
        $seasons = ['spring', 'summer', 'autumn', 'winter'];
        $relevance = [];

        foreach ($seasons as $season) {
            $relevance[$season] = [
                'compatibility' => $this->calculate_seasonal_compatibility($color, $season),
                'usage_recommendations' => $this->get_seasonal_recommendations($color, $season),
                'complementary_palettes' => $this->get_seasonal_palettes($season),
                'lighting_considerations' => $this->get_seasonal_lighting_info($season)
            ];
        }

        return $relevance;
    }

    /**
     * Analyze trend alignment
     *
     * @param string $color Hex color code.
     * @return array Trend alignment data.
     */
    public function analyze_trend_alignment($color) {
        return [
            'current_trends' => $this->get_current_trends(),
            'alignment_score' => $this->calculate_trend_alignment($color),
            'trend_categories' => $this->categorize_trends($color),
            'trend_momentum' => $this->analyze_trend_momentum($color)
        ];
    }

    /**
     * Predict trend trajectory
     *
     * @param string $color Hex color code.
     * @return array Trend prediction data.
     */
    public function predict_trend_trajectory($color) {
        return [
            'short_term_forecast' => $this->forecast_short_term($color),
            'long_term_forecast' => $this->forecast_long_term($color),
            'industry_specific_predictions' => $this->predict_industry_trends($color),
            'confidence_score' => $this->calculate_prediction_confidence($color)
        ];
    }

    /**
     * Generate monochromatic scheme
     *
     * @param string $color Hex color code.
     * @return array Monochromatic color scheme.
     */
    public function generate_monochromatic_scheme($color) {
        $hsl = $this->color_utility->hex_to_hsl($color);
        $variations = [];

        // Generate 5 lightness variations
        for ($i = 1; $i <= 5; $i++) {
            $lightness = min(100, max(0, $hsl['l'] + ($i - 3) * 20));
            $variations[] = $this->color_utility->hsl_to_hex([
                'h' => $hsl['h'],
                's' => $hsl['s'],
                'l' => $lightness
            ]);
        }

        // Generate 3 saturation variations
        for ($i = 1; $i <= 3; $i++) {
            $saturation = min(100, max(0, $hsl['s'] + ($i - 2) * 30));
            $variations[] = $this->color_utility->hsl_to_hex([
                'h' => $hsl['h'],
                's' => $saturation,
                'l' => $hsl['l']
            ]);
        }

        return $variations;
    }

    /**
     * Suggest gradients
     *
     * @param string $color Hex color code.
     * @return array Gradient suggestions.
     */
    public function suggest_gradients($color) {
        return [
            'linear_gradients' => $this->generate_linear_gradients($color),
            'radial_gradients' => $this->generate_radial_gradients($color),
            'gradient_stops' => $this->suggest_gradient_stops($color),
            'gradient_directions' => $this->suggest_gradient_directions($color)
        ];
    }

    /**
     * Analyze pattern compatibility
     *
     * @param string $color Hex color code.
     * @return array Pattern compatibility analysis
     */
    public function analyze_pattern_compatibility($color) {
        return [
            'geometric_patterns' => $this->analyze_geometric_compatibility($color),
            'organic_patterns' => $this->analyze_organic_compatibility($color),
            'texture_patterns' => $this->analyze_texture_compatibility($color),
            'pattern_contrast' => $this->analyze_pattern_contrast($color)
        ];
    }
} 
