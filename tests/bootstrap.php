<?php
/**
 * WordPress Environment Switcher Tests: Bootstrap
 *
 * @package wp-environment-switcher
 */

/**
 * Visit {@see https://mantle.alley.com/testing/test-framework.html} to learn more.
 */
\Mantle\Testing\manager()
	->maybe_rsync_plugin()
	->with_sqlite()
	// Load the main file of the plugin.
	->loaded( fn () => require_once __DIR__ . '/../wp-environment-switcher.php' )
	->install();
