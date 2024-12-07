import React, { useEffect, useState } from 'react';
import { ColorPalette } from './ColorPalette';
import { ErrorBoundary } from './ErrorBoundary';
import { Color } from '../types';

interface ColorSchemeProps {
    colors: Color[];
    onSave?: (name: string, colors: Color[]) => void;
    onDelete?: (name: string) => void;
    className?: string;
    name?: string;
    editable?: boolean;
}

/**
 * ColorScheme component displays a named color scheme with options to save and delete
 *
 * @param {ColorSchemeProps} props - Component props
 * @returns {JSX.Element} Rendered color scheme
 */
export const ColorScheme: React.FC<ColorSchemeProps> = ({
    colors,
    onSave,
    onDelete,
    className = '',
    name = '',
    editable = true,
}) => {
    const [schemeName, setSchemeName] = useState(name);
    const [isEditing, setIsEditing] = useState(false);
    const [colorList, setColorList] = useState<string[]>(colors.map(c => c.hex));
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        setColorList(colors.map(c => c.hex));
    }, [colors]);

    const handleSave = () => {
        if (!schemeName.trim()) {
            setError('Please enter a scheme name');
            return;
        }

        if (onSave) {
            const colorObjects: Color[] = colorList.map(hex => ({ hex }));
            onSave(schemeName, colorObjects);
        }
        setIsEditing(false);
        setError(null);
    };

    const handleDelete = () => {
        if (onDelete) {
            onDelete(schemeName);
        }
    };

    const styles: Record<string, React.CSSProperties> = {
        container: {
            display: 'flex',
            flexDirection: 'column',
            gap: '16px',
            padding: '20px',
            background: '#FFFFFF',
            borderRadius: '8px',
            boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
        },
        header: {
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            gap: '16px',
        },
        nameContainer: {
            flex: 1,
        },
        name: {
            fontSize: '18px',
            fontWeight: 'bold',
            color: '#333',
            margin: 0,
        },
        input: {
            width: '100%',
            padding: '8px 12px',
            borderRadius: '4px',
            border: '1px solid #E0E0E0',
            fontSize: '16px',
        },
        buttonGroup: {
            display: 'flex',
            gap: '8px',
        },
        button: {
            padding: '6px 12px',
            borderRadius: '4px',
            border: 'none',
            fontSize: '14px',
            cursor: 'pointer',
            transition: 'background-color 0.2s ease',
        },
        saveButton: {
            background: '#4CAF50',
            color: '#FFFFFF',
            '&:hover': {
                backgroundColor: '#45a049',
            },
        } as React.CSSProperties,
        editButton: {
            background: '#2196F3',
            color: '#FFFFFF',
            '&:hover': {
                backgroundColor: '#1976D2',
            },
        } as React.CSSProperties,
        deleteButton: {
            background: '#f44336',
            color: '#FFFFFF',
            '&:hover': {
                backgroundColor: '#d32f2f',
            },
        } as React.CSSProperties,
        error: {
            color: '#f44336',
            fontSize: '14px',
            marginTop: '4px',
        },
    };

    return (
        <ErrorBoundary>
            <div className={`color-scheme ${className}`} style={styles.container}>
                <div style={styles.header}>
                    <div style={styles.nameContainer}>
                        {isEditing ? (
                            <input
                                type="text"
                                value={schemeName}
                                onChange={(e) => setSchemeName(e.target.value)}
                                style={styles.input}
                                placeholder="Enter scheme name"
                                aria-label="Scheme name"
                            />
                        ) : (
                            <h3 style={styles.name}>{schemeName || 'Untitled Scheme'}</h3>
                        )}
                        {error && <div style={styles.error} role="alert">{error}</div>}
                    </div>
                    {editable && (
                        <div style={styles.buttonGroup}>
                            {isEditing ? (
                                <button
                                    onClick={handleSave}
                                    style={{ ...styles.button, ...styles.saveButton }}
                                    aria-label="Save scheme"
                                >
                                    Save
                                </button>
                            ) : (
                                <button
                                    onClick={() => setIsEditing(true)}
                                    style={{ ...styles.button, ...styles.editButton }}
                                    aria-label="Edit scheme"
                                >
                                    Edit
                                </button>
                            )}
                            <button
                                onClick={handleDelete}
                                style={{ ...styles.button, ...styles.deleteButton }}
                                aria-label="Delete scheme"
                            >
                                Delete
                            </button>
                        </div>
                    )}
                </div>
                <ColorPalette
                    colors={colorList}
                    readonly={!isEditing}
                    onChange={setColorList}
                    className="scheme-palette"
                />
            </div>
        </ErrorBoundary>
    );
};
