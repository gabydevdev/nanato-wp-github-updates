<?php
/**
 * Uninstall handler for DD WP GitHub Updates
 *
 * @package NANATO_WP_GitHub_Updates
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('nanato_github_updates_settings');
delete_option('nanato_github_updates_repositories');

// Clean up any transients we might have created
delete_transient('nanato_github_api_cache');
