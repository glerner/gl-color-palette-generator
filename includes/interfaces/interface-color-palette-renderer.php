<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Renderer Interface
 *
 * Defines the contract for rendering color palettes in various formats.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteRenderer {
    /**
     * Renders palette as HTML.
     *
     * @param array $palette Palette to render.
     * @param array $options {
     *     Optional. HTML options.
     *     @type string $template      HTML template.
     *     @type array  $classes       CSS classes.
     *     @type array  $attributes    HTML attributes.
     *     @type array  $interactions  Interactive features.
     *     @type array  $accessibility A11y options.
     * }
     * @return array {
     *     HTML render results.
     *     @type string $html          Rendered HTML.
     *     @type array  $assets        Required assets.
     *     @type array  $interactions  Interaction data.
     *     @type array  $metadata      Render metadata.
     * }
     */
    public function render_as_html(array $palette, array $options = []): array;

    /**
     * Renders palette as image.
     *
     * @param array $palette Palette to render.
     * @param array $options {
     *     Optional. Image options.
     *     @type string $format        Image format.
     *     @type array  $dimensions    Image size.
     *     @type array  $layout        Color layout.
     *     @type array  $effects       Visual effects.
     *     @type array  $optimization  Image optimization.
     * }
     * @return array {
     *     Image render results.
     *     @type string $image         Image data/path.
     *     @type array  $dimensions    Image dimensions.
     *     @type array  $format        Image format info.
     *     @type array  $metadata      Render metadata.
     * }
     */
    public function render_as_image(array $palette, array $options = []): array;

    /**
     * Renders palette preview.
     *
     * @param array $palette Palette to preview.
     * @param array $options {
     *     Optional. Preview options.
     *     @type string $type          Preview type.
     *     @type array  $context       Preview context.
     *     @type array  $elements      Preview elements.
     *     @type array  $interactions  Interactive features.
     * }
     * @return array {
     *     Preview results.
     *     @type string $preview       Preview content.
     *     @type array  $context       Context data.
     *     @type array  $interactions  Interaction data.
     *     @type array  $metadata      Preview metadata.
     * }
     */
    public function render_preview(array $palette, array $options = []): array;

    /**
     * Renders palette documentation.
     *
     * @param array $palette Palette to document.
     * @param array $options {
     *     Optional. Documentation options.
     *     @type string $format        Doc format.
     *     @type array  $sections      Doc sections.
     *     @type array  $examples      Usage examples.
     *     @type array  $metadata      Doc metadata.
     * }
     * @return array {
     *     Documentation results.
     *     @type string $documentation Generated docs.
     *     @type array  $sections      Section content.
     *     @type array  $examples      Example content.
     *     @type array  $metadata      Doc metadata.
     * }
     */
    public function render_documentation(array $palette, array $options = []): array;
} 
