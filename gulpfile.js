/**
 * Build + package pipeline for the WordPress.org release of Bold Bento Grid.
 *
 *   gulp build    -> compile src/ into build/ via wp-scripts
 *   gulp copy     -> stage the distributable files into dist/<slug>/
 *   gulp zip      -> zip dist/<slug>/ into dist/<slug>.zip (+ a versioned copy)
 *   gulp package  -> clean -> build -> copy -> zip   (default task)
 */

const { series, src, dest } = require( 'gulp' );
const { execFileSync } = require( 'child_process' );
const fs = require( 'fs' );
const path = require( 'path' );
const del = require( 'del' );
const zip = require( 'gulp-zip' );

const SLUG = 'bold-bento-grid';
const DIST_DIR = 'dist';
const STAGE_DIR = path.join( DIST_DIR, SLUG );

/**
 * The files that make up the shipped plugin. Everything else (node_modules, the
 * TypeScript/SCSS sources, build tooling, dot-files) is excluded here and in
 * .distignore, which `wp-scripts plugin-zip` and wp-cli honour.
 */
const DIST_GLOBS = [
	'bold-bento-grid.php',
	'uninstall.php',
	'readme.txt',
	'LICENSE',
	'includes/**/*',
	'assets/**/*',
	'build/**/*',
	'!**/.DS_Store',
	'!**/*.map',
];

function pluginVersion() {
	const header = fs.readFileSync( `${ SLUG }.php`, 'utf8' );
	const match = header.match( /^\s*\*\s*Version:\s*(.+)$/m );

	if ( ! match ) {
		throw new Error( 'Could not read "Version:" from the plugin header.' );
	}

	return match[ 1 ].trim();
}

function clean() {
	return del( [ DIST_DIR, 'build' ] );
}

function cleanDist() {
	return del( [ DIST_DIR ] );
}

function build( done ) {
	const wpScripts = require.resolve( '@wordpress/scripts/bin/wp-scripts.js' );
	execFileSync( process.execPath, [ wpScripts, 'build', '--webpack-copy-php' ], {
		stdio: 'inherit',
	} );
	done();
}

function copy() {
	return src( DIST_GLOBS, { base: '.', encoding: false, dot: false, nodir: true } ).pipe(
		dest( STAGE_DIR )
	);
}

function makeZip() {
	return src( `${ STAGE_DIR }/**/*`, { base: DIST_DIR, encoding: false, dot: false, nodir: true } )
		.pipe( zip( `${ SLUG }.zip` ) )
		.pipe( dest( DIST_DIR ) );
}

function versionZip( done ) {
	const version = pluginVersion();
	fs.copyFileSync(
		path.join( DIST_DIR, `${ SLUG }.zip` ),
		path.join( DIST_DIR, `${ SLUG }-${ version }.zip` )
	);
	done();
}

exports.clean = clean;
exports.build = build;
exports.copy = series( cleanDist, copy );
exports.zip = series( makeZip, versionZip );
exports.package = series( clean, build, cleanDist, copy, makeZip, versionZip );
exports.default = exports.package;
