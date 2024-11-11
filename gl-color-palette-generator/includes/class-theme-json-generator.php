<?php

/**
 * Class ThemeJsonGenerator
 */
class ThemeJsonGenerator {
    /**
     * Create individual style variation
     */
    private function create_style_variation($primary_colors, $secondary_colors, $primary_role, $secondary_role) {
        // Use 'light' variation for naming instead of 'original'
        $primary_name = $this->sanitize_variation_name($primary_colors['names']['light']);
        $secondary_name = $this->sanitize_variation_name($secondary_colors['names']['light']);

        $variation = [
            'title' => "{$primary_name}-{$secondary_name}",
            'version' => 2,
            'settings' => [
                'color' => [
                    'palette' => [
                        // Primary colors - only accessible variations
                        [
                            'color' => $primary_colors['hex']['lighter'],
                            'name' => $primary_colors['names']['lighter'],
                            'slug' => 'primary-lighter'
                        ],
                        [
                            'color' => $primary_colors['hex']['light'],
                            'name' => $primary_colors['names']['light'],
                            'slug' => 'primary-light'
                        ],
                        [
                            'color' => $primary_colors['hex']['dark'],
                            'name' => $primary_colors['names']['dark'],
                            'slug' => 'primary-dark'
                        ],
                        [
                            'color' => $primary_colors['hex']['darker'],
                            'name' => $primary_colors['names']['darker'],
                            'slug' => 'primary-darker'
                        ],
                        // Secondary colors - only accessible variations
                        [
                            'color' => $secondary_colors['hex']['lighter'],
                            'name' => $secondary_colors['names']['lighter'],
                            'slug' => 'secondary-lighter'
                        ],
                        [
                            'color' => $secondary_colors['hex']['light'],
                            'name' => $secondary_colors['names']['light'],
                            'slug' => 'secondary-light'
                        ],
                        [
                            'color' => $secondary_colors['hex']['dark'],
                            'name' => $secondary_colors['names']['dark'],
                            'slug' => 'secondary-dark'
                        ],
                        [
                            'color' => $secondary_colors['hex']['darker'],
                            'name' => $secondary_colors['names']['darker'],
                            'slug' => 'secondary-darker'
                        ]
                    ]
                ]
            ],
            'styles' => $this->generate_default_styles()
        ];

        return $variation;
    }
} 
