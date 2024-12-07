import React, { useState, useEffect } from 'react';
import { SketchPicker, ColorResult } from 'react-color';

interface ColorPickerProps {
    color: string;
    onChange: (color: string) => void;
    onClick?: () => void;
    disabled?: boolean;
    ariaLabel?: string;
    label?: string;
    className?: string;
}

export const ColorPicker: React.FC<ColorPickerProps> = ({
    color,
    onChange,
    onClick,
    disabled,
    ariaLabel,
    label,
    className = '',
}) => {
    const [displayColorPicker, setDisplayColorPicker] = useState(false);
    const [currentColor, setCurrentColor] = useState(color);

    useEffect(() => {
        setCurrentColor(color);
    }, [color]);

    const handleClick = () => {
        if (!disabled) {
            setDisplayColorPicker(!displayColorPicker);
            if (onClick) {
                onClick();
            }
        }
    };

    const handleClose = () => {
        setDisplayColorPicker(false);
    };

    const handleChange = (color: ColorResult) => {
        const newColor = color.hex.toUpperCase();
        setCurrentColor(newColor);
        onChange(newColor);
    };

    const styles: Record<string, React.CSSProperties> = {
        color: {
            width: '36px',
            height: '36px',
            borderRadius: '4px',
            border: '2px solid #E0E0E0',
            background: currentColor,
            cursor: disabled ? 'not-allowed' : 'pointer',
            transition: 'border-color 0.2s ease',
        },
        swatch: {
            display: 'inline-block',
            position: 'relative',
        },
        popover: {
            position: 'absolute',
            zIndex: 2,
            top: '44px',
            left: '0px',
        },
        cover: {
            position: 'fixed',
            top: '0px',
            right: '0px',
            bottom: '0px',
            left: '0px',
        },
        wrapper: {
            display: 'inline-flex',
            flexDirection: 'column',
            gap: '8px',
        },
        label: {
            fontSize: '14px',
            fontWeight: 500,
            color: '#333',
        },
    };

    return (
        <div style={styles.wrapper} className={className}>
            {label && <label style={styles.label}>{label}</label>}
            <div style={styles.swatch}>
                <div
                    style={styles.color}
                    onClick={handleClick}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            handleClick();
                        }
                    }}
                    role="button"
                    tabIndex={disabled ? -1 : 0}
                    aria-label={ariaLabel || label || 'Color picker'}
                    aria-disabled={disabled}
                />
                {displayColorPicker ? (
                    <div style={styles.popover}>
                        <div
                            style={styles.cover}
                            onClick={handleClose}
                            onKeyDown={(e) => {
                                if (e.key === 'Escape') {
                                    handleClose();
                                }
                            }}
                            role="button"
                            tabIndex={-1}
                        />
                        <SketchPicker
                            color={currentColor}
                            onChange={handleChange}
                            disableAlpha
                        />
                    </div>
                ) : null}
            </div>
        </div>
    );
};
