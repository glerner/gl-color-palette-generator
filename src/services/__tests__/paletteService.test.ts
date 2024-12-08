import { PaletteService } from '../paletteService';
import { ColorUtils } from '../../utils/color';
import { beforeEach, describe, expect, it, jest } from '@jest/globals';

// Mock the fetch function
const mockFetch = jest.fn() as jest.MockedFunction<typeof fetch>;
(global as any).fetch = mockFetch;

describe('PaletteService', () => {
    let service: PaletteService;
    const mockNonce = 'test-nonce';

    beforeEach(() => {
        // Reset fetch mock
        mockFetch.mockReset();
        
        // Mock WordPress nonce
        (window as any).glCpgVars = { nonce: mockNonce };
        
        // Create new service instance
        service = new PaletteService({
            baseUrl: '/wp-json/gl-color-palette/v1',
            validateResponses: true,
            cacheResults: true,
        });
    });

    describe('AI Provider Configuration', () => {
        it('should configure AI provider', async () => {
            const config = {
                provider: 'openai' as const,
                apiKey: 'test-key',
                settings: {
                    model: 'gpt-4',
                    temperature: 0.7,
                },
            };

            mockFetch.mockResolvedValueOnce({
                ok: true,
                json: () => Promise.resolve({ success: true }),
            } as Response);

            await service.configureAIProvider(config);

            expect(mockFetch).toHaveBeenCalledWith(
                '/wp-json/gl-color-palette/v1/ai-config',
                expect.objectContaining({
                    method: 'POST',
                    body: JSON.stringify(config),
                })
            );
        });

        it('should get AI provider configuration', async () => {
            const mockConfig = {
                provider: 'openai' as const,
                apiKey: 'test-key',
                settings: {
                    model: 'gpt-4',
                },
            };

            mockFetch.mockResolvedValueOnce({
                ok: true,
                json: () => Promise.resolve(mockConfig),
            } as Response);

            const config = await service.getAIConfig();

            expect(config).toEqual(mockConfig);
            expect(mockFetch).toHaveBeenCalledWith(
                '/wp-json/gl-color-palette/v1/ai-config',
                expect.any(Object)
            );
        });
    });

    describe('Cultural Context', () => {
        it('should generate palette with cultural context', async () => {
            const mockResponse = {
                colors: [
                    { hex: '#FF0000', name: '红色' }, // Red in Chinese
                    { hex: '#00FF00', name: '绿色' }, // Green in Chinese
                    { hex: '#0000FF', name: '蓝色' }, // Blue in Chinese
                    { hex: '#FFFF00', name: '黄色' }, // Yellow in Chinese
                    { hex: '#800080', name: '紫色' }, // Purple in Chinese
                ],
            };

            mockFetch.mockResolvedValueOnce({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            } as Response);

            const result = await service.generateFromPrompt('traditional chinese', {
                count: 5,
                culturalContext: {
                    culture: 'chinese',
                    language: 'zh-CN',
                    preferences: {
                        traditional: true,
                        region: 'mainland',
                    },
                },
            });

            expect(mockFetch).toHaveBeenCalledWith(
                '/wp-json/gl-color-palette/v1/palettes/generate',
                expect.objectContaining({
                    method: 'POST',
                    body: expect.stringContaining('chinese'),
                })
            );

            expect(result).toEqual(mockResponse);
        });

        it('should respect cultural preferences in color generation', async () => {
            const mockResponse = {
                colors: [
                    { hex: '#FFD700', name: 'Golden' }, // Auspicious color in Chinese culture
                    { hex: '#FF0000', name: 'Lucky Red' },
                    { hex: '#800020', name: 'Dark Red' },
                    { hex: '#FFA500', name: 'Orange' },
                    { hex: '#FFFF00', name: 'Yellow' },
                ],
            };

            mockFetch.mockResolvedValueOnce({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            } as Response);

            await service.generateFromColor('#FFD700', {
                count: 5,
                culturalContext: {
                    culture: 'chinese',
                    language: 'zh-CN',
                    preferences: {
                        auspicious: true,
                    },
                },
            });

            expect(mockFetch).toHaveBeenCalledWith(
                '/wp-json/gl-color-palette/v1/palettes',
                expect.objectContaining({
                    method: 'POST',
                    body: expect.stringContaining('auspicious'),
                })
            );
        });
    });

    describe('generateFromPrompt', () => {
        it('should generate palette from prompt', async () => {
            const mockResponse = {
                colors: [
                    { hex: '#000000', name: 'Black' },
                    { hex: '#FFFFFF', name: 'White' },
                    { hex: '#003366', name: 'Dark Blue' },
                    { hex: '#990000', name: 'Dark Red' },
                    { hex: '#006600', name: 'Dark Green' },
                ],
            };

            mockFetch.mockResolvedValueOnce({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            } as Response);

            const result = await service.generateFromPrompt('modern and vibrant', {
                count: 5,
            });

            expect(mockFetch).toHaveBeenCalledWith(
                '/wp-json/gl-color-palette/v1/palettes/generate',
                expect.objectContaining({
                    method: 'POST',
                    headers: expect.objectContaining({
                        'X-WP-Nonce': mockNonce,
                    }),
                })
            );

            expect(result).toEqual(mockResponse);
        });

        it('should validate accessibility when required', async () => {
            mockFetch.mockResolvedValueOnce({
                ok: true,
                json: () => Promise.resolve({
                    colors: [
                        { hex: '#000000' },
                        { hex: '#003366' },
                        { hex: '#990000' },
                        { hex: '#006600' },
                        { hex: '#333333' }  
                    ]
                })
            } as Response);

            await expect(
                service.generateFromPrompt('high contrast', {
                    count: 5,
                    accessibility: {
                        level: 'AAA',
                        ensureReadability: true
                    }
                })
            ).rejects.toThrow(/have a contrast ratio of .* which does not meet WCAG AAA requirements/);
        });

        it('should throw error for invalid accessibility', async () => {
            mockFetch.mockResolvedValueOnce({
                ok: true,
                json: () => Promise.resolve({
                    colors: [
                        { hex: '#777777' },
                        { hex: '#888888' },
                        { hex: '#999999' },
                        { hex: '#AAAAAA' },
                        { hex: '#BBBBBB' }
                    ]
                })
            } as Response);

            await expect(
                service.generateFromPrompt('low contrast', {
                    count: 5,
                    accessibility: {
                        level: 'AAA',
                        ensureReadability: true
                    }
                })
            ).rejects.toThrow(/have a contrast ratio of .* which does not meet WCAG AAA requirements/);
        });
    });

    describe('generateFromColor', () => {
        it('should generate palette from base color', async () => {
            const mockResponse = {
                colors: [
                    { hex: '#FF0000', name: 'Red' },
                    { hex: '#FF5555', name: 'Light Red' },
                    { hex: '#AA0000', name: 'Dark Red' },
                    { hex: '#FF8888', name: 'Pale Red' },
                    { hex: '#550000', name: 'Deep Red' },
                ],
            };

            mockFetch.mockResolvedValueOnce({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            } as Response);

            const result = await service.generateFromColor('#FF0000', {
                count: 5,
                scheme: 'monochromatic',
            });

            expect(mockFetch).toHaveBeenCalledWith(
                '/wp-json/gl-color-palette/v1/palettes',
                expect.objectContaining({
                    method: 'POST',
                    body: expect.stringContaining('monochromatic'),
                })
            );

            expect(result).toEqual(mockResponse);
        });

        it('should throw error for invalid base color', async () => {
            await expect(
                service.generateFromColor('invalid-color')
            ).rejects.toThrow(/Invalid base color/);
        });
    });

    describe('caching', () => {
        it('should cache GET requests', async () => {
            const mockResponse = {
                themes: [
                    { id: 1, name: 'Theme 1' },
                    { id: 2, name: 'Theme 2' },
                ],
            };

            mockFetch.mockResolvedValue({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            } as Response);

            // First call
            await service.getThemes();
            // Second call should use cache
            await service.getThemes();

            expect(mockFetch).toHaveBeenCalledTimes(1);
        });

        it('should not cache POST requests', async () => {
            const mockResponse = {
                colors: [
                    { hex: '#000000', name: 'Black' },
                    { hex: '#FFFFFF', name: 'White' },
                    { hex: '#003366', name: 'Dark Blue' },
                    { hex: '#990000', name: 'Dark Red' },
                    { hex: '#006600', name: 'Dark Green' },
                ],
            };

            mockFetch.mockResolvedValue({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            } as Response);

            // Multiple calls should not use cache
            await service.generateFromPrompt('test');
            await service.generateFromPrompt('test');

            expect(mockFetch).toHaveBeenCalledTimes(2);
        });

        it('should clear cache', async () => {
            const mockResponse = { themes: [] };

            mockFetch.mockResolvedValue({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            } as Response);

            await service.getThemes();
            service.clearCache();
            await service.getThemes();

            expect(mockFetch).toHaveBeenCalledTimes(2);
        });

        it('should respect cache duration', async () => {
            const mockResponse = { themes: [] };
            jest.useFakeTimers();

            mockFetch.mockResolvedValue({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            } as Response);

            await service.getThemes();
            
            // Advance time by 6 minutes (beyond cache duration)
            jest.advanceTimersByTime(6 * 60 * 1000);
            
            await service.getThemes();

            expect(mockFetch).toHaveBeenCalledTimes(2);

            jest.useRealTimers();
        });
    });

    describe('optimizePalette', () => {
        it('should optimize palette with WCAG options', async () => {
            const mockResponse = {
                colors: [
                    { hex: '#000000', name: 'Black' },
                    { hex: '#FFFFFF', name: 'White' },
                    { hex: '#003366', name: 'Dark Blue' },
                    { hex: '#990000', name: 'Dark Red' },
                    { hex: '#006600', name: 'Dark Green' },
                ],
            };

            mockFetch.mockResolvedValueOnce({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            } as Response);

            await service.optimizePalette('test-id', {
                target: 'accessibility',
                wcagLevel: 'AAA',
            });

            expect(mockFetch).toHaveBeenCalledWith(
                '/wp-json/gl-color-palette/v1/palettes/test-id/optimize',
                expect.objectContaining({
                    method: 'POST',
                    body: expect.stringContaining('AAA'),
                })
            );
        });
    });

    describe('error handling', () => {
        it('should handle API errors', async () => {
            mockFetch.mockResolvedValueOnce({
                ok: false,
                statusText: 'Server Error',
            } as Response);

            await expect(service.getThemes()).rejects.toThrow(/API request failed/);
        });

        it('should handle network errors', async () => {
            mockFetch.mockRejectedValueOnce(new Error('Network Error'));

            await expect(service.getThemes()).rejects.toThrow('Network Error');
        });

        it('should handle malformed responses', async () => {
            mockFetch.mockResolvedValueOnce({
                ok: true,
                json: () => Promise.resolve({
                    colors: [
                        { hex: '#FF0000', name: 'Red' },
                        { hex: '#00FF00', name: 'Green' },
                    ],
                }),
            } as Response);

            await expect(service.generateFromPrompt('test')).rejects.toThrow(/Palette must have at least 5 colors/);
        });
    });
});
