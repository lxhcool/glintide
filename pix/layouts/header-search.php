<?php
$type = isset($_COOKIE["s_type"]) ? $_COOKIE["s_type"] : 'post';
$p_active = ($type == 'post') ? 'active' : '';
$m_active = ($type == 'moment') ? 'active' : '';

if($type == 'post'){
    $set_text = '文章';
} else if($type == 'moment'){
    $set_text = '片刻';
} else {
    $set_text = '文章';
}

?>

<?php if((get_op('top_nav_on_web') && !wp_is_mobile() )){ ?>
<div class="top_nav top_tool">
    <a class="top_nav_btn"><i class="ri-menu-2-line"></i></a>
    <div class="top_menu_box" uk-dropdown="mode: click;flip: false;offset: 12;toggle:.top_nav_btn;animation:uk-animation-slide-top-small;animate-out:uk-animation-slide-top-small;delay-hide:5000;boundary: .top_bar;stretch: x;">
        <div class="inner">
        <?php
        
        if (has_nav_menu('top')) {
            wp_nav_menu( array(
                'theme_location' => 'top',
                'menu_id'        => 'top_menu',
                'walker' => new pix_Walker_Nav_Menu()
            ) );
            } else {
                echo '<div class="nav_tips">请前往后台设置顶部菜单</div>';
            }
        
        ?>
        </div>
    </div>
</div>
<?php } ?>

<?php if((get_op('top_nav_on_m') == true && wp_is_mobile())){ ?>
<div class="m_top_nav top_tool">
    <a class="top_nav_btn" uk-toggle="target: #m_offcanvas"><i class="ri-menu-2-line"></i></a>
    <div id="m_offcanvas" class="m_offcanvas uk-animation-fast" uk-offcanvas="overlay: true;mode: push;container:.main_wrap">
        <div class="uk-offcanvas-bar inner">

        <div class="m_logo"><a href="<?php echo home_url(); ?>"><img src="<?php echo get_op('mobile_logo'); ?>"></a></div>
        <?php
        
        if (has_nav_menu('top')) {
            wp_nav_menu( array(
                'theme_location' => 'top',
                'menu_id'        => 'top_menu',
                'walker' => new pix_Walker_Nav_Menu()
            ) );
            } else {
                echo '<div class="nav_tips">请前往后台设置顶部菜单</div>';
            }
        
        ?>
        </div>
    </div>
</div>
<?php } ?>

<!-- 搜索区域 -->

<div class="search_mod">
<?php

if(get_op('top_s_type') == 'normal') { ?>
<div class="search_box top_s_box">
    <div class="set_text"><?php echo $set_text ?></div>
<div class="s_set_box top_set_box" uk-dropdown="mode: click;toggle:.set_text;pos:bottom-center;animation:uk-animation-slide-top-small;offset:10">
        <div class="inner">
            <a class="<?php echo $p_active ?>" data="post">文章</a>
            <a class="<?php echo $m_active ?>" data="moment">片刻</a>
        </div>
    </div>
    <form method="get" id="top_search" class="top_search-form index_s_form" action="<?php echo esc_url(home_url('/')); ?>">
        <input class="s_input uk-input" type="search" name="s" placeholder="Search">
        <input type="hidden" name="type" value="<?php echo $type ?>">
        <i class="ri-search-line s_toogle_btn"></i>
    </form>	
</div>

    <?php if(get_op('m_search_on')){ ?>
        <div class="t_search top_tool icon_color"><a href="#search_modal" uk-toggle><i class="ri-search-line s_toogle_btn"></i></a></div>
    <?php } ?> 
    
<?php } else if(get_op('top_s_type') == 'modal') {
    echo '<div class="t_search_on top_tool icon_color"><a href="#search_modal" uk-toggle><i class="ri-search-line s_toogle_btn"></i></a></div>';
} 

echo '</div>';


