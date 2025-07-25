# Changelog

All notable changes to `WordPress Environment Switcher` will be documented in this file.

## 1.2.1

- Refactor environment detection logic to allow for proper targeting.

## 1.2.0

- Added support for more flexible environment configurations.

## 1.1.0

- Upgrade to PHP 8.1.
- Change the environment switcher to show if the user has the
  `view_environment_switcher` capability (which is mapped to `manage_options`).
  This allows for more fine-grained control over who can see the environment
  switcher.

## 1.0.1

- Infer the environment from the hosting provider, allow it to be filtered via `wp_environment_switcher_current_environment`.

## 1.0.0

- Initial release
