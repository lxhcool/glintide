<?php
/**
 * 文章卡片模板(首页/归档/搜索列表)
 *
 * @package glintide
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'glintide-post-card' ); ?>>

	<div class="glintide-post-card-header">
		<div class="glintide-post-card-feature">
			<a class="glintide-post-card-thumb-link" href="<?php the_permalink(); ?>">
				<img class="glintide-post-card-thumb" src="<?php echo esc_url( glintide_get_thumb( get_the_ID() ) ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" decoding="async">
			</a>
		</div>
	</div>

	<div class="glintide-post-card-content">
		<div class="glintide-post-card-copy">
			<div class="glintide-post-card-title">
				<h2 class="glintide-post-card-title-heading">
					<a class="glintide-post-card-title-link" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
				</h2>
			</div>
			<div class="glintide-post-card-time">
				<span class="nickname">@<?php the_author(); ?></span><span class="glintide-post-card-time-separator">-</span><time class="timeago" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			</div>
			<div class="glintide-post-card-excerpt">
				<p><?php the_excerpt(); ?></p>
			</div>
		</div>
	</div>

	<div class="glintide-post-card-footer">
		<div class="glintide-post-card-meta">
			<div class="glintide-post-card-social"><?php echo glintide_post_card_meta(); ?></div>
			<div class="glintide-post-card-cats"><div class="pf-cat"><i class="ri-hashtag" aria-hidden="true"></i><?php echo esc_html( glintide_post_first_cat() ); ?></div></div>
		</div>
	</div>

</article>