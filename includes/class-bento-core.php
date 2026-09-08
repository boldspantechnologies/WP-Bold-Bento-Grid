<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Bento_Core {

	const MIN_PHP_VERSION = '7.4';

	const MIN_WP_VERSION = '6.4';

	private static $instance = null;

	public static function bento_get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'bento_init' ) );
	}

	public function bento_init() {
		if ( ! $this->bento_meets_requirements() ) {
			add_action( 'admin_notices', array( $this, 'bento_render_requirements_notice' ) );
			return;
		}

		$this->bento_load_dependencies();
		$this->bento_register_hooks();
	}

	private function bento_meets_requirements() {
		global $wp_version;

		$php_ok = version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '>=' );
		$wp_ok  = version_compare( $wp_version, self::MIN_WP_VERSION, '>=' );

		return $php_ok && $wp_ok;
	}

	public function bento_render_requirements_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$message = sprintf(
			esc_html__( 'Bold Bento Grid requires PHP %1$s+ and WordPress %2$s+. Please upgrade your environment to activate all features.', 'bento-grid' ),
			esc_html( self::MIN_PHP_VERSION ),
			esc_html( self::MIN_WP_VERSION )
		);

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			wp_kses_post( $message )
		);
	}

	private function bento_load_dependencies() {
		require_once BENTO_GRID_PATH . 'includes/class-bento-i18n.php';
		require_once BENTO_GRID_PATH . 'includes/class-bento-assets.php';
		require_once BENTO_GRID_PATH . 'includes/class-bento-gutenberg.php';
		require_once BENTO_GRID_PATH . 'includes/class-bento-elementor.php';
		require_once BENTO_GRID_PATH . 'includes/class-bento-rest-api.php';
		require_once BENTO_GRID_PATH . 'includes/class-bento-pro-hooks.php';
	}

	private function bento_register_hooks() {
		new Bento_I18n();
		new Bento_Assets();
		new Bento_Gutenberg();
		new Bento_Elementor();
		new Bento_Rest_Api();
		new Bento_Pro_Hooks();
	}
}
