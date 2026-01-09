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

## Working with Vibrant Brand Palettes

Vibrant, high-saturation color palettes create energetic, attention-grabbing designs perfect for youth brands, sports teams, food marketing, and festive applications. However, they require careful handling to maintain usability and accessibility.

### Understanding Saturation in Vibrant Palettes

**Saturation** (S in HSL) controls color intensity:
- **0%** = Grayscale (no color)
- **50%** = Moderate color
- **100%** = Maximum intensity

Vibrant palettes typically use **60-100% saturation** for brand colors, creating bold, memorable designs.

### The Challenge: Semantic Colors in Vibrant Palettes

When your brand palette is highly saturated, using the same colors for semantic purposes (error, notice, success) creates problems:

#### ❌ Problems with Reusing Vibrant Brand Colors:

1. **Visual Confusion**: Users can't distinguish between "this is our brand red" vs "this is an error"
2. **Overwhelming Alerts**: High-saturation error messages cause visual fatigue
3. **Accessibility Issues**: Vibrant colors may fail contrast requirements
4. **Emotional Mismatch**: Brand excitement ≠ error severity

#### ✅ Solution: Differentiate Through Lightness (L)

The key insight: **Adjust Lightness (L) in HSL to create distinct semantic colors while maintaining color relationships.**

### Strategy: The Lightness Ladder

Create visual separation by adjusting the L (lightness) value in HSL:

```
Brand Colors (High Saturation):
├─ Primary:   HSL(200, 100%, 50%)  ← Vibrant blue
├─ Secondary: HSL(40,  97%,  64%)  ← Bright gold
├─ Tertiary:  HSL(30,  100%, 48%)  ← Vivid orange
└─ Accent:    HSL(0,   69%,  50%)  ← Bold red

Semantic Colors (Adjusted Lightness):
├─ Error:   HSL(0,   47%, 47%)  ← Darker, muted red
├─ Notice:  HSL(47,  100%, 72%)  ← Lighter yellow (distinct from Secondary)
└─ Success: HSL(148, 50%, 18%)  ← Darker green
```

### Practical Example: "Fiery Ice Cream Delight" Palette

Based on a Coolors.co vibrant palette, here's how to create distinct semantic colors:

#### Starting Palette from Coolors.co:
```json
{
  "primary": "#003049",    // Deep Space Blue (H=201, S=100%, L=14%)
  "secondary": "#FCBF49",  // Sunflower Gold (H=40, S=97%, L=64%)
  "tertiary": "#F77F00",   // Princeton Orange (H=31, S=100%, L=48%)
  "accent": "#D62828"      // Flag Red (H=0, S=69%, L=50%)
}
```

#### Critical Insight: Don't Blindly Trust Palette Generators

**Palette generators like Coolors.co are starting points, not gospel.** They create aesthetically pleasing combinations but don't consider:
- Your specific use case (WordPress themes need semantic colors)
- Accessibility requirements (AAA contrast)
- Visual hierarchy (brand vs semantic distinction)
- Saturation consistency across your palette

**Think critically about the generated palette:**

1. **Question Saturation Inconsistencies**
   - Notice Accent has S=69% while Primary/Secondary/Tertiary have S=97-100%
   - ❌ Problem: Accent feels less vibrant, doesn't match brand energy
   - ✅ Solution: Increase Accent to S=100% for consistency

2. **Identify Missing Semantic Colors**
   - Coolors gave you 4 brand colors (blue, gold, orange, red)
   - ❌ Problem: No Notice, Error, or Success colors
   - ✅ Solution: You need to create these yourself

3. **Recognize Color Overlap Issues**
   - Secondary (gold) and Tertiary (orange) are both yellow-orange (H=40° and H=31°)
   - ❌ Problem: Where does Notice (yellow) fit? It will clash with both!
   - ✅ Solution: Adjust brand colors to create space for semantics

#### Problems with Using Coolors Palette Directly:

1. **Accent Saturation Too Low**:
   - Coolors: S=69% (muted compared to other brand colors)
   - Should be: S=100% (match the vibrant energy)

2. **No Semantic Color Strategy**:
   - ❌ Error = Accent (both vibrant red) → Confusing
   - ❌ Notice = Secondary (both bright yellow) → No distinction
   - ❌ Need complementary color for Notice to stand out

3. **Yellow-Orange Cluster**:
   - Secondary (H=40°) and Tertiary (H=31°) are too close
   - Leaves no room for Notice (typically yellow, H=50-60°)

#### Solution: Strategic Adjustments for Functionality

**Step 1: Make Tertiary Lighter**
```
Tertiary: HSL(30, 100%, 48%) → HSL(30, 100%, 86%)
Result: Lighter orange, creates space for Notice
```

**Step 2: Make Secondary Darker**
```
Secondary: HSL(40, 97%, 64%) → HSL(39, 97%, 15%)
Result: Darker gold, distinct from Notice
```

**Step 3: Position Notice Between Them**
```
Notice: HSL(47, 100%, 72%)
Result: Bright yellow, clearly different from both Secondary and Tertiary
```

**Step 4: Mute Error (Lower Saturation + Adjust Lightness)**
```
Error: HSL(0, 47%, 47%)
Result: Muted red, serious tone, distinct from vibrant Accent
```

**Step 5: Choose Notice Color Using Complementary Theory**

Since Secondary and Tertiary occupy the yellow-orange range (H=28-42°), use **color wheel complementary** to find Notice:

```
Yellow-Orange average: (28° + 42°) / 2 = 35°
Complementary (opposite): 35° + 180° = 215° (blue)
But Primary is already blue (H=201°)!

Alternative: Use split-complementary
35° ± 150° = 185° (cyan) or 245° (purple-blue)
Better choice: Purple-magenta at H=270 to 280°
```

**Why Purple/Magenta for Notice?**
- ✅ Complementary to yellow-orange (creates visual pop)
- ✅ Not in your brand palette (no confusion)
- ✅ Purple = "pay attention" (between calm and urgent)
- ✅ High saturation works (S=100%) since it's far from brand colors

```
Notice: HSL(274, 100%, 70%)
Result: Vibrant purple, clearly distinct from all brand colors
```

**Step 6: Increase Accent Saturation for Consistency**
```
Accent: HSL(0, 69%, 50%) → HSL(0, 100%, 50%)
Result: Matches the vibrancy of other brand colors
```

### How to Think About Adjusting Generated Palettes

When you get a palette from Coolors, Adobe Color, or any generator:

#### 1. **Check Saturation Consistency**
   - Are all brand colors similarly vibrant?
   - If one color has S=60% and others have S=100%, ask: "Is this intentional?"
   - **Action**: Adjust outliers to match the overall energy level

#### 2. **Map Out Hue Distribution**
   - Plot your colors on a mental color wheel
   - Look for gaps (opportunities) and clusters (conflicts)
   - **Action**: Spread colors to create distinct zones

#### 3. **Plan for Semantic Colors**
   - You need Error (red), Notice (yellow), Success (green)
   - Check if any brand colors occupy these hues
   - **Action**: Either adjust brand colors or choose different semantic hues

#### 4. **Use Complementary Colors Strategically**
   - If your brand is warm (red, orange, yellow), consider cool semantics (blue, purple, green)
   - If your brand is cool (blue, green), consider warm semantics (orange, yellow)
   - **Action**: Find complementary hues for Notice to make it stand out

#### 5. **Test in Your Generator**
   - Don't guess - use the HSL editor to experiment
   - Watch the validation messages
   - **Action**: Iterate until all bands generate successfully

### Real-World Decision Process

**Scenario**: You have Coolors palette with warm brand colors (blue, gold, orange, red)

**Question 1**: "Should I keep Accent at S=69%?"
- **Think**: Other brand colors are S=97-100%
- **Decide**: No, increase to S=100% for consistency
- **Why**: Vibrant palettes need uniform energy

**Question 2**: "Should Notice be the same as Secondary? Where should Notice go?"
- **Think**: Secondary (H=40°) and Tertiary (H=31°) are yellow-orange
- **Decide**: Use complementary - purple at H=270°
- **Why**: Maximum distinction from brand colors, no confusion

### Final Recommended Configuration

After applying all strategic adjustments:

```json
{
  // Brand Colors (High Saturation, Varied Lightness)
  "primary": "#003049",     // Deep Space Blue (H=201, S=100%, L=14%)
  "secondary": "#FCBF49",   // Sunflower Gold (H=40, S=97%, L=64%)
  "tertiary": "#F77F00",    // Princeton Orange (H=31, S=100%, L=48%)
  "accent": "#D62828",      // Flag Red (H=0, S=100%, L=50%) ← Increased from S=69%

  // Semantic Colors (Strategic Placement)
  "error": "#B84040",       // Muted Red (H=0, S=50%, L=48%)
  "notice": "#B366FF",      // Purple (H=274, S=100%, L=70%) ← Complementary!
  "success": "#2D8659"      // Muted Green (H=148, S=50%, L=35%)
}
```

**Key Changes from Coolors Original**:
1. ✅ **Accent S: 69% → 100%** (matches brand energy)
2. ✅ **Notice: Added purple at H=274°** (complementary to yellow-orange)
3. ✅ **Error: Muted to S=50%** (serious tone, distinct from vibrant Accent)

### Visual Hierarchy Guidelines

For vibrant brand palettes, follow this saturation hierarchy:

| Color Type | Saturation Range | Lightness Range | Purpose |
|------------|------------------|-----------------|----------|
| **Brand Colors** | 60-100% | 40-60% | Attract attention, express personality |
| **Semantic Colors** | 40-60% | 35-75% | Inform without overwhelming |
| **Text Colors** | 0-20% | 10-20% (light) or 90-97% (dark) | Maximum readability |
| **Backgrounds** | 0-10% | 95-100% (light) or 5-15% (dark) | Neutral canvas |

### Testing Your Palette

Use the generator's validation to ensure:

1. **Text Colors Pass AAA Contrast**:
   - text-on-light: Y ≤ 0.20 (dark enough)
   - text-on-dark: Y ≥ 0.85 (light enough)

2. **All Bands Generate Successfully**:
   - Each color should produce 3+ variations per band (lighter, light, dark, darker)
   - If validation fails, adjust L (lightness) in HSL editor

3. **Visual Distinction Test**:
   - View all colors side-by-side
   - Semantic colors should be clearly different from brand colors
   - No two colors should look identical at small sizes

### Common Mistakes to Avoid

❌ **Using Maximum Saturation Everywhere**
```json
// Too intense - everything screams for attention
{
  "error": "#FF0000",    // S=100%
  "notice": "#FFFF00",   // S=100%
  "success": "#00FF00"   // S=100%
}
```

✅ **Balanced Saturation Hierarchy**
```json
// Better - semantic colors are muted
{
  "error": "#B84040",    // S=50%
  "notice": "#D4A800",   // S=100% but darker L
  "success": "#2D8659"   // S=50%
}
```

❌ **Ignoring Lightness Differences**
```json
// All mid-range lightness - no hierarchy
{
  "primary": "HSL(200, 100%, 50%)",
  "secondary": "HSL(40, 100%, 50%)",
  "tertiary": "HSL(30, 100%, 50%)"
}
```

✅ **Strategic Lightness Variation**
```json
// Creates visual hierarchy through L values
{
  "primary": "HSL(200, 100%, 14%)",    // Very dark
  "secondary": "HSL(40, 97%, 64%)",    // Bright
  "tertiary": "HSL(30, 100%, 48%)"     // Medium
}
```

### HSL Editor Tips

When adjusting colors in the generator:

1. **Click the color swatch** to open the HSL editor
2. **To darken**: Decrease L (lightness) - slide left
3. **To lighten**: Increase L (lightness) - slide right
4. **To mute**: Decrease S (saturation) - slide left
5. **To shift hue**: Adjust H (hue) - slide to change color

**Pro Tip**: For semantic colors, adjust L first, then S if needed. Small L changes (5-10%) create noticeable differences.

### Psychology of Vibrant Palettes

Vibrant, high-saturation palettes evoke:
- **Energy** and **excitement**
- **Youth** and **playfulness**
- **Confidence** and **boldness**
- **Creativity** and **innovation**

Best for:
- Food and beverage brands
- Sports teams and athletic wear
- Youth-oriented products
- Festival and event marketing
- Creative agencies
- Entertainment and gaming

Avoid for:
- Financial services (needs trust/stability)
- Healthcare (needs calm/professionalism)
- Legal services (needs authority/seriousness)
- Luxury brands (needs sophistication/restraint)

### Real-World Example: Your Palette

Your successful configuration shows the strategy in action:

**Brand Colors** (Vibrant, High Saturation):
- Primary Lighter: HSL(260.8, 100%, 84.7%) - Bright blue
- Secondary Light: HSL(39.5, 96.9%, 62.2%) - Vibrant gold
- Tertiary Light: HSL(30.9, 100%, 86.7%) - Bright orange
- Accent Light: HSL(0.0, 68.2%, 91.4%) - Soft red

**Semantic Colors** (Muted, Adjusted Lightness):
- Notice Light: HSL(47.5, 100%, 72.7%) - Positioned between Secondary and Tertiary
- Error Light: HSL(0.0, 47.1%, 86.7%) - Lower saturation than Accent
- Success Light: HSL(150.0, 48.9%, 81.6%) - Distinct green

This creates a **cohesive yet functional** palette where brand colors attract attention while semantic colors inform without overwhelming.

### Summary: The Vibrant Palette Formula

1. **Brand colors**: High saturation (60-100%), varied lightness
2. **Semantic colors**: Moderate saturation (40-60%), strategic lightness placement
3. **Text colors**: Low saturation (0-20%), extreme lightness (very dark or very light)
4. **Use HSL editor**: Adjust L to create distinction, S to control intensity
5. **Test thoroughly**: Ensure AAA contrast and visual distinction

### Quick Reference: Critical Thinking Checklist

When you import a palette from Coolors, Adobe Color, or any generator, ask yourself:

#### ✅ Saturation Check
- [ ] Are all brand colors at similar saturation levels (within 10%)?
- [ ] If not, is the variation intentional or an oversight?
- [ ] Should I adjust outliers to match the overall energy?

#### ✅ Hue Distribution Check
- [ ] Are my brand colors spread across the color wheel?
- [ ] Do I have any hue clusters (colors within 20° of each other)?
- [ ] Where will my semantic colors (Error, Notice, Success) fit?

#### ✅ Semantic Color Planning
- [ ] Does any brand color occupy red (Error), yellow (Notice), or green (Success)?
- [ ] If yes, how will I differentiate semantic from brand?
- [ ] What hue should Notice be? (Hint: Use complementary to your dominant brand hue)

#### ✅ Complementary Strategy
- [ ] What's the average hue of my warm brand colors?
- [ ] What's the complementary hue (add 180°)?
- [ ] Can I use this for Notice to create maximum distinction?

#### ✅ Accessibility Validation
- [ ] Do my text colors pass AAA contrast (Y ≤ 0.20 for dark, Y ≥ 0.85 for light)?
- [ ] Does each color generate 3+ variations per band?
- [ ] Are semantic colors visually distinct at small sizes?

### Remember: You're the Designer

**Palette generators are tools, not authorities.** They don't know:
- Your WordPress theme needs semantic colors
- Your brand needs consistent saturation
- Your users need clear visual hierarchy
- Your accessibility requirements (AAA contrast)

**Trust your judgment:**
- If Accent feels less vibrant than Primary, increase its saturation
- If Notice looks too similar to Secondary, choose a complementary hue
- If the generator gives you 4 warm colors, add a cool semantic color
- If validation fails, adjust L (lightness) in the HSL editor

**The generator validates your choices** - use it to experiment and iterate until you have a palette that's both beautiful and functional.

By following these guidelines, you can create vibrant, energetic palettes that are both visually striking and functionally sound.
