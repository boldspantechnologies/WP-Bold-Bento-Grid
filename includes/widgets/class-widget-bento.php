<?php
/**
 * Elementor widget — renders the same markup as the Gutenberg
 * `bold-bento/grid` block so the front end is identical between editors.
 *
 * @package Bold_Bento_Grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

class Bento_Elementor_Widget extends Widget_Base {

	const MAX_TILES = 5;

	const DEFAULT_PRESET = 'hero-3';

	const DEFAULT_GAP = 16;

	const DEFAULT_RADIUS = 16;

	const DEFAULT_BG_COLOR = '#f4f4f5';

	const DEFAULT_OVERLAY_OPACITY = 40;

	const PRESETS = array(
		'hero-3'       => 'Hero 3',
		'showcase-4'   => 'Showcase 4',
		'balanced-4'   => 'Balanced 4',
		'feature-5'    => 'Feature 5',
		'asymmetric-5' => 'Asymmetric 5',
	);

	const CONTENT_MODES = array( 'image-overlay', 'text-top', 'text-bottom' );

	public function get_name() {
		return 'bento-grid-widget';
	}

	public function get_title() {
		return esc_html__( 'Bento Grid', 'bold-bento-grid' );
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

	protected function register_controls() {
		$this->bento_register_layout_controls();
		$this->bento_register_tile_controls();
		$this->bento_register_style_controls();
	}

	private function bento_register_layout_controls() {
		$this->start_controls_section(
			'bento_section_layout',
			array(
				'label' => esc_html__( 'Layout', 'bold-bento-grid' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'bento_layout_preset',
			array(
				'label'       => esc_html__( 'Layout Preset', 'bold-bento-grid' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => self::DEFAULT_PRESET,
				'options'     => array(
					'hero-3'       => esc_html__( 'Hero 3', 'bold-bento-grid' ),
					'showcase-4'   => esc_html__( 'Showcase 4', 'bold-bento-grid' ),
					'balanced-4'   => esc_html__( 'Balanced 4', 'bold-bento-grid' ),
					'feature-5'    => esc_html__( 'Feature 5', 'bold-bento-grid' ),
					'asymmetric-5' => esc_html__( 'Asymmetric 5', 'bold-bento-grid' ),
				),
				'description' => esc_html__( 'Tile size and position are fixed by the preset.', 'bold-bento-grid' ),
			)
		);

		$this->end_controls_section();
	}

	private function bento_register_tile_controls() {
		$this->start_controls_section(
			'bento_section_tiles',
			array(
				'label' => esc_html__( 'Tiles', 'bold-bento-grid' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'bento_content_mode',
			array(
				'label'   => esc_html__( 'Content Mode', 'bold-bento-grid' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'image-overlay',
				'options' => array(
					'image-overlay' => esc_html__( 'Image overlay', 'bold-bento-grid' ),
					'text-top'      => esc_html__( 'Text on top', 'bold-bento-grid' ),
					'text-bottom'   => esc_html__( 'Text below', 'bold-bento-grid' ),
				),
			)
		);

		$repeater->add_control(
			'bento_image',
			array(
				'label'   => esc_html__( 'Image', 'bold-bento-grid' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => '' ),
			)
		);

		$repeater->add_control(
			'bento_title',
			array(
				'label'       => esc_html__( 'Title', 'bold-bento-grid' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'bento_caption',
			array(
				'label'   => esc_html__( 'Caption', 'bold-bento-grid' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => '',
			)
		);

		$repeater->add_control(
			'bento_link',
			array(
				'label'       => esc_html__( 'Link', 'bold-bento-grid' ),
				'type'        => Controls_Manager::URL,
				'default'     => array( 'url' => '' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'bento_overlay_opacity',
			array(
				'label'     => esc_html__( 'Scrim Opacity', 'bold-bento-grid' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 5,
					),
				),
				'default'   => array(
					'size' => self::DEFAULT_OVERLAY_OPACITY,
					'unit' => 'px',
				),
				'condition' => array( 'bento_content_mode' => 'image-overlay' ),
			)
		);

		$repeater->add_control(
			'bento_hover_effect',
			array(
				'label'   => esc_html__( 'Hover Effect', 'bold-bento-grid' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'lift',
				'options' => array(
					'none' => esc_html__( 'None', 'bold-bento-grid' ),
					'lift' => esc_html__( 'Lift', 'bold-bento-grid' ),
					'glow' => esc_html__( 'Glow', 'bold-bento-grid' ),
					'zoom' => esc_html__( 'Image zoom', 'bold-bento-grid' ),
					'tilt' => esc_html__( '3D tilt', 'bold-bento-grid' ),
				),
			)
		);

		$repeater->add_control(
			'bento_bg_color',
			array(
				'label' => esc_html__( 'Background Color', 'bold-bento-grid' ),
				'type'  => Controls_Manager::COLOR,
			)
		);

		$repeater->add_control(
			'bento_bg_gradient',
			array(
				'label'       => esc_html__( 'Background Gradient (CSS)', 'bold-bento-grid' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'linear-gradient(135deg, #6366f1, #ec4899)',
				'description' => esc_html__( 'Overrides the background colour when set.', 'bold-bento-grid' ),
			)
		);

		$repeater->add_control(
			'bento_text_color',
			array(
				'label' => esc_html__( 'Text Color', 'bold-bento-grid' ),
				'type'  => Controls_Manager::COLOR,
			)
		);

		$repeater->add_control(
			'bento_border_width',
			array(
				'label'      => esc_html__( 'Border Width', 'bold-bento-grid' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 8,
						'step' => 1,
					),
				),
			)
		);

		$repeater->add_control(
			'bento_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'bold-bento-grid' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'bento_border_width[size]!' => '' ),
			)
		);

		$this->add_control(
			'bento_tiles',
			array(
				'label'       => esc_html__( 'Tiles', 'bold-bento-grid' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'bento_content_mode' => 'image-overlay' ),
					array( 'bento_content_mode' => 'image-overlay' ),
					array( 'bento_content_mode' => 'image-overlay' ),
				),
				'title_field' => '{{{ bento_title || "Tile" }}}',
				'description' => sprintf(
					/* translators: %d: maximum number of tiles. */
					esc_html__( 'Bento Grid Lite supports up to %d tiles — extra items are ignored.', 'bold-bento-grid' ),
					self::MAX_TILES
				),
			)
		);

		$this->add_control(
			'bento_tiles_pro_notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => sprintf(
					'<div class="bento-pro-upsell-notice"><strong>🔒 %1$s</strong></div>',
					esc_html__( 'Unlock 6+ Tile Layouts, Glassmorphism, Dynamic Product Grids, & Hover Animations in Bento Grid Pro', 'bold-bento-grid' )
				),
				'content_classes' => 'bento-pro-upsell-notice-wrap',
			)
		);

		$this->end_controls_section();
	}

	private function bento_register_style_controls() {
		$this->start_controls_section(
			'bento_section_style',
			array(
				'label' => esc_html__( 'Style', 'bold-bento-grid' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'bento_gap',
			array(
				'label'      => esc_html__( 'Gap', 'bold-bento-grid' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 40,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => self::DEFAULT_GAP,
					'unit' => 'px',
				),
			)
		);

		$this->add_control(
			'bento_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'bold-bento-grid' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 32,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => self::DEFAULT_RADIUS,
					'unit' => 'px',
				),
			)
		);

		$this->add_control(
			'bento_bg_color',
			array(
				'label'   => esc_html__( 'Background Color', 'bold-bento-grid' ),
				'type'    => Controls_Manager::COLOR,
				'default' => self::DEFAULT_BG_COLOR,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * @param array $settings Widget settings.
	 * @return string A valid preset slug.
	 */
	private function bento_get_preset( $settings ) {
		$preset = isset( $settings['bento_layout_preset'] ) ? sanitize_key( $settings['bento_layout_preset'] ) : self::DEFAULT_PRESET;

		return array_key_exists( $preset, self::PRESETS ) ? $preset : self::DEFAULT_PRESET;
	}

	/**
	 * @param array  $settings Widget settings.
	 * @param string $key      Slider control name.
	 * @param int    $fallback Default size.
	 * @return int
	 */
	private function bento_get_slider_px( $settings, $key, $fallback ) {
		if ( isset( $settings[ $key ]['size'] ) && is_numeric( $settings[ $key ]['size'] ) ) {
			return min( 200, absint( $settings[ $key ]['size'] ) );
		}

		return $fallback;
	}

	/**
	 * Conservative CSS-value filter — mirrors `bentoSafeCssValue()` in
	 * `src/shared/sanitize.ts`. Rejects anything that could break out of an
	 * inline declaration or pull in a resource.
	 *
	 * @param mixed $value Raw control value.
	 * @return string Safe value or ''.
	 */
	private function bento_safe_css_value( $value ) {
		$css = is_string( $value ) ? trim( $value ) : '';

		if ( '' === $css ) {
			return '';
		}

		if ( preg_match( '/[;{}<>\\\\]|url\(|@import|expression\(|javascript:/i', $css ) ) {
			return '';
		}

		return $css;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$preset = $this->bento_get_preset( $settings );
		$gap    = $this->bento_get_slider_px( $settings, 'bento_gap', self::DEFAULT_GAP );
		$radius = $this->bento_get_slider_px( $settings, 'bento_border_radius', self::DEFAULT_RADIUS );

		$bg_color = ! empty( $settings['bento_bg_color'] )
			? $this->bento_safe_css_value( $settings['bento_bg_color'] )
			: self::DEFAULT_BG_COLOR;

		if ( '' === $bg_color ) {
			$bg_color = self::DEFAULT_BG_COLOR;
		}

		$tiles = ( isset( $settings['bento_tiles'] ) && is_array( $settings['bento_tiles'] ) )
			? array_slice( $settings['bento_tiles'], 0, self::MAX_TILES )
			: array();

		$style = sprintf(
			'--bento-gap:%dpx;--bento-radius:%dpx;--bento-bg-color:%s',
			$gap,
			$radius,
			$bg_color
		);

		printf(
			'<div class="wp-block-bold-bento-grid bold-bento-wrapper" data-bento-grid="%1$s" style="%2$s">',
			esc_attr( $preset ),
			esc_attr( $style )
		);

		foreach ( $tiles as $tile ) {
			$this->bento_render_tile( $tile );
		}

		echo '</div>';
	}

	private function bento_render_tile( $tile ) {
		$mode = isset( $tile['bento_content_mode'] ) ? sanitize_key( $tile['bento_content_mode'] ) : 'image-overlay';

		if ( ! in_array( $mode, self::CONTENT_MODES, true ) ) {
			$mode = 'image-overlay';
		}

		// URLs are escaped once here (esc_url strips unsafe protocols); the
		// helpers below treat these as already-safe. An unsafe URL becomes ''
		// and is dropped entirely, matching the Gutenberg save output.
		$image_url = isset( $tile['bento_image']['url'] ) ? esc_url( $tile['bento_image']['url'] ) : '';
		$image_id  = isset( $tile['bento_image']['id'] ) ? absint( $tile['bento_image']['id'] ) : 0;

		$alt = $image_id
			? (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true )
			: '';

		$title   = isset( $tile['bento_title'] ) ? (string) $tile['bento_title'] : '';
		$caption = isset( $tile['bento_caption'] ) ? (string) $tile['bento_caption'] : '';
		$link    = isset( $tile['bento_link']['url'] ) ? esc_url( $tile['bento_link']['url'] ) : '';
		$opacity = isset( $tile['bento_overlay_opacity']['size'] ) ? (int) $tile['bento_overlay_opacity']['size'] : self::DEFAULT_OVERLAY_OPACITY;

		$has_media = '' !== $image_url;

		$classes = array(
			'wp-block-bold-bento-tile',
			'bento-grid__tile',
			'bento-tile',
			'bento-mode-' . $mode,
			$has_media ? 'has-media' : 'is-empty',
		);

		$hover = isset( $tile['bento_hover_effect'] ) ? sanitize_key( $tile['bento_hover_effect'] ) : 'lift';

		if ( '' !== $hover && 'none' !== $hover && in_array( $hover, array( 'lift', 'glow', 'zoom', 'tilt' ), true ) ) {
			$classes[] = 'bento-hover-' . $hover;
		}

		$style_attr = $this->bento_tile_style_attr( $tile );

		// Accessible name for the tile, taken from the author's title input.
		$aria_label = trim( wp_strip_all_tags( $title ) );
		$aria_attr  = '' !== $aria_label
			? ' role="group" aria-label="' . esc_attr( $aria_label ) . '"'
			: '';

		echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $style_attr . $aria_attr . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pieces individually escaped above.

		if ( '' !== $link ) {
			$link_aria = '' !== $aria_label ? ' aria-label="' . esc_attr( $aria_label ) . '"' : '';

			echo '<a class="bento-tile__link" href="' . $link . '"' . $link_aria . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $link esc_url()'d, aria label esc_attr()'d.
		}

		if ( 'text-top' === $mode ) {
			$this->bento_render_tile_body( $title, $caption );
			$this->bento_render_tile_media( $image_url, $alt, false, $opacity );
		} elseif ( 'text-bottom' === $mode ) {
			$this->bento_render_tile_media( $image_url, $alt, false, $opacity );
			$this->bento_render_tile_body( $title, $caption );
		} else {
			$this->bento_render_tile_media( $image_url, $alt, true, $opacity );
			$this->bento_render_tile_body( $title, $caption );
		}

		if ( '' !== $link ) {
			echo '</a>';
		}

		echo '</div>';
	}

	/**
	 * Build the tile's inline ` style="…"` attribute (with a leading space), or
	 * an empty string. Mirrors `bentoTileStyle()` in `src/blocks/tile/edit.tsx`
	 * exactly so the Elementor and Gutenberg markup are byte-identical.
	 *
	 * @param array $tile Repeater item.
	 * @return string
	 */
	private function bento_tile_style_attr( $tile ) {
		$vars = array();

		$gradient   = isset( $tile['bento_bg_gradient'] ) ? $this->bento_safe_css_value( $tile['bento_bg_gradient'] ) : '';
		$bg_color   = isset( $tile['bento_bg_color'] ) ? $this->bento_safe_css_value( $tile['bento_bg_color'] ) : '';
		$background = '' !== $gradient ? $gradient : $bg_color;

		if ( '' !== $background ) {
			$vars['--bento-tile-bg'] = $background;
		}

		$text_color = isset( $tile['bento_text_color'] ) ? $this->bento_safe_css_value( $tile['bento_text_color'] ) : '';

		if ( '' !== $text_color ) {
			$vars['--bento-tile-text'] = $text_color;
		}

		$border_width = isset( $tile['bento_border_width']['size'] ) && is_numeric( $tile['bento_border_width']['size'] )
			? min( 20, max( 0, (int) $tile['bento_border_width']['size'] ) )
			: 0;

		if ( $border_width > 0 ) {
			$vars['--bento-tile-border-width'] = $border_width . 'px';

			$border_color = isset( $tile['bento_border_color'] ) ? trim( (string) $tile['bento_border_color'] ) : '';

			if ( '' !== $border_color ) {
				$vars['--bento-tile-border-color'] = $border_color;
			}
		}

		if ( empty( $vars ) ) {
			return '';
		}

		$pairs = array();

		foreach ( $vars as $prop => $value ) {
			$pairs[] = $prop . ':' . $value;
		}

		return ' style="' . esc_attr( implode( ';', $pairs ) ) . '"';
	}

	/**
	 * @param string $url Already `esc_url()`'d in `bento_render_tile()`.
	 */
	private function bento_render_tile_media( $url, $alt, $overlay, $opacity ) {
		if ( '' === $url ) {
			return;
		}

		echo '<div class="bento-tile__media">';
		echo '<img class="bento-tile__img" src="' . $url . '" alt="' . esc_attr( $alt ) . '" loading="lazy" />'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $url is already esc_url()'d.

		if ( $overlay ) {
			$ratio = max( 0, min( 100, (int) $opacity ) ) / 100;
			$ratio = rtrim( rtrim( number_format( $ratio, 2, '.', '' ), '0' ), '.' );

			if ( '' === $ratio ) {
				$ratio = '0';
			}

			echo '<span class="bento-tile__scrim" aria-hidden="true" style="opacity:' . esc_attr( $ratio ) . '"></span>';
		}

		echo '</div>';
	}

	private function bento_render_tile_body( $title, $caption ) {
		echo '<div class="bento-tile__body">';

		if ( '' !== $title || '' !== $caption ) {
			echo '<div class="bento-tile__text">';

			if ( '' !== $title ) {
				echo '<h3 class="bento-tile__title">' . esc_html( $title ) . '</h3>';
			}

			if ( '' !== $caption ) {
				echo '<p class="bento-tile__caption">' . esc_html( $caption ) . '</p>';
			}

			echo '</div>';
		}

		echo '</div>';
	}
}
