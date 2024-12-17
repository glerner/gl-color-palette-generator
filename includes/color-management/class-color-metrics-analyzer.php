<?php declare(strict_types=1);

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Metrics_Analyzer_Interface;
use GL_Color_Palette_Generator\Types\Color_Types;
use GL_Color_Palette_Generator\Types\Metric_Types;
use GL_Color_Palette_Generator\Traits\Error_Handler;
use GL_Color_Palette_Generator\Traits\Logger;

/**
 * Color Metrics Analyzer Class
 *
 * Analyzes detailed color metrics and relationships including:
 * - Basic color properties (brightness, saturation, etc.)
 * - Perceptual metrics (perceived brightness, colorfulness)
 * - Psychological aspects (temperature, weight, activity)
 * - Accessibility measurements (contrast ratios, WCAG compliance)
 * - Color space conversions and relationships
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @since 1.0.0
 */
class Color_Metrics_Analyzer implements Color_Metrics_Analyzer_Interface {
    use Error_Handler, Logger;

    /**
     * Color utility instance
     *
     * @var Color_Utility
     */
    protected Color_Utility $utility;

    /**
     * Color calculator instance
     *
     * @var Color_Calculator
     */
    protected Color_Calculator $calculator;

    /**
     * Constructor
     *
     * @param Color_Utility    $utility    Color utility instance
     * @param Color_Calculator $calculator Color calculator instance
     */
    public function __construct(
        Color_Utility $utility,
        Color_Calculator $calculator
    ) {
        $this->utility = $utility;
        $this->calculator = $calculator;
    }

    /**
     * Analyze comprehensive color metrics
     *
     * @param string $color Hex color code
     * @return array Color metrics analysis
     */
    public function analyze_color(string $color): array {
        $this->validate_color($color);

        try {
            return [
                'basic_metrics' => $this->get_basic_metrics($color),
                'perceptual_metrics' => $this->get_perceptual_metrics($color),
                'color_space_values' => $this->get_color_space_values($color),
                'psychological_metrics' => $this->get_psychological_metrics($color),
                'accessibility_metrics' => $this->get_accessibility_metrics($color),
                'semantic_analysis' => $this->get_semantic_analysis($color)
            ];
        } catch (\Exception $e) {
            $this->log_error('Color analysis failed', ['color' => $color, 'error' => $e->getMessage()]);
            throw new \RuntimeException("Failed to analyze color: {$e->getMessage()}");
        }
    }

    /**
     * Get basic color metrics
     *
     * @param string $color Hex color code
     * @return array Basic metrics data
     */
    public function get_basic_metrics(string $color): array {
        $rgb = $this->utility->hex_to_rgb($color);
        $hsl = $this->utility->rgb_to_hsl($rgb);

        return [
            'brightness' => $this->calculate_brightness($rgb),
            'relative_luminance' => $this->calculator->calculate_relative_luminance($rgb),
            'saturation' => $hsl['s'],
            'lightness' => $hsl['l'],
            'intensity' => ($rgb['r'] + $rgb['g'] + $rgb['b']) / (3 * 255),
            'purity' => $this->calculate_color_purity($rgb)
        ];
    }

    /**
     * Get perceptual color metrics
     *
     * @param string $color Hex color code
     * @return array{
     *     perceived_brightness: float,
     *     perceived_colorfulness: float,
     *     perceived_hue: float,
     *     perceived_warmth: float,
     *     visual_weight: float
     * }
     * @throws \RuntimeException If metric calculation fails
     * @since 1.0.0
     */
    private function get_perceptual_metrics(string $color): array {
        try {
            $rgb = $this->utility->hex_to_rgb($color);
            $lab = $this->utility->rgb_to_lab($rgb);

            return [
                'perceived_brightness' => $lab['L'],
                'perceived_colorfulness' => sqrt(pow($lab['a'], 2) + pow($lab['b'], 2)),
                'perceived_hue' => atan2($lab['b'], $lab['a']),
                'perceived_warmth' => $this->calculate_perceived_warmth($color),
                'visual_weight' => $this->calculate_visual_weight($color)
            ];
        } catch (\Exception $e) {
            $this->handle_error($e, "Perceptual metrics calculation failed");
            throw new \RuntimeException("Failed to calculate perceptual metrics: " . $e->getMessage());
        }
    }

    /**
     * Get color space values
     *
     * @param string $color Hex color code
     * @return array{
     *     rgb: array{r: int, g: int, b: int},
     *     hsl: array{h: float, s: float, l: float},
     *     lab: array{l: float, a: float, b: float},
     *     cmyk: array{c: float, m: float, y: float, k: float},
     *     xyz: array{x: float, y: float, z: float}
     * }
     * @throws \RuntimeException If color space conversion fails
     * @since 1.0.0
     */
    private function get_color_space_values(string $color): array {
        try {
            $rgb = $this->utility->hex_to_rgb($color);
            return [
                'rgb' => $rgb,
                'hsl' => $this->utility->rgb_to_hsl($rgb),
                'lab' => $this->utility->rgb_to_lab($rgb),
                'cmyk' => $this->utility->rgb_to_cmyk($rgb),
                'xyz' => $this->utility->rgb_to_xyz($rgb)
            ];
        } catch (\Exception $e) {
            $this->handle_error($e, "Color space conversion failed");
            throw new \RuntimeException("Failed to convert color spaces: " . $e->getMessage());
        }
    }

    /**
     * Get psychological color metrics
     *
     * @param string $color Hex color code
     * @return array{
     *     temperature: float,
     *     weight: float,
     *     activity: float,
     *     emotional_response: array,
     *     cultural_associations: array
     * }
     * @throws \RuntimeException If metric calculation fails
     * @since 1.0.0
     */
    private function get_psychological_metrics(string $color): array {
        try {
            $rgb = $this->utility->hex_to_rgb($color);
            $hsl = $this->utility->rgb_to_hsl($rgb);

            return [
                'temperature' => $this->calculate_psychological_temperature($hsl),
                'weight' => $this->calculate_psychological_weight($hsl),
                'activity' => $this->calculate_psychological_activity($rgb, $hsl),
                'emotional_response' => $this->get_emotional_response($hsl),
                'cultural_associations' => $this->get_cultural_associations($hsl)
            ];
        } catch (\Exception $e) {
            $this->handle_error($e, "Psychological metrics calculation failed");
            throw new \RuntimeException("Failed to calculate psychological metrics: " . $e->getMessage());
        }
    }

    /**
     * Get accessibility metrics
     *
     * @param string $color Hex color code
     * @return array{
     *     contrast_white: float,
     *     contrast_black: float,
     *     legibility_score: float,
     *     wcag_compliance: array,
     *     color_blindness_simulation: array
     * }
     * @throws \RuntimeException If metric calculation fails
     * @since 1.0.0
     */
    private function get_accessibility_metrics(string $color): array {
        try {
            $rgb = $this->utility->hex_to_rgb($color);

            return [
                'wcag_contrast_white' => $this->calculator->calculate_contrast_ratio($rgb, ['r' => 255, 'g' => 255, 'b' => 255]),
                'wcag_contrast_black' => $this->calculator->calculate_contrast_ratio($rgb, ['r' => 0, 'g' => 0, 'b' => 0]),
                'meets_aa_normal' => $this->check_wcag_aa_compliance($rgb, 'normal'),
                'meets_aa_large' => $this->check_wcag_aa_compliance($rgb, 'large'),
                'meets_aaa' => $this->check_wcag_aaa_compliance($rgb)
            ];
        } catch (\Exception $e) {
            $this->handle_error($e, "Accessibility metrics calculation failed");
            throw new \RuntimeException("Failed to calculate accessibility metrics: " . $e->getMessage());
        }
    }

    /**
     * Get semantic analysis of the color
     *
     * @param string $color Hex color code.
     * @return array Semantic analysis.
     */
    private function get_semantic_analysis(string $color) {
        $rgb = $this->utility->hex_to_rgb($color);
        $hsl = $this->utility->rgb_to_hsl($rgb);

        return [
            'color_category' => $this->determine_color_category($hsl),
            'color_properties' => $this->analyze_color_properties($rgb, $hsl),
            'color_harmony' => $this->analyze_color_harmony($hsl)
        ];
    }

    /**
     * Calculate perceived warmth
     *
     * @param string $color Hex color code.
     * @return float Warmth value.
     */
    private function calculate_perceived_warmth($color) {
        $rgb = $this->utility->hex_to_rgb($color);
        return ($rgb['r'] * 2 + $rgb['g'] + $rgb['b'] * 0.5) / (3.5 * 255);
    }

    /**
     * Calculate visual weight
     *
     * @param string $color Hex color code.
     * @return float Visual weight value.
     */
    private function calculate_visual_weight($color) {
        $lab = $this->utility->hex_to_lab($color);
        return (100 - $lab['L']) / 100;
    }

    /**
     * Calculate psychological temperature
     *
     * @param array $hsl HSL values.
     * @return array Temperature metrics.
     */
    private function calculate_psychological_temperature($hsl) {
        $temp_factors = [
            'warmth' => $this->calculate_warmth_factor($hsl),
            'coolness' => $this->calculate_coolness_factor($hsl),
            'neutrality' => $this->calculate_neutrality_factor($hsl)
        ];

        $dominant = array_keys($temp_factors, max($temp_factors))[0];

        return [
            'factors' => $temp_factors,
            'dominant' => $dominant,
            'intensity' => max($temp_factors)
        ];
    }

    /**
     * Calculate warmth factor
     *
     * @param array $hsl HSL values.
     * @return float Warmth factor.
     */
    private function calculate_warmth_factor($hsl) {
        // Warm colors: red (0°) through yellow (60°)
        $hue = $hsl['h'];
        if ($hue <= 60 || $hue >= 300) {
            return 1 - (min(abs($hue), abs(360 - $hue)) / 60);
        }
        return 0;
    }

    /**
     * Calculate coolness factor
     *
     * @param array $hsl HSL values.
     * @return float Coolness factor.
     */
    private function calculate_coolness_factor($hsl) {
        // Cool colors: green (120°) through blue (240°)
        $hue = $hsl['h'];
        if ($hue >= 120 && $hue <= 240) {
            return 1 - (abs($hue - 180) / 60);
        }
        return 0;
    }

    /**
     * Calculate neutrality factor
     *
     * @param array $hsl HSL values.
     * @return float Neutrality factor.
     */
    private function calculate_neutrality_factor($hsl) {
        return 1 - ($hsl['s'] / 100);
    }

    /**
     * Calculate legibility score
     *
     * @param string $color Hex color code.
     * @return float Legibility score.
     */
    private function calculate_legibility_score(string $color): float {
        try {
            $white_contrast = $this->accessibility_checker->calculate_contrast_ratio($color, '#FFFFFF');
            $black_contrast = $this->accessibility_checker->calculate_contrast_ratio($color, '#000000');

            return max($white_contrast, $black_contrast);
        } catch (\Exception $e) {
            $this->handle_error($e, "Legibility score calculation failed");
            throw new \RuntimeException("Failed to calculate legibility score: " . $e->getMessage());
        }
    }

    /**
     * Check WCAG compliance levels
     *
     * @param string $color Hex color code
     * @return array WCAG compliance information for different levels
     * @since 1.0.0
     */
    private function check_wcag_compliance(string $color): array {
        try {
            $white_contrast = $this->accessibility_checker->calculate_contrast_ratio($color, '#FFFFFF');
            $black_contrast = $this->accessibility_checker->calculate_contrast_ratio($color, '#000000');

            return [
                'aa_large_text' => $white_contrast >= 3.0 || $black_contrast >= 3.0,
                'aa_normal_text' => $white_contrast >= 4.5 || $black_contrast >= 4.5,
                'aaa_large_text' => $white_contrast >= 4.5 || $black_contrast >= 4.5,
                'aaa_normal_text' => $white_contrast >= 7.0 || $black_contrast >= 7.0
            ];
        } catch (\Exception $e) {
            $this->handle_error($e, "WCAG compliance check failed");
            throw new \RuntimeException("Failed to check WCAG compliance: " . $e->getMessage());
        }
    }

    /**
     * Get closest color name
     *
     * @param string $color Hex color code.
     * @return string Color name.
     */
    private function get_closest_color_name($color) {
        $rgb = $this->utility->hex_to_rgb($color);
        $min_distance = PHP_FLOAT_MAX;
        $closest_name = '';

        foreach ($this->get_color_name_map() as $name => $values) {
            $distance = sqrt(
                pow($rgb['r'] - $values['r'], 2) +
                pow($rgb['g'] - $values['g'], 2) +
                pow($rgb['b'] - $values['b'], 2)
            );

            if ($distance < $min_distance) {
                $min_distance = $distance;
                $closest_name = $name;
            }
        }

        return $closest_name;
    }

    /**
     * Get color category
     *
     * @param array $hsl HSL values.
     * @return string Color category.
     */
    private function get_color_category($hsl) {
        if ($hsl['s'] < 10) {
            return $hsl['l'] < 20 ? 'black' : ($hsl['l'] > 80 ? 'white' : 'gray');
        }

        $hue = $hsl['h'];
        if ($hue < 30) return 'red';
        if ($hue < 60) return 'orange';
        if ($hue < 90) return 'yellow';
        if ($hue < 150) return 'green';
        if ($hue < 210) return 'cyan';
        if ($hue < 270) return 'blue';
        if ($hue < 330) return 'purple';
        return 'red';
    }

    /**
     * Get semantic associations
     *
     * @param array $hsl HSL values.
     * @return array Semantic associations.
     */
    private function get_semantic_associations($hsl) {
        $category = $this->get_color_category($hsl);

        $associations = [
            'red' => ['energy', 'passion', 'danger', 'excitement'],
            'orange' => ['creativity', 'adventure', 'confidence', 'sociability'],
            'yellow' => ['happiness', 'optimism', 'intellect', 'attention'],
            'green' => ['nature', 'growth', 'harmony', 'freshness'],
            'cyan' => ['communication', 'clarity', 'calm', 'focus'],
            'blue' => ['trust', 'depth', 'stability', 'professionalism'],
            'purple' => ['royalty', 'luxury', 'mystery', 'spirituality'],
            'black' => ['power', 'elegance', 'mystery', 'sophistication'],
            'white' => ['purity', 'cleanliness', 'simplicity', 'peace'],
            'gray' => ['neutrality', 'balance', 'calmness', 'professionalism']
        ];

        return $associations[$category] ?? [];
    }

    /**
     * Get industry usage
     *
     * @param array $hsl HSL values.
     * @return array Industry associations.
     */
    private function get_industry_usage($hsl) {
        $category = $this->get_color_category($hsl);

        $industries = [
            'red' => ['food', 'sports', 'entertainment', 'retail'],
            'orange' => ['food', 'retail', 'technology', 'creative'],
            'yellow' => ['food', 'retail', 'construction', 'children'],
            'green' => ['environment', 'health', 'education', 'finance'],
            'cyan' => ['healthcare', 'technology', 'science', 'education'],
            'blue' => ['finance', 'technology', 'healthcare', 'corporate'],
            'purple' => ['luxury', 'beauty', 'arts', 'education'],
            'black' => ['luxury', 'fashion', 'technology', 'automotive'],
            'white' => ['healthcare', 'technology', 'retail', 'minimalist'],
            'gray' => ['corporate', 'technology', 'automotive', 'professional']
        ];

        return $industries[$category] ?? [];
    }

    /**
     * Validate color input
     *
     * @param string $color Hex color code.
     * @throws \InvalidArgumentException If color format is invalid.
     * @return bool True if valid.
     */
    private function validate_color($color) {
        if (!is_string($color)) {
            throw new \InvalidArgumentException('Color must be a string');
        }

        if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
            throw new \InvalidArgumentException(
                'Invalid hex color format. Expected #RGB or #RRGGBB'
            );
        }

        return true;
    }

    /**
     * Get color name map
     *
     * @return array Color name mapping
     */
    private function get_color_name_map() {
        return [
            // Basic colors
            'red' => ['#FF0000', '#FF4444', '#FF6666'],
            'green' => ['#00FF00', '#44FF44', '#66FF66'],
            'blue' => ['#0000FF', '#4444FF', '#6666FF'],
            // Add more color mappings as needed
        ];
    }

    /**
     * Get brand personality traits for business type
     *
     * @param string $category Color category
     * @return array{
     *     primary_traits: array<string>,
     *     industry_alignment: array<string>,
     *     emotional_response: array<string>,
     *     brand_values: array<string>
     * }
     */
    private function get_brand_personality(string $category): array {
        $personalities = [
            'red' => [
                'primary_traits' => ['bold', 'energetic', 'passionate', 'dynamic'],
                'industry_alignment' => ['food', 'sports', 'entertainment', 'retail'],
                'emotional_response' => ['excitement', 'urgency', 'power', 'confidence'],
                'brand_values' => ['action', 'energy', 'passion', 'strength']
            ],
            'blue' => [
                'primary_traits' => ['trustworthy', 'professional', 'reliable', 'calm'],
                'industry_alignment' => ['finance', 'technology', 'healthcare', 'consulting'],
                'emotional_response' => ['trust', 'stability', 'confidence', 'serenity'],
                'brand_values' => ['integrity', 'expertise', 'reliability', 'professionalism']
            ],
            'green' => [
                'primary_traits' => ['natural', 'balanced', 'peaceful', 'growth-oriented'],
                'industry_alignment' => ['health', 'environment', 'education', 'finance'],
                'emotional_response' => ['harmony', 'balance', 'growth', 'freshness'],
                'brand_values' => ['sustainability', 'health', 'growth', 'balance']
            ],
            'purple' => [
                'primary_traits' => ['luxurious', 'creative', 'wise', 'sophisticated'],
                'industry_alignment' => ['luxury', 'beauty', 'arts', 'education'],
                'emotional_response' => ['luxury', 'creativity', 'wisdom', 'quality'],
                'brand_values' => ['excellence', 'creativity', 'wisdom', 'luxury']
            ],
            'orange' => [
                'primary_traits' => ['friendly', 'cheerful', 'confident', 'adventurous'],
                'industry_alignment' => ['food', 'fitness', 'travel', 'youth'],
                'emotional_response' => ['enthusiasm', 'adventure', 'sociability', 'fun'],
                'brand_values' => ['creativity', 'adventure', 'friendliness', 'enthusiasm']
            ]
        ];

        return $personalities[$category] ?? [
            'primary_traits' => ['professional', 'balanced', 'trustworthy'],
            'industry_alignment' => ['general business', 'services'],
            'emotional_response' => ['trust', 'reliability'],
            'brand_values' => ['professionalism', 'quality']
        ];
    }

    /**
     * Get target audience characteristics for business type
     *
     * @param string $category Color category
     * @return array{
     *     demographics: array<string>,
     *     psychographics: array<string>,
     *     behavior_patterns: array<string>,
     *     communication_style: string
     * }
     */
    private function get_target_audience(string $category): array {
        $audiences = [
            'red' => [
                'demographics' => ['25-45', 'action-oriented', 'urban'],
                'psychographics' => ['ambitious', 'energetic', 'trend-conscious'],
                'behavior_patterns' => ['quick decision makers', 'social media active', 'brand-conscious'],
                'communication_style' => 'direct and energetic'
            ],
            'blue' => [
                'demographics' => ['30-60', 'professional', 'suburban/urban'],
                'psychographics' => ['analytical', 'security-focused', 'quality-oriented'],
                'behavior_patterns' => ['research-driven', 'loyalty-focused', 'value-conscious'],
                'communication_style' => 'professional and informative'
            ],
            'green' => [
                'demographics' => ['25-55', 'health-conscious', 'suburban'],
                'psychographics' => ['environmentally conscious', 'wellness-oriented', 'community-focused'],
                'behavior_patterns' => ['eco-friendly choices', 'health-focused', 'community-engaged'],
                'communication_style' => 'authentic and educational'
            ],
            'purple' => [
                'demographics' => ['30-65', 'affluent', 'urban'],
                'psychographics' => ['luxury-oriented', 'status-conscious', 'quality-focused'],
                'behavior_patterns' => ['premium buyers', 'trend-setters', 'experience-seekers'],
                'communication_style' => 'sophisticated and exclusive'
            ]
        ];

        return $audiences[$category] ?? [
            'demographics' => ['25-65', 'general public'],
            'psychographics' => ['value-conscious', 'practical'],
            'behavior_patterns' => ['considered decision makers'],
            'communication_style' => 'clear and professional'
        ];
    }

    /**
     * Get marketing style recommendations
     *
     * @param string $category Color category
     * @return array{
     *     tone: string,
     *     content_style: array<string>,
     *     visual_elements: array<string>,
     *     marketing_channels: array<string>
     * }
     */
    private function get_marketing_style(string $category): array {
        $styles = [
            'red' => [
                'tone' => 'bold and dynamic',
                'content_style' => ['action-oriented', 'energetic', 'direct', 'compelling'],
                'visual_elements' => ['bold typography', 'dynamic imagery', 'strong contrasts'],
                'marketing_channels' => ['social media', 'video marketing', 'outdoor advertising']
            ],
            'blue' => [
                'tone' => 'professional and trustworthy',
                'content_style' => ['informative', 'clear', 'authoritative', 'reliable'],
                'visual_elements' => ['clean layouts', 'professional imagery', 'structured design'],
                'marketing_channels' => ['linkedin', 'email marketing', 'industry publications']
            ],
            'green' => [
                'tone' => 'natural and balanced',
                'content_style' => ['educational', 'authentic', 'nurturing', 'community-focused'],
                'visual_elements' => ['organic shapes', 'natural imagery', 'balanced layouts'],
                'marketing_channels' => ['content marketing', 'community events', 'sustainability forums']
            ],
            'purple' => [
                'tone' => 'luxurious and sophisticated',
                'content_style' => ['refined', 'exclusive', 'premium', 'cultured'],
                'visual_elements' => ['elegant typography', 'luxury imagery', 'refined details'],
                'marketing_channels' => ['high-end publications', 'exclusive events', 'influencer partnerships']
            ]
        ];

        return $styles[$category] ?? [
            'tone' => 'professional and approachable',
            'content_style' => ['clear', 'professional', 'friendly'],
            'visual_elements' => ['clean design', 'professional imagery'],
            'marketing_channels' => ['website', 'email', 'social media']
        ];
    }

    /**
     * Generate AI-driven color palette based on business description
     *
     * @param string $business_description Description of the business
     * @param string $target_audience Description of target audience
     * @param array $preferences Additional preferences or constraints
     * @return array{
     *     primary: string,
     *     secondary: string,
     *     tertiary: string,
     *     accent: string,
     *     rationale: array<string, string>
     * }
     */
    public function generate_business_palette(
        string $business_description,
        string $target_audience,
        array $preferences = []
    ): array {
        // Analyze business description for industry and brand personality
        $industry_keywords = $this->extract_industry_keywords($business_description);
        $personality_traits = $this->extract_personality_traits($business_description);

        // Analyze target audience
        $audience_characteristics = $this->analyze_audience($target_audience);

        // Select primary color based on industry and brand personality
        $primary_color = $this->select_primary_color($industry_keywords, $personality_traits);

        // Generate complementary colors that align with brand personality
        $secondary_color = $this->generate_secondary_color($primary_color, $personality_traits);
        $tertiary_color = $this->generate_tertiary_color($primary_color, $secondary_color);
        $accent_color = $this->generate_accent_color($primary_color, $preferences);

        return [
            'primary' => $primary_color,
            'secondary' => $secondary_color,
            'tertiary' => $tertiary_color,
            'accent' => $accent_color,
            'rationale' => [
                'primary' => $this->get_color_rationale($primary_color, $industry_keywords),
                'secondary' => $this->get_color_rationale($secondary_color, $personality_traits),
                'tertiary' => 'Selected for balance and harmony with primary/secondary colors',
                'accent' => 'Chosen to provide effective contrast and call-to-action emphasis'
            ]
        ];
    }

    /**
     * Extract industry keywords from business description
     *
     * @param string $description Business description
     * @return array<string> Industry keywords
     */
    private function extract_industry_keywords(string $description): array {
        $industry_keywords = [
            'technology' => ['tech', 'software', 'digital', 'online', 'internet', 'web', 'app', 'cloud'],
            'finance' => ['finance', 'banking', 'investment', 'insurance', 'wealth', 'money', 'financial'],
            'healthcare' => ['health', 'medical', 'wellness', 'care', 'clinic', 'hospital', 'therapy'],
            'retail' => ['shop', 'store', 'retail', 'ecommerce', 'products', 'goods', 'merchandise'],
            'education' => ['education', 'learning', 'teaching', 'school', 'training', 'academic', 'courses'],
            'creative' => ['design', 'art', 'creative', 'media', 'studio', 'agency', 'brand'],
            'food' => ['food', 'restaurant', 'cafe', 'catering', 'cuisine', 'dining', 'culinary'],
            'fitness' => ['fitness', 'gym', 'sports', 'workout', 'training', 'exercise', 'health'],
            'beauty' => ['beauty', 'salon', 'spa', 'cosmetics', 'skincare', 'makeup', 'wellness']
        ];

        $found_keywords = [];
        $description = strtolower($description);

        foreach ($industry_keywords as $industry => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($description, $keyword) !== false) {
                    $found_keywords[] = $industry;
                    break;
                }
            }
        }

        return array_unique($found_keywords);
    }

    /**
     * Extract personality traits from business description
     *
     * @param string $description Business description
     * @return array<string> Personality traits
     */
    private function extract_personality_traits(string $description): array {
        $personality_keywords = [
            'professional' => ['professional', 'expert', 'reliable', 'trusted', 'established'],
            'innovative' => ['innovative', 'cutting-edge', 'modern', 'advanced', 'tech-savvy'],
            'friendly' => ['friendly', 'welcoming', 'warm', 'personal', 'family'],
            'luxury' => ['luxury', 'premium', 'exclusive', 'high-end', 'sophisticated'],
            'creative' => ['creative', 'artistic', 'unique', 'original', 'innovative'],
            'traditional' => ['traditional', 'classic', 'heritage', 'established', 'trusted'],
            'energetic' => ['energetic', 'dynamic', 'active', 'vibrant', 'exciting'],
            'eco-friendly' => ['sustainable', 'eco', 'green', 'natural', 'organic']
        ];

        $found_traits = [];
        $description = strtolower($description);

        foreach ($personality_keywords as $trait => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($description, $keyword) !== false) {
                    $found_traits[] = $trait;
                    break;
                }
            }
        }

        return array_unique($found_traits);
    }

    /**
     * Analyze target audience characteristics
     *
     * @param string $audience Target audience description
     * @return array{
     *     demographics: array<string>,
     *     psychographics: array<string>,
     *     behavior_patterns: array<string>,
     *     communication_style: string
     * }
     */
    private function analyze_audience(string $audience): array {
        $age_patterns = [
            'young' => '/young|youth|teen|20s/',
            'middle' => '/30s|40s|middle|professional|adult/',
            'senior' => '/senior|elderly|mature|50\+|60\+/'
        ];

        $demographic_patterns = [
            'professional' => '/professional|executive|business/',
            'student' => '/student|academic|education/',
            'parent' => '/parent|family|mom|dad/',
            'urban' => '/urban|city|metropolitan/',
            'suburban' => '/suburban|residential/',
            'affluent' => '/affluent|wealthy|luxury/'
        ];

        $interest_patterns = [
            'tech' => '/tech|digital|online/',
            'health' => '/health|wellness|fitness/',
            'fashion' => '/fashion|style|trend/',
            'outdoor' => '/outdoor|nature|adventure/',
            'arts' => '/art|culture|creative/',
            'business' => '/business|professional|corporate/'
        ];

        $audience = strtolower($audience);
        $analysis = [
            'age_group' => 'general',
            'demographics' => [],
            'interests' => [],
            'preferences' => []
        ];

        // Analyze age group
        foreach ($age_patterns as $group => $pattern) {
            if (preg_match($pattern, $audience)) {
                $analysis['age_group'] = $group;
                break;
            }
        }

        // Analyze demographics
        foreach ($demographic_patterns as $demo => $pattern) {
            if (preg_match($pattern, $audience)) {
                $analysis['demographics'][] = $demo;
            }
        }

        // Analyze interests
        foreach ($interest_patterns as $interest => $pattern) {
            if (preg_match($pattern, $audience)) {
                $analysis['interests'][] = $interest;
            }
        }

        return $analysis;
    }

    /**
     * Select primary color based on industry and brand personality
     *
     * @param array<string> $industry_keywords Industry keywords
     * @param array<string> $personality_traits Personality traits
     * @return string Hex color code
     */
    private function select_primary_color(array $industry_keywords, array $personality_traits): string {
        $industry_colors = [
            'technology' => ['#0066CC', '#00A3E0', '#2D72D9'],
            'finance' => ['#004B87', '#1B447E', '#12376E'],
            'healthcare' => ['#00A0B0', '#44B3C2', '#2C8C99'],
            'retail' => ['#FF6B6B', '#4ECDC4', '#45B7AF'],
            'education' => ['#4A90E2', '#5B9BD5', '#3F7CAC'],
            'creative' => ['#FF5252', '#FF7B7B', '#E64A4A'],
            'food' => ['#FF9900', '#F7B733', '#FC913A'],
            'fitness' => ['#00B16A', '#26A65B', '#03C03C'],
            'beauty' => ['#FF69B4', '#FF8DA1', '#FF5983']
        ];

        $personality_colors = [
            'professional' => ['#2C3E50', '#34495E', '#2E4053'],
            'innovative' => ['#00BCD4', '#03A9F4', '#039BE5'],
            'friendly' => ['#FF9800', '#FFA726', '#FB8C00'],
            'luxury' => ['#8E44AD', '#9B59B6', '#8E44AD'],
            'creative' => ['#E74C3C', '#FF5252', '#FF5722'],
            'traditional' => ['#2E4053', '#34495E', '#2C3E50'],
            'energetic' => ['#FF4081', '#FF5252', '#FF1744'],
            'eco-friendly' => ['#4CAF50', '#66BB6A', '#43A047']
        ];

        // Get color candidates based on industry
        $color_candidates = [];
        foreach ($industry_keywords as $industry) {
            if (isset($industry_colors[$industry])) {
                $color_candidates = array_merge($color_candidates, $industry_colors[$industry]);
            }
        }

        // Add colors based on personality traits
        foreach ($personality_traits as $trait) {
            if (isset($personality_colors[$trait])) {
                $color_candidates = array_merge($color_candidates, $personality_colors[$trait]);
            }
        }

        // If no specific colors found, use default professional colors
        if (empty($color_candidates)) {
            $color_candidates = $personality_colors['professional'];
        }

        // Store original color for reference
        $original_color = $color_candidates[0];

        // Generate accessible variations
        $variations = $this->generate_color_variations($original_color);

        // Return both original and variations
        return [
            'original' => $original_color,
            'variations' => $variations
        ];
    }

    /**
     * Generate secondary color that complements primary color
     *
     * @param string $primary_color Primary color hex code
     * @param array<string> $personality_traits Personality traits
     * @return string Hex color code
     */
    private function generate_secondary_color(string $primary_color, array $personality_traits): string {
        $hsl = $this->color_utility->hex_to_hsl($primary_color);

        // Adjust hue based on personality traits
        $hue_shift = 30; // Default complementary shift
        if (in_array('creative', $personality_traits)) {
            $hue_shift = 60; // More contrasting for creative brands
        } elseif (in_array('traditional', $personality_traits)) {
            $hue_shift = 15; // More subtle for traditional brands
        }

        $hsl['h'] = fmod($hsl['h'] + $hue_shift, 360);

        // Adjust saturation and lightness
        $hsl['s'] = min(100, $hsl['s'] * 0.9); // Slightly less saturated
        $hsl['l'] = min(100, $hsl['l'] * 1.1); // Slightly lighter

        return $this->color_utility->hsl_to_hex($hsl);
    }

    /**
     * Generate tertiary color that complements primary and secondary colors
     *
     * @param string $primary_color Primary color hex code
     * @param string $secondary_color Secondary color hex code
     * @return string Hex color code
     */
    private function generate_tertiary_color(string $primary_color, string $secondary_color): string {
        $primary_hsl = $this->color_utility->hex_to_hsl($primary_color);
        $secondary_hsl = $this->color_utility->hex_to_hsl($secondary_color);

        // Create tertiary color by finding middle point and shifting
        $hsl = [
            'h' => fmod(($primary_hsl['h'] + $secondary_hsl['h']) / 2 + 60, 360),
            's' => min(100, (($primary_hsl['s'] + $secondary_hsl['s']) / 2) * 0.85),
            'l' => min(100, (($primary_hsl['l'] + $secondary_hsl['l']) / 2) * 1.15)
        ];

        return $this->color_utility->hsl_to_hex($hsl);
    }

    /**
     * Generate accent color that enhances the palette
     *
     * @param string $primary_color Primary color hex code
     * @return string Hex color code
     */
    private function generate_accent_color(string $primary_color): string {
        $hsl = $this->color_utility->hex_to_hsl($primary_color);

        // Create vibrant accent by increasing saturation and shifting hue
        $hsl['h'] = fmod($hsl['h'] + 120, 360); // Opposite hue
        $hsl['s'] = min(100, $hsl['s'] * 1.2); // More saturated
        $hsl['l'] = min(100, $hsl['l'] * 0.9); // Slightly darker

        return $this->color_utility->hsl_to_hex($hsl);
    }

    /**
     * Generate a color palette from existing colors with good contrast
     *
     * @param array<string> $base_colors Array of 1-4 hex color codes
     * @param array $options Optional settings for palette generation
     * @return array{
     *     colors: array<string>,
     *     contrast_ratios: array<string, float>,
     *     accessibility: array
     * }
     */
    public function generate_palette_from_colors(array $base_colors, array $options = []): array {
        // Validate input colors
        $validated_colors = [];
        foreach ($base_colors as $color) {
            try {
                $this->validate_color($color);
                $validated_colors[] = $color;
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }

        if (empty($validated_colors)) {
            throw new \InvalidArgumentException('No valid colors provided');
        }

        // Optimize base colors for visual separation
        $separated_colors = $this->optimize_color_separation($validated_colors);

        // Generate additional colors if needed
        $palette_colors = $this->expand_color_palette($separated_colors);

        // Optimize for accessibility
        $optimized_colors = $this->optimize_palette_accessibility($palette_colors);

        // Calculate contrast ratios with black and white
        $contrast_ratios = [];
        foreach ($optimized_colors as $color) {
            $white_contrast = $this->accessibility_checker->calculate_contrast_ratio($color, '#FFFFFF');
            $black_contrast = $this->accessibility_checker->calculate_contrast_ratio($color, '#000000');

            $contrast_ratios[$color] = [
                'black' => $black_contrast,
                'white' => $white_contrast
            ];
        }

        // Generate accessibility report
        $accessibility = $this->analyze_palette_accessibility($optimized_colors);

        return [
            'colors' => $optimized_colors,
            'contrast_ratios' => $contrast_ratios,
            'accessibility' => $accessibility
        ];
    }

    /**
     * Ensure base colors are visually distinct from each other
     *
     * @param array<string> $colors Colors to adjust
     * @return array<string> Adjusted colors with sufficient visual separation
     */
    private function optimize_color_separation(array $colors): array {
        if (count($colors) <= 1) {
            return $colors;
        }

        $optimized = [$colors[0]]; // Keep first color as anchor
        $min_distance = 25; // Minimum CIELAB delta E distance

        // For each remaining color, ensure it's different enough from existing ones
        for ($i = 1; $i < count($colors); $i++) {
            $current_hsl = $this->color_utility->hex_to_hsl($colors[$i]);
            $needs_adjustment = false;

            // Check distance from all previously optimized colors
            foreach ($optimized as $existing) {
                $existing_hsl = $this->color_utility->hex_to_hsl($existing);
                $distance = $this->calculate_color_distance($current_hsl, $existing_hsl);

                if ($distance < $min_distance) {
                    $needs_adjustment = true;
                    break;
                }
            }

            if ($needs_adjustment) {
                // Adjust hue and saturation to increase separation
                $adjusted = $current_hsl;
                $max_attempts = 8;
                $best_distance = 0;
                $best_color = $colors[$i];

                for ($attempt = 0; $attempt < $max_attempts; $attempt++) {
                    // Try different hue shifts
                    $hue_shift = 45 * ($attempt + 1);
                    $adjusted['h'] = fmod($current_hsl['h'] + $hue_shift, 360);
                    $adjusted['s'] = min(100, $current_hsl['s'] * 1.1);

                    $min_current_distance = PHP_FLOAT_MAX;
                    foreach ($optimized as $existing) {
                        $existing_hsl = $this->color_utility->hex_to_hsl($existing);
                        $distance = $this->calculate_color_distance($adjusted, $existing_hsl);
                        $min_current_distance = min($min_current_distance, $distance);
                    }

                    if ($min_current_distance > $best_distance) {
                        $best_distance = $min_current_distance;
                        $best_color = $this->color_utility->hsl_to_hex($adjusted);
                    }

                    if ($best_distance >= $min_distance) {
                        break;
                    }
                }

                $optimized[] = $best_color;
            } else {
                $optimized[] = $colors[$i];
            }
        }

        return $optimized;
    }

    /**
     * Calculate perceptual distance between two colors in CIELAB space
     *
     * @param array $color1_hsl First color in HSL
     * @param array $color2_hsl Second color in HSL
     * @return float Delta E distance
     */
    private function calculate_color_distance(array $color1_hsl, array $color2_hsl): float {
        // Convert HSL to LAB for better perceptual distance calculation
        $color1_hex = $this->color_utility->hsl_to_hex($color1_hsl);
        $color2_hex = $this->color_utility->hsl_to_hex($color2_hsl);

        $color1_lab = $this->color_utility->hex_to_lab($color1_hex);
        $color2_lab = $this->color_utility->hex_to_lab($color2_hex);

        // Calculate CIEDE2000 Delta E
        return sqrt(
            pow($color1_lab['L'] - $color2_lab['L'], 2) +
            pow($color1_lab['a'] - $color2_lab['a'], 2) +
            pow($color1_lab['b'] - $color2_lab['b'], 2)
        );
    }

    /**
     * Optimize colors for accessibility with black/white text
     *
     * @param array<string> $colors Colors to optimize
     * @return array<string> Optimized colors
     */
    private function optimize_palette_accessibility(array $colors): array {
        $optimized = [];
        foreach ($colors as $color) {
            $hsl = $this->color_utility->hex_to_hsl($color);

            // Ensure sufficient contrast by adjusting lightness
            $white_contrast = $this->accessibility_checker->calculate_contrast_ratio($color, '#FFFFFF');
            $black_contrast = $this->accessibility_checker->calculate_contrast_ratio($color, '#000000');

            if ($white_contrast < 4.5 && $black_contrast < 4.5) {
                // Adjust lightness to improve contrast
                if ($hsl['l'] > 50) {
                    $hsl['l'] = max(20, $hsl['l'] - 20);
                } else {
                    $hsl['l'] = min(80, $hsl['l'] + 20);
                }
                $optimized[] = $this->color_utility->hsl_to_hex($hsl);
            } else {
                $optimized[] = $color;
            }
        }

        return $optimized;
    }

    /**
     * Expand a set of base colors into a full palette
     *
     * @param array<string> $base_colors Base colors to expand from
     * @return array<string> Complete color palette
     */
    private function expand_color_palette(array $base_colors): array {
        $palette = $base_colors;
        $count = count($base_colors);

        // If only one color provided, generate complementary colors
        if ($count === 1) {
            $primary_hsl = $this->color_utility->hex_to_hsl($base_colors[0]);

            // Add complementary color
            $complementary = $primary_hsl;
            $complementary['h'] = fmod($complementary['h'] + 180, 360);
            $palette[] = $this->color_utility->hsl_to_hex($complementary);

            // Add analogous colors
            $analogous1 = $primary_hsl;
            $analogous1['h'] = fmod($analogous1['h'] + 30, 360);
            $palette[] = $this->color_utility->hsl_to_hex($analogous1);

            $analogous2 = $primary_hsl;
            $analogous2['h'] = fmod($analogous2['h'] - 30, 360);
            $palette[] = $this->color_utility->hsl_to_hex($analogous2);
        }
        // If two colors provided, generate intermediate and accent colors
        elseif ($count === 2) {
            $color1_hsl = $this->color_utility->hex_to_hsl($base_colors[0]);
            $color2_hsl = $this->color_utility->hex_to_hsl($base_colors[1]);

            // Add intermediate color
            $intermediate = [
                'h' => fmod(($color1_hsl['h'] + $color2_hsl['h']) / 2, 360),
                's' => ($color1_hsl['s'] + $color2_hsl['s']) / 2,
                'l' => ($color1_hsl['l'] + $color2_hsl['l']) / 2
            ];
            $palette[] = $this->color_utility->hsl_to_hex($intermediate);

            // Add accent color
            $accent = $color1_hsl;
            $accent['h'] = fmod($accent['h'] + 120, 360);
            $accent['s'] = min(100, $accent['s'] * 1.2);
            $palette[] = $this->color_utility->hsl_to_hex($accent);
        }
        // If three colors provided, generate one more complementary color
        elseif ($count === 3) {
            $primary_hsl = $this->color_utility->hex_to_hsl($base_colors[0]);
            $accent = $primary_hsl;
            $accent['h'] = fmod($accent['h'] + 180, 360);
            $accent['s'] = min(100, $accent['s'] * 1.1);
            $accent['l'] = min(100, $accent['l'] * 0.9);
            $palette[] = $this->color_utility->hsl_to_hex($accent);
        }

        return array_unique($palette);
    }

    /**
     * Analyze accessibility of a color palette
     *
     * @param array<string> $colors Colors to analyze
     * @return array Accessibility analysis
     */
    private function analyze_palette_accessibility(array $colors): array {
        $analysis = [
            'wcag_compliance' => [],
            'recommendations' => []
        ];

        foreach ($colors as $color) {
            $white_contrast = $this->accessibility_checker->calculate_contrast_ratio($color, '#FFFFFF');
            $black_contrast = $this->accessibility_checker->calculate_contrast_ratio($color, '#000000');

            $analysis['wcag_compliance'][$color] = [
                'AA_large_text' => $white_contrast >= 3.0 || $black_contrast >= 3.0,
                'AA_small_text' => $white_contrast >= 4.5 || $black_contrast >= 4.5,
                'AAA_large_text' => $white_contrast >= 4.5 || $black_contrast >= 4.5,
                'AAA_small_text' => $white_contrast >= 7.0 || $black_contrast >= 7.0,
                'recommended_text_color' => ($white_contrast > $black_contrast) ? '#FFFFFF' : '#000000'
            ];

            // Add recommendations if needed
            if (!$analysis['wcag_compliance'][$color]['AA_small_text']) {
                $analysis['recommendations'][] = "Consider adjusting the lightness of $color for better text contrast";
            }
        }

        return $analysis;
    }

    /**
     * Calculate color temperature
     *
     * @param array $rgb RGB color values
     * @return float Color temperature value
     */
    private function calculate_temperature(array $rgb): float {
        return (($rgb['r'] * 2) + $rgb['g'] + $rgb['b']) / 4;
    }

    /**
     * Calculate perceived weight of a color
     *
     * @param array $hsl HSL color values
     * @return float Weight value
     */
    private function calculate_weight(array $hsl): float {
        return (1 - $hsl['l']) * (1 + $hsl['s'] * 0.5);
    }

    /**
     * Calculate color activity level
     *
     * @param array $rgb RGB color values
     * @param array $hsl HSL color values
     * @return float Activity level
     */
    private function calculate_activity(array $rgb, array $hsl): float {
        $saturation_factor = $hsl['s'];
        $contrast_factor = max($rgb['r'], $rgb['g'], $rgb['b']) - min($rgb['r'], $rgb['g'], $rgb['b']);
        return ($saturation_factor + $contrast_factor / 255) / 2;
    }

    /**
     * Calculate color purity
     *
     * @param array $rgb RGB color values
     * @return float Purity value
     */
    private function calculate_color_purity(array $rgb): float {
        $max = max($rgb['r'], $rgb['g'], $rgb['b']);
        $min = min($rgb['r'], $rgb['g'], $rgb['b']);
        return ($max - $min) / 255;
    }

    /**
     * Calculate brightness
     *
     * @param array $rgb RGB color values
     * @return float Brightness value
     */
    private function calculate_brightness(array $rgb): float {
        return (($rgb['r'] * 299) + ($rgb['g'] * 587) + ($rgb['b'] * 114)) / 1000;
    }
}
