import React, { Component } from 'react';
import { ErrorBoundaryProps, ErrorBoundaryState } from '../types';

export class ErrorBoundary extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
    constructor(props: ErrorBoundaryProps) {
        super(props);
        this.state = { hasError: false };
    }

    static getDerivedStateFromError(error: Error): ErrorBoundaryState {
        return { hasError: true, error };
    }

    componentDidCatch(error: Error, errorInfo: React.ErrorInfo): void {
        console.error('Error caught by boundary:', error, errorInfo);
    }

    render(): React.ReactNode {
        if (this.state.hasError) {
            return this.props.fallback || (
                <div role="alert" className="error-boundary">
                    <h2>Something went wrong.</h2>
                    <p>{this.state.error?.message}</p>
                </div>
            );
        }

        return this.props.children;
    }
}
