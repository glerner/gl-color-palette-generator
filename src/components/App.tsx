import React, { useState } from 'react';
import { ColorPalette } from './ColorPalette';
import {
    PaletteGeneratorForm,
    PaletteGeneratorFormData,
} from './PaletteGeneratorForm';

interface PaletteMetadata {
    theme: string;
    mood: string;
    description: string;
}

interface GeneratedPalette {
    colors: string[];
    metadata: PaletteMetadata;
}

export const App: React.FC = () => {
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [generatedPalette, setGeneratedPalette] =
        useState<GeneratedPalette | null>(null);

    const handleGeneratePalette = async (data: PaletteGeneratorFormData) => {
        setIsLoading(true);
        setError(null);

        try {
            const response = await fetch('/wp-json/gl-color-palette/v1/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': (window as any).wpApiSettings.nonce,
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error('Failed to generate palette');
            }

            const palette: GeneratedPalette = await response.json();
            setGeneratedPalette(palette);
        } catch (err) {
            setError(
                err instanceof Error
                    ? err.message
                    : 'An unexpected error occurred'
            );
        } finally {
            setIsLoading(false);
        }
    };

    const handleColorClick = (color: string) => {
        navigator.clipboard.writeText(color);
    };

    const styles: Record<string, React.CSSProperties> = {
        wrapper: {
            maxWidth: '1200px',
            margin: '0 auto',
            padding: '40px 20px',
            display: 'flex',
            flexDirection: 'column',
            gap: '40px',
        },
        header: {
            textAlign: 'center',
        },
        title: {
            fontSize: '36px',
            fontWeight: 700,
            color: '#333',
            marginBottom: '16px',
        },
        subtitle: {
            fontSize: '18px',
            color: '#666',
            maxWidth: '600px',
            margin: '0 auto',
        },
        content: {
            display: 'flex',
            gap: '40px',
            flexWrap: 'wrap',
            justifyContent: 'center',
        },
        formSection: {
            flex: '1 1 400px',
            maxWidth: '600px',
        },
        resultSection: {
            flex: '1 1 400px',
            maxWidth: '600px',
            display: 'flex',
            flexDirection: 'column',
            gap: '24px',
        },
        error: {
            padding: '16px',
            borderRadius: '4px',
            background: '#FFEBEE',
            color: '#C62828',
            marginBottom: '24px',
        },
        metadata: {
            padding: '24px',
            background: '#FFFFFF',
            borderRadius: '8px',
            boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
        },
        metadataTitle: {
            fontSize: '18px',
            fontWeight: 500,
            color: '#333',
            marginBottom: '16px',
        },
        metadataList: {
            display: 'flex',
            flexDirection: 'column',
            gap: '12px',
        },
        metadataItem: {
            display: 'flex',
            gap: '8px',
        },
        metadataLabel: {
            fontWeight: 500,
            color: '#666',
            minWidth: '100px',
        },
        metadataValue: {
            color: '#333',
        },
    };

    return (
        <div style={styles.wrapper}>
            <header style={styles.header}>
                <h1 style={styles.title}>Color Palette Generator</h1>
                <p style={styles.subtitle}>
                    Generate beautiful and harmonious color palettes using AI.
                    Perfect for your next design project.
                </p>
            </header>

            <main style={styles.content}>
                <section style={styles.formSection}>
                    <PaletteGeneratorForm
                        onSubmit={handleGeneratePalette}
                        isLoading={isLoading}
                    />
                </section>

                <section style={styles.resultSection}>
                    {error && <div style={styles.error}>{error}</div>}

                    {generatedPalette && (
                        <>
                            <ColorPalette
                                colors={generatedPalette.colors}
                                readonly
                                onColorClick={handleColorClick}
                            />

                            <div style={styles.metadata}>
                                <h2 style={styles.metadataTitle}>
                                    Palette Information
                                </h2>
                                <div style={styles.metadataList}>
                                    <div style={styles.metadataItem}>
                                        <span style={styles.metadataLabel}>
                                            Theme:
                                        </span>
                                        <span style={styles.metadataValue}>
                                            {generatedPalette.metadata.theme}
                                        </span>
                                    </div>
                                    <div style={styles.metadataItem}>
                                        <span style={styles.metadataLabel}>
                                            Mood:
                                        </span>
                                        <span style={styles.metadataValue}>
                                            {generatedPalette.metadata.mood}
                                        </span>
                                    </div>
                                    <div style={styles.metadataItem}>
                                        <span style={styles.metadataLabel}>
                                            Description:
                                        </span>
                                        <span style={styles.metadataValue}>
                                            {generatedPalette.metadata.description}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </>
                    )}
                </section>
            </main>
        </div>
    );
};
