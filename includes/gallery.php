<?php

namespace WP_Backbone_Gallery;

/**
 * Setup WordPress hooks and filters
 *
 * @return void
 */
function setup() {

	add_action( 'init', __NAMESPACE__ . '\register_scripts' );

	add_shortcode( 'wp_backbone_gallery', __NAMESPACE__ . '\get_backbone_gallery_html', 10, 2 );
}

function get_version() {
	return '1.0.0';
}

function register_scripts() {
	wp_register_script(
		'wp-backbone-gallery',
		WP_BACKBONE_GALLERY_URL . 'assets/js/wp-backbone-gallery.js',
		array( 'jquery', 'wp-util', 'backbone', 'underscore' ),
		get_version(),
		true
	);
}

function get_backbone_gallery_html( $args, $content ) {

	$args = wp_parse_args( $args, array(
		'columns'          => 5,
		'post_parent'      => get_the_id(),
		)
	);

	// Include the Backbone template.
	ob_start();
	include_once WP_BACKBONE_GALLERY_ROOT . 'templates/gallery-template.php';
	$html = ob_get_clean();

	// Enqueue the plugin script.
	wp_enqueue_script( 'wp-backbone-gallery' );

	// Localize the data for the plugin script.
	wp_localize_script( 'wp-backbone-gallery', 'WP_Backbone_Gallery_Data', get_gallery_script_data( $args ) );

	return $html;
}

function get_gallery_script_data( $args ) {

	$data = array(
		'columns' => $args['columns'],
		'images'  => get_gallery_images( $args ),
		);

	return $data;
}

function get_gallery_images( $args ) {

	$images = array();

	$query = new \WP_Query( array(
		'post_type'        => 'attachment',
		'post_status'      => 'inherit',
		'posts_per_page'   => 500,
		'post_parent'      => $args['post_parent'],
		'post_mime_type'   => array( 'image/gif', 'image/jpeg', 'image/png' ),
		)
	);

	foreach( $query->posts as $image ) {

		$img = array(
			'id'          => $image->ID,
			'title'       => $image->post_title,
			'slug'        => $image->post_name,
			'permalink'   => get_permalink( $image->ID ),
			'src'         => '',
			'caption'     => $image->post_excerpt,
			'gifv_url'    => apply_filters( 'son-of-gifv-permalink', '', $image->ID ),
			'thumbnail'   => array(
				'src'    => '',
				'width'  => 0,
				'height' => 0,
				),
			);

		$src = wp_get_attachment_image_src( $image->ID, 'full' );
		if ( ! empty( $src ) ) {
			$img['src'] = $src[0];
		}

		$thumbnail = wp_get_attachment_image_src( $image->ID, 'thumbnail' );

		if ( ! empty( $thumbnail ) ) {
			$img['thumbnail']['src']    = $thumbnail[0];
			$img['thumbnail']['width']  = $thumbnail[1];
			$img['thumbnail']['height'] = $thumbnail[2];
		}

		$images[] = $img;
	}

	return $images;
}

