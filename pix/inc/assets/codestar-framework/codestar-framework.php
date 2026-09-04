<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
/**
 *
 * @package   Codestar Framework - WordPress Options Framework
 * @author    Codestar <info@codestarthemes.com>
 * @link      http://codestarframework.com
 * @copyright 2015-2022 Codestar
 *
 *
 * Plugin Name: Codestar Framework
 * Plugin URI: http://codestarframework.com/
 * Author: Codestar
 * Author URI: http://codestarthemes.com/
 * Version: 2.2.6
 * Description: A Simple and Lightweight WordPress Option Framework for Themes and Plugins
 * Text Domain: csf
 * Domain Path: /languages
 *
 */
require_once plugin_dir_path( __FILE__ ) .'classes/setup.class.php';
require_once plugin_dir_path( __FILE__ ) .'classes/normal-option.class.php';
require_once plugin_dir_path( __FILE__ ) .'options/option.php';
//require_once plugin_dir_path( __FILE__ ) .'options/profile.php';
require_once plugin_dir_path( __FILE__ ) .'options/metabox.php';
require_once plugin_dir_path( __FILE__ ) .'options/widget.php';
//require_once plugin_dir_path( __FILE__ ) .'options/shortcode.php';
require_once plugin_dir_path( __FILE__ ) .'options/tax.php';
require_once plugin_dir_path( __FILE__ ) .'options/nav-menu-options.php';

if( ! function_exists( 'csf_add_my_custom_css' ) ) {
	function csf_add_my_custom_css() {
  
		wp_enqueue_style( 'codestar-custom.css', THEME_URL . '/inc/assets/css/codestar-custom.css', array(), '' );
  
	}
	add_action('csf/enqueue', 'csf_add_my_custom_css' );
  }