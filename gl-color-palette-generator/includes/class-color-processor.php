<?php

class ColorProcessor {
    private $colors = [];

    public function process_colors($hex_colors) {
        foreach ($hex_colors as $hex) {
            $variations = $this->generate_variations($hex);
            $this->colors[] = $variations;
        }

        return $this->colors;
    }

    private function generate_variations($hex) {
        // Generate lighter, light, dark, darker versions
        return [
            'original' => $hex,
            'lighter' => $this->adjust_brightness($hex, 40),
            'light' => $this->adjust_brightness($hex, 20),
            'dark' => $this->adjust_brightness($hex, -20),
            'darker' => $this->adjust_brightness($hex, -40)
        ];
    }

    private function adjust_brightness($hex, $steps) {
        // Color adjustment logic here
    }
}
