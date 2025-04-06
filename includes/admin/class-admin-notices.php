<?php
/**
 * Admin Notices Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Admin
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Admin;

use GL_Color_Palette_Generator\Core\Abstract_Component;

/**
 * Class Admin_Notices
 *
 * Handles admin notices for the plugin
 */
class Admin_Notices extends Abstract_Component {
	/**
	 * Array of notices
	 *
	 * @var array
	 */
	private array $notices = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->set_name( 'admin_notices' );
		$this->set_version( '1.0.0' );
	}

	/**
	 * Initialize the component
	 *
	 * @return bool True if initialization was successful
	 */
	public function init(): bool {
		add_action( 'admin_notices', array( $this, 'display_notices' ) );
		return true;
	}

	/**
	 * Add a notice
	 *
	 * @param string $message Notice message
	 * @param string $type    Notice type (error, warning, success, info)
	 */
	public function add_notice( string $message, string $type = 'info' ): void {
		$this->notices[] = array(
			'message' => $message,
			'type'    => $type,
		);
	}

	/**
	 * Display all notices
	 */
	public function display_notices(): void {
		foreach ( $this->notices as $notice ) {
			printf(
				'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
				esc_attr( $notice['type'] ),
				wp_kses_post( $notice['message'] )
			);
		}
		$this->notices = array();
	}
}
