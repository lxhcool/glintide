<?php
/**
 * 首页模板:三栏布局(左栏 | 内容 | 右栏)
 *
 * @package glintide
 */

get_header();
?>

<div class="pix-content home-box">

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

				<?php if ( have_posts() ) : ?>

					<div class="pix-post-list">
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