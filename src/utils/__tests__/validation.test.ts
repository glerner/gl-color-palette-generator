import { ColorValidator, PaletteValidator } from '../validation';
import { Color, PaletteAnalysis } from '../../types';

describe('ColorValidator', () => {
    describe('validateHex', () => {
        it('validates correct hex colors', () => {
            expect(ColorValidator.validateHex('#000000')).toBe(true);
            expect(ColorValidator.validateHex('#FFFFFF')).toBe(true);
            expect(ColorValidator.validateHex('#123ABC')).toBe(true);
        });

        it('rejects invalid hex colors', () => {
            expect(ColorValidator.validateHex('000000')).toBe(false);
            expect(ColorValidator.validateHex('#GGGGGG')).toBe(false);
            expect(ColorValidator.validateHex('#12345')).toBe(false);
        });
    });

    describe('validateRgb', () => {
        it('validates correct RGB values', () => {
            expect(ColorValidator.validateRgb(0, 0, 0)).toBe(true);
            expect(ColorValidator.validateRgb(255, 255, 255)).toBe(true);
            expect(ColorValidator.validateRgb(123, 45, 67)).toBe(true);
        });

        it('rejects invalid RGB values', () => {
            expect(ColorValidator.validateRgb(-1, 0, 0)).toBe(false);
            expect(ColorValidator.validateRgb(256, 0, 0)).toBe(false);
            expect(ColorValidator.validateRgb(0, 0.5, 0)).toBe(false);
        });
    });

    describe('validateHsl', () => {
        it('validates correct HSL values', () => {
            expect(ColorValidator.validateHsl(0, 0, 0)).toBe(true);
            expect(ColorValidator.validateHsl(360, 100, 100)).toBe(true);
            expect(ColorValidator.validateHsl(180, 50, 50)).toBe(true);
        });

        it('rejects invalid HSL values', () => {
            expect(ColorValidator.validateHsl(-1, 0, 0)).toBe(false);
            expect(ColorValidator.validateHsl(361, 0, 0)).toBe(false);
            expect(ColorValidator.validateHsl(0, 101, 0)).toBe(false);
        });
    });

    describe('validateColor', () => {
        it('validates a complete color object', () => {
            const color: Color = {
                hex: '#123456',
                rgb: { r: 18, g: 52, b: 86 },
                hsl: { h: 210, s: 65, l: 20 },
                name: 'Deep Blue'
            };

            const result = ColorValidator.validateColor(color, {
                allowRgb: true,
                allowHsl: true,
                requireName: true
            });

            expect(result.isValid).toBe(true);
            expect(result.errors).toHaveLength(0);
        });

        it('reports multiple validation errors', () => {
            const color: Color = {
                hex: 'invalid',
                rgb: { r: -1, g: 300, b: 0 },
                hsl: { h: 400, s: 150, l: -10 }
            };

            const result = ColorValidator.validateColor(color, {
                allowRgb: true,
                allowHsl: true,
                requireName: true
            });

            expect(result.isValid).toBe(false);
            expect(result.errors.length).toBeGreaterThan(0);
        });
    });
});

describe('PaletteValidator', () => {
    describe('validatePalette', () => {
        it('validates a correct palette', () => {
            const palette: Color[] = [
                { hex: '#000000' },
                { hex: '#FFFFFF' },
                { hex: '#123456' }
            ];

            const result = PaletteValidator.validatePalette(palette, {
                minColors: 2,
                maxColors: 5
            });

            expect(result.isValid).toBe(true);
            expect(result.errors).toHaveLength(0);
        });

        it('detects palette size violations', () => {
            const palette: Color[] = [
                { hex: '#000000' },
                { hex: '#FFFFFF' }
            ];

            const result = PaletteValidator.validatePalette(palette, {
                minColors: 3,
                maxColors: 5
            });

            expect(result.isValid).toBe(false);
            expect(result.errors).toContain('Palette must have at least 3 colors');
        });

        it('warns about duplicate colors', () => {
            const palette: Color[] = [
                { hex: '#000000' },
                { hex: '#000000' },
                { hex: '#FFFFFF' }
            ];

            const result = PaletteValidator.validatePalette(palette);

            expect(result.warnings).toContain('Palette contains duplicate colors');
        });
    });

    describe('validateAnalysis', () => {
        it('validates correct analysis results', () => {
            const analysis: PaletteAnalysis = {
                contrast: {
                    ratio: 4.5,
                    passes_aa: true,
                    passes_aaa: false
                },
                harmony: {
                    score: 85,
                    type: 'complementary'
                },
                accessibility: {
                    wcag_compliance: 'AA',
                    color_blindness_safe: true
                }
            };

            const result = PaletteValidator.validateAnalysis(analysis);

            expect(result.isValid).toBe(true);
            expect(result.errors).toHaveLength(0);
        });

        it('detects invalid analysis values', () => {
            const analysis: PaletteAnalysis = {
                contrast: {
                    ratio: -1,
                    passes_aa: true,
                    passes_aaa: false
                },
                harmony: {
                    score: 150,
                    type: 'complementary'
                },
                accessibility: {
                    wcag_compliance: 'invalid',
                    color_blindness_safe: true
                }
            };

            const result = PaletteValidator.validateAnalysis(analysis);

            expect(result.isValid).toBe(false);
            expect(result.errors.length).toBeGreaterThan(0);
        });
    });
});
