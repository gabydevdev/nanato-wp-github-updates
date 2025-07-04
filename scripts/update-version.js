#!/usr/bin/env node

/**
 * Version Update Script for Nanato WP GitHub Updates
 * 
 * This script automatically updates version numbers across plugin files
 * when the version in package.json is changed.
 */

const fs = require('fs');
const path = require('path');
const chalk = require('chalk');

// Get the new version from package.json
const packageJson = require('../package.json');
const newVersion = packageJson.version;

console.log(chalk.blue('🔄 Updating plugin version to:'), chalk.green(newVersion));

// Files to update with their respective patterns
const filesToUpdate = [
  {
    file: 'nanato-wp-github-updates.php',
    patterns: [
      {
        // Plugin header version
        search: /(\* Version:\s+)[\d.]+/,
        replace: `$1${newVersion}`,
        description: 'Plugin header version'
      },
      {
        // Constant definition
        search: /(define\('NANATO_GITHUB_UPDATES_VERSION',\s+')[\d.]+('\);)/,
        replace: `$1${newVersion}$2`,
        description: 'Plugin version constant'
      }
    ]
  },
  {
    file: 'README.md',
    patterns: [
      {
        // Version in header
        search: /(\*\*Version:\*\*\s+)[\d.]+/,
        replace: `$1${newVersion}`,
        description: 'README version header'
      }
    ]
  }
];

// Function to update a single file
function updateFile(fileConfig) {
  const filePath = path.join(__dirname, '..', fileConfig.file);
  
  if (!fs.existsSync(filePath)) {
    console.log(chalk.yellow(`⚠️  File not found: ${fileConfig.file}`));
    return false;
  }

  let content = fs.readFileSync(filePath, 'utf8');
  let updated = false;

  fileConfig.patterns.forEach(pattern => {
    if (pattern.search.test(content)) {
      content = content.replace(pattern.search, pattern.replace);
      console.log(chalk.green(`✅ Updated ${pattern.description} in ${fileConfig.file}`));
      updated = true;
    } else {
      console.log(chalk.yellow(`⚠️  Pattern not found: ${pattern.description} in ${fileConfig.file}`));
    }
  });

  if (updated) {
    fs.writeFileSync(filePath, content, 'utf8');
    return true;
  }

  return false;
}

// Main execution
let totalUpdated = 0;

console.log(chalk.blue('\n📝 Starting version update process...\n'));

filesToUpdate.forEach(fileConfig => {
  if (updateFile(fileConfig)) {
    totalUpdated++;
  }
});

console.log(chalk.blue('\n📊 Version update summary:'));
console.log(chalk.green(`✅ Successfully updated ${totalUpdated} file(s)`));
console.log(chalk.blue(`🎯 New version: ${newVersion}`));

// Update last modified date in README
const readmePath = path.join(__dirname, '..', 'README.md');
if (fs.existsSync(readmePath)) {
  let readmeContent = fs.readFileSync(readmePath, 'utf8');
  const currentDate = new Date().toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
  
  // Update the last updated date
  const lastUpdatedPattern = /(\*Last updated:\s*)[^*]+/;
  if (lastUpdatedPattern.test(readmeContent)) {
    readmeContent = readmeContent.replace(lastUpdatedPattern, `$1${currentDate}`);
    fs.writeFileSync(readmePath, readmeContent, 'utf8');
    console.log(chalk.green(`✅ Updated last modified date in README.md`));
  }
}

console.log(chalk.blue('\n🎉 Version update completed!\n'));

// Exit with appropriate code
process.exit(totalUpdated > 0 ? 0 : 1);
