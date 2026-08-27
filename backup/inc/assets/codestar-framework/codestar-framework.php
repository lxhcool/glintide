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
 * Version: 2.3.1
 * Description: A Simple and Lightweight WordPress Option Framework for Themes and Plugins
 * Text Domain: csf
 * Domain Path: /languages
 *
 */
require_once plugin_dir_path( __FILE__ ) .'classes/setup.class.php';
//require_once plugin_dir_path( __FILE__ ) .'options/admin-options.php';
require_once plugin_dir_path( __FILE__ ) .'options/nav-menu-options.php';
//require_once plugin_dir_path( __FILE__ ) .'options/customize-options.php';
//require_once plugin_dir_path( __FILE__ ) .'options/taxonomy-options.php';
require_once plugin_dir_path( __FILE__ ) .'options/metabox-options.php';
require_once plugin_dir_path( __FILE__ ) .'options/shortcode-options.php';
require_once plugin_dir_path( __FILE__ ) .'options/profile-options.php';
require_once plugin_dir_path( __FILE__ ) .'options/widget-options.php';
