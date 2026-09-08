const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		...( defaultConfig.entry ? defaultConfig.entry() : {} ),
		index: path.resolve( process.cwd(), 'src/blocks', 'index.tsx' ),
		shared: path.resolve( process.cwd(), 'src/shared', 'index.ts' ),
	},
	resolve: {
		...defaultConfig.resolve,
		extensions: [ '.tsx', '.ts', '.js', '.jsx', '...' ],
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( process.cwd(), 'build' ),
	},
};
