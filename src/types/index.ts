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
    /**
     * Cultural significance of the color
     */
    culturalMeaning?: {
        /**
         * Primary cultural meaning
         */
        meaning: string;
        /**
         * Associated cultural context
         */
        culture: string;
        /**
         * Traditional or modern interpretation
         */
        traditional?: boolean;
        /**
         * Specific occasions or uses
         */
        occasions?: string[];
    };
}

/**
 * Cultural context for color generation
 */
export interface CulturalContext {
    /**
     * Primary culture/region for color interpretation
     */
    culture: string;
    /**
     * Language for color names and descriptions
     */
    language: string;
    /**
     * Additional cultural preferences
     */
    preferences?: {
        /**
         * Use traditional color interpretations
         */
        traditional?: boolean;
        /**
         * Specific region within the culture
         */
        region?: string;
        /**
         * Consider colors' auspicious meanings
         */
        auspicious?: boolean;
        /**
         * Specific occasions (e.g., "wedding", "festival")
         */
        occasions?: string[];
        /**
         * Other culture-specific preferences
         */
        [key: string]: any;
    };
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
    /**
     * Cultural analysis results
     */
    cultural?: {
        /**
         * Overall cultural harmony score
         */
        harmony_score: number;
        /**
         * Cultural appropriateness by context
         */
        context_scores: {
            [context: string]: number;
        };
        /**
         * Cultural meanings and associations
         */
        meanings: {
            [color: string]: string[];
        };
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
         * Generation method used
         */
        generation_method: string;
        /**
         * Cultural context if applicable
         */
        cultural_context?: CulturalContext;
        /**
         * AI provider used if applicable
         */
        ai_provider?: string;
    };
}

/**
 * Error Boundary component props
 */
export interface ErrorBoundaryProps {
    /**
     * Child components to render
     */
    children: React.ReactNode;
    /**
     * Optional fallback component to render when error occurs
     */
    fallback?: React.ReactNode;
    /**
     * Optional error handler callback
     */
    onError?: (error: Error, errorInfo: React.ErrorInfo) => void;
}

/**
 * Error Boundary component state
 */
export interface ErrorBoundaryState {
    /**
     * Whether an error has occurred
     */
    hasError: boolean;
    /**
     * The error that occurred, if any
     */
    error?: Error;
    /**
     * Additional error information
     */
    errorInfo?: React.ErrorInfo;
}
