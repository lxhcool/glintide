<div class="top_right">
    <?php echo msg_btn(); ?>
    <?php if(is_user_logged_in()){ ?>
        <div class="t_login top_tool icon_color">				
            <a  class="normal_edit" uk-toggle="target: #create_post_box"><i class="ri-edit-box-line"></i></a>									
        </div>

    <div class="admin_ava">
        <a class="mobile_edit" uk-toggle="target: #create_post_box"><i class="ri-add-line"></i></a>
        <div class="top_ava"><?php echo get_user_avatar(); ?></div>
        <div class="user_pannel round12" uk-dropdown="mode: click;toggle:.top_ava;pos:bottom-right;animation:uk-animation-slide-top-small">
            <div class="inner">
                <a href="<?php echo home_url('/wp-admin'); ?>" target="_blank" pjax="exclude"><i class="ri-function-line"></i>控制台</a>
                <a href="<?php echo wp_logout_url(cst_get_curl()); ?>" pjax="exclude"><i class="ri-logout-circle-r-line"></i>登出</a>
            </div>
        
        </div>
    </div>
    <?php } else { ?>
        <div class="top_tool">
            <a uk-toggle="target: #login_form_box"><i class="ri-user-4-fill"></i></a>
        </div>	
        <?php } ?>

</div>