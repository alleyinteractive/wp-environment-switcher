# WordPress Environment Switcher

[![Coding Standards](https://github.com/alleyinteractive/wp-environment-switcher/actions/workflows/coding-standards.yml/badge.svg)](https://github.com/alleyinteractive/wp-environment-switcher/actions/workflows/coding-standards.yml)

Easily switch between different site environments from the WordPress admin bar.

> Props to [WordPress Stage Switcher](https://github.com/roots/wp-stage-switcher) for the inspiration.

## Installation

You can install the package via Composer:

```bash
composer require alleyinteractive/wp-environment-switcher
```

## Usage

Activate the plugin in WordPress and you will see the switcher appear in the top right admin bar:

![Screenshot of plugin](https://github.com/alleyinteractive/wp-environment-switcher/assets/346399/83684c99-4f74-4969-b302-a0c617c17190)

The plugin reads the current WordPress environment from the current hosting
provider (Pantheon and WordPress VIP supported) and falls back to
`wp_get_environment_type()` which can be set by defining `WP_ENVIRONMENT_TYPE`
in your `wp-config.php` file. You can override the current environment by
using the `wp_environment_switcher_current_environment` filter:

```php
add_filter(
	'wp_environment_switcher_current_environment',
	fn () => 'my-custom-environment'
);
```

You can define the available environments by using the
`wp_environment_switcher_environments` filter:

```php
add_filter(
	'wp_environment_switcher_environments',
	fn () => [
		'production' => 'https://example.org',
		'staging'    => 'https://staging.example.org',
		'local'      => 'https://example.test',
	]
);
```

The plugin will automatically detect the current environment and highlight it in
the switcher.

## Testing

Run `composer test` to run tests against PHPStan/PHPCS.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

This project is actively maintained by [Alley
Interactive](https://github.com/alleyinteractive). Like what you see? [Come work
with us](https://alley.com/careers/).

- [Sean Fisher](https://github.com/srtfisher)
- [All Contributors](../../contributors)

## License

The GNU General Public License (GPL) license. Please see [License File](LICENSE) for more information.
