<?php
/**
 * Settings Manager Interface
 *
 * Defines the contract for managing plugin settings.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface SettingsManager
 *
 * Provides methods for managing plugin settings with proper validation and type handling.
 */
interface SettingsManager {
	/**
	 * Get a setting value by key
	 *
	 * @param string $key     Setting key to retrieve
	 * @param mixed  $default Default value if setting doesn't exist
	 * @return mixed Setting value or default if not found
	 * @throws \InvalidArgumentException If key is invalid
	 */
	public function get_setting( string $key, $default = null );

	/**
	 * Update a setting value
	 *
	 * @param string $key   Setting key to update
	 * @param mixed  $value New value to set
	 * @return bool True if update was successful
	 * @throws \InvalidArgumentException If key or value is invalid
	 * @throws \RuntimeException If update operation fails
	 */
	public function update_setting( string $key, $value ): bool;

	/**
	 * Delete a setting
	 *
	 * @param string $key Setting key to delete
	 * @return bool True if deletion was successful
	 * @throws \InvalidArgumentException If key is invalid
	 * @throws \RuntimeException If deletion operation fails
	 */
	public function delete_setting( string $key ): bool;

	/**
	 * Get all plugin settings
	 *
	 * @return array {
	 *     All plugin settings
	 *     @type mixed  $setting_key   Setting value
	 *     @type string $last_updated  Last update timestamp
	 *     @type string $updated_by    User who last updated the setting
	 *     @type bool   $is_protected  Whether setting is protected from deletion
	 * }
	 * @throws \RuntimeException If settings retrieval fails
	 */
	public function get_all_settings(): array;

	/**
	 * Validate a setting value
	 *
	 * @param string $key   Setting key to validate
	 * @param mixed  $value Value to validate
	 * @return bool True if value is valid for the given key
	 */
	public function validate_setting( string $key, $value ): bool;

	/**
	 * Get setting metadata
	 *
	 * @param string $key Setting key
	 * @return array {
	 *     Setting metadata
	 *     @type string $type          Setting data type
	 *     @type string $description   Setting description
	 *     @type array  $allowed_values Allowed values if applicable
	 *     @type bool   $is_required   Whether setting is required
	 *     @type mixed  $default_value Default value
	 * }
	 */
	public function get_setting_metadata( string $key ): array;
}
