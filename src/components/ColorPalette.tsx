import React, { useState } from 'react';
import { ColorPicker } from './ColorPicker';
import { ErrorBoundary } from './ErrorBoundary';
import { Color, PaletteAnalysis } from '../types';

interface ExportFormat {
    id: string;
    name: string;
    extension: string;
}

const EXPORT_FORMATS: ExportFormat[] = [
    { id: 'css', name: 'CSS Variables', extension: 'css' },
    { id: 'scss', name: 'SCSS Variables', extension: 'scss' },
    { id: 'json', name: 'JSON', extension: 'json' },
    { id: 'theme.json', name: 'WordPress Theme', extension: 'json' }
];

interface ColorPaletteProps {
    colors: string[];
    onChange?: (colors: string[]) => void;
    onColorClick?: (color: string, index: number) => void;
    onAnalyze?: (paletteId: string) => Promise<PaletteAnalysis>;
    className?: string;
    readonly?: boolean;
}

/**
 * ColorPalette component displays a collection of color swatches that can be interactive
 * or readonly. It supports both controlled and uncontrolled color selection.
 *
 * @param {ColorPaletteProps} props - Component props
 * @returns {JSX.Element} Rendered color palette
 */
export function ColorPalette({
    colors,
    onChange,
    onColorClick,
    onAnalyze,
    className = '',
    readonly = false
}: ColorPaletteProps) {
    const [analyzing, setAnalyzing] = useState(false);
    const [analysis, setAnalysis] = useState<PaletteAnalysis | null>(null);
    const [exportFormat, setExportFormat] = useState<string>('css');
    const [exportLoading, setExportLoading] = useState(false);

    const handleAnalyze = async () => {
        if (!onAnalyze) return;

        setAnalyzing(true);
        try {
            const result = await onAnalyze(colors.join(','));
            setAnalysis(result);
        } catch (error) {
            console.error('Analysis failed:', error);
        } finally {
            setAnalyzing(false);
        }
    };

    const handleExport = async () => {
        setExportLoading(true);
        try {
            const response = await fetch('/wp-json/gl-color-palette/v1/palettes/export', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': (window as any).glCpgVars?.nonce || ''
                },
                body: JSON.stringify({
                    colors,
                    format: exportFormat,
                    options: {
                        variable_prefix: '--gl-color',
                        include_metadata: true,
                        naming_convention: 'kebab',
                        minify: false
                    }
                })
            });

            if (!response.ok) {
                throw new Error('Export failed');
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `palette.${EXPORT_FORMATS.find(f => f.id === exportFormat)?.extension || 'txt'}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } catch (error) {
            console.error('Export failed:', error);
        } finally {
            setExportLoading(false);
        }
    };

    const handleColorChange = (newColor: string, index: number) => {
        if (onChange && !readonly) {
            const newColors = [...colors];
            newColors[index] = newColor;
            onChange(newColors);
        }
    };

    const handleColorClick = (color: string, index: number) => {
        if (onColorClick) {
            onColorClick(color, index);
        }
    };

    // Validate colors array
    if (!Array.isArray(colors) || colors.length === 0) {
        throw new Error('ColorPalette requires a non-empty array of colors');
    }

    const styles: Record<string, React.CSSProperties> = {
        wrapper: {
            display: 'flex',
            flexWrap: 'wrap',
            gap: '16px',
            padding: '16px',
            background: '#FFFFFF',
            borderRadius: '8px',
            boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
        },
        colorBox: {
            width: '80px',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            gap: '8px',
        },
        colorSwatch: {
            width: '80px',
            height: '80px',
            borderRadius: '8px',
            border: '2px solid #E0E0E0',
            cursor: readonly ? 'pointer' : 'default',
            transition: 'transform 0.2s ease, box-shadow 0.2s ease',
        },
        colorHex: {
            fontSize: '14px',
            fontFamily: 'monospace',
            color: '#666',
            userSelect: 'all',
        },
    };

    return (
        <ErrorBoundary>
            <div
                className={`color-palette ${className}`}
                role="group"
                aria-label="Color Palette"
                style={styles.wrapper}
            >
                {colors.map((color, index) => (
                    <div
                        key={`${color}-${index}`}
                        className="color-box"
                        style={styles.colorBox}
                    >
                        {readonly ? (
                            <div
                                style={{
                                    ...styles.colorSwatch,
                                    background: color,
                                }}
                                onClick={() => handleColorClick(color, index)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter' || e.key === ' ') {
                                        handleColorClick(color, index);
                                    }
                                }}
                                role="button"
                                tabIndex={0}
                                aria-label={`Color ${color}`}
                            />
                        ) : (
                            <ColorPicker
                                color={color}
                                onChange={(newColor) => handleColorChange(newColor, index)}
                                onClick={() => handleColorClick(color, index)}
                                disabled={readonly}
                                ariaLabel={`Color ${index + 1}: ${color}`}
                            />
                        )}
                        <span
                            className="color-value"
                            aria-label={`Color value: ${color}`}
                            style={styles.colorHex}
                        >
                            {color}
                        </span>
                        {analysis && (
                            <div className="gl-cpg-color-analysis">
                                <span className={`gl-cpg-wcag ${analysis.contrast.passes_aa ? 'pass' : 'fail'}`}>
                                    AA {analysis.contrast.passes_aa ? '✓' : '✗'}
                                </span>
                                <span className={`gl-cpg-wcag ${analysis.contrast.passes_aaa ? 'pass' : 'fail'}`}>
                                    AAA {analysis.contrast.passes_aaa ? '✓' : '✗'}
                                </span>
                            </div>
                        )}
                    </div>
                ))}
            </div>

            <div className="gl-cpg-actions">
                <button
                    onClick={handleAnalyze}
                    disabled={analyzing || !onAnalyze}
                    className="gl-cpg-analyze-btn"
                >
                    {analyzing ? 'Analyzing...' : 'Analyze Palette'}
                </button>

                <div className="gl-cpg-export">
                    <select
                        value={exportFormat}
                        onChange={(e) => setExportFormat(e.target.value)}
                        disabled={exportLoading}
                    >
                        {EXPORT_FORMATS.map(format => (
                            <option key={format.id} value={format.id}>
                                {format.name}
                            </option>
                        ))}
                    </select>
                    <button
                        onClick={handleExport}
                        disabled={exportLoading}
                        className="gl-cpg-export-btn"
                    >
                        {exportLoading ? 'Exporting...' : 'Export'}
                    </button>
                </div>
            </div>
        </ErrorBoundary>
    );
}
