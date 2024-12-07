/**
 * Represents a color with its hex code and optional properties
 */
export interface Color {
    /**
     * Hex code of the color (e.g., "#FF0000")
     */
    hex: string;
    /**
     * RGB color values
     */
    rgb?: {
        r: number;  // Red (0-255)
        g: number;  // Green (0-255)
        b: number;  // Blue (0-255)
    };
    /**
     * HSL color values
     */
    hsl?: {
        h: number;  // Hue (0-360)
        s: number;  // Saturation (0-100)
        l: number;  // Lightness (0-100)
    };
    /**
     * Optional descriptive name of the color
     */
    name?: string;
}

/**
 * Analysis results for a color palette
 */
export interface PaletteAnalysis {
    /**
     * Contrast analysis results
     */
    contrast: {
        /**
         * Contrast ratio between colors
         */
        ratio: number;
        /**
         * Whether the palette meets WCAG AA standards
         */
        passes_aa: boolean;
        /**
         * Whether the palette meets WCAG AAA standards
         */
        passes_aaa: boolean;
    };
    /**
     * Color harmony analysis
     */
    harmony: {
        /**
         * Harmony score (0-100)
         */
        score: number;
        /**
         * Type of color harmony (e.g., "complementary", "analogous")
         */
        type: string;
    };
    /**
     * Accessibility analysis results
     */
    accessibility: {
        /**
         * WCAG compliance level ("AA", "AAA", or "None")
         */
        wcag_compliance: string;
        /**
         * Whether the palette is safe for color-blind users
         */
        color_blindness_safe: boolean;
    };
}

/**
 * Response from the palette generation API
 */
export interface PaletteResponse {
    /**
     * Generated colors in the palette
     */
    colors: Color[];
    /**
     * Unique identifier for the palette
     */
    id: string;
    /**
     * Theme used for generation
     */
    theme: string;
    /**
     * Optional analysis results
     */
    analysis?: PaletteAnalysis;
    /**
     * Optional metadata about the palette
     */
    metadata?: {
        /**
         * Creation timestamp
         */
        created_at: string;
        /**
         * Last update timestamp
         */
        updated_at: string;
        /**
         * API version used
         */
        version: string;
    };
}
