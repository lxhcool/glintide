<?php

?>

<div id="login_form_box" uk-modal>
    <div class="modal_inner uk-modal-dialog uk-modal-body round8 uk-margin-auto-vertical">
    <button class="uk-modal-close-outside" type="button" uk-close></button>
    <form id="login" class="ajax-auth log" action="login" method="post">
        <p class="log_title">登录 | SIGN IN</p>
        <?php echo wp_nonce_field('ajax-login-nonce', 'security',true,false); ?>
        <label for="username">
            <i class="ri-user-4-line"></i>
            <input id="username" type="text" class="required" name="username" placeholder="用户名">
        </label>
        <label for="password">
            <i class="ri-lock-line"></i>
            <input id="password" type="password" class="required" name="password" placeholder="密码">
        </label>
        <input class="submit_button" type="submit" value="LOGIN">

    </form>
    </div>
</div>