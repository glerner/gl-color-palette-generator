import React, { useState } from 'react';
import { ColorPicker } from './ColorPicker';

interface PaletteGeneratorFormProps {
    onSubmit: (data: PaletteGeneratorFormData) => void;
    isLoading?: boolean;
    className?: string;
}

export interface PaletteGeneratorFormData {
    prompt: string;
    baseColor?: string;
    numColors: number;
    mode: 'analogous' | 'complementary' | 'triadic' | 'custom';
}

export const PaletteGeneratorForm: React.FC<PaletteGeneratorFormProps> = ({
    onSubmit,
    isLoading = false,
    className = '',
}) => {
    const [formData, setFormData] = useState<PaletteGeneratorFormData>({
        prompt: '',
        numColors: 5,
        mode: 'custom',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSubmit(formData);
    };

    const styles: Record<string, React.CSSProperties> = {
        form: {
            display: 'flex',
            flexDirection: 'column',
            gap: '24px',
            maxWidth: '600px',
            padding: '24px',
            background: '#FFFFFF',
            borderRadius: '8px',
            boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
        },
        field: {
            display: 'flex',
            flexDirection: 'column',
            gap: '8px',
        },
        label: {
            fontSize: '14px',
            fontWeight: 500,
            color: '#333',
        },
        input: {
            padding: '12px',
            borderRadius: '4px',
            border: '1px solid #E0E0E0',
            fontSize: '16px',
            transition: 'border-color 0.2s ease',
        },
        select: {
            padding: '12px',
            borderRadius: '4px',
            border: '1px solid #E0E0E0',
            fontSize: '16px',
            background: '#FFFFFF',
        },
        button: {
            padding: '12px 24px',
            borderRadius: '4px',
            border: 'none',
            background: '#4CAF50',
            color: '#FFFFFF',
            fontSize: '16px',
            fontWeight: 500,
            cursor: 'pointer',
            transition: 'background-color 0.2s ease',
            opacity: isLoading ? 0.7 : 1,
        },
        colorPickerField: {
            display: 'flex',
            alignItems: 'center',
            gap: '16px',
        },
    };

    return (
        <form
            onSubmit={handleSubmit}
            style={styles.form}
            className={className}
        >
            <div style={styles.field}>
                <label style={styles.label} htmlFor="prompt">
                    Describe your desired color palette
                </label>
                <textarea
                    id="prompt"
                    style={{ ...styles.input, minHeight: '100px' }}
                    value={formData.prompt}
                    onChange={(e) =>
                        setFormData({ ...formData, prompt: e.target.value })
                    }
                    placeholder="E.g., A warm and cozy autumn palette with earthy tones"
                    required
                />
            </div>

            <div style={styles.field}>
                <label style={styles.label} htmlFor="mode">
                    Generation Mode
                </label>
                <select
                    id="mode"
                    style={styles.select}
                    value={formData.mode}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            mode: e.target.value as PaletteGeneratorFormData['mode'],
                        })
                    }
                >
                    <option value="custom">Custom</option>
                    <option value="analogous">Analogous</option>
                    <option value="complementary">Complementary</option>
                    <option value="triadic">Triadic</option>
                </select>
            </div>

            <div style={styles.field}>
                <label style={styles.label} htmlFor="numColors">
                    Number of Colors
                </label>
                <input
                    id="numColors"
                    type="number"
                    style={styles.input}
                    value={formData.numColors}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            numColors: parseInt(e.target.value) || 5,
                        })
                    }
                    min={2}
                    max={10}
                    required
                />
            </div>

            <div style={styles.field}>
                <div style={styles.colorPickerField}>
                    <ColorPicker
                        color={formData.baseColor || '#FFFFFF'}
                        onChange={(color) =>
                            setFormData({ ...formData, baseColor: color })
                        }
                        label="Base Color (Optional)"
                    />
                </div>
            </div>

            <button
                type="submit"
                style={styles.button}
                disabled={isLoading}
            >
                {isLoading ? 'Generating...' : 'Generate Palette'}
            </button>
        </form>
    );
};
