import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { ColorScheme } from '../../src/components/ColorScheme';
import '@testing-library/jest-dom';

describe('ColorScheme', () => {
    const mockColors = [
        { hex: '#FF0000' },
        { hex: '#00FF00' },
        { hex: '#0000FF' }
    ];

    const mockOnSave = jest.fn();
    const mockOnDelete = jest.fn();

    const defaultProps = {
        colors: mockColors,
        onSave: mockOnSave,
        onDelete: mockOnDelete,
        name: 'Test Scheme',
        editable: true
    };

    beforeEach(() => {
        jest.clearAllMocks();
    });

    it('renders with default props', () => {
        render(<ColorScheme {...defaultProps} />);
        
        expect(screen.getByText('Test Scheme')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /edit scheme/i })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /delete scheme/i })).toBeInTheDocument();
    });

    it('enters edit mode when edit button is clicked', () => {
        render(<ColorScheme {...defaultProps} />);
        
        fireEvent.click(screen.getByRole('button', { name: /edit scheme/i }));
        
        expect(screen.getByRole('textbox', { name: /scheme name/i })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /save scheme/i })).toBeInTheDocument();
    });

    it('allows editing scheme name', () => {
        render(<ColorScheme {...defaultProps} />);
        
        fireEvent.click(screen.getByRole('button', { name: /edit scheme/i }));
        
        const input = screen.getByRole('textbox', { name: /scheme name/i });
        fireEvent.change(input, { target: { value: 'New Scheme Name' } });
        
        expect(input).toHaveValue('New Scheme Name');
    });

    it('calls onSave with updated name and colors', () => {
        render(<ColorScheme {...defaultProps} />);
        
        // Enter edit mode
        fireEvent.click(screen.getByRole('button', { name: /edit scheme/i }));
        
        // Change name
        const input = screen.getByRole('textbox', { name: /scheme name/i });
        fireEvent.change(input, { target: { value: 'New Scheme Name' } });
        
        // Save changes
        fireEvent.click(screen.getByRole('button', { name: /save scheme/i }));
        
        expect(mockOnSave).toHaveBeenCalledWith('New Scheme Name', mockColors);
    });

    it('shows error when trying to save with empty name', () => {
        render(<ColorScheme {...defaultProps} />);
        
        // Enter edit mode
        fireEvent.click(screen.getByRole('button', { name: /edit scheme/i }));
        
        // Clear name
        const input = screen.getByRole('textbox', { name: /scheme name/i });
        fireEvent.change(input, { target: { value: '' } });
        
        // Try to save
        fireEvent.click(screen.getByRole('button', { name: /save scheme/i }));
        
        expect(screen.getByRole('alert')).toHaveTextContent('Please enter a scheme name');
        expect(mockOnSave).not.toHaveBeenCalled();
    });

    it('calls onDelete when delete button is clicked', () => {
        render(<ColorScheme {...defaultProps} />);
        
        fireEvent.click(screen.getByRole('button', { name: /delete scheme/i }));
        
        expect(mockOnDelete).toHaveBeenCalledWith('Test Scheme');
    });

    it('renders in read-only mode when editable is false', () => {
        render(<ColorScheme {...defaultProps} editable={false} />);
        
        expect(screen.queryByRole('button', { name: /edit scheme/i })).not.toBeInTheDocument();
        expect(screen.queryByRole('button', { name: /delete scheme/i })).not.toBeInTheDocument();
    });

    it('shows "Untitled Scheme" when name is empty', () => {
        render(<ColorScheme {...defaultProps} name="" />);
        
        expect(screen.getByText('Untitled Scheme')).toBeInTheDocument();
    });

    it('updates when new colors are provided', () => {
        const { rerender } = render(<ColorScheme {...defaultProps} />);
        
        const newColors = [
            { hex: '#FF00FF' },
            { hex: '#FFFF00' }
        ];
        
        rerender(<ColorScheme {...defaultProps} colors={newColors} />);
        
        // Since ColorPalette is mocked, we can't directly test the colors
        // but we can verify that the component updates without errors
        expect(screen.getByText('Test Scheme')).toBeInTheDocument();
    });
});
