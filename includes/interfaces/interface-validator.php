<?php

namespace GL_Color_Palette_Generator\Interfaces;

interface Validator {
	public function validate( $data, array $rules ): bool;
	public function get_validation_errors(): array;
	public function get_validation_rules(): array;
}
