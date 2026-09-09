<?php
/**
 * Plugin Name:       Bold Bento Grid
 * Description:       A modern, responsive Bento-style grid for Gutenberg and Elementor with curated presets, per-tile content modes, and hover effects.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Bold Span Technologies
 * Author URI:        https://boldspan.tech
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bold-bento-grid
 *
 * @package Bento_Grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'BENTO_GRID_VERSION', '1.0.0' );
define( 'BENTO_GRID_FILE', __FILE__ );
define( 'BENTO_GRID_PATH', plugin_dir_path( __FILE__ ) );
define( 'BENTO_GRID_URL', plugin_dir_url( __FILE__ ) );
define( 'BENTO_GRID_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Core plugin loader. Boots on `plugins_loaded`, verifies the environment,
 * then loads every feature component.
 */
final class Bento_Grid {

	const VERSION = '1.0.0';

	const MIN_PHP_VERSION = '7.4';

	const MIN_WP_VERSION = '6.4';

	/**
	 * @var array<string, string> class name => file path relative to plugin root.
	 */
	const COMPONENTS = array(
		'Bento_Assets'     => 'includes/class-bento-assets.php',
		'Bento_Gutenberg'  => 'includes/class-bento-gutenberg.php',
		'Bento_Elementor'  => 'includes/class-bento-elementor.php',
		'Bento_Rest_Api'   => 'includes/class-bento-rest-api.php',
		'Bento_Onboarding' => 'includes/class-bento-onboarding.php',
	);

	/**
	 * @var Bento_Grid|null
	 */
	private static $instance = null;

	/**
	 * Instantiated components, keyed by class name.
	 *
	 * @var array<string, object>
	 */
	private $components = array();

	/**
	 * Singleton accessor.
	 *
	 * @return Bento_Grid
	 */
	public static function bento_get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'bento_bootstrap' ) );
	}

	/**
	 * Verify requirements, then load and initialise every component.
	 */
	public function bento_bootstrap() {
		if ( ! $this->bento_meets_requirements() ) {
			add_action( 'admin_notices', array( $this, 'bento_render_requirements_notice' ) );
			return;
		}

		foreach ( self::COMPONENTS as $class => $relative_path ) {
			$file = BENTO_GRID_PATH . $relative_path;

			if ( ! file_exists( $file ) ) {
				continue;
			}

			require_once $file;

			if ( class_exists( $class ) ) {
				$this->components[ $class ] = new $class();
			}
		}

		/**
		 * Fires once the Bento Grid engine has finished booting.
		 *
		 * @param Bento_Grid $plugin The plugin instance.
		 */
		do_action( 'bento_grid_loaded', $this );
	}

	/**
	 * @return bool
	 */
	private function bento_meets_requirements() {
		global $wp_version;

		return version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '>=' )
			&& version_compare( $wp_version, self::MIN_WP_VERSION, '>=' );
	}

	/**
	 * Retrieve an instantiated component (or null when unavailable).
	 *
	 * @param string $class Component class name.
	 * @return object|null
	 */
	public function bento_get_component( $class ) {
		return isset( $this->components[ $class ] ) ? $this->components[ $class ] : null;
	}

	/**
	 * Admin notice shown when the host environment is too old.
	 */
	public function bento_render_requirements_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$message = sprintf(
			/* translators: 1: minimum PHP version, 2: minimum WordPress version. */
			esc_html__( 'Bold Bento Grid requires PHP %1$s+ and WordPress %2$s+. Please upgrade your environment to activate the plugin.', 'bold-bento-grid' ),
			esc_html( self::MIN_PHP_VERSION ),
			esc_html( self::MIN_WP_VERSION )
		);

		printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
	}
}

Bento_Grid::bento_get_instance();

register_activation_hook(
	__FILE__,
	static function () {
		$onboarding = BENTO_GRID_PATH . 'includes/class-bento-onboarding.php';

		if ( file_exists( $onboarding ) ) {
			require_once $onboarding;
			Bento_Onboarding::bento_on_activate();
		}
	}
);

register_deactivation_hook(
	__FILE__,
	static function () {
		delete_transient( 'bento_grid_activation_redirect' );
		delete_option( 'bento_grid_show_welcome_notice' );
	}
);
