import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { PaletteGenerator } from '../../src/components/PaletteGenerator';
import '@testing-library/jest-dom';

// Mock fetch globally
global.fetch = jest.fn();

describe('PaletteGenerator', () => {
    const mockOnGenerate = jest.fn();
    const defaultProps = {
        onGenerate: mockOnGenerate,
        maxColors: 5,
        defaultBaseColor: '#FF0000',
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

    it('allows selecting different color schemes', () => {
        render(<PaletteGenerator {...defaultProps} />);
        
        const select = screen.getByLabelText('Color scheme');
        fireEvent.change(select, { target: { value: 'complementary' } });
        
        expect(select).toHaveValue('complementary');
    });

    it('handles successful palette generation', async () => {
        const mockColors = [
            { hex: '#FF0000' },
            { hex: '#00FF00' },
            { hex: '#0000FF' }
        ];

        (global.fetch as jest.Mock).mockImplementationOnce(() =>
            Promise.resolve({
                ok: true,
                json: () => Promise.resolve({ colors: mockColors })
            })
        );

        render(<PaletteGenerator {...defaultProps} />);
        
        const generateButton = screen.getByRole('button', { name: /generate palette/i });
        fireEvent.click(generateButton);

        expect(generateButton).toHaveAttribute('aria-busy', 'true');
        expect(screen.getByText('Generating...')).toBeInTheDocument();

        await waitFor(() => {
            expect(mockOnGenerate).toHaveBeenCalledWith(mockColors);
        });
    });

    it('handles generation errors', async () => {
        (global.fetch as jest.Mock).mockImplementationOnce(() =>
            Promise.reject(new Error('Network error'))
        );

        render(<PaletteGenerator {...defaultProps} />);
        
        fireEvent.click(screen.getByRole('button', { name: /generate palette/i }));

        await waitFor(() => {
            expect(screen.getByRole('alert')).toHaveTextContent('Network error');
        });
    });

    it('handles HTTP errors', async () => {
        (global.fetch as jest.Mock).mockImplementationOnce(() =>
            Promise.resolve({
                ok: false,
                status: 429
            })
        );

        render(<PaletteGenerator {...defaultProps} />);
        
        fireEvent.click(screen.getByRole('button', { name: /generate palette/i }));

        await waitFor(() => {
            expect(screen.getByRole('alert')).toHaveTextContent('HTTP error! status: 429');
        });
    });

    it('disables generate button while generating', async () => {
        (global.fetch as jest.Mock).mockImplementationOnce(() =>
            new Promise(resolve => setTimeout(resolve, 100))
        );

        render(<PaletteGenerator {...defaultProps} />);
        
        const generateButton = screen.getByRole('button', { name: /generate palette/i });
        fireEvent.click(generateButton);

        expect(generateButton).toBeDisabled();
        expect(generateButton).toHaveAttribute('aria-busy', 'true');
    });

    it('sends correct data to API', async () => {
        render(<PaletteGenerator {...defaultProps} />);
        
        const select = screen.getByLabelText('Color scheme');
        fireEvent.change(select, { target: { value: 'complementary' } });
        
        fireEvent.click(screen.getByRole('button', { name: /generate palette/i }));

        expect(global.fetch).toHaveBeenCalledWith(
            '/wp-json/gl-cpg/v1/generate',
            expect.objectContaining({
                method: 'POST',
                headers: expect.objectContaining({
                    'Content-Type': 'application/json'
                }),
                body: JSON.stringify({
                    baseColor: '#FF0000',
                    scheme: 'complementary',
                    maxColors: 5
                })
            })
        );
    });
});
