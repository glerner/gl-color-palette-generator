<?php
/**
 * Color Palette Storage Class
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette;

use GLColorPalette\Interfaces\ColorPaletteStorageInterface;
use GLColorPalette\ColorPalette;
use wpdb;

/**
 * Handles database storage operations for color palettes.
 */
class ColorPaletteStorage implements ColorPaletteStorageInterface {
    /**
     * WordPress database instance.
     *
     * @var wpdb
     */
    private wpdb $wpdb;

    /**
     * Table name for palettes.
     *
     * @var string
     */
    private string $table_name;

    /**
     * Constructor.
     *
     * @param wpdb $wpdb WordPress database instance.
     */
    public function __construct(wpdb $wpdb) {
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'color_palettes';
    }

    /**
     * Saves a color palette.
     *
     * @param ColorPalette $palette Palette to save.
     * @return int|false Palette ID on success, false on failure.
     */
    public function savePalette(ColorPalette $palette): int|false {
        $data = [
            'name' => $palette->getName(),
            'colors' => json_encode($palette->getColors()),
            'metadata' => json_encode($palette->getMetadata()),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];

        $result = $this->wpdb->insert(
            $this->table_name,
            $data,
            ['%s', '%s', '%s', '%s', '%s']
        );

        if ($result === false) {
            return false;
        }

        return $this->wpdb->insert_id;
    }

    /**
     * Retrieves a color palette by ID.
     *
     * @param int $id Palette ID.
     * @return ColorPalette|null Retrieved palette or null if not found.
     */
    public function getPalette(int $id): ?ColorPalette {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$row) {
            return null;
        }

        return new ColorPalette([
            'name' => $row['name'],
            'colors' => json_decode($row['colors'], true),
            'metadata' => json_decode($row['metadata'], true)
        ]);
    }

    /**
     * Updates an existing color palette.
     *
     * @param int          $id      Palette ID.
     * @param ColorPalette $palette Updated palette data.
     * @return bool True on success, false on failure.
     */
    public function updatePalette(int $id, ColorPalette $palette): bool {
        $data = [
            'name' => $palette->getName(),
            'colors' => json_encode($palette->getColors()),
            'metadata' => json_encode($palette->getMetadata()),
            'updated_at' => current_time('mysql')
        ];

        $result = $this->wpdb->update(
            $this->table_name,
            $data,
            ['id' => $id],
            ['%s', '%s', '%s', '%s'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Deletes a color palette.
     *
     * @param int $id Palette ID.
     * @return bool True on success, false on failure.
     */
    public function deletePalette(int $id): bool {
        $result = $this->wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Lists all color palettes with optional filtering.
     *
     * @param array $filters Optional. Filter criteria.
     * @return array List of palettes.
     */
    public function listPalettes(array $filters = []): array {
        $query = "SELECT * FROM {$this->table_name}";
        $where = [];
        $params = [];

        if (!empty($filters['name'])) {
            $where[] = 'name LIKE %s';
            $params[] = '%' . $this->wpdb->esc_like($filters['name']) . '%';
        }

        if (!empty($filters['created_after'])) {
            $where[] = 'created_at >= %s';
            $params[] = $filters['created_after'];
        }

        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        if (!empty($filters['order_by'])) {
            $query .= ' ORDER BY ' . esc_sql($filters['order_by']);
            if (!empty($filters['order'])) {
                $query .= ' ' . esc_sql($filters['order']);
            }
        }

        if (!empty($filters['limit'])) {
            $query .= ' LIMIT %d';
            $params[] = (int) $filters['limit'];
        }

        if (!empty($params)) {
            $query = $this->wpdb->prepare($query, $params);
        }

        $rows = $this->wpdb->get_results($query, ARRAY_A);

        return array_map(function($row) {
            return new ColorPalette([
                'name' => $row['name'],
                'colors' => json_decode($row['colors'], true),
                'metadata' => json_decode($row['metadata'], true)
            ]);
        }, $rows);
    }

    /**
     * Searches for palettes matching criteria.
     *
     * @param array $criteria Search criteria.
     * @return array Matching palettes.
     */
    public function searchPalettes(array $criteria): array {
        $query = "SELECT * FROM {$this->table_name} WHERE 1=1";
        $params = [];

        if (!empty($criteria['color'])) {
            $query .= ' AND colors LIKE %s';
            $params[] = '%' . $this->wpdb->esc_like($criteria['color']) . '%';
        }

        if (!empty($criteria['metadata'])) {
            foreach ($criteria['metadata'] as $key => $value) {
                $query .= ' AND metadata LIKE %s';
                $params[] = '%' . $this->wpdb->esc_like(json_encode([$key => $value])) . '%';
            }
        }

        if (!empty($params)) {
            $query = $this->wpdb->prepare($query, $params);
        }

        $rows = $this->wpdb->get_results($query, ARRAY_A);

        return array_map(function($row) {
            return new ColorPalette([
                'name' => $row['name'],
                'colors' => json_decode($row['colors'], true),
                'metadata' => json_decode($row['metadata'], true)
            ]);
        }, $rows);
    }
} 
