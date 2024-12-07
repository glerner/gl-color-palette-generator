import { Color } from '../types';

/**
 * Color utility class for color manipulations and conversions
 */
export class ColorUtils {
    /**
     * Converts hex to RGB
     */
    static hexToRgb(hex: string): { r: number; g: number; b: number } {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        if (!result) {
            throw new Error(`Invalid hex color: ${hex}`);
        }
        return {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        };
    }

    /**
     * Converts RGB to hex
     */
    static rgbToHex(r: number, g: number, b: number): string {
        return '#' + [r, g, b]
            .map(x => {
                const hex = x.toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            })
            .join('');
    }

    /**
     * Converts RGB to HSL
     */
    static rgbToHsl(r: number, g: number, b: number): { h: number; s: number; l: number } {
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
     * Converts HSL to RGB
     */
    static hslToRgb(h: number, s: number, l: number): { r: number; g: number; b: number } {
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
     * Calculates relative luminance
     */
    static getLuminance(r: number, g: number, b: number): number {
        const [rs, gs, bs] = [r, g, b].map(c => {
            c = c / 255;
            return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
    }

    /**
     * Calculates contrast ratio between two colors
     */
    static getContrastRatio(color1: string, color2: string): number {
        const rgb1 = this.hexToRgb(color1);
        const rgb2 = this.hexToRgb(color2);

        const l1 = this.getLuminance(rgb1.r, rgb1.g, rgb1.b);
        const l2 = this.getLuminance(rgb2.r, rgb2.g, rgb2.b);

        const lighter = Math.max(l1, l2);
        const darker = Math.min(l1, l2);

        return (lighter + 0.05) / (darker + 0.05);
    }

    /**
     * Creates a complete Color object with all properties
     */
    static createColor(hex: string, name?: string): Color {
        const rgb = this.hexToRgb(hex);
        const hsl = this.rgbToHsl(rgb.r, rgb.g, rgb.b);

        return {
            hex,
            name,
            rgb,
            hsl
        };
    }

    /**
     * Checks if a color combination meets WCAG accessibility standards
     */
    static isColorAccessible(foreground: string, background: string, level: 'AA' | 'AAA' = 'AA'): boolean {
        const ratio = this.getContrastRatio(foreground, background);
        return level === 'AA' ? ratio >= 4.5 : ratio >= 7;
    }

    /**
     * Adjusts color brightness
     */
    static adjustBrightness(color: string, amount: number): string {
        const rgb = this.hexToRgb(color);
        const hsl = this.rgbToHsl(rgb.r, rgb.g, rgb.b);
        hsl.l = Math.max(0, Math.min(100, hsl.l + amount));
        const newRgb = this.hslToRgb(hsl.h, hsl.s, hsl.l);
        return this.rgbToHex(newRgb.r, newRgb.g, newRgb.b);
    }

    /**
     * Adjusts color saturation
     */
    static saturate(color: string, amount: number): string {
        const rgb = this.hexToRgb(color);
        const hsl = this.rgbToHsl(rgb.r, rgb.g, rgb.b);
        hsl.s = Math.max(0, Math.min(100, hsl.s + amount));
        const newRgb = this.hslToRgb(hsl.h, hsl.s, hsl.l);
        return this.rgbToHex(newRgb.r, newRgb.g, newRgb.b);
    }

    /**
     * Converts a color to grayscale
     */
    static grayscale(color: string): string {
        const rgb = this.hexToRgb(color);
        const gray = Math.round(0.299 * rgb.r + 0.587 * rgb.g + 0.114 * rgb.b);
        return this.rgbToHex(gray, gray, gray);
    }
}
