<?php
/**
 * Nanato WP GitHub Updates
 *
 * Plugin Name: Nanato WP GitHub Updates
 * Description: Update and install WordPress themes or plugins from your own private GitHub repositories.
 * Version: 1.0.3
 * Text Domain: nanato-github-updates
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'NANATO_GITHUB_UPDATES_VERSION', '1.0.2' );
define( 'NANATO_GITHUB_UPDATES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NANATO_GITHUB_UPDATES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NANATO_GITHUB_UPDATES_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Include required files
require_once NANATO_GITHUB_UPDATES_PLUGIN_DIR . 'includes/class-nanato-github-updates.php';

// Initialize the plugin
function nanato_github_updates_init() {
	// Load text domain for translations
	load_plugin_textdomain( 'nanato-github-updates', false, dirname( NANATO_GITHUB_UPDATES_PLUGIN_BASENAME ) . '/languages' );

	$plugin = new Nanato_GitHub_Updates();
	$plugin->init();
}
add_action( 'plugins_loaded', 'nanato_github_updates_init' );
