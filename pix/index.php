<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pix
 */

get_header();
$home_type = get_op('home_post_type');

switch ($home_type)
{
case 'moment':
	get_template_part('layouts/home','moment');
    break;
case 'post':
	get_template_part('layouts/home','blog');
    break;
default:
	get_template_part('layouts/home','moment');
}

get_footer();
