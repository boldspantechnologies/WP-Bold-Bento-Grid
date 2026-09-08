<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Bento_Widget extends Widget_Base {

	const MIN_COLUMNS = 2;

	const MAX_COLUMNS = 4;

	const TILE_SPANS = array(
		'normal'   => '',
		'featured' => 'bento-tile-featured',
		'wide'     => 'bento-tile-wide',
		'tall'     => 'bento-tile-tall',
	);

	public function get_name() {
		return 'bento-grid-widget';
	}

	public function get_title() {
		return esc_html__( 'Bento Grid', 'bento-grid' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return array( Bento_Elementor::CATEGORY_SLUG );
	}

	public function get_keywords() {
		return array( 'bento', 'grid', 'layout', 'gallery' );
	}

	public function get_script_depends() {
		return array( Bento_Assets::SCRIPT_HANDLE );
	}

	public function get_style_depends() {
		return array( Bento_Assets::STYLE_HANDLE );
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'bento_section_layout',
			array(
				'label' => esc_html__( 'Layout', 'bento-grid' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'bento_columns',
			array(
				'label'   => esc_html__( 'Columns', 'bento-grid' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min'  => self::MIN_COLUMNS,
						'max'  => self::MAX_COLUMNS,
						'step' => 1,
					),
				),
				'default' => array(
					'size' => 3,
					'unit' => 'px',
				),
			)
		);

		$this->add_control(
			'bento_gap',
			array(
				'label'   => esc_html__( 'Gap (px)', 'bento-grid' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min'  => 0,
						'max'  => 48,
						'step' => 4,
					),
				),
				'default' => array(
					'size' => 16,
					'unit' => 'px',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'bento_section_tiles',
			array(
				'label' => esc_html__( 'Tiles', 'bento-grid' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$tile_repeater = new Repeater();

		$tile_repeater->add_control(
			'bento_tile_title',
			array(
				'label'       => esc_html__( 'Title', 'bento-grid' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Tile title', 'bento-grid' ),
				'label_block' => true,
			)
		);

		$tile_repeater->add_control(
			'bento_tile_content',
			array(
				'label'       => esc_html__( 'Content', 'bento-grid' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => '',
				'label_block' => true,
			)
		);

		$tile_repeater->add_control(
			'bento_tile_image',
			array(
				'label'   => esc_html__( 'Image', 'bento-grid' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array(
					'url' => '',
				),
			)
		);

		$tile_repeater->add_control(
			'bento_tile_span',
			array(
				'label'   => esc_html__( 'Tile Size', 'bento-grid' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'normal',
				'options' => array(
					'normal'   => esc_html__( 'Normal', 'bento-grid' ),
					'featured' => esc_html__( 'Featured (2x2)', 'bento-grid' ),
					'wide'     => esc_html__( 'Wide (2x1)', 'bento-grid' ),
					'tall'     => esc_html__( 'Tall (1x2)', 'bento-grid' ),
				),
			)
		);

		$tile_repeater->add_control(
			'bento_tile_link',
			array(
				'label'       => esc_html__( 'Link', 'bento-grid' ),
				'type'        => Controls_Manager::URL,
				'default'     => array(
					'url' => '',
				),
				'label_block' => true,
			)
		);

		$this->add_control(
			'bento_tiles',
			array(
				'label'       => esc_html__( 'Tiles', 'bento-grid' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $tile_repeater->get_controls(),
				'default'     => array(
					array( 'bento_tile_title' => esc_html__( 'Tile One', 'bento-grid' ) ),
					array( 'bento_tile_title' => esc_html__( 'Tile Two', 'bento-grid' ) ),
					array( 'bento_tile_title' => esc_html__( 'Tile Three', 'bento-grid' ) ),
				),
				'title_field' => '{{{ bento_tile_title }}}',
			)
		);

		$this->end_controls_section();
	}

	private function bento_get_columns( $settings ) {
		$columns = isset( $settings['bento_columns']['size'] ) ? absint( $settings['bento_columns']['size'] ) : self::MIN_COLUMNS + 1;

		return max( self::MIN_COLUMNS, min( self::MAX_COLUMNS, $columns ) );
	}

	private function bento_get_gap( $settings ) {
		return isset( $settings['bento_gap']['size'] ) ? absint( $settings['bento_gap']['size'] ) : 16;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$columns = $this->bento_get_columns( $settings );
		$gap     = $this->bento_get_gap( $settings );

		$grid_style = sprintf( '--bento-columns:%d;--bento-gap:%dpx;', $columns, $gap );

		$tiles = isset( $settings['bento_tiles'] ) && is_array( $settings['bento_tiles'] ) ? $settings['bento_tiles'] : array();
		?>
		<div class="bento-grid-wrapper" data-bento-grid-columns="<?php echo esc_attr( $columns ); ?>" data-bento-grid-gap="<?php echo esc_attr( $gap ); ?>">
			<div class="bento-grid" style="<?php echo esc_attr( $grid_style ); ?>">
				<?php foreach ( $tiles as $tile ) : ?>
					<?php $this->bento_render_tile( $tile ); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	private function bento_render_tile( $tile ) {
		$span_key   = isset( $tile['bento_tile_span'] ) ? $tile['bento_tile_span'] : 'normal';
		$span_class = isset( self::TILE_SPANS[ $span_key ] ) ? self::TILE_SPANS[ $span_key ] : '';

		$tile_class = trim( 'bento-grid__tile ' . $span_class );

		$title   = isset( $tile['bento_tile_title'] ) ? $tile['bento_tile_title'] : '';
		$content = isset( $tile['bento_tile_content'] ) ? $tile['bento_tile_content'] : '';
		$image   = isset( $tile['bento_tile_image']['url'] ) ? $tile['bento_tile_image']['url'] : '';
		$link    = isset( $tile['bento_tile_link']['url'] ) ? $tile['bento_tile_link']['url'] : '';

		$is_linked = ! empty( $link );
		$tag       = $is_linked ? 'a' : 'div';
		?>
		<<?php echo tag_escape( $tag ); ?>
			class="<?php echo esc_attr( $tile_class ); ?>"
			<?php if ( $is_linked ) : ?>
				href="<?php echo esc_url( $link ); ?>"
			<?php endif; ?>
		>
			<?php if ( ! empty( $image ) ) : ?>
				<img class="bento-w-full bento-h-full bento-object-cover" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
			<?php endif; ?>

			<?php if ( ! empty( $title ) ) : ?>
				<h3 class="bento-tile-title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>

			<?php if ( ! empty( $content ) ) : ?>
				<p class="bento-tile-content"><?php echo wp_kses_post( $content ); ?></p>
			<?php endif; ?>
		</<?php echo tag_escape( $tag ); ?>>
		<?php
	}
}
