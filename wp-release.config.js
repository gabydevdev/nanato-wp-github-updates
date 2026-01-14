module.exports = {
  "pluginName": "nanato-wp-github-updates",
  "mainFile": "nanato-wp-github-updates.php",
  "buildDir": "dist",
  "zipName": "{{name}}-{{version}}.zip",
  "excludePatterns": [
    ".git/",
    ".github/",
    "dist/",
    "node_modules/",
    "vendor/",
    ".*",
    "composer.json",
    "composer.lock",
    "package.json",
    "package-lock.json",
    "phpcs*",
    "*.md",
    "*.config.js"
  ],
  "config": {
    "includeGitOps": true,
    "tagPrefix": "v",
    "branch": "main"
  },
  "hooks": {
    "preRelease": [],
    "postRelease": [],
    "preBuild": [],
    "postBuild": []
  }
};