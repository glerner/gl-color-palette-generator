<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Preview Interface
 *
 * Defines the contract for generating previews of color palettes.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPalettePreview {
    /**
     * Generates UI preview.
     *
     * @param array $palette Palette to preview.
     * @param array $options {
     *     Optional. UI options.
     *     @type string $template      UI template.
     *     @type array  $components    UI components.
     *     @type array  $interactions  Interactive features.
     *     @type array  $metadata      Preview metadata.
     * }
     * @return array {
     *     UI results.
     *     @type string $preview       Preview content.
     *     @type array  $components    Component data.
     *     @type array  $assets        Required assets.
     *     @type array  $metadata      Preview metadata.
     * }
     */
    public function generate_ui_preview(array $palette, array $options = []): array;

    /**
     * Generates design preview.
     *
     * @param array $palette Palette to preview.
     * @param array $options {
     *     Optional. Design options.
     *     @type string $template      Design template.
     *     @type array  $elements      Design elements.
     *     @type array  $styles        Design styles.
     *     @type array  $metadata      Preview metadata.
     * }
     * @return array {
     *     Design results.
     *     @type string $preview       Preview content.
     *     @type array  $elements      Element data.
     *     @type array  $assets        Required assets.
     *     @type array  $metadata      Preview metadata.
     * }
     */
    public function generate_design_preview(array $palette, array $options = []): array;

    /**
     * Generates code preview.
     *
     * @param array $palette Palette to preview.
     * @param array $options {
     *     Optional. Code options.
     *     @type string $language      Code language.
     *     @type array  $format        Code format.
     *     @type array  $syntax        Syntax options.
     *     @type array  $metadata      Preview metadata.
     * }
     * @return array {
     *     Code results.
     *     @type string $preview       Preview content.
     *     @type array  $syntax        Syntax data.
     *     @type array  $assets        Required assets.
     *     @type array  $metadata      Preview metadata.
     * }
     */
    public function generate_code_preview(array $palette, array $options = []): array;

    /**
     * Generates mockup preview.
     *
     * @param array $palette Palette to preview.
     * @param array $options {
     *     Optional. Mockup options.
     *     @type string $template      Mockup template.
     *     @type array  $context       Mockup context.
     *     @type array  $devices       Device options.
     *     @type array  $metadata      Preview metadata.
     * }
     * @return array {
     *     Mockup results.
     *     @type string $preview       Preview content.
     *     @type array  $context       Context data.
     *     @type array  $assets        Required assets.
     *     @type array  $metadata      Preview metadata.
     * }
     */
    public function generate_mockup_preview(array $palette, array $options = []): array;
} 
