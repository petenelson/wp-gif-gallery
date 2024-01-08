<?php

namespace WP_GIF_Gallery;

/**
 * Quickly provide a namespaced way to get functions.
 *
 * @param string $function Name of function in namespace.
 * @return string
 */
function n( $function ) {
	return __NAMESPACE__ . "\\$function";
}

/**
 * Setup WordPress hooks and filters
 *
 * @return void
 */
function setup() {
	add_action( 'init', n( 'register_scripts' ) );
	add_shortcode( 'wp_gif_gallery', n( 'get_gallery_html', 10, 2 ) );
	add_action( 'add_attachment', n( 'attach_to_gif_page' ) );
}

function get_version() {
	return '1.2.3';
}

function register_scripts() {
	wp_register_script(
		'wp-gif-gallery',
		WP_GIF_GALLERY_URL . 'assets/js/wp-gif-gallery.js',
		[],
		get_version(),
		true
	);

	wp_register_script(
		'wp-gif-gallery-long-press',
		WP_GIF_GALLERY_URL . 'assets/js/long-press-event.min.js',
		[],
		get_version(),
		true
	);
}

function get_gallery_html( $args, $content ) {

	$args = wp_parse_args( $args, array(
		'columns'          => 5,
		'post_parent'      => get_the_id(),
		)
	);

	$data = get_gallery_data( $args );

	// Include the HTML template.
	ob_start();
	include_once WP_GIF_GALLERY_ROOT . 'templates/gallery-template.php';
	$html = ob_get_clean();

	// Enqueue the plugin script.
	wp_enqueue_script( 'wp-gif-gallery' );
	wp_enqueue_script( 'wp-gif-gallery-long-press' );

	return $html;
}

function get_gallery_data( $args ) {

	$data = array(
		'columns' => $args['columns'],
		'images'  => get_gallery_images( $args ),
		);

	return $data;
}

function get_gallery_images( $args ) {

	$images = array();

	$query = new \WP_Query(
		[
			'post_type'        => 'attachment',
			'post_status'      => 'inherit',
			'posts_per_page'   => 500,
			'post_parent'      => $args['post_parent'],
			'post_mime_type'   => [
				'image/gif',
				'image/jpeg',
				'image/png',
			],
		]
	);

	foreach( $query->posts as $image ) {

		$img = array(
			'id'          => $image->ID,
			'title'       => $image->post_title,
			'alt'         => trim( get_post_meta( $image->ID, '_wp_attachment_image_alt', true ) ),
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

/**
 * Attaches a newly-added GIF to the GIF page.
 *
 * @param  int $attachment_id The attachment ID.
 * @return void
 */
function attach_to_gif_page( $attachment_id ) {

	if ( 'image/gif' === get_post_mime_type( $attachment_id ) ) {

		$page = get_page_by_path( '/gifs' );

		if ( $page instanceof \WP_Post ) {
			wp_update_post(
				[
					'ID' => $attachment_id,
					'post_parent' => $page->ID,
				]
			);
		}
	}
}
