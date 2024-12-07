import React, { useState, useCallback } from 'react';
import { ColorPicker } from './ColorPicker';
import { ColorPalette } from './ColorPalette';
import { ErrorBoundary } from './ErrorBoundary';
import { Color, PaletteAnalysis, PaletteResponse } from '../types';

/**
 * Available color palette themes
 */
const THEMES = {
    modern: 'Modern & Clean',
    vintage: 'Vintage & Retro',
    nature: 'Natural & Organic',
    tech: 'Tech & Digital',
    minimal: 'Minimal & Simple',
    bold: 'Bold & Vibrant',
    pastel: 'Soft & Pastel',
    seasonal: {
        spring: 'Spring Fresh',
        summer: 'Summer Bright',
        autumn: 'Autumn Warm',
        winter: 'Winter Cool'
    }
} as const;

/**
 * Error state interface
 */
interface ErrorState {
    message: string;
    code: string;
    retry?: () => Promise<void>;
}

/**
 * Props for the PaletteGenerator component.
 */
interface PaletteGeneratorProps {
    /**
     * Callback when new palette is generated
     */
    onPaletteGenerated?: (colors: Color[]) => void;
    /**
     * Maximum number of colors in palette
     */
    maxColors?: number;
    /**
     * Default theme for generation
     */
    defaultTheme?: string;
}

/**
 * PaletteGenerator component provides an interface for generating color palettes
 * based on a base color and theme. It integrates with the WordPress REST API
 * to generate harmonious color combinations with built-in accessibility checks.
 *
 * @component
 * @example
 * ```tsx
 * <PaletteGenerator
 *   maxColors={5}
 *   defaultTheme="modern"
 *   onPaletteGenerated={(colors) => console.log('New palette:', colors)}
 * />
 * ```
 *
 * @param {PaletteGeneratorProps} props - Component properties
 * @param {Function} [props.onPaletteGenerated] - Callback when new palette is generated
 * @param {number} [props.maxColors=5] - Maximum number of colors in palette
 * @param {string} [props.defaultTheme="modern"] - Default theme for generation
 * @returns {JSX.Element} Rendered palette generator component
 */
export function PaletteGenerator({
    onPaletteGenerated,
    maxColors = 5,
    defaultTheme = 'modern'
}: PaletteGeneratorProps) {
    const [baseColor, setBaseColor] = useState<string>('#000000');
    const [theme, setTheme] = useState<string>(defaultTheme);
    const [palette, setPalette] = useState<Color[]>([]);
    const [loading, setLoading] = useState<boolean>(false);
    const [error, setError] = useState<ErrorState | null>(null);
    const [seasonalTheme, setSeasonalTheme] = useState<keyof typeof THEMES.seasonal>('spring');

    // Error handling utility
    const handleError = useCallback((error: any, retryFn?: () => Promise<void>) => {
        const message = error.response?.data?.message || error.message || 'An unexpected error occurred';
        const code = error.response?.data?.code || 'unknown_error';
        setError({ message, code, retry: retryFn });
        setLoading(false);
    }, []);

    // Retry mechanism
    const retryOperation = useCallback(async () => {
        if (error?.retry) {
            setError(null);
            await error.retry();
        }
    }, [error]);

    const generatePalette = useCallback(async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetch('/wp-json/gl-color-palette/v1/palettes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': (window as any).glCpgVars?.nonce || ''
                },
                body: JSON.stringify({
                    base_color: baseColor,
                    theme: theme === 'seasonal' ? `seasonal_${seasonalTheme}` : theme,
                    count: maxColors
                })
            });

            if (!response.ok) {
                throw new Error('Failed to generate palette');
            }

            const data: PaletteResponse = await response.json();
            setPalette(data.colors);
            onPaletteGenerated?.(data.colors);
        } catch (err) {
            handleError(err, generatePalette);
        } finally {
            setLoading(false);
        }
    }, [baseColor, theme, seasonalTheme, maxColors, onPaletteGenerated, handleError]);

    const analyzePalette = useCallback(async (paletteId: string): Promise<PaletteAnalysis> => {
        try {
            const response = await fetch(`/wp-json/gl-color-palette/v1/palettes/${paletteId}/analyze`, {
                headers: {
                    'X-WP-Nonce': (window as any).glCpgVars?.nonce || ''
                }
            });

            if (!response.ok) {
                throw new Error('Failed to analyze palette');
            }

            const analysis: PaletteAnalysis = await response.json();
            console.log('Palette Analysis:', analysis);
            return analysis;
        } catch (err) {
            console.error('Analysis error:', err);
            throw err; // Re-throw to let ColorPalette handle the error
        }
    }, []);

    const styles = {
        container: {
            padding: '20px',
            maxWidth: '800px',
            margin: '0 auto'
        },
        controls: {
            display: 'flex',
            gap: '20px',
            marginBottom: '20px',
            alignItems: 'center'
        },
        select: {
            padding: '8px',
            borderRadius: '4px',
            border: '1px solid #ddd'
        },
        button: {
            padding: '10px 20px',
            borderRadius: '4px',
            border: 'none',
            background: '#2196F3',
            color: '#fff',
            cursor: 'pointer',
            '&:hover': {
                backgroundColor: '#1976D2'
            },
            '&:disabled': {
                background: '#cccccc',
                cursor: 'not-allowed',
            },
        } as React.CSSProperties,
        error: {
            color: '#f44336',
            fontSize: '14px',
            marginTop: '10px'
        }
    };

    return (
        <ErrorBoundary>
            <div className="gl-cpg-palette-generator">
                <div className="gl-cpg-controls">
                    <ColorPicker
                        color={baseColor}
                        onChange={setBaseColor}
                        disabled={loading}
                    />

                    <div className="gl-cpg-theme-selector">
                        <select
                            value={theme}
                            onChange={(e) => setTheme(e.target.value)}
                            disabled={loading}
                        >
                            {Object.entries(THEMES).map(([key, value]) =>
                                typeof value === 'string' ? (
                                    <option key={key} value={key}>{value}</option>
                                ) : (
                                    <optgroup key={key} label={key.charAt(0).toUpperCase() + key.slice(1)}>
                                        {Object.entries(value).map(([subKey, subValue]) => (
                                            <option key={`${key}_${subKey}`} value={`${key}_${subKey}`}>
                                                {subValue}
                                            </option>
                                        ))}
                                    </optgroup>
                                )
                            )}
                        </select>
                    </div>

                    <button
                        onClick={generatePalette}
                        disabled={loading}
                        className="gl-cpg-generate-btn"
                    >
                        {loading ? 'Generating...' : 'Generate Palette'}
                    </button>
                </div>

                {error && (
                    <div className="gl-cpg-error">
                        <p>{error.message}</p>
                        {error.retry && (
                            <button onClick={retryOperation}>
                                Try Again
                            </button>
                        )}
                    </div>
                )}

                {palette.length > 0 && (
                    <ColorPalette
                        colors={palette.map(color => color.hex)}
                        onAnalyze={analyzePalette}
                    />
                )}
            </div>
        </ErrorBoundary>
    );
}
