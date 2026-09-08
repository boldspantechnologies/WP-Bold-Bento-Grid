<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bento_I18n {

	public function __construct() {
		add_action( 'init', array( $this, 'bento_load_textdomain' ) );
	}

	public function bento_load_textdomain() {
		load_plugin_textdomain(
			'bento-grid',
			false,
			dirname( plugin_basename( BENTO_GRID_FILE ) ) . '/languages'
		);
	}
}
