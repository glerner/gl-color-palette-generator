import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { PaletteGenerator } from '../../src/components/PaletteGenerator';
import '@testing-library/jest-dom';

// Mock fetch globally
global.fetch = jest.fn();

describe('PaletteGenerator', () => {
    const mockOnGenerate = jest.fn();
    const defaultProps = {
        onPaletteGenerated: mockOnGenerate,
        maxColors: 5
    };

    beforeEach(() => {
        // Clear all mocks before each test
        jest.clearAllMocks();
        (global.fetch as jest.Mock).mockClear();
    });

    it('renders with default props', () => {
        render(<PaletteGenerator {...defaultProps} />);
        
        expect(screen.getByLabelText('Base color')).toBeInTheDocument();
        expect(screen.getByLabelText('Color scheme')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /generate palette/i })).toBeInTheDocument();
    });

    it('handles HTTP errors', async () => {
        (global.fetch as jest.Mock).mockRejectedValueOnce(new Error('HTTP error! status: 429'));
        render(<PaletteGenerator {...defaultProps} />);

        fireEvent.click(screen.getByRole('button', { name: /generate palette/i }));

        await waitFor(() => {
            const alert = screen.getByRole('alert');
            expect(alert).toHaveTextContent('HTTP error! status: 429');
            expect(alert).toHaveTextContent('Try Again');
        });
    });

    it('sends correct data to API', async () => {
        (global.fetch as jest.Mock).mockResolvedValueOnce({
            ok: true,
            json: () => Promise.resolve({ colors: ['#FF0000', '#00FF00', '#0000FF'] })
        });

        render(<PaletteGenerator {...defaultProps} />);
        
        // Set base color
        const baseColorPicker = screen.getByLabelText('Base color');
        fireEvent.click(baseColorPicker);
        
        // Select theme
        const themeSelect = screen.getByLabelText('Color scheme');
        fireEvent.change(themeSelect, { target: { value: 'modern' } });

        // Generate palette
        fireEvent.click(screen.getByRole('button', { name: /generate palette/i }));

        await waitFor(() => {
            expect(global.fetch).toHaveBeenCalledWith(
                '/wp-json/gl-color-palette/v1/palettes',
                expect.objectContaining({
                    method: 'POST',
                    headers: expect.objectContaining({
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': ''
                    }),
                    body: JSON.stringify({
                        base_color: '#000000',
                        theme: 'modern',
                        count: 5
                    })
                })
            );
        });
    });
});
