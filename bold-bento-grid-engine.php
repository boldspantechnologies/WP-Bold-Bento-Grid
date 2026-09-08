<?php
/**
 * Plugin Name:       WP Bold Bento Grid
 * Description:       A high-performance, secure, Vercel/Stripe-inspired Bento Grid engine for Gutenberg and Elementor.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Bold Bento Grid
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bento-grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BENTO_GRID_VERSION', '0.1.0' );
define( 'BENTO_GRID_PATH', plugin_dir_path( __FILE__ ) );
define( 'BENTO_GRID_URL', plugin_dir_url( __FILE__ ) );
define( 'BENTO_GRID_FILE', __FILE__ );

require_once BENTO_GRID_PATH . 'includes/class-bento-core.php';

Bento_Core::bento_get_instance();
