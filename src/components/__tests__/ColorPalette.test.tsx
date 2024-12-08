import React from 'react';
import { render, fireEvent, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import { ColorPalette } from '../ColorPalette';

describe('ColorPalette', () => {
    const mockColors = ['#FF0000', '#00FF00', '#0000FF'];
    const mockOnChange = jest.fn();
    const mockOnColorClick = jest.fn();

    beforeEach(() => {
        jest.clearAllMocks();
    });

    it('renders all colors correctly', () => {
        render(<ColorPalette colors={mockColors} />);
        mockColors.forEach((color, index) => {
            expect(screen.getByRole('button', { name: `Color ${index + 1}: ${color}` })).toBeInTheDocument();
            expect(screen.getByText(color)).toBeInTheDocument();
        });
    });

    it('handles color changes when not readonly', () => {
        render(
            <ColorPalette
                colors={mockColors}
                onChange={mockOnChange}
                readonly={false}
            />
        );
        
        const colorButton = screen.getByRole('button', { name: `Color 1: ${mockColors[0]}` });
        fireEvent.click(colorButton);
        
        expect(mockOnChange).not.toHaveBeenCalled();
    });

    it('triggers onColorClick callback when color is clicked', () => {
        render(
            <ColorPalette
                colors={mockColors}
                onColorClick={mockOnColorClick}
                readonly={false}
            />
        );
        
        const secondColorButton = screen.getByRole('button', { name: `Color 2: ${mockColors[1]}` });
        fireEvent.click(secondColorButton);
        
        expect(mockOnColorClick).toHaveBeenCalledWith(mockColors[1], 1);
    });

    it('disables interaction when readonly is true', () => {
        render(
            <ColorPalette
                colors={mockColors}
                onColorClick={mockOnColorClick}
                readonly={true}
            />
        );
        
        const colorButton = screen.getByRole('button', { name: `Color 1: ${mockColors[0]}` });
        fireEvent.click(colorButton);
        
        expect(mockOnColorClick).not.toHaveBeenCalled();
        expect(colorButton).toHaveAttribute('aria-disabled', 'true');
    });

    it('renders analyze and export buttons', () => {
        render(<ColorPalette colors={mockColors} />);
        
        expect(screen.getByRole('button', { name: /analyze palette/i })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /export/i })).toBeInTheDocument();
    });
});
