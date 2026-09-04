<?php
/**
 * pix functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package pix
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.7' );
}
define( 'THEME_DIR', get_template_directory() );
define( 'THEME_URL', get_bloginfo('template_directory') );
define( 'THEME_DEFAULT_URL', get_bloginfo('template_directory').'/img/banner.jpg' );
//define( 'THEME_DEFAULT_BG', get_bloginfo('template_directory').'/img/infobg.jpg' );
require_once get_theme_file_path() .'/inc/assets/codestar-framework/codestar-framework.php';
require THEME_DIR . '/inc/enqueue.php';
