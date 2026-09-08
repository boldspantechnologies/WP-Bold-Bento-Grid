<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bento_Rest_Api {

	const NAMESPACE_SLUG = 'bento-grid/v1';

	const ROUTE = '/posts';

	const MAX_POSTS_PER_PAGE = 20;

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'bento_register_routes' ) );
	}

	public function bento_register_routes() {
		register_rest_route(
			self::NAMESPACE_SLUG,
			self::ROUTE,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'bento_get_posts' ),
				'permission_callback' => array( $this, 'bento_check_permission' ),
				'args'                => $this->bento_get_collection_args(),
			)
		);
	}

	private function bento_get_collection_args() {
		return array(
			'post_type'      => array(
				'description'       => __( 'Post type to query.', 'bento-grid' ),
				'type'              => 'string',
				'default'           => 'post',
				'sanitize_callback' => 'sanitize_key',
				'validate_callback' => array( $this, 'bento_validate_post_type' ),
			),
			'posts_per_page' => array(
				'description'       => __( 'Number of posts to return.', 'bento-grid' ),
				'type'              => 'integer',
				'default'           => 6,
				'sanitize_callback' => 'absint',
				'validate_callback' => array( $this, 'bento_validate_posts_per_page' ),
			),
			'category'       => array(
				'description'       => __( 'Category slug or ID to filter by.', 'bento-grid' ),
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	public function bento_validate_post_type( $value ) {
		$post_type = sanitize_key( $value );

		return post_type_exists( $post_type ) && is_post_type_viewable( $post_type );
	}

	public function bento_validate_posts_per_page( $value ) {
		$value = absint( $value );

		return $value >= 1 && $value <= self::MAX_POSTS_PER_PAGE;
	}

	public function bento_check_permission( $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error(
				'bento_rest_invalid_nonce',
				__( 'A valid nonce is required to access this endpoint.', 'bento-grid' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	public function bento_get_posts( $request ) {
		$post_type      = $request->get_param( 'post_type' );
		$posts_per_page = $request->get_param( 'posts_per_page' );
		$category       = $request->get_param( 'category' );

		$query_args = array(
			'post_type'           => $post_type,
			'post_status'         => 'publish',
			'posts_per_page'      => $posts_per_page,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
		);

		if ( ! empty( $category ) ) {
			if ( is_numeric( $category ) ) {
				$query_args['cat'] = absint( $category );
			} else {
				$query_args['category_name'] = sanitize_title( $category );
			}
		}

		$query = new WP_Query( $query_args );

		$items = array();

		foreach ( $query->posts as $post ) {
			$items[] = $this->bento_format_post( $post );
		}

		wp_reset_postdata();

		return rest_ensure_response(
			array(
				'items' => $items,
				'total' => count( $items ),
			)
		);
	}

	private function bento_format_post( $post ) {
		$thumbnail_id  = get_post_thumbnail_id( $post );
		$thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'large' ) : '';

		$excerpt = has_excerpt( $post )
			? $post->post_excerpt
			: wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 );

		return array(
			'id'            => absint( $post->ID ),
			'title'         => esc_html( get_the_title( $post ) ),
			'permalink'     => esc_url( get_permalink( $post ) ),
			'excerpt'       => esc_html( $excerpt ),
			'featuredImage' => $thumbnail_url ? esc_url( $thumbnail_url ) : '',
		);
	}
}
