<?php
/**
 * Plugin Name: WordPress Environment Switcher
 * Plugin URI: https://github.com/alleyinteractive/wp-environment-switcher
 * Description: Easily switch between different site environments from the WordPress admin bar.
 * Version: 1.0.0
 * Author: Sean Fisher
 * Author URI: https://github.com/alleyinteractive/wp-environment-switcher
 * Requires at least: 5.5.0
 * Tested up to: 6.2
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
 * Instantiate the plugin.
 */
function main(): void {
	add_action( 'admin_bar_menu', __NAMESPACE__ . '\\register_admin_bar', 300 );
	add_action( 'wp_before_admin_bar_render', __NAMESPACE__ . '\\add_switcher_css' );
}
main();

/**
 * Retrieve all the available environments for the switcher.
 *
 * @return array<string, string>
 */
function get_environments(): array {
	return (array) apply_filters( 'wp_environment_switcher_environments', [] );
}

/**
 * Translate the current request path to a different host.
 *
 * Used to translate www.example.org/the/path to staging.example.org/the/path
 * for switching environments with ease.
 *
 * @param string $environment_url The new base URL.
 * @return string
 */
function get_translated_url( string $environment_url ): string {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return $environment_url;
	}

	return rtrim( $environment_url, '/' ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
}

/**
 * Register the admin environment switcher in the admin bar.
 */
function register_admin_bar(): void {
	$environments = get_environments();

	if ( empty( $environments ) ) {
		return;
	}

	$current = wp_get_environment_type();

	// Bail if we can't determine the current environment.
	if ( empty( $current ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			esc_html__( 'The current environment could not be determined.', 'wp-environment-switcher' ),
			'0.1.0'
		);

		return;
	}

	// Fire a warning if the current environment is not in the list of environments.
	if ( ! isset( $environments[ $current ] ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			sprintf(
				/* translators: %s is the current environment */
				esc_html__( 'The current environment (%s) is not in the list of environments.', 'wp-environment-switcher' ),
				esc_html( $current )
			),
			'0.1.0'
		);
	}

	global $wp_admin_bar;

	$wp_admin_bar->add_menu(
		[
			'id'     => 'wp-environment-switcher',
			'title'  => ucwords( $current ),
			'href'   => '#',
			'parent' => 'top-secondary',
			'meta'   => [
				'class' => 'wp-environment-switcher',
			],
		]
	);

	/**
	 * Filter the method used to translate the URL to the new environment.
	 *
	 * @param callable $callback The callback to use to translate the URL.
	 */
	$callback = apply_filters( 'wp_environment_switcher_url_translation', __NAMESPACE__ . '\\get_translated_url' );

	// Fire a warning if the translation callback is not callable.
	if ( ! is_callable( $callback ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			esc_html__( 'The URL translation callback is not callable.', 'wp-environment-switcher' ),
			'0.1.0'
		);

		// Reverse the callback to the default.
		$callback = __NAMESPACE__ . '\\get_translated_url';
	}

	foreach ( $environments as $environment => $url ) {
		$wp_admin_bar->add_menu(
			[
				'id'     => 'wp-environment-switcher-' . $environment,
				'parent' => 'wp-environment-switcher',
				'title'  => ucwords( $environment ),
				'href'   => $callback( $url ),
				'meta'   => [
					'class' => 'wp-environment-switcher__item ' . ( $environment === $current ? 'wp-environment-switcher__item--active' : '' ),
				],
			]
		);
	}
}

/**
 * Add CSS to support the environment switcher.
 */
function add_switcher_css(): void {
	if ( empty( get_environments() ) ) {
		return;
	}

	?>
	<style>
		#wpadminbar #wp-admin-bar-wp-environment-switcher > .ab-item:before {
			content: "\f177";
			top: 2px;
		}

		<?php
		/**
		 * Filter whether to warn the user when they are on production.
		 *
		 * @param bool $warn_production Whether to warn the user when they are on production. Defaults to true when on production.
		 */
		if ( apply_filters( 'wp_environment_switcher_warn_production', 'production' === wp_get_environment_type() ) ) {
			?>
				#wpadminbar #wp-admin-bar-wp-environment-switcher:not(.hover) > .ab-item {
					background: #d63638;
				}
			<?php
		}
		?>
	</style>
	<?php
}
