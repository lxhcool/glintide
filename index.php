<?php
/**
 * 首页模板:三栏布局(左栏 | 内容 | 右栏)
 *
 * @package glintide
 */

get_header();
?>

<div class="glintide-content home-box">

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
				<div class="glintide-home-stream">

				<?php if ( ! is_paged() ) : ?>
				<figure class="glintide-home-banner">
					<img src="<?php echo esc_url( glintide_home_banner_url() ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="1000" height="667" fetchpriority="high" decoding="async">
				</figure>
				<?php endif; ?>

				<?php if ( have_posts() ) : ?>

					<div class="glintide-post-list">
						<?php
						while ( have_posts() ) :
							the_post();
							get_template_part( 'tpl/content', get_post_format() );
						endwhile;
						?>
					</div>

					<?php glintide_pagination(); ?>

				<?php else : ?>

					<?php get_template_part( 'tpl/content', 'none' ); ?>

				<?php endif; ?>

				</div>

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
