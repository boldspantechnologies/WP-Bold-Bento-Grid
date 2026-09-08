module.exports = {
	content: [ './src/**/*.{ts,tsx,php,scss}' ],
	prefix: 'bento-',
	important: false,
	corePlugins: {
		preflight: false,
	},
	theme: {
		extend: {
			colors: {
				'bento-bg': '#0a0a0a',
				'bento-surface': '#111111',
				'bento-border': '#27272a',
			},
			borderRadius: {
				'bento-lg': '1.25rem',
			},
		},
	},
	plugins: [],
};
