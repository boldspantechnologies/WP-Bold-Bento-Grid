<?php
/**
 * Central plugin bootstrapper.
 *
 * @package Bento_Grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Bento_Core
 *
 * Singleton controller that verifies environment requirements and wires
 * up the plugin's centralized hooks, per AGENT.md 5 (group hooks into a
 * centralized loader/controller class).
 */
final class Bento_Core {

	/**
	 * Minimum supported PHP version.
	 *
	 * @var string
	 */
	const MIN_PHP_VERSION = '7.4';

	/**
	 * Minimum supported WordPress version.
	 *
	 * @var string
	 */
	const MIN_WP_VERSION = '6.4';

	/**
	 * Singleton instance.
	 *
	 * @var Bento_Core|null
	 */
	private static $instance = null;

	/**
	 * Retrieves the singleton instance, creating it on first call.
	 *
	 * @return Bento_Core
	 */
	public static function bento_get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor is private to enforce the singleton pattern.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'bento_init' ) );
	}

	/**
	 * Verifies environment requirements and loads the plugin.
	 */
	public function bento_init() {
		if ( ! $this->bento_meets_requirements() ) {
			add_action( 'admin_notices', array( $this, 'bento_render_requirements_notice' ) );
			return;
		}

		$this->bento_load_dependencies();
		$this->bento_register_hooks();
	}

	/**
	 * Checks that the current environment satisfies the minimum PHP and
	 * WordPress versions required by the plugin.
	 *
	 * @return bool
	 */
	private function bento_meets_requirements() {
		global $wp_version;

		$php_ok = version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '>=' );
		$wp_ok  = version_compare( $wp_version, self::MIN_WP_VERSION, '>=' );

		return $php_ok && $wp_ok;
	}

	/**
	 * Renders an admin notice when the environment does not meet the
	 * plugin's minimum requirements.
	 */
	public function bento_render_requirements_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$message = sprintf(
			/* translators: 1: Minimum PHP version. 2: Minimum WordPress version. */
			esc_html__( 'Bold Bento Grid requires PHP %1$s+ and WordPress %2$s+. Please upgrade your environment to activate all features.', 'bento-grid' ),
			esc_html( self::MIN_PHP_VERSION ),
			esc_html( self::MIN_WP_VERSION )
		);

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			wp_kses_post( $message )
		);
	}

	/**
	 * Requires the plugin's core class files.
	 */
	private function bento_load_dependencies() {
		require_once BENTO_GRID_PATH . 'includes/class-bento-i18n.php';
		require_once BENTO_GRID_PATH . 'includes/class-bento-assets.php';
		require_once BENTO_GRID_PATH . 'includes/class-bento-gutenberg.php';
	}

	/**
	 * Instantiates and registers the plugin's core services.
	 */
	private function bento_register_hooks() {
		new Bento_I18n();
		new Bento_Assets();
		new Bento_Gutenberg();

		add_action( 'elementor/widgets/register', array( $this, 'bento_register_elementor_widget' ) );
	}

	/**
	 * Registers the Bento Grid Elementor widget.
	 *
	 * Loaded only when Elementor fires its widget registration hook, per
	 * AGENT.md 7: never load the Elementor integration on every request.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager instance.
	 */
	public function bento_register_elementor_widget( $widgets_manager ) {
		$widget_class_file = BENTO_GRID_PATH . 'includes/elementor/class-bento-grid-widget.php';

		if ( ! file_exists( $widget_class_file ) ) {
			error_log( 'Bento Grid: Elementor widget class file not found at ' . $widget_class_file );
			return;
		}

		require_once $widget_class_file;

		if ( class_exists( 'Bento_Grid_Widget' ) ) {
			$widgets_manager->register( new Bento_Grid_Widget() );
		}
	}
}
