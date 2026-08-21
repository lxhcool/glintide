<?php
/**
 * 归档页模板(分类/标签/作者/日期)
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

				<header class="pix-archive-header">
					<h1>
						<?php
						if ( is_category() ) {
							single_cat_title();
						} elseif ( is_tag() ) {
							single_tag_title();
						} elseif ( is_author() ) {
							the_archive_title();
						} elseif ( is_day() ) {
							printf( esc_html__( '每日归档: %s', 'glintide' ), esc_html( get_the_date() ) );
						} elseif ( is_month() ) {
							printf( esc_html__( '每月归档: %s', 'glintide' ), esc_html( get_the_date( 'Y年n月' ) ) );
						} elseif ( is_year() ) {
							printf( esc_html__( '每年归档: %s', 'glintide' ), esc_html( get_the_date( 'Y年' ) ) );
						} else {
							the_archive_title();
						}
						?>
					</h1>
					<?php if ( get_the_archive_description() ) : ?>
						<div class="pix-archive-desc"><?php echo wp_kses_post( wpautop( get_the_archive_description() ) ); ?></div>
					<?php endif; ?>
				</header>

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