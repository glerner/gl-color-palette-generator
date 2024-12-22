# Theme Color Usage Guide

This guide helps theme developers effectively use the color palette in their WordPress themes, ensuring both visual appeal and accessibility.

## Color Roles and Their Uses

### Primary Colors
- **Primary (`primary`)**: Main brand color
  - Primary buttons
  - Key interactive elements
  - Brand accents
  - WCAG Requirements: AA contrast (4.5:1) with background

### Secondary Colors
- **Secondary (`secondary`)**: Supporting brand color
  - Secondary buttons
  - Highlights
  - Section backgrounds
  - WCAG Requirements: AA contrast (4.5:1) with text

### Accent Colors
- **Accent (`accent`)**: Highlight color
  - Call-to-action buttons
  - Important notifications
  - Highlighted content
  - WCAG Requirements: AA contrast (4.5:1) with text

### Base Colors
- **Base (`base`)**: Page background
  - Main content background
  - Article backgrounds
  - WCAG Requirements: Sufficient contrast with text colors

### Neutral Colors
Neutral colors form the foundation of your UI. They're automatically generated with subtle hints of your primary color.

#### Text Colors
- **Primary Text (`neutral-700`)**
  - Body text
  - WCAG Requirements: AAA contrast (7:1) with background
  
- **Secondary Text (`neutral-600`)**
  - Subheadings
  - Secondary content
  - WCAG Requirements: AA contrast (4.5:1) with background

- **Subtle Text (`neutral-500`)**
  - Meta information
  - Captions
  - WCAG Requirements: AA contrast (4.5:1) with background

#### UI Elements
- **Borders (`neutral-300`)**
  - Table borders
  - Dividers
  - Form input borders
  - WCAG Requirements: 3:1 contrast with adjacent colors

- **Backgrounds (`neutral-100`, `neutral-200`)**
  - Card backgrounds
  - Alternative table rows
  - Form inputs
  - WCAG Requirements: Sufficient contrast with text

### Interactive Elements

#### Buttons
```css
.button-primary {
    background: var(--wp--preset--color--primary);
    color: var(--wp--preset--color--base);
}

.button-primary:hover {
    background: var(--wp--preset--color--primary-dark);
}

.button-secondary {
    background: var(--wp--preset--color--secondary);
    color: var(--wp--preset--color--base);
}
```

#### Links
```css
a {
    color: var(--wp--preset--color--primary);
}

a:hover {
    color: var(--wp--preset--color--primary-dark);
}

a.subtle {
    color: var(--wp--preset--color--neutral-600);
}
```

#### Forms
```css
input, textarea {
    border-color: var(--wp--preset--color--neutral-300);
    background: var(--wp--preset--color--neutral-100);
    color: var(--wp--preset--color--neutral-700);
}

input:focus {
    border-color: var(--wp--preset--color--primary);
}
```

### Common UI Patterns

#### Headers and Navigation
```css
.site-header {
    background: var(--wp--preset--color--base);
    border-bottom: 1px solid var(--wp--preset--color--neutral-200);
}

.nav-menu {
    color: var(--wp--preset--color--neutral-700);
}

.nav-menu a:hover {
    color: var(--wp--preset--color--primary);
}
```

#### Content Areas
```css
.entry-title {
    color: var(--wp--preset--color--neutral-800);
}

.entry-meta {
    color: var(--wp--preset--color--neutral-500);
}

.entry-content {
    color: var(--wp--preset--color--neutral-700);
}
```

#### Cards and Sections
```css
.card {
    background: var(--wp--preset--color--neutral-100);
    border: 1px solid var(--wp--preset--color--neutral-200);
}

.featured-section {
    background: var(--wp--preset--color--primary-lighter);
    color: var(--wp--preset--color--neutral-800);
}
```

## Accessibility Guidelines

### Text Contrast Requirements
- **Large Text** (18pt+ or 14pt+ bold):
  - Minimum: 3:1 (WCAG AA)
  - Recommended: 4.5:1 (WCAG AAA)

- **Regular Text**:
  - Minimum: 4.5:1 (WCAG AA)
  - Recommended: 7:1 (WCAG AAA)

### Interactive Elements
- **Focus Indicators**: Must have 3:1 contrast with adjacent colors
- **Hover States**: Should maintain required contrast ratios
- **Buttons**: Text must maintain contrast requirements with button background

### Color Independence
- Don't rely solely on color to convey information
- Use icons, patterns, or text labels alongside color
- Ensure functionality is clear in grayscale

## Best Practices

1. **Consistent Usage**
   - Use color roles consistently across your theme
   - Don't repurpose colors for different semantic meanings

2. **Hierarchy**
   - Use color to reinforce visual hierarchy
   - Primary color for main actions
   - Secondary/accent colors for supporting elements

3. **White Space**
   - Use neutral backgrounds to create breathing room
   - Don't overwhelm with too many colored elements

4. **Dark Mode**
   - Colors automatically adjust for dark mode
   - Test both light and dark variations

5. **Gradients**
   - Use `neutral-gradient-*` colors for smooth transitions
   - Maintain contrast requirements throughout gradient

## Testing

1. **Contrast Checker**
   - Use the built-in contrast checker
   - Test all text/background combinations

2. **Color Blindness**
   - Test with color blindness simulators
   - Ensure information is clear in all modes

3. **Device Testing**
   - Test on multiple screens/devices
   - Check both indoor and outdoor visibility
