<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package easter
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

global $current_user;
$user_ID = $current_user->ID;
$avatar_size = '50';
$comment_off = get_op('com_close') ? get_op('com_close') : false;
?>

<?php if($comment_off !=true): ?>
<?php if(comments_open() != false ): //如果评论关闭 ?>  
<div id="comments" class="comments comments-area" data="normal">
		<div class="comments-title">
		<i class="ri-discuss-line"></i>Comments | <span class="noticom"><?php comments_popup_link('NOTHING', '1 条评论', '% 条评论'); ?> </span>
		</div><!-- .comments-title -->

		<div id="respond_box">
		<div id="respond" class="comment-respond">
			
			<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
				<div class="overlay_inner cst_com_login">
					<a class="must_login gradient round">LOGIN IN</a>
					<p><i class="iconfont icon-mima"></i>必须登录之后才能发表状态</p>
				</div>
			<?php else : ?>
			<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">	
			
			<div class ="vi_avatar_box">
				<a class="edit_comment_info"><?php echo comment_visitor( $user_ID, $comment_author, $comment_author_email, $avatar_size ); ?></a>
				<div id="ava-content" class="commentput shadow" uk-drop="mode:click;offset: 10;toggle:.edit-card;pos:bottom-left;animation:uk-animation-scale-down">
						<?php if ( ! $user_ID ): ?>
						<div id="comment-author-info" class="card">	
							<p>*邮箱和昵称必须填写</p>
								<input type="text" name="author" id="author"  value="<?php echo $comment_author; ?>"  size="22" tabindex="1" placeholder="Name" />
								<label for="author"></label>				
								<input type="text" name="email" id="email"  value="<?php echo $comment_author_email; ?>"  size="22" placeholder="Email" tabindex="2" />
								<label for="email"></label>				
								<input type="text" name="url" id="url"  value="<?php echo $comment_author_url; ?>" size="22" placeholder="http://"  tabindex="3" />
								<label for="url"></label>	
							
						</div>
				<?php endif; ?>
				</div>
			</div>
		
			<div class="clear"></div>
		
			<div class="ava_comments">
					<div class="comarea">
						<textarea name="comment" id="comment"  placeholder="say something" tabindex="4" cols="50" rows="5"></textarea>
					</div>
				</div>
				
			
				<div class="com-footer">
					<div class="com_tools">
						<?php echo show_smile_btn(); ?>
					</div>
					<div class="com_push">
					
						<?php if(get_op('com_robot') && !is_user_logged_in() ){ echo no_robot_check();} ?>
					
						<div class="cancel-comment-reply" data="single">
							<?php cancel_comment_reply_link('取消'); ?>
						</div>
						<input class="push_comment" name="push_comment" type="submit" id="push_comment" tabindex="5" value="发送">
						<?php comment_id_fields(); ?>
					</div>					
				</div>
				<?php do_action('comment_form', $post->ID); ?>
			</form>
			
			<?php endif; // If registration required and not logged in ?>
		</div>
		</div>

		<div class="commentshow">
			<ul class="comment-list">
	<?php
	// You can start editing here -- including this comment!
	if ( have_comments() ) :
		
		wp_list_comments('type=comment&callback=cst_comment_format&max_depth=10000');

	else :

		echo '<p class="nodata"><i class="ri-ghost-line"></i>空空如也！</p>';

	endif; // Check for have_comments().

	?>

	</ul><!-- .comment-list -->

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
			<nav class="commentnav" data-fuck="<?php echo $post->ID?>"><?php paginate_comments_links('prev_text=<i class="ri-arrow-left-s-line"></i>&next_text=<i class="ri-arrow-right-s-line"></i>');?></nav>
		<?php endif; ?> 
	</div>


</div><!-- #comments -->

<?php 

else :

	echo '<div class="commclose">评论已关闭...</div>';

endif; //评论关闭
endif; //评论总开关