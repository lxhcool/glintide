<?php
/**
 * 首页多媒体内容卡片。
 *
 * @package glintide
 */

$post_id    = get_the_ID();
$type       = glintide_card_get_type( $post_id );
$type_data  = glintide_card_get_type_data( $post_id );
$title      = get_the_title();
$title      = $title ? $title : $type_data['label'];
$summary      = glintide_card_get_summary( $post_id );
$hide_summary = in_array( $type, array( 'quote', 'code' ), true );
$permalink    = get_permalink( $post_id );
$categories   = get_the_category( $post_id );
$category     = ( ! empty( $categories ) && ! is_wp_error( $categories ) ) ? $categories[0]->name : '';
$author       = get_the_author();
$author       = $author ? $author : get_bloginfo( 'name' );
$initial      = function_exists( 'mb_substr' ) ? mb_substr( $author, 0, 1 ) : substr( $author, 0, 1 );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( array( 'glintide-card', 'glintide-card--' . $type ) ); ?>>
	<div class="glintide-card-topline">
		<span class="glintide-card-type glintide-card-type--<?php echo esc_attr( $type ); ?>">
			<i class="<?php echo esc_attr( $type_data['icon'] ); ?>" aria-hidden="true"></i>
			<span><?php echo esc_html( $type_data['label'] ); ?></span>
		</span>
		<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?></time>
	</div>

	<?php echo glintide_card_media_html( $post_id, 'card' ); ?>

	<div class="glintide-card-body">
		<h2 class="glintide-card-title">
			<a href="<?php echo esc_url( $permalink ); ?>" rel="bookmark"><?php echo esc_html( $title ); ?></a>
		</h2>

		<?php if ( $summary && ! $hide_summary ) : ?>
			<p class="glintide-card-summary"><?php echo esc_html( $summary ); ?></p>
		<?php elseif ( 'text' === $type && ! $summary ) : ?>
			<p class="glintide-card-summary glintide-card-summary--empty">暂无文字内容</p>
		<?php endif; ?>
	</div>

	<footer class="glintide-card-footer">
		<div class="glintide-card-author">
			<span class="glintide-card-avatar" aria-hidden="true"><?php echo esc_html( strtoupper( $initial ) ); ?></span>
			<span><?php echo esc_html( $author ); ?></span>
		</div>
		<div class="glintide-card-footer-right">
			<?php if ( $category ) : ?>
				<span class="glintide-card-category"><?php echo esc_html( $category ); ?></span>
			<?php endif; ?>
			<a class="glintide-card-open" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( '查看' . $title ); ?>">
				<i class="ri-arrow-right-up-line" aria-hidden="true"></i>
			</a>
		</div>
	</footer>
</article>
