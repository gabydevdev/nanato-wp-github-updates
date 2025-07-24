module.exports = {
	pluginName: 'nanato-wp-github-updates',
	mainFile: 'nanato-wp-github-updates.php',
	buildDir: 'build',
	zipName: '{{name}}-{{version}}.zip',
	excludePatterns: [
    'node_modules/', 
    '.git/',
    'vendor/',
    '.github/',
    'src/', 
    '*.log', 
    '.env*', 
    'tests/', 
    '*.md', 
    '*.zip', 
    'wp-release.config.js',
    '.*',
    'package.json',
    'package-lock.json'
  ],
	config: {
		includeGitOps: true,
		tagPrefix: 'v',
		branch: 'main',
	},
	hooks: {
		preRelease: [],
		postRelease: [],
		preBuild: [],
		postBuild: [],
	},
};