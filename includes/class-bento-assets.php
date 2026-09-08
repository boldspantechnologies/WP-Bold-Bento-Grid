<?php
/**
 * Asset registration and conditional front-end enqueueing.
 *
 * @package Bold_Bento_Grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the compiled editor bundle, front-end runtime and stylesheet, then
 * loads the front-end assets only on requests that actually render a Bento Grid
 * (Gutenberg block or Elementor widget).
 */
class Bento_Assets {

	/**
	 * Editor bundle handle — also referenced from `src/blocks/block.json`
	 * as `editorScript`.
	 */
	const EDITOR_SCRIPT_HANDLE = 'bento-grid-block-editor-script';

	/**
	 * Front-end runtime handle — referenced from `block.json` as `viewScript`
	 * and from the Elementor widget's `get_script_depends()`.
	 */
	const SCRIPT_HANDLE = 'bento-grid-frontend-script';

	/**
	 * Stylesheet handle — referenced from `block.json` as `style` and from the
	 * Elementor widget's `get_style_depends()`.
	 */
	const STYLE_HANDLE = 'bento-grid-frontend-style';

	const BLOCK_NAME = 'bold-bento/grid';

	const ELEMENTOR_WIDGET_NAME = 'bento-grid-widget';

	public function __construct() {
		add_action( 'init', array( $this, 'bento_register_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'bento_maybe_enqueue_frontend' ) );
	}

	/**
	 * Register (but do not enqueue) every compiled asset.
	 */
	public function bento_register_assets() {
		$editor_asset = $this->bento_get_asset_meta( 'index' );

		wp_register_script(
			self::EDITOR_SCRIPT_HANDLE,
			BENTO_GRID_URL . 'build/index.js',
			$editor_asset['dependencies'],
			$editor_asset['version'],
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( self::EDITOR_SCRIPT_HANDLE, 'bold-bento-grid' );
		}

		if ( file_exists( BENTO_GRID_PATH . 'build/shared.js' ) ) {
			$runtime_asset = $this->bento_get_asset_meta( 'shared' );

			wp_register_script(
				self::SCRIPT_HANDLE,
				BENTO_GRID_URL . 'build/shared.js',
				$runtime_asset['dependencies'],
				$runtime_asset['version'],
				true
			);
		}

		$style_path = BENTO_GRID_PATH . 'build/style-index.css';

		wp_register_style(
			self::STYLE_HANDLE,
			BENTO_GRID_URL . 'build/style-index.css',
			array(),
			file_exists( $style_path ) ? (string) filemtime( $style_path ) : BENTO_GRID_VERSION
		);

		wp_style_add_data( self::STYLE_HANDLE, 'rtl', 'replace' );
	}

	/**
	 * Enqueue the front-end stylesheet + runtime, but only when a Bento Grid is
	 * present on the current request. Keeps the plugin off pages that do not
	 * use it, protecting Core Web Vitals.
	 */
	public function bento_maybe_enqueue_frontend() {
		if ( is_admin() || ! $this->bento_should_enqueue() ) {
			return;
		}

		wp_enqueue_style( self::STYLE_HANDLE );

		if ( wp_script_is( self::SCRIPT_HANDLE, 'registered' ) ) {
			wp_enqueue_script( self::SCRIPT_HANDLE );

			wp_localize_script(
				self::SCRIPT_HANDLE,
				'bentoGridConfig',
				array(
					'restUrl' => esc_url_raw( rest_url( 'bento-grid/v1' ) ),
					'nonce'   => wp_create_nonce( 'wp_rest' ),
					'version' => BENTO_GRID_VERSION,
				)
			);
		}
	}

	/**
	 * @return bool Whether the current request renders a Bento Grid.
	 */
	private function bento_should_enqueue() {
		$post = get_post();

		if ( $post instanceof WP_Post ) {
			if ( has_block( self::BLOCK_NAME, $post ) ) {
				return true;
			}

			if ( $this->bento_post_has_elementor_widget( $post ) ) {
				return true;
			}
		}

		/**
		 * Force-load the Bento Grid front-end assets.
		 *
		 * Useful for template locations Core cannot introspect (widgets,
		 * theme parts, Pro dynamic loops).
		 *
		 * @param bool         $enqueue Default false.
		 * @param WP_Post|null $post    Current post object, if any.
		 */
		return (bool) apply_filters( 'bento_grid_should_enqueue_assets', false, $post );
	}

	/**
	 * @param WP_Post $post Post to inspect.
	 * @return bool Whether the post's Elementor data contains the Bento widget.
	 */
	private function bento_post_has_elementor_widget( $post ) {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return false;
		}

		$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );

		return is_string( $elementor_data )
			&& '' !== $elementor_data
			&& false !== strpos( $elementor_data, self::ELEMENTOR_WIDGET_NAME );
	}

	/**
	 * Read a `*.asset.php` manifest emitted by `@wordpress/scripts`.
	 *
	 * @param string $slug Build entry slug (e.g. `index`, `shared`).
	 * @return array{dependencies: string[], version: string}
	 */
	private function bento_get_asset_meta( $slug ) {
		$defaults = array(
			'dependencies' => array(),
			'version'      => BENTO_GRID_VERSION,
		);

		$asset_path = BENTO_GRID_PATH . 'build/' . $slug . '.asset.php';

		if ( ! file_exists( $asset_path ) ) {
			return $defaults;
		}

		$asset = require $asset_path;

		if ( ! is_array( $asset ) ) {
			return $defaults;
		}

		return array(
			'dependencies' => isset( $asset['dependencies'] ) ? (array) $asset['dependencies'] : $defaults['dependencies'],
			'version'      => isset( $asset['version'] ) ? (string) $asset['version'] : $defaults['version'],
		);
	}
}
