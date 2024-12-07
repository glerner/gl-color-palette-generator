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
        mockColors.forEach(color => {
            expect(screen.getByRole('button', { name: new RegExp(color, 'i') })).toBeInTheDocument();
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
        
        // Find ColorPicker component and simulate a color change
        const colorPicker = screen.getByLabelText(`Color 1: ${mockColors[0]}`);
        fireEvent.change(colorPicker, { target: { value: '#FFFFFF' } });
        
        expect(mockOnChange).toHaveBeenCalledWith(['#FFFFFF', ...mockColors.slice(1)]);
    });

    it('prevents color changes when readonly', () => {
        render(
            <ColorPalette
                colors={mockColors}
                onChange={mockOnChange}
                readonly={true}
            />
        );
        
        const firstColorButton = screen.getByRole('button', { name: new RegExp(mockColors[0], 'i') });
        fireEvent.click(firstColorButton);
        
        expect(mockOnChange).not.toHaveBeenCalled();
    });

    it('triggers onColorClick callback when color is clicked', () => {
        render(
            <ColorPalette
                colors={mockColors}
                onColorClick={mockOnColorClick}
            />
        );
        
        const secondColorButton = screen.getByRole('button', { name: new RegExp(mockColors[1], 'i') });
        fireEvent.click(secondColorButton);
        
        expect(mockOnColorClick).toHaveBeenCalledWith(mockColors[1], 1);
    });

    it('applies custom className when provided', () => {
        const customClass = 'custom-palette';
        const { container } = render(
            <ColorPalette colors={mockColors} className={customClass} />
        );
        
        expect(container.firstChild).toHaveClass(customClass);
    });
});
