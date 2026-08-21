<form id="login" class="ajax-auth" action="login" method="post">
    <h3>New to site? <a id="pop_signup" href="">Create an Account</a></h3>
    <hr />
    <h1>登录</h1>
    <p class="status"></p>  
    <?php wp_nonce_field('ajax-login-nonce', 'security'); ?>  
    <label for="username">Username</label>
    <input id="username" type="text" class="required" name="username">
    <label for="password">Password</label>
    <input id="password" type="password" class="required" name="password">
    <label for="repassword">Password</label>
    <input id="repassword" type="repassword" class="required" name="repassword">
    <a class="text-link" href="<?php
    echo wp_lostpassword_url(); ?>">Lost password?</a>
    <input class="submit_button" type="submit" value="LOGIN">
	<a class="close" href="">(close)</a>    
</form>
 
<form id="register" class="ajax-auth"  action="register" method="post">
	<h3>Already have an account? <a id="pop_login"  href="">Login</a></h3>
    <hr />
    <h1>注册</h1>
    <p class="status"></p>
    <?php wp_nonce_field('ajax-register-nonce', 'signonsecurity'); ?>         
    <label for="signonname">Username</label>
    <input id="signonname" type="text" name="signonname" class="required">
    <label for="email">Email</label>
    <input id="email" type="text" class="required email" name="email">
    <label for="signonpassword">Password</label>
    <input id="signonpassword" type="password" class="required" name="signonpassword" >
    <label for="password2">Confirm Password</label>
    <input type="password" id="password2" class="required" name="password2">
    <input class="submit_button" type="submit" value="SIGNUP">
    <a class="close" href="">(close)</a>    
</form>