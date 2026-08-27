<?php
/**
 * 评论模板
 *
 * @package glintide
 */

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>

		<h2 class="comments-title">
			<?php
			$glintide_comment_count = get_comments_number();
			printf(
				/* translators: %s: 评论数 */
				esc_html( _n( '%s 条评论', '%s 条评论', $glintide_comment_count, 'glintide' ) ),
				esc_html( number_format_i18n( $glintide_comment_count ) )
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 44,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation();

		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( '评论已关闭。', 'glintide' ); ?></p>
		<?php endif; ?>

	<?php endif; ?>

	<?php comment_form(); ?>

</div><!-- #comments -->