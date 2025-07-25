<?php
/**
 * Plugin Name: WordPress Environment Switcher
 * Plugin URI: https://github.com/alleyinteractive/wp-environment-switcher
 * Description: Easily switch between different site environments from the WordPress admin bar.
 * Version: 1.2.1
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

use WP_Admin_Bar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Instantiate the plugin.
 */
function main(): void {
	add_action( 'admin_bar_menu', __NAMESPACE__ . '\\register_admin_bar', 300 );
	add_action( 'wp_before_admin_bar_render', __NAMESPACE__ . '\\add_switcher_css' );
	add_filter( 'map_meta_cap', __NAMESPACE__ . '\\map_meta_cap', 10, 2 );
}
main();

/**
 * Retrieve all the available environments for the switcher.
 *
 * @return list<array{type?: string, url?: string, label?: string}>
 */
function get_environments(): array {
	$environments = (array) apply_filters( 'wp_environment_switcher_environments', [] );

	// Determine if the environments are a key-value pair or an array of
	// associative arrays. Key-value pairs are of the form of 'environment' => 'url',
	// while associative arrays are of the form of
	// [ 'type' => 'environment', 'url' => 'url', 'label' => 'Label' ].
	$is_key_value = ! array_is_list( $environments );

	// Shape key-value pairs into associative arrays for easier handling afterwards.
	if ( $is_key_value ) {
		// @phpstan-ignore argument.type
		$environments = array_map( static function ( string $type, string $url ): array {
			return [
				'type'  => $type,
				'url'   => $url,
				'label' => ucwords( $type ),
			];
		}, array_keys( $environments ), $environments );
	}

	return $environments; // @phpstan-ignore return.type
}

/**
 * Retrieve the current environment name.
 *
 * Will attempt to infer the environment from the hosting provider and fallback
 * to the WP_ENVIRONMENT_TYPE constant.
 *
 * @return string
 */
function get_current_environment(): string {
	$default = match ( true ) {
		// @phpstan-ignore-next-line cast.string
		! empty( $_ENV['PANTHEON_ENVIRONMENT'] ) => (string) $_ENV['PANTHEON_ENVIRONMENT'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		// @phpstan-ignore-next-line cast.string
		defined( 'VIP_GO_APP_ENVIRONMENT' ) => (string) VIP_GO_APP_ENVIRONMENT,
		default => (string) wp_get_environment_type(),
	};

	/**
	 * Filter the current environment name.
	 *
	 * @param string $default The current environment.
	 */
	return (string) apply_filters( 'wp_environment_switcher_current_environment', $default );
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

	return rtrim( $environment_url, '/' ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ); // @phpstan-ignore-line argument.type
}

/**
 * Register the admin environment switcher in the admin bar.
 *
 * @param WP_Admin_Bar $wp_admin_bar The admin bar instance.
 */
function register_admin_bar( WP_Admin_Bar $wp_admin_bar ): void {
	// Check if the user has permission to view the switcher.
	if ( ! current_user_can( 'view_environment_switcher' ) ) {
		return;
	}

	$environments = get_environments();

	if ( empty( $environments ) ) {
		return;
	}

	$current_type = get_current_environment();

	// Bail if we can't determine the current environment.
	if ( empty( $current_type ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			esc_html__( 'The current environment could not be determined.', 'wp-environment-switcher' ),
			'0.1.0'
		);

		return;
	}

	// Fire a warning if the current environment is not in the list of environments.
	if ( ! in_array( $current_type, array_column( $environments, 'type' ), true ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			sprintf(
				/* translators: %s is the current environment */
				esc_html__( 'The current environment (%s) is not in the list of environments.', 'wp-environment-switcher' ),
				esc_html( $current_type )
			),
			'0.1.0'
		);
	}

	$current_environment = array_values( array_filter(
		$environments,
		static function ( array $environment ) use ( $current_type ): bool {
			return ( $environment['type'] ?? null ) === $current_type;
		}
	) ) [0];

	$wp_admin_bar->add_menu(
		[
			'id'     => 'wp-environment-switcher',
			'title'  => $current_environment['label'] ?? ucwords( $current_type ),
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
	if ( ! is_callable( $callback ) ) { // @phpstan-ignore-line function.alreadyNarrowedType
		_doing_it_wrong(
			__FUNCTION__,
			esc_html__( 'The URL translation callback is not callable.', 'wp-environment-switcher' ),
			'0.1.0'
		);

		// Reverse the callback to the default.
		$callback = __NAMESPACE__ . '\\get_translated_url';
	}

	foreach ( $environments as $environment ) {
		if ( ! isset( $environment['type'], $environment['url'], $environment['label'] ) ) {
			continue;
		}

		$environment_slug = sanitize_title_with_dashes( $environment['label'] );

		$wp_admin_bar->add_menu(
			[
				'id'     => 'wp-environment-switcher-' . esc_attr( "{$environment['type']}-{$environment_slug}" ),
				'parent' => 'wp-environment-switcher',
				'title'  => $environment['label'],
				'href'   => $callback( $environment['url'] ),
				'meta'   => [
					'class'  => implode( ' ', array_unique( array_filter( [
						'wp-environment-switcher__item',
						'wp-environment-switcher__item--' . esc_attr( $environment_slug ),
						'wp-environment-switcher__item--' . esc_attr( $environment['type'] ),
						$environment['type'] === $current_type ? 'wp-environment-switcher__item--active' : null,
					] ) ) ),
					'target' => '_blank',
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
		if ( apply_filters( 'wp_environment_switcher_warn_production', 'production' === get_current_environment() ) ) {
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

/**
 * Map the meta capability for viewing the environment switcher.
 *
 * @param array<string> $caps An array of the user's capabilities.
 * @param string        $cap The capability being checked.
 * @return array<string>
 */
function map_meta_cap( $caps, $cap ): array {
	if ( 'view_environment_switcher' === $cap ) {
		$caps = [ 'manage_options' ];
	}

	return $caps;
}
