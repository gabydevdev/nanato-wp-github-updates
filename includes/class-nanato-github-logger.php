<?php
/**
 * GitHub Logger
 *
 * @package Nanato_WP_GitHub_Updates
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GitHub Logger Class
 */
class Nanato_GitHub_Logger {

	/**
	 * Log levels
	 */
	const ERROR = 'error';
	const WARNING = 'warning';
	const INFO = 'info';
	const DEBUG = 'debug';

	/**
	 * Log a message
	 *
	 * @param string $message Message to log
	 * @param string $level Log level
	 * @param array  $context Additional context
	 */
	public static function log( $message, $level = self::INFO, $context = array() ) {
		if ( ! self::should_log( $level ) ) {
			return;
		}

		$log_entry = array(
			'timestamp' => current_time( 'mysql' ),
			'level'     => $level,
			'message'   => $message,
			'context'   => $context,
		);

		// Write to WordPress debug log
		error_log( '[GitHub Updates - ' . strtoupper( $level ) . '] ' . $message );

		if ( ! empty( $context ) ) {
			error_log( 'Context: ' . wp_json_encode( $context ) );
		}

		// Also store in options for dashboard display
		$logs = get_option( 'nanato_github_updates_logs', array() );
		array_unshift( $logs, $log_entry );

		// Keep only last 100 logs
		$logs = array_slice( $logs, 0, 100 );
		update_option( 'nanato_github_updates_logs', $logs );
	}

	/**
	 * Check if a log level should be logged
	 *
	 * @param string $level Log level
	 * @return bool True if should log
	 */
	private static function should_log( $level ) {
		// Get the configured log level
		$log_level = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ? self::DEBUG : self::INFO;

		$levels = array(
			self::DEBUG   => 0,
			self::INFO    => 1,
			self::WARNING => 2,
			self::ERROR   => 3,
		);

		return isset( $levels[ $level ] ) && isset( $levels[ $log_level ] ) &&
			$levels[ $level ] >= $levels[ $log_level ];
	}

	/**
	 * Get logs
	 *
	 * @param int $limit Number of logs to retrieve
	 * @return array Logs
	 */
	public static function get_logs( $limit = 20 ) {
		$logs = get_option( 'nanato_github_updates_logs', array() );
		return array_slice( $logs, 0, $limit );
	}

	/**
	 * Clear logs
	 */
	public static function clear_logs() {
		update_option( 'nanato_github_updates_logs', array() );
	}
}