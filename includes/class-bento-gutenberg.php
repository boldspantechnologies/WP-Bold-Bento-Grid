<?php
/**
 * Gutenberg block registration.
 *
 * @package Bold_Bento_Grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bento_Gutenberg {

	const METADATA_FILES = array(
		'build/blocks/block.json',
		'build/blocks/tile/block.json',
	);

	public function __construct() {
		add_action( 'init', array( $this, 'bento_register_blocks' ) );
	}

	public function bento_register_blocks() {
		foreach ( self::METADATA_FILES as $relative_path ) {
			$metadata_file = BENTO_GRID_PATH . $relative_path;

			if ( ! file_exists( $metadata_file ) ) {
				continue;
			}

			register_block_type_from_metadata( $metadata_file );
		}
	}
}
