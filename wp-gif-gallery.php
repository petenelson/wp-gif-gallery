<?php
/**
Plugin Name: GIF Gallery
Description: GIF gallery shortcode with JS search.
Version:     1.2.3
Author:      Pete Nelson <a href="https://twitter.com/CodeGeekATX">(@CodeGeekATX)</a>
Author URI:  https://petenelson.io
Text Domain: wp-gif-gallery
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_GIF_GALLERY_ROOT' ) ) {
	define( 'WP_GIF_GALLERY_ROOT', trailingslashit( dirname( __FILE__ ) ) );
}

if ( ! defined( 'WP_GIF_GALLERY_URL' ) ) {
	define( 'WP_GIF_GALLERY_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );
}

// Load plugin files.
require_once WP_GIF_GALLERY_ROOT . 'includes/gallery.php';

// Setup plugin hooks and such.
WP_GIF_Gallery\setup();
