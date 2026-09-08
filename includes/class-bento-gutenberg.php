<?php
/**
 * Gutenberg block registration and server-render fallback.
 *
 * @package Bento_Grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Bento_Gutenberg
 *
 * Registers the `bold-bento/grid` block from block.json and provides a
 * render_callback that guarantees valid, escaped markup on the frontend
 * even in the rare case the block's static save() content is missing
 * (e.g. a corrupted/edited post_content), rebuilding the exact same
 * `.bento-grid-wrapper > .bento-grid` structure emitted by save.tsx, per
 * AGENT.md 7 (single source of truth for frontend DOM/CSS).
 */
class Bento_Gutenberg {

	/**
	 * Minimum allowed grid column count, mirrored from the editor's
	 * RangeControl bounds in src/blocks/edit.tsx.
	 *
	 * @var int
	 */
	const MIN_COLUMNS = 2;

	/**
	 * Maximum allowed grid column count.
	 *
	 * @var int
	 */
	const MAX_COLUMNS = 4;

	/**
	 * Constructor. Wires block registration to the 'init' hook.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'bento_register_block' ) );
	}

	/**
	 * Registers the Bento Grid block from its block.json metadata.
	 */
	public function bento_register_block() {
		$block_json_path = BENTO_GRID_PATH . 'block.json';

		if ( ! file_exists( $block_json_path ) ) {
			error_log( 'Bento Grid: block.json not found at ' . $block_json_path );
			return;
		}

		$register_args = array(
			'render_callback' => array( $this, 'bento_render_block' ),
		);

		if ( function_exists( 'register_block_type_from_metadata' ) ) {
			register_block_type_from_metadata( BENTO_GRID_PATH, $register_args );
			return;
		}

		// Older WordPress versions: register_block_type() itself accepts
		// a metadata directory/file path as a fallback.
		register_block_type( $block_json_path, $register_args );
	}

	/**
	 * Server-side render callback for the Bento Grid block.
	 *
	 * Under normal operation this simply returns the already-correct
	 * markup produced by save.tsx (passed in as $content). If that
	 * content is ever empty, it rebuilds the same wrapper structure
	 * server-side from sanitized attributes as a defensive fallback.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block save() output (inner HTML).
	 * @return string
	 */
	public function bento_render_block( $attributes, $content ) {
		if ( ! empty( $content ) ) {
			return $content;
		}

		return $this->bento_render_fallback_markup( $attributes );
	}

	/**
	 * Rebuilds the block's wrapper markup from sanitized attributes when
	 * no saved content is available.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
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
