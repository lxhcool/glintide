<?php
/**
 * 页面模板
 *
 * @package glintide
 */

get_header();
?>

<div class="glintide-content">

	<div class="glintide-home-layout<?php echo is_active_sidebar( 'sidebar-left' ) ? ' glintide-home-layout--has-left' : ''; ?><?php echo is_active_sidebar( 'sidebar-right' ) ? ' glintide-home-layout--has-right' : ''; ?>">

		<?php if ( is_active_sidebar( 'sidebar-left' ) ) : ?>
		<aside class="left left-widget glintide-home-sidebar glintide-home-sidebar-left" aria-label="<?php esc_attr_e( '左侧栏', 'glintide' ); ?>">
			<div class="widget_inner glintide-home-widget-stack">
				<?php dynamic_sidebar( 'sidebar-left' ); ?>
			</div>
		</aside>
		<?php endif; ?>

		<div class="center-content glintide-home-main">
			<div id="primary" class="site-main">

				<?php
				while ( have_posts() ) :
					the_post();
					?>

					<article id="post-<?php the_ID(); ?>" <?php post_class( 'glintide-single-card' ); ?>>

						<header class="glintide-single-header">
							<h1 class="glintide-single-title"><?php the_title(); ?></h1>
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
		<aside class="right right-widget glintide-home-sidebar glintide-home-sidebar-right" aria-label="<?php esc_attr_e( '右侧栏', 'glintide' ); ?>">
			<div class="widget_inner glintide-home-widget-stack">
				<?php dynamic_sidebar( 'sidebar-right' ); ?>
			</div>
		</aside>
		<?php endif; ?>

	</div>

</div>

<?php
get_footer();