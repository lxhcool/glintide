<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pix
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
global $current_user;
$user_ID = $current_user->ID;
$comment_off = false;
$is_moment_comment = get_post_type() === 'moment';
$comments_title_class = $is_moment_comment ? 'comments-title pix-moment-comments-title' : 'comments-title';
$comments_count_class = $is_moment_comment ? 'noticom pix-moment-comments-count' : 'noticom';
$comments_title_prefix = $is_moment_comment ? '讨论 | ' : '';
$comments_zero_label = $is_moment_comment ? '暂无评论' : 'NOTHING';
$comments_one_label = $is_moment_comment ? '1 评论' : '1 条评论';
$comments_more_label = $is_moment_comment ? '% 评论' : '% 条评论';

if ( post_password_required() ) {
	return;
}
?>

<?php if($comment_off !=true): ?>
<?php if(comments_open() != false ): //如果评论关闭 ?>  
<div id="comments" class="comments-area" data="normal">

	<h2 class="<?php echo esc_attr($comments_title_class); ?>">
		<i class="ri-discuss-line"></i><?php echo esc_html($comments_title_prefix); ?><span class="<?php echo esc_attr($comments_count_class); ?>"><?php comments_popup_link($comments_zero_label, $comments_one_label, $comments_more_label); ?> </span>
	</h2><!-- .comments-title -->


	<div id="respond_box">
		<div id="respond" class="comment-respond">
			<?php if ( get_option('comment_registration') && !$user_ID ) : //须登录才能评论 ?>
				<div class="overlay_inner cst_com_login">
					<a class="must_login gradient round">登录 | 注册</a>
					<p><i class="ri-notification-line"></i>必须登录之后才能发表评论</p>
				</div>
			<?php else : ?>

				<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform" class="b-top" com-type="normal">	
					
					<?php if ( ! $user_ID ): ?>
						<div id="ava-content" class="commentput">
						<div class="visitor-title">
							<div class="left">你好，<span><?php echo $comment_author ? $comment_author : '朋友'; ?></span></div>
							<a class="edit-visitor-info">修改资料</a>
						</div>
						<div id="comment-author-info-wrap" class="card">	
							<div id="comment-author-info">	
								<div class="com-author">
									<input type="text" name="author" id="author"  value="<?php echo $comment_author; ?>"  size="22" tabindex="1" placeholder="昵称(必填)" />
								</div>				
								<div class="com-email">
									<input type="text" name="email" id="email"  value="<?php echo $comment_author_email; ?>"  size="22" placeholder="邮箱(必填)" tabindex="2" />
								</div>				
								<div class="com-url">
									<input type="text" name="url" id="url"  value="<?php echo $comment_author_url; ?>" size="22" placeholder="http://"  tabindex="3" />
								</div>		
							</div>					
						</div>
						</div>
					<?php endif; ?>

					<div class="content_comments">
						<div class="edit_comment_info"><?php echo comment_visitor( $user_ID, $comment_author, $comment_author_email); ?></div>
						<div class="comarea">
							<textarea name="comment" id="comment"  placeholder="说点什么？" tabindex="4" cols="50" rows="5"></textarea>
						</div>
						<input type="text" name="pix_guard" value="" autocomplete="off" tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;opacity:0;pointer-events:none;">
					</div>

					<div class="com-footer">		
						<div class="com-footer-inner">
							<div class="comment-tools"><?php echo comment_tool_btn(); ?></div>
						
							<div class="com_push">
							
								<div class="cancel-comment-reply" data="single">
									<?php cancel_comment_reply_link('取消'); ?>
								</div>
								<a class="cancel-comment-textarea">取消</a>
								<input class="push_comment protect" name="push_comment" type="submit" id="push_comment" tabindex="5" value="发送">
								<?php comment_id_fields(); ?>
							</div>	
						</div>				
					</div>
					<?php do_action('comment_form', $post->ID); ?>

				</form>
				<?php endif; // If registration required and not logged in ?>
		</div>
	</div>
	<!-- .respond_box -->

	<div class="commentshow">
		<ul class="comment-list">

		<?php
		// You can start editing here -- including this comment!
		if ( have_comments() ) :
			
			wp_list_comments('type=comment&callback=ppo_comment_format&max_depth=10000');

		else :

			echo '<p class="nodata"><i class="ri-ghost-line"></i>空空如也！</p>';

		endif; // Check for have_comments().

		?>

		</ul><!-- .comment-list -->

		<?php echo comment_nav_type(); ?> 

		</div>

</div><!-- #comments -->

<?php 

else :

	echo '<div class="commclose">评论已关闭...</div>';

endif; //评论关闭
endif; //评论总开关
