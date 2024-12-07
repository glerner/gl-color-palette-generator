import { Color, PaletteAnalysis } from '../types';

/**
 * Color format validation options
 */
export interface ColorValidationOptions {
    allowRgb?: boolean;
    allowHsl?: boolean;
    requireName?: boolean;
}

/**
 * Palette validation rules
 */
export interface PaletteValidationRules {
    minColors?: number;
    maxColors?: number;
    requireContrast?: boolean;
    wcagLevel?: 'AA' | 'AAA';
    requireColorBlindnessSafe?: boolean;
    allowedThemes?: string[];
}

/**
 * Validation result interface
 */
export interface ValidationResult {
    isValid: boolean;
    errors: string[];
    warnings: string[];
}

/**
 * Color validation utilities
 */
export class ColorValidator {
    /**
     * Validates a hex color code
     */
    static validateHex(hex: string): boolean {
        const hexRegex = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
        return hexRegex.test(hex);
    }

    /**
     * Validates RGB values
     */
    static validateRgb(r: number, g: number, b: number): boolean {
        return [r, g, b].every(value =>
            Number.isInteger(value) && value >= 0 && value <= 255
        );
    }

    /**
     * Validates HSL values
     */
    static validateHsl(h: number, s: number, l: number): boolean {
        return Number.isInteger(h) && h >= 0 && h <= 360 &&
               s >= 0 && s <= 100 && l >= 0 && l <= 100;
    }

    /**
     * Validates a complete Color object
     */
    static validateColor(color: Color, options: ColorValidationOptions = {}): ValidationResult {
        const errors: string[] = [];
        const warnings: string[] = [];

        if (!this.validateHex(color.hex)) {
            errors.push(`Invalid hex color: ${color.hex}`);
        }

        if (options.allowRgb && color.rgb) {
            const { r, g, b } = color.rgb;
            if (!this.validateRgb(r, g, b)) {
                errors.push(`Invalid RGB values: r=${r}, g=${g}, b=${b}`);
            }
        }

        if (options.allowHsl && color.hsl) {
            const { h, s, l } = color.hsl;
            if (!this.validateHsl(h, s, l)) {
                errors.push(`Invalid HSL values: h=${h}, s=${s}, l=${l}`);
            }
        }

        if (options.requireName && !color.name) {
            errors.push('Color name is required but missing');
        }

        return {
            isValid: errors.length === 0,
            errors,
            warnings
        };
    }
}

/**
 * Palette validation utilities
 */
export class PaletteValidator {
    /**
     * Validates a color palette
     */
    static validatePalette(
        colors: Color[],
        rules: PaletteValidationRules = {}
    ): ValidationResult {
        const errors: string[] = [];
        const warnings: string[] = [];

        // Check number of colors
        if (rules.minColors && colors.length < rules.minColors) {
            errors.push(`Palette must have at least ${rules.minColors} colors`);
        }
        if (rules.maxColors && colors.length > rules.maxColors) {
            errors.push(`Palette must have at most ${rules.maxColors} colors`);
        }

        // Validate individual colors
        colors.forEach((color, index) => {
            const result = ColorValidator.validateColor(color);
            result.errors.forEach(error =>
                errors.push(`Color ${index + 1}: ${error}`)
            );
        });

        // Check for duplicates
        const hexCodes = colors.map(c => c.hex.toLowerCase());
        const uniqueHexCodes = new Set(hexCodes);
        if (uniqueHexCodes.size !== hexCodes.length) {
            warnings.push('Palette contains duplicate colors');
        }

        return {
            isValid: errors.length === 0,
            errors,
            warnings
        };
    }

    /**
     * Validates palette analysis results
     */
    static validateAnalysis(analysis: PaletteAnalysis): ValidationResult {
        const errors: string[] = [];
        const warnings: string[] = [];

        // Validate contrast ratio
        if (analysis.contrast.ratio < 0 || analysis.contrast.ratio > 21) {
            errors.push(`Invalid contrast ratio: ${analysis.contrast.ratio}`);
        }

        // Validate harmony score
        if (analysis.harmony.score < 0 || analysis.harmony.score > 100) {
            errors.push(`Invalid harmony score: ${analysis.harmony.score}`);
        }

        // Validate WCAG compliance
        if (!['AA', 'AAA', 'None'].includes(analysis.accessibility.wcag_compliance)) {
            errors.push(`Invalid WCAG compliance level: ${analysis.accessibility.wcag_compliance}`);
        }

        return {
            isValid: errors.length === 0,
            errors,
            warnings
        };
    }
}
