<?php 
global $current_user;
$user_ID = $current_user->ID;
$comment_author = @$_COOKIE["comment_author_" . COOKIEHASH];
$comment_author_email = @$_COOKIE["comment_author_email_" . COOKIEHASH];
$comment_author_url = @$_COOKIE["comment_author_url_" . COOKIEHASH];
$avatar_size = '50';
?>

<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="t_commentform">

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
				<div class="avaleft">
					<div id="ava-popover" class="visitor-avatar">
							<?php get_avatar($comment_author_email,'100') ?>
						</div>
				</div>
			</div>
    <textarea id="comment" class="comment" name="comment" placeholder="说点什么" required="required"></textarea>
    <div class="topic_comments_foobar">
        <div class="left">
            <?php echo show_smile_btn(); ?>
        </div>
        <div class="right">
		<?php if(get_op('com_robot') && !is_user_logged_in() ){ echo no_robot_check();} ?>
        <div class="cancel-comment-reply" data="loop"><?php cancel_comment_reply_link('取消'); ?></div>
        <div class="com_push">			
            <input class="push_comment" name="push_comment" type="submit" id="push_comment" tabindex="5" value="发送"/>
            <input type="hidden" name="comment_post_ID" value="0" id="comment_post_ID">
            <input type="hidden" name="comment_parent" id="comment_parent" value="0">
        </div>	
        </div>
    </div>
</form>