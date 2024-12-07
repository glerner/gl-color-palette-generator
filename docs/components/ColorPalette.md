# ColorPalette Component

The `ColorPalette` component is a React component that displays a collection of color swatches. It supports both interactive and readonly modes, making it versatile for various use cases.

## Features

- Interactive color selection
- Readonly mode for display-only purposes
- Keyboard navigation support
- ARIA labels for accessibility
- Error boundary protection
- Typescript support

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| colors | string[] | Required | Array of color values in hex format |
| onChange | (colors: string[]) => void | undefined | Callback when colors are changed |
| onColorClick | (color: string, index: number) => void | undefined | Callback when a color is clicked |
| className | string | '' | Additional CSS class names |
| readonly | boolean | false | If true, colors cannot be changed |
| ariaLabel | string | 'Color Palette' | ARIA label for the palette |

## Usage

```tsx
import { ColorPalette } from './components/ColorPalette';

// Basic usage
<ColorPalette colors={['#FF0000', '#00FF00', '#0000FF']} />

// With change handler
<ColorPalette
  colors={colors}
  onChange={(newColors) => setColors(newColors)}
  onColorClick={(color, index) => console.log(`Clicked ${color} at index ${index}`)}
/>

// Readonly mode
<ColorPalette
  colors={colors}
  readonly={true}
/>
```

## Accessibility

The component implements the following accessibility features:

- ARIA labels for the palette and individual colors
- Keyboard navigation support
- High contrast color values
- Screen reader support

## Error Handling

The component includes error handling for:

- Invalid color arrays
- Empty color arrays
- Invalid color formats
- Runtime errors (via ErrorBoundary)

## Testing

The component includes comprehensive tests covering:

- Rendering
- Color changes
- Click handling
- Readonly mode
- Accessibility
- Error cases

Run tests with:
```bash
npm test
```

## Contributing

When contributing to this component:

1. Ensure all props are properly typed
2. Add tests for new features
3. Update documentation
4. Verify accessibility compliance
5. Test error handling
