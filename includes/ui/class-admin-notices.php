<?php
namespace GL_Color_Palette_Generator;

class GL_Color_Palette_GeneratorAdminNotices {
	/**
	 * Show deletion notice
	 */
	public function show_deletion_notice() {
		if ( isset( $_GET['gl_plugin_deleted'] ) ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					_e( 'GL Color Palette Generator has been deleted. ', 'gl-color-palette-generator' );
					if ( get_transient( 'gl_color_palette_export_file' ) ) {
						$export_url = str_replace(
							wp_upload_dir()['basedir'],
							wp_upload_dir()['baseurl'],
							get_transient( 'gl_color_palette_export_file' )
						);
						printf(
							__( 'Your data backup is available for download: %s', 'gl-color-palette-generator' ),
							'<a href="' . esc_url( $export_url ) . '">' . __( 'Download Backup', 'gl-color-palette-generator' ) . '</a>'
						);
					}
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Register admin notices
	 */
	public function register_notices() {
		add_action( 'admin_notices', array( $this, 'display_notices' ) );
		add_action( 'wp_ajax_dismiss_palette_notice', array( $this, 'handle_notice_dismissal' ) );
	}

	/**
	 * Add notice
	 */
	public function add_notice( $message, $type = 'info', $dismissible = true, $duration = null ) {
		$notice = array(
			'message'     => $message,
			'type'        => $type,
			'dismissible' => $dismissible,
			'duration'    => $duration,
			'created_at'  => current_time( 'mysql' ),
		);

		$notices   = get_option( 'color_palette_notices', array() );
		$notices[] = $notice;
		update_option( 'color_palette_notices', $notices );

		return array(
			'notice_id' => count( $notices ) - 1,
			'status'    => 'added',
		);
	}

	/**
	 * Display notices
	 */
	public function display_notices() {
		$notices   = get_option( 'color_palette_notices', array() );
		$user_id   = get_current_user_id();
		$dismissed = get_user_meta( $user_id, 'dismissed_palette_notices', true ) ?: array();

		foreach ( $notices as $id => $notice ) {
			if ( in_array( $id, $dismissed ) ) {
				continue;
			}

			if ( $notice['duration'] && $this->is_notice_expired( $notice ) ) {
				continue;
			}

			$this->render_notice( $id, $notice );
		}
	}

	/**
	 * Handle notice dismissal
	 */
	public function handle_notice_dismissal() {
		check_ajax_referer( 'palette_notice_dismissal', 'nonce' );

		$notice_id = intval( $_POST['notice_id'] );
		$user_id   = get_current_user_id();

		$dismissed   = get_user_meta( $user_id, 'dismissed_palette_notices', true ) ?: array();
		$dismissed[] = $notice_id;

		update_user_meta( $user_id, 'dismissed_palette_notices', $dismissed );

		wp_send_json_success(
			array(
				'message'   => 'Notice dismissed successfully',
				'notice_id' => $notice_id,
			)
		);
	}
}
