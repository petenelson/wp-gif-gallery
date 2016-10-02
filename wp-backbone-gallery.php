<?php
/**
Plugin Name: Backbone Gallery
Description: Backbone-based gallery shortcode
Version:     1.0.0
Author:      Pete Nelson <a href="https://twitter.com/GunGeekATX">(@GunGeekATX)</a>
Author URI:  https://petenelson.io
Text Domain: wp-backbone-gallery
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_BACKBONE_GALLERY_ROOT' ) ) {
	define( 'WP_BACKBONE_GALLERY_ROOT', trailingslashit( dirname( __FILE__ ) ) );
}

if ( ! defined( 'WP_BACKBONE_GALLERY_URL' ) ) {
	define( 'WP_BACKBONE_GALLERY_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );
}

// Load plugin files.
require_once WP_BACKBONE_GALLERY_ROOT . 'includes/gallery.php';

// Setup plugin hooks and such.
WP_Backbone_Gallery\setup();
