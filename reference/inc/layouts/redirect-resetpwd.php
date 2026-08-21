<?php
wp_head();
?>
<link rel="stylesheet" href="https://npm.elemecdn.com/gahotx-cdn@1.0.14/fonts/harmony/regular.min.css" media="all" onload="this.media='all'">
<?php
//
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

$check_type = get_op('pwd_check_type','all');

if($check_type == 'phone'){
	$check_text = '手机号码';
	$check_icon = 'ri-smartphone-line';
} else if($check_type == 'email'){
	$check_text = '邮箱';
	$check_icon = 'ri-mail-line';
} else {
	$check_text = '手机号码或邮箱';
	$check_icon = 'ri-smartphone-line';
}

echo '<div class="resetpwd-wrap">

        <div class="resetpwd-header">
            <div class="resetpwd-title"><i class="ri-door-lock-fill"></i>密码找回</div>
            <a href="'.home_url().'" class="back-link"><i class="ri-arrow-left-s-line"></i> 返回首页</a>
        </div>

        <div class="resetpwd-box">
        <div class="resetpwd-progress">
            <div class="progress-item active"><span class="step-number">1</span><span>安全验证</span></div>
            <div class="progress-item"><span class="step-number">2</span><span>设置新密码</span></div>
            <div class="progress-item"><span class="step-number">3</span><span>完成</span></div>
        </div>
        <div class="resetpwd-step-1-wrap resetpwd-step-panel">
                <form id="resetpwd-form" class="resetpwd-form resetpwd-step-1 ajax-auth" action="resetpwd" method="post">
                    <label for="email_phone">
                    <i class="'.$check_icon.' logonicon"></i>
                    <input type="text" id="email_phone" name="email_phone" class="required" placeholder="请输入'.$check_text.'">
                    </label>
                    <label for="smscode" class="pwd-send-code">
                        <i class="ri-shield-keyhole-line logonicon"></i>
                        <input id="smscode" type="text" class="required" name="smscode" placeholder="请输入验证码">
                        '.captcha_type('reset_pwd').'
                    </label>
                    <div class="top-tips">请输入您账户绑定的'.$check_text.'，我们将向该'.$check_text.'发送验证码，以确认您的身份。</div>
                </form>
                <div class="reset-pwd-btn">
                    <a class="next-pwd">下一步</a>
                </div>
            </div>
        </div>
    </div>'; 






wp_footer();        
