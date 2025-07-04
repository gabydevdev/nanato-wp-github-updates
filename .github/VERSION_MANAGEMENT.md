# Version Management Instructions

**Last Updated:** July 4, 2025  
**Plugin:** Nanato WP GitHub Updates  
**Version System:** Semantic Versioning (SemVer)

## 📋 Overview

This plugin uses an automated version management system that synchronizes version numbers across all plugin files. The system is built with Node.js scripts and npm commands to ensure consistency and reduce manual errors.

## 🎯 Quick Start

```bash
# 1. Install dependencies (one-time setup)
npm install

# 2. Update version (choose one)
npm run version:patch    # 1.0.0 → 1.0.1
npm run version:minor    # 1.0.0 → 1.1.0  
npm run version:major    # 1.0.0 → 2.0.0

# 3. Create release package
npm run release

# 4. Commit and push changes
git add .
git commit -m "Release version 1.0.1"
git push origin main
```

## 📦 Available Commands

### Version Management
| Command | Description | Example |
|---------|-------------|---------|
| `npm run version:patch` | Increment patch version | `1.0.0` → `1.0.1` |
| `npm run version:minor` | Increment minor version | `1.0.0` → `1.1.0` |
| `npm run version:major` | Increment major version | `1.0.0` → `2.0.0` |
| `npm run version:set --version=X.Y.Z` | Set specific version | `npm run version:set --version=2.1.0` |

### Build & Release
| Command | Description |
|---------|-------------|
| `npm run build` | Update version across all files |
| `npm run zip` | Create distribution ZIP file |
| `npm run release` | Build + ZIP in one command |

### Utility Commands
| Command | Description |
|---------|-------------|
| `npm run preversion` | Show current version |
| `npm run postversion` | Show updated version |

## 🔧 What Gets Updated Automatically

When you run any version command, the following files are automatically updated:

### 1. `nanato-wp-github-updates.php`
```php
/**
 * Plugin Name: Nanato WP GitHub Updates
 * Version:     1.0.1  ← Updated automatically
 */

define('NANATO_GITHUB_UPDATES_VERSION', '1.0.1');  ← Updated automatically
```

### 2. `README.md`
```markdown
**Version:** 1.0.1  ← Updated automatically
...
*Last updated: July 4, 2025*  ← Updated automatically
```

### 3. `package.json`
```json
{
  "name": "nanato-wp-github-updates",
  "version": "1.0.1"  ← Updated automatically
}
```

## 🚀 Step-by-Step Release Process

### Step 1: Prepare for Release
1. **Ensure all changes are committed** to your current branch
2. **Test your plugin** thoroughly
3. **Update any documentation** if needed
4. **Review the changelog** (if you maintain one)

### Step 2: Update Version
Choose the appropriate version increment:

**Patch Version (Bug fixes, minor improvements)**
```bash
npm run version:patch
```

**Minor Version (New features, backwards compatible)**
```bash
npm run version:minor
```

**Major Version (Breaking changes, major updates)**
```bash
npm run version:major
```

**Custom Version (Specific version number)**
```bash
npm run version:set --version=1.5.0
```

### Step 3: Create Release Package
```bash
npm run release
```

This command will:
- ✅ Update version numbers across all files
- ✅ Create a clean ZIP file for distribution
- ✅ Remove development files from ZIP
- ✅ Clean up old ZIP files (keeps latest 3)

### Step 4: Commit Changes
```bash
git add .
git commit -m "Release version 1.0.1"
git push origin main
```

### Step 5: Create GitHub Release (Optional)
1. Go to your GitHub repository
2. Click "Releases" → "Create a new release"
3. Tag version: `v1.0.1`
4. Release title: `Version 1.0.1`
5. Upload the generated ZIP file
6. Add release notes

## 📝 Version Numbering Guidelines

We follow **Semantic Versioning (SemVer)**: `MAJOR.MINOR.PATCH`

### MAJOR Version (X.0.0)
- Breaking changes
- Major feature rewrites
- Incompatible API changes
- WordPress version requirement changes

**Examples:**
- Complete UI overhaul
- Changed plugin architecture
- Removed deprecated features
- Updated minimum WordPress version

### MINOR Version (0.X.0)
- New features
- Backwards compatible changes
- New functionality additions
- Performance improvements

**Examples:**
- Added new GitHub installation features
- New admin interface options
- Additional API endpoints
- Enhanced security features

### PATCH Version (0.0.X)
- Bug fixes
- Security patches
- Minor improvements
- Documentation updates

**Examples:**
- Fixed authentication issues
- Corrected PHP warnings
- Updated translations
- Fixed typos in documentation

## 🛠️ Technical Details

### Scripts Location
All version management scripts are located in the `scripts/` directory:

- `scripts/update-version.js` - Main version update logic
- `scripts/create-zip.js` - ZIP file creation
- `scripts/setup.js` - Development environment setup

### File Pattern Matching
The version update script uses regex patterns to find and replace version numbers:

```javascript
// Plugin header pattern
/(\* Version:\s+)[\d.]+/

// PHP constant pattern
/(define\('NANATO_GITHUB_UPDATES_VERSION',\s+')[\d.]+('\);)/

// README version pattern
/(\*\*Version:\*\*\s+)[\d.]+/
```

### ZIP File Contents
The release ZIP includes:
- ✅ Main plugin file
- ✅ Admin files (CSS, JS, PHP)
- ✅ Core includes
- ✅ README and documentation
- ✅ Uninstall script
- ✅ Language files (if present)

**Excluded from ZIP:**
- ❌ Node.js dependencies
- ❌ Development scripts
- ❌ Git files
- ❌ IDE configurations
- ❌ Log files
- ❌ Documentation files

## 🐛 Troubleshooting

### Common Issues

**❌ "npm command not found"**
```bash
# Install Node.js from https://nodejs.org/
# Then run:
npm install
```

**❌ "Permission denied" on script execution**
```bash
# On Unix-like systems:
chmod +x scripts/*.js
```

**❌ "Version pattern not found"**
- Check that the target file exists
- Verify the regex patterns match your file content
- Look for typos in version format

**❌ "ZIP creation failed"**
```bash
# Ensure you have write permissions
# Check available disk space
# Verify all required files exist
```

### Debug Mode
Enable verbose logging:
```bash
DEBUG=* npm run version:patch
```

### Manual Version Update
If scripts fail, you can manually update:
1. Update `package.json` version
2. Run `npm run build` to sync other files
3. Run `npm run zip` to create distribution

## 📋 Checklist Template

Use this checklist for each release:

### Pre-Release
- [ ] All changes tested locally
- [ ] Documentation updated
- [ ] No pending commits
- [ ] Version increment type decided

### Release Process
- [ ] Version command executed
- [ ] ZIP file created successfully
- [ ] Files committed to repository
- [ ] Changes pushed to GitHub

### Post-Release
- [ ] GitHub release created (if applicable)
- [ ] ZIP file uploaded to release
- [ ] Release notes added
- [ ] Team notified of release

## 🔗 Related Files

- `package.json` - Main version configuration
- `scripts/update-version.js` - Version update logic
- `scripts/create-zip.js` - ZIP creation logic
- `.gitignore` - Files excluded from version control
- `README.md` - User documentation
- `PLUGIN_EXPLANATION.md` - Technical documentation

## 📞 Support

If you encounter issues with the version management system:

1. **Check the console output** for error messages
2. **Verify Node.js and npm** are properly installed
3. **Ensure file permissions** are correct
4. **Review the troubleshooting section** above
5. **Check GitHub Issues** for similar problems

---

*This document is automatically updated with each release. For the most current version, check the repository's main branch.*
