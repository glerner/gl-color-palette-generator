<?php
namespace GL_Color_Palette_Generator\Interfaces;

interface AdminInterface {
	public function register_menu_pages(): void;
	public function render_admin_page(): void;
	public function handle_form_submission(): void;
	public function get_admin_notices(): array;
}
