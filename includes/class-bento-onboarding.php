<?php
/**
 * Post-activation onboarding: a one-time redirect to a "Getting started"
 * screen, plus a dismissible pointer notice.
 *
 * @package Bold_Bento_Grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bento_Onboarding {

	const PAGE_SLUG = 'bold-bento-grid-welcome';

	const REDIRECT_TRANSIENT = 'bento_grid_activation_redirect';

	const NOTICE_OPTION = 'bento_grid_show_welcome_notice';

	const NOTICE_DISMISS_META = 'bento_grid_welcome_notice_dismissed';

	const STYLE_HANDLE = 'bento-grid-welcome';

	const PRO_URL = 'https://bentogrid.boldspan.tech';

	const CAPABILITY = 'edit_posts';

	/**
	 * @var string|false Hook suffix of the welcome page.
	 */
	private $page_hook = false;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'bento_register_page' ) );
		add_action( 'admin_init', array( $this, 'bento_maybe_redirect' ) );
		add_action( 'admin_init', array( $this, 'bento_maybe_dismiss_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'bento_enqueue_assets' ) );
		add_action( 'admin_notices', array( $this, 'bento_render_notice' ) );
		add_filter( 'plugin_action_links_' . BENTO_GRID_BASENAME, array( $this, 'bento_action_links' ) );
	}

	/**
	 * Runs from the plugin activation hook. Cannot assume this class is loaded,
	 * so the main file requires the file and calls this statically.
	 */
	public static function bento_on_activate() {
		set_transient( self::REDIRECT_TRANSIENT, 1, 30 );
		update_option( self::NOTICE_OPTION, '1' );
	}

	public function bento_register_page() {
		$this->page_hook = add_submenu_page(
			'index.php',
			__( 'Get started with Bento Grid', 'bold-bento-grid' ),
			__( 'Bento Grid', 'bold-bento-grid' ),
			self::CAPABILITY,
			self::PAGE_SLUG,
			array( $this, 'bento_render_page' )
		);

		if ( $this->page_hook ) {
			remove_submenu_page( 'index.php', self::PAGE_SLUG );
		}
	}

	public function bento_enqueue_assets( $hook_suffix ) {
		if ( ! $this->page_hook || $hook_suffix !== $this->page_hook ) {
			return;
		}

		wp_enqueue_style(
			self::STYLE_HANDLE,
			BENTO_GRID_URL . 'assets/css/bento-welcome.css',
			array(),
			BENTO_GRID_VERSION
		);
	}

	public function bento_action_links( $links ) {
		$get_started = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'index.php?page=' . self::PAGE_SLUG ) ),
			esc_html__( 'Get started', 'bold-bento-grid' )
		);

		array_unshift( $links, $get_started );

		return $links;
	}

	/**
	 * One-time redirect to the welcome screen right after a single activation.
	 */
	public function bento_maybe_redirect() {
		if ( ! get_transient( self::REDIRECT_TRANSIENT ) ) {
			return;
		}

		delete_transient( self::REDIRECT_TRANSIENT );

		if ( wp_doing_ajax() || is_network_admin() ) {
			return;
		}

		// Skip when several plugins were activated at once.
		if ( isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'index.php?page=' . self::PAGE_SLUG ) );
		exit;
	}

	public function bento_maybe_dismiss_notice() {
		if ( ! isset( $_GET['bento-dismiss-welcome'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		check_admin_referer( 'bento_dismiss_welcome' );

		update_user_meta( get_current_user_id(), self::NOTICE_DISMISS_META, '1' );
		delete_option( self::NOTICE_OPTION );

		wp_safe_redirect( remove_query_arg( array( 'bento-dismiss-welcome', '_wpnonce' ) ) );
		exit;
	}

	public function bento_render_notice() {
		if ( '1' !== get_option( self::NOTICE_OPTION ) ) {
			return;
		}

		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		// Not on the welcome screen itself.
		if ( isset( $_GET['page'] ) && self::PAGE_SLUG === sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		if ( get_user_meta( get_current_user_id(), self::NOTICE_DISMISS_META, true ) ) {
			return;
		}

		$welcome_url = admin_url( 'index.php?page=' . self::PAGE_SLUG );
		$dismiss_url = wp_nonce_url(
			add_query_arg( 'bento-dismiss-welcome', '1' ),
			'bento_dismiss_welcome'
		);

		printf(
			'<div class="notice notice-info"><p>%1$s <a href="%2$s">%3$s</a> &nbsp;&middot;&nbsp; <a href="%4$s">%5$s</a></p></div>',
			esc_html__( 'Bold Bento Grid is ready.', 'bold-bento-grid' ),
			esc_url( $welcome_url ),
			esc_html__( 'See how to add your first grid', 'bold-bento-grid' ),
			esc_url( $dismiss_url ),
			esc_html__( 'Dismiss', 'bold-bento-grid' )
		);
	}

	private function bento_elementor_active() {
		return did_action( 'elementor/loaded' ) || class_exists( '\Elementor\Plugin' );
	}

	public function bento_render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'bold-bento-grid' ) );
		}

		delete_option( self::NOTICE_OPTION );

		$new_page_url      = admin_url( 'post-new.php?post_type=page' );
		$elementor_active  = $this->bento_elementor_active();
		$elementor_install = admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' );
		$plugins_url       = admin_url( 'plugins.php' );
		?>
		<div class="wrap bento-welcome">
			<h1><?php esc_html_e( 'Get started with Bento Grid', 'bold-bento-grid' ); ?></h1>
			<p class="bento-welcome__lead">
				<?php esc_html_e( 'Build a modern bento-box layout in a couple of minutes. Pick the editor you use below.', 'bold-bento-grid' ); ?>
			</p>

			<div class="bento-welcome__grid">
				<div class="bento-welcome__card">
					<h2><?php esc_html_e( 'Block editor (Gutenberg)', 'bold-bento-grid' ); ?></h2>
					<ol>
						<li><?php esc_html_e( 'Open or create a post or page.', 'bold-bento-grid' ); ?></li>
						<li><?php esc_html_e( 'Click the + block inserter and search for "Bento Grid".', 'bold-bento-grid' ); ?></li>
						<li><?php esc_html_e( 'In the block sidebar, set the number of tiles and choose a layout preset.', 'bold-bento-grid' ); ?></li>
						<li><?php esc_html_e( 'Select each tile to add an image, a title and caption, colours, borders, or a hover effect.', 'bold-bento-grid' ); ?></li>
					</ol>
					<a class="button button-primary" href="<?php echo esc_url( $new_page_url ); ?>">
						<?php esc_html_e( 'Create a new page', 'bold-bento-grid' ); ?>
					</a>
				</div>

				<div class="bento-welcome__card">
					<h2><?php esc_html_e( 'Elementor', 'bold-bento-grid' ); ?></h2>
					<?php if ( $elementor_active ) : ?>
						<p class="bento-welcome__status is-ok">
							<?php esc_html_e( 'Elementor is active — the widget is ready to use.', 'bold-bento-grid' ); ?>
						</p>
						<ol>
							<li><?php esc_html_e( 'Edit a page with Elementor.', 'bold-bento-grid' ); ?></li>
							<li><?php esc_html_e( 'In the widget panel, open the "Bento Engine" category, or search for "Bento Grid".', 'bold-bento-grid' ); ?></li>
							<li><?php esc_html_e( 'Drag the Bento Grid widget onto the canvas.', 'bold-bento-grid' ); ?></li>
							<li><?php esc_html_e( 'Use the Layout, Tiles, and Style tabs to configure the grid.', 'bold-bento-grid' ); ?></li>
						</ol>
						<a class="button" href="<?php echo esc_url( $plugins_url ); ?>">
							<?php esc_html_e( 'Go to Plugins', 'bold-bento-grid' ); ?>
						</a>
					<?php else : ?>
						<p class="bento-welcome__status">
							<?php esc_html_e( 'Elementor is not installed. Install it to get the Bento Grid widget — the block editor works without it.', 'bold-bento-grid' ); ?>
						</p>
						<a class="button" href="<?php echo esc_url( $elementor_install ); ?>">
							<?php esc_html_e( 'Search for Elementor', 'bold-bento-grid' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

			
		</div>
		<?php
	}
}
