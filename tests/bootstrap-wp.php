<?php
/**
 * PHPUnit bootstrap file
 *
 * @package GL_Color_Palette_Generator
 */

// Load composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load our plugin's autoloader
require_once dirname(__DIR__) . '/includes/system/class-autoloader.php';

// Load test base classes
require_once __DIR__ . '/integration/test-provider-integration.php';
