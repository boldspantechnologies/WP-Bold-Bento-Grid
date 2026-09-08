<?php
/**
 * Internationalization loader.
 *
 * @package Bento_Grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Bento_I18n
 *
 * Loads the plugin's text domain for translations.
 */
class Bento_I18n {

	/**
	 * Constructor. Wires the text domain loading hook.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'bento_load_textdomain' ) );
	}

	/**
	 * Loads the 'bento-grid' text domain from the plugin's /languages
	 * directory.
	 */
	public function bento_load_textdomain() {
		load_plugin_textdomain(
			'bento-grid',
			false,
			dirname( plugin_basename( BENTO_GRID_FILE ) ) . '/languages'
		);
	}
}
