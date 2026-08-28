<?php
/**
 * 首页多媒体内容卡片。
 *
 * 卡片高度由内容决定,不设固定高度;外层瀑布流容器负责列分配。
 *
 * @package glintide
 */

$post_id    = get_the_ID();
$type       = glintide_card_get_type( $post_id );
$type_data  = glintide_card_get_type_data( $post_id );
$title      = get_the_title();
$title      = $title ? $title : $type_data['label'];
$permalink  = get_permalink( $post_id );
$summary    = glintide_card_get_summary( $post_id );
$hide_body  = in_array( $type, array( 'quote', 'code', 'photo', 'music' ), true );
$is_article = 'text' === $type;
$is_photo   = 'photo' === $type;
$is_music   = 'music' === $type;
$show_topline = ! $is_article && ! $is_photo && ! $is_music;
$categories = get_the_category( $post_id );
$category   = ( ! empty( $categories ) && ! is_wp_error( $categories ) ) ? $categories[0] : null;
$author     = get_the_author();
$author     = $author ? $author : get_bloginfo( 'name' );
$initial    = function_exists( 'mb_substr' ) ? mb_substr( $author, 0, 1 ) : substr( $author, 0, 1 );
$music_url  = 'music' === $type ? glintide_card_get_music_url( $post_id ) : '';
$likes      = absint( get_post_meta( $post_id, 'likes_count', true ) );
$comments   = absint( get_comments_number() );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( array( 'glintide-card', 'glintide-card--' . $type, $is_article ? 'glintide-card--article' : '' ) ); ?> data-glintide-card>
	<?php if ( $show_topline ) : ?>
		<div class="glintide-card-topline">
			<span class="glintide-card-type glintide-card-type--<?php echo esc_attr( $type ); ?>">
				<i class="<?php echo esc_attr( $type_data['icon'] ); ?>" aria-hidden="true"></i>
				<span><?php echo esc_html( $type_data['label'] ); ?></span>
			</span>

			<time class="glintide-card-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?></time>
		</div>
	<?php endif; ?>

	<?php echo glintide_card_media_html( $post_id, 'card' ); ?>

	<?php if ( ! $hide_body ) : ?>
		<div class="glintide-card-body">
			<h2 class="glintide-card-title">
				<a href="<?php echo esc_url( $permalink ); ?>" rel="bookmark"><?php echo esc_html( $title ); ?></a>
			</h2>

			<?php if ( 'music' === $type && $music_url ) : ?>
				<p class="glintide-card-summary glintide-card-summary--ready">可直接播放</p>
			<?php elseif ( $summary ) : ?>
				<p class="glintide-card-summary"><?php echo esc_html( $summary ); ?></p>
			<?php elseif ( $is_article ) : ?>
				<p class="glintide-card-summary glintide-card-summary--empty">暂无文字内容</p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! $is_photo && ! $is_music ) : ?>
	<footer class="glintide-card-footer <?php echo $is_article ? 'glintide-card-footer--article' : ''; ?>">
		<?php if ( $is_article ) : ?>
			<a class="glintide-card-read" href="<?php echo esc_url( $permalink ); ?>" rel="bookmark">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"></path>
				</svg>
				<span class="text">阅读</span>
			</a>

			<span class="glintide-card-stats">
				<button class="glintide-card-stat glintide-card-stat--like" type="button" aria-label="点赞">
					<i class="iconfont icon-dianzan2" aria-hidden="true"></i>
					<span class="number"><?php echo esc_html( number_format_i18n( $likes ) ); ?></span>
				</button>
				<a class="glintide-card-stat glintide-card-stat--comment" href="<?php echo esc_url( $permalink ); ?>#comments" aria-label="查看评论">
					<i class="iconfont icon-icon_comment_linear_light" aria-hidden="true"></i>
					<span class="number"><?php echo esc_html( number_format_i18n( $comments ) ); ?></span>
				</a>
			</span>
		<?php else : ?>
			<span class="glintide-card-author">
				<span class="glintide-card-avatar" aria-hidden="true"><?php echo esc_html( strtoupper( $initial ) ); ?></span>
				<span class="glintide-card-author-name"><?php echo esc_html( $author ); ?></span>
			</span>

			<span class="glintide-card-footer-right">
				<?php if ( $category ) : ?>
					<a class="glintide-card-category" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
				<?php endif; ?>

				<a class="glintide-card-open" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( '查看' . $title ); ?>">
					<i class="ri-arrow-right-up-line" aria-hidden="true"></i>
				</a>
			</span>
		<?php endif; ?>
	</footer>
	<?php endif; ?>
</article>