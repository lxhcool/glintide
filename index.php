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
					<div class="glintide-card-stream-heading">
						<span class="glintide-card-stream-kicker">CONTENT STREAM</span>
						<h1 class="glintide-card-stream-title">内容卡片</h1>
						<p class="glintide-card-stream-description">文章、照片、音乐、视频、链接和动态，在这里自然流动。</p>
					</div>
					<?php if ( current_user_can( 'publish_posts' ) ) : ?>
						<a class="glintide-card-publish-link" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=glintide_card' ) ); ?>">
							<i class="ri-add-line" aria-hidden="true"></i>
							<span>发布内容</span>
						</a>
					<?php endif; ?>
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
							<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=glintide_card' ) ); ?>">现在发布</a>
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
