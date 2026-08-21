<?php
/**
 * 页面模板
 *
 * @package glintide
 */

get_header();
?>

<div class="pix-content">

	<div class="pix-home-layout<?php echo is_active_sidebar( 'sidebar-left' ) ? ' pix-home-layout--has-left' : ''; ?><?php echo is_active_sidebar( 'sidebar-right' ) ? ' pix-home-layout--has-right' : ''; ?>">

		<?php if ( is_active_sidebar( 'sidebar-left' ) ) : ?>
		<aside class="left left-widget pix-home-sidebar pix-home-sidebar-left" aria-label="<?php esc_attr_e( '左侧栏', 'glintide' ); ?>">
			<div class="widget_inner pix-home-widget-stack">
				<?php dynamic_sidebar( 'sidebar-left' ); ?>
			</div>
		</aside>
		<?php endif; ?>

		<div class="center-content pix-home-main">
			<div id="primary" class="site-main">

				<?php
				while ( have_posts() ) :
					the_post();
					?>

					<article id="post-<?php the_ID(); ?>" <?php post_class( 'pix-single-card' ); ?>>

						<header class="pix-single-header">
							<h1 class="pix-single-title"><?php the_title(); ?></h1>
						</header>

						<div class="entry-content">
							<?php
							the_content();

							wp_link_pages(
								array(
									'before' => '<div class="page-links">' . esc_html__( '分页:', 'glintide' ),
									'after'  => '</div>',
								)
							);
							?>
						</div>

					</article>

					<?php
					// 评论
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}

				endwhile;
				?>

			</div><!-- #main -->
		</div>

		<?php if ( is_active_sidebar( 'sidebar-right' ) ) : ?>
		<aside class="right right-widget pix-home-sidebar pix-home-sidebar-right" aria-label="<?php esc_attr_e( '右侧栏', 'glintide' ); ?>">
			<div class="widget_inner pix-home-widget-stack">
				<?php dynamic_sidebar( 'sidebar-right' ); ?>
			</div>
		</aside>
		<?php endif; ?>

	</div>

</div>

<?php
get_footer();