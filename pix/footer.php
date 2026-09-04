<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package pix
 */

?>
	
	</div><!-- pjax-container -->
	<div class="footer_main">
		<?php pix_site_cr(); ?>
	</div>
	</div><!-- page_main -->

	<div class="main_sidebar uk-width-1-3@m">
		<div class="sidebar_right">
			<div class="right_admin_tool" uk-sticky="offset: 0">
			<?php 
				$mod_array = array('mod_third','mod_third_s');
				if(in_array(get_op('layout_set'),$mod_array) && !wp_is_mobile()){
					get_template_part('layouts/header','tool');
				} 
				?>	
			</div>
		
            <?php if(is_active_sidebar( 'sidebar_right' )){ ?>				
                    <div class="widget_inner sidebar_right_inner">
						<div uk-sticky="offset: 72">
                        	<?php dynamic_sidebar( 'sidebar_right' ); ?>
						</div>
                    </div>				
			    <?php } ?>
            </div>
	</div>

	</div><!-- main_body -->
				
	<div class="go_top_box">
		<div class="footer_tool">
			<?php if(get_op('bgm_open')) { ?>
				<div class="t_music top_tool icon_color">
					<a class="bg_music"><i class="ri-disc-line"></i></a>			
				</div>
			<?php } ?>
			
			<?php if(get_op('theme_set') !== 'dark-theme') { ?>
				<div class="t_dark top_tool icon_color"><a><i class="ri-contrast-2-line"></i></a></div>
			<?php } ?>
		</div>
			<a class="go_top" href="#page" uk-scroll><i class="ri-arrow-up-s-line"></i></a> 
	</div>
	<?php 
	if(get_op('fpush_open')){
		echo '<div class="footer_nav">';
			get_template_part('layouts/footer','nav');
			echo get_footer_nav();
		echo '</div>';
	} //底部音乐		
	//echo footer_box(); //底部版权信息
	echo msg_modal_box(); //消息盒子
	echo top_search(); //搜索弹窗
	
		if(is_user_logged_in()) {
			get_template_part('layouts/topic-push'); 
		} else {
			get_template_part('layouts/login-form');
		}
		
	?>
	
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
