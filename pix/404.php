<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package pix
 */

get_header();
?>

	<main id="primary" class="site-main">
	<div class="nodata_main" uk-height-viewport="offset-top: true">
		<section class="error-404 not-found">
			<div class="page-content no_resault">
				<img src="<?php echo THEME_URL .'/img/404.png';?>">
			</div><!-- .page-content -->
		</section><!-- .error-404 -->
	</div>

	</main><!-- #main -->

<?php
get_footer();
