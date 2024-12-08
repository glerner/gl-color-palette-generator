import React, { useCallback, useState } from 'react';
import { ColorPicker } from './ColorPicker';
import { ColorPalette } from './ColorPalette';
import { ErrorBoundary } from './ErrorBoundary';
import { PaletteAnalysis } from '../types';
import { ColorResult } from 'react-color';

interface PaletteGeneratorProps {
    onPaletteGenerated?: (colors: string[]) => void;
    maxColors?: number;
}

interface ErrorState {
    message: string;
    code: string;
    retry?: () => Promise<void>;
}

interface ThemeOption {
    value: string;
    label: string;
}

const REGULAR_THEMES: Record<string, ThemeOption> = {
    modern: { value: 'modern', label: 'Modern & Clean' },
    vintage: { value: 'vintage', label: 'Vintage & Retro' },
    nature: { value: 'nature', label: 'Natural & Organic' },
    tech: { value: 'tech', label: 'Tech & Digital' },
    minimal: { value: 'minimal', label: 'Minimal & Simple' },
    bold: { value: 'bold', label: 'Bold & Vibrant' },
    pastel: { value: 'pastel', label: 'Soft & Pastel' },
};

const SEASONAL_THEMES: Record<string, ThemeOption> = {
    spring: { value: 'seasonal_spring', label: 'Spring Fresh' },
    summer: { value: 'seasonal_summer', label: 'Summer Bright' },
    autumn: { value: 'seasonal_autumn', label: 'Autumn Warm' },
    winter: { value: 'seasonal_winter', label: 'Winter Cool' }
};

export const PaletteGenerator: React.FC<PaletteGeneratorProps> = ({
    onPaletteGenerated,
    maxColors = 5
}) => {
    const [baseColor, setBaseColor] = useState('#000000');
    const [theme, setTheme] = useState('');
    const [palette, setPalette] = useState<string[]>([]);
    const [showColorPicker, setShowColorPicker] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<ErrorState | null>(null);

    // Error handling utility
    const handleError = useCallback((error: any, retryFn?: () => Promise<void>) => {
        const message = error.response?.data?.message || error.message || 'An unexpected error occurred';
        const code = error.response?.data?.code || 'unknown_error';
        setError({ message, code, retry: retryFn });
        setLoading(false);
    }, []);

    // Generate palette
    const generatePalette = useCallback(async () => {
        try {
            setLoading(true);
            setError(null);

            const response = await fetch('/wp-json/gl-color-palette/v1/palettes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': ''
                },
                body: JSON.stringify({
                    base_color: baseColor,
                    theme: theme,
                    count: maxColors
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            setPalette(data.colors);
            onPaletteGenerated?.(data.colors);

        } catch (err) {
            handleError(err, generatePalette);
        } finally {
            setLoading(false);
        }
    }, [baseColor, theme, maxColors, onPaletteGenerated, handleError]);

    // Analyze palette
    const analyzePalette = useCallback(async (colors: string[]) => {
        try {
            const response = await fetch('/wp-json/gl-color-palette/v1/analyze', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': ''
                },
                body: JSON.stringify({ colors })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (err) {
            handleError(err);
            throw err;
        }
    }, [handleError]);

    const handleColorChange = (color: ColorResult) => {
        setBaseColor(color.hex);
    };

    return (
        <ErrorBoundary>
            <div className="gl-cpg-palette-generator">
                <div className="gl-cpg-controls">
                    <div style={{ display: 'inline-flex', flexDirection: 'column', gap: '8px' }}>
                        <div style={{ display: 'inline-block', position: 'relative' }}>
                            <div
                                role="button"
                                aria-label="Base color"
                                aria-disabled={false}
                                tabIndex={0}
                                style={{
                                    width: '36px',
                                    height: '36px',
                                    borderRadius: '4px',
                                    border: '2px solid #e0e0e0',
                                    background: baseColor,
                                    cursor: 'pointer',
                                    transition: 'border-color 0.2s ease',
                                }}
                                onClick={() => setShowColorPicker(!showColorPicker)}
                            />
                            {showColorPicker && (
                                <div style={{ position: 'absolute', zIndex: 2 }}>
                                    <ColorPicker
                                        color={baseColor}
                                        onChange={(newColor) => setBaseColor(newColor)}
                                    />
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="gl-cpg-theme-selector">
                        <select
                            value={theme}
                            onChange={(e) => setTheme(e.target.value)}
                            aria-label="Color scheme"
                        >
                            <option value="">Select a theme</option>
                            {Object.entries(REGULAR_THEMES).map(([key, theme]) => (
                                <option key={key} value={theme.value}>
                                    {theme.label}
                                </option>
                            ))}
                            <optgroup label="Seasonal">
                                {Object.entries(SEASONAL_THEMES).map(([key, theme]) => (
                                    <option key={key} value={theme.value}>
                                        {theme.label}
                                    </option>
                                ))}
                            </optgroup>
                        </select>
                    </div>

                    <button
                        className="gl-cpg-generate-btn"
                        onClick={generatePalette}
                        disabled={loading}
                        aria-busy={loading}
                    >
                        {loading ? 'Generating...' : 'Generate Palette'}
                    </button>
                </div>

                {error && (
                    <div className="gl-cpg-error" role="alert">
                        <p>{error.message}</p>
                        {error.retry && (
                            <button onClick={() => {
                                if (error.retry) {
                                    error.retry();
                                }
                            }}>Try Again</button>
                        )}
                    </div>
                )}

                {palette.length > 0 && (
                    <ColorPalette
                        colors={palette}
                        onAnalyze={analyzePalette}
                    />
                )}
            </div>
        </ErrorBoundary>
    );
};

export default PaletteGenerator;
