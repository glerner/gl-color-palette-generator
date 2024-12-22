import { Color, PaletteAnalysis, PaletteResponse } from '../types';
import { ColorUtils } from '../utils/color';
import { ColorValidator, PaletteValidator, ValidationResult } from '../utils/validation';

/**
 * Service options interface
 * @interface PaletteServiceOptions
 */
interface PaletteServiceOptions {
    /** Base URL for the WordPress REST API */
    baseUrl?: string;
    /** Whether to validate API responses */
    validateResponses?: boolean;
    /** Whether to cache GET requests */
    cacheResults?: boolean;
}

/**
 * AI provider configuration
 * @interface AIProviderConfig
 */
interface AIProviderConfig {
    /** Name of the AI provider */
    provider: 'openai' | 'anthropic' | 'palm' | 'local';
    /** API key for the provider */
    apiKey?: string;
    /** Additional provider-specific settings */
    settings?: Record<string, any>;
}

/**
 * Palette generation options
 * @interface GenerateOptions
 */
interface GenerateOptions {
    /** Theme for the palette */
    theme?: string;
    /** Number of colors in the palette */
    count?: number;
    /** Base color in hex format */
    baseColor?: string;
    /** Text prompt for AI generation */
    prompt?: string;
    /** Color scheme type */
    scheme?: 'analogous' | 'complementary' | 'triadic' | 'tetradic' | 'monochromatic';
    /** Whether to include palette analysis */
    includeAnalysis?: boolean;
    /** Accessibility requirements */
    accessibility?: {
        /** WCAG level to enforce */
        level: 'AA' | 'AAA';
        /** Whether to ensure text readability */
        ensureReadability?: boolean;
        /** Whether the text is large (≥18pt or 14pt bold) */
        isLargeText?: boolean;
    };
    /** Cultural context for color naming */
    culturalContext?: {
        /** Primary culture/region */
        culture: string;
        /** Language for color names */
        language: string;
        /** Additional cultural preferences */
        preferences?: Record<string, any>;
    };
}

/**
 * Service for handling palette-related API calls to WordPress backend
 * Provides integration with AI color services and WordPress theme system
 */
export class PaletteService {
    private baseUrl: string;
    private validateResponses: boolean;
    private cacheResults: boolean;
    private cache: Map<string, { data: any; timestamp: number }>;
    private readonly CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

    constructor(options: PaletteServiceOptions = {}) {
        this.baseUrl = options.baseUrl || '/wp-json/gl-color-palette/v1';
        this.validateResponses = options.validateResponses ?? true;
        this.cacheResults = options.cacheResults ?? true;
        this.cache = new Map();
    }

    /**
     * Gets the WordPress nonce for authentication
     * @private
     * @returns {string} WordPress nonce
     */
    private getNonce(): string {
        return (window as any).glCpgVars?.nonce || '';
    }

    /**
     * Makes an authenticated API request to WordPress
     * @private
     * @param {string} endpoint - API endpoint
     * @param {RequestInit} options - Fetch options
     * @returns {Promise<T>} API response
     */
    private async request<T>(
        endpoint: string,
        options: RequestInit = {}
    ): Promise<T> {
        // Check cache if enabled and method is GET
        const cacheKey = `${endpoint}${options.body || ''}`;
        if (this.cacheResults && (!options.method || options.method === 'GET')) {
            const cached = this.cache.get(cacheKey);
            if (cached && Date.now() - cached.timestamp < this.CACHE_DURATION) {
                return cached.data as T;
            }
        }

        const response = await fetch(`${this.baseUrl}${endpoint}`, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': this.getNonce(),
                ...options.headers,
            },
        });

        if (!response.ok) {
            throw new Error(`API request failed: ${response.statusText}`);
        }

        const data = await response.json();

        // Cache the result if enabled and method is GET
        if (this.cacheResults && (!options.method || options.method === 'GET')) {
            this.cache.set(cacheKey, { data, timestamp: Date.now() });
        }

        return data;
    }

    /**
     * Generates a new color palette using AI
     * @param {string} prompt - Text description for desired palette
     * @param {Partial<GenerateOptions>} options - Generation options
     * @returns {Promise<PaletteResponse>} Generated palette
     */
    async generateFromPrompt(
        prompt: string,
        options: Partial<GenerateOptions> = {}
    ): Promise<PaletteResponse> {
        const response = await this.request<PaletteResponse>('/palettes/generate', {
            method: 'POST',
            body: JSON.stringify({
                prompt,
                theme: options.theme,
                count: options.count || 5,
                include_analysis: options.includeAnalysis ?? true,
                accessibility: options.accessibility,
                cultural_context: options.culturalContext,
            }),
        });

        if (this.validateResponses) {
            const validation = PaletteValidator.validatePalette(response.colors, {
                minColors: options.count || 5,
                maxColors: options.count || 5,
            });

            if (!validation.isValid) {
                throw new Error(`Invalid palette response: ${validation.errors.join(', ')}`);
            }

            // Validate accessibility if required
            if (options.accessibility?.ensureReadability) {
                this.validateAccessibility(response.colors, options.accessibility.level, options.accessibility.isLargeText);
            }
        }

        return response;
    }

    /**
     * Generates a new color palette based on a base color
     * @param {string} baseColor - Starting color in hex format
     * @param {Partial<GenerateOptions>} options - Generation options
     * @returns {Promise<PaletteResponse>} Generated palette
     */
    async generateFromColor(
        baseColor: string,
        options: Partial<GenerateOptions> = {}
    ): Promise<PaletteResponse> {
        // Validate input color
        const colorValidation = ColorValidator.validateColor({ hex: baseColor });
        if (!colorValidation.isValid) {
            throw new Error(`Invalid base color: ${colorValidation.errors.join(', ')}`);
        }

        const response = await this.request<PaletteResponse>('/palettes', {
            method: 'POST',
            body: JSON.stringify({
                base_color: baseColor,
                theme: options.theme,
                count: options.count || 5,
                scheme: options.scheme,
                include_analysis: options.includeAnalysis ?? true,
                accessibility: options.accessibility,
                cultural_context: options.culturalContext,
            }),
        });

        if (this.validateResponses) {
            const validation = PaletteValidator.validatePalette(response.colors, {
                minColors: options.count || 5,
                maxColors: options.count || 5,
            });

            if (!validation.isValid) {
                throw new Error(`Invalid palette response: ${validation.errors.join(', ')}`);
            }

            // Validate accessibility if required
            if (options.accessibility?.ensureReadability) {
                this.validateAccessibility(response.colors, options.accessibility.level, options.accessibility.isLargeText);
            }
        }

        return response;
    }

    /**
     * Validates accessibility of a color palette
     * @private
     * @param {Color[]} colors - Colors to validate
     * @param {('AA'|'AAA')} level - WCAG level
     * @param {boolean} isLargeText - Whether the text is large (≥18pt or 14pt bold)
     */
    private validateAccessibility(
        colors: Color[], 
        level: 'AA' | 'AAA' = 'AA',
        isLargeText: boolean = false
    ): void {
        const requiredRatio = isLargeText
            ? (level === 'AAA' ? ColorUtils.CONTRAST_THRESHOLD_AAA_LARGE : ColorUtils.CONTRAST_THRESHOLD_LARGE)
            : (level === 'AAA' ? ColorUtils.CONTRAST_THRESHOLD_AAA : ColorUtils.CONTRAST_THRESHOLD_MIN);

        let validationErrors: string[] = [];

        for (let i = 0; i < colors.length; i++) {
            for (let j = i + 1; j < colors.length; j++) {
                const contrastRatio = ColorUtils.getContrastRatio(
                    colors[i].hex,
                    colors[j].hex
                );
                
                if (contrastRatio < requiredRatio) {
                    validationErrors.push(
                        `Colors ${colors[i].hex} and ${colors[j].hex} have a contrast ratio of ${contrastRatio.toFixed(2)}, which does not meet WCAG ${level} requirements (${requiredRatio}:1)`
                    );
                }
            }
        }

        if (validationErrors.length > 0) {
            throw new Error(validationErrors.join('\n'));
        }
    }

    /**
     * Configures the AI provider for palette generation
     * @param {AIProviderConfig} config - Provider configuration
     * @returns {Promise<void>}
     */
    async configureAIProvider(config: AIProviderConfig): Promise<void> {
        await this.request('/ai-config', {
            method: 'POST',
            body: JSON.stringify(config),
        });
    }

    /**
     * Gets the current AI provider configuration
     * @returns {Promise<AIProviderConfig>} Current configuration
     */
    async getAIConfig(): Promise<AIProviderConfig> {
        return this.request<AIProviderConfig>('/ai-config');
    }

    /**
     * Analyzes a color palette for accessibility and harmony
     * @param {string} paletteId - Unique identifier of the palette
     * @returns {Promise<PaletteAnalysis>} Palette analysis
     */
    async analyzePalette(paletteId: string): Promise<PaletteAnalysis> {
        return this.request<PaletteAnalysis>(`/palettes/${paletteId}/analyze`);
    }

    /**
     * Optimizes a palette for better accessibility and harmony
     * @param {string} paletteId - Unique identifier of the palette
     * @param {object} options - Optimization options
     * @returns {Promise<PaletteResponse>} Optimized palette
     */
    async optimizePalette(
        paletteId: string,
        options: {
            target?: 'accessibility' | 'harmony' | 'both';
            strictness?: 'loose' | 'normal' | 'strict';
            wcagLevel?: 'AA' | 'AAA';
        } = {}
    ): Promise<PaletteResponse> {
        return this.request<PaletteResponse>(`/palettes/${paletteId}/optimize`, {
            method: 'POST',
            body: JSON.stringify({
                ...options,
                wcag_level: options.wcagLevel || 'AA',
            }),
        });
    }

    /**
     * Exports a palette in various formats
     * @param {string} paletteId - Unique identifier of the palette
     * @param {string} format - Export format
     * @returns {Promise<string>} Exported palette
     */
    async exportPalette(
        paletteId: string,
        format: 'css' | 'scss' | 'json' | 'wordpress'
    ): Promise<string> {
        return this.request<string>(`/palettes/${paletteId}/export`, {
            method: 'POST',
            body: JSON.stringify({ format }),
        });
    }

    /**
     * Gets available themes for palette generation
     * @returns {Promise<Array<{id: string; name: string; description: string}>>} Available themes
     */
    async getThemes(): Promise<Array<{ id: string; name: string; description: string }>> {
        return this.request<Array<{ id: string; name: string; description: string }>>(
            '/themes'
        );
    }

    /**
     * Gets color scheme suggestions based on current WordPress theme
     * @returns {Promise<Array<Color>>} Suggested colors
     */
    async getThemeSuggestions(): Promise<Array<Color>> {
        return this.request<Array<Color>>('/theme-suggestions');
    }

    /**
     * Applies a palette to the current WordPress theme
     * @param {string} paletteId - Unique identifier of the palette
     * @returns {Promise<void>}
     */
    async applyToTheme(paletteId: string): Promise<void> {
        await this.request(`/palettes/${paletteId}/apply-theme`, {
            method: 'POST',
        });
    }

    /**
     * Clears the service cache
     */
    clearCache(): void {
        this.cache.clear();
    }
}
