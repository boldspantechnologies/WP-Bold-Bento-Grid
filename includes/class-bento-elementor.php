<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bento_Elementor {

	const MIN_ELEMENTOR_VERSION = '3.5.0';

	const CATEGORY_SLUG = 'bento-grid-category';

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'bento_check_environment' ), 20 );
	}

	public function bento_check_environment() {
		if ( ! did_action( 'elementor/loaded' ) && ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		if ( ! $this->bento_meets_elementor_version() ) {
			add_action( 'admin_notices', array( $this, 'bento_render_version_notice' ) );
			return;
		}

		add_action( 'elementor/elements/categories_registered', array( $this, 'bento_register_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'bento_register_widget' ) );
	}

	private function bento_meets_elementor_version() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return false;
		}

		return version_compare( ELEMENTOR_VERSION, self::MIN_ELEMENTOR_VERSION, '>=' );
	}

	public function bento_render_version_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$message = sprintf(
			esc_html__( 'Bold Bento Grid requires Elementor %s or higher. Please update Elementor to use the Bento Grid widget.', 'bold-bento-grid' ),
			esc_html( self::MIN_ELEMENTOR_VERSION )
		);

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			wp_kses_post( $message )
		);
	}

	public function bento_register_category( $elements_manager ) {
		$category_args = array(
			'title' => esc_html__( 'Bento Engine', 'bold-bento-grid' ),
			'icon'  => 'eicon-gallery-grid',
		);

		if ( ! $this->bento_prepend_category( $elements_manager, $category_args ) ) {
			$elements_manager->add_category( self::CATEGORY_SLUG, $category_args );
		}
	}

	private function bento_prepend_category( $elements_manager, $category_args ) {
		try {
			$reflection = new ReflectionClass( $elements_manager );

			if ( ! $reflection->hasProperty( 'categories' ) ) {
				return false;
			}

			$property = $reflection->getProperty( 'categories' );
			$property->setAccessible( true );

			$existing_categories = $property->getValue( $elements_manager );

			if ( ! is_array( $existing_categories ) ) {
				return false;
			}

			$property->setValue(
				$elements_manager,
				array( self::CATEGORY_SLUG => $category_args ) + $existing_categories
			);

			return true;
		} catch ( \Throwable $exception ) {
			error_log( 'Bento Grid: failed to prepend Elementor category - ' . $exception->getMessage() );
			return false;
		}
	}

	public function bento_register_widget( $widgets_manager ) {
		$widget_class_file = BENTO_GRID_PATH . 'includes/widgets/class-widget-bento.php';

		if ( ! file_exists( $widget_class_file ) ) {
			error_log( 'Bento Grid: Elementor widget class file not found at ' . $widget_class_file );
			return;
		}

		require_once $widget_class_file;

		if ( class_exists( 'Bold_Bento_Elementor_Widget' ) ) {
			$widgets_manager->register( new Bold_Bento_Elementor_Widget() );
		}
	}
}
