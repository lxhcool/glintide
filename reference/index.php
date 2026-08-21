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
$type = theme_mod();
?>

<div class="pix-content home-box home-classic pix-modern pix-modern-home">
    <?php get_template_part( 'inc/layouts/home', $type ); ?>
</div>
<?php
get_footer();
