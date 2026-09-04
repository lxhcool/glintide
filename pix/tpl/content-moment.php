<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pix
 */

$loca = get_post_meta(get_the_ID(), 'mylocal',true);
$local_h = '';
if(strlen(trim($loca)) > 2){
	$local_h = '<i class="ri-map-pin-2-line"></i>'.$loca.'';
} else {
	$local_h = '';
}
$post_ID = get_the_ID();
$comment_off = get_op('com_close') ? get_op('com_close') :false;
$m_sticky = is_sticky() ? 'unstick' : 'stick';
$m_sticky_text = is_sticky() ? '取消置顶' : '置顶片刻';
?>


<div id="post-<?php the_ID(); ?>" <?php post_class('loop_content p_item moment_item uk-animation-slide-bottom-small'); ?>>
	<div class="p_item_inner">
		<?php if(get_user_role('administrator') && is_user_logged_in()) { ?>
			<div class="post_control">
				<a class="post_control_btn"><i class="ri-menu-3-line"></i></a>
				<div class="post_control_box">
					<div class="post_control_list round8 shadow uk-animation-slide-bottom-small  uk-animation-fast" pid="<?php echo $post_ID ?>">
						<a class="control_edit_post control_type" uk-toggle="target: #create_post_box">编辑片刻</a>
						<a class="<?php echo $m_sticky ?> control_type sticky_btn"><?php echo $m_sticky_text ?></a>
						<a class="control_delete_post control_type">删除片刻</a>
					</div>
				</div>
			</div>
		<?php } ?>
		<div class="list_user_meta">
			<div class="avatar"><?php echo  get_user_avatar(); ?></div>
			<div class="name">
				<?php echo get_nickname(); ?>
				<time itemprop="datePublished" datetime="<?php echo get_the_date('c');?>"><?php echo ''.timeago( get_gmt_from_date(get_the_time('Y-m-d G:i:s')) ); ?></time>
			</div>
		</div>

		<div class="blog_content">
			<div class="entry-content">
				<div class="p_title"><a href="<?php echo get_permalink();?>"><?php the_title('<i class="ri-at-line"></i>','');  ?></a></div>
				<div class="t_content"><p><?php echo moment_excerpt(); ?></p></div>
				<?php echo get_moment_type_content(); ?>
			</div><!-- .entry-content -->

			<span class="ip_loca"><?php echo $local_h ?></span>

			<div class="entry-footer">
				<div class="post_footer_meta">
					<div class="left">
						<?php echo get_like_btn(); ?>
						<?php echo share_btn(); ?>
					</div>
					<div class="right">
						<?php if(is_sticky()){ echo '<span class="sticky_icon"><i class="ri-fire-line"></i> TOP</span>';} ?>
						<?php if($comment_off !=true){ ?>
							<span class="comnum show_comment" pid="<?php echo get_the_ID(); ?>"><i class="ri-message-3-line"></i><?php echo get_comments_number(); ?></span>
						<?php } ?>	
					</div>				
				</div>
				
			</div><!-- .entry-footer -->

			<div class="topic_comments_wrap t_com_<?php echo $post_ID; ?>" style="display:none">
				<?php
					global $withcomments;
					$withcomments = true;
					comments_template('/layouts/topic-comments.php'); 				
				?>
			</div>


		</div>
	</div>
</div><!-- #post-<?php the_ID(); ?> -->
