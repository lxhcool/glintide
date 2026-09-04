<?php
/**
 * 首页内容卡片。
 *
 * 文章卡片使用图文阅读结构；照片、音乐、视频和链接
 * 继续交给各自的媒体渲染器处理。
 *
 * @package glintide
 */

$post_id         = get_the_ID();
$type            = glintide_card_get_type( $post_id );
$type_data       = glintide_card_get_type_data( $post_id );
$title           = get_the_title( $post_id );
$title           = $title ? $title : $type_data['label'];
$permalink       = get_permalink( $post_id );
$is_article      = 'text' === $type;
$is_link         = 'link' === $type;
$is_content_card = 'post' === get_post_type( $post_id );
$author          = get_the_author();
$author          = $author ? $author : get_bloginfo( 'name' );
$author_id       = (int) get_the_author_meta( 'ID' );
$author_avatar   = ( $author_id && function_exists( 'glintide_get_avatar_url' ) ) ? glintide_get_avatar_url( $author_id ) : '';
$avatar_fallback = function_exists( 'glintide_get_default_avatar_url' ) ? glintide_get_default_avatar_url() : GLINTIDE_URL . '/assets/images/default-avatar.png';
$article_body_source = $is_article && function_exists( 'glintide_card_get_plain_content' ) ? glintide_card_get_plain_content( $post_id ) : '';
$article_excerpt     = $is_article ? trim( (string) wp_strip_all_tags( get_post_field( 'post_excerpt', $post_id ) ) ) : '';
$article_body       = $is_article ? wp_trim_words( $article_excerpt ? $article_excerpt : $article_body_source, 40, '…' ) : '';
$article_image_urls = ( $is_article && function_exists( 'glintide_card_get_image_urls' ) ) ? glintide_card_get_image_urls( $post_id ) : array();
$article_image      = ! empty( $article_image_urls ) ? $article_image_urls[0] : '';
$article_likes      = $is_article ? absint( get_post_meta( $post_id, 'likes_count', true ) ) : 0;
$article_modal      = $is_content_card ? ' data-glintide-modal="' . esc_attr( $post_id ) . '" data-glintide-no-pjax' : '';

if ( $is_article && '' === $article_body ) {
	$article_body = '这篇文章还没有添加正文内容。';
}

// 链接卡片:域名与路径。
$link_url   = $is_link ? glintide_card_get_link_url( $post_id ) : '';
$link_label = $is_link ? glintide_card_get_link_label( $post_id ) : '';
$link_host  = '';
$link_path  = '';
if ( $link_url ) {
	$link_host = wp_parse_url( $link_url, PHP_URL_HOST );
	$link_host = $link_host ? preg_replace( '/^www\./', '', $link_host ) : '';
	$link_path = wp_parse_url( $link_url, PHP_URL_PATH );
	$link_path = trim( (string) $link_path, '/' );
	$link_path = function_exists( 'mb_substr' ) ? mb_substr( $link_path, 0, 42 ) : substr( $link_path, 0, 42 );
	$full_path = trim( (string) wp_parse_url( $link_url, PHP_URL_PATH ), '/' );
	if ( $link_path && function_exists( 'mb_strlen' ) && mb_strlen( $full_path ) > 42 ) {
		$link_path .= '…';
	} elseif ( $link_path && ! function_exists( 'mb_strlen' ) && strlen( $full_path ) > 42 ) {
		$link_path .= '…';
	}
}
$link_label = $link_label ? $link_label : ( $title ? $title : '打开链接' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( array( 'glintide-card', 'glintide-card--' . $type, $is_article ? 'glintide-card--article' : '' ) ); ?> data-glintide-card>
	<?php if ( $is_link ) : ?>
		<div class="glintide-link-preview" data-glintide-link-preview>
			<div class="glintide-link-preview-window">
				<iframe src="<?php echo esc_url( $link_url ); ?>" title="<?php echo esc_attr( $link_label ); ?>" loading="lazy" tabindex="-1"></iframe>
			</div>
			<a class="glintide-link-preview-overlay" href="<?php echo esc_url( $link_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( '打开链接：' . $link_label ); ?>">
				<span class="glintide-link-preview-bar"><i class="ri-links-line" aria-hidden="true"></i><strong><?php echo esc_html( $link_label ); ?></strong><span><?php echo esc_html( $link_host ); ?></span><i class="ri-external-link-line" aria-hidden="true"></i></span>
			</a>
		</div>
	<?php elseif ( $is_article ) : ?>
		<?php if ( $article_image ) : ?>
			<a class="glintide-article-cover-link" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( '查看文章：' . $title ); ?>"<?php echo $article_modal; ?>>
				<img class="glintide-article-cover" src="<?php echo esc_url( $article_image ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" decoding="async">
			</a>
		<?php endif; ?>

		<div class="glintide-article-heading">
			<h2 class="glintide-article-title">
				<a href="<?php echo esc_url( $permalink ); ?>" rel="bookmark"<?php echo $article_modal; ?>><?php echo esc_html( $title ); ?></a>
			</h2>
		</div>

		<?php if ( $article_body ) : ?>
			<p class="glintide-article-summary"><?php echo esc_html( $article_body ); ?></p>
		<?php endif; ?>

		<footer class="glintide-article-footer">
			<time class="glintide-article-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y/m/d' ) ); ?></time>
			<div class="glintide-article-stats" aria-label="文章互动数据">
				<span class="glintide-article-stat"><i class="ri-heart-3-line" aria-hidden="true"></i><span><?php echo esc_html( $article_likes ); ?></span></span>
				<span class="glintide-article-stat"><i class="ri-chat-3-line" aria-hidden="true"></i><span><?php echo esc_html( get_comments_number( $post_id ) ); ?></span></span>
			</div>
		</footer>
	<?php else : ?>
		<?php echo glintide_card_media_html( $post_id, 'card' ); ?>
	<?php endif; ?>
</article>
