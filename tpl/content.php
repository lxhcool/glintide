<?php
/**
 * 文章卡片模板(首页/归档/搜索列表)
 *
 * @package glintide
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'pix-post-card' ); ?>>

	<div class="pix-post-card-body">
		<?php
		$glintide_cats = get_the_category();
		if ( ! empty( $glintide_cats ) ) :
			?>
			<a class="pix-post-card-cat" href="<?php echo esc_url( get_category_link( $glintide_cats[0]->term_id ) ); ?>"><?php echo esc_html( $glintide_cats[0]->name ); ?></a>
		<?php endif; ?>

		<h2 class="pix-post-card-title">
			<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
		</h2>

		<div class="pix-post-card-excerpt">
			<?php the_excerpt(); ?>
		</div>

		<div class="pix-post-card-meta">
			<span class="pix-post-card-author">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 24 ); ?>
				<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php the_author(); ?></a>
			</span>
			<span><i class="ri-calendar-line" aria-hidden="true"></i><?php echo esc_html( get_the_date() ); ?></span>
			<span><i class="ri-chat-3-line" aria-hidden="true"></i><?php comments_number( '0', '1', '%' ); ?></span>
		</div>

	</div>

</article>
