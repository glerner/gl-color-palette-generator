<?php

namespace GLColorPalette;

class ColorProcessor {
    /**
     * Process a color value into standardized format
     */
    public function process($color) {
        if ($this->is_hex($color)) {
            return $this->process_hex($color);
        } elseif ($this->is_rgb($color)) {
            return $this->process_rgb($color);
        } elseif ($this->is_hsl($color)) {
            return $this->process_hsl($color);
        }

        throw new Exception("Invalid color format: $color");
    }

    /**
     * Convert color to different formats
     */
    public function convert($color, $to_format = 'hex') {
        $processed = $this->process($color);

        switch ($to_format) {
            case 'hex':
                return $this->to_hex($processed);
            case 'rgb':
                return $this->to_rgb($processed);
            case 'hsl':
                return $this->to_hsl($processed);
            default:
                throw new Exception("Unsupported format: $to_format");
        }
    }

    // ... other color processing methods ...
}
