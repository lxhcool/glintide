<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package pix
 */

$footer_text = get_cu('footer_text');
$show_widgets = get_cu('footer_widgets', true);
$footer_icp = get_cu('footer_icp');
?>

</div>
<!-- #app-warp -->
</div>
<!-- .app_container -->

	<footer id="colophon" class="site-footer pix-content">
		<?php if ($show_widgets && is_active_sidebar('footer-widget')) : ?>
		<div class="footer-widget-area">
			<?php dynamic_sidebar('footer-widget'); ?>
		</div>
		<?php endif; ?>
		<div class="site-info">
			<div class="footer-copyright">
				<?php if ($footer_text) : ?>
					<?php echo do_shortcode(wp_kses_post($footer_text)); ?>
				<?php else : ?>
					<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'pix' ) ); ?>">
						<?php printf( esc_html__( 'Proudly powered by %s', 'pix' ), 'WordPress' ); ?>
					</a>
					<span class="sep"> | </span>
					<?php printf( esc_html__( 'Theme: %1$s by %2$s.', 'pix' ), 'pix', 'lxhcool & fuzzz' ); ?>
				<?php endif; ?>
			</div>
			<?php if (!empty($footer_icp['url']) || !empty($footer_icp['text'])) : ?>
			<div class="footer-icp">
				<?php if (!empty($footer_icp['url'])) : ?>
					<a href="<?php echo esc_url($footer_icp['url']); ?>" target="<?php echo esc_attr($footer_icp['target'] ?: '_blank'); ?>" rel="external nofollow">
						<?php echo esc_html($footer_icp['text']); ?>
					</a>
				<?php else : ?>
					<span><?php echo esc_html($footer_icp['text']); ?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</footer>
</div><!-- #page -->
<?php if ( get_cu( 'header_mode', 'floating' ) !== 'classic' ) : ?>
<?php get_template_part( 'inc/layouts/header', 'layout' ); ?>
<?php endif; ?>
<div class="pix-global-tools" data-pix-global-tools aria-label="页面快捷工具">
	<?php if ( get_cu( 'header_mode', 'floating' ) !== 'classic' ) : ?>
	<button type="button" class="pix-global-tool pix-global-menu pix-tooltip pix-tooltip-left pix-mobile-menu-trigger" data-pix-tooltip="菜单" aria-label="打开菜单" aria-expanded="false">
		<i class="ri-menu-2-line" aria-hidden="true"></i>
	</button>
	<button type="button" class="pix-global-tool pix-global-search pix-tooltip pix-tooltip-left pix-search-trigger" data-pix-tooltip="搜索" aria-label="打开搜索">
		<i class="ri-search-line" aria-hidden="true"></i>
	</button>
	<?php if ( is_user_logged_in() ) : ?>
	<button type="button" class="pix-global-tool pix-global-user pix-tooltip pix-tooltip-left pix-mobile-menu-trigger" data-pix-tooltip="用户中心" aria-label="用户中心" aria-expanded="false">
		<i class="ri-user-3-line" aria-hidden="true"></i>
	</button>
	<?php else : ?>
	<button type="button" class="pix-global-tool pix-global-user pix-tooltip pix-tooltip-left" data-pix-tooltip="登录 / 注册" data-pix-auth-open="login" aria-haspopup="dialog" aria-controls="modal-login" aria-label="登录 / 注册">
		<i class="ri-user-3-line" aria-hidden="true"></i>
	</button>
	<?php endif; ?>
	<button type="button" class="pix-global-tool pix-global-back pix-tooltip pix-tooltip-left pix-mobile-topbar-back" data-pix-tooltip="返回" aria-label="返回上一页" data-home-url="<?php echo esc_url( home_url( '/' ) ); ?>" data-dashboard-url="<?php echo esc_url( user_dashboard_url( 'center' ) ); ?>">
		<i class="ri-arrow-left-s-line" aria-hidden="true"></i>
	</button>
	<?php endif; ?>
	<button type="button" class="pix-global-tool pix-global-theme-toggle pix-tooltip pix-tooltip-left" data-pix-theme-toggle data-pix-tooltip="切换黑暗模式" aria-label="切换黑暗模式" aria-pressed="false" hidden aria-hidden="true">
		<i class="ri-moon-line" aria-hidden="true"></i>
	</button>
	<button type="button" class="pix-global-tool pix-global-back-top pix-tooltip pix-tooltip-left" data-pix-back-top data-pix-tooltip="回到顶部" aria-label="回到顶部">
		<i class="ri-arrow-up-line" aria-hidden="true"></i>
	</button>
</div>
<?php wp_footer(); ?>

</body>
</html>