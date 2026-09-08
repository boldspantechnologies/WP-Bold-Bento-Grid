<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bento_Assets {

	const SCRIPT_HANDLE = 'bento-grid-frontend-script';

	const STYLE_HANDLE = 'bento-grid-frontend-style';

	public function __construct() {
		add_action( 'init', array( $this, 'bento_register_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'bento_maybe_enqueue_assets' ) );
	}

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

		$style_path = BENTO_GRID_PATH . 'build/style-index.css';
		$style_ver  = file_exists( $style_path ) ? filemtime( $style_path ) : BENTO_GRID_VERSION;

		wp_register_style(
			self::STYLE_HANDLE,
			BENTO_GRID_URL . 'build/style-index.css',
			array(),
			$style_ver
		);

		wp_style_add_data( self::STYLE_HANDLE, 'rtl', 'replace' );
	}

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

		return (bool) apply_filters( 'bento_grid_should_enqueue_assets', false, $post );
	}

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
