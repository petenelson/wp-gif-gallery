<?php
/*
Plugin Name: Pete's wp-ajax sample
Plugin URI: http://petenelson.com/
Description: For the WordPress API meetup
Author: Pete Nelson (@GunGeekATX)
Author URI: https://twitter.com/GunGeekATX
*/

/*
Standard AJAX
http://codex.wordpress.org/AJAX_in_Plugins
*/

define( 'PN_WPAUSTIN_WPAJAX_ACTION', 'pn-wpaustin-image-search' );
define( 'PN_WPAUSTIN_WPAJAX_ACTION_UPLOAD', 'pn-wpaustin-image-upload' );


// WordPress actions for handling AJAX requests for logged-in users
add_action( 'wp_ajax_' . PN_WPAUSTIN_WPAJAX_ACTION, 'pn_wpaustin_ajax_image_search' );

// WordPress actions for handling AJAX requests from non-logged-in users
add_action( 'wp_ajax_nopriv_' . PN_WPAUSTIN_WPAJAX_ACTION, 'pn_wpaustin_ajax_image_search' );


function pn_wpaustin_ajax_image_search() {

	$results = pn_wpaustin_get_image_search_results();

	// return the output as a JSON object
	wp_send_json( $results );

}


function pn_wpaustin_get_image_search_results() {

	$query_args = array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit', // required for attachments
		'posts_per_page' => -1, // everything
		's'              => isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '',
		'post_parent'    => isset( $_REQUEST['parent_post_id'] ) ? intval( $_REQUEST['parent_post_id'] ) : 0
	);


	// check for a previous search in the cache
	$cache_key = md5( json_encode( $query_args ) );
	$output = wp_cache_get( $cache_key, 'wpaustin' );
	if ( ! empty( $output) ) {
		return $output;
	}


	// default return values
	$output = new stdClass();
	$output->number_of_matches = 0;
	$output->gallery_html = '';
	$output->post_ids = array();
	$output->permalink = '';


	// use WP_Query to search for pics attached to this post (not query_posts!)
	global $post;
	$query = new WP_Query( $query_args );

	// gather up the IDs and permalinks
	if ( $query->have_posts() ) {
		$output->number_of_matches = intval( $query->found_posts );

		while ( $query->have_posts() ) {
			$query->the_post();
			$output->post_ids[] = $post->ID;

			// for a single post, get the permalink so the page can redirect to it immediately
			if ( 1 === $output->number_of_matches ) {
				$output->permalink = get_permalink( $post->ID );
			}

		}
	}

	// always do this after running a custom WP_Query
	wp_reset_postdata();


	// run the gallery shortcode on these pics
	if ( $output->number_of_matches > 0 ) {
		$output->gallery_html = do_shortcode( '[gallery link="post" columns="5" ids="' . implode( ',', $output->post_ids ) . '"]' );
	}

	// store this for later
	wp_cache_set( $cache_key, $output, 'wpaustin', DAY_IN_SECONDS * 1 );

	return $output;

}


/*
Custom API endpoints
https://10up.github.io/Engineering-Best-Practices/php/#ajax-endpoints
*/

add_action( 'init', 'pn_wpaustin_add_api_endpoints' );
function pn_wpaustin_add_api_endpoints() {

	$endpoint = 'pn-api/';

	// sample URL: https://petenelson.com/pn-api/v1/image-search/
	// gets converted to: https://petenelson.com/index.php?pn-api-request=1&pn-api-version=v1&pn-api-action=image-search

	add_rewrite_rule( $endpoint . 'v([1-3]{1})/([A-Za-z0-9\-\_]+)/?', 'index.php?pn-api-request=1&pn-api-version=$matches[1]&pn-api-action=$matches[2]', 'top' );

	// allows these tags to get added to the global $wp_query object
	add_rewrite_tag( '%pn-api-action%', '([A-Za-z0-9\-\_]+)' );
	add_rewrite_tag( '%pn-api-version%', 'v([1-3]{1})' );
	add_rewrite_tag( '%pn-api-request%', '1' );

	// any time you change the above, go into Settings -> Permalinks and click save to flush the rewrite rules

	// you use the template_redirect action in other plugins to look for API requests

}


add_action( 'template_redirect', 'pn_wpaustin_template_redirect' );
function pn_wpaustin_template_redirect() {

	// call the filter to look for an API request
	$args = apply_filters( 'pn-api-get-request', false );

	if ( ! empty( $args ) ) {

		switch ( $args['version'] ) {
			case 1:
					switch ( $args['action'] ) {
						case 'output-args':
							wp_send_json( $args );
							break;
						case 'image-search':
							pn_wpaustin_ajax_image_search();
							break;
					}
					break;

			case 2:
				switch ( $args['action'] ) {
					case 'output-args':
						wp_send_json_success( $args );
						break;
					case 'image-search':
						wp_send_json_success( pn_wpaustin_get_image_search_results() );
						break;
				}
				break;

			default:
				wp_send_json_error( 'API v' . $args['version'] . ' is not supported.' );
		}


	}


}


// this filter allows us easy access to API requests
add_filter( 'pn-api-get-request', 'pn_wpaustin_filter_api_request' );
function pn_wpaustin_filter_api_request( $args = false ) {

	global $wp_query;
	if ( $wp_query->get( 'pn-api-request' ) === '1' ) {

		// default args
		$args = array(
				'api-request' => false,
				'action' => '',
				'version' => 1,
			);

		$args['api-request'] = true;
		$args['action'] = $wp_query->get( 'pn-api-action' );
		$args['version'] = intval( $wp_query->get( 'pn-api-version' ) );
		$args['QUERY_STRING'] = $_SERVER['QUERY_STRING'];

		if ( ! empty( $args['action'] ) ) {
			nocache_headers();
		}

	} else {
		$args = false;
	}

	return $args;
}






add_shortcode( 'pete-wpaustin-wpajax', 'pn_wpaustin_wpajax' );
function pn_wpaustin_wpajax( $args ) {

	// http://jquery.malsup.com/form/
	// Ajax-ify the form, not required, but very handy, and built-in to WordPress
	wp_enqueue_script( 'jquery-form', false, array( 'jquery' ) );

	// custom JS for our plugin
	wp_enqueue_script( 'pn-wpaustin-wpajax', plugin_dir_url( __FILE__ ) . 'ajax.js', array( 'jquery-form' ), '2015-05-01-01' );

	ob_start();
?>
		<form method="get" class="forum-pics-search-form" action="#">
			<input type="hidden" name="action" class="ajax-action" value="" />
			<input type="hidden" name="parent_post_id" class="parent-post-id" value="" />
			<input type="text" name="s" class="search-for" />
			<input type="submit" value="Search" />
			<label>
				<input type="checkbox" name="use-api" class="use-api" />
				Use API endpoint instead of admin-ajax.php
			</label>
			<span class="matches"></span>
			<img src="<?php echo plugin_dir_url( __FILE__ ) ?>ajax.gif" class="ajax-loading" style="display: none" />
		</form>

		<?php if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
?>


		<a href="#show-upload" class="forum-pics-show-upload">Upload</a>

		<form method="post" class="forum-pics-upload-form" action="#" style="display: none;">
			<input type="hidden" name="action" class="ajax-action" value="" />
			<input type="hidden" name="parent_post_id" class="parent-post-id" value="" />


			<table>
				<tr>
					<td>
						Url:
					</td>
					<td>
						<input type="text" name="file" value="" class="upload-file-name" />
					</td>
				</tr>

				<tr>
					<td>
						Description:
					</td>
					<td>
						<input type="text" name="desc" value="" />
					</td>
				</tr>

				<tr>
					<td>
					</td>
					<td>
						<input type="submit" value="Upload" />
					</td>
				</tr>

			</table>

			<br/>

		</form>

	<?php
	} // end upload form


	$form = ob_get_contents();
	ob_end_clean();


	/*
		Example of outputting variables to a Javascript object so client-side code can access it

		This creates a client-side Javascript object like this:
		var pn_wpaustin_wpajax = {
			"admin_ajax_url":"http:\/\/localhost:8080\/wp-petenelson\/wp-admin\/admin-ajax.php",
			"ajax_action":"pete-forum-pics-search",
			"parent_post_id":"195"
		};
	*/

	wp_localize_script( 'pn-wpaustin-wpajax', 'pn_wpaustin_wpajax',
		array(
			'admin_ajax_url' => admin_url( 'admin-ajax.php' ),
			'ajax_action' => PN_WPAUSTIN_WPAJAX_ACTION,
			'ajax_action_api' => home_url( '/pn-api/v1/image-search/' ),
			'ajax_action_upload' => PN_WPAUSTIN_WPAJAX_ACTION_UPLOAD,
			'parent_post_id' => get_the_id()
		)
	);


	return $form;

}




















add_action( 'wp_ajax_' . PN_WPAUSTIN_WPAJAX_ACTION_UPLOAD, 'pn_wpaustin_handle_sideload' );

function pn_wpaustin_handle_sideload() {

	if ( ! current_user_can( 'manage_options' ) );
		wp_die( );

	$results = new stdClass();
	$results->error = false;
	$results->permalink = '';
	$results->id = false;


	$file = $_REQUEST['file'];

	preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
	$file_array = array();
	$file_array['name'] = basename( $matches[0] );


	$post_id = $_REQUEST['parent_post_id'];
	$desc = ! empty( $_REQUEST['desc'] ) ? filter_var( $_REQUEST['desc'], FILTER_SANITIZE_STRING ) : '';

	// download the image
	$file_array['tmp_name'] = download_url( $file );
	$id = media_handle_sideload( $file_array, $post_id, $desc  );

	// If error storing permanently, unlink.
	if ( is_wp_error( $id ) ) {
		@unlink( $file_array['tmp_name'] );
		$results->error = true;
	} else {

		$results->id= $id ;
		$results->permalink = get_permalink( $id );

	}

	wp_send_json( $results );

}

