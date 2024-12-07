import { Color, PaletteAnalysis, PaletteResponse } from '../types';
import { ColorValidator, PaletteValidator, ValidationResult } from '../utils/validation';

/**
 * Service options interface
 */
interface PaletteServiceOptions {
    baseUrl?: string;
    validateResponses?: boolean;
}

/**
 * Service for handling palette-related API calls
 */
export class PaletteService {
    private baseUrl: string;
    private validateResponses: boolean;

    constructor(options: PaletteServiceOptions = {}) {
        this.baseUrl = options.baseUrl || '/wp-json/gl-color-palette/v1';
        this.validateResponses = options.validateResponses ?? true;
    }

    /**
     * Gets the WordPress nonce
     */
    private getNonce(): string {
        return (window as any).glCpgVars?.nonce || '';
    }

    /**
     * Makes an API request
     */
    private async request<T>(
        endpoint: string,
        options: RequestInit = {}
    ): Promise<T> {
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

        return response.json();
    }

    /**
     * Generates a new color palette
     */
    async generatePalette(
        baseColor: string,
        theme: string,
        count: number
    ): Promise<PaletteResponse> {
        // Validate input
        const colorValidation = ColorValidator.validateColor({ hex: baseColor });
        if (!colorValidation.isValid) {
            throw new Error(`Invalid base color: ${colorValidation.errors.join(', ')}`);
        }

        const response = await this.request<PaletteResponse>('/palettes', {
            method: 'POST',
            body: JSON.stringify({
                base_color: baseColor,
                theme,
                count,
            }),
        });

        // Validate response if enabled
        if (this.validateResponses) {
            const validation = PaletteValidator.validatePalette(response.colors, {
                minColors: count,
                maxColors: count,
            });

            if (!validation.isValid) {
                throw new Error(`Invalid palette response: ${validation.errors.join(', ')}`);
            }
        }

        return response;
    }

    /**
     * Analyzes a color palette
     */
    async analyzePalette(
        paletteId: string
    ): Promise<PaletteAnalysis> {
        const response = await this.request<PaletteAnalysis>(
            `/palettes/${paletteId}/analyze`
        );

        // Validate response if enabled
        if (this.validateResponses) {
            const validation = PaletteValidator.validateAnalysis(response);
            if (!validation.isValid) {
                throw new Error(`Invalid analysis response: ${validation.errors.join(', ')}`);
            }
        }

        return response;
    }

    /**
     * Exports a color palette
     */
    async exportPalette(
        colors: Color[],
        format: string,
        options: Record<string, any> = {}
    ): Promise<Blob> {
        // Validate input
        const validation = PaletteValidator.validatePalette(colors);
        if (!validation.isValid) {
            throw new Error(`Invalid palette: ${validation.errors.join(', ')}`);
        }

        const response = await fetch(`${this.baseUrl}/palettes/export`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': this.getNonce(),
            },
            body: JSON.stringify({
                colors,
                format,
                options,
            }),
        });

        if (!response.ok) {
            throw new Error('Export failed');
        }

        return response.blob();
    }
}
