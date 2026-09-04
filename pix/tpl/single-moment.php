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
?>


<div id="post-<?php the_ID(); ?>" <?php post_class('loop_content p_item moment_item moment_single'); ?>>
	<div class="p_item_inner">
		<div class="list_user_meta">
			<div class="avatar"><?php echo  get_user_avatar(); ?></div>
			<div class="name">
				<?php echo get_nickname(); ?>
				<time itemprop="datePublished" datetime="<?php echo get_the_date('c');?>"><?php echo ''.timeago( get_gmt_from_date(get_the_time('Y-m-d G:i:s')) ); ?></time>
			</div>
		</div>

		<div class="blog_content">
			<div class="entry-content">
				<div class="p_title"><?php the_title('<i class="ri-at-line"></i>','');  ?></div>
				<div class="t_content"><p><?php echo get_the_content(); ?></p></div>
				<?php echo get_moment_type_content(); ?>
			</div><!-- .entry-content -->

			<span class="ip_loca"><?php echo $local_h ?></span>

			<div class="entry-footer">
				<div class="post_footer_meta">
					<div class="left">
						<?php echo get_like_btn(); ?>
						<?php echo share_btn(); ?>
					</div>
					<div class="right" pid="<?php echo $post_ID ?>">
						<?php if (current_user_can( 'edit_post', $post_ID ) || current_user_can( 'manage_options') ) { ?>
							<a class="control_edit_post control_type" uk-toggle="target: #create_post_box"><i class="ri-edit-line"></i>编辑</a>
						<?php } ?>	
					</div>				
				</div>
				
			</div><!-- .entry-footer -->

		</div>
	</div>
</div><!-- #post-<?php the_ID(); ?> -->
