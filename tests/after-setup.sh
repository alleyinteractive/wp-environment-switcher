#!/usr/bin/env bash
# wp-env after setup script.

INSTALL_PATH=$(wp-env install-path)
PATH_TO_CURRENT_DIR=$(dirname "$0")

# Copy the wp-env.php.stub file to mu-plugins directory
mkdir -p "$INSTALL_PATH/WordPress/wp-content/mu-plugins"
cp "$PATH_TO_CURRENT_DIR/wp-env.php.stub" "$INSTALL_PATH/WordPress/wp-content/mu-plugins/wp-env.php"
