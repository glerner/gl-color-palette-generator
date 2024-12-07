import React from 'react';
import { createRoot } from 'react-dom/client';
import { App } from './components/App';

// Wait for DOM content to be loaded
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('gl-color-palette-generator');
    if (container) {
        const root = createRoot(container);
        root.render(
            <React.StrictMode>
                <App />
            </React.StrictMode>
        );
    }
});
