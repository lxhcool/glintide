<?php
global $wp_query;
// Current author

$user_id = get_current_user_id();
$user_info = get_userdata($user_id);

$des = !empty($user_info->description) ? $user_info->description : 'TA很懒，什么也没写';
$web = !empty($user_info->user_url) ? $user_info->user_url : '请输入你的网址';
$qq = !empty($user_info->user_qq) ? $user_info->user_qq : '请输入QQ，以方便联系';
$location = !empty($user_info->user_location) ? $user_info->user_location : '未知地带';
$gender = isset($user_info->user_gender) ? $user_info->user_gender : '保密';
$email = !empty($user_info->user_email) ? $user_info->user_email : '暂未绑定';
$phone = !empty($user_info->user_phone) ? encryptTel($user_info->user_phone) : '暂未绑定';

$em_check = check_email_bind($user_id);
$ph_check = get_user_meta($user_id,'user_phone',true);

if($gender == 0){
    $gender_name = '男';
} else if($gender == 1){
    $gender_name = '女';
} else {
    $gender_name = '保密';
}

$has_custom_password = ppo_user_has_custom_password($user_id);

$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'profile';

?>

<div class="user-edit-page pix-dashboard-edit">

<ul class="user-page-tab pix-dashboard-secondary-tabs pix-dashboard-edit-tabs">
    <li><a href="?tab=profile" class="user-tab-item pix-dashboard-secondary-tab pix-dashboard-edit-tab <?php echo $tab === 'profile' ? 'active' : ''; ?>">个人资料</a></li>
    <li><a href="?tab=security" class="user-tab-item pix-dashboard-secondary-tab pix-dashboard-edit-tab <?php echo $tab === 'security' ? 'active' : ''; ?>">安全设置</a></li>
</ul>

<div class="user-page-body user-edit-body pix-dashboard-edit-body">

<?php if($tab === 'profile' || $tab === '') : ?>
<div class="edit-tab-item pix-dashboard-edit-panel pix-dashboard-edit-profile">
    <div class="edit-body-inner pix-dashboard-edit-grid">
        <div class="left pix-dashboard-edit-main">

            <div class="eidt-box edit-top pix-dashboard-edit-meta-card">
                <div class="left pix-dashboard-edit-card-body">
                    <div class="uid_show"><span>UID</span><small><?php echo esc_html($user_id); ?></small></div>
                    <div class="regdate_show">
                        <div><span>注册时间：</span><small><?php echo esc_html(user_show_registered_date($user_id)); ?></small></div>
                        <div><span>上次登录：</span><small><?php get_last_login($user_id); ?></small></div>
                    </div>
                </div>
            </div>

            <div class="eidt-box pix-dashboard-edit-card">
                <div class="left pix-dashboard-edit-card-body">
                    <div class="edit-title pix-dashboard-edit-card-label"><i class="ri-user-5-line"></i>昵称</div>
                    <div class="edit-info pix-dashboard-edit-card-value"><?php echo esc_html(@$user_info->display_name);?></div>
                </div>
                <a class="edit_btn user-info-edit pix-dashboard-edit-action" action="display_name">编辑</a>
            </div>

            <div class="eidt-box pix-dashboard-edit-card">
                <div class="left pix-dashboard-edit-card-body">
                    <div class="edit-title pix-dashboard-edit-card-label"><i class="ri-profile-line"></i>个人简介</div>
                    <div class="edit-info pix-dashboard-edit-card-value"><?php echo esc_html($des); ?></div>
                </div>
                <a class="edit_btn user-info-edit pix-dashboard-edit-action" action="description">编辑</a>
            </div>

            <div class="eidt-box pix-dashboard-edit-card">
                <div class="left pix-dashboard-edit-card-body">
                    <div class="edit-title pix-dashboard-edit-card-label"><i class="ri-user-smile-line"></i>性别</div>
                    <div class="edit-info pix-dashboard-edit-card-value"><?php echo esc_html($gender_name); ?></div>
                </div>
                <a class="edit_btn user-info-edit pix-dashboard-edit-action" action="user_gender" gender="<?php echo esc_attr($gender); ?>">编辑</a>
            </div>

            <div class="eidt-box pix-dashboard-edit-card">
                <div class="left pix-dashboard-edit-card-body">
                    <div class="edit-title pix-dashboard-edit-card-label"><i class="ri-window-line"></i>个人网址</div>
                    <div class="edit-info pix-dashboard-edit-card-value"><?php echo esc_html($web); ?></div>
                </div>
                <a class="edit_btn user-info-edit pix-dashboard-edit-action" action="user_url">编辑</a>
            </div>

            <div class="eidt-box pix-dashboard-edit-card">
                <div class="left pix-dashboard-edit-card-body">
                    <div class="edit-title pix-dashboard-edit-card-label"><i class="ri-qq-line"></i>QQ号码</div>
                    <div class="edit-info pix-dashboard-edit-card-value"><?php echo esc_html($qq); ?></div>
                </div>
                <a class="edit_btn user-info-edit pix-dashboard-edit-action" action="user_qq">编辑</a>
            </div>

            <div class="eidt-box pix-dashboard-edit-card">
                <div class="left pix-dashboard-edit-card-body">
                    <div class="edit-title pix-dashboard-edit-card-label"><i class="ri-map-pin-user-line"></i>居住地址</div>
                    <div class="edit-info pix-dashboard-edit-card-value"><?php echo esc_html($location); ?></div>
                </div>
                <a class="edit_btn user-info-edit pix-dashboard-edit-action" action="user_location">编辑</a>
            </div>

            <div id="user-edit-modal" class="pix-modal pix-hs-modal pix-dashboard-modal pix-dashboard-edit-modal pix-dashboard-edit-hs-modal hidden" role="dialog" tabindex="-1" aria-labelledby="user-edit-modal-title">
                <div class="pix-modal-dialog hs-overlay-animation-target">
                <div class="pix-modal-panel pix-dashboard-modal-dialog user-edit-modal">
                    <div id="user-edit-modal-title" class="edit-modal-title pix-dashboard-modal-title"></div>
                    
                    <div class="edit-form-box pix-dashboard-modal-body pix-dashboard-form"></div>
                    <div class="user-edit-tool pix-dashboard-modal-actions pix-dashboard-form-actions">
                        <button class="pix-dashboard-form-button pix-dashboard-form-button-ghost" type="button" data-pix-modal-close="#user-edit-modal">取消</button>
                        <button class="user-edit-sure pix-dashboard-form-button pix-dashboard-form-button-primary" type="button" uid="<?php echo $user_id ?>">确定</button>
                    </div>
                </div>
                </div>
            </div>

        </div>

        <div class="right pix-dashboard-edit-side">
            <div class="yinsi-edit pix-dashboard-edit-privacy">
                <h4><i class="ri-chat-private-line"></i><span>隐私设置</span></h4>
                <div class="pix-dashboard-edit-empty">开发中...</div>
            </div>
        </div>
    </div>
    </div>
    <?php endif; ?>

    <?php if($tab === 'security') : ?>
    <div class="edit-tab-item pix-dashboard-edit-panel pix-dashboard-edit-security">
        <div class="safe-edit-wrap pix-dashboard-edit-section">
        <h4 class="safe-title"><i class="ri-shield-user-line"></i><span>账户安全</span></h4>
        <div class="safe-edit-body pix-dashboard-edit-section-body">

            <div class="safe-eidt-box pix-dashboard-edit-card pix-dashboard-edit-safe-card">
                    <div class="left pix-dashboard-edit-card-body">
                        <div class="edit-title pix-dashboard-edit-card-label"><i class="ri-lock-password-line safe-pass"></i>账户密码</div>
                        <div class="edit-info pix-dashboard-edit-card-value"><?php if($has_custom_password) echo '建议每隔一段时间修改一次密码'; else echo '请设置自定义密码' ?></div>
                    </div>
                    <a class="edit_btn user-pass-edit pix-dashboard-edit-action" action="user_pass"><?php if($has_custom_password) echo '修改'; else echo '设置' ?></a>
                </div>

            <div class="safe-eidt-box pix-dashboard-edit-card pix-dashboard-edit-safe-card">
                    <div class="left pix-dashboard-edit-card-body">
                        <div class="edit-title pix-dashboard-edit-card-label"><i class="ri-mail-line safe-email"></i><?php echo $em_check ? '修改邮箱' : '绑定邮箱'; ?></div>
                        <div class="edit-info pix-dashboard-edit-card-value"><?php echo esc_html($email); ?></div>
                    </div>
                    <a class="edit_btn user-safe-edit pix-dashboard-edit-action" action="user_email" type="<?php echo esc_attr($em_check); ?>"><?php if($em_check) echo '修改'; else echo '绑定' ?></a>
                </div>   
                
            <div class="safe-eidt-box pix-dashboard-edit-card pix-dashboard-edit-safe-card">
                    <div class="left pix-dashboard-edit-card-body">
                        <div class="edit-title pix-dashboard-edit-card-label"><i class="ri-smartphone-line safe-phone"></i><?php echo $ph_check ? '修改手机' : '绑定手机'; ?></div>
                        <div class="edit-info pix-dashboard-edit-card-value"><?php echo esc_html($phone); ?></div>
                    </div>
                    <a class="edit_btn user-safe-edit pix-dashboard-edit-action" action="user_phone"><?php if($ph_check) echo '修改'; else echo '绑定' ?></a>
                </div>     

        </div>  
        </div>

        <div class="safe-edit-wrap pix-dashboard-edit-section">
            <h4 class="safe-title"><i class="ri-parent-line"></i><span>社交账户绑定</span></h4>
            <div class="safe-edit-body pix-dashboard-edit-section-body pix-dashboard-edit-oauth-list">
                <?php echo  oauth_bind_list($user_id); ?>
            </div>
        </div>
        
        <div id="user-safe-modal" class="pix-modal pix-hs-modal pix-dashboard-modal pix-dashboard-safe-modal pix-dashboard-edit-hs-modal hidden" role="dialog" tabindex="-1" aria-labelledby="user-safe-modal-title">
                <div class="pix-modal-dialog hs-overlay-animation-target">
                <div class="pix-modal-panel pix-dashboard-modal-dialog user-safe-modal">
                    <div id="user-safe-modal-title" class="safe-modal-title pix-dashboard-modal-title"></div>
                    
                    <div class="safe-form-box pix-dashboard-modal-body pix-dashboard-form"></div>
                    <div class="user-edit-tool pix-dashboard-modal-actions pix-dashboard-form-actions">
                        <button class="pix-dashboard-form-button pix-dashboard-form-button-ghost" type="button" data-pix-modal-close="#user-safe-modal">取消</button>
                        <button class="user-safe-sure pix-dashboard-form-button pix-dashboard-form-button-primary" type="button" uid="<?php echo $user_id ?>">确定</button>
                    </div>
                </div>
                </div>
            </div>

        <div id="user-repass-modal" class="pix-modal pix-hs-modal pix-dashboard-modal pix-dashboard-repass-modal pix-dashboard-edit-hs-modal hidden" role="dialog" tabindex="-1" aria-labelledby="user-repass-modal-title">
            <div class="pix-modal-dialog hs-overlay-animation-target">
            <div class="pix-modal-panel pix-dashboard-modal-dialog user-repass-modal">
                <div id="user-repass-modal-title" class="safe-modal-title pix-dashboard-modal-title"></div>

                <div class="safe-form-box pix-dashboard-modal-body pix-dashboard-form"></div>
                <div class="user-edit-tool pix-dashboard-modal-actions pix-dashboard-form-actions">
                    <button class="pix-dashboard-form-button pix-dashboard-form-button-ghost" type="button" data-pix-modal-close="#user-repass-modal">取消</button>
                    <button class="user-pass-sure pix-dashboard-form-button pix-dashboard-form-button-primary" type="button" uid="<?php echo $user_id ?>">确定</button>
                </div>
            </div>
            </div>
        </div>

    </div>
    <?php endif; ?>

</div>
