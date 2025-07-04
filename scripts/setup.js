#!/usr/bin/env node

/**
 * Setup Script for Nanato WP GitHub Updates
 * 
 * Installs dependencies and sets up the development environment
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');
const chalk = require('chalk');

console.log(chalk.blue('🚀 Setting up Nanato WP GitHub Updates development environment...\n'));

// Check if npm is available
try {
  execSync('npm --version', { stdio: 'ignore' });
} catch (error) {
  console.log(chalk.red('❌ npm is not installed. Please install Node.js and npm first.'));
  process.exit(1);
}

// Install dependencies
console.log(chalk.blue('📦 Installing npm dependencies...'));
try {
  execSync('npm install', { stdio: 'inherit' });
  console.log(chalk.green('✅ Dependencies installed successfully!\n'));
} catch (error) {
  console.log(chalk.red('❌ Failed to install dependencies'));
  process.exit(1);
}

// Make scripts executable (Unix-like systems)
if (process.platform !== 'win32') {
  console.log(chalk.blue('🔧 Making scripts executable...'));
  const scriptFiles = [
    'scripts/update-version.js',
    'scripts/create-zip.js',
    'scripts/setup.js'
  ];
  
  scriptFiles.forEach(script => {
    const scriptPath = path.join(__dirname, '..', script);
    if (fs.existsSync(scriptPath)) {
      try {
        fs.chmodSync(scriptPath, '755');
        console.log(chalk.green(`✅ Made ${script} executable`));
      } catch (error) {
        console.log(chalk.yellow(`⚠️  Could not make ${script} executable: ${error.message}`));
      }
    }
  });
  console.log('');
}

// Create .nvmrc file for Node version management
const nvmrcPath = path.join(__dirname, '..', '.nvmrc');
if (!fs.existsSync(nvmrcPath)) {
  const nodeVersion = process.version;
  fs.writeFileSync(nvmrcPath, nodeVersion + '\n');
  console.log(chalk.green(`✅ Created .nvmrc with Node version ${nodeVersion}`));
}

console.log(chalk.green('🎉 Setup completed successfully!\n'));

console.log(chalk.blue('📋 Available commands:'));
console.log(chalk.cyan('  npm run version:patch') + '  - Increment patch version (1.0.0 → 1.0.1)');
console.log(chalk.cyan('  npm run version:minor') + '  - Increment minor version (1.0.0 → 1.1.0)');
console.log(chalk.cyan('  npm run version:major') + '  - Increment major version (1.0.0 → 2.0.0)');
console.log(chalk.cyan('  npm run version:set --version=1.2.3') + ' - Set specific version');
console.log(chalk.cyan('  npm run build') + '        - Update version across files');
console.log(chalk.cyan('  npm run zip') + '          - Create distribution ZIP');
console.log(chalk.cyan('  npm run release') + '      - Build and create ZIP');

console.log(chalk.blue('\n🎯 Quick start:'));
console.log(chalk.gray('  1. Make your changes'));
console.log(chalk.gray('  2. Run: npm run version:patch'));
console.log(chalk.gray('  3. Run: npm run release'));
console.log(chalk.gray('  4. Commit and push your changes\n'));

console.log(chalk.green('Happy coding! 🎉'));
