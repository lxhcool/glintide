<?php
/**
 * 统一文章单页。
 *
 * @package glintide
 */

get_header();
?>

<main id="primary" class="glintide-content">
	<div class="glintide-site-layout">
		<aside class="glintide-site-column glintide-site-column--left" aria-label="左侧栏">
			<?php echo glintide_render_left_navigation(); ?>
		</aside>

		<section class="glintide-site-column glintide-site-column--center" aria-label="文章详情">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php
				$post_id      = get_the_ID();
				$type         = glintide_card_get_type( $post_id );
				$type_data    = glintide_card_get_type_data( $post_id );
				$author_id    = (int) get_the_author_meta( 'ID' );
				$author_name  = get_the_author();
				$author_name  = $author_name ? $author_name : get_bloginfo( 'name' );
				$author_avatar = ( $author_id && function_exists( 'glintide_get_avatar_url' ) ) ? glintide_get_avatar_url( $author_id ) : '';
				$author_avatar = $author_avatar ? $author_avatar : GLINTIDE_URL . '/assets/images/default-avatar.png';
				$comments     = get_comments(
					array(
						'post_id' => $post_id,
						'status'  => 'approve',
						'orderby' => 'comment_date_gmt',
						'order'   => 'ASC',
					)
				);
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( array( 'glintide-card-single', 'glintide-card-single--' . $type ) ); ?>>
					<a class="glintide-single-back" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<i class="ri-arrow-left-line" aria-hidden="true"></i>
						<span>返回首页</span>
					</a>

					<header class="glintide-card-single-header">
						<?php if ( 'photo' !== $type ) : ?>
							<h1 class="glintide-card-single-title"><?php the_title(); ?></h1>
						<?php endif; ?>

						<div class="glintide-card-single-author">
							<img class="glintide-single-avatar" src="<?php echo esc_url( $author_avatar ); ?>" alt="">
							<div class="glintide-single-author-info">
								<span class="glintide-single-author-name"><?php echo esc_html( $author_name ); ?></span>
								<span class="glintide-single-author-date"><?php echo esc_html( get_the_date( 'Y-m-d H:i' ) ); ?></span>
							</div>
						</div>
					</header>

					<?php echo glintide_card_media_html( $post_id, 'single' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

					<?php if ( 'photo' !== $type ) : ?>
						<div class="glintide-card-single-content">
							<?php
							the_content();
								wp_link_pages(
									array(
										'before' => '<div class="page-links">' . esc_html__( '分页:', 'glintide' ),
										'after'  => '</div>',
									)
								);
							?>
						</div>
					<?php endif; ?>

					<section class="glintide-single-comments" data-glintide-single-comments data-glintide-post-id="<?php echo esc_attr( $post_id ); ?>" data-glintide-comment-nonce="<?php echo esc_attr( wp_create_nonce( 'glintide_card_comment_' . $post_id ) ); ?>">
						<div class="glintide-note-divider">共 <?php echo esc_html( count( $comments ) ); ?> 条评论</div>
						<ul class="glintide-note-comments" data-glintide-single-list>
							<?php if ( ! empty( $comments ) ) : ?>
								<?php
								$glintide_by_id   = array();
								$glintide_roots   = array();
								foreach ( $comments as $glintide_comment ) {
									$glintide_by_id[ $glintide_comment->comment_ID ] = $glintide_comment;
								}
								foreach ( $comments as $glintide_comment ) {
									$glintide_pid = (int) $glintide_comment->comment_parent;
									if ( $glintide_pid && isset( $glintide_by_id[ $glintide_pid ] ) ) {
										$glintide_by_id[ $glintide_pid ]->glintide_replies[] = $glintide_comment;
									} else {
										$glintide_roots[] = $glintide_comment;
									}
								}
								foreach ( $glintide_roots as $glintide_comment ) :
									?>
									<?php
									foreach ( array( $glintide_comment ) as $glintide_item ) :
										$glintide_is_reply = isset( $glintide_item->glintide_is_reply );
										?>
										<li class="glintide-note-comment<?php echo $glintide_is_reply ? ' glintide-note-comment--reply' : ''; ?>" data-comment-id="<?php echo esc_attr( $glintide_item->comment_ID ); ?>">
											<img class="glintide-note-comment-avatar" src="<?php echo esc_url( glintide_card_comment_avatar( $glintide_item ) ); ?>" alt="" loading="lazy">
											<div class="glintide-note-comment-body">
												<span class="glintide-note-comment-author"><?php echo esc_html( $glintide_item->comment_author ); ?></span>
												<p class="glintide-note-comment-content"><?php echo esc_html( $glintide_item->comment_content ); ?></p>
												<span class="glintide-note-comment-date" title="<?php echo esc_attr( get_comment_date( 'Y-m-d H:i', $glintide_item ) ); ?>"><?php echo esc_html( glintide_card_relative_date( get_comment_date( 'Y-m-d H:i', $glintide_item ) ) ); ?></span>
												<button type="button" class="glintide-note-comment-reply-btn" data-comment-id="<?php echo esc_attr( $glintide_item->comment_ID ); ?>" data-comment-author="<?php echo esc_attr( $glintide_item->comment_author ); ?>">回复</button>
											</div>
										</li>
									<?php endforeach; ?>
									<?php
									if ( ! empty( $glintide_comment->glintide_replies ) ) :
										foreach ( $glintide_comment->glintide_replies as $glintide_reply ) :
											?>
											<li class="glintide-note-comment glintide-note-comment--reply" data-comment-id="<?php echo esc_attr( $glintide_reply->comment_ID ); ?>">
												<img class="glintide-note-comment-avatar" src="<?php echo esc_url( glintide_card_comment_avatar( $glintide_reply ) ); ?>" alt="" loading="lazy">
												<div class="glintide-note-comment-body">
													<span class="glintide-note-comment-author"><?php echo esc_html( $glintide_reply->comment_author ); ?></span>
													<p class="glintide-note-comment-content"><?php echo esc_html( $glintide_reply->comment_content ); ?></p>
													<span class="glintide-note-comment-date" title="<?php echo esc_attr( get_comment_date( 'Y-m-d H:i', $glintide_reply ) ); ?>"><?php echo esc_html( glintide_card_relative_date( get_comment_date( 'Y-m-d H:i', $glintide_reply ) ) ); ?></span>
													<button type="button" class="glintide-note-comment-reply-btn" data-comment-id="<?php echo esc_attr( $glintide_reply->comment_ID ); ?>" data-comment-author="<?php echo esc_attr( $glintide_reply->comment_author ); ?>">回复</button>
												</div>
											</li>
										<?php endforeach; ?>
									<?php endif; ?>
								<?php endforeach; ?>
							<?php else : ?>
								<li class="glintide-note-comments-empty">还没有评论,来抢沙发吧~</li>
							<?php endif; ?>
						</ul>
						<div class="glintide-note-comment-form">
							<input type="text" class="glintide-note-comment-input" data-glintide-single-input placeholder="说点什么…" maxlength="1000">
							<button type="button" class="glintide-note-emoji-btn" data-glintide-emoji-toggle aria-label="插入表情">
								<i class="ri-emotion-happy-line" aria-hidden="true"></i>
							</button>
							<button type="button" class="glintide-note-send" data-glintide-single-send>发送</button>
						</div>
					</section>
				</article>
			<?php endwhile; ?>
		</section>

		<aside class="glintide-site-column glintide-site-column--right" aria-label="右侧栏">
			<?php echo glintide_render_right_tools(); ?>
		</aside>
	</div>
</main>

<?php get_footer(); ?>
