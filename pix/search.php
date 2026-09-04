<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package pix
 */

get_header();
$type = isset($_GET['type']) && !empty($_GET['type']) ? $_GET['type'] : 'moment';
$key = get_search_query();
?>

<main id="primary" class="site-main">

	<?php get_template_part( 'search/'.$type ); ?>

</main><!-- #main -->

<?php
get_footer();
