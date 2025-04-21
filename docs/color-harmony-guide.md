# Understanding Color Harmonies

Color harmonies are combinations of colors that work well together based on their positions on the color wheel. Understanding these relationships helps create visually appealing and balanced designs.

## Common Color Harmonies

### Analogous
Colors that are next to each other on the color wheel. Creates a harmonious, comfortable design.
- Example: Yellow, Yellow-Green, Green
- Best for: Creating a cohesive, unified look
- Use when: You want a gentle, soothing design

### Complementary
Colors opposite each other on the color wheel. Creates high contrast and visual impact.
- Example: Blue and Orange
- Best for: Making elements stand out
- Use when: You need strong emphasis or call-to-action elements

### Analogous with Complement
A base color, its analogous colors, and its complement. Perfect for WordPress themes needing both harmony and contrast.
- Example: Blue (base), Blue-Purple and Blue-Green (analogous), Orange (complement)
- Best for: WordPress themes needing primary, secondary, and accent colors
- Use when: You want both harmony and contrast
- Theme Usage:
  - Primary: Base color
  - Secondary: one of the Analogous colors
  - Accent: Complement color

### Triadic
Three colors equally spaced on the color wheel. Creates vibrant, balanced designs.
- Example: Red, Yellow, Blue
- Best for: Creating dynamic, energetic layouts
- Use when: You want variety while maintaining balance

### Monochromatic
Different shades and tints of the same color. Creates a cohesive, sophisticated look.
- Example: Light Blue, Blue, Dark Blue
- Best for: Clean, professional designs
- Use when: You want elegance and simplicity

### Split-Complementary
A base color and two colors adjacent to its complement. Provides high contrast while being easier to balance than complementary colors.
- Example: Blue with Yellow-Orange and Red-Orange
- Best for: Beginners wanting high contrast
- Use when: Complementary feels too intense

### Tetradic (Double Complementary)
Two pairs of complementary colors. Creates a rich, dynamic color scheme best used with one dominant color.
- Example: Blue and Orange with Yellow and Purple
- Best for: Complex, vibrant designs
- Use when: You need a full range of colors

### Square
Four colors evenly spaced on the color wheel. Creates a balanced, vibrant design when used with varying intensities.
- Example: Red, Yellow-Green, Blue, and Purple-Red
- Best for: Rich, varied designs
- Use when: You need multiple colors with equal emphasis

## Common WordPress Theme Harmonies

### Analogous with Complement
A base color, its analogous colors, and its complement. Perfect for WordPress themes needing both harmony and contrast.
- Example: Blue (base), Blue-Purple and Blue-Green (analogous), Orange (complement)
- Best for: WordPress themes needing primary, secondary, and accent colors
- Use when: You want both harmony and contrast
- Theme Usage:
  - Primary: Base color
  - Secondary: one of the Analogous colors
  - Accent: Complement color

### Monochromatic with Accent
A single color in different shades plus one contrasting accent. Popular in minimalist WordPress themes.
- Example: Different shades of blue with orange accent
- Best for: Clean, focused designs that need a pop of color
- Use when: You want simplicity with impact
- Theme Usage:
  - Primary: Base color
  - Secondary: Lighter/darker variations
  - Accent: Contrasting color for Calls to Action (CTAs)

### Dual Tone
Two main colors with neutral grays. Common in modern WordPress themes.
- Example: Deep blue and teal with gray text/backgrounds
- Best for: Modern, professional designs
- Use when: You want a strong brand presence
- Theme Usage:
  - Primary: First main color
  - Secondary: Second main color
  - Text/Backgrounds: Grays for balance

### Neutral with Pop
Neutral base colors with one vibrant accent. Popular in business and portfolio themes.
- Example: Grays/beiges with vibrant blue accent
- Best for: Professional sites needing subtle branding
- Use when: Content should be the focus
- Theme Usage:
  - Primary/Secondary: Neutral colors
  - Accent: Single vibrant color
  - Text/Backgrounds: Neutral grays

## Special Generation Methods

### AI-Generated Palettes
Let artificial intelligence create custom color palettes optimized for your needs:

1. **From Themes or Moods**
   - "Valentine's Day romance"
   - "Arizona desert at sunset"
   - "Tropical beach paradise"
   - "Cozy winter cabin"

2. **From Business Goals**
   - "Professional consulting firm targeting Fortune 500"
   - "Eco-friendly organic food brand"
   - "Tech startup focused on AI innovation"
   - "Luxury spa and wellness center"

3. **From Website Screenshots**
   - "Extract the main theme colors from this website screenshot"
   - "Create a palette similar to this website but more modern"
   - "Match this website's style but make it more energetic"
   - AI understands layout context (headers, buttons, etc.)

4. **From Inspiration Photos**
   - Upload photos that match your vision
   - Corrects white balance and exposure
   - AI adjusts colors for optimal web usage
   - Maintains mood while ensuring accessibility

Best for:
- Creating mood-specific designs
- While matching business objectives
- Replicating successful website designs
- Ensuring colors work well together, regardless of source

### From Image (Direct)
Extract colors directly from an image without AI adjustment.
- Best for: Exactly matching existing theme designs
- Consider: Colors may need manual adjustment for web usage
- Simple "most used colors" approach, won't work well if image background colors don't match site mood
- Try AI-Generated method instead for better results with website screenshots

## Using Color Harmonies in WordPress Themes

1. **Choose Your Base**
   - Start with your primary brand color
   - Use it as the foundation for your harmony

2. **Apply the 60-30-10 Rule**
   - 60% dominant color (Primary)
   - 30% secondary colors (Secondary and analogous)
   - 10% accent color (Complement)

3. **Consider Context**
   - Headers and footers: Use your dominant color
   - Call-to-action buttons: Use complementary colors
   - Text and backgrounds: Ensure good contrast

4. **Test Accessibility**
   - This plugin will *always* check WCAG contrast ratios
   - Only WCAG AAA contrast, or better, with either black or white text
   - Test with different types of content
   - Verify readability in all color combinations

## Page Background and Text Colors

### Internal Color Structure

In our internal color structure, we use semantic naming that clearly indicates the purpose of each color:

- **'main-background'**: The main page background color
  - Light mode: Usually white or off-white (e.g., #ffffff, #f9f9f9)
  - Dark mode: Usually soft black (e.g., #111111, #1a1a1a)

- **'text-color'**: The primary text color
  - Light mode: Usually near-black (e.g., #111111, #333333)
  - Dark mode: Usually near-white (e.g., #f5f5f5, #e0e0e0)

### Light/Dark Mode Implementation

For light/dark mode support:

1. **Light Mode (Default)**:
   - 'main-background': Near-white
   - 'text-color': Near-black

2. **Dark Mode**:
   - 'main-background': Near-black
   - 'text-color': Near-white

The plugin will implement both light and dark mode colors in a single theme.json file using the CSS `light-dark()` function. This modern approach allows for automatic switching between color schemes based on the user's system preferences.

#### CSS `light-dark()` Function

The `light-dark()` function is a CSS color function that allows for defining both light and dark mode colors in a single declaration:

```css
color: light-dark( #111111, #f5f5f5); /* Dark text in light mode, light text in dark mode */
background-color: light-dark( #ffffff, #1a1a1a); /* Light background in light mode, dark in dark mode */
```

In our theme.json output, we'll use this function to define colors that automatically adapt to the user's preferred color scheme:

```json
{
  "settings": {
    "color": {
      "palette": [
        {
          "slug": "base",
          "color": "light-dark( #ffffff, #111111)",
          "name": "Base"
        },
        {
          "slug": "contrast",
          "color": "light-dark( #111111, #f5f5f5)",
          "name": "Contrast"
        },
        {
          "slug": "primary",
          "color": "#0073aa",
          "name": "Primary"
        }
        // Additional colors...
      ]
    }
  }
}
```

This approach allows a single theme.json file to support both light and dark modes automatically, with colors that adapt based on the user's system preferences without requiring separate theme files or manual switching.

#### CSS Variables and Fallbacks

When WordPress processes a theme.json file, it generates CSS variables for each color in the palette:

```css
/* Generated by WordPress from theme.json */
:root {
  --wp--preset--color--base: light-dark(#ffffff, #111111);
  --wp--preset--color--contrast: light-dark(#111111, #f5f5f5);
  --wp--preset--color--primary: #0073aa;
  /* Additional colors... */
}
```

These variables can then be used throughout the theme's CSS. However, there are some important considerations:

1. **Fallbacks**: The theme.json format doesn't support fallbacks directly in color definitions. Fallbacks need to be implemented in CSS:
   ```css
   background-color: var(--wp--preset--color--primary-lighter, lightgreen);
   ```

2. **Browser Support**: Not all browsers support the `light-dark()` function yet. For maximum compatibility, our plugin will:
   - Generate a theme.json with `light-dark()` for modern browsers
   - Provide a recommended style.css with fallbacks for older browsers
   - Include media queries for prefers-color-scheme as a fallback

#### Recommended style.css Approach

Rather than overwriting a user's style.css, our plugin will generate a streamlined recommended style.css file. With 96.43% browser support for `prefers-color-scheme` and 87.54% for `light-dark()`, we can focus on a practical approach:

```css
/* ===== COMBINED LIGHT/DARK MODE STRATEGY ===== */

/* 1. Base variables with fallbacks for all browsers */
:root {
  /* Default light mode colors (fallback for all browsers) */
  --base-color: var(--wp--preset--color--base, #ffffff); /* If you see #ffffff, WordPress variables aren't loaded */
  --text-color: var(--wp--preset--color--contrast, #111111);

  /* Brand colors - base versions */
  --primary-color: var(--wp--preset--color--primary, #0073aa);
  --secondary-color: var(--wp--preset--color--secondary, #6c757d);

  /* Default UI-optimized variations for light mode */
  --primary-for-ui: var(--wp--preset--color--primary-darker, fuchsia); /* If you see fuchsia, the variable isn't defined */
  --secondary-for-ui: var(--wp--preset--color--secondary-darker, fuchsia);
}

/* 2. Dark mode media query fallback (96.43% browser support) */
@media (prefers-color-scheme: dark) {
  :root {
    /* Dark mode colors */
    --base-color: var(--wp--preset--color--contrast, #111111); /* In dark mode, we swap base/contrast */
    --text-color: var(--wp--preset--color--base, fuchsia);

    /* UI-optimized variations for dark mode */
    --primary-for-ui: var(--wp--preset--color--primary-lighter, fuchsia); /* Lighter version for dark backgrounds */
    --secondary-for-ui: var(--wp--preset--color--secondary-lighter, fuchsia);
  }
}

/* 3. Modern browsers with light-dark() support (87.54% browser support) */
@supports (color: light-dark( #000, #fff)) {
  :root {
    /* Override with light-dark() for supported browsers */
    --base-color: light-dark( var(--wp--preset--color--base, #ffffff), var(--wp--preset--color--contrast, #111111));
    --text-color: light-dark( var(--wp--preset--color--contrast, #111111), var(--wp--preset--color--base, fuchsia));
    --primary-for-ui: light-dark( var(--wp--preset--color--primary-darker, fuchsia), var(--wp--preset--color--primary-lighter, fuchsia));
    --secondary-for-ui: light-dark( var(--wp--preset--color--secondary-darker, fuchsia), var(--wp--preset--color--secondary-lighter, fuchsia));
  }
}

/* 4. Utility classes */
.site-background {
  background-color: var(--base-color, #ffffff);
}

.site-text {
  color: var(--text-color, #111111);
}

/* WordPress-specific classes */
.has-base-background-color {
  background-color: var(--base-color, #ffffff);
}

.has-text-color {
  color: var(--text-color, #111111);
}
```

This approach provides:

1. **Maximum browser compatibility with a layered strategy**:
   - Base light theme for all browsers (100% support)
   - Dark mode via media queries for most browsers (96.43% support)
   - Enhanced with `light-dark()` for modern browsers (87.54% support)
   - Progressive enhancement that works for everyone

2. **WCAG Contrast Compliance**:
   - All color combinations are tested to ensure they meet WCAG AAA standards
   - Different brand color variations for light/dark modes:
     - Use lighter brand variations (primary-light, primary-lighter) in dark mode
     - Use darker brand variations (primary-dark, primary-darker) in light mode
   - Primary brand colors should only be used for decorative elements, not for text/background combinations

3. **Minimal Code Footprint**:
   - Focuses on the essential variables and classes
   - Avoids unnecessary complexity and bloat

This recommended style.css will not overwrite the user's existing styles but will provide them with a practical template they can incorporate into their theme.

### WordPress Theme.json Mapping

WordPress themes use different naming conventions than our internal structure. Here's how we map between them:

| WordPress theme.json | Our Internal Structure | Purpose |
|----------------------|------------------------|----------|
| 'base'               | 'main-background'      | Page background |
| 'contrast'           | 'text-color'           | Text color |
| 'primary'            | 'primary'              | Main brand color |
| 'secondary'          | 'secondary'            | Secondary brand color |
| 'accent-1', 'accent-2', etc. | 'accent', 'tertiary' | Accent colors |

When generating theme.json output, the plugin will:

1. Map our internal semantic names to WordPress conventions
2. Use CSS `light-dark()` function to include both light and dark mode colors in a single theme.json file
3. Ensure all color combinations meet WCAG AAA standards

This approach provides clear, semantic naming internally while maintaining compatibility with WordPress theme.json conventions.

For detailed information about color roles and their uses in WordPress themes, see [Color Roles and Their Uses](THEME-COLOR-GUIDE.md#color-roles-and-their-uses)
