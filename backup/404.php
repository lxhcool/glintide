<?php
/**
 * 404 页面模板
 *
 * @package glintide
 */

get_header();
?>

<div class="glintide-content">

	<div class="box glintide-404">
		<div class="glintide-404-code">404</div>
		<h1><?php esc_html_e( '页面不存在', 'glintide' ); ?></h1>
		<p><?php esc_html_e( '你访问的页面可能已被删除或移动,去看看别的内容吧。', 'glintide' ); ?></p>
		<a class="glintide-404-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<i class="ri-home-4-line" aria-hidden="true"></i>
			<?php esc_html_e( '返回首页', 'glintide' ); ?>
		</a>
	</div>

</div>

<?php
get_footer();