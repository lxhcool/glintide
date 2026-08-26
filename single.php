<?php
/**
 * 文章页模板
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
							<div class="glintide-single-meta">
								<span><i class="ri-calendar-line" aria-hidden="true"></i><?php echo esc_html( get_the_date() ); ?></span>
								<span><i class="ri-user-3-line" aria-hidden="true"></i><?php the_author(); ?></span>
								<span><i class="ri-chat-3-line" aria-hidden="true"></i><?php comments_number( '0', '1', '%' ); ?></span>
								<?php if ( get_the_category() ) : ?>
									<span><i class="ri-folder-2-line" aria-hidden="true"></i><?php the_category( ' / ' ); ?></span>
								<?php endif; ?>
							</div>
						</header>

						<?php if ( has_post_thumbnail() ) : ?>
						<div class="glintide-single-cover">
							<?php the_post_thumbnail( 'large' ); ?>
						</div>
						<?php endif; ?>

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

						<?php if ( get_the_tags() ) : ?>
						<div class="glintide-single-tags">
							<?php the_tags( '', '', '' ); ?>
						</div>
						<?php endif; ?>

					</article>

					<?php
					// 上一篇 / 下一篇
					the_post_navigation(
						array(
							'prev_text' => '<i class="ri-arrow-left-line" aria-hidden="true"></i> %title',
							'next_text' => '%title <i class="ri-arrow-right-line" aria-hidden="true"></i>',
						)
					);

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