<?php

define( 'VASU_THEME_DIR', trailingslashit( get_stylesheet_directory() ) );
define( 'VASU_THEME_URL', get_stylesheet_directory_uri() );
define( 'VASU_THEME_STATIC_URL', VASU_THEME_URL . '/static' );
define( 'VASU_THEME_CSS_URL', VASU_THEME_STATIC_URL . '/css' );
define( 'VASU_THEME_JS_URL', VASU_THEME_STATIC_URL . '/js' );
define( 'VASU_THEME_IMG_URL', VASU_THEME_STATIC_URL . '/img' );

include_once 'includes/config.php';
include_once 'includes/customizer.php';
include_once 'includes/navbar.php';
include_once 'includes/utilities.php';

add_theme_support( 'post-thumbnails' );

// Add Options page
if( function_exists('acf_add_options_page') ) {
	acf_add_options_page();
}
