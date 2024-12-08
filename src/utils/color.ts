import { Color } from '../types';

/**
 * Color utility class for color manipulations and conversions.
 * Provides a comprehensive set of methods for working with colors in different formats (HEX, RGB, HSL),
 * performing color calculations, and ensuring accessibility compliance.
 */
export interface RGBColor {
    r: number;
    g: number;
    b: number;
}

export interface HSLColor {
    h: number;
    s: number;
    l: number;
}

export interface ParsedColor {
    hex: string;
    rgb: RGBColor;
    hsl: HSLColor;
}

export class ColorUtils {
    /**
     * Convert hex color to RGB
     * @param hex - The hexadecimal color string (e.g., "#FF0000" or "#F00")
     * @returns Object containing RGB values (0-255)
     * @throws Error if the hex color format is invalid
     */
    static hexToRgb(hex: string): RGBColor {
        // Ensure hex starts with #
        if (!hex.startsWith('#')) {
            throw new Error('Invalid hex color: must start with #');
        }

        // Remove # and validate length
        const cleanHex = hex.substring(1);
        if (![3, 6].includes(cleanHex.length)) {
            throw new Error('Invalid hex color length');
        }

        // Validate hex characters
        if (!/^[0-9A-Fa-f]+$/.test(cleanHex)) {
            throw new Error('Invalid hex color characters');
        }

        // Convert 3-digit hex to 6-digit
        const fullHex = cleanHex.length === 3
            ? cleanHex.split('').map(c => c + c).join('')
            : cleanHex;

        const r = parseInt(fullHex.substring(0, 2), 16);
        const g = parseInt(fullHex.substring(2, 4), 16);
        const b = parseInt(fullHex.substring(4, 6), 16);

        return { r, g, b };
    }

    /**
     * Convert RGB values to hex color
     * @param r - Red component (0-255)
     * @param g - Green component (0-255)
     * @param b - Blue component (0-255)
     * @returns Hex color string (e.g., "#FF0000")
     */
    static rgbToHex(r: number, g: number, b: number): string {
        const toHex = (n: number) => {
            const hex = Math.max(0, Math.min(255, Math.round(n))).toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        };
        return `#${toHex(r)}${toHex(g)}${toHex(b)}`.toLowerCase();
    }

    /**
     * Convert RGB values to HSL color space.
     * @param r - Red component (0-255)
     * @param g - Green component (0-255)
     * @param b - Blue component (0-255)
     * @returns Object containing HSL values (h: 0-360, s: 0-100, l: 0-100)
     */
    static rgbToHsl(r: number, g: number, b: number): HSLColor {
        r /= 255;
        g /= 255;
        b /= 255;

        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        let h = 0;
        let s = 0;
        const l = (max + min) / 2;

        if (max !== min) {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

            switch (max) {
                case r:
                    h = (g - b) / d + (g < b ? 6 : 0);
                    break;
                case g:
                    h = (b - r) / d + 2;
                    break;
                case b:
                    h = (r - g) / d + 4;
                    break;
            }

            h /= 6;
        }

        return {
            h: Math.round(h * 360),
            s: Math.round(s * 100),
            l: Math.round(l * 100)
        };
    }

    /**
     * Convert HSL values to RGB color space.
     * @param h - Hue (0-360)
     * @param s - Saturation (0-100)
     * @param l - Lightness (0-100)
     * @returns Object containing RGB values (0-255)
     */
    static hslToRgb(h: number, s: number, l: number): RGBColor {
        h /= 360;
        s /= 100;
        l /= 100;

        let r: number, g: number, b: number;

        if (s === 0) {
            r = g = b = l;
        } else {
            const hue2rgb = (p: number, q: number, t: number) => {
                if (t < 0) t += 1;
                if (t > 1) t -= 1;
                if (t < 1/6) return p + (q - p) * 6 * t;
                if (t < 1/2) return q;
                if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                return p;
            };

            const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            const p = 2 * l - q;

            r = hue2rgb(p, q, h + 1/3);
            g = hue2rgb(p, q, h);
            b = hue2rgb(p, q, h - 1/3);
        }

        return {
            r: Math.round(r * 255),
            g: Math.round(g * 255),
            b: Math.round(b * 255)
        };
    }

    /**
     * Calculate relative luminance of a color
     * Uses the formula from WCAG 2.0
     * @param r - Red component (0-255)
     * @param g - Green component (0-255)
     * @param b - Blue component (0-255)
     * @returns Relative luminance value (0-1)
     */
    static getLuminance(r: number, g: number, b: number): number {
        const [rs, gs, bs] = [r, g, b].map(c => {
            c = c / 255;
            return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
    }

    /**
     * Calculate relative luminance of a hex color
     * @param color - Color in hex format
     * @returns Relative luminance value (0-1)
     */
    private static getLuminanceFromHex(color: string): number {
        const rgb = this.hexToRgb(color);
        return this.getLuminance(rgb.r, rgb.g, rgb.b);
    }

    /**
     * Calculate contrast ratio between two colors according to WCAG 2.0
     * @param color1 - First color in hex format
     * @param color2 - Second color in hex format
     * @returns Contrast ratio (1-21)
     */
    static getContrastRatio(color1: string, color2: string): number {
        const l1 = this.getLuminanceFromHex(color1);
        const l2 = this.getLuminanceFromHex(color2);
        const lighter = Math.max(l1, l2);
        const darker = Math.min(l1, l2);
        return (lighter + 0.05) / (darker + 0.05);
    }

    /**
     * Check if colors meet WCAG accessibility standards
     * @param color1 - First color in hex format
     * @param color2 - Second color in hex format
     * @param level - WCAG compliance level ('AA' or 'AAA')
     * @returns boolean indicating if the combination is accessible
     */
    static isColorAccessible(color1: string, color2: string, level: 'AA' | 'AAA' = 'AA'): boolean {
        const contrastRatio = this.getContrastRatio(color1, color2);
        // Using exact WCAG 2.0 thresholds
        // For normal text:
        // - WCAG AA requires 4.5:1
        // - WCAG AAA requires 7.0:1
        const threshold = level === 'AAA' ? 7.0 : 4.5;
        return contrastRatio >= threshold;
    }

    /**
     * Check if a color is dark based on luminance and RGB values
     * @param color - Color in hex format
     * @returns boolean indicating if the color is dark
     */
    static isDark(color: string): boolean {
        const rgb = this.hexToRgb(color);
        
        // For grayscale colors, use specific thresholds
        if (rgb.r === rgb.g && rgb.g === rgb.b) {
            return rgb.r <= 128; // #808080 and below are dark
        }
        
        // For pure primary colors or very bright colors, consider them light
        const BRIGHT_THRESHOLD = 240; // f0 in hex
        if (
            // Pure primary colors
            (rgb.r === 255 && rgb.g === 0 && rgb.b === 0) || // pure red
            (rgb.r === 0 && rgb.g === 255 && rgb.b === 0) || // pure green
            (rgb.r === 0 && rgb.g === 0 && rgb.b === 255) || // pure blue
            // Very bright colors (any component >= f0)
            rgb.r >= BRIGHT_THRESHOLD ||
            rgb.g >= BRIGHT_THRESHOLD ||
            rgb.b >= BRIGHT_THRESHOLD
        ) {
            return false;
        }
        
        // For all other colors, use luminance
        const luminance = this.getLuminanceFromHex(color);
        return luminance < 0.5;
    }

    /**
     * Parse color string in various formats (hex, rgb, hsl)
     * @param color - Color string in hex, rgb(), or hsl() format
     * @returns ParsedColor object with RGB, hex, and HSL values
     * @throws Error if color format is invalid
     */
    static parseColor(color: string): ParsedColor {
        let rgb: RGBColor;

        // Handle hex colors
        if (color.startsWith('#')) {
            rgb = this.hexToRgb(color);
            return {
                hex: color.toLowerCase(),
                rgb,
                hsl: this.rgbToHsl(rgb.r, rgb.g, rgb.b)
            };
        }

        // Handle rgb/rgba
        const rgbMatch = color.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)/);
        if (rgbMatch) {
            const [_, r, g, b] = rgbMatch.map(Number);
            if (r > 255 || g > 255 || b > 255) {
                throw new Error('RGB values must be between 0 and 255');
            }
            rgb = { r, g, b };
            return {
                hex: this.rgbToHex(r, g, b),
                rgb,
                hsl: this.rgbToHsl(r, g, b)
            };
        }

        // Handle hsl/hsla
        const hslMatch = color.match(/^hsla?\((\d+),\s*(\d+)%,\s*(\d+)%/);
        if (hslMatch) {
            const [_, h, s, l] = hslMatch.map(Number);
            if (h >= 360 || s > 100 || l > 100) {
                throw new Error('Invalid HSL values');
            }
            rgb = this.hslToRgb(h, s, l);
            return {
                hex: this.rgbToHex(rgb.r, rgb.g, rgb.b),
                rgb,
                hsl: { h, s, l }
            };
        }

        throw new Error('Invalid color format');
    }

    /**
     * Returns the most readable text color (black or white) for a given background color
     * @param backgroundColor Background color in hex format
     * @returns Appropriate text color (#000000 or #ffffff)
     */
    static getReadableTextColor(backgroundColor: string): string {
        const white = '#ffffff';
        const black = '#000000';
        const whiteContrast = this.getContrastRatio(white, backgroundColor);
        const blackContrast = this.getContrastRatio(black, backgroundColor);
        return whiteContrast > blackContrast ? white : black;
    }

    /**
     * Mix two colors together
     * @param color1 - First color in hex format
     * @param color2 - Second color in hex format
     * @param weight - Weight of the first color (0-1)
     * @returns Mixed color in hex format
     */
    static mix(color1: string, color2: string, weight: number = 0.5): string {
        const rgb1 = this.hexToRgb(color1);
        const rgb2 = this.hexToRgb(color2);

        const w = Math.max(0, Math.min(1, weight));
        const w2 = 1 - w;

        const r = Math.round(rgb1.r * w + rgb2.r * w2);
        const g = Math.round(rgb1.g * w + rgb2.g * w2);
        const b = Math.round(rgb1.b * w + rgb2.b * w2);

        return this.rgbToHex(r, g, b);
    }

    /**
     * Adjust color brightness
     * @param color - Color in hex format
     * @param amount - Amount to adjust (-100 to 100)
     * @returns Adjusted color in hex format
     */
    static adjustBrightness(color: string, amount: number): string {
        const rgb = this.hexToRgb(color);
        const factor = 1 + Math.max(-1, Math.min(1, amount / 100));

        if (amount >= 100) return '#ffffff';
        if (amount <= -100) return '#000000';

        const r = Math.min(255, Math.max(0, Math.round(rgb.r * factor)));
        const g = Math.min(255, Math.max(0, Math.round(rgb.g * factor)));
        const b = Math.min(255, Math.max(0, Math.round(rgb.b * factor)));

        return this.rgbToHex(r, g, b);
    }

    /**
     * Adjust color saturation
     * @param color - Color in hex format
     * @param amount - Amount to adjust (-100 to 100)
     * @returns Adjusted color in hex format
     */
    static saturate(color: string, amount: number): string {
        const rgb = this.hexToRgb(color);
        const hsl = this.rgbToHsl(rgb.r, rgb.g, rgb.b);
        hsl.s = Math.max(0, Math.min(100, hsl.s + amount));
        const newRgb = this.hslToRgb(hsl.h, hsl.s, hsl.l);
        return this.rgbToHex(newRgb.r, newRgb.g, newRgb.b);
    }

    /**
     * Convert color to grayscale
     * @param color - Color in hex format
     * @returns Grayscale color in hex format
     */
    static grayscale(color: string): string {
        const rgb = this.hexToRgb(color);
        const gray = Math.round(0.299 * rgb.r + 0.587 * rgb.g + 0.114 * rgb.b);
        return this.rgbToHex(gray, gray, gray);
    }

    /**
     * Creates a complete Color object with all properties.
     * @param hex - Hexadecimal color string
     * @param name - Optional color name
     * @returns Complete color object with all properties
     */
    static createColor(hex: string, name?: string): { hex: string, name?: string, rgb: RGBColor, hsl: HSLColor, isDark: boolean } {
        const rgb = this.hexToRgb(hex);
        const hsl = this.rgbToHsl(rgb.r, rgb.g, rgb.b);
        return {
            hex: hex.toLowerCase(),
            name,
            rgb,
            hsl,
            isDark: this.isDark(hex)
        };
    }
}
