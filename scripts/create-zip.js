#!/usr/bin/env node

/**
 * ZIP Creation Script for Nanato WP GitHub Updates
 * 
 * Creates a clean ZIP file for plugin distribution
 */

const fs = require('fs');
const path = require('path');
const archiver = require('archiver');
const chalk = require('chalk');

const packageJson = require('../package.json');
const version = packageJson.version;
const pluginName = 'nanato-wp-github-updates';

// Files and directories to include in the ZIP
const includePatterns = [
  'nanato-wp-github-updates.php',
  'README.md',
  'uninstall.php',
  'admin/**/*',
  'includes/**/*',
  'languages/**/*'
];

// Files and directories to exclude
const excludePatterns = [
  'node_modules/**/*',
  'scripts/**/*',
  'package.json',
  'package-lock.json',
  '.git/**/*',
  '.gitignore',
  '.vscode/**/*',
  '.idea/**/*',
  '*.log',
  '.DS_Store',
  'Thumbs.db',
  '*.zip',
  'PLUGIN_EXPLANATION.md'
];

const outputDir = path.join(__dirname, '..');
const outputFile = path.join(outputDir, `${pluginName}-${version}.zip`);

console.log(chalk.blue('📦 Creating plugin ZIP file...'));
console.log(chalk.gray(`Version: ${version}`));
console.log(chalk.gray(`Output: ${path.basename(outputFile)}\n`));

// Create a file to stream archive data to
const output = fs.createWriteStream(outputFile);
const archive = archiver('zip', {
  zlib: { level: 9 } // Maximum compression
});

// Listen for all archive data to be written
output.on('close', function() {
  const sizeInMB = (archive.pointer() / 1024 / 1024).toFixed(2);
  console.log(chalk.green(`✅ ZIP file created successfully!`));
  console.log(chalk.blue(`📁 File: ${path.basename(outputFile)}`));
  console.log(chalk.blue(`📊 Size: ${sizeInMB} MB`));
  console.log(chalk.blue(`📝 Total files: ${archive.pointer()} bytes\n`));
});

// Handle warnings (e.g., stat failures and other non-blocking errors)
archive.on('warning', function(err) {
  if (err.code === 'ENOENT') {
    console.log(chalk.yellow(`⚠️  Warning: ${err.message}`));
  } else {
    throw err;
  }
});

// Handle errors
archive.on('error', function(err) {
  console.log(chalk.red(`❌ Error creating ZIP: ${err.message}`));
  throw err;
});

// Pipe archive data to the file
archive.pipe(output);

// Add files to archive
const glob = require('glob');

function addFilesToArchive() {
  const filesToAdd = [];
  
  // Get all files matching include patterns
  includePatterns.forEach(pattern => {
    const files = glob.sync(pattern, { 
      cwd: path.join(__dirname, '..'),
      dot: false 
    });
    filesToAdd.push(...files);
  });
  
  // Remove duplicates and filter out excluded files
  const uniqueFiles = [...new Set(filesToAdd)];
  
  const filteredFiles = uniqueFiles.filter(file => {
    return !excludePatterns.some(excludePattern => {
      const excludeRegex = new RegExp(
        excludePattern.replace(/\*\*/g, '.*').replace(/\*/g, '[^/]*')
      );
      return excludeRegex.test(file);
    });
  });
  
  console.log(chalk.blue('📋 Adding files to ZIP:'));
  
  filteredFiles.forEach(file => {
    const filePath = path.join(__dirname, '..', file);
    const archivePath = `${pluginName}/${file}`;
    
    if (fs.existsSync(filePath)) {
      const stats = fs.statSync(filePath);
      if (stats.isFile()) {
        archive.file(filePath, { name: archivePath });
        console.log(chalk.gray(`  ✓ ${file}`));
      }
    }
  });
  
  console.log(chalk.blue(`\n📦 Packaging ${filteredFiles.length} files...\n`));
}

// Add files and finalize
addFilesToArchive();
archive.finalize();

// Clean up old ZIP files (keep only the latest 3)
output.on('close', function() {
  const zipFiles = glob.sync(`${pluginName}-*.zip`, { 
    cwd: outputDir 
  }).sort().reverse();
  
  if (zipFiles.length > 3) {
    const filesToDelete = zipFiles.slice(3);
    filesToDelete.forEach(file => {
      const filePath = path.join(outputDir, file);
      fs.unlinkSync(filePath);
      console.log(chalk.gray(`🗑️  Cleaned up old ZIP: ${file}`));
    });
  }
});
