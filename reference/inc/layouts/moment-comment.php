<?php 
global $current_user;
$user_ID = $current_user->ID;
$comment_author = @$_COOKIE["comment_author_" . COOKIEHASH];
$comment_author_email = @$_COOKIE["comment_author_email_" . COOKIEHASH];
$comment_author_url = @$_COOKIE["comment_author_url_" . COOKIEHASH];
$avatar_size = '50';
$type = get_query_var('msg_action') ? get_query_var('msg_action') : 'moment';

?>

<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="t_commentform" class="b-top pix-moment-comment-form" com-type="<?php echo $type ?>">

<div id="ava-content" class="commentput pix-moment-comment-visitor">
						<?php if ( ! $user_ID ): ?>
						<div class="visitor-title pix-moment-comment-visitor-title">
							<div class="left pix-moment-comment-visitor-name">你好，<span><?php echo $comment_author ? $comment_author : '朋友'; ?></span></div>
							<a class="edit-visitor-info pix-moment-comment-visitor-edit">修改资料</a>
						</div>
						<div id="comment-author-info-wrap" class="card pix-moment-comment-visitor-card">
							<div id="comment-author-info" class="pix-moment-comment-visitor-fields">
								<div class="com-author pix-moment-comment-visitor-field">
									<input type="text" name="author" id="author"  value="<?php echo $comment_author; ?>"  size="22" tabindex="1" placeholder="昵称(必填)" />
								</div>				
								<div class="com-email pix-moment-comment-visitor-field">
									<input type="text" name="email" id="email"  value="<?php echo $comment_author_email; ?>"  size="22" placeholder="邮箱(必填)" tabindex="2" />
								</div>				
								<div class="com-url pix-moment-comment-visitor-field">
									<input type="text" name="url" id="url"  value="<?php echo $comment_author_url; ?>" size="22" placeholder="http://"  tabindex="3" />
								</div>		
							</div>					
						</div>
						<?php endif; ?>
					</div>


					<div class="clear"></div>

					<div class="content_comments pix-moment-comment-editor">
						<div class="edit_comment_info pix-moment-comment-editor-user"><?php echo comment_visitor( $user_ID, $comment_author, $comment_author_email); ?></div>
						<div class="comarea pix-moment-comment-textarea-wrap">
							<textarea name="comment" id="comment" class="mo-com-textarea pix-moment-comment-textarea" placeholder="说点什么？" tabindex="4" cols="50" rows="5"></textarea>
						</div>
						<input type="text" name="pix_guard" value="" autocomplete="off" tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;opacity:0;pointer-events:none;">
					</div>
    <div class="topic_comments_foobar pix-moment-comment-toolbar">
        <div class="left pix-moment-comment-toolbar-tools">
			<div class="comment-tools pix-moment-comment-tools"><?php if($type == 'moment') { echo comment_tool_btn();} ?></div>
        </div>
        <div class="right pix-moment-comment-toolbar-submit">

       
        <div class="com_push pix-moment-comment-submit-group">
			<div class="cancel-comment-reply pix-moment-comment-cancel" data="<?php echo $type ?>">
				<?php if($type == 'reply'){
						echo '<a href="javascript:void(0);" class="cancel-msg-reply-link pix-moment-comment-cancel-btn">取消</a>';
					} else {
						cancel_comment_reply_link('取消'); 
					}?>
			</div>	
            <input class="push_comment protect pix-moment-comment-submit" name="push_comment" type="submit" id="push_comment" tabindex="5" value="发送"/>
            <input type="hidden" name="comment_post_ID" value="0" id="comment_post_ID">
            <input type="hidden" name="comment_parent" id="comment_parent" value="0">
        </div>	
        </div>
    </div>
</form>
