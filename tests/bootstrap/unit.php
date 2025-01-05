<?php
/**
 * Bootstrap file for pure unit tests
 *
 * @package GL_Color_Palette_Generator
 */

use GL_Color_Palette_Generator\Tests\Unit\Test_Case;

echo "\n=== GL_Color_Palette_Generator Unit Testing Bootstrap ===\n";
echo "\n=== Phase 1: Environment Setup ===\n";

// Load environment variables
$env_path = dirname(__DIR__) . '/.env.local.testing';
if (file_exists($env_path)) {
    echo "Loading environment from: $env_path\n";
    $env_content = parse_ini_file($env_path);
    foreach ($env_content as $key => $value) {
        if (!getenv($key)) {
            putenv("$key=$value");
        }
    }
}

echo "\n=== Phase 2: Composer Autoloader ===\n";
echo "Loading composer autoloader\n";

if (!defined('GL_CPG_PLUGIN_DIR')) {
    echo "Defining GL_CPG_PLUGIN_DIR constant\n";
    define('GL_CPG_PLUGIN_DIR', dirname(__FILE__, 3).'/');
}
$test_vendor_path = GL_CPG_PLUGIN_DIR . 'vendor/autoload.php';
if (!file_exists($test_vendor_path)) {
    echo "ERROR: Test vendor file not found: $test_vendor_path\n";
    exit(1);
}
require_once $test_vendor_path;

echo "\n=== Phase 3: Test Base Classes Setup ===\n";
echo "Loading test base classes:\n";

// Only load the base test case
$test_case_path = GL_CPG_PLUGIN_DIR . 'tests/unit/class-test-case.php';
echo "Loading Test_Case from $test_case_path\n";
if (!file_exists($test_case_path)) {
    echo "ERROR: Test case file not found: $test_case_path\n";
    exit(1);
}
require_once $test_case_path;

$full_class = "GL_Color_Palette_Generator\\Tests\\Unit\\Test_Case";
if (!class_exists($full_class)) {
    echo "ERROR: Class $full_class not found\n";
    exit(1);
}
echo "Successfully loaded $full_class\n";
// Add backward compatibility for old namespace
class_alias('GL_Color_Palette_Generator\Tests\Unit\Test_Case', 'GL_Color_Palette_Generator\Tests\Test_Case');

echo "\n=== Bootstrap Complete ===\n";
