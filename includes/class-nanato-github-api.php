<?php
/**
 * GitHub API Wrapper
 *
 * @package Nanato_WP_GitHub_Updates
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GitHub API Wrapper Class
 */
class Nanato_GitHub_API {

	/**
	 * GitHub API URL
	 *
	 * @var string
	 */
	private $api_url = 'https://api.github.com';

	/**
	 * GitHub personal access token
	 *
	 * @var string
	 */
	private $access_token;

	/**
	 * API request rate limiting information
	 *
	 * @var array
	 */
	private $rate_limit_info = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$options            = get_option( 'nanato_github_updates_settings' );
		$this->access_token = isset( $options['github_token'] ) ? $options['github_token'] : '';
	}

	/**
	 * Make an API request to GitHub
	 *
	 * @param string $url API endpoint URL.
	 * @param array  $args Additional arguments for the request.
	 * @return array|WP_Error Response data or WP_Error on failure.
	 */
	public function api_request( $url, $args = array() ) {
		$default_args = array(
			'headers'   => array(
				'Accept'               => 'application/vnd.github+json',
				'X-GitHub-Api-Version' => '2022-11-28',
				'User-Agent'           => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
			),
			'timeout'   => 10,
			'sslverify' => true,
		);

		// Add authentication if token is available
		if ( ! empty( $this->access_token ) ) {
			// Support both classic tokens (token prefix) and fine-grained tokens (ghp_ prefix)
			if ( strpos( $this->access_token, 'ghp_' ) === 0 || strpos( $this->access_token, 'github_pat_' ) === 0 ) {
				// Fine-grained token - use Bearer
				$default_args['headers']['Authorization'] = 'Bearer ' . $this->access_token;
			} else {
				// Classic token - use token prefix
				$default_args['headers']['Authorization'] = 'token ' . $this->access_token;
			}
		}

		$args = wp_parse_args( $args, $default_args );

		// Log the request (without sensitive data)
		$log_url = preg_replace( '/([?&]access_token)=[^&]+/', '$1=REDACTED', $url );
		error_log( 'GitHub API Request: ' . $log_url );

		// Make the request using WordPress HTTP API
		$response = wp_remote_get( $url, $args );

		// Check for errors
		if ( is_wp_error( $response ) ) {
			error_log( 'GitHub API Error: ' . $response->get_error_message() );
			return $response;
		}

		// Get response code
		$response_code    = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );
		$body             = wp_remote_retrieve_body( $response );

		// Log response code
		error_log( 'GitHub API Response Code: ' . $response_code );

		// Store rate limiting information from headers
		$this->rate_limit_info = array(
			'limit'     => wp_remote_retrieve_header( $response, 'X-RateLimit-Limit' ),
			'remaining' => wp_remote_retrieve_header( $response, 'X-RateLimit-Remaining' ),
			'reset'     => wp_remote_retrieve_header( $response, 'X-RateLimit-Reset' ),
			'resources' => wp_remote_retrieve_header( $response, 'X-RateLimit-Resource' ),
		);

		// Check for rate limiting
		if ( $response_code === 403 && $this->rate_limit_info['remaining'] === '0' ) {
			$reset_time = $this->rate_limit_info['reset'];
			$reset_date = $reset_time ? date( 'Y-m-d H:i:s', $reset_time ) : 'unknown';

			error_log( 'GitHub API Rate Limit Exceeded. Resets at: ' . $reset_date );

			return new WP_Error(
				'github_rate_limit_exceeded',
				sprintf( 'GitHub API rate limit exceeded. Resets at %s.', $reset_date ),
				array(
					'response' => $response,
					'code'     => $response_code,
				)
			);
		}

		if ( $response_code !== 200 ) {
			$error_data    = json_decode( $body, true );
			$error_message = isset( $error_data['message'] ) ? $error_data['message'] : $response_message;

			error_log( 'GitHub API Error: ' . $error_message . ' (HTTP ' . $response_code . ')' );

			return new WP_Error(
				'github_api_error',
				sprintf( 'GitHub API error (HTTP %d): %s', $response_code, $error_message ),
				array(
					'response' => $response,
					'code'     => $response_code,
					'url'      => $url,
					'body'     => $body,
				)
			);
		}

		// Parse JSON response
		$data = json_decode( $body, true );

		if ( is_null( $data ) ) {
			error_log( 'GitHub API Error: Invalid JSON response' );
			return new WP_Error(
				'github_api_error',
				'Invalid JSON response from GitHub API',
				array(
					'url'  => $url,
					'body' => $body,
				)
			);
		}

		return $data;
	}

	/**
	 * Get rate limit information
	 *
	 * @return array Rate limit information
	 */
	public function get_rate_limit_info() {
		return $this->rate_limit_info;
	}

	/**
	 * Test the GitHub API connection
	 *
	 * @return array|WP_Error User data or WP_Error on failure.
	 */
	public function test_connection() {
		$url = $this->api_url . '/user';

		$response = $this->api_request( $url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Return user data along with rate limit info
		$response['rate_limit'] = $this->rate_limit_info;

		return $response;
	}

	/**
	 * Get repository releases
	 *
	 * @param string $owner Repository owner/organization.
	 * @param string $repo Repository name.
	 * @return array|WP_Error Array of releases or WP_Error on failure.
	 */
	public function get_releases( $owner, $repo ) {
		$url = sprintf( '%s/repos/%s/%s/releases', $this->api_url, $owner, $repo );

		return $this->api_request( $url );
	}

	/**
	 * Get latest release
	 *
	 * @param string $owner Repository owner/organization.
	 * @param string $repo Repository name.
	 * @return array|WP_Error Release data or WP_Error on failure.
	 */
	public function get_latest_release( $owner, $repo ) {
		$url = sprintf( '%s/repos/%s/%s/releases/latest', $this->api_url, $owner, $repo );

		$response = $this->api_request( $url );

		// If no releases found or access denied, try to use the default branch
		if ( is_wp_error( $response ) ) {
			$error_data = $response->get_error_data();
			$error_code = isset( $error_data['code'] ) ? $error_data['code'] : 0;
			
			// Only fallback for 403, 404 errors (no releases or access issues)
			if ( $error_code === 403 || $error_code === 404 ) {
				error_log( 'GitHub API: No releases available (HTTP ' . $error_code . '), falling back to default branch' );
				
				// Get repository info to find the default branch
				$repo_info = $this->get_repository( $owner, $repo );

				if ( ! is_wp_error( $repo_info ) && isset( $repo_info['default_branch'] ) ) {
					// Create a synthetic release using the default branch
					$default_branch = $repo_info['default_branch'];
					error_log( 'GitHub API: Using default branch: ' . $default_branch );
					
					return array(
						'tag_name'     => $default_branch,
						'name'         => 'Latest from ' . $default_branch,
						'zipball_url'  => sprintf(
							'%s/repos/%s/%s/zipball/%s',
							$this->api_url,
							$owner,
							$repo,
							$default_branch
						),
						'tarball_url'  => sprintf(
							'%s/repos/%s/%s/tarball/%s',
							$this->api_url,
							$owner,
							$repo,
							$default_branch
						),
						'body'         => 'Using latest code from default branch.',
						'published_at' => $repo_info['updated_at'],
						'assets'       => array(),
					);
				}
			}
		}

		return $response;
	}

	/**
	 * Get repository information
	 *
	 * @param string $owner Repository owner/organization.
	 * @param string $repo Repository name.
	 * @return array|WP_Error Repository data or WP_Error on failure.
	 */
	public function get_repository( $owner, $repo ) {
		$url = sprintf( '%s/repos/%s/%s', $this->api_url, $owner, $repo );

		return $this->api_request( $url );
	}

	/**
	 * Search repositories
	 *
	 * @param string $query Search query.
	 * @param int    $page Page number.
	 * @param int    $per_page Results per page.
	 * @return array|WP_Error Search results or WP_Error on failure.
	 */
	public function search_repositories( $query, $page = 1, $per_page = 10 ) {
		$url = sprintf(
			'%s/search/repositories?q=%s&page=%d&per_page=%d',
			$this->api_url,
			urlencode( $query ),
			$page,
			$per_page
		);

		return $this->api_request( $url );
	}

	/**
	 * Get download URL for a repository
	 *
	 * @param string $owner Repository owner/organization.
	 * @param string $repo Repository name.
	 * @param string $version Version or branch to download (default: latest release).
	 * @return string|WP_Error Download URL or WP_Error on failure.
	 */
	public function get_download_url( $owner, $repo, $version = null ) {
		// If version is null, try to get the latest release
		if ( is_null( $version ) ) {
			$release = $this->get_latest_release( $owner, $repo );

			if ( is_wp_error( $release ) ) {
				error_log( 'GitHub API Error: Failed to get latest release - ' . $release->get_error_message() );

				// Fallback to default branch
				$repo_info = $this->get_repository( $owner, $repo );

				if ( is_wp_error( $repo_info ) ) {
					error_log( 'GitHub API Error: Failed to get repository info - ' . $repo_info->get_error_message() );
					return $repo_info;
				}

				// Use default branch
				$version = $repo_info['default_branch'];
			} else {
				// Use the release tag
				$version = $release['tag_name'];
			}
		}

		// Try to get download from assets first
		if ( ! is_null( $version ) && ! is_wp_error( $release ) && ! empty( $release['assets'] ) ) {
			foreach ( $release['assets'] as $asset ) {
				if ( isset( $asset['browser_download_url'] ) &&
					( strpos( $asset['name'], '.zip' ) !== false ||
						isset( $asset['content_type'] ) && $asset['content_type'] === 'application/zip' ) ) {

					// For private repos, we need to use the API endpoint for assets
					if ( ! empty( $this->access_token ) && isset( $asset['url'] ) ) {
						error_log( 'GitHub API: Using asset API URL for authenticated download: ' . $asset['url'] );
						return $asset['url'];
					}

					return $asset['browser_download_url'];
				}
			}
		}

		// Fallback to zipball_url from API (works better with auth)
		if ( ! empty( $release['zipball_url'] ) ) {
			error_log( 'GitHub API: Fallback to zipball URL: ' . $release['zipball_url'] );
			return $release['zipball_url'];
		}

		// If all else fails, construct an API download URL for the tag
		if ( ! empty( $version ) ) {
			$download_url = sprintf(
				'%s/repos/%s/%s/zipball/%s',
				$this->api_url,
				$owner,
				$repo,
				$version
			);

			return $download_url;
		}

		return new WP_Error(
			'github_api_error',
			'No download URL found in release information',
			$release ?? array()
		);
	}

	/**
	 * Download and verify a file from GitHub
	 *
	 * @param string $url File URL to download.
	 * @return string|WP_Error Path to downloaded file or WP_Error on failure.
	 */
	public function download_file_authenticated( $url ) {
		// Debug the URL we're trying to download
		error_log( 'Attempting to download file from URL with authentication: ' . $url );

		// Include required WordPress file handling functions
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// Prepare download arguments with authentication
		$args = array(
			'headers'   => array(
				'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
			),
			'timeout'   => 60, // Increase timeout for large files
			'sslverify' => true,
		);

		// Check if this is a GitHub API asset URL (needs special Accept header)
		if ( strpos( $url, 'api.github.com' ) !== false && strpos( $url, '/releases/assets/' ) !== false ) {
			// For asset downloads, we need application/octet-stream
			$args['headers']['Accept'] = 'application/octet-stream';
			error_log( 'GitHub Download: Using asset download headers for API URL' );
		} else {
			// For regular API calls
			$args['headers']['Accept']               = 'application/vnd.github+json';
			$args['headers']['X-GitHub-Api-Version'] = '2022-11-28';
		}

		// Add authentication if token is available
		if ( ! empty( $this->access_token ) ) {
			if ( strpos( $this->access_token, 'ghp_' ) === 0 || strpos( $this->access_token, 'github_pat_' ) === 0 ) {
				$args['headers']['Authorization'] = 'Bearer ' . $this->access_token;
			} else {
				$args['headers']['Authorization'] = 'token ' . $this->access_token;
			}
			error_log( 'GitHub Download: Using authenticated request' );
		} else {
			error_log( 'GitHub Download: No authentication token available' );
		}

		// Use wp_remote_get for authenticated downloads
		$response = wp_remote_get( $url, $args );

		// Check for errors
		if ( is_wp_error( $response ) ) {
			error_log( 'GitHub Download Error: ' . $response->get_error_message() );
			return $response;
		}

		// Get response code and body
		$response_code    = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );
		$body             = wp_remote_retrieve_body( $response );

		error_log( 'GitHub Download Response Code: ' . $response_code );

		// Handle redirects for GitHub API downloads (common with zipball URLs)
		if ( $response_code === 302 || $response_code === 301 ) {
			$redirect_url = wp_remote_retrieve_header( $response, 'location' );
			if ( $redirect_url ) {
				error_log( 'GitHub Download: Following redirect to: ' . $redirect_url );

				// Follow the redirect with the same authentication
				$redirect_args = $args;
				// Remove Accept header for the redirected URL (usually to AWS S3)
				unset( $redirect_args['headers']['Accept'] );
				unset( $redirect_args['headers']['X-GitHub-Api-Version'] );
				unset( $redirect_args['headers']['Authorization'] ); // AWS S3 doesn't need GitHub auth

				$response = wp_remote_get( $redirect_url, $redirect_args );

				if ( is_wp_error( $response ) ) {
					error_log( 'GitHub Download Redirect Error: ' . $response->get_error_message() );
					return $response;
				}

				$response_code = wp_remote_retrieve_response_code( $response );
				$body          = wp_remote_retrieve_body( $response );
				error_log( 'GitHub Download Redirect Response Code: ' . $response_code );
			}
		}

		if ( $response_code !== 200 ) {
			$error_message = $response_message;
			$error_body = wp_remote_retrieve_body( $response );
			$error_data = json_decode( $error_body, true );

			// Provide more specific error messages
			if ( $response_code === 404 ) {
				$error_message = 'Repository or release not found. Check if the repository exists and is accessible.';
			} elseif ( $response_code === 401 || $response_code === 403 ) {
				// Check if it's a private repository access issue
				if ( isset( $error_data['message'] ) && strpos( $error_data['message'], 'not accessible by personal access token' ) !== false ) {
					$error_message = 'Access denied. Your GitHub token is missing required permissions. For private repositories, please add "Contents: Read-only" permission to your token at https://github.com/settings/tokens';
				} else {
					$error_message = 'Access denied. Your GitHub token may be invalid or missing required permissions. For private repositories, ensure your token has "Contents: Read-only" permission.';
				}
			}

			error_log( 'GitHub Download Error: ' . $error_message . ' (HTTP ' . $response_code . ')' );
			if ( isset( $error_data['message'] ) ) {
				error_log( 'GitHub API Error Message: ' . $error_data['message'] );
			}

			return new WP_Error(
				'github_download_error',
				sprintf( 'GitHub download failed (HTTP %d): %s', $response_code, $error_message ),
				array(
					'response' => $response,
					'code'     => $response_code,
				)
			);
		}

		// Create temporary file
		$temp_file = wp_tempnam();
		if ( ! $temp_file ) {
			error_log( 'GitHub Download Error: Could not create temporary file' );
			return new WP_Error(
				'github_download_error',
				'Could not create temporary file for download'
			);
		}

		// Write the file
		$result = file_put_contents( $temp_file, $body );
		if ( ! $result ) {
			error_log( 'GitHub Download Error: Could not write to temporary file' );
			@unlink( $temp_file );
			return new WP_Error(
				'github_download_error',
				'Could not write downloaded file to disk'
			);
		}

		// Validate the ZIP file
		if ( ! $this->validate_zip_file( $temp_file ) ) {
			error_log( 'GitHub Download Error: Downloaded file is not a valid ZIP archive' );
			@unlink( $temp_file );
			return new WP_Error(
				'github_download_error',
				'Downloaded file is not a valid ZIP archive'
			);
		}

		error_log( 'GitHub Download: File downloaded successfully to: ' . $temp_file );
		return $temp_file;
	}

	/**
	 * Validate ZIP file integrity
	 *
	 * @param string $file Path to ZIP file.
	 * @return bool True if valid ZIP file, false otherwise.
	 */
	private function validate_zip_file( $file ) {
		if ( ! file_exists( $file ) ) {
			error_log( 'ZIP validation failed: File does not exist' );
			return false;
		}

		// Try to open with ZipArchive if available
		if ( class_exists( 'ZipArchive' ) ) {
			$zip    = new ZipArchive();
			$result = $zip->open( $file, ZipArchive::CHECKCONS );
			if ( $result === true ) {
				// Log the contents of the ZIP file to help diagnose issues
				error_log( 'ZIP validation passed: Archive contains ' . $zip->numFiles . ' files' );

				// List the first 5 files to help diagnose structure issues
				$file_count = min( 5, $zip->numFiles );
				for ( $i = 0; $i < $file_count; $i++ ) {
					$file_info = $zip->statIndex( $i );
					if ( $file_info ) {
						error_log( 'ZIP file ' . ( $i + 1 ) . ': ' . $file_info['name'] );
					}
				}

				$zip->close();
				return true;
			} else {
				error_log( 'ZIP validation failed with ZipArchive error code: ' . $result );
				return false;
			}
		}

		// Fallback: Check file signature (first 4 bytes of a ZIP file should be PK\003\004)
		$handle = fopen( $file, 'rb' );
		if ( ! $handle ) {
			error_log( 'ZIP validation failed: Could not open file for reading' );
			return false;
		}

		$signature = fread( $handle, 4 );
		fclose( $handle );

		$result = $signature === "PK\003\004";
		error_log(
			'ZIP validation with signature check: ' . ( $result ? 'Passed' : 'Failed' ) .
			' (Signature: ' . bin2hex( $signature ) . ', Expected: 504b0304)'
		);

		return $result;
	}

	/**
	 * Check if a GitHub URL likely requires authentication
	 *
	 * @param string $url The GitHub URL to check
	 * @return bool True if authentication is likely required
	 */
	public function url_requires_auth( $url ) {
		// If we have no token, we can't authenticate anyway
		if ( empty( $this->access_token ) ) {
			return false;
		}

		// GitHub URLs that typically require authentication:
		// - api.github.com URLs (zipball/tarball)
		// - Any github.com URLs (since we can't easily determine if repo is private)
		// - Private repository archive URLs
		if ( strpos( $url, 'api.github.com' ) !== false ) {
			return true;
		}

		// For any GitHub URLs, we should use authentication if we have a token
		// This covers both private repos and helps avoid rate limiting for public repos
		if ( strpos( $url, 'github.com' ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Debug method to log release information
	 *
	 * @param array $release Release data from GitHub API
	 */
	public function debug_release_info( $release ) {
		if ( ! is_array( $release ) ) {
			error_log( 'GitHub Debug: Release data is not an array' );
			return;
		}

		error_log( 'GitHub Debug: Release tag: ' . ( isset( $release['tag_name'] ) ? $release['tag_name'] : 'N/A' ) );
		error_log( 'GitHub Debug: Zipball URL: ' . ( isset( $release['zipball_url'] ) ? $release['zipball_url'] : 'N/A' ) );
		error_log( 'GitHub Debug: Assets count: ' . ( isset( $release['assets'] ) ? count( $release['assets'] ) : '0' ) );

		if ( isset( $release['assets'] ) && is_array( $release['assets'] ) ) {
			foreach ( $release['assets'] as $index => $asset ) {
				error_log( 'GitHub Debug: Asset ' . $index . ' - Name: ' . ( isset( $asset['name'] ) ? $asset['name'] : 'N/A' ) );
				error_log( 'GitHub Debug: Asset ' . $index . ' - Download URL: ' . ( isset( $asset['browser_download_url'] ) ? $asset['browser_download_url'] : 'N/A' ) );
				error_log( 'GitHub Debug: Asset ' . $index . ' - API URL: ' . ( isset( $asset['url'] ) ? $asset['url'] : 'N/A' ) );
			}
		}
	}
}