import React from 'react';
import { ColorPicker } from './ColorPicker';
import { ErrorBoundary } from './ErrorBoundary';
import { ColorPaletteProps } from '../types';

/**
 * ColorPalette component displays a collection of color swatches that can be interactive
 * or readonly. It supports both controlled and uncontrolled color selection.
 *
 * @param {ColorPaletteProps} props - Component props
 * @returns {JSX.Element} Rendered color palette
 */
export const ColorPalette: React.FC<ColorPaletteProps> = ({
    colors,
    onChange,
    onColorClick,
    className = '',
    readonly = false,
    ariaLabel = 'Color Palette',
}) => {
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
                aria-label={ariaLabel}
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
                    </div>
                ))}
            </div>
        </ErrorBoundary>
    );
};
