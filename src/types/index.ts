export interface Color {
    hex: string;
    rgb?: {
        r: number;
        g: number;
        b: number;
    };
    hsl?: {
        h: number;
        s: number;
        l: number;
    };
    name?: string;
}

export interface ColorPaletteProps {
    colors: string[];
    onChange?: (colors: string[]) => void;
    onColorClick?: (color: string, index: number) => void;
    className?: string;
    readonly?: boolean;
    ariaLabel?: string;
}

export interface ColorPickerProps {
    color: string;
    onChange?: (color: string) => void;
    onClick?: (color: string) => void;
    disabled?: boolean;
    ariaLabel?: string;
}

export interface ErrorBoundaryProps {
    children: React.ReactNode;
    fallback?: React.ReactNode;
}

export interface ErrorBoundaryState {
    hasError: boolean;
    error?: Error;
}
