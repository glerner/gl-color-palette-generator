import { ColorUtils } from '../color';

describe('ColorUtils', () => {
    describe('hexToRgb', () => {
        it('validates correct hex colors', () => {
            expect(ColorUtils.hexToRgb('#000000')).toEqual({ r: 0, g: 0, b: 0 });
            expect(ColorUtils.hexToRgb('#ffffff')).toEqual({ r: 255, g: 255, b: 255 });
            expect(ColorUtils.hexToRgb('#123abc')).toEqual({ r: 18, g: 58, b: 188 });
        });

        it('rejects invalid hex colors', () => {
            expect(() => ColorUtils.hexToRgb('000000')).toThrow();
            expect(() => ColorUtils.hexToRgb('#gggggg')).toThrow();
            expect(() => ColorUtils.hexToRgb('#12345')).toThrow();
        });
    });

    describe('rgbToHex', () => {
        it('converts RGB to hex correctly', () => {
            expect(ColorUtils.rgbToHex(0, 0, 0)).toBe('#000000');
            expect(ColorUtils.rgbToHex(255, 255, 255)).toBe('#ffffff');
            expect(ColorUtils.rgbToHex(255, 0, 0)).toBe('#ff0000');
        });

        it('handles single digit values', () => {
            expect(ColorUtils.rgbToHex(0, 1, 2)).toBe('#000102');
        });
    });

    describe('rgbToHsl', () => {
        it('converts RGB to HSL correctly', () => {
            expect(ColorUtils.rgbToHsl(0, 0, 0)).toEqual({ h: 0, s: 0, l: 0 });
            expect(ColorUtils.rgbToHsl(255, 255, 255)).toEqual({ h: 0, s: 0, l: 100 });
            expect(ColorUtils.rgbToHsl(255, 0, 0)).toEqual({ h: 0, s: 100, l: 50 });
        });

        it('handles grayscale colors', () => {
            expect(ColorUtils.rgbToHsl(128, 128, 128)).toEqual({ h: 0, s: 0, l: 50 });
        });

        it('calculates correct hue for different primary colors', () => {
            expect(ColorUtils.rgbToHsl(255, 0, 0)).toEqual({ h: 0, s: 100, l: 50 });    // Red
            expect(ColorUtils.rgbToHsl(0, 255, 0)).toEqual({ h: 120, s: 100, l: 50 });  // Green
            expect(ColorUtils.rgbToHsl(0, 0, 255)).toEqual({ h: 240, s: 100, l: 50 });  // Blue
        });
    });

    describe('hslToRgb', () => {
        it('converts HSL to RGB correctly', () => {
            expect(ColorUtils.hslToRgb(0, 0, 0)).toEqual({ r: 0, g: 0, b: 0 });
            expect(ColorUtils.hslToRgb(0, 0, 100)).toEqual({ r: 255, g: 255, b: 255 });
            expect(ColorUtils.hslToRgb(0, 100, 50)).toEqual({ r: 255, g: 0, b: 0 });
        });

        it('handles grayscale values', () => {
            expect(ColorUtils.hslToRgb(0, 0, 50)).toEqual({ r: 128, g: 128, b: 128 });
        });

        it('converts primary colors correctly', () => {
            expect(ColorUtils.hslToRgb(0, 100, 50)).toEqual({ r: 255, g: 0, b: 0 });     // Red
            expect(ColorUtils.hslToRgb(120, 100, 50)).toEqual({ r: 0, g: 255, b: 0 });   // Green
            expect(ColorUtils.hslToRgb(240, 100, 50)).toEqual({ r: 0, g: 0, b: 255 });   // Blue
        });
    });

    describe('getLuminance', () => {
        it('calculates luminance correctly', () => {
            expect(ColorUtils.getLuminance(0, 0, 0)).toBe(0);
            expect(ColorUtils.getLuminance(255, 255, 255)).toBeCloseTo(1);
            expect(ColorUtils.getLuminance(255, 0, 0)).toBeCloseTo(0.2126);
        });

        it('handles mid-range values', () => {
            expect(ColorUtils.getLuminance(128, 128, 128)).toBeCloseTo(0.21586, 5);
        });
    });

    describe('getContrastRatio', () => {
        it('calculates contrast ratio correctly', () => {
            expect(ColorUtils.getContrastRatio('#000000', '#ffffff')).toBeCloseTo(21);
            expect(ColorUtils.getContrastRatio('#ffffff', '#ffffff')).toBeCloseTo(1);
        });

        it('is symmetric', () => {
            const ratio1 = ColorUtils.getContrastRatio('#ff0000', '#00ff00');
            const ratio2 = ColorUtils.getContrastRatio('#00ff00', '#ff0000');
            expect(ratio1).toBeCloseTo(ratio2);
        });

        it('throws error for invalid colors', () => {
            expect(() => ColorUtils.getContrastRatio('invalid', '#ffffff')).toThrow();
        });
    });

    describe('isColorAccessible', () => {
        it('validates WCAG AA compliance', () => {
            expect(ColorUtils.isColorAccessible('#000000', '#ffffff', 'AA')).toBe(true);
            expect(ColorUtils.isColorAccessible('#777777', '#ffffff', 'AA')).toBe(false);
        });

        it('validates WCAG AAA compliance', () => {
            expect(ColorUtils.isColorAccessible('#000000', '#ffffff', 'AAA')).toBe(true);
            expect(ColorUtils.isColorAccessible('#666666', '#ffffff', 'AAA')).toBe(false);
        });

        it('defaults to AA level', () => {
            expect(ColorUtils.isColorAccessible('#000000', '#ffffff')).toBe(true);
        });
    });

    describe('createColor', () => {
        it('creates a complete color object', () => {
            const color = ColorUtils.createColor('#ff0000', 'Red');
            expect(color).toEqual({
                hex: '#ff0000',
                name: 'Red',
                rgb: { r: 255, g: 0, b: 0 },
                hsl: { h: 0, s: 100, l: 50 },
                isDark: false
            });
        });

        it('handles optional name parameter', () => {
            const color = ColorUtils.createColor('#ff0000');
            expect(color.name).toBeUndefined();
        });

        it('throws error for invalid hex', () => {
            expect(() => ColorUtils.createColor('invalid')).toThrow();
        });
    });

    describe('adjustBrightness', () => {
        it('adjusts brightness correctly', () => {
            expect(ColorUtils.adjustBrightness('#808080', 20)).not.toBe('#808080');
            expect(ColorUtils.adjustBrightness('#000000', 100)).toBe('#ffffff');
            expect(ColorUtils.adjustBrightness('#ffffff', -100)).toBe('#000000');
        });

        it('clamps values to valid range', () => {
            expect(ColorUtils.adjustBrightness('#ff0000', 200)).toBe('#ffffff');
            expect(ColorUtils.adjustBrightness('#ff0000', -200)).toBe('#000000');
        });
    });

    describe('saturate', () => {
        it('adjusts saturation correctly', () => {
            expect(ColorUtils.saturate('#808080', 50)).not.toBe('#808080');
            expect(ColorUtils.saturate('#ff0000', -100)).toBe('#808080');
        });

        it('clamps values to valid range', () => {
            expect(ColorUtils.saturate('#ff0000', 200)).toBe(ColorUtils.saturate('#ff0000', 100));
            expect(ColorUtils.saturate('#ff0000', -200)).toBe(ColorUtils.saturate('#ff0000', -100));
        });
    });

    describe('grayscale', () => {
        it('converts to grayscale correctly', () => {
            expect(ColorUtils.grayscale('#ff0000')).toBe('#4c4c4c');
            expect(ColorUtils.grayscale('#00ff00')).toBe('#969696');
            expect(ColorUtils.grayscale('#0000ff')).toBe('#1d1d1d');
        });

        it('maintains existing grayscale', () => {
            const gray = '#808080';
            expect(ColorUtils.grayscale(gray)).toBe(gray);
        });
    });

    describe('parseColor', () => {
        it('parses hex colors', () => {
            const color = ColorUtils.parseColor('#ff0000');
            expect(color.hex).toBe('#ff0000');
            expect(color.rgb).toEqual({ r: 255, g: 0, b: 0 });
        });

        it('parses rgb colors', () => {
            const color = ColorUtils.parseColor('rgb(255, 0, 0)');
            expect(color.hex).toBe('#ff0000');
            expect(color.rgb).toEqual({ r: 255, g: 0, b: 0 });
        });

        it('parses hsl colors', () => {
            const color = ColorUtils.parseColor('hsl(0, 100%, 50%)');
            expect(color.hex).toBe('#ff0000');
            expect(color.hsl).toEqual({ h: 0, s: 100, l: 50 });
        });

        it('throws error for invalid formats', () => {
            expect(() => ColorUtils.parseColor('invalid')).toThrow();
            expect(() => ColorUtils.parseColor('rgb(300,0,0)')).toThrow();
            expect(() => ColorUtils.parseColor('hsl(400,100%,50%)')).toThrow();
        });
    });

    describe('mix', () => {
        it('mixes colors correctly', () => {
            expect(ColorUtils.mix('#ff0000', '#0000ff')).toBe('#800080'); // Red + Blue = Purple
            expect(ColorUtils.mix('#ff0000', '#00ff00')).toBe('#808000'); // Red + Green = Yellow
        });

        it('respects weight parameter', () => {
            expect(ColorUtils.mix('#000000', '#ffffff', 0)).toBe('#ffffff');
            expect(ColorUtils.mix('#000000', '#ffffff', 1)).toBe('#000000');
            expect(ColorUtils.mix('#000000', '#ffffff', 0.5)).toBe('#808080');
        });

        it('clamps weight to valid range', () => {
            expect(ColorUtils.mix('#000000', '#ffffff', -1)).toBe('#ffffff');
            expect(ColorUtils.mix('#000000', '#ffffff', 2)).toBe('#000000');
        });
    });

    describe('isDark', () => {
        it('identifies dark colors correctly', () => {
            expect(ColorUtils.isDark('#000000')).toBe(true);
            expect(ColorUtils.isDark('#ffffff')).toBe(false);
            expect(ColorUtils.isDark('#808080')).toBe(true);
        });

        it('handles edge cases', () => {
            expect(ColorUtils.isDark('#7f7f7f')).toBe(true);
            expect(ColorUtils.isDark('#808080')).toBe(true);
            expect(ColorUtils.isDark('#818181')).toBe(false);
        });
    });

    describe('getReadableTextColor', () => {
        it('returns appropriate text colors', () => {
            expect(ColorUtils.getReadableTextColor('#000000')).toBe('#ffffff');
            expect(ColorUtils.getReadableTextColor('#ffffff')).toBe('#000000');
        });

        it('ensures sufficient contrast', () => {
            const backgrounds = ['#ff0000', '#00ff00', '#0000ff', '#808080'];
            backgrounds.forEach(bg => {
                const textColor = ColorUtils.getReadableTextColor(bg);
                expect(ColorUtils.getContrastRatio(textColor, bg)).toBeGreaterThanOrEqual(4.5);
            });
        });
    });
});
