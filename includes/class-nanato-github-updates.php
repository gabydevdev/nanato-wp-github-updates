<?php
/**
 * Main plugin class
 *
 * @package Nanato_WP_GitHub_Updates
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class
 */
class Nanato_GitHub_Updates {

	/**
	 * Initialize the plugin
	 */
	public function init() {
		// Load dependencies
		$this->load_dependencies();

		// Register hooks
		$this->register_hooks();
	}

	/**
	 * Load required dependencies
	 */
	private function load_dependencies() {
		require_once NANATO_GITHUB_UPDATES_PLUGIN_DIR . 'includes/class-nanato-github-api.php';
		require_once NANATO_GITHUB_UPDATES_PLUGIN_DIR . 'includes/class-nanato-github-updater.php';
		require_once NANATO_GITHUB_UPDATES_PLUGIN_DIR . 'includes/class-nanato-github-installer.php';
		require_once NANATO_GITHUB_UPDATES_PLUGIN_DIR . 'includes/class-nanato-github-updates-admin.php';
	}

	/**
	 * Register all hooks related to the plugin functionality
	 */
	private function register_hooks() {
		// Initialize admin functionality
		$admin = new Nanato_GitHub_Updates_Admin();
		$admin->register_hooks();

		// Initialize updater
		$updater = new Nanato_GitHub_Updater();
		$updater->register_hooks();

		// Initialize installer
		$installer = new Nanato_GitHub_Installer();
		$installer->register_hooks();

		// Add hooks for GitHub updates
		add_filter( 'upgrader_pre_download', array( $this, 'mark_as_github_update' ), 10, 3 );
	}

	/**
	 * Handle GitHub downloads with authentication
	 *
	 * @param bool|WP_Error $result The download result or error
	 * @param string        $package The package URL
	 * @param object        $upgrader The WP_Upgrader instance
	 * @return bool|WP_Error The download result or WP_Error on failure
	 */
	public function mark_as_github_update( $result, $package, $upgrader ) {
		// Check if the package URL is from GitHub
		if ( strpos( $package, 'github.com' ) !== false ||
			strpos( $package, 'api.github.com' ) !== false ) {

			error_log( 'GitHub Updates: Intercepting GitHub download: ' . $package );

			// Store a flag that will be checked by our upgrader_source_selection filter
			if ( isset( $upgrader->skin ) && isset( $upgrader->skin->options ) ) {
				$upgrader->skin->options['github_update'] = true;

				// Try to determine the type from the context
				if ( is_a( $upgrader, 'Theme_Upgrader' ) ) {
					$upgrader->skin->options['type'] = 'theme';
				} elseif ( is_a( $upgrader, 'Plugin_Upgrader' ) ) {
					$upgrader->skin->options['type'] = 'plugin';
				}
			}

			// Use our authenticated download method for all GitHub URLs if we have a token
			$api = new Nanato_GitHub_API();

			// Check if we have a token to use for authentication
			$options   = get_option( 'nanato_github_updates_settings' );
			$has_token = ! empty( $options['github_token'] );

			if ( $has_token ) {
				error_log( 'GitHub Updates: Using authenticated download for GitHub URL' );

				// Download using our authenticated method
				$temp_file = $api->download_file_authenticated( $package );

				if ( is_wp_error( $temp_file ) ) {
					error_log( 'GitHub Updates: Authenticated download failed: ' . $temp_file->get_error_message() );
					return $temp_file;
				}

				error_log( 'GitHub Updates: Successfully downloaded with authentication to: ' . $temp_file );
				return $temp_file;
			} else {
				error_log( 'GitHub Updates: No authentication token available, trying public access' );
				// For public repositories without auth, let WordPress handle it normally
				// unless it's an API URL which definitely needs auth
				if ( strpos( $package, 'api.github.com' ) !== false ) {
					return new WP_Error(
						'github_auth_required',
						'This GitHub API URL requires authentication. Please configure your GitHub token in the plugin settings.'
					);
				}
			}
		}

		return $result;
	}
}
