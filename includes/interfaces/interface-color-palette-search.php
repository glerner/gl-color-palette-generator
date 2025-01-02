<?php

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Color Palette Search Interface
 *
 * Defines the contract for searching and filtering color palettes.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteSearch {
    /**
     * Searches palettes by criteria.
     *
     * @param array $criteria {
     *     Search criteria.
     *     @type array  $colors        Colors to search for.
     *     @type array  $tags          Tags to filter by.
     *     @type string $name          Palette name search.
     *     @type array  $metadata      Metadata filters.
     *     @type array  $date_range    Creation date range.
     *     @type array  $sort          Sort parameters.
     *     @type int    $limit         Maximum results.
     *     @type int    $offset        Results offset.
     * }
     * @return array {
     *     Search results.
     *     @type array  $palettes      Matching palettes.
     *     @type int    $total         Total matches found.
     *     @type array  $facets        Search facets.
     *     @type array  $metadata      Search metadata.
     * }
     */
    public function search(array $criteria): array;

    /**
     * Finds similar palettes.
     *
     * @param string $palette_id Reference palette ID.
     * @param array $options {
     *     Optional. Similarity options.
     *     @type float  $threshold     Similarity threshold.
     *     @type array  $aspects       Aspects to compare.
     *     @type int    $limit         Maximum results.
     *     @type bool   $include_score Include similarity scores.
     * }
     * @return array {
     *     Similar palettes results.
     *     @type array  $matches       Similar palettes.
     *     @type array  $scores        Similarity scores.
     *     @type array  $analysis      Similarity analysis.
     *     @type array  $metadata      Match metadata.
     * }
     */
    public function find_similar(string $palette_id, array $options = []): array;

    /**
     * Suggests palettes based on criteria.
     *
     * @param array $preferences {
     *     Suggestion preferences.
     *     @type array  $colors        Preferred colors.
     *     @type array  $style         Style preferences.
     *     @type string $context       Usage context.
     *     @type array  $constraints   Design constraints.
     * }
     * @return array {
     *     Suggestion results.
     *     @type array  $suggestions   Suggested palettes.
     *     @type array  $reasoning     Suggestion reasoning.
     *     @type array  $alternatives  Alternative options.
     *     @type array  $metadata      Suggestion metadata.
     * }
     */
    public function suggest(array $preferences): array;

    /**
     * Indexes palette for search.
     *
     * @param array $palette Palette to index.
     * @param array $options {
     *     Optional. Indexing options.
     *     @type bool   $async         Async indexing.
     *     @type array  $fields        Fields to index.
     *     @type string $priority      Indexing priority.
     *     @type array  $analyzers     Custom analyzers.
     * }
     * @return array {
     *     Indexing results.
     *     @type string $index_id      Index identifier.
     *     @type bool   $indexed       Whether palette was indexed.
     *     @type array  $status        Indexing status.
     *     @type array  $metadata      Index metadata.
     * }
     */
    public function index(array $palette, array $options = []): array;
} 
