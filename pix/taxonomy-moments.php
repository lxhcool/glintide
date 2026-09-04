<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pix
 */

get_header();
global $wp_query;
$term_id = get_queried_object()->term_id;
$cat_img = get_term_meta( $term_id, 'cat_img', true );

?>

	<main id="primary" class="site-main">
	<div class="archive_main" uk-height-viewport="offset-top: true">
		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<div class="cat_title">
					<?php
						the_archive_title( '<h1 class="page-title">', '</h1>' );
						the_archive_description( '<div class="archive-description">', '</div>' );
					?>
				</div>
	
			</header><!-- .page-header -->

		<div id="post_item" class="moment_list norpost_list">	
			<?php
			/* Start the Loop */
			while ( have_posts() ) :
				the_post();

				/*
				 * Include the Post-Type-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
				 */
				get_template_part( 'tpl/content', 'moment' );

			endwhile;

		else :

			get_template_part( 'tpl/content', 'none' );

		endif;
		?>
		</div>

		<div class="arc_pagenav"><?php next_posts_link(_('LOAD MORE')); ?></div>

	</div>
	</main><!-- #main -->

<?php
get_footer();
