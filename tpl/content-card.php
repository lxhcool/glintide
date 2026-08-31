<?php
/**
 * 首页内容卡片。
 *
 * 文章卡片使用图文阅读结构；照片、音乐、视频、动态、链接和代码
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
$is_content_card = 'glintide_card' === get_post_type( $post_id );
$author          = get_the_author();
$author          = $author ? $author : get_bloginfo( 'name' );
$author_id       = (int) get_the_author_meta( 'ID' );
$author_login    = $author_id ? get_the_author_meta( 'user_login', $author_id ) : '';
$author_handle   = $author_login ? '@' . $author_login : '';
$author_avatar   = ( $author_id && function_exists( 'glintide_get_avatar_url' ) ) ? glintide_get_avatar_url( $author_id ) : '';
$avatar_fallback = function_exists( 'glintide_get_default_avatar_url' ) ? glintide_get_default_avatar_url() : GLINTIDE_URL . '/assets/images/default-avatar.png';
$article_body    = $is_article && function_exists( 'glintide_card_get_plain_content' ) ? glintide_card_get_plain_content( $post_id ) : '';
$article_body    = $is_article ? wp_trim_words( $article_body, 52, '…' ) : '';
$article_callout = $is_article ? trim( (string) wp_strip_all_tags( get_post_field( 'post_excerpt', $post_id ) ) ) : '';
$article_image   = ( $is_article && function_exists( 'glintide_card_get_primary_image_url' ) ) ? glintide_card_get_primary_image_url( $post_id ) : '';
$article_modal   = $is_content_card ? ' data-glintide-modal="' . esc_attr( $post_id ) . '" data-glintide-no-pjax' : '';

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
		<a class="glintide-link-panel" href="<?php echo esc_url( $link_url ); ?>" target="_blank" rel="noopener noreferrer">
			<span class="glintide-link-top">
				<span class="glintide-link-chip">
					<i class="ri-links-line" aria-hidden="true"></i>
					<span>链接</span>
				</span>
				<time class="glintide-link-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?></time>
			</span>

			<span class="glintide-link-main">
				<strong><?php echo esc_html( $link_label ); ?></strong>
				<?php if ( $link_host ) : ?>
					<span class="glintide-link-origin"><i class="ri-global-line" aria-hidden="true"></i><?php echo esc_html( $link_host ); ?><?php echo $link_path ? ' / ' . esc_html( $link_path ) : ''; ?></span>
				<?php endif; ?>
			</span>

			<span class="glintide-link-foot">
				<span class="glintide-article-author">
					<?php if ( $author_avatar ) : ?>
						<img class="glintide-article-avatar" src="<?php echo esc_url( $author_avatar ); ?>" alt="" loading="lazy" decoding="async"
							data-glintide-avatar data-glintide-avatar-fallback="<?php echo esc_url( $avatar_fallback ); ?>">
					<?php else : ?>
						<img class="glintide-article-avatar" src="<?php echo esc_url( $avatar_fallback ); ?>" alt="" loading="lazy" decoding="async">
					<?php endif; ?>
					<span class="glintide-article-author-name"><?php echo esc_html( $author ); ?></span>
				</span>

				<span class="glintide-link-go" aria-hidden="true">
					<i class="ri-external-link-line"></i>
				</span>
			</span>
		</a>
	<?php elseif ( $is_article ) : ?>
		<header class="glintide-article-header">
			<div class="glintide-article-profile">
				<?php if ( $author_avatar ) : ?>
					<img class="glintide-article-avatar" src="<?php echo esc_url( $author_avatar ); ?>" alt="<?php echo esc_attr( $author ); ?>" loading="lazy" decoding="async"
						data-glintide-avatar data-glintide-avatar-fallback="<?php echo esc_url( $avatar_fallback ); ?>">
				<?php else : ?>
					<img class="glintide-article-avatar" src="<?php echo esc_url( $avatar_fallback ); ?>" alt="<?php echo esc_attr( $author ); ?>" loading="lazy" decoding="async">
				<?php endif; ?>
				<span class="glintide-article-identity">
					<strong class="glintide-article-name">
						<?php echo esc_html( $author ); ?>
						<span class="glintide-article-verified" aria-hidden="true"><i class="ri-checkbox-circle-fill"></i></span>
					</strong>
					<?php if ( $author_handle ) : ?>
						<span class="glintide-article-handle"><?php echo esc_html( $author_handle ); ?></span>
					<?php endif; ?>
				</span>
			</div>
			<time class="glintide-article-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y/m/d' ) ); ?></time>
		</header>

		<h2 class="glintide-article-title">
			<a href="<?php echo esc_url( $permalink ); ?>" rel="bookmark"<?php echo $article_modal; ?>><?php echo esc_html( $title ); ?></a>
		</h2>

		<?php if ( $article_image ) : ?>
			<a class="glintide-article-cover-link" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( '查看' . $title ); ?>"<?php echo $article_modal; ?>>
				<img class="glintide-article-cover" src="<?php echo esc_url( $article_image ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" decoding="async">
			</a>
		<?php endif; ?>

		<div class="glintide-article-copy">
			<?php if ( $article_body ) : ?>
				<p class="glintide-article-summary"><?php echo esc_html( $article_body ); ?></p>
			<?php endif; ?>
			<?php if ( $article_callout ) : ?>
				<p class="glintide-article-callout"><i class="ri-double-quotes-l" aria-hidden="true"></i><span><?php echo esc_html( $article_callout ); ?></span></p>
			<?php endif; ?>
		</div>

		<footer class="glintide-article-footer">
			<div class="glintide-article-stats" aria-label="文章数据">
				<span class="glintide-article-stat"><i class="ri-eye-line" aria-hidden="true"></i><?php echo esc_html( glintide_post_views( $post_id ) ); ?></span>
				<span class="glintide-article-stat"><i class="ri-chat-3-line" aria-hidden="true"></i><?php echo esc_html( get_comments_number( $post_id ) ); ?></span>
			</div>
			<a class="glintide-article-read" href="<?php echo esc_url( $permalink ); ?>" rel="bookmark">阅读全文<i class="ri-arrow-right-line" aria-hidden="true"></i></a>
		</footer>
	<?php else : ?>
		<?php echo glintide_card_media_html( $post_id, 'card' ); ?>
	<?php endif; ?>
</article>
