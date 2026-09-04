<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package pix
 */

get_header();

?>

	<main id="primary" class="site-main">
		<div class="single_wrap" uk-height-viewport="offset-top: true">

			<?php
			while ( have_posts() ) :
				the_post();

				get_template_part( 'tpl/content','single');


				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile; // End of the loop.
			?>

		</div>

	</main><!-- #main -->

<?php
get_footer();
