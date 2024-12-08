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
    onAnalyze?: (colors: string[]) => Promise<PaletteAnalysis>;
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
            const result = await onAnalyze(colors);
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

    const handleColorChange = (index: number, newColor: string) => {
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

    return (
        <div className={`color-palette ${className}`} role="group" aria-label="Color Palette" style={{
            display: 'flex',
            flexWrap: 'wrap',
            gap: '16px',
            padding: '16px',
            background: '#FFFFFF',
            borderRadius: '8px',
            boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)'
        }}>
            {colors.map((color, index) => (
                <div key={`${color}-${index}`} className="color-box" style={{
                    width: '80px',
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    gap: '8px'
                }}>
                    <div className="" style={{ display: 'inline-flex', flexDirection: 'column', gap: '8px' }}>
                        <div style={{ display: 'inline-block', position: 'relative' }}>
                            <div
                                role="button"
                                aria-label={`Color ${index + 1}: ${color}`}
                                aria-disabled={readonly}
                                tabIndex={readonly ? -1 : 0}
                                onClick={() => !readonly && onColorClick?.(color, index)}
                                style={{
                                    width: '36px',
                                    height: '36px',
                                    borderRadius: '4px',
                                    border: '2px solid #e0e0e0',
                                    background: color,
                                    cursor: readonly ? 'default' : 'pointer',
                                    transition: 'border-color 0.2s ease',
                                }}
                            />
                        </div>
                    </div>
                    <span
                        className="color-value"
                        aria-label={`Color value: ${color}`}
                        style={{
                            fontSize: '14px',
                            fontFamily: 'monospace',
                            color: '#666666',
                            userSelect: 'all'
                        }}
                    >
                        {color}
                    </span>
                </div>
            ))}
            <div className="gl-cpg-actions">
                <button
                    className="gl-cpg-analyze-btn"
                    onClick={handleAnalyze}
                    disabled={analyzing || !onAnalyze}
                >
                    {analyzing ? 'Analyzing...' : 'Analyze Palette'}
                </button>
                <div className="gl-cpg-export">
                    <select
                        value={exportFormat}
                        onChange={(e) => setExportFormat(e.target.value)}
                    >
                        {EXPORT_FORMATS.map(format => (
                            <option key={format.id} value={format.id}>
                                {format.name}
                            </option>
                        ))}
                    </select>
                    <button
                        className="gl-cpg-export-btn"
                        onClick={handleExport}
                        disabled={exportLoading}
                    >
                        {exportLoading ? 'Exporting...' : 'Export'}
                    </button>
                </div>
            </div>
        </div>
    );
}

export default ColorPalette;
