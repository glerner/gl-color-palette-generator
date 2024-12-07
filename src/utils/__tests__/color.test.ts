import { ColorUtils } from '../color';

describe('ColorUtils', () => {
    describe('hexToRgb', () => {
        it('converts hex to RGB correctly', () => {
            expect(ColorUtils.hexToRgb('#000000')).toEqual({ r: 0, g: 0, b: 0 });
            expect(ColorUtils.hexToRgb('#FFFFFF')).toEqual({ r: 255, g: 255, b: 255 });
            expect(ColorUtils.hexToRgb('#FF0000')).toEqual({ r: 255, g: 0, b: 0 });
        });

        it('throws error for invalid hex', () => {
            expect(() => ColorUtils.hexToRgb('invalid')).toThrow();
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
    });

    describe('hslToRgb', () => {
        it('converts HSL to RGB correctly', () => {
            expect(ColorUtils.hslToRgb(0, 0, 0)).toEqual({ r: 0, g: 0, b: 0 });
            expect(ColorUtils.hslToRgb(0, 0, 100)).toEqual({ r: 255, g: 255, b: 255 });
            expect(ColorUtils.hslToRgb(0, 100, 50)).toEqual({ r: 255, g: 0, b: 0 });
        });
    });

    describe('getLuminance', () => {
        it('calculates luminance correctly', () => {
            expect(ColorUtils.getLuminance(0, 0, 0)).toBe(0);
            expect(ColorUtils.getLuminance(255, 255, 255)).toBeCloseTo(1);
            expect(ColorUtils.getLuminance(255, 0, 0)).toBeCloseTo(0.2126);
        });
    });

    describe('getContrastRatio', () => {
        it('calculates contrast ratio correctly', () => {
            expect(ColorUtils.getContrastRatio('#000000', '#FFFFFF')).toBeCloseTo(21);
            expect(ColorUtils.getContrastRatio('#FFFFFF', '#FFFFFF')).toBeCloseTo(1);
        });

        it('throws error for invalid colors', () => {
            expect(() => ColorUtils.getContrastRatio('invalid', '#FFFFFF')).toThrow();
        });
    });

    describe('createColor', () => {
        it('creates a complete color object', () => {
            const color = ColorUtils.createColor('#FF0000', 'Red');
            expect(color).toEqual({
                hex: '#FF0000',
                name: 'Red',
                rgb: { r: 255, g: 0, b: 0 },
                hsl: { h: 0, s: 100, l: 50 }
            });
        });

        it('throws error for invalid hex', () => {
            expect(() => ColorUtils.createColor('invalid')).toThrow();
        });
    });

    describe('adjustBrightness', () => {
        it('adjusts brightness correctly', () => {
            expect(ColorUtils.adjustBrightness('#808080', 20)).not.toBe('#808080');
            expect(ColorUtils.adjustBrightness('#000000', 100)).toBe('#ffffff');
            expect(ColorUtils.adjustBrightness('#FFFFFF', -100)).toBe('#000000');
        });
    });

    describe('getComplementary', () => {
        it('generates complementary colors correctly', () => {
            expect(ColorUtils.getComplementary('#FF0000')).toMatch(/#00FF00/i);
            expect(ColorUtils.getComplementary('#00FF00')).toMatch(/#FF00FF/i);
        });

        it('throws error for invalid colors', () => {
            expect(() => ColorUtils.getComplementary('invalid')).toThrow();
        });
    });
});
