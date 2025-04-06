<?php
// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options
delete_option( 'gl_color_palette_settings' );

// Delete any transients
delete_transient( 'gl_color_palette_cache' );
