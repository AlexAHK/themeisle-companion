var path = require('path');

module.exports = {
	entry: {
		'obfx_modules/mystock-import/js/script' : path.resolve(__dirname, 'obfx_modules/mystock-import/js/src/registerPlugin.js'),
	},
	output: {
		path: path.resolve(__dirname),
		filename: '[name].js'
	},
	watch: true,
	module: {
		rules: [
			{
				test: /.jsx?$/,
				loader: 'babel-loader',
				exclude: /node_modules/,
				query: {
					presets: ['es2015', 'react']
				}
			},
		]
	},
};
