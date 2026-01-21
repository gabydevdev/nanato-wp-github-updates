// WordPress webpack config.
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

// Plugins.
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');

// Utilities.
const path = require('path');

// Environment check
const isProduction = process.env.NODE_ENV === 'production';

// Disable RTL CSS generation
const filteredPlugins = defaultConfig.plugins.filter(
	plugin => plugin.constructor.name !== 'RtlCssPlugin'
);

// Add any new entry points by extending the webpack config.
module.exports = {
	...defaultConfig,
	entry: {
		'js/admin': path.resolve(process.cwd(), 'src/js/admin-script.js'),
		'js/editor': path.resolve(process.cwd(), 'src/js/editor-script.js'),
		'js/frontend': path.resolve(process.cwd(), 'src/js/frontend-script.js'),
		'css/admin': path.resolve(process.cwd(), 'src/scss/admin-styles.scss'),
		'css/editor': path.resolve(process.cwd(), 'src/scss/editor-styles.scss'),
		'css/frontend': path.resolve(process.cwd(), 'src/scss/frontend-styles.scss'),
	},
	output: {
		...defaultConfig.output,
		clean: true,
	},
	devtool: isProduction ? false : 'source-map',
	module: {
		...defaultConfig.module,
		rules: [
			...defaultConfig.module.rules,
			{
				test: /\.(png|jpe?g|gif|svg)$/i,
				type: 'asset/resource',
				generator: {
					filename: 'images/[name][ext]',
				},
				parser: {
					dataUrlCondition: {
						maxSize: 8 * 1024, // 8kb
					},
				},
			},
		],
	},
	optimization: {
		...defaultConfig.optimization,
		splitChunks: {
			cacheGroups: {
				style: {
					type: 'css/mini-extract',
					enforce: true,
				},
			},
		},
	},
	plugins: [
		...filteredPlugins,
		new RemoveEmptyScriptsPlugin({
			stage: RemoveEmptyScriptsPlugin.STAGE_AFTER_PROCESS_PLUGINS,
		}),
	],
	performance: {
		maxEntrypointSize: 512000,
		maxAssetSize: 512000,
	},
};
