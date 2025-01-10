# CSS for Print Black Text on White

To ensure that all text prints as black on white paper for a WordPress website, you can add specific CSS rules to your print stylesheet. Here are the steps and CSS snippets to achieve this:

- Create or Update Print Stylesheet: Ensure you have a print stylesheet (print.css) or add print-specific styles to your existing stylesheet.
- Set Basic Print Styles: Use the following CSS to set the background to white and the text color to black:
```css
@media print {
    body {
        background: white !important;
        color: black !important;
    }
}```

- Remove Unnecessary Elements: Hide elements that are not needed for print, such as navigation bars, sidebars, or any decorative images. For example:
```css
@media print {
    nav, aside, footer {
        display: none !important;
    }
}
```

- Ensure Text is Readable: Set appropriate font sizes and styles to ensure text is readable on print. You can also adjust margins and padding to optimize the layout:
```css
@media print {
    body {
        margin: 1cm;
        font-size: 12pt;
        line-height: 1.5;
    }
}
```

- Manage Page Breaks: Control how content is split across pages to avoid breaking headings or important content:
```css
@media print {
    h1, h2, h3, h4, h5, h6 {
        page-break-after: avoid;
    }
}
```

- Invert Colors for Dark Backgrounds: If you have elements with dark backgrounds, you can invert their colors to white for printing:
```css
@media print {
    .dark-background {
        background: white !important;
        color: black !important;
    }
}
```

- Handle Images: Decide whether to hide or show images. If you choose to hide them, use:
```css
@media print {
    img {
        display: none !important;
    }
}
```

- Include Important Content: Ensure that critical content, such as headings and paragraphs, is visible and readable:
```css
@media print {
    h1, h2, h3, h4, h5, h6, p {
        display: block !important;
    }
}
```

By following these guidelines and incorporating the provided CSS snippets, you can ensure that your WordPress website prints with all text in black on white paper, making it more readable and efficient for print.
