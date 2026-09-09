<?php
/**
 * Uninstall cleanup for WP Bold Bento Grid.
 *
 * @package Bold_Bento_Grid
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bento_grid_show_welcome_notice' );
delete_transient( 'bento_grid_activation_redirect' );
delete_metadata( 'user', 0, 'bento_grid_welcome_notice_dismissed', '', true );
