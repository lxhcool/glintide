<?php
/**
 * Glintide 最小首页模板
 *
 * @package glintide
 */

get_header();

$glintide_card_page  = max( 1, absint( get_query_var( 'paged' ) ), absint( get_query_var( 'page' ) ) );
$glintide_card_query = glintide_get_card_feed_query( $glintide_card_page );
?>

<main id="primary" class="glintide-content">
	<div class="glintide-site-layout">
		<aside class="glintide-site-column glintide-site-column--left" aria-label="左侧栏">
			<?php echo glintide_render_left_navigation(); ?>
		</aside>
		<section class="glintide-site-column glintide-site-column--center" aria-label="主要内容">
			<div class="glintide-card-stream">
				<header class="glintide-card-stream-header">
					<nav class="glintide-card-tabs" aria-label="内容类型筛选">
						<button type="button" class="glintide-card-tab is-active" data-card-type="" aria-pressed="true">全部</button>
						<button type="button" class="glintide-card-tab" data-card-type="text" aria-pressed="false">文章</button>
						<button type="button" class="glintide-card-tab" data-card-type="photo" aria-pressed="false">照片</button>
						<button type="button" class="glintide-card-tab" data-card-type="music" aria-pressed="false">音乐</button>
						<button type="button" class="glintide-card-tab" data-card-type="video" aria-pressed="false">视频</button>
						<button type="button" class="glintide-card-tab" data-card-type="link" aria-pressed="false">链接</button>
					</nav>
					<form class="glintide-card-search" role="search" action="#">
						<i class="ri-search-line" aria-hidden="true"></i>
						<input class="glintide-card-search-input" type="search" name="card_q" value=""
							placeholder="搜索内容…" aria-label="搜索内容卡片" autocomplete="off">
						<button type="button" class="glintide-card-search-clear" aria-label="清除搜索" hidden>
							<i class="ri-close-line" aria-hidden="true"></i>
						</button>
					</form>
				</header>

				<?php if ( $glintide_card_query->have_posts() ) : ?>
					<div class="glintide-card-grid">
						<?php
						while ( $glintide_card_query->have_posts() ) :
							$glintide_card_query->the_post();
							get_template_part( 'tpl/content', 'card' );
						endwhile;
						?>
					</div>
					<?php if ( $glintide_card_query->max_num_pages > 1 ) : ?>
						<div class="glintide-card-feed-sentinel" data-glintide-infinite
							data-paged="<?php echo esc_attr( $glintide_card_page ); ?>"
							data-max="<?php echo esc_attr( (int) $glintide_card_query->max_num_pages ); ?>">
							<span class="glintide-card-feed-loading" hidden><i class="ri-loader-4-line" aria-hidden="true"></i>加载中…</span>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<div class="glintide-card-empty">
						<i class="ri-layout-masonry-line" aria-hidden="true"></i>
						<strong>还没有内容卡片</strong>
						<span>发布第一篇文章、照片、音乐、视频、链接或动态。</span>
						<?php if ( current_user_can( 'publish_posts' ) ) : ?>
							<a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>">现在发布</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<aside class="glintide-site-column glintide-site-column--right" aria-label="右侧栏">
			<?php echo glintide_render_right_tools(); ?>
		</aside>
	</div>
</main>

<?php wp_reset_postdata(); ?>
<?php get_footer(); ?>
