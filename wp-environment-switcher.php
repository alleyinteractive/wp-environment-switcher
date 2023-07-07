<?php
/**
 * Plugin Name: WordPress Environment Switcher
 * Plugin URI: https://github.com/alleyinteractive/wp-environment-switcher
 * Description: Easily switch between different site environments from the WordPress admin bar.
 * Version: 0.1.0
 * Author: Sean Fisher
 * Author URI: https://github.com/alleyinteractive/wp-environment-switcher
 * Requires at least: 6.0
 * Tested up to: 6.2.2
 *
 * Text Domain: wp-environment-switcher
 *
 * @package wp-environment-switcher
 */

namespace Alley\WP\WordPress_Environment_Switcher;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Root directory to this plugin.
 */
define( 'WP_ENVIRONMENT_SWITCHER_DIR', __DIR__ );

// Load the plugin's main files.
require_once __DIR__ . '/src/meta.php';

/**
 * Instantiate the plugin.
 */
function main(): void {
	// ...
}
main();
