<?php
namespace GLColorPalette;

class GLColorPaletteManager {
    use GLColorPaletteDatabaseTables;

    /**
     * Save palette
     */
    public function save_palette($data) {
        global $wpdb;

        $table = $this->get_table('palettes');

        $result = $wpdb->insert(
            $table,
            [
                'name' => $data['name'],
                'colors' => json_encode($data['colors']),
                'metadata' => json_encode($data['metadata'] ?? [])
            ],
            ['%s', '%s', '%s']
        );

        if ($result) {
            / Log to history
            $this->log_history($wpdb->insert_id, $data);
        }

        return $result;
    }

    /**
     * Log history
     */
    private function log_history($palette_id, $data) {
        global $wpdb;

        $wpdb->insert(
            $this->get_table('history'),
            [
                'palette_id' => $palette_id,
                'changes' => json_encode($data),
                'user_id' => get_current_user_id()
            ],
            ['%d', '%s', '%d']
        );
    }
}
