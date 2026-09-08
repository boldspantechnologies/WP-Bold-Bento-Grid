<?php
/**
 * Asset registration and conditional enqueuing.
 *
 * @package Bento_Grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Bento_Assets
 *
 * Registers shared build assets and enqueues them only on requests where
 * the Bento Grid block or Elementor widget is actually rendered, per
 * AGENT.md 4 (conditional loading, cache busting via BENTO_GRID_VERSION).
 */
class Bento_Assets {

	/**
	 * Handle for the shared frontend script.
	 *
	 * @var string
	 */
	const SCRIPT_HANDLE = 'bento-grid-frontend-script';

	/**
	 * Handle for the shared frontend style.
	 *
	 * @var string
	 */
	const STYLE_HANDLE = 'bento-grid-frontend-style';

	/**
	 * Constructor. Wires registration and conditional enqueuing hooks.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'bento_register_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'bento_maybe_enqueue_assets' ) );
	}

	/**
	 * Registers (but does not enqueue) the plugin's shared build assets.
	 */
	public function bento_register_assets() {
		$script_asset_path = BENTO_GRID_PATH . 'build/shared.asset.php';

		$script_asset = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => BENTO_GRID_VERSION,
			);

		wp_register_script(
			self::SCRIPT_HANDLE,
			BENTO_GRID_URL . 'build/shared.js',
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		$style_path = BENTO_GRID_PATH . 'build/index.css';
		$style_ver  = file_exists( $style_path ) ? filemtime( $style_path ) : BENTO_GRID_VERSION;

		wp_register_style(
			self::STYLE_HANDLE,
			BENTO_GRID_URL . 'build/index.css',
			array(),
			$style_ver
		);
	}

	/**
	 * Conditionally enqueues assets only when the Bento Grid block or the
	 * Elementor widget is present on the current front-end request.
	 */
	public function bento_maybe_enqueue_assets() {
		if ( ! $this->bento_should_enqueue() ) {
			return;
		}

		wp_enqueue_style( self::STYLE_HANDLE );
		wp_enqueue_script( self::SCRIPT_HANDLE );

		wp_localize_script(
			self::SCRIPT_HANDLE,
			'bentoGridConfig',
			array(
				'ajaxUrl' => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
				'nonce'   => wp_create_nonce( 'bento_grid_nonce' ),
				'version' => esc_html( BENTO_GRID_VERSION ),
			)
		);
	}

	/**
	 * Determines whether the current request should load Bento Grid
	 * assets: either the Gutenberg block or the Elementor widget is
	 * present in the rendered content.
	 *
	 * @return bool
	 */
	private function bento_should_enqueue() {
		if ( is_admin() ) {
			return false;
		}

		$post = get_post();

		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		if ( has_block( 'bold-bento/grid', $post ) ) {
			return true;
		}

		if ( $this->bento_has_elementor_widget( $post ) ) {
			return true;
		}

		/**
		 * Filters whether Bento Grid frontend assets should be enqueued
		 * for the current request. Allows other detection strategies
		 * (e.g. Elementor widgets rendered via templates) to opt in.
		 *
		 * @param bool    $should_enqueue Whether to enqueue assets.
		 * @param WP_Post $post           Current post object.
		 */
		return (bool) apply_filters( 'bento_grid_should_enqueue_assets', false, $post );
	}

	/**
	 * Checks whether the current post's Elementor data contains a Bento
	 * Grid widget instance.
	 *
	 * @param WP_Post $post Current post object.
	 * @return bool
	 */
	private function bento_has_elementor_widget( $post ) {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return false;
		}

		$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );

		if ( empty( $elementor_data ) || ! is_string( $elementor_data ) ) {
			return false;
		}

		return false !== strpos( $elementor_data, 'bento-grid-widget' );
	}
}
