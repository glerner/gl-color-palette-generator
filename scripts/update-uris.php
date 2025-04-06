<?php
/**
 * Update URIs Script
 * Run from plugin root: php scripts/update-uris.php
 */

$uris = array(
	'plugin_uri' => array(
		'old' => array(
			'https://github.com/GeorgeLerner/gl-color-palette-generator',
			'https://github.com/GeorgeLerner/gl-color-palette-generator',
			'https://github.com/GeorgeLerner/gl-color-palette-generator',
		),
		'new' => 'https://github.com/GeorgeLerner/gl-color-palette-generator',
	),

	'author_uri' => array(
		'old' => array(
			'https://website-tech.glerner.com/',
			'https://website-tech.glerner.com/',
			'https://website-tech.glerner.com/',
		),
		'new' => 'https://website-tech.glerner.com/',
	),

	'update_uri' => array(
		'old' => array(
			'https://website-tech.glerner.com/plugins/color-palette-generator/',
			'https://github.com/GeorgeLerner/gl-color-palette-generator/',
			'https://github.com/GeorgeLerner/gl-color-palette-generator',
		),
		'new' => 'https://github.com/GeorgeLerner/gl-color-palette-generator/',
	),
);

$plugin_root     = dirname( __DIR__ );
$file_extensions = array( 'php', 'pot', 'txt', 'md' );

function normalizeUrl( $url ) {
	return preg_replace( '#([^:])/+#', '$1/', $url );
}

function updateFiles( $dir, $uris, $extensions ) {
	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $dir )
	);

	$updates = 0;
	foreach ( $files as $file ) {
		if ( $file->isFile() &&
			in_array( $file->getExtension(), $extensions ) &&
			! strpos( $file->getPathname(), 'vendor/' ) &&
			! strpos( $file->getPathname(), 'node_modules/' ) ) {

			$content  = file_get_contents( $file->getPathname() );
			$original = $content;

			// Replace old URIs with new ones
			foreach ( $uris as $type => $uri_data ) {
				foreach ( $uri_data['old'] as $old_uri ) {
					$content = str_replace(
						$old_uri,
						normalizeUrl( $uri_data['new'] ),
						$content
					);
				}
			}

			// Additional pass to fix any remaining double slashes
			$content = preg_replace( '#([^:])/+#', '$1/', $content );

			if ( $content !== $original ) {
				file_put_contents( $file->getPathname(), $content );
				echo 'Updated: ' . $file->getPathname() . PHP_EOL;
				++$updates;
			}
		}
	}
	return $updates;
}

// Run the update
$total_updates = updateFiles( $plugin_root, $uris, $file_extensions );
echo 'Total files updated: ' . $total_updates . PHP_EOL;
