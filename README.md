# Nanato WP GitHub Updates

**Version:** 1.0.1  
**Author:** Nanato Development Team  
**License:** GPL v2 or later  
**Requires WordPress:** 5.0 or higher  
**Tested up to:** 6.6  
**Requires PHP:** 7.4 or higher  

A WordPress plugin that enables automatic updates and installations of themes and plugins directly from GitHub repositories, including private repositories.

## Features

- 🔄 **Automatic Updates**: Keep your GitHub-hosted themes and plugins up-to-date automatically
- 🔒 **Private Repository Support**: Works with private GitHub repositories using personal access tokens
- 📦 **Direct Installation**: Install themes and plugins directly from GitHub without manual downloads
- 🛡️ **Secure Authentication**: Uses GitHub personal access tokens for secure API access
- 🎯 **Easy Configuration**: Simple admin interface for managing repositories
- 📊 **Connection Testing**: Built-in tools to verify GitHub API connectivity
- 🔍 **Repository Search**: Search and preview GitHub repositories before installation
- 🗂️ **Repository Management**: Add and remove repositories dynamically through the admin interface
- 📝 **Comprehensive Logging**: Detailed error logging for troubleshooting and debugging

## Requirements

### Server Requirements
- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **cURL:** Enabled (for API requests)
- **SSL/TLS:** Support for HTTPS connections
- **File Permissions:** WordPress must have write permissions to themes and plugins directories

### User Requirements
- **GitHub Account:** With access to repositories you want to use
- **Personal Access Token:** GitHub token with appropriate permissions
- **WordPress Admin:** `manage_options` capability for configuration

## Installation

### Method 1: Manual Installation

1. Download the plugin files
2. Upload the `nanato-wp-github-updates` folder to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to **GitHub Updates** in your admin menu to configure

### Method 2: Git Clone

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/gabydevdev/nanato-wp-github-updates.git
```

## Configuration

### 1. GitHub Personal Access Token

To use this plugin, you'll need a GitHub personal access token:

1. Go to [GitHub Settings > Developer settings > Personal access tokens](https://github.com/settings/tokens)
2. Click "Generate new token (classic)"
3. Give it a descriptive name (e.g., "WordPress GitHub Updates")
4. Select the following scopes:
   - **`repo`** - Full control of private repositories (required)
   - **`read:packages`** - Read packages (optional, only if using GitHub packages)
5. Click "Generate token"
6. Copy the token immediately (you won't be able to see it again)

### 2. Plugin Configuration

1. In WordPress admin, go to **GitHub Updates**
2. Paste your GitHub token in the "Personal Access Token" field
3. Click "Test Connection" to verify the token works
4. Save the settings

## Usage

### Admin Interface Overview

The plugin adds a **GitHub Updates** menu to your WordPress admin with the following submenus:

1. **Settings** - Main configuration page for GitHub token and repository management
2. **Install from GitHub** - Direct installation interface for GitHub repositories

### Adding Repositories for Updates

1. Go to **GitHub Updates > Settings** in your admin menu
2. Scroll down to "Repository Management"
3. Click "Add New Repository"
4. Fill in the repository details:
   - **Type**: Theme or Plugin
   - **Owner**: GitHub username or organization
   - **Repository Name**: The repository name
   - **Theme Slug**: (for themes) The directory name of your theme
   - **Plugin File**: (for plugins) The main plugin file path (e.g., `my-plugin/my-plugin.php`)
5. Click "Add Repository"

### Installing from GitHub

1. Go to **GitHub Updates > Install from GitHub**
2. Enter the repository details:
   - **Repository Owner**: GitHub username or organization
   - **Repository Name**: The repository name
3. Click "Search Repository" to preview the repository information
4. Choose installation type (Theme or Plugin)
5. Configure installation options:
   - **Custom slug/directory name**: Optional custom directory name
   - **Activate after installation**: Auto-activate plugins after installation
   - **Add to updater list**: Monitor this repository for future updates
6. Click "Install Now"

### Example Repository Configurations

#### Plugin Example
```
Type: Plugin
Owner: your-username
Repository: my-custom-plugin
Plugin File: my-custom-plugin/my-custom-plugin.php
```

#### Theme Example
```
Type: Theme
Owner: your-organization
Repository: my-custom-theme
Theme Slug: my-custom-theme
```

## Security Considerations

- **Token Security**: Store GitHub tokens securely and never commit them to version control
- **Repository Access**: Only add repositories you trust to the updater
- **File Permissions**: Ensure proper WordPress file permissions are maintained
- **SSL Verification**: The plugin enforces SSL certificate verification for all GitHub API requests
- **Rate Limiting**: The plugin respects GitHub API rate limits to prevent account restrictions

## Troubleshooting

### Common Issues

#### 1. "Connection Failed" Error
**Symptoms**: Test connection fails or repositories don't update
**Solutions**:
- Verify your GitHub token is valid and hasn't expired
- Check that the token has the correct permissions (`repo` scope)
- Ensure your server can make HTTPS requests to api.github.com
- Check if your server's IP is blocked by GitHub

#### 2. "Authentication Failed" During Installation
**Symptoms**: Public repositories fail to install
**Solutions**:
- Verify the repository exists and is accessible
- For private repositories, ensure your token has access
- Check repository name spelling and owner username

#### 3. "Permission Denied" Errors
**Symptoms**: Installation fails with permission errors
**Solutions**:
- Verify WordPress has write permissions to `/wp-content/themes/` and `/wp-content/plugins/`
- Check file ownership and permissions on your server
- Ensure the web server user can create directories

#### 4. Plugin/Theme Not Updating
**Symptoms**: Updates don't appear in WordPress admin
**Solutions**:
- Verify the repository is properly added to the repository list
- Check that new releases are tagged in GitHub
- Clear any update caches: `wp transient delete --all` (WP-CLI)
- Check WordPress debug logs for GitHub API errors

### Debug Mode

To enable detailed logging:

1. Add this to your `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

2. Check your WordPress debug log (usually `/wp-content/debug.log`) for messages starting with "GitHub Updates:"

3. Look for these common log entries:
   - `GitHub Updates: Intercepting GitHub download` - Download process started
   - `GitHub Updates: URL requires authentication` - Private repository detected
   - `GitHub API Request:` - API calls being made
   - `GitHub API Error:` - API request failures

## FAQ

### General Questions

**Q: Can I use this plugin with public repositories?**
A: Yes! The plugin works with both public and private repositories. For public repositories, you can still use a token to avoid rate limiting.

**Q: What happens if my GitHub token expires?**
A: Updates and installations will fail. You'll need to generate a new token and update it in the plugin settings.

**Q: Can I use this with GitHub Enterprise?**
A: Currently, the plugin is designed for GitHub.com. GitHub Enterprise support would require code modifications.

### Repository Management

**Q: How do I remove a repository from monitoring?**
A: Go to GitHub Updates > Settings, find the repository in the list, and click "Remove".

**Q: Can I monitor multiple branches?**
A: The plugin monitors releases/tags, not specific branches. Create releases from your desired branch.

**Q: What if my repository doesn't have releases?**
A: The plugin requires GitHub releases for version detection. Create releases/tags for your versions.

### Installation Issues

**Q: Why is my theme/plugin installed in the wrong directory?**
A: Ensure your repository structure follows WordPress standards with the main files in the repository root.

**Q: Can I install beta or development versions?**
A: Yes, if they're tagged as pre-releases in GitHub. The plugin will detect pre-release versions.

**Q: What file structure should my repository have?**
A: 
- **Plugins**: Main plugin file should be in the repository root or one level deep
- **Themes**: `style.css` and `index.php` should be in the repository root

## Uninstall

When you deactivate and delete the plugin:

1. **Settings Preserved**: Plugin settings are kept in case you reinstall
2. **Manual Cleanup**: To completely remove all data, you can:
   - Delete the option `nanato_github_updates_settings` from your database
   - Remove any cached transients related to GitHub updates

3. **Installed Themes/Plugins**: Remain installed but will no longer receive updates through this plugin

To manually clean up (optional):
```sql
DELETE FROM wp_options WHERE option_name LIKE 'nanato_github_%';
```

## Changelog

### Version 1.0.0 (Current)
- Initial release
- GitHub repository monitoring and updates
- Private repository support with token authentication
- Direct installation from GitHub
- Admin interface for repository management
- Connection testing and validation
- Comprehensive error logging and debugging

## Contributing

We welcome contributions! Here's how you can help:

### Reporting Issues
- Use the GitHub Issues page for bug reports
- Include WordPress version, PHP version, and plugin version
- Provide debug logs when possible
- Describe steps to reproduce the issue

### Feature Requests
- Check existing issues before submitting new requests
- Provide detailed use cases and examples
- Explain the benefit to other users

### Development
- Fork the repository
- Create a feature branch
- Follow WordPress coding standards
- Add tests for new functionality
- Submit a pull request with detailed description

## Support

### Documentation
- Check this README for common questions and setup
- Review the troubleshooting section for known issues
- Enable debug logging for detailed error information

### Community Support
- GitHub Issues: For bug reports and feature requests
- WordPress Support Forums: For general WordPress integration questions

### Professional Support
- For custom development or priority support, contact the development team
- Available for enterprise installations and custom modifications

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## Credits

Developed by the Nanato Development Team for seamless GitHub integration with WordPress.

---

*Last updated: July 4, 2025*
