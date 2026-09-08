<?php
/**
 * Gutenberg block registration.
 *
 * @package Bold_Bento_Grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the Bento Grid block and its Tile child block straight from their
 * `block.json` metadata. Both are static blocks — markup is produced by the
 * compiled `save.tsx`, so no PHP render callback is required.
 */
class Bento_Gutenberg {

	/**
	 * Block metadata files, relative to the plugin root.
	 *
	 * @var string[]
	 */
	const METADATA_FILES = array(
		'src/blocks/block.json',
		'src/blocks/tile/block.json',
	);

	public function __construct() {
		add_action( 'init', array( $this, 'bento_register_blocks' ) );
	}

	/**
	 * Register every block from metadata.
	 */
	public function bento_register_blocks() {
		foreach ( self::METADATA_FILES as $relative_path ) {
			$metadata_file = BENTO_GRID_PATH . $relative_path;

			if ( ! file_exists( $metadata_file ) ) {
				error_log( 'Bold Bento Grid: block metadata not found at ' . $metadata_file );
				continue;
			}

			register_block_type_from_metadata( $metadata_file );
		}
	}
}
