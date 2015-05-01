<?php
/*
Plugin Name: Pete's wp-ajax sample
Plugin URI: http://petenelson.com/
Description: For the August 2013 WordPress meetup
Author: Pete Nelson (@GunGeekATX)
Author URI: https://twitter.com/GunGeekATX
*/

/*
http://codex.wordpress.org/AJAX_in_Plugins
*/

define( 'PETE_WPAUSTIN_WPAJAX_ACTION', 'pete-forum-pics-search' );
define( 'PETE_WPAUSTIN_WPAJAX_ACTION_UPLOAD', 'pete-forum-pics-upload' );


add_shortcode( 'pete-wpaustin-wpajax', 'pete_wpaustin_wpajax' );
function pete_wpaustin_wpajax( $args ) {

	// http://jquery.malsup.com/form/
	// Ajax-ify the form, not required, but very handy, and built-in to WordPress
	wp_enqueue_script( 'jquery-form', false, array( 'jquery' ) );

	// custom JS for our plugin
	wp_enqueue_script( 'pete-wpaustin-wpajax', plugin_dir_url( __FILE__ ) . 'pete-wpaustin-wpajax.js', array( 'jquery-form' ), '2015-01-19-01' );

	ob_start();
?>
		<form method="get" class="forum-pics-search-form" action="#">
			<input type="hidden" name="action" class="ajax-action" value="" />
			<input type="hidden" name="parent_post_id" class="parent-post-id" value="" />
			<input type="text" name="s" class="search-for" />
			<input type="submit" value="Search" />
			<span class="matches"></span>
			<img src="<?php echo plugin_dir_url( __FILE__ ) ?>pete-wpaustin-wpajax.gif" class="ajax-loading" style="display: none" />
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
		var pete_wpaustin_wpajax = {
			"admin_ajax_url":"http:\/\/localhost:8080\/wp-petenelson\/wp-admin\/admin-ajax.php",
			"ajax_action":"pete-forum-pics-search",
			"parent_post_id":"195"
		};
	*/

	wp_localize_script( 'pete-wpaustin-wpajax', 'pete_wpaustin_wpajax',
		array(
			'admin_ajax_url' => admin_url( 'admin-ajax.php' ),
			'ajax_action' => PETE_WPAUSTIN_WPAJAX_ACTION,
			'ajax_action_upload' => PETE_WPAUSTIN_WPAJAX_ACTION_UPLOAD,
			'parent_post_id' => get_the_id()
		)
	);


	return $form;

}


// WordPress actions for handling AJAX requests for logged-in users
add_action( 'wp_ajax_' . PETE_WPAUSTIN_WPAJAX_ACTION, 'pete_forum_pics_search' );

// WordPress actions for handling AJAX requests from non-logged-in users
add_action( 'wp_ajax_nopriv_' . PETE_WPAUSTIN_WPAJAX_ACTION, 'pete_forum_pics_search' );

function pete_forum_pics_search() {

	// default return values
	$output = new stdClass();
	$output->number_of_matches = 0;
	$output->gallery_html = '';
	$output->post_ids = array();
	$output->permalink = '';


	// use WP_Query to search for pics attached to this post (not query_posts!)
	global $post;
	$query = new WP_Query(
		array(
			'post_type' => 'attachment',
			'post_status' => 'inherit', // required for attachments
			'posts_per_page'=> -1, // everything
			's' => isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '',
			'post_parent' => isset( $_REQUEST['parent_post_id'] ) ? intval( $_REQUEST['parent_post_id'] ) : 0
		)
	);


	// gather up the IDs and permalinks
	if ( $query->have_posts() ) {
		$output->number_of_matches = intval( $query->found_posts );

		while ( $query->have_posts() ) {
			$query->the_post();
			$output->post_ids[] = $post->ID;

			// for a single post, get the permalink so the page can redirect to it immediately
			if ( 1 === $output->number_of_matches )
				$output->permalink = get_permalink( $post->ID );

		}
	}

	// always do this after running a custom WP_Query
	wp_reset_postdata();


	// run the gallery shortcode on these pics
	if ( $output->number_of_matches > 0 )
		$output->gallery_html = do_shortcode( '[gallery link="post" columns="5" ids="' . implode( ',', $output->post_ids ) . '"]' );


	// return the output as a JSON object
	header( 'Content-Type: application/json' );
	echo json_encode( $output );
	die();

}


add_action( 'wp_ajax_' . PETE_WPAUSTIN_WPAJAX_ACTION_UPLOAD, 'petenelson_wpaustin_handle_sideload' );

add_action( 'wp_ajax_nopriv_' . PETE_WPAUSTIN_WPAJAX_ACTION_UPLOAD, 'petenelson_wpaustin_handle_sideload' );

function petenelson_wpaustin_handle_sideload() {

//	if ( ! current_user_can( 'manage_options' ) );
//		wp_die( );

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

	header( 'Content-Type: application/json' );
	echo json_encode( $results );
	exit();

}

