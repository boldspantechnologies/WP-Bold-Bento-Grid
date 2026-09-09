<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

class Bento_Pro_Hooks {

	const EDITOR_SCRIPT_HANDLE = 'bento-grid-block-editor-script';

	const ELEMENTOR_WIDGET_NAME = 'bento-grid-widget';

	const ELEMENTOR_SECTION_ID = 'bento_section_layout';

	public function __construct() {
		add_action(
			'elementor/element/' . self::ELEMENTOR_WIDGET_NAME . '/' . self::ELEMENTOR_SECTION_ID . '/before_section_end',
			array( $this, 'bento_render_elementor_upsell' ),
			10,
			2
		);

		add_action( 'enqueue_block_editor_assets', array( $this, 'bento_enqueue_editor_upsell' ) );
	}

	public static function bento_is_pro() {
		/**
		 * Filter whether Bold Bento Grid Pro is active.
		 *
		 * @param bool $is_pro Default false.
		 */
		return (bool) apply_filters( 'bento_grid_is_pro', false );
	}

	public function bento_render_elementor_upsell( $element, $args ) {
		if ( self::bento_is_pro() ) {
			return;
		}

		if ( ! class_exists( '\Elementor\Controls_Manager' ) ) {
			return;
		}

		$locked_presets = array(
			__( 'Glassmorphism 8-tile', 'bold-bento-grid' ),
			__( 'WooCommerce Auto-Fetch Grid', 'bold-bento-grid' ),
			__( 'Interactive Product Bento', 'bold-bento-grid' ),
		);

		$items = '';
		foreach ( $locked_presets as $preset ) {
			$items .= sprintf( '<li>🔒 %s</li>', esc_html( $preset ) );
		}

		$pro_url = esc_url( apply_filters( 'bento_grid_pro_url', 'https://bentogrid.boldspan.tech' ) );

		$element->add_control(
			'bento_pro_upsell_notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => sprintf(
					'<div class="bento-pro-upsell-notice"><p><strong>%1$s</strong></p><ul>%2$s</ul><p><a href="%3$s" target="_blank" rel="noopener noreferrer">%4$s</a></p></div>',
					esc_html__( 'Unlock 6+ Tile Layouts, Glassmorphism, Dynamic Product Grids, & Hover Animations in Bento Grid Pro', 'bold-bento-grid' ),
					$items,
					$pro_url,
					esc_html__( 'Explore Bento Grid Pro →', 'bold-bento-grid' )
				),
				'content_classes' => 'bento-pro-upsell-notice-wrap',
			)
		);
	}

	/**
	 * Expose the Pro state to the block editor via `window.bentoGridPro`.
	 */
	public function bento_enqueue_editor_upsell() {
		if ( ! wp_script_is( self::EDITOR_SCRIPT_HANDLE, 'registered' ) ) {
			return;
		}

		wp_localize_script(
			self::EDITOR_SCRIPT_HANDLE,
			'bentoGridPro',
			array(
				'isPro' => self::bento_is_pro(),
				'url'   => esc_url_raw( apply_filters( 'bento_grid_pro_url', 'https://bentogrid.boldspan.tech' ) ),
			)
		);
	}
}
