<?php
/**
 * pix functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package pix
 */

if ( ! defined( 'PIX_VERSION' ) ) {
	define( 'PIX_VERSION', '1.0.0' );
}

define( 'THEME_DIR', get_template_directory() );
define( 'THEME_URL', get_bloginfo('template_directory') );
define( 'THEME_DEFAULT_URL', get_bloginfo('template_directory').'/img/banner.jpg' );
define( 'PPO_BLOG_NAME', get_bloginfo('name') );
//define( 'THEME_DEFAULT_BG', get_bloginfo('template_directory').'/img/infobg.jpg' );
require_once get_theme_file_path() . '/inc/pix-xload.php';
if (pix_xload_bootstrap_should_stop()) {
  return;
}
require_once get_theme_file_path() .'/inc/assets/codestar-framework/codestar-framework.php';
require THEME_DIR . '/inc/main.php';

//code end!
