<?php
namespace GL_Color_Palette_Generator;

class FileHandler {
	protected $base_theme;
	protected $upload_dir;
	protected $theme_path;
	protected $styles_path;

	public function __construct( $base_theme = 'twentytwentyfour' ) {
		$this->base_theme  = $base_theme;
		$this->upload_dir  = wp_upload_dir();
		$this->theme_path  = get_theme_root() . '/' . $this->base_theme;
		$this->styles_path = $this->theme_path . '/styles';

		// Create necessary directories
		$this->init_directories();
	}

	/**
	 * Initialize necessary directories
	 */
	private function init_directories() {
		// Create temporary working directory in uploads
		$temp_dir = $this->upload_dir['basedir'] . '/color-palette-temp';
		if ( ! file_exists( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );
		}

		// Create styles directory if it doesn't exist
		if ( ! file_exists( $this->styles_path ) ) {
			wp_mkdir_p( $this->styles_path );
		}

		// Add .htaccess to protect temp directory
		$htaccess = $temp_dir . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, 'Deny from all' );
		}
	}

	/**
	 * Save theme.json file
	 */
	public function save_theme_json( $data ) {
		$theme_json_path = $this->theme_path . '/theme.json';

		try {
			// Backup existing theme.json if it exists
			if ( file_exists( $theme_json_path ) ) {
				$backup_path = $theme_json_path . '.backup-' . date( 'Y-m-d-His' );
				copy( $theme_json_path, $backup_path );
			}

			// Save new theme.json
			$result = file_put_contents(
				$theme_json_path,
				json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
			);

			if ( $result === false ) {
				throw new Exception( 'Failed to save theme.json file' );
			}
		} catch ( Exception $e ) {
			throw new Exception( 'Error saving theme.json file: ' . $e->getMessage() );
		}
	}

	/**
	 * Handle file operations
	 */
	public function handle_file_operation( $operation, $file_data ) {
		switch ( $operation ) {
			case 'upload':
				return $this->handle_file_upload( $file_data );
			case 'download':
				return $this->handle_file_download( $file_data );
			case 'delete':
				return $this->handle_file_deletion( $file_data );
			case 'update':
				return $this->handle_file_update( $file_data );
			default:
				throw new Exception( "Unsupported file operation: {$operation}" );
		}
	}

	/**
	 * Manage temporary files
	 */
	public function manage_temp_files() {
		$temp_dir      = $this->get_temp_directory();
		$expired_files = $this->find_expired_temp_files();

		foreach ( $expired_files as $file ) {
			$this->delete_temp_file( $file );
		}

		return array(
			'cleaned_files'        => count( $expired_files ),
			'remaining_files'      => $this->count_temp_files(),
			'disk_space_recovered' => $this->calculate_recovered_space(),
			'next_cleanup'         => $this->schedule_next_cleanup(),
		);
	}

	/**
	 * Process file exports
	 */
	public function process_file_export( $data, $format ) {
		$export_path = $this->get_export_directory();
		$filename    = $this->generate_export_filename( $format );

		switch ( $format ) {
			case 'pdf':
				return $this->export_as_pdf( $data, $filename );
			case 'json':
				return $this->export_as_json( $data, $filename );
			case 'csv':
				return $this->export_as_csv( $data, $filename );
			case 'xml':
				return $this->export_as_xml( $data, $filename );
			default:
				throw new Exception( "Unsupported export format: {$format}" );
		}
	}
}
