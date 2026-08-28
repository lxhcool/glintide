<?php
/**
 * 首页和归档文章卡片
 *
 * @package glintide
 */

$thumb = function_exists( 'glintide_get_thumb' ) ? glintide_get_thumb( get_the_ID() ) : '';
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'glintide-post-card' ); ?>>
	<?php if ( $thumb ) : ?>
		<div class="glintide-post-card-header">
			<div class="glintide-post-card-feature">
				<a class="glintide-post-card-thumb-link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
					<img class="glintide-post-card-thumb" src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" decoding="async">
				</a>
			</div>
		</div>
	<?php endif; ?>

	<div class="glintide-post-card-content">
		<div class="glintide-post-card-copy">
			<h2 class="glintide-post-card-title-heading">
				<a class="glintide-post-card-title-link" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h2>
			<div class="glintide-post-card-time">
				<span class="nickname">@<?php echo esc_html( get_the_author() ); ?></span>
				<span class="glintide-post-card-time-separator">-</span>
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			</div>
			<p class="glintide-post-card-excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 42, '...' ) ); ?></p>
		</div>
	</div>

	<div class="glintide-post-card-footer">
		<div class="glintide-post-card-meta">
			<div class="glintide-post-card-social"><?php echo glintide_post_card_meta(); ?></div>
			<?php $category = glintide_post_first_cat(); ?>
			<?php if ( $category ) : ?>
				<div class="glintide-post-card-cats"><span class="pf-cat"><i class="ri-hashtag" aria-hidden="true"></i><?php echo esc_html( $category ); ?></span></div>
			<?php endif; ?>
		</div>
	</div>
</article>
