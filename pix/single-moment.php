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
		<div class="moment_single single_content" uk-height-viewport="offset-top: true">
		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'tpl/single','moment');
		
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;
			
		endwhile; // End of the loop.
		?>
		</div>
		<div id="comment_form_reset"></div>
	</main><!-- #main -->
	
<?php
get_footer();
