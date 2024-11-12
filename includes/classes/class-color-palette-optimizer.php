<?php

namespace GLColorPalette;

/**
 * Color Palette Optimizer Class
 *
 * Optimizes color palettes for various criteria including contrast, harmony, and accessibility.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
class ColorPaletteOptimizer {
    /**
     * Color formatter instance.
     *
     * @var ColorPaletteFormatter
     */
    private $formatter;

    /**
     * Color analyzer instance.
     *
     * @var ColorPaletteAnalyzer
     */
    private $analyzer;

    /**
     * Default optimization options.
     *
     * @var array
     */
    private $default_options = [
        'target_contrast_ratio' => 4.5,    // WCAG AA standard
        'min_contrast_ratio' => 3.0,       // Minimum acceptable contrast
        'harmony_weight' => 0.3,           // Weight for harmony score
        'contrast_weight' => 0.4,          // Weight for contrast score
        'accessibility_weight' => 0.3,      // Weight for accessibility score
        'max_iterations' => 100,           // Maximum optimization iterations
        'convergence_threshold' => 0.01,   // Minimum improvement for convergence
        'preserve_primary' => true,        // Whether to preserve primary color
        'color_space' => 'hsl',           // Color space for optimization
        'optimization_strategy' => 'global' // global|local optimization strategy
    ];

    /**
     * Constructor.
     *
     * @param ColorPaletteFormatter $formatter Color formatter instance.
     * @param ColorPaletteAnalyzer  $analyzer  Color analyzer instance.
     */
    public function __construct(
        ColorPaletteFormatter $formatter,
        ColorPaletteAnalyzer $analyzer
    ) {
        $this->formatter = $formatter;
        $this->analyzer = $analyzer;
    }

    /**
     * Optimizes a color palette.
     *
     * @param ColorPalette $palette Palette to optimize.
     * @param array $options Optional. Optimization options.
     * @return ColorPalette Optimized palette.
     */
    public function optimize_palette(ColorPalette $palette, array $options = []): ColorPalette {
        $options = array_merge($this->default_options, $options);
        $colors = $palette->get_colors();

        if (empty($colors)) {
            return $palette;
        }

        $best_score = $this->evaluate_palette($palette, $options);
        $best_colors = $colors;
        $iterations = 0;
        $last_improvement = 0;

        while ($iterations < $options['max_iterations']) {
            $candidate_colors = $this->generate_candidate_colors(
                $best_colors,
                $options
            );

            $candidate_palette = new ColorPalette([
                'name' => $palette->get_name(),
                'colors' => $candidate_colors
            ]);

            $candidate_score = $this->evaluate_palette($candidate_palette, $options);

            if ($candidate_score > $best_score) {
                $improvement = $candidate_score - $best_score;
                if ($improvement < $options['convergence_threshold']) {
                    $last_improvement++;
                    if ($last_improvement > 5) { // No significant improvement in 5 iterations
                        break;
                    }
                } else {
                    $last_improvement = 0;
                }

                $best_score = $candidate_score;
                $best_colors = $candidate_colors;
            }

            $iterations++;
        }

        return new ColorPalette([
            'name' => $palette->get_name(),
            'colors' => $best_colors,
            'metadata' => array_merge($palette->get_metadata(), [
                'optimized' => true,
                'optimization_score' => $best_score,
                'optimization_iterations' => $iterations
            ])
        ]);
    }

    /**
     * Evaluates a palette's fitness score.
     *
     * @param ColorPalette $palette Palette to evaluate.
     * @param array $options Evaluation options.
     * @return float Fitness score (0-1).
     */
    private function evaluate_palette(ColorPalette $palette, array $options): float {
        $analysis = $this->analyzer->analyze_palette($palette);

        // Calculate contrast score
        $contrast_score = $this->calculate_contrast_score(
            $analysis['contrast'],
            $options['target_contrast_ratio']
        );

        // Calculate harmony score
        $harmony_score = $analysis['harmony']['harmony_score'];

        // Calculate accessibility score
        $accessibility_score = $this->calculate_accessibility_score(
            $analysis['accessibility']
        );

        // Weighted combination of scores
        return
            $options['contrast_weight'] * $contrast_score +
            $options['harmony_weight'] * $harmony_score +
            $options['accessibility_weight'] * $accessibility_score;
    }

    /**
     * Generates candidate colors for optimization.
     *
     * @param array $current_colors Current colors.
     * @param array $options Generation options.
     * @return array Candidate colors.
     */
    private function generate_candidate_colors(array $current_colors, array $options): array {
        $candidates = $current_colors;

        if ($options['optimization_strategy'] === 'global') {
            return $this->global_optimization_step($candidates, $options);
        } else {
            return $this->local_optimization_step($candidates, $options);
        }
    }

    /**
     * Performs global optimization step.
     *
     * @param array $colors Current colors.
     * @param array $options Optimization options.
     * @return array Modified colors.
     */
    private function global_optimization_step(array $colors, array $options): array {
        $modified = [];
        $start_index = $options['preserve_primary'] ? 1 : 0;

        for ($i = $start_index; $i < count($colors); $i++) {
            $color = $colors[$i];
            $hsl = $this->formatter->format_color($color, 'hsl');
            preg_match('/hsl\((\d+),\s*(\d+)%?,\s*(\d+)%?\)/', $hsl, $matches);

            // Apply global transformations
            $h = ($matches[1] + rand(-30, 30)) % 360;
            $s = max(0, min(100, $matches[2] + rand(-10, 10)));
            $l = max(0, min(100, $matches[3] + rand(-10, 10)));

            $modified[] = $this->formatter->format_color(
                "hsl({$h}, {$s}%, {$l}%)",
                'hex'
            );
        }

        return array_merge(
            array_slice($colors, 0, $start_index),
            $modified
        );
    }

    /**
     * Performs local optimization step.
     *
     * @param array $colors Current colors.
     * @param array $options Optimization options.
     * @return array Modified colors.
     */
    private function local_optimization_step(array $colors, array $options): array {
        $modified = [];
        $start_index = $options['preserve_primary'] ? 1 : 0;

        for ($i = $start_index; $i < count($colors); $i++) {
            $color = $colors[$i];
            $hsl = $this->formatter->format_color($color, 'hsl');
            preg_match('/hsl\((\d+),\s*(\d+)%?,\s*(\d+)%?\)/', $hsl, $matches);

            // Apply local transformations based on neighbors
            $prev_color = $i > 0 ? $colors[$i - 1] : null;
            $next_color = $i < count($colors) - 1 ? $colors[$i + 1] : null;

            list($h, $s, $l) = $this->calculate_local_adjustment(
                [$matches[1], $matches[2], $matches[3]],
                $prev_color,
                $next_color,
                $options
            );

            $modified[] = $this->formatter->format_color(
                "hsl({$h}, {$s}%, {$l}%)",
                'hex'
            );
        }

        return array_merge(
            array_slice($colors, 0, $start_index),
            $modified
        );
    }

    /**
     * Calculates contrast score.
     *
     * @param array $contrast_analysis Contrast analysis results.
     * @param float $target_ratio Target contrast ratio.
     * @return float Score (0-1).
     */
    private function calculate_contrast_score(array $contrast_analysis, float $target_ratio): float {
        $ratios = array_column($contrast_analysis['ratios'], 'ratio');
        if (empty($ratios)) {
            return 0;
        }

        $score = 0;
        foreach ($ratios as $ratio) {
            $score += min(1, $ratio / $target_ratio);
        }

        return $score / count($ratios);
    }

    /**
     * Calculates accessibility score.
     *
     * @param array $accessibility_analysis Accessibility analysis results.
     * @return float Score (0-1).
     */
    private function calculate_accessibility_score(array $accessibility_analysis): float {
        $wcag = $accessibility_analysis['wcag_compliance'];
        $cvd = $accessibility_analysis['color_blindness'];

        $wcag_score = ($wcag['aa_pass_rate'] + $wcag['aaa_pass_rate'] * 1.5) / 2.5;
        $cvd_score = empty($cvd['issues']) ? 1 : 1 - (count($cvd['issues']) * 0.2);

        return ($wcag_score + $cvd_score) / 2;
    }

    /**
     * Calculates local color adjustment.
     *
     * @param array $current_hsl Current HSL values.
     * @param string|null $prev_color Previous color.
     * @param string|null $next_color Next color.
     * @param array $options Adjustment options.
     * @return array Adjusted HSL values.
     */
    private function calculate_local_adjustment(
        array $current_hsl,
        ?string $prev_color,
        ?string $next_color,
        array $options
    ): array {
        list($h, $s, $l) = $current_hsl;

        if ($prev_color) {
            $prev_hsl = $this->color_to_hsl($prev_color);
            $h = $this->adjust_hue($h, $prev_hsl[0], $options);
            $s = $this->adjust_saturation($s, $prev_hsl[1], $options);
            $l = $this->adjust_lightness($l, $prev_hsl[2], $options);
        }

        if ($next_color) {
            $next_hsl = $this->color_to_hsl($next_color);
            $h = $this->adjust_hue($h, $next_hsl[0], $options);
            $s = $this->adjust_saturation($s, $next_hsl[1], $options);
            $l = $this->adjust_lightness($l, $next_hsl[2], $options);
        }

        return [
            max(0, min(360, $h)),
            max(0, min(100, $s)),
            max(0, min(100, $l))
        ];
    }

    /**
     * Converts color to HSL array.
     *
     * @param string $color Color value.
     * @return array HSL values [h, s, l].
     */
    private function color_to_hsl(string $color): array {
        $hsl = $this->formatter->format_color($color, 'hsl');
        preg_match('/hsl\((\d+),\s*(\d+)%?,\s*(\d+)%?\)/', $hsl, $matches);
        return [
            (int)$matches[1],
            (int)$matches[2],
            (int)$matches[3]
        ];
    }

    /**
     * Adjusts hue value based on reference.
     *
     * @param int $current Current hue.
     * @param int $reference Reference hue.
     * @param array $options Adjustment options.
     * @return int Adjusted hue.
     */
    private function adjust_hue(int $current, int $reference, array $options): int {
        $diff = abs($current - $reference);
        if ($diff < 30) {
            return $current + rand(30, 60) * (rand(0, 1) ? 1 : -1);
        }
        return $current;
    }

    /**
     * Adjusts saturation value based on reference.
     *
     * @param int $current Current saturation.
     * @param int $reference Reference saturation.
     * @param array $options Adjustment options.
     * @return int Adjusted saturation.
     */
    private function adjust_saturation(int $current, int $reference, array $options): int {
        $diff = abs($current - $reference);
        if ($diff < 10) {
            return $current + rand(10, 20) * (rand(0, 1) ? 1 : -1);
        }
        return $current;
    }

    /**
     * Adjusts lightness value based on reference.
     *
     * @param int $current Current lightness.
     * @param int $reference Reference lightness.
     * @param array $options Adjustment options.
     * @return int Adjusted lightness.
     */
    private function adjust_lightness(int $current, int $reference, array $options): int {
        $diff = abs($current - $reference);
        if ($diff < 10) {
            return $current + rand(10, 20) * (rand(0, 1) ? 1 : -1);
        }
        return $current;
    }
} 
