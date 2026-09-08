<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bento_Gutenberg {

	const MIN_COLUMNS = 2;

	const MAX_COLUMNS = 4;

	const EDITOR_SCRIPT_HANDLE = 'bento-grid-block-editor-script';

	public function __construct() {
		add_action( 'init', array( $this, 'bento_register_block' ) );
	}

	public function bento_register_block() {
		$block_json_path = BENTO_GRID_PATH . 'block.json';

		if ( ! file_exists( $block_json_path ) ) {
			error_log( 'Bento Grid: block.json not found at ' . $block_json_path );
			return;
		}

		$this->bento_register_editor_script();

		$register_args = array(
			'render_callback' => array( $this, 'bento_render_block' ),
		);

		if ( function_exists( 'register_block_type_from_metadata' ) ) {
			register_block_type_from_metadata( BENTO_GRID_PATH, $register_args );
			return;
		}

		register_block_type( $block_json_path, $register_args );
	}

	private function bento_register_editor_script() {
		$editor_script_path = BENTO_GRID_PATH . 'build/index.js';

		if ( ! file_exists( $editor_script_path ) ) {
			error_log( 'Bento Grid: build/index.js not found; run npm run build.' );
			return;
		}

		$asset_file_path = BENTO_GRID_PATH . 'build/index.asset.php';

		$asset = file_exists( $asset_file_path )
			? require $asset_file_path
			: array(
				'dependencies' => array(),
				'version'      => BENTO_GRID_VERSION,
			);

		wp_register_script(
			self::EDITOR_SCRIPT_HANDLE,
			BENTO_GRID_URL . 'build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);
	}

	public function bento_render_block( $attributes, $content ) {
		if ( ! empty( $content ) ) {
			return $content;
		}

		return $this->bento_render_fallback_markup( $attributes );
	}

	private function bento_render_fallback_markup( $attributes ) {
		$columns = isset( $attributes['columns'] ) ? absint( $attributes['columns'] ) : self::MIN_COLUMNS + 1;
		$columns = max( self::MIN_COLUMNS, min( self::MAX_COLUMNS, $columns ) );

		$gap = isset( $attributes['gap'] ) ? absint( $attributes['gap'] ) : 16;

		$style = sprintf( '--bento-columns:%d;--bento-gap:%dpx;', $columns, $gap );

		return sprintf(
			'<div class="bento-grid-wrapper" data-bento-grid-columns="%1$d" data-bento-grid-gap="%2$d"><div class="bento-grid" style="%3$s"></div></div>',
			esc_attr( $columns ),
			esc_attr( $gap ),
			esc_attr( $style )
		);
	}
}
