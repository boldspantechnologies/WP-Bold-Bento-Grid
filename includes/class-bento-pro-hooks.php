<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

class Bento_Pro_Hooks {

	const FILTER_IS_PRO = 'bento_grid_is_pro';

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
		return (bool) apply_filters( self::FILTER_IS_PRO, false );
	}

	public function bento_render_elementor_upsell( $element, $args ) {
		if ( self::bento_is_pro() ) {
			return;
		}

		if ( ! class_exists( '\Elementor\Controls_Manager' ) ) {
			return;
		}

		$element->add_control(
			'bento_pro_upsell_notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => sprintf(
					'<div class="bento-pro-upsell-notice">%s</div>',
					wp_kses_post(
						sprintf(
							__( '🔒 More layout presets, dynamic queries, and 3D tilt effects are available in %s.', 'bento-grid' ),
							'<strong>Bento Grid Pro</strong>'
						)
					)
				),
				'content_classes' => 'bento-pro-upsell-notice-wrap',
			)
		);
	}

	public function bento_enqueue_editor_upsell() {
		if ( self::bento_is_pro() ) {
			return;
		}

		if ( ! wp_script_is( self::EDITOR_SCRIPT_HANDLE, 'registered' ) ) {
			error_log( 'Bento Grid: editor script handle "' . self::EDITOR_SCRIPT_HANDLE . '" not registered; Pro upsell badge skipped.' );
			return;
		}

		$notice_text = esc_js(
			__( 'Unlock layout presets, dynamic queries, and 3D tilt effects with Bento Grid Pro.', 'bento-grid' )
		);
		$panel_title = esc_js( __( 'Bento Pro', 'bento-grid' ) );
		$block_name  = esc_js( 'bold-bento/grid' );

		$inline_script = <<<JS
( function( wp ) {
	if ( ! wp || ! wp.hooks || ! wp.element || ! wp.blockEditor || ! wp.components ) {
		return;
	}

	var addFilter = wp.hooks.addFilter;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var Notice = wp.components.Notice;

	addFilter(
		'editor.BlockEdit',
		'bento-grid/pro-upsell-badge',
		function ( BlockEdit ) {
			return function ( props ) {
				if ( props.name !== '{$block_name}' ) {
					return createElement( BlockEdit, props );
				}

				return createElement(
					Fragment,
					{},
					createElement( BlockEdit, props ),
					createElement(
						InspectorControls,
						{},
						createElement(
							PanelBody,
							{ title: '{$panel_title}', initialOpen: false, icon: 'lock' },
							createElement(
								Notice,
								{ status: 'info', isDismissible: false },
								'{$notice_text}'
							)
						)
					)
				);
			};
		}
	);
} )( window.wp );
JS;

		wp_add_inline_script( self::EDITOR_SCRIPT_HANDLE, $inline_script );
	}
}
