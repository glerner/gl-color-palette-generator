Root cause • After the first “relative-shift” pass, two post-filters (ensureMinimumContrast + minSeparation loop) overwrite the computed L value independently for each swatch.
– If base L is already high (>0.55) the light swatch is pushed downward while the dark swatch is sometimes pushed upward → the numerically “dark” entry can end up lighter than the “light” entry.
– The code never re-sorts by final luminance, so the names stay attached to whatever value was calculated first.
• HSL “l” is only a crude proxy for perceived brightness; small saturation changes can move visual luminance in the opposite direction even when l is monotonic.
• Thresholds (0.10 / 0.90 clamps, 1.5 contrast rule) are arbitrary and can easily collide, forcing aggressive corrections that destroy the intended spacing.

Target luminance values that work (Using WCAG relative-luminance Y, not raw HSL l)
• lighter ≈ 0.90 Y → contrast vs #000 ≈ 12:1 (safe for white text on darker neighbouring shades)
• light ≈ 0.78 Y → contrast vs #000 ≈ 9 – 10:1, easily legible with black text
• original = whatever the user passes
• dark ≈ 0.38 Y → contrast vs #FFF ≈ 5:1
• darker ≈ 0.22 Y → contrast vs #FFF ≈ 8:1

(These five Y values give ≥ 0.25 luminance difference between neighbours which is enough for obvious visual separation.)

Robust long-term fix • Stop trying to “nudge” each variation individually; instead compute the whole scale in one shot then rename after sorting.

Convert base colour to OKLCH (or at minimum CIELAB) – this gives a perceptual Lightness (L*) channel.
Choose absolute target L* values as shown above.
– If base L* sits outside the 0.38–0.78 band, slide the entire template up or down so base stays in its original position but ordering remains lighter>light>base>dark>darker.
For every target L* create colour by keeping the original hue, keeping ≈ 80 % of the original chroma (chroma is what makes very pale colours look dirty at high L*).
When finished, sort by final Y before labelling so naming always matches brightness.
• Drop ensureMinimumContrast and minSeparation; they are no longer needed because the fixed template already guarantees
– ≥ 4.5 : 1 contrast against either #000 or #FFF for every swatch
– ≥ 0.25 ΔY between neighbours for good visual separation.
• Unit tests
– For 100 random base colours assert luminance(lighter) > light > base > dark > darker.
– Assert contrast(light, #000) ≥ 4.5 and contrast(dark, #FFF) ≥ 4.5.
– Assert |Y(i)–Y(i+1)| ≥ 0.25.
Migration steps • Replace generateShades with the template-driven OKLCH algorithm.
• Remove ensureMinimumContrast and generatedLightnesses logic.
• Add luminance-ordering tests in helpers/colorUtils.spec.tsx.
• Because OKLCH is native in modern browsers you can use CSS.color-mix(…) for the conversion or import a tiny utility (e.g. culori/oklch) – tree-shakes to <1 kB.

Result – “Light” variations will always be lighter than “dark” ones.
– Any light background will be light enough to keep black text AAA or AA compliant.
– Adjacent shades differ by at least 25 % luminance so they are clearly distinct.
– Algorithm is simpler, deterministic, and no longer relies on ad-hoc clamps that break in edge cases.
