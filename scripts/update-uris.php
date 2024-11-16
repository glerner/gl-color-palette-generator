<?php
/**
 * Update URIs Script
 * Run from plugin root: php scripts/update-uris.php
 */

$uris = [
    'old_plugin_uri' => [
        'https://website-tech.glerner.com/plugins/color-palette-generator',
        'https://glerner.com/wordpress/plugins/color-palette-generator'
    ],
    'new_plugin_uri' => 'https://github.com/GeorgeLerner/gl-color-palette-generator',

    'old_author_uri' => [
        'https://glerner.com',
        'https://website-tech.glerner.com'
    ],
    'new_author_uri' => 'https://website-tech.glerner.com/',

    'old_update_uri' => [
        'https://github.com/glerner/gl-color-palette-generator',
        'https://website-tech.glerner.com/plugins/color-palette-generator'
    ],
    'new_update_uri' => 'https://website-tech.glerner.com/plugins/color-palette-generator/'
];

$plugin_root = dirname(__DIR__);
$file_extensions = ['php', 'pot', 'txt', 'md'];

function updateFiles($dir, $uris, $extensions) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );

    $updates = 0;
    foreach ($files as $file) {
        if ($file->isFile() &&
            in_array($file->getExtension(), $extensions) &&
            !strpos($file->getPathname(), 'vendor/') &&
            !strpos($file->getPathname(), 'node_modules/')) {

            $content = file_get_contents($file->getPathname());
            $original = $content;

            // Replace old URIs with new ones
            foreach ($uris as $type => $uri_data) {
                if (is_array($uri_data['old_' . $type])) {
                    foreach ($uri_data['old_' . $type] as $old_uri) {
                        $content = str_replace(
                            $old_uri,
                            $uri_data['new_' . $type],
                            $content
                        );
                    }
                }
            }

            if ($content !== $original) {
                file_put_contents($file->getPathname(), $content);
                echo "Updated: " . $file->getPathname() . PHP_EOL;
                $updates++;
            }
        }
    }
    return $updates;
}

// Run the update
$total_updates = updateFiles($plugin_root, $uris, $file_extensions);
echo "Total files updated: " . $total_updates . PHP_EOL; 
