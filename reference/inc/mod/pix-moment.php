<?php

if (!function_exists('ppo_moment_label')) {
function ppo_moment_label($key = 'moment'){
    $labels = array(
        'moment' => array('option' => 'moment_name', 'default' => '片刻'),
        'moments' => array('option' => 'moments_name', 'default' => '圈子'),
        'owner' => array('option' => 'moments_owner', 'default' => '圈主'),
        'user' => array('option' => 'moments_user', 'default' => '圈友'),
    );

    if (!isset($labels[$key])) {
        return '';
    }

    return get_op($labels[$key]['option'], $labels[$key]['default']);
}
}

if (!function_exists('ppo_moment_slug')) {
function ppo_moment_slug($option = 'moment_slug', $default = 'moment'){
    $slug = strtolower((string) get_op($option, $default));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    $slug = trim($slug, '-');

    return $slug ? $slug : $default;
}
}

//增加文章样式-广场
function ppo_custom_post_moment() {
    $name = ppo_moment_label('moment');
    $slug = ppo_moment_slug('moment_slug', 'moment');
    $labels = array(
        'name'               => sprintf( '%1$s',$name),
        'singular_name'      => sprintf( '%1$s',$name),
        'add_new'            => sprintf( '新建一个%1$s',$name),
        'add_new_item'       => sprintf( '新建一个%1$s',$name),
        'edit_item'          => sprintf( '编辑%1$s',$name),
        'new_item'           => sprintf( '新%1$s',$name),
        'all_items'          => sprintf( '所有%1$s',$name),
        'view_item'          => sprintf( '查看%1$s',$name),
        'search_items'       => sprintf( '搜索%1$s',$name),
        'not_found'          => sprintf( '没有找到有关的%1$s',$name),
        'not_found_in_trash' => sprintf( '回收站里没有%1$s',$name),
        'parent_item_colon'  => '',
        'menu_name'          => sprintf( '%1$s',$name),
    );
    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'menu_position' => 5,
		'menu_icon'     => 'dashicons-marker',
        'supports' => array(
			'title',
			'comments',
			'editor'
		),
        'exclude_from_search' => false,
        'capability_type' => 'page',
        'has_archive'   => true,
        'map_meta_cap' => true,
		'yarpp_support' => true,
        'capabilities' => array(
			'create_posts' => false,
		),
        'rewrite' => array( 'slug' => $slug , 'with_front' => false),
		//'taxonomies' => array('category', 'post_tag')
    );
    register_post_type( 'moment', $args );
}
add_action( 'init', 'ppo_custom_post_moment' );

// 片刻固定链接使用 ID 格式 /{moment_slug}/123/
add_filter( 'post_type_link', function($post_link, $post, $leavename) {
    if ($post->post_type === 'moment') {
        $slug = ppo_moment_slug('moment_slug', 'moment');
        return home_url('/' . $slug . '/' . $post->ID . '/');
    }
    return $post_link;
}, 10, 3 );

// 添加重写规则支持 /{moment_slug}/123/ 格式，兼容旧 /moment/123/
add_action( 'init', function() {
    $slug = ppo_moment_slug('moment_slug', 'moment');
    add_rewrite_rule( $slug . '/(\d+)/?$', 'index.php?post_type=moment&p=$matches[1]', 'top' );
    if ($slug !== 'moment') {
        add_rewrite_rule( 'moment/(\d+)/?$', 'index.php?post_type=moment&p=$matches[1]', 'top' );
    }
}, 10 );

//添加一个分类法 moment
function ppo_moment_taxonomy(){
    $name = ppo_moment_label('moments');
    $slugs = ppo_moment_slug('moments_slug', 'moments');
    $labels = array(
            'name' => sprintf( '%1$s',$name),
            'singular_name' => sprintf( '%1$s',$name),
            'search_items' => __( '搜索' ,'ppo' ),
            'popular_items' => sprintf( '热门%1$s',$name),
            'all_items' => sprintf( '所有%1$s',$name),
            'edit_item' => sprintf( '编辑%1$s',$name),
            'update_item' => sprintf( '更新%1$s',$name),
            'add_new_item' => sprintf( '新建%1$s',$name),
            'new_item_name' => sprintf( '新的%1$s',$name),
    );
    $args = array(
            'labels' => $labels,
            'hierarchical' => true,//分层级
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
            'public'                => true,
			'query_var'             => true,
            'show_in_rest'          => true,
			'rewrite'           => array( 'slug' => $slugs , 'with_front' => false ),
    );
    register_taxonomy('moments',array('moment'), $args);
}
add_action('init', 'ppo_moment_taxonomy');

add_action('init', function(){
    $slug = ppo_moment_slug('moments_slug', 'moments');
    if ($slug !== 'moments') {
        add_rewrite_rule('moments/([^/]+)/?$', 'index.php?moments=$matches[1]', 'top');
    }
}, 10);

// 后台创建圈子时自动设置圈主并加入
function ppo_on_moment_created($term_id, $tt_id) {
    $term = get_term($term_id, 'moments');
    if (!$term || is_wp_error($term)) return;

    $user_id = get_current_user_id();
    if (!$user_id) return;

    update_term_meta($term_id, 'mo_owner', $user_id);
    update_user_join($user_id, $term_id);
}
add_action('created_moments', 'ppo_on_moment_created', 10, 2);

//添加一个标签 moment
function ppo_moment_tag(){
    $name = ppo_moment_label('moment');
    $slug = ppo_moment_slug('moment_slug', 'moment');
    $tags = 'moment_tag';

    $labels = array(
            'name' => sprintf( '%1$s标签',$name),
            'singular_name' => sprintf( '%1$s标签',$name),
            'search_items' => __( '搜索' ,'ppo' ),
            'popular_items' => sprintf( '热门的%1$s标签',$name),
            'all_items' => sprintf( '所有%1$s标签',$name),
            'edit_item' => sprintf( '编辑%1$s标签',$name),
            'update_item' => sprintf( '更新%1$s标签',$name),
            'add_new_item' => sprintf( '新建%1$s标签',$name),
            'new_item_name' => sprintf( '新的%1$s标签',$name),
    );
    $args = array(
            'labels' => $labels,
            'hierarchical' => true,//分层级
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
            'public'                => true,
			'query_var'             => true,
            'show_in_rest'          => true,
			'rewrite'           => array( 'slug' => $slug . '-tag' , 'with_front' => false),
    );
    register_taxonomy($tags,array('moment'), $args);
}
add_action('init', 'ppo_moment_tag');

add_action('update_option_ppo_options', 'ppo_moment_flush_rewrite_on_slug_change', 10, 2);
function ppo_moment_flush_rewrite_on_slug_change($old_value, $value){
    $old_value = is_array($old_value) ? $old_value : array();
    $value = is_array($value) ? $value : array();

    $old_moment_slug = isset($old_value['moment_slug']) ? $old_value['moment_slug'] : 'moment';
    $new_moment_slug = isset($value['moment_slug']) ? $value['moment_slug'] : 'moment';
    $old_moments_slug = isset($old_value['moments_slug']) ? $old_value['moments_slug'] : 'moments';
    $new_moments_slug = isset($value['moments_slug']) ? $value['moments_slug'] : 'moments';

    if ($old_moment_slug !== $new_moment_slug || $old_moments_slug !== $new_moments_slug) {
        flush_rewrite_rules(false);
    }
}

// 插入附件
function move_to_media($temp_name,$destination, $name){
    $output = array();
    $user = wp_get_current_user();
    $user_id = $user->ID;
    //$extension = pathinfo( $filename, PATHINFO_EXTENSION );
    //$new_filename = pix_generate_random_code( 20 )  . '.' . $extension;
        $filename = $temp_name;
    
        $new_file = $destination;
        $filetype = wp_check_filetype( basename( $filename ), null );
        $wp_upload_dir = wp_upload_dir();
            $attachment = array(
                'guid'           => $wp_upload_dir['url'] . '/' . basename( $new_file ),
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content'   => '',
                'post_status'    => 'inherit',
                'post_author'    => $user_id,
            );
        $attach_id = wp_insert_attachment( $attachment, $new_file);
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
               
        // Generate meta data
        $attach_data = wp_generate_attachment_metadata( $attach_id, $new_file );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        //$attach_url = wp_get_attachment_image_src($attach_id, 'full')[0];

        /* array_push($output, array(
            'thumb' => wp_get_attachment_image_src($attach_id, 'large')[0],
            'src' => $attach_url,
        ));  */
        
        return $attach_id;           
    
}

// 圈子图标
function circle_icon(){
    $img = '<img src="'.THEME_URL.'/img/circle.svg">';
    return $img;
}

//随机图片名
function ppo_generate_random_code($length=10) {
 
    $string = '';
    $characters = "23456789ABCDEFHJKLMNPRTVWXYZabcdefghijklmnopqrstuvwxyz";
  
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters)-1)];
    }
  
    return $string;
  
 }

 // 圈子顶部banner

function moment_banner_info(){
    global $wp_query;
    $moment_label = ppo_moment_label('moment');
    $owner_label = ppo_moment_label('owner');
    $user_label = ppo_moment_label('user');
    $term_id = get_queried_object()->term_id ?? false;
    if($term_id){
        $mo_data = get_mo_num_data($term_id);
        //$meta = get_term_meta( $term_id, '_ppo_moments_options', true );
        $term_data = get_term_by('id',$term_id,'moments');
        $img = get_term_meta( $term_id, 'mo_cat_img' , true);
        $img = $img ? $img : THEME_URL.'/img/modef.png';
        $banner = get_term_meta( $term_id, 'mo_cat_banner', true );
        $banner = $banner ? $banner : THEME_URL.'/img/banner.jpg';
        $owner_id = get_term_meta($term_id, 'mo_owner', true);
        $owner_id = $owner_id ? $owner_id : 1;
        $user_info = get_userdata($owner_id);
        $current_user_id = get_current_user_id();
        $notice_dot = is_user_logged_in() ? mo_all_notice_dot($term_id) : '';
        $manage_btn = '';
        if(is_user_logged_in() && $term_id && moment_auth('', $current_user_id, $term_id)) {
            $manage_btn = '<a href="'.home_url('/moment-manage?term_id='.$term_id.'').'" class="mo-manage pix-moment-circle-hero-menu-item"><i class="ri-settings-3-line"></i>管理'.$notice_dot.'</a>';
        }
        $hero_actions = is_user_logged_in() ? '<div class="mo-user-box pix-moment-circle-hero-actions-box">
                            <div class="pix-moment-circle-hero-more hs-dropdown [--placement:bottom-end] [--offset:8] [--auto-close:inside] [--strategy:static]">
                                <button type="button" class="pix-moment-circle-hero-more-btn hs-dropdown-toggle" aria-haspopup="menu" aria-expanded="false" aria-label="圈子入口"><i class="ri-more-line"></i>'.$notice_dot.'</button>
                                <div class="pix-moment-dropdown pix-moment-circle-hero-menu hs-dropdown-menu hidden" role="menu">
                                    <div class="pix-dropdown-motion pix-dropdown-animated pix-dropdown-slide-down" data-hs-dropdown-transition>
                                        <a class="mo-join pix-moment-circle-hero-menu-item" data="join" role="menuitem"><i class="ri-user-add-line"></i>加入的圈子</a>
                                        <a class="mo-create pix-moment-circle-hero-menu-item" data="create" role="menuitem"><i class="ri-group-2-line"></i>创建的圈子</a>
                                        '.$manage_btn.'
                                    </div>
                                </div>
                                <div class="mo-user-join-drop pix-moment-dropdown pix-moment-circle-user-dropdown pix-moment-circle-hero-list-dropdown hs-dropdown-menu hidden" role="menu">
                                    <div class="pix-dropdown-motion pix-dropdown-animated pix-dropdown-slide-down" data-hs-dropdown-transition></div>
                                </div>
                                <div class="mo-user-create-drop pix-moment-dropdown pix-moment-circle-user-dropdown pix-moment-circle-hero-list-dropdown hs-dropdown-menu hidden" role="menu">
                                    <div class="pix-dropdown-motion pix-dropdown-animated pix-dropdown-slide-down" data-hs-dropdown-transition></div>
                                </div>
                            </div>
                        </div>' : '';

        $info = '<div class="mo-banner-info pix-moment-circle-hero-info">
                    <div class="top pix-moment-circle-hero-top">
                        <div class="left pix-moment-circle-hero-main">
                            <div class="icon-img pix-moment-circle-hero-icon"><img class="lazy" data-src="'.$img.'"></div>
                            <div class="info pix-moment-circle-hero-copy">
                                <div class="term-name pix-moment-circle-hero-title">'.$term_data->name.'</div>
                                <div class="term-info pix-moment-circle-hero-owner">
                                    <span>'.$owner_label.' <a href="'.get_author_posts_url($owner_id).'">'.$user_info->display_name.'</a></span>
                                </div>
                            </div>
                        </div>
                        <div class="right pix-moment-circle-hero-stats">
                            <span class="pix-moment-circle-hero-stat"><b><i class="ri-outlet-line"></i> '.$moment_label.'</b><small>'.$mo_data['count'].'</small></span>
                            <span class="pix-moment-circle-hero-stat"><b><i class="ri-user-5-line"></i> '.$user_label.'</b><small>'.$mo_data['join'].'</small></span>
                        </div>
                    </div>
                <div class="mo-banner-des pix-moment-circle-hero-desc">
                    提出好的建议问题，也是一门学问！
                </div>
                </div>';

           

        $html = '<div class="mo-top-banner pix-moment-circle-hero">
                    <img class="banner-img lazy" data-src="'.$banner.'">
                    <div class="bt-cover pix-moment-circle-hero-cover"></div>
                    '.$info.'
                    '.$hero_actions.'
                </div>';

                return $html;
    }
   
}

// 圈子个人信息
function mo_user_box($term_id){

    $current_user = wp_get_current_user();	
	$user_id = $current_user->ID;
    $moment_label = ppo_moment_label('moment');
    $manage_btn = '';
    $notice_dot = mo_all_notice_dot($term_id);
    if(is_user_logged_in()){
        $avatar = get_u_avatar($user_id,'img');
        $user_info = get_userdata($user_id);
        $mo_owner = get_term_meta($term_id, 'mo_owner', true);
        if($term_id && moment_auth('',$user_id,$term_id)) {
            $manage_btn = '<a href="'.home_url('/moment-manage?term_id='.$term_id.'').'" class="mo-manage pix-moment-circle-user-action"><i class="ri-settings-3-line"></i>管理'.$notice_dot.'</a>';
        };

        $html = '<div class="mo-user-box pix-moment-circle-user-box">
                    <div class="mo-user-inner pix-moment-circle-user-inner">
                        <div class="left-info pix-moment-circle-user-main">
                            <div class="left pix-moment-circle-user-avatar">'.$avatar.'</div>
                            <div class="u-info pix-moment-circle-user-copy">
                                <div class="name pix-moment-circle-user-name">'.$user_info->display_name.'</div>
                                <div class="u-num pix-moment-circle-user-meta">
                                    <span>'.count_user_posts($user_id, 'moment', true).$moment_label.'</span> ·
                                    <span>'.ppo_get_follower_count($user_id).'粉丝</span> ·
                                    <span>'.ppo_get_following_count($user_id).'关注</span>
                                </div>
                            </div>
                        </div>
                        <div class="right-btn pix-moment-circle-user-actions">
                            <div class="pix-moment-circle-user-action-wrap hs-dropdown [--placement:bottom-end] [--offset:8] [--auto-close:inside] [--strategy:static]">
                                <a class="mo-join pix-moment-circle-user-action hs-dropdown-toggle" data="join" aria-haspopup="menu" aria-expanded="false"><i class="ri-user-add-line"></i>加入的</a>
                                <div class="mo-user-join-drop pix-moment-dropdown pix-moment-circle-user-dropdown hs-dropdown-menu hidden" role="menu">
                                    <div class="pix-dropdown-motion pix-dropdown-animated pix-dropdown-slide-down" data-hs-dropdown-transition></div>
                                </div>
                            </div>
                            <div class="pix-moment-circle-user-action-wrap hs-dropdown [--placement:bottom-end] [--offset:8] [--auto-close:inside] [--strategy:static]">
                                <a class="mo-create pix-moment-circle-user-action hs-dropdown-toggle" data="create" aria-haspopup="menu" aria-expanded="false"><i class="ri-group-2-line"></i>创建的</a>
                                <div class="mo-user-create-drop pix-moment-dropdown pix-moment-circle-user-dropdown hs-dropdown-menu hidden" role="menu">
                                    <div class="pix-dropdown-motion pix-dropdown-animated pix-dropdown-slide-down" data-hs-dropdown-transition></div>
                                </div>
                            </div>
                            '.$manage_btn.'
                        </div>
                    </div>
                </div>';
    
        return $html;
    }
    
}

function pix_get_default_moment_cat_id($user_id = 0){
    $term_id = absint(get_cu('moment_default_cat', 0));
    if(!$term_id){
        return 0;
    }

    $term = get_term($term_id, 'moments');
    if(!$term || is_wp_error($term)){
        return 0;
    }

    $user_id = $user_id ? absint($user_id) : get_current_user_id();
    if(current_user_can('manage_options') || get_term_meta($term_id, 'mo_owner', true) == $user_id){
        return $term_id;
    }

    return pix_moment_user_joined_term($user_id, $term_id) ? $term_id : 0;
}

 // 圈子分类按钮
function get_moment_cat_btn(){
    global $wp_query;
    $moments_label = ppo_moment_label('moments');
    $term_id = get_queried_object()->term_id ?? false;
    //$de_mos = get_op('moment_de_cat','');

    if(!$term_id){
        $default_term_id = pix_get_default_moment_cat_id();
        if($default_term_id){
            $img = get_term_meta( $default_term_id, 'mo_cat_img' , true);
            $img = $img ? $img : THEME_URL.'/img/modef.png';
            $term_data = get_term_by('id', $default_term_id, 'moments');
            $html = '<div class="pix-moment-category-dropdown-wrap pix-moment-circle-select-wrap hs-dropdown [--placement:bottom-start] [--offset:8] [--auto-close:inside] [--strategy:static]">
                    <a class="mo-cir-btn pix-moment-category-button pix-moment-circle-select-button active is-selected hs-dropdown-toggle" catid="'.$default_term_id.'" aria-haspopup="menu" aria-expanded="false"><div class="cat-thum pix-moment-category-button-icon pix-moment-circle-select-button-icon"><img src="'.$img.'" class="cover-img rounded"></div> <span>'.$term_data->name.'</span></a>';
        } else {
            $html = '<div class="pix-moment-category-dropdown-wrap pix-moment-circle-select-wrap hs-dropdown [--placement:bottom-start] [--offset:8] [--auto-close:inside] [--strategy:static]">
                    <a class="mo-cir-btn pix-moment-category-button pix-moment-circle-select-button hs-dropdown-toggle" aria-haspopup="menu" aria-expanded="false"><div class="cat-thum pix-moment-category-button-icon pix-moment-circle-select-button-icon"><i class="ri-outlet-line"></i></div> <span>'.$moments_label.'</span></a>';
        }
        $html .= '<div class="circle-drop mo-cat-drop pix-moment-dropdown pix-moment-category-dropdown pix-moment-circle-select-dropdown hs-dropdown-menu hidden" role="menu">
                    <div class="pix-dropdown-motion pix-dropdown-animated pix-dropdown-slide-down" data-hs-dropdown-transition>
                    '.get_moment_cat().'
                    </div>
                    </div>
                    </div>';
    } else {
        //$meta = get_term_meta( $term_id, '_ppo_moments_options', true );
        $img = get_term_meta( $term_id, 'mo_cat_img' , true);
        $img = $img ? $img : THEME_URL.'/img/modef.png';
        $term_data = get_term_by('id',$term_id,'moments');
        $html = '<a class="mo-cir-btn pix-moment-category-button pix-moment-circle-select-button active is-selected" catid="'.$term_id.'"><div class="cat-thum pix-moment-category-button-icon pix-moment-circle-select-button-icon"><img src="'.$img.'" class="cover-img rounded"></div> <span>'.$term_data->name.'</span></a>';
    }
    
    return $html;
                
}

// 片刻首页推荐圈子
function moment_rec_cat(){
    $html = '';
    $list = '';
    $moment_label = ppo_moment_label('moment');
    $moments_label = ppo_moment_label('moments');
    $user_label = ppo_moment_label('user');
    $mos_list = get_cu('mos_home_hot','');
    $open = get_cu('mos_home_hot_show',false);
    $type = get_cu('mos_home_show_type','grid');
    $allmo = home_url().'/allmoments';
    if($open && !empty($mos_list)){
        $arr = array(
            'taxonomy'   => 'moments',
            'hide_empty' => false,
            'include' => $mos_list,
            'orderby' => 'include',
            'number'     => 6,
        );

        $terms = get_terms($arr);
        $compact_class = (!is_wp_error($terms) && count($terms) <= 2) ? ' is-compact' : '';

        foreach($terms as $term){
            $term_id = $term->term_id;
            $term_name = $term->name;
            $mo_data = get_mo_num_data($term_id);
            $count = $term->count;
            $term_link = get_term_link($term_id,'moments');
            $img = get_term_meta( $term_id, 'mo_cat_img' , true);
            $banner = get_term_meta( $term_id, 'mo_cat_banner', true );
            $img = $img ? $img : THEME_URL.'/img/modef.png';
            $banner = $banner ? $banner : THEME_URL.'/img/banner.jpg';
            
            if($type == 'grid'){
                $html .= '<a href="'.$term_link.'" class="top-mos-cat item pix-moment-circle-card pix-moment-circle-grid-card">
                            <div class="bg-banner pix-moment-circle-banner"><img class="lazy" data-src="'.$banner.'"></div>
                            <div class="left pix-moment-circle-icon"><img class="lazy" data-src="'.$img.'"></div>
                            <div class="right pix-moment-circle-info">
                                <div class="title pix-moment-circle-title">'.$term_name.'</div>
                                <div class="info pix-moment-circle-meta"><span>'.$count.$moment_label.'</span> · <span>'.$mo_data['join'].$user_label.'</span></div>
                                </div>                          
                            </a>';
            } else {
                $html .= '<a href="'.$term_link.'" class="top-mos-cat-slide-item swiper-slide item pix-moment-circle-card pix-moment-circle-slide-card">
                            <div class="bg-banner pix-moment-circle-banner"><img class="lazy" data-src="'.$banner.'"></div>
                                <div class="bottom pix-moment-circle-bottom">
                                <div class="bg-cover pix-moment-circle-cover"></div>
                                <div class="left pix-moment-circle-icon"><img class="lazy" data-src="'.$img.'"></div>
                                    <div class="right pix-moment-circle-info">
                                        <div class="title pix-moment-circle-title">'.$term_name.'</div>
                                        <div class="info pix-moment-circle-meta"><span>'.$count.$moment_label.'</span> · <span>'.$mo_data['join'].$user_label.'</span></div>
                                    </div>   
                                </div>                       
                            </a>';
            }
            

        }

        if($type == 'grid'){
            $list = '<div class="mos-home-hot-cat pix-moment-circle-section pix-moment-circle-section-grid'.$compact_class.'"><div class="top pix-moment-circle-head"><span><i class="ri-fire-line"></i>热门'.$moments_label.'</span><a href="'.$allmo.'">全部'.$moments_label.'<i class="ri-arrow-right-s-line"></i></a></div><div class="pix-moment-circle-scroll">'.$html.'</div></div>';
        } else {
            $list = '<div class="mos-home-hot-cat pix-moment-circle-section pix-moment-circle-section-slide'.$compact_class.'">
                        <div class="top pix-moment-circle-head"><span><i class="ri-fire-line"></i>热门'.$moments_label.'</span><a href="'.$allmo.'">全部'.$moments_label.'<i class="ri-arrow-right-s-line"></i></a></div>
                        <div class="mos-swiper-box pix-moment-circle-swiper"><div class="swiper" id="top-mos-cat-slide"><div class="swiper-wrapper">'.$html.'</div><div class="swiper-button-prev"></div><div class="swiper-button-next"></div></div><div class="swiper-pagination"></div></div>
                        </div>';
        }
        

    } 

    return $list;
    
}

// 圈子消息条
function mos_notice_bar(){
    $icon = THEME_URL.'/img/notice.png';
    $moments_label = ppo_moment_label('moments');
    $link_array = array(
        'url' => '#',
        'target' => '_blank',
        'text' => '',
    );
    $text = get_cu('mos_home_notice','这是一条'.$moments_label.'公告');
    $show = get_cu('mos_home_notice_show',false);
    $link = get_cu('mos_home_notice_link');
    $link = !empty($link) ? $link : $link_array;
    $html = '<div class="mos-notice-bar pix-moment-notice">
                <a href="'.$link['url'].'" class="inner pix-moment-notice-inner" target="'.$link['target'].'">
                <div class="left pix-moment-notice-icon"><img src="'.$icon.'"></div>
                <div class="right pix-moment-notice-content"><div class="content pix-moment-notice-text">'.$text.'</div><span class="pix-moment-notice-arrow"><i class="ri-arrow-right-s-line"></i></span></div>
                </a>            
            </div>';
    if($show){
        return $html;
    }
    
}

// 判断圈子预设标签下有无子项
function check_motag_child($data){
    $terms = get_terms( array(
        'taxonomy'   => 'moments',
        'hide_empty' => false,
        'meta_query' => array(
            array(
                'key' => 'ppo_moments_tag',
                'value' => $data,
                'compare' => '=',
            ),
        )
    ) );

    if(is_array($terms) && !empty($terms)){
        return true;
    }
}

//获取圈子分类
function get_moment_cat(){
    $user_id = get_current_user_id();
    $html = '';
    $nav = mo_tags_nav();
    $def = THEME_URL.'/img/modef.png';
    $moment_label = ppo_moment_label('moment');
    $moments_label = ppo_moment_label('moments');
    $user_label = ppo_moment_label('user');
    $desired_tag = '广场';
    $search = '<div class="mo-tag-s pix-moment-category-search pix-moment-circle-select-search">
                <i class="ri-search-line"></i><input type="text" autocomplete="off" maxlength="100" placeholder="搜索'.$moments_label.'" class="mo-cat-search">
                <div class="mos-s-content pix-moment-category-search-results pix-moment-circle-select-search-results" style="display:none"></div>
                </div>';
    /* $terms = get_terms( array(
        'taxonomy'   => 'moments',
        'hide_empty' => false,
        'meta_query' => array(
            array(
                'key' => 'ppo_moments_tag',
                'value' => $desired_tag,
                'compare' => '=',
            ),
        )
    ) ); */
    $join = get_user_meta($user_id, 'user_mo_joined', true);
    $terms = get_terms( array(
        'taxonomy'   => 'moments',
        'hide_empty' => false,
        'include' => $join,
        //'number' => 3,
    ) );

    $terms = !empty($join) ? $terms : '';

    if(is_array($terms)){
        foreach($terms as $term){
            $term_id = $term->term_id;
            //$meta = get_term_meta( $term_id, '_ppo_moments_options', true );
            $thum = get_term_meta( $term_id, 'mo_cat_img' , true);
            $thum = $thum ? $thum : THEME_URL.'/img/modef.png';
            $mo_data = get_mo_num_data($term_id);
            $html .= '<div class="mo-circle-item pix-moment-category-item pix-moment-circle-select-item rounded" catid="'.$term_id.'">
                        <div class="left pix-moment-category-icon pix-moment-circle-select-icon"><img src="'.$thum.'" class="cover-img rounded"></div>
                        <div class="right pix-moment-category-meta pix-moment-circle-select-meta">
                            <div class="title pix-moment-category-title pix-moment-circle-select-title">'.$term->name.'</div>
                            <div class="count-mo pix-moment-category-count pix-moment-circle-select-count">'.$term->count.$moment_label.' <span>·</span> '.$mo_data['join'].$user_label.'</div>
                        </div>
                        </div>';
        }
    } else {
        $html = '<div class="empty-content"><img src="'.THEME_URL.'/img/empty.png"></div>';
    }

    return $search.$nav.'<div class="mo-circle-content pix-moment-category-list pix-moment-circle-select-list trans-scroll-bar">'.$html.'</div>';
    
} 

// 搜索分类
function search_mo_cat(){
    if (!check_ajax_referer('moment_ajax', 'security', false)) {
        wp_send_json(array(
            'status' => 0,
            'msg'    => '请求已失效，请刷新页面后重试',
            'html'   => '',
        ));
    }

    $html = '';
    $keyword = sanitize_text_field($_POST['keyword'] ?? '');
    $user_id = get_current_user_id();
    $moment_label = ppo_moment_label('moment');
    $moments_label = ppo_moment_label('moments');
    $user_label = ppo_moment_label('user');
    if (empty($keyword)) {
        $msg = array('status' => 0,'msg' => '请输入关键词');
        wp_send_json( $msg );
    }

    $args = array(
        'taxonomy'   => 'moments',
        'hide_empty' => false,
        'search'     => $keyword,
    );

    $terms = get_terms($args);

    if(is_array($terms) && !empty($terms)){
        foreach($terms as $term){
            $term_id = $term->term_id;
            //$meta = get_term_meta( $term_id, '_ppo_moments_options', true );
            $thum = get_term_meta( $term_id, 'mo_cat_img' , true);
            $thum = $thum ? $thum : THEME_URL.'/img/modef.png';
            $mo_data = get_mo_num_data($term_id);
            $html .= '<div class="mo-circle-item pix-moment-category-item pix-moment-circle-select-item rounded" catid="'.$term_id.'">
                        <div class="left pix-moment-category-icon pix-moment-circle-select-icon"><img src="'.$thum.'" class="cover-img rounded"></div>
                        <div class="right pix-moment-category-meta pix-moment-circle-select-meta">
                            <div class="title pix-moment-category-title pix-moment-circle-select-title">'.$term->name.'</div>
                            <div class="count-mo pix-moment-category-count pix-moment-circle-select-count">'.$term->count.$moment_label.' <span>·</span> '.$mo_data['join'].$user_label.'</div>
                        </div>
                        </div>';
        }

        $msg = array('status' => 1,'html' => $html);
    } else {
        //$html = '<div class="empty-content"><img src="'.THEME_URL.'/img/empty.png"></div>';
        $html = '<div class="empty-content"><img src="'.THEME_URL.'/img/empty.png"></div>';
        if(pix_can_create_moment_circle($user_id)){
            $html = '<div class="empty-content"><div class="cr-moment"><span>暂无'.$moments_label.'</span><a href="javascript:;" class="publish-mos">立即创建'.$moments_label.'</a></div></div>';
        }
        $msg = array('status' => 1,'html' => $html);
    }

    wp_send_json( $msg );

}
add_action('wp_ajax_search_mo_cat', 'search_mo_cat');
add_action('wp_ajax_nopriv_search_mo_cat', 'search_mo_cat');

//搜索标签
function search_mo_tag(){
    if (!check_ajax_referer('moment_ajax', 'security', false)) {
        wp_send_json(array(
            'status' => 0,
            'msg'    => '请求已失效，请刷新页面后重试',
            'html'   => '',
        ));
    }

    $html = '';
    $keyword = sanitize_text_field($_POST['keyword'] ?? '');
    $user_id = get_current_user_id();
    $moment_label = ppo_moment_label('moment');
    if (empty($keyword)) {
        $msg = array('status' => 0,'msg' => '请输入关键词');
        wp_send_json( $msg );
    }

    $args = array(
        'taxonomy'   => 'moment_tag',
        'hide_empty' => false,
        'search'     => $keyword,
    );

    $terms = get_terms($args);

    if(is_array($terms) && !empty($terms)){
        foreach($terms as $term){
            $term_id = $term->term_id;
            $html .= '<div class="mo-huati-item pix-moment-tag-item pix-moment-topic-item rounded" tagid="'.$term_id.'">
                        <div class="left pix-moment-tag-icon pix-moment-topic-icon"><i class="ri-hashtag"></i></div>
                        <div class="right pix-moment-tag-meta pix-moment-topic-meta">
                            <div class="title pix-moment-tag-title pix-moment-topic-title">'.$term->name.'</div>
                            <div class="count-mo pix-moment-tag-count pix-moment-topic-count">'.$term->count.$moment_label.'</div>
                        </div>
                        </div>';
        }

        $msg = array('status' => 1,'html' => $html);
    } else {
        $html = '<div class="empty-content"><img src="'.THEME_URL.'/img/empty.png"></div>';
        $msg = array('status' => 0,'html' => $html);
    }

    wp_send_json($msg);
}
add_action('wp_ajax_search_mo_tag', 'search_mo_tag');
add_action('wp_ajax_nopriv_search_mo_tag', 'search_mo_tag');


function get_mocat_list(){
    if (!check_ajax_referer('moment_ajax', 'security', false)) {
        wp_send_json(array(
            'code' => 0,
            'msg'  => '请求已失效，请刷新页面后重试',
            'html' => '',
        ));
    }

    $tag = isset($_POST['tag']) ? sanitize_text_field(wp_unslash($_POST['tag'])) : '';
    $user_id = get_current_user_id();
    $html = '';
    $def = THEME_URL.'/img/modef.png';
    $moment_label = ppo_moment_label('moment');
    $moments_label = ppo_moment_label('moments');
    $user_label = ppo_moment_label('user');

    if($tag == 'create'){
        if(!$user_id){
            wp_send_json(array(
                'code' => 0,
                'msg'  => '请先登录后查看创建的'.$moments_label,
                'html' => '',
            ));
        }

        $terms = get_terms( array(
            'taxonomy'   => 'moments',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => 'mo_owner',
                    'value' => $user_id,
                    'compare' => '=',
                ),
            )
        ) );
    } else if($tag == 'join'){
        if(!$user_id){
            wp_send_json(array(
                'code' => 0,
                'msg'  => '请先登录后查看加入的'.$moments_label,
                'html' => '',
            ));
        }

        $join = get_user_meta($user_id, 'user_mo_joined', true);
        $join = is_array($join) ? $join : explode(',', (string) $join);
        $join = array_filter(array_map('absint', $join));
        $terms = get_terms( array(
            'taxonomy'   => 'moments',
            'hide_empty' => false,
            'include' => $join ,
        ) );

        $terms = !empty($join) ? $terms : array();
    } else {
        $terms = get_terms( array(
            'taxonomy'   => 'moments',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => 'ppo_moments_tag',
                    'value' => $tag,
                    'compare' => '=',
                ),
            )
        ) );
    }

    if(is_array($terms) && !empty($terms)){
        foreach($terms as $term){
            $term_id = $term->term_id;
            //$meta = get_term_meta( $term_id, '_ppo_moments_options', true );
            $thum = get_term_meta( $term_id, 'mo_cat_img' , true);
            $thum = $thum ? $thum : THEME_URL.'/img/modef.png';
            $mo_data = get_mo_num_data($term_id);
            $html .= '<div class="mo-circle-item rounded" catid="'.esc_attr($term_id).'">
                        <div class="left"><img src="'.esc_url($thum).'" class="cover-img rounded"></div>
                        <div class="right">
                            <div class="title">'.esc_html($term->name).'</div>
                            <div class="count-mo">'.absint($term->count).$moment_label.' <span>·</span> '.absint($mo_data['join']).$user_label.'</div>
                        </div>
                        </div>';
        }

        $msg = array('code' => 1, 'html' => $html);
    } else {
        //$html = '<div class="empty-content"><img src="'.THEME_URL.'/img/empty.png"></div>';
        $html = '<div class="empty-content"><img src="'.THEME_URL.'/img/empty.png"></div>';
        if(pix_can_create_moment_circle($user_id)){
            $html = '<div class="empty-content"><div class="cr-moment"><span>暂无'.$moments_label.'</span><a href="javascript:;" class="publish-mos">立即创建'.$moments_label.'</a></div></div>';
        }
        $msg = array('code' => 1, 'html' => $html);
    }

    wp_send_json( $msg );

}
add_action('wp_ajax_get_mocat_list', 'get_mocat_list');
add_action('wp_ajax_nopriv_get_mocat_list', 'get_mocat_list');

function mo_tags_nav(){
    $moments_label = ppo_moment_label('moments');
    $html = '<li><span class="moment-tags-item active" data="join">加入的'.$moments_label.'</span></li>
            <li><span class="moment-tags-item" data="create">创建的'.$moments_label.'</span></li>';
    $mo_tag = get_moments_tag_arr();
    foreach($mo_tag as $v){
        $html .= '<li><span class="moment-tags-item" data="'.$v.'">'.$v.'</span></li>';
    }

    return '<div class="moment-tags-nav"><ul class="pix-moment-tags-items">'.$html.'</ul></div>';
    
}

// 圈子预设标签
function get_moments_tag_arr(){
    $output = array();
    $arr = get_op('moments_tag');
    if($arr){
        foreach($arr as $v){
            $output[$v['name']] = $v['name'];
        }
        return $output;
    } else {
        return array('广场' => '广场','生活' => '生活','学习' => '学习','工作' => '工作','兴趣' => '兴趣','其他' => '其他');
    }
}

// 圈子标签列表
function get_moment_tag(){
    $html = '';
    $search = '<div class="mo-huati-s pix-moment-tag-search pix-moment-topic-search">
                <i class="ri-search-line"></i>
                <input type="text" autocomplete="off" maxlength="100" placeholder="搜索话题" class="mo-tag-search">
                <div class="mos-s-tag-content pix-moment-topic-search-results" style="display:none"></div>
                </div>';
    $terms = get_terms( array(
        'taxonomy'   => 'moment_tag',
        'hide_empty' => false,
    ) );

    if(is_array($terms) && !empty($terms)){
        foreach($terms as $term){
            $term_id = $term->term_id;
            $html .= '<div class="mo-huati-item pix-moment-tag-item pix-moment-topic-item rounded" tagid="'.$term_id.'">
                        <div class="left pix-moment-tag-icon pix-moment-topic-icon"><i class="ri-hashtag"></i></div>
                        <div class="right pix-moment-tag-meta pix-moment-topic-meta">
                            <div class="title pix-moment-tag-title pix-moment-topic-title">'.$term->name.'</div>
                            <div class="count-mo pix-moment-tag-count pix-moment-topic-count">'.$term->count.'片刻</div>
                        </div>
                        </div>';
        }
    } else {
        $html = '<div class="empty-content"><img src="'.THEME_URL.'/img/empty.png"></div>';
    }

    return $search.'<div class="motags-content pix-moment-tag-list pix-moment-topic-list trans-scroll-bar">'.$html.'</div>';
    
} 

// 片刻发布 ajax
function pix_moment_content_length($content){
    $text = (string) $content;
    $text = preg_replace_callback('/\[link\s+([^\]]*)\]/i', function($matches){
        $title = '';
        if(preg_match('/\bt=(["\'])(.*?)\1/i', $matches[1], $title_match)){
            $title = html_entity_decode($title_match[2], ENT_QUOTES, 'UTF-8');
        }
        return $title;
    }, $text);
    $text = preg_replace('/\[s=[^\]]+\]/i', '表', $text);
    $text = wp_strip_all_tags($text, true);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = str_replace("\xC2\xA0", '', $text);
    return mb_strlen(trim($text));
}

function pix_forbidden_words_defaults(){
    return array('傻逼', '脑残', '去死', '滚蛋', '垃圾东西', '人渣', '操你', '草你', '妈的', '辱骂词', '违禁词', '敏感词');
}

function pix_forbidden_words_list(){
    if(!get_op('forbidden_words_enable', true)){
        return array();
    }
    $raw = get_op('forbidden_words_list', implode("\n", pix_forbidden_words_defaults()));
    if(is_array($raw)){
        $words = $raw;
    } else {
        $words = preg_split('/[\r\n,，]+/u', (string)$raw);
    }
    $words = array_map('trim', $words);
    $words = array_filter($words, function($word){
        return $word !== '';
    });
    return array_values(array_unique($words));
}

function pix_check_forbidden_words($content){
    $words = pix_forbidden_words_list();
    if(empty($words)){
        return false;
    }
    $text = wp_strip_all_tags((string)$content, true);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    foreach($words as $word){
        if(function_exists('mb_stripos')){
            $matched = mb_stripos($text, $word, 0, 'UTF-8') !== false;
        } else {
            $matched = stripos($text, $word) !== false;
        }
        if($matched){
            return $word;
        }
    }
    return false;
}

function pix_forbidden_words_message($word = ''){
    return $word ? '内容包含不适宜词语：'.$word : '内容包含不适宜词语，请修改后再提交';
}

function pix_moment_user_can_use_type($user_id, $catid, $moment_type){
    $map = array(
        'gallery' => 'gallery',
        'card'    => 'card',
        'audio'   => 'audio',
        'video'   => 'video',
        'file'    => 'file',
    );

    if($moment_type === 'text' || !isset($map[$moment_type])){
        return true;
    }

    $profile = pix_moment_get_permission_profile($user_id, $catid);
    if(is_wp_error($profile)){
        return false;
    }

    return !empty($profile['mo_user_power'][$map[$moment_type]]);
}

function pix_moment_validate_attachment_owner($attachment_id, $user_id, $mime_prefix = ''){
    $attachment_id = absint($attachment_id);
    if(!$attachment_id){
        return false;
    }

    $post = get_post($attachment_id);
    if(!$post || $post->post_type !== 'attachment'){
        return false;
    }

    if(!current_user_can('manage_options') && (int)$post->post_author !== (int)$user_id){
        return false;
    }

    if($mime_prefix && strpos((string)get_post_mime_type($attachment_id), $mime_prefix) !== 0){
        return false;
    }

    return true;
}

function pix_moment_validate_payload($moment_type, $moment_data, $user_id, $catid){
    if(!pix_moment_user_can_use_type($user_id, $catid, $moment_type)){
        return new WP_Error('moment_type_disabled', '当前圈子未开启该发布类型');
    }

    if($moment_type === 'text'){
        return array();
    }

    if(!is_array($moment_data) || empty($moment_data)){
        return new WP_Error('moment_data_empty', '请添加有效内容后再发布');
    }

    switch($moment_type){
        case 'gallery':
            $profile = pix_moment_get_permission_profile($user_id, $catid);
            if(is_wp_error($profile)){
                return $profile;
            }

            $limit = max(1, (int)$profile['gallery_num']);
            if(count($moment_data) > $limit){
                return new WP_Error('gallery_limit', '图片数量不能超过'.$limit.'张');
            }

            $clean = array();
            foreach($moment_data as $item){
                if(!is_array($item)){
                    return new WP_Error('gallery_invalid', '图片数据格式不正确');
                }

                $src = esc_url_raw($item['src'] ?? '');
                $thum = esc_url_raw($item['thum'] ?? $src);
                $attach_id = absint($item['attach_id'] ?? 0);

                if(!$src || !wp_http_validate_url($src)){
                    return new WP_Error('gallery_url_invalid', '图片地址不正确');
                }

                if($attach_id && !pix_moment_validate_attachment_owner($attach_id, $user_id, 'image/')){
                    return new WP_Error('gallery_attachment_invalid', '图片附件无效或无权使用');
                }

                if(!$attach_id && empty($profile['gallery_link'])){
                    return new WP_Error('gallery_external_forbidden', '当前圈子不允许使用外链图片');
                }

                $clean[] = array(
                    'src' => $src,
                    'thum' => $thum ?: $src,
                    'attach_id' => $attach_id,
                );
            }

            return $clean;

        case 'video':
            if(count($moment_data) !== 1 || !is_array($moment_data[0])){
                return new WP_Error('video_invalid', '一次只能发布一个视频');
            }

            $item = $moment_data[0];
            $type = sanitize_key($item['type'] ?? 'local');
            if(!in_array($type, array('local', 'bili'), true)){
                return new WP_Error('video_type_invalid', '视频类型不正确');
            }

            if($type === 'local'){
                $attach_id = absint($item['attach_id'] ?? 0);
                $cover_id = absint($item['cover'] ?? 0);
                if(!pix_moment_validate_attachment_owner($attach_id, $user_id, 'video/')){
                    return new WP_Error('video_attachment_invalid', '视频附件无效或无权使用');
                }
                if($cover_id && !pix_moment_validate_attachment_owner($cover_id, $user_id, 'image/')){
                    return new WP_Error('video_cover_invalid', '视频封面无效或无权使用');
                }

                $cover_url = esc_url_raw($item['cover_url'] ?? '');
                if($cover_url && !wp_http_validate_url($cover_url)){
                    $cover_url = '';
                }

                return array(array(
                    'attach_id' => $attach_id,
                    'cover' => $cover_id,
                    'cover_url' => $cover_url,
                    'type' => 'local',
                ));
            }

            $bvid = sanitize_text_field($item['bvid'] ?? '');
            if(!preg_match('/^BV[0-9A-Za-z]{8,20}$/', $bvid)){
                return new WP_Error('video_bvid_invalid', 'B站视频 BV 号不正确');
            }

            return array(array(
                'bvid' => $bvid,
                'title' => sanitize_text_field($item['title'] ?? ''),
                'cover' => esc_url_raw($item['cover'] ?? ''),
                'type' => 'bili',
            ));

        case 'file':
            $limit = max(1, (int)mo_file_num($catid));
            if(count($moment_data) > $limit){
                return new WP_Error('file_limit', '文件数量不能超过'.$limit.'个');
            }

            $clean = array();
            foreach($moment_data as $item){
                if(!is_array($item)){
                    return new WP_Error('file_invalid', '文件数据格式不正确');
                }

                $attach_id = absint($item['attach_id'] ?? 0);
                if(!pix_moment_validate_attachment_owner($attach_id, $user_id)){
                    return new WP_Error('file_attachment_invalid', '文件附件无效或无权使用');
                }

                $clean[] = array(
                    'attach_id' => $attach_id,
                    'file_title' => sanitize_text_field($item['file_title'] ?? get_the_title($attach_id)),
                );
            }

            return $clean;

        case 'card':
            $limit = get_term_meta($catid, 'card_num', true);
            $limit = $limit !== '' ? (int)$limit : (int)get_op('mo_card_num', 3);
            $limit = max(1, $limit);
            if(count($moment_data) > $limit){
                return new WP_Error('card_limit', '卡片数量不能超过'.$limit.'个');
            }

            $clean = array();
            foreach($moment_data as $post_id){
                $post_id = absint($post_id);
                $post = $post_id ? get_post($post_id) : false;
                if(!$post || $post->post_status !== 'publish'){
                    return new WP_Error('card_invalid', '卡片内容不存在或不可用');
                }
                $clean[] = $post_id;
            }

            return $clean;

        case 'audio':
            $item = $moment_data[0] ?? array();
            $aid = is_array($item) ? preg_replace('/\D+/', '', (string)($item['aid'] ?? '')) : '';
            if(!$aid){
                return new WP_Error('audio_invalid', '音乐 ID 不正确');
            }

            return array(array('aid' => $aid));
    }

    return new WP_Error('moment_type_invalid', '片刻类型不正确');
}

function push_moment(){

    if(!is_user_logged_in()){
		wp_send_json(array('status'=>0,'msg'=>'请登录'));
	}

    check_ajax_referer('moment_ajax', 'security');

    if(function_exists('pix_content_submission_guard')){
        $guard = pix_content_submission_guard('moment');
        if(!empty($guard['code'])){
            wp_send_json(array('status'=>0,'msg'=>$guard['msg']));
        }
    }

    $current_user = wp_get_current_user();	
	$user_id = $current_user->ID;
	//$max_length = '800';

    $content = isset( $_POST['content'] ) ? $_POST['content'] : '';
    $title = isset( $_POST['title'] ) ? $_POST['title'] : '';

    // 内容消毒：过滤 HTML 标签防止 XSS
    $content = wp_kses_post($content);
    $title = sanitize_text_field($title);
    $catid = isset( $_POST['catid'] ) ? (int)$_POST['catid'] : 0;
    $tagid = isset( $_POST['tagid'] ) ? (int)$_POST['tagid'] : 0;
    $moment_data = isset( $_POST['moment_data'] ) ? $_POST['moment_data'] : false;
    $moment_type = isset($_POST['moment_type']) ? sanitize_key($_POST['moment_type']) : 'text';
    $pid = isset($_POST['pid']) ? absint($_POST['pid']) : 0;
    $action_type = isset($_POST['action_type']) ? sanitize_key($_POST['action_type']) : 'publish';
    $allowed_types = array('text', 'gallery', 'card', 'audio', 'video', 'file');

    if(!in_array($moment_type, $allowed_types, true)){
        wp_send_json(array('status'=>0,'msg'=>'片刻类型不正确'));
    }

    $old_post = null;
    if($action_type === 'edit'){
        if(!$pid){
            wp_send_json(array('status'=>0,'msg'=>'片刻ID不能为空'));
        }

        $old_post = get_post($pid);
        if(!$old_post || $old_post->post_type !== 'moment'){
            wp_send_json(array('status'=>0,'msg'=>'片刻不存在'));
        }

        if(!moment_auth($pid, $user_id, false)){
            wp_send_json(array('status'=>0,'msg'=>'无权编辑此片刻'));
        }

        if(!$catid){
            $catid = (int)get_category_id_by_post_id($pid, 'moments');
        }
    }

    $term = $catid ? get_term($catid, 'moments') : false;
    if(!$catid || !$term || is_wp_error($term)){
        wp_send_json(array('status'=>0,'msg'=>'请选择有效圈子'));
    }

    if($tagid > 0){
        $tag = get_term($tagid, 'moment_tag');
        if(!$tag || is_wp_error($tag)){
            wp_send_json(array('status'=>0,'msg'=>'话题不存在'));
        }
    }

    // 判断是否需要审核：管理员或圈主发布不需要审核
    $is_admin = current_user_can('manage_options');
    $is_owner = $catid ? get_term_meta($catid, 'mo_owner', true) == $user_id : false;
    if($action_type !== 'edit' && function_exists('pix_user_is_plain_user') && pix_user_is_plain_user($user_id)){
        $allow_moment = function_exists('pix_normal_user_can') ? pix_normal_user_can('normal_user_allow_moment', true, $user_id) : true;
        if(!$allow_moment){
            wp_send_json(array('status'=>0,'msg'=>'普通用户暂不能发布片刻'));
        }
    }

    $user_join = get_user_meta($user_id, 'user_mo_joined', true);
    $joined_terms = array_filter(array_map('absint', explode(',', (string)$user_join)));
    if(!$is_admin && !$is_owner && !in_array($catid, $joined_terms, true)){
        wp_send_json(array('status'=>0,'msg'=>'请先加入该圈子后再发布'));
    }

    $need_review = !($is_admin || $is_owner);
    $post_status = $need_review ? 'pending' : 'publish';

    $forbidden_word = pix_check_forbidden_words($content);
    if($forbidden_word){
        wp_send_json(array('status'=>0,'msg'=>pix_forbidden_words_message($forbidden_word)));
    }

    $max_word = mo_word_max($catid);
    $max = $max_word['max'];
    $min = $max_word['min'];

    // 字数限制
    $content_length = pix_moment_content_length($content);
    if($content_length > $max){
        wp_send_json(array('status'=>0,'msg'=>'内容不能超过'.$max.'个字'));
    }

    if($content_length < $min){
        wp_send_json(array('status'=>0,'msg'=>'内容不能少于'.$min.'个字'));
    }

    $validated_data = pix_moment_validate_payload($moment_type, $moment_data, $user_id, $catid);
    if(is_wp_error($validated_data)){
        wp_send_json(array('status'=>0,'msg'=>$validated_data->get_error_message()));
    }
    $moment_data = $validated_data;

    $push_data = array();

    switch ($moment_type)
        {
        case 'gallery':
            $push_data['moment_ga'] = mo_gallery_data($moment_data);
            break;
        case 'card':
            $push_data['moment_card'] = $moment_data;
            break;
        case 'audio':
            $push_data['moment_audio'] = $moment_data;
            break;    
        case 'video':
            $push_data['moment_video'] = mo_video_data($moment_data);
            break;     
        case 'file':
            $push_data['moment_file'] = mo_file_data($moment_data);
            break;     
        }

        $push_data['moment_type'] = $moment_type;

        $insert_moment = array(
            'post_title' => $title,
            'post_type' => 'moment',
            'post_author' => $user_id, 
            'post_content' => $content,
            'post_status' => $post_status,
            'comment_status'=>'open',
            'tax_input' => array(
                'moments' => array($catid),
                'moment_tag' => array($tagid),
            ),
            'meta_input' => $push_data
        );

        if($action_type == 'publish'){
            $res = wp_insert_post( $insert_moment );
        } else {
            $insert_moment['ID'] = $pid;
            $insert_moment['post_author'] = $old_post->post_author;
            $res = wp_update_post( $insert_moment );
        }

        if($res && !is_wp_error($res)){
            if($catid > 0){
                wp_set_object_terms($res, (int)$catid, 'moments');
            }
            if($tagid > 0){
                wp_set_object_terms($res, (int)$tagid, 'moment_tag');
            }

            $html = '';
            if($post_status === 'publish'){
                $new_post = get_post($res);
                if($new_post){
                    global $post;
                    $old_post_global = $post;
                    $post = $new_post;
                    setup_postdata($post);
                    ob_start();
                    get_template_part( 'tpl/content','moment');
                    $html = ob_get_clean();
                    wp_reset_postdata();
                    $post = $old_post_global;
                }
            }

            $term_url = $catid ? get_term_link((int)$catid, 'moments') : '';
            if(is_wp_error($term_url)){
                $term_url = home_url('/');
            }
            $success_msg = $post_status === 'pending' ? '发布成功，等待审核' : '发布成功';
            if($action_type === 'edit'){
                $success_msg = $post_status === 'pending' ? '更新成功，等待审核' : '更新成功';
            }

            wp_send_json(array(
                'status' => 1,
                'msg' => $success_msg,
                'id' => $res,
                'post_status' => $post_status,
                'url' => get_permalink($res),
                'term_url' => $term_url,
                'html' => $html,
                'action_type' => $action_type,
            ));
        }

        $msg = is_wp_error($res) ? $res->get_error_message() : '发布失败，请稍后重试';
        wp_send_json(array('status'=>0,'msg'=>$msg));

}
add_action('wp_ajax_push_moment', 'push_moment');

// 片刻列表内容
function get_moment_type_content(){
    global $post;
    $output = '';
    $type = get_post_meta($post->ID,'moment_type',true);
    switch ($type)
        {
        case 'image':
        case 'gallery':
            $output = get_moment_gallery();
            break;
        case 'card':
            $output = get_m_card();
            break;
        case 'audio':
            $output = get_m_audio();
            break;  
        case 'video':
            $output = get_moment_video();
            break;       
        case 'file':
            $output = get_moment_file();
            break;     
        default:
            $output = '';
        }

        return $output;
}

// 前台音乐内容
function get_m_audio(){
    global $post;
    $pid = $post->ID;
    $data = get_post_meta($pid,'moment_audio',true);
    $html = '';
    if(is_array($data) && !empty($data)){
        $aid= $data[0]['aid'];
        
        $html = '<div class="mo-index-audio-wrap lazy"><div style="margin:-11px"><iframe frameborder="no" border="0" marginwidth="0" marginheight="0" width=330 height=86 src="//music.163.com/outchain/player?type=2&id='.$aid.'&auto=0&height=66" allowtransparency="true"></iframe></div></div>';
    }

    return $html;
}


// 图集数据
function mo_gallery_data($data){
    $arr = array();
    foreach($data as $v){
        array_push($arr, array(
            'thum' => $v['thum'],
            'src' => $v['src'],
            'attach_id' => isset($v['attach_id']) ? absint($v['attach_id']) : 0,
        ));
    }

    return $arr;
}

function pix_moment_attachment_size($attach_id) {
    $file = get_attached_file($attach_id);
    return ($file && file_exists($file)) ? filesize($file) : 0;
}

function pix_moment_edit_pix_items($moment_type, $moment_data) {
    $items = array();

    if($moment_type === 'gallery'){
        foreach((array)$moment_data as $item){
            $src = esc_url_raw($item['src'] ?? '');
            if(!$src){
                continue;
            }
            $attach_id = absint($item['attach_id'] ?? 0);
            if(!$attach_id){
                $attach_id = attachment_url_to_postid($src);
            }
            $title = $attach_id ? get_the_title($attach_id) : basename(parse_url($src, PHP_URL_PATH));
            $mime = $attach_id ? get_post_mime_type($attach_id) : 'image';
            $items[] = array(
                'id' => $attach_id ? $attach_id : md5($src),
                'attachment_id' => $attach_id,
                'kind' => 'image',
                'type' => 'image',
                'source' => $attach_id ? 'library' : 'external',
                'status' => 'done',
                'url' => $src,
                'thumb' => esc_url_raw($item['thum'] ?? $src),
                'preview' => esc_url_raw($item['thum'] ?? $src),
                'title' => $title,
                'name' => $title,
                'mime' => $mime,
                'size' => $attach_id ? pix_moment_attachment_size($attach_id) : 0,
            );
        }
    } elseif($moment_type === 'video' && !empty($moment_data[0]) && ($moment_data[0]['type'] ?? '') === 'bili'){
        $bvid = sanitize_text_field($moment_data[0]['bvid'] ?? '');
        if($bvid){
            $items[] = array(
                'id' => md5($bvid),
                'attachment_id' => 0,
                'kind' => 'video',
                'type' => 'video',
                'source' => 'bili',
                'status' => 'done',
                'bvid' => $bvid,
                'url' => '//player.bilibili.com/player.html?bvid=' . rawurlencode($bvid) . '&page=1',
                'title' => sanitize_text_field($moment_data[0]['title'] ?? ('B站视频 ' . $bvid)),
                'name' => sanitize_text_field($moment_data[0]['title'] ?? ('B站视频 ' . $bvid)),
                'thumb' => pix_bilibili_cover_proxy_url($bvid),
                'cover' => esc_url_raw($moment_data[0]['cover'] ?? ''),
                'mime' => 'bilibili',
                'size' => 0,
            );
        }
    } elseif($moment_type === 'video' && !empty($moment_data[0]) && ($moment_data[0]['type'] ?? '') === 'local'){
        $item = $moment_data[0];
        $attach_id = absint($item['video_id'] ?? 0);
        $poster_id = absint($item['att_id'] ?? 0);
        $url = $attach_id ? wp_get_attachment_url($attach_id) : esc_url_raw($item['url'] ?? '');
        if($url){
            $title = $attach_id ? get_the_title($attach_id) : basename(parse_url($url, PHP_URL_PATH));
            $poster = $poster_id ? wp_get_attachment_image_url($poster_id, 'medium') : esc_url_raw($item['cover'] ?? '');
            $items[] = array(
                'id' => $attach_id,
                'attachment_id' => $attach_id,
                'kind' => 'video',
                'type' => 'video',
                'source' => 'library',
                'status' => 'done',
                'url' => $url,
                'preview' => $url,
                'poster_id' => $poster_id,
                'poster' => $poster,
                'thumb' => $poster,
                'title' => $title,
                'name' => $title,
                'mime' => $attach_id ? get_post_mime_type($attach_id) : 'video',
                'size' => $attach_id ? pix_moment_attachment_size($attach_id) : 0,
            );
        }
    } elseif($moment_type === 'card'){
        foreach((array)$moment_data as $post_id){
            $card = get_card_data_arr($post_id);
            if(empty($card['pid'])){
                continue;
            }
            $items[] = array(
                'id' => absint($card['pid']),
                'attachment_id' => 0,
                'kind' => 'card',
                'type' => 'card',
                'source' => 'card',
                'status' => 'done',
                'pid' => absint($card['pid']),
                'url' => esc_url_raw($card['url'] ?? ''),
                'thumb' => esc_url_raw($card['image'] ?? ''),
                'preview' => esc_url_raw($card['image'] ?? ''),
                'title' => sanitize_text_field($card['title'] ?? '内容卡片'),
                'name' => sanitize_text_field($card['title'] ?? '内容卡片'),
                'desc' => sanitize_text_field($card['des'] ?? ''),
                'mime' => 'card',
                'size' => 0,
            );
        }
    } elseif($moment_type === 'file'){
        foreach((array)$moment_data as $item){
            $attach_id = absint($item['attach_id'] ?? 0);
            $url = $attach_id ? wp_get_attachment_url($attach_id) : esc_url_raw($item['url'] ?? '');
            if(!$attach_id || !$url){
                continue;
            }
            $title = sanitize_text_field($item['file_title'] ?? get_the_title($attach_id));
            $items[] = array(
                'id' => $attach_id,
                'attachment_id' => $attach_id,
                'kind' => 'file',
                'type' => 'file',
                'source' => 'library',
                'status' => 'done',
                'url' => $url,
                'preview' => $url,
                'title' => $title,
                'name' => $title,
                'mime' => get_post_mime_type($attach_id),
                'size' => pix_moment_attachment_size($attach_id),
            );
        }
    }

    return $items;
}

// 视频数据
function mo_video_data($data){
    $arr = array();
    $attach_id = isset($data[0]['attach_id']) ? absint($data[0]['attach_id']) : 0;
    $url = $attach_id ? wp_get_attachment_url( $attach_id ) : '';
    if(!$url && !empty($data[0]['url'])){
        $url = esc_url_raw($data[0]['url']);
    }
    $cover_id = isset($data[0]['cover']) ? absint($data[0]['cover']) : 0;
    $cover_data = $cover_id ? wp_get_attachment_image_src($cover_id, 'full') : false;
    $cover = $cover_data ? $cover_data[0] : ($data[0]['cover_url'] ?? '');

    $type = $data[0]['type'];

    if($type == 'local'){
        $video_data = array(
            'url' => $url,
            'cover' => $cover,
            'att_id' => $cover_id,
            'video_id' => $attach_id,
            'type' => $data[0]['type'],
        );
    } else {
        $video_data = array(
            'bvid' => $data[0]['bvid'],
            'title' => sanitize_text_field($data[0]['title'] ?? ''),
            'cover' => esc_url_raw($data[0]['cover'] ?? ''),
            'type' => $data[0]['type'],
        );
    }
    array_push($arr, $video_data);

    return $arr;
}

// 文件数据
function mo_file_data($data){
    $arr = array();
    
    foreach ($data as $key => $v) {
        $url = wp_get_attachment_url( $v['attach_id'] );
        array_push($arr, array(
            'url' => $url,
            'attach_id' => $v['attach_id'],
            'file_title' => $v['file_title'],
        ));
    }
    
    
    return $arr;
}

// 前台获取图集
function get_moment_gallery(){
    global $post;
    $pid = $post->ID;
    $lists = get_post_meta($pid,'moment_ga',true);
    $html = '';
    if(!empty($lists) && is_array($lists)){
        $count = count($lists);
        $gallery_classes = array('img_list', 'img-count-'.$count);
        if($count === 1){
            $gallery_classes[] = 'is-single';
        } elseif($count > 9){
            $gallery_classes[] = 'is-over-9';
        }

        foreach($lists as $index => $list){
            $src = $list['src'];
            $thum = $list['thum'];
            $item_classes = array('fancybox', 'mo_img');
            $img_html = '<img class="lazy" data-src="'.esc_url($thum).'" alt="片刻图片">';
            if($index >= 9){
                $item_classes[] = 'is-hidden-gallery-item';
                $img_html = '';
            }
            $more_html = '';
            if($index === 8 && $count > 9){
                $more_html = '<span class="mo-img-more">+'.esc_html($count - 9).'</span>';
            }
            $html .= '<a class="'.esc_attr(implode(' ', $item_classes)).'" href="'.esc_url($src).'" data-fancybox="post-images-'.$pid.'" data-type="image">'.$img_html.$more_html.'</a>';
        }
    
        return '<div class="'.esc_attr(implode(' ', $gallery_classes)).'">
                    <div class="list_inner">'.$html.'</div>
                </div>';
    }

}

// 获取B站视频封面
function get_bilibili_info($bvid){
    $bvid = sanitize_text_field($bvid);
    if(!preg_match('/^BV[0-9A-Za-z]{8,20}$/', $bvid)){
        return array();
    }

    $cache_key = 'pix_bili_info_' . md5($bvid);
    $cached = get_transient($cache_key);
    if(is_array($cached)){
        $cached['pic'] = !empty($cached['pic']) ? pix_normalize_bilibili_pic_url($cached['pic']) : '';
        return $cached;
    }

    $url = 'https://api.bilibili.com/x/web-interface/view?bvid=' . rawurlencode($bvid);
    $res = wp_remote_get($url, array('timeout' => 5));
    if(is_wp_error($res)){
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($res), true);
    if(empty($data['data']) || !is_array($data['data'])){
        return array();
    }

    $info = array(
        'bvid' => $bvid,
        'title' => sanitize_text_field($data['data']['title'] ?? ''),
        'pic' => !empty($data['data']['pic']) ? pix_normalize_bilibili_pic_url($data['data']['pic']) : '',
    );
    set_transient($cache_key, $info, DAY_IN_SECONDS);
    return $info;
}

function get_bilibili_pic($bvid){
    $info = get_bilibili_info($bvid);
    return !empty($info['pic']) ? $info['pic'] : '';
}

function pix_ajax_bili_video_info(){
    if (!check_ajax_referer('pix_upload_action', 'nonce', false)) {
        wp_send_json(array('status' => 0, 'msg' => '安全验证失败'));
    }
    if(!is_user_logged_in()){
        wp_send_json(array('status' => 0, 'msg' => '请先登录'));
    }
    $bvid = sanitize_text_field($_POST['bvid'] ?? '');
    $info = get_bilibili_info($bvid);
    if(empty($info)){
        wp_send_json(array('status' => 0, 'msg' => 'B站视频信息获取失败，已使用 BV 号兜底'));
    }
    $info['cover'] = $info['pic'];
    $info['pic'] = pix_bilibili_cover_proxy_url($bvid);
    wp_send_json(array('status' => 1, 'data' => $info));
}
add_action('wp_ajax_pix_bili_video_info', 'pix_ajax_bili_video_info');

function pix_normalize_bilibili_pic_url($url){
    $url = esc_url_raw($url);
    if(!$url){
        return '';
    }

    if(strpos($url, '//') === 0){
        $url = 'https:' . $url;
    }

    if(stripos($url, 'http://') === 0){
        $url = 'https://' . substr($url, 7);
    }

    return esc_url_raw($url);
}

function pix_bilibili_cover_proxy_url($bvid){
    $bvid = sanitize_text_field($bvid);
    if(!preg_match('/^BV[0-9A-Za-z]{8,20}$/', $bvid)){
        return '';
    }
    return add_query_arg(array(
        'action' => 'pix_bili_cover',
        'bvid' => rawurlencode($bvid),
    ), admin_url('admin-ajax.php'));
}

function pix_send_bilibili_cover($body, $content_type){
    if(headers_sent()){
        echo $body;
        exit;
    }
    status_header(200);
    header('Content-Type: '.$content_type);
    header('Cache-Control: public, max-age=86400');
    header('X-Content-Type-Options: nosniff');
    echo $body;
    exit;
}

function pix_ajax_bili_cover(){
    $bvid = sanitize_text_field($_GET['bvid'] ?? '');
    if(!preg_match('/^BV[0-9A-Za-z]{8,20}$/', $bvid)){
        status_header(404);
        exit;
    }

    $cache_key = 'pix_bili_cover_' . md5($bvid);
    $cached = get_transient($cache_key);
    if(is_array($cached) && !empty($cached['body']) && !empty($cached['type'])){
        pix_send_bilibili_cover(base64_decode($cached['body']), $cached['type']);
    }

    $pic = get_bilibili_pic($bvid);
    if(!$pic){
        status_header(404);
        exit;
    }

    $res = wp_remote_get($pic, array(
        'timeout' => 8,
        'redirection' => 3,
        'headers' => array(
            'Referer' => 'https://www.bilibili.com/',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125 Safari/537.36',
            'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        ),
    ));
    if(is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200){
        status_header(404);
        exit;
    }

    $content_type = wp_remote_retrieve_header($res, 'content-type');
    $content_type = is_array($content_type) ? reset($content_type) : $content_type;
    $content_type = $content_type ? strtolower(trim(explode(';', $content_type)[0])) : 'image/jpeg';
    if(strpos($content_type, 'image/') !== 0){
        status_header(404);
        exit;
    }

    $body = wp_remote_retrieve_body($res);
    if(!$body){
        status_header(404);
        exit;
    }

    set_transient($cache_key, array(
        'type' => $content_type,
        'body' => base64_encode($body),
    ), DAY_IN_SECONDS);
    pix_send_bilibili_cover($body, $content_type);
}
add_action('wp_ajax_pix_bili_cover', 'pix_ajax_bili_cover');
add_action('wp_ajax_nopriv_pix_bili_cover', 'pix_ajax_bili_cover');

// 前台获取视频
function get_moment_video(){
    global $post;
    $pid = $post->ID;
    $data = get_post_meta($pid,'moment_video',true);
    $html = '';
    if(is_array($data) && !empty($data)){
          
        $type = $data[0]['type'];
        if($type == 'bili'){
            $bvid = sanitize_text_field($data[0]['bvid']);
            $btitle = sanitize_text_field($data[0]['title'] ?? ('B站视频 '.$bvid));
            $bpic = pix_bilibili_cover_proxy_url($bvid);
            $cover_html = $bpic ? '<img class="lazy" data-src="'.esc_url($bpic).'" referrerpolicy="no-referrer" alt="'.esc_attr($btitle).'">' : '<span>'.esc_html($btitle ?: '点击播放 B站视频').'</span>';
            $html = '<div class="pix_local_player'.($bpic ? '' : ' pix_local_video_fallback').'"><a class="mo-fancy-video'.($bpic ? '' : ' no-cover').'" href="//player.bilibili.com/player.html?bvid='.esc_attr($bvid).'&page=1" data-fancybox="moment-video-'.$pid.'" data-type="iframe" data-width="1920" data-height="1080"><i class="ri-play-mini-line pix-mvideo-btn"></i>'.$cover_html.'</a></div>';
            //$html = '<div class="pix_bili_player"><iframe src="//player.bilibili.com/player.html?bvid='.$bvid.'&page=1" scrolling="no" border="0" frameborder="no" framespacing="0" allowfullscreen="true" > </iframe></div>';
        } else {
            $url = $data[0]['url'];
            $att_id = isset($data[0]['att_id']) ? $data[0]['att_id'] : '';
            $cover = isset($data[0]['cover']) ? $data[0]['cover'] : "";
            if($att_id){
                $cover = wp_get_attachment_image_src($att_id, 'full')[0];
                //$cover = 'background-image:url('.$cover.')';
            }
            $has_valid_cover = $cover && preg_match('#^(https?:)?//#i', $cover);
            if($has_valid_cover){
                $html = '<div class="pix_local_player">   
                            <a class="mo-fancy-video" href="'.esc_url($url).'" data-fancybox="moment-video-'.$pid.'"><i class="ri-play-mini-line pix-mvideo-btn"></i><img class="lazy" data-src="'.esc_url($cover).'" alt="视频封面"></a>
                        </div>';
            } else {
                $html = '<div class="pix_local_player pix_local_video_fallback">
                            <a class="mo-fancy-video no-cover" href="'.esc_url($url).'" data-fancybox="moment-video-'.$pid.'"><i class="ri-play-mini-line pix-mvideo-btn"></i><span>点击播放视频</span></a>
                        </div>';
            }
        } 

    return '<div class="video_list">
                <div class="list_inner">'.$html.'</div>
            </div>';
}

}

// 前台获取文件下载
function get_moment_file(){
    global $post;
    $pid = $post->ID;
    $data = get_post_meta($pid,'moment_file',true);
    $html = '';

    if(is_array($data) && !empty($data)){

        foreach($data as $v) {
            $url = $v['url'];
            $title = $v['file_title'];
            $extension = pathinfo($url, PATHINFO_EXTENSION);

            $file_data = wp_get_attachment_metadata($v['attach_id']);
            $file_size = $file_data['filesize'];
            $html .= '<div class="moment-file-down-box">
                        <div class="left">
                            <span>'.$extension.'</span>
                            <div class="info"><div class="title">'.$title.'</div><div class="size">'.size_format($file_size).'</div></div>
                        </div>
                        <div class="right"><a href="'.$url.'" download="'.$title.'" class="mo-file-down-link"><i class="ri-download-line"></i>下载</a></div>
                    </div>';
        }

        return $html;
    
    }
}

add_action('wp_ajax_nopriv_cls_load_moments', 'cls_load_moments');
add_action('wp_ajax_cls_load_moments', 'cls_load_moments');

//随机图片名
function pix_generate_random_code($length=10) {
 
    $string = '';
    $characters = "23456789ABCDEFHJKLMNPRTVWXYZabcdefghijklmnopqrstuvwxyz";
  
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters)-1)];
    }
  
    return $string;
  
 }

// 删除片刻
add_action('wp_ajax_moment_delete', 'moment_delete');
function moment_delete(){
    if(!is_user_logged_in()){
        wp_send_json(array('code'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
    if (!$pid) {
        wp_send_json(array('code'=>0,'msg'=>'片刻ID不能为空'));
    }

    $moment = get_post($pid);
    if (!$moment || $moment->post_type !== 'moment') {
        wp_send_json(array('code'=>0,'msg'=>'片刻不存在'));
    }

    $current_user_id = get_current_user_id();

    $is_admin = current_user_can('manage_options');
    $term_id = get_category_id_by_post_id($pid, 'moments');
    $is_owner = $term_id ? get_term_meta($term_id, 'mo_owner', true) == $current_user_id : false;

    if ($moment->post_author != $current_user_id && !$is_admin && !$is_owner) {
        wp_send_json(array('code'=>0,'msg'=>'无权删除此片刻'));
    }

    $res = wp_trash_post($pid);
    if($res){
        wp_send_json(array('code'=>1,'msg'=>'删除成功'));
    } else {
        wp_send_json(array('code'=>0,'msg'=>'删除失败'));
    }
}

// 片刻加精  需要判定管理员或版主权限
add_action('wp_ajax_moment_hot', 'moment_hot');
function moment_hot(){
    if(!is_user_logged_in()){
        wp_send_json(array('state'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $pid = isset($_POST['pid']) ? absint($_POST['pid']) : 0;
    $type =  isset( $_POST['state'] ) ? sanitize_key($_POST['state']) : '';
    $moment = $pid ? get_post($pid) : false;
    if(!$moment || $moment->post_type !== 'moment'){
        wp_send_json(array('state'=>0,'msg'=>'片刻不存在'));
    }

    $term_id = get_category_id_by_post_id($pid, 'moments');
    $is_owner = $term_id ? (int) get_term_meta($term_id, 'mo_owner', true) === get_current_user_id() : false;
    if (!current_user_can('manage_options') && !$is_owner) {
        wp_send_json(array('state'=>0,'msg'=>'无权操作'));
    }

    if($type == 'hot'){
        update_post_meta( $pid, 'moment_hot', '1' );
        $succ = array('state'=>'1','msg' => '加精成功','type'=>'hot');
    } else {
        update_post_meta( $pid, 'moment_hot', '0' );
        $succ = array('state'=>'0','msg' => '取消加精','type'=>'unhot');
    }

    wp_send_json($succ);
}


// 置顶片刻
add_action('wp_ajax_moment_top', 'moment_top');
function moment_top(){
    if(!is_user_logged_in()){
        wp_send_json(array('state'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $pid = isset($_POST['pid']) ? absint($_POST['pid']) : 0;
    $type =  isset( $_POST['state'] ) ? sanitize_key($_POST['state']) : '';
    $moment = $pid ? get_post($pid) : false;
    if(!$moment || $moment->post_type !== 'moment'){
        wp_send_json(array('state'=>0,'msg'=>'片刻不存在'));
    }

    $term_id = get_category_id_by_post_id($pid, 'moments');
    $is_owner = $term_id ? (int) get_term_meta($term_id, 'mo_owner', true) === get_current_user_id() : false;
    if (!current_user_can('manage_options') && !$is_owner) {
        wp_send_json(array('state'=>0,'msg'=>'无权操作'));
    }

    if($type == 'stick'){
        stick_post( $pid );
        $succ = array('state'=>'1','msg' => '置顶成功','type'=>'stick');
    } else {
        unstick_post( $pid );
        $succ = array('state'=>'0','msg' => '取消置顶','type'=>'unstick');
    }

    wp_send_json($succ);
}

// 获取置顶文章列表
function moment_sticky_loop(){
    $posts_html = '';
    $sticky = array_values(array_filter(array_map('absint', (array) get_option('sticky_posts'))));
	$args = array(
		'post_type' => 'moment', 
        'post__in' => $sticky,
        'posts_per_page' => 7,       
	);


    if(!empty($sticky)){
        $stick_query = new WP_Query($args);
        if( $stick_query->have_posts() ) {
            ob_start();
            while ($stick_query->have_posts()) : $stick_query->the_post();
            get_template_part( 'tpl/content','moment');
        endwhile; 
        $posts_html = ob_get_contents(); // we pass the posts to variable
   		ob_end_clean(); // clear the buffer
        wp_reset_postdata(); 
        }
    }

    return $posts_html;
 
}

//置顶样式
function cpt_sticky_class($classes) {
    if ( is_sticky() ) : 
    $classes[] = 'sticky';
    return $classes;
endif; 
return $classes;
        }
add_filter('post_class', 'cpt_sticky_class');

//支持置顶
add_action( 'add_meta_boxes', 'pix_add_moment_box' );
function pix_add_moment_box(){
add_meta_box( 'pix_moment_sticky', '置顶片刻', 'pix_moment_sticky', 'moment', 'side', 'high' );
}
function pix_moment_sticky(){ ?>
 <input id="super-sticky" name="sticky" type="checkbox" value="sticky" <?php checked( is_sticky() ); ?> /><label for="super-sticky" class="selectit">置顶片刻</label>
 <?php }


// 获取片刻话题|标签
function get_moment_tag_link($pid){
    $type = 'moment_tag';
    $terms = get_the_terms( $pid, $type ); 
    if ( $terms && ! is_wp_error( $terms ) ) {
        // 循环输出分类
        $term = $terms[0];
        $link = get_term_link( $term->term_id, $type );
        return '<a href="'.$link.'" class="mo-huati-link"><i class="ri-hashtag"></i>'.$term->name.'</a>';
        
    } else {
        return '';
    }
}

// 片刻卡片
function get_card_by_url($data){
    $data = is_string($data) ? trim($data) : $data;
    $preg = "/^http(s)?:\\/\\/.+/";
    if ( is_string($data) && preg_match($preg, $data) ) {
        $pid = url_to_postid($data);
    } else {
        $pid = absint($data);
    }

    if(!$pid){
        return false;
    }

    $link = get_permalink($pid);
    $title = get_the_title($pid);

    if($link && $title){
        $type = get_post_type($pid);
        if(!in_array($type, array('post', 'page', 'moment'), true)){
            return false;
        }
        $meta = array();
        $meta['title'] = $title;
        $meta['url'] = $link;
        $meta['des'] = get_the_excerpt($pid);
        $meta['pid'] = $pid;
        $meta_image_url = get_ppo_thum( $pid, 'medium','random');
        if ( $meta_image_url ) {
            $meta['image'] = $meta_image_url;
        }
    
        if($type == 'moment'){
            $meta['title'] = '片刻';
            $moment_img = THEME_URL.'/img/modef.png';
                $lists = get_post_meta($pid,'moment_ga',true);
                if(is_array($lists) && !empty($lists)){
                    $moment_img = $lists[0]['thum'];
                }
                $meta['image'] = $moment_img;
        }

        return $meta;
    } else {
        return false;
    }
}

// ajax获取卡片信息
function get_card_data($data){
    $card = get_card_by_url($data);
    $html = '';
    if($card){
       $html = '<div class="card-box mo-card-item" pid="'.$card['pid'].'">
                    <div class="card-img"><img class="post-thum lazy" data-src="'.$card['image'].'" alt=""></div>
                    <div class="card-info"><div class="title">'.$card['title'].'</div><div class="des">'.$card['des'].'</div></div>
                    <a class="card-url" target="_blank" href="'.$card['url'].'"></a>
                    <span class="de_card"><i class="ri-close-line"></i></span>
                </div>';
        return $html;        
    } else {
        return false;
    }
 }

// 获取单卡片信息数组
function get_card_data_arr($data){
    $arr = array();
    $card = is_numeric($data) ? get_card_by_url(get_permalink(absint($data))) : get_card_by_url($data);
    if($card){
        $arr['title'] = $card['title'];
        $arr['url'] = $card['url'];
        $arr['des'] = $card['des'];
        $arr['pid'] = $card['pid'];
        $arr['image'] = $card['image'];
    }
    return $arr;
} 

// 获取多卡片信息数组
function get_cards_data_arr($data){
    $arr = array();
    foreach($data as $index => $pid){
        $arr[$index] = get_card_data_arr($pid);
    }
    return $arr;
}


// 前端卡片显示
function get_m_card(){
    global $post;
    $pid = $post->ID;
    $lists = get_post_meta($pid,'moment_card',true);
    $html = '';
    if(!empty($lists) && is_array($lists)){
        foreach($lists as $index => $list){
            $html .=get_card_data($list);
        }
    
        return '<div class="card_list">
                    <div class="list_inner">'.$html.'</div>
                </div>';
    }
}

 function ajax_get_card_data(){
    check_ajax_referer('moment_ajax', 'security');

    $data = isset($_POST['url']) ? trim((string) wp_unslash($_POST['url'])) : '';
    if(!$data){
        wp_send_json(array('status'=>0,'msg'=>'请先粘贴内容链接'));
    }

    if(!wp_http_validate_url($data) && !ctype_digit($data)){
        wp_send_json(array('status'=>0,'msg'=>'请输入本站文章、页面或片刻链接'));
    }

    $data = wp_http_validate_url($data) ? esc_url_raw($data) : absint($data);
    $card = get_card_data($data);
    $card_data = get_card_data_arr($data);
    if($card && !empty($card_data['pid'])){
        wp_send_json(array('status'=>1,'html'=>$card,'card'=>$card_data));
    } else {
        wp_send_json(array('status'=>0,'msg'=>'未找到可生成卡片的本站内容'));
    }

 }
//add_action('wp_ajax_nopriv_ajax_get_card_data', 'ajax_get_card_data');
add_action('wp_ajax_ajax_get_card_data', 'ajax_get_card_data');


// 片刻内容过滤
function pix_moment_allowed_html(){
    $allowed = wp_kses_allowed_html('post');
    unset($allowed['code'], $allowed['pre'], $allowed['em']);
    $allowed['span'] = array('class' => true);
    $allowed['strong'] = array();
    $allowed['b'] = array();
    $allowed['em'] = array();
    $allowed['i'] = array('class' => true);
    $allowed['u'] = array();
    $allowed['s'] = array();
    $allowed['del'] = array();
    $allowed['img'] = array(
        'class' => true,
        'src' => true,
        'alt' => true,
        'width' => true,
        'height' => true,
        'loading' => true,
        'decoding' => true,
    );
    $allowed['a']['class'] = true;
    $allowed['a']['target'] = true;
    $allowed['a']['rel'] = true;
    $allowed['a']['data-fancybox'] = true;
    $allowed['a']['data-type'] = true;
    return $allowed;
}

function pix_moment_sanitize_richtext_classes($content){
    $allowed_classes = array(
        'mo-inner-link',
        'ri-link',
        'wp-smiley',
        'pix-editor-topic',
        'pix-editor-link',
    );

    return preg_replace_callback('/\sclass=("|\')(.*?)\1/i', function($matches) use ($allowed_classes){
        $classes = preg_split('/\s+/', trim($matches[2]));
        $classes = array_values(array_intersect($classes, $allowed_classes));
        return $classes ? ' class="'.esc_attr(implode(' ', $classes)).'"' : '';
    }, $content);
}

function pix_moment_render_link_token($matches){
    $title = isset($matches[1]) ? wp_strip_all_tags($matches[1]) : '';
    $url = isset($matches[2]) ? esc_url_raw($matches[2]) : '';
    if(!$url || !wp_http_validate_url($url)){
        return esc_html($title);
    }

    return '<a class="mo-inner-link" href="'.esc_url($url).'" target="_blank" rel="nofollow noopener noreferrer"><i class="ri-link"></i> '.esc_html($title ?: $url).'</a>';
}

function pix_moment_render_emoji_token($matches){
    $name = isset($matches[1]) ? sanitize_file_name($matches[1]) : '';
    if(!$name || !preg_match('/^[a-zA-Z0-9_-]+$/', $name)){
        return '';
    }

    return '<img class="wp-smiley" src="'.esc_url(THEME_URL.'/img/emoji/'.$name.'.png').'" alt="'.esc_attr('emoji['.$name.']').'">';
}

function ppo_moment_filter(){
    global $post;
    if(!$post){
        return '';
    }

    $content = (string)$post->post_content;

    $content = convert_smilies($content);
    
    //$content = preg_replace('/\[img=(.*?)\]/','<a href="$1" class="fancy-box" data-fancybox="comment-img-'.$comment_id.'"><img src="$1"></a>', $content);
    //$content = preg_replace('/\[d\](.*?)\[\/d\]/', '<span class="comt-img-wrap">$1</span>', $content);
    $content = preg_replace_callback('/\[link t="(.*?)" u="(.*?)"\]/', 'pix_moment_render_link_token', $content);
    $content = preg_replace_callback('/\[s=(.*?)\]/', 'pix_moment_render_emoji_token', $content);

    //$content = str_replace('[code]', '<pre class="prettyprint linenums"><code>', $content);
    //$content = str_replace('[/code]', '</code></pre>', $content);
    $content = pix_moment_sanitize_richtext_classes($content);
    $content = wp_kses($content, pix_moment_allowed_html());
    return ltrim($content);
}

//ajax加载话题评论
function load_moment_comment(){
    check_ajax_referer('moment_ajax', 'security');

    global $post,$wp_query, $wp_rewrite;
    $pid = isset($_POST['pid']) ? absint($_POST['pid']) : 0;
    $moment = $pid ? get_post($pid) : false;
    if(!$moment || $moment->post_type !== 'moment'){
        wp_die();
    }

    $current_user = wp_get_current_user();
	$user_id = $current_user->ID;
    $page_data = function_exists('pix_comment_page_data') ? pix_comment_page_data($pid, 1) : array('comments' => array(), 'pages' => 1, 'page' => 1);
    $comments = $page_data['comments'];
    $pages = $page_data['pages'];
    $pageid = $page_data['page'];
    $wp_query->is_singular = true;
    $baseLink = '';
    if ($wp_rewrite->using_permalinks()) {
        $baseLink = '&base=' . user_trailingslashit(get_permalink($pid) . 'comment-page-%#%', 'commentpaged');
    }

    echo '<ul class="comment-list pix-moment-comment-list pix-moment-comment-list-enter">';
        if($comments){
            echo function_exists('pix_render_comment_items') ? pix_render_comment_items($comments) : '';
        } else {
            echo '<li class="nodata"><i class="ri-ghost-line"></i>空空如也！</li>';
        }
     
    echo '</ul>';
    if(function_exists('pix_render_comment_nav')){
        echo pix_render_comment_nav($pid, $pages, $pageid);
    }

    die();
}
add_action('wp_ajax_nopriv_load_moment_comment', 'load_moment_comment');
add_action('wp_ajax_load_moment_comment', 'load_moment_comment');

// 片刻分类筛选
function moment_cat_filter(){
    global $wp_query;
    $term_id = get_queried_object()->term_id ?? 0;


        $html = '<a class="filter-item pix-moment-filter-item active" type="new">最新</a>
                <a class="filter-item pix-moment-filter-item" type="ofi">官方</a>
                <a class="filter-item pix-moment-filter-item" type="hot">精华</a>
                <a class="filter-item pix-moment-filter-item" type="image">图片</a>
                <a class="filter-item pix-moment-filter-item" type="video">视频</a>
                <a class="filter-item pix-moment-filter-item" type="file">文件</a>
                <a class="filter-item pix-moment-filter-item" type="card">卡片</a>';
    
        if($term_id > 0
            && pix_moment_term_view_mode($term_id) === 'private'
            && !check_mo_joined($term_id)){
            return;
        }

            return '<div class="filter-inner pix-moment-filter-inner" catid="'.$term_id.'">'.$html.'</div>';

}

// 片刻筛选条件
function moment_filter_arr($type,$params,$owner){
    switch ($type) {
        case 'new':
            $params['order'] = 'DESC';
            break;
        
        case 'ofi':
            $params['author'] = $owner;
            break;

        case 'hot':
            $params['meta_key'] = 'moment_hot';
            $params['meta_value'] = 1;
            break;   

        case 'image':
            $params['meta_key'] = 'moment_type';
            $params['meta_value'] = array('gallery','image');
            $params['meta_compare'] = 'IN';
            break;    

        case 'video':
            $params['meta_key'] = 'moment_type';
            $params['meta_value'] = 'video';
            break;  
        
        case 'file':
            $params['meta_key'] = 'moment_type';
            $params['meta_value'] = 'file';
            break; 

        case 'card':
            $params['meta_key'] = 'moment_type';
            $params['meta_value'] = 'card';
            break; 
            
    }

    return $params;
}

function pix_moment_filter_cache_key($params, $user_id) {
    $version = (string) get_option('pix_moment_filter_cache_version', '1');
    return 'pix_moment_filter_' . md5($version . '|' . absint($user_id) . '|' . wp_json_encode($params));
}

function pix_moment_bump_filter_cache_version($post_id = 0) {
    if($post_id && get_post_type($post_id) !== 'moment'){
        return;
    }
    update_option('pix_moment_filter_cache_version', (string) microtime(true), false);
}
add_action('save_post_moment', 'pix_moment_bump_filter_cache_version');
add_action('trashed_post', 'pix_moment_bump_filter_cache_version');
add_action('untrashed_post', 'pix_moment_bump_filter_cache_version');
add_action('deleted_post', 'pix_moment_bump_filter_cache_version');

function pix_moment_query_cached_ids($params, $user_id, $ttl = 45) {
    $cache_key = pix_moment_filter_cache_key($params, $user_id);
    $cached = get_transient($cache_key);
    if (is_array($cached) && isset($cached['ids'], $cached['max_num_pages'], $cached['found_posts'])) {
        return $cached;
    }

    $id_params = $params;
    $id_params['fields'] = 'ids';
    $id_params['update_post_meta_cache'] = false;
    $id_params['update_post_term_cache'] = false;

    $query = new WP_Query($id_params);
    $data = array(
        'ids' => array_values(array_map('absint', $query->posts)),
        'max_num_pages' => (int) $query->max_num_pages,
        'found_posts' => (int) $query->found_posts,
    );

    set_transient($cache_key, $data, max(10, absint($ttl)));
    return $data;
}

function pix_moment_render_ids($ids, $empty_html = true) {
    $ids = array_values(array_filter(array_map('absint', (array) $ids)));
    if (empty($ids)) {
        return $empty_html ? '<div class="no-moment"><img src="'.THEME_URL.'/img/empty.png"></div>' : '';
    }

    $query = new WP_Query(array(
        'post_type' => 'moment',
        'post_status' => 'publish',
        'post__in' => $ids,
        'orderby' => 'post__in',
        'posts_per_page' => count($ids),
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    ));

    if(!$query->have_posts()){
        return $empty_html ? '<div class="no-moment"><img src="'.THEME_URL.'/img/empty.png"></div>' : '';
    }

    ob_start();
    while($query->have_posts()){
        $query->the_post();
        get_template_part( 'tpl/content' ,'moment');
    }
    wp_reset_postdata();

    return ob_get_clean();
}

function pix_moment_term_view_mode($term_id){
    $term_id = absint($term_id);
    if(!$term_id){
        return 'show';
    }

    $mode = get_term_meta($term_id, 'mo_show_type', true);
    if($mode === 'open'){
        $mode = 'show';
    }

    return in_array($mode, array('show', 'join', 'private'), true) ? $mode : 'show';
}

function pix_moment_term_preview_count($term_id){
    return max(0, absint(get_term_meta(absint($term_id), 'show_num', true)));
}

function pix_moment_user_circle_subscription_active($user_id, $term_id){
    $user_id = absint($user_id);
    $term_id = absint($term_id);
    if(!$user_id || !$term_id){
        return false;
    }

    if(current_user_can('manage_options') || (int)get_term_meta($term_id, 'mo_owner', true) === $user_id){
        return true;
    }

    $join_type = get_term_meta($term_id, 'mo_join_type', true);
    // 支付功能已移除：付费圈子按普通圈子处理，不限制访问
    return true;
}

function pix_moment_user_joined_term($user_id, $term_id){
    $user_id = absint($user_id);
    $term_id = absint($term_id);
    if(!$user_id || !$term_id){
        return false;
    }

    if(current_user_can('manage_options') || (int)get_term_meta($term_id, 'mo_owner', true) === $user_id){
        return true;
    }

    if(!pix_moment_user_circle_subscription_active($user_id, $term_id)){
        return false;
    }

    $user_join = get_user_meta($user_id, 'user_mo_joined', true);
    $joined_terms = array_filter(array_map('absint', explode(',', (string)$user_join)));
    return in_array($term_id, $joined_terms, true);
}

function pix_moment_user_can_view_all_term($user_id, $term_id){
    return pix_moment_term_view_mode($term_id) === 'show'
        || pix_moment_user_joined_term($user_id, $term_id);
}

function pix_moment_apply_term_view_limit(&$params, $term_id, $user_id = null){
    $term_id = absint($term_id);
    $user_id = $user_id ? absint($user_id) : get_current_user_id();

    if(!$term_id || pix_moment_user_can_view_all_term($user_id, $term_id)){
        return 'open';
    }

    if(pix_moment_term_view_mode($term_id) === 'join'){
        $show_num = pix_moment_term_preview_count($term_id);
        if($show_num > 0){
            $params['posts_per_page'] = $show_num;
            $params['paged'] = 1;
            $params['no_found_rows'] = true;
            return 'preview';
        }
    }

    $params['post__in'] = array(0);
    $params['posts_per_page'] = 1;
    $params['paged'] = 1;
    $params['no_found_rows'] = true;
    return 'blocked';
}

// 片刻类型筛选
function moment_type_filter(){
    check_ajax_referer('moment_ajax', 'security');

    global $wp_query;
    $sticky = get_option('sticky_posts');
    $sticky_posts = '';
    $user_id = get_current_user_id();
    $cat = !empty($_POST['cat']) ? absint($_POST['cat']) : 0;
    $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : 'new';
    $merge_hide = get_mo_hide_unjoin($user_id);
    $page_nav_html = '';
    $nav_type = get_op('moment_nav','btn');
    //$params = json_decode( stripslashes( $_POST['query'] ), true ); 
    //$params['paged'] = $_POST['paged'] + 1;
        $params = array(
            'post_type' => 'moment', 
            'post_status' => 'publish',
            'ignore_sticky_posts' => true,
            'tax_query' => array(
                'relation' => 'AND',
            ),
            ); 
    
    $owner = $cat ? absint(get_term_meta($cat, 'mo_owner', true)) : 0;

    if($cat == 0){

        if($type == 'new'){

            if(!empty($sticky)){
               $params['post__not_in'] = $sticky;          
            } 
    
            $sticky_posts = moment_sticky_loop(); 
        }
        
        
        if(!empty($merge_hide)){
            array_push( $params['tax_query'], array (
                'taxonomy' => 'moments',
                'field' => 'term_id',
                'terms' => $merge_hide,
                'operator' => 'NOT IN',
            ));
        } else{
            unset($params['tax_query']);
        }
        
        $owner = 1;
    }

    if($cat != 0 && $cat > 0){

        array_push( $params['tax_query'], array (
            'taxonomy' => 'moments',
            'field' => 'term_id',
            'terms' => $cat,
        ));

        pix_moment_apply_term_view_limit($params, $cat, $user_id);
    }

    $params = moment_filter_arr($type,$params,$owner);

    $filter_data = pix_moment_query_cached_ids($params, $user_id, 45);
    $posts_html = pix_moment_render_ids($filter_data['ids'], true);

   if($nav_type == 'pagenav'){
    $base_url = isset($_POST['baseurl']) ? esc_url_raw($_POST['baseurl']) : '';
    ob_start(); 
    echo paginate_links(array(
        'base' => trailingslashit($base_url) . 'page/%#%/',
        'format' => '?paged=%#%',
        'current' => 1,
        'total' => $filter_data['max_num_pages'],
        'end_size' => 1,
        'mid_size' => 1,
        'prev_text' => '上一页',
        'next_text' => '下一页',
        'type' => 'list'
    ));

    $page_nav_html = ob_get_clean();
   } 

   wp_send_json( array(
		'posts' => json_encode( $params ),
		'max_page' => $filter_data['max_num_pages'],
		'found_posts' => $filter_data['found_posts'],
		'content' => $sticky_posts.$posts_html,
        'pagenav' => $page_nav_html,
	) );
 
}
add_action('wp_ajax_nopriv_moment_type_filter', 'moment_type_filter');
add_action('wp_ajax_moment_type_filter', 'moment_type_filter');

// ajax加载片刻
function cls_load_moments(){
    check_ajax_referer('moment_ajax', 'security');

    global $wp_query;
    //$params = json_decode( stripslashes( $_POST['query'] ), true ); 
    $sticky = get_option('sticky_posts');
    $sticky_posts = '';
    $user_id = get_current_user_id();
    $params = array();
    $catid = !empty($_POST['cat']) ? absint($_POST['cat']) : 0;
    $tagid = !empty($_POST['tag']) ? absint($_POST['tag']) : 0;
    $max = isset($_POST['max']) ? max(1, absint($_POST['max'])) : 1;
    $nav_type = get_op('moment_nav','btn');
    $filter_type = isset($_POST['filter_type']) ? sanitize_key($_POST['filter_type']) : 'new';

    $show = get_term_meta( $catid, 'mo_show_type', true);
    $show = $show ? $show : 'show';
    $hide_moments = get_option('moments_hide');
    $hide_moments = $hide_moments ? $hide_moments : '';
    $merge_hide = get_mo_hide_unjoin($user_id);

    $page_nav_html = '';

   $params = array(
        'post_type' => 'moment', 
        'post_status' => 'publish',
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
        'tax_query' =>  array(
            'relation' => 'AND',
        ),
    ); 

    if($nav_type != 'pagenav') {
        $params['paged'] = isset($_POST['paged']) ? absint($_POST['paged']) + 1 : 2;
    } else {
        $params['paged'] = isset($_POST['paged']) ? max(1, absint($_POST['paged'])) : 1;
        $base_url = isset($_POST['baseurl']) ? esc_url_raw($_POST['baseurl']) : '';
        ob_start(); 
        echo paginate_links(array(
            'base' => trailingslashit($base_url) . 'page/%#%/',
            'format' => '?paged=%#%',
            'current' => $params['paged'],
            'total' => $max,
            'end_size' => 1,
            'mid_size' => 1,
            'prev_text' => '上一页',
            'next_text' => '下一页',
            'type' => 'list'
        ));

        $page_nav_html = ob_get_clean(); 
        
    }
    
    $owner = $catid ? absint(get_term_meta($catid, 'mo_owner', true)) : 0;

    if($catid == 0 && $tagid == 0){

        if($filter_type == 'new'){
            if(!empty($sticky)){
                $params['post__not_in'] = $sticky;
                
            } 

            if($params['paged'] == 1) {
                $sticky_posts = moment_sticky_loop();
            }
        }

        if(!empty($merge_hide)){
            array_push( $params['tax_query'], array (
                'taxonomy' => 'moments',
                'field' => 'term_id',
                'terms' => $merge_hide,
                'operator' => 'NOT IN',
            ));
        } else {
            unset($params['tax_query']);
        }

        $owner = 1;
    
    }


    if($catid > 0){
        array_push( $params['tax_query'], array (
            'taxonomy' => 'moments',
            'field' => 'term_id',
            'terms' => $catid,
        ));

        $view_state = pix_moment_apply_term_view_limit($params, $catid, $user_id);
        if($view_state !== 'open'){
            wp_send_json( array(
                'content' => '',
                'pagenav' => '',
            ) );
        }
    }

    if($tagid > 0){
        array_push( $params['tax_query'], array (
            'taxonomy' => 'moment_tag',
            'field' => 'term_id',
            'terms' => $tagid,
        ));
    }

    $params = moment_filter_arr($filter_type,$params,$owner);
    

    $filter_data = pix_moment_query_cached_ids($params, $user_id, 45);
    $posts_html = pix_moment_render_ids($filter_data['ids'], false);
    

    wp_send_json( array(
		'content' => $sticky_posts.$posts_html,
        'pagenav' => $page_nav_html,
	) );
}

// 判断是否加入了圈子
function check_mo_joined($term_id = null, $user_id = null){
    $term_id = $term_id ? absint($term_id) : absint(get_queried_object()->term_id ?? 0);
    $user_id = $user_id ? absint($user_id) : get_current_user_id();
    return pix_moment_user_joined_term($user_id, $term_id);
}

// 加入圈子按钮
function join_mo_box($term_id){
    $type = get_term_meta( $term_id, 'mo_join_type', true );
    $type = $type ? $type : 'free';
    // 支付/VIP 功能已移除：付费圈子和权限圈子统一按免费圈子处理
    if ($type === 'pay' || $type === 'limits') {
        $type = 'free';
    }
    $user_id = get_current_user_id();
    $html = '';
    switch ($type) {
        case 'free':
            $free_join = get_term_meta( $term_id, 'mo_free_join_type', true );

            if($free_join == 'free'){
                    $html = '<div class="title pix-moment-join-title"><i class="ri-error-warning-line"></i>您暂未加入该圈子</div>
                    <div class="tips pix-moment-join-tip">免费圈子，加入后可阅读更多内容</div>
                    <a class="free-join pix-moment-join-action pix-moment-join-action-primary" term_id="'.$term_id.'">立即加入</a>';
            } else {
                    $html = '<div class="title pix-moment-join-title"><i class="ri-error-warning-line"></i>您暂未加入该圈子</div>
                    <div class="tips pix-moment-join-tip">免费圈子，申请加入后可阅读更多内容</div>
                    <a class="verify-join pix-moment-join-action pix-moment-join-action-primary" term_id="'.$term_id.'">立即申请</a>';

                    $wait_join = get_term_meta( $term_id, 'mo_wait_join', true );
                    if(!empty($wait_join)){
                        if (check_mo_had_join($user_id,$term_id)) {
                            $html = '<div class="title pix-moment-join-title"><i class="ri-error-warning-line"></i>您暂未加入该圈子</div>
                            <div class="tips pix-moment-join-tip">免费圈子，申请加入后可阅读更多内容</div>
                            <a class="wait-join pix-moment-join-action pix-moment-join-action-wait" term_id="'.$term_id.'">申请已提交，正在审核</a>';
                        }
                    }
            }
            break;
    }

    return '<div class="user-unjoined pix-moment-join-box">'.$html.'</div>';
}

// 免费圈子加入
add_action('wp_ajax_mo_join_free', 'mo_join_free');
function mo_join_free(){
    if(!is_user_logged_in()){
        wp_send_json(array('code'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $term_id = $_POST['term_id'] ?? '';
    $current_user = wp_get_current_user();
	$user_id = $current_user->ID;
    if($term_id){
        $type = get_term_meta( $term_id, 'mo_join_type', true );
        $type = $type ? $type : 'free';
        // 支付/VIP 功能已移除：付费圈子和权限圈子按免费圈子加入
        if ($type == 'free' || $type == 'pay' || $type == 'limits') {
            update_user_join($user_id,$term_id);

            $msg = array('code'=>1,'msg'=>'加入成功，跳转中...');
        } else {
            $msg = array('code'=>0,'msg'=>'未知错误');
        }

        wp_send_json($msg);
    } 
    
}

// 申请加入圈子
add_action('wp_ajax_mo_join_verify', 'mo_join_verify');
function mo_join_verify(){
    if(!is_user_logged_in()){
        wp_send_json(array('code'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $term_id = $_POST['term_id'] ?? '';
    $current_user = wp_get_current_user();
	$user_id = $current_user->ID;
    $wait_join = get_term_meta( $term_id, 'mo_wait_join', true );
    $wait_join = $wait_join ? $wait_join : array();
    
    if($term_id){
        $type = get_term_meta( $term_id, 'mo_free_join_type', true );
        $type = $type ? $type : 'verify';
        if($type == 'verify'){

            
                //$wait_join_arr = explode(',', $wait_join);
                if (!check_mo_had_join($user_id,$term_id)) {
                    $wait_join[] = array('user_id'=>$user_id,'time'=>current_time('mysql'));
                }
           
            update_term_meta($term_id, 'mo_wait_join', $wait_join);
            // 留个钩子通知圈主
            $msg = array('code'=>1,'msg'=>'申请成功，等待审核');
        } else {
            $msg = array('code'=>0,'msg'=>'未知错误');
        }

        wp_send_json($msg);
    } 
    
}

// 判断用户是否已经申请圈子
function check_mo_had_join($user_id,$term_id){
   $joined_arr = get_term_meta($term_id, 'mo_wait_join', true);
   $userExists = false;
   foreach ($joined_arr as $joined) {
    if ($joined['user_id'] == $user_id) {
        $userExists = true;
        break; // 找到后无需继续遍历
    }
    }

    return $userExists;
}

// 更新用户加入的圈子
function update_user_join($user_id,$term_id){
    $user_join = get_user_meta($user_id, 'user_mo_joined', true);
    $mo_joined = get_term_meta($term_id, 'mo_joined', true);

    $user_join_count = get_user_meta($user_id, 'user_mo_joined_count', true);
    $mo_joined_count = get_term_meta($term_id, 'mo_joined_count', true);

    $u_plus = 0;
    $m_plus = 0;

    if (!empty($user_join)) {
        $user_join_arr = explode(',', $user_join);
        if (!in_array($term_id, $user_join_arr)) {
            $user_join .= ',' . $term_id;
            $u_plus = 1;
        }
    } else {
        $user_join = $term_id;
        $user_join_count = 0;
        $u_plus = 1;
    }

    if (!empty($mo_joined)) {
        $mo_joined_arr = explode(',', $mo_joined);
        if (!in_array($user_id, $mo_joined_arr)) {
            $mo_joined .= ',' . $user_id;
            $m_plus = 1;
        }
    } else {
        $mo_joined = $user_id;
        $mo_joined_count = 0;
        $m_plus = 1;
    }

    update_user_meta($user_id, 'user_mo_joined', $user_join);
    update_term_meta($term_id, 'mo_joined', $mo_joined);

    $user_join_count += $u_plus; // 如果不存在，则默认为0
    $mo_joined_count += $m_plus; // 如果不存在，则默认为0

    update_user_meta($user_id, 'user_mo_joined_count', $user_join_count);
    update_term_meta($term_id, 'mo_joined_count', $mo_joined_count); 
}

// 获取创建的和加入的
function get_mo_join($type){
    $user_id = get_current_user_id();
    $html = '';
    $user_join = get_user_meta($user_id, 'user_mo_joined', true);
    //$type = 'create';
    if($type == 'create'){
        $arr = get_terms( array(
            'taxonomy'   => 'moments',
            'hide_empty' => false,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'mo_owner',
                    'value' => $user_id,
                    'compare' => '=',
                ),
            )
        ) );
    } else {
        $arr = $user_join ? explode(',', $user_join) : '';
        $arr = is_array($arr) ? array_values(array_filter(array_map('absint', $arr), function($term_id) use ($user_id){
            return pix_moment_user_joined_term($user_id, $term_id);
        })) : '';
    }

    //return $arr;

    if(is_array($arr) && !empty($arr)){
        foreach ($arr as $k => $v) {
            $term_data = get_term_by( 'id', $v, 'moments' );
            $title = $term_data->name;
            $term_link = get_term_link((int)$v, 'moments');
            $img = get_term_meta($v, 'mo_cat_img', true);
            $img = !empty($img) ? $img : THEME_URL.'/img/modef.png';
    
            $html .= '<a href="'.$term_link.'" class="mo-topbar-item pix-moment-circle-user-dropdown-item">
                        <div class="left pix-moment-circle-user-dropdown-icon"><img src="'.$img.'"></div>
                        <div class="right pix-moment-circle-user-dropdown-title">'.$title.'</div>
                    </a>';
            
              
    
        }

        return '<div class="mo-topbar-list pix-moment-circle-user-dropdown-list">'.$html.'</div>';
    } else {
        return '<div class="mo-topbar-list pix-moment-circle-user-dropdown-list"><div class="empty pix-moment-circle-user-dropdown-empty">空空如也</div></div>';
    }

}

add_action('wp_ajax_load_user_mo_join', 'load_user_mo_join');
add_action('wp_ajax_nopriv_load_user_mo_join', 'load_user_mo_join');
function load_user_mo_join(){
    if(!is_user_logged_in()){
        wp_send_json(array('code'=>0,'msg'=>'请先登录'));
    }

    if(!check_ajax_referer('moment_ajax', 'security', false)){
        wp_send_json(array('code'=>0,'msg'=>'页面验证已过期，请刷新后重试'));
    }

    $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : 'join';
    if(!in_array($type, array('join', 'create'), true)){
        wp_send_json(array('code'=>0,'msg'=>'请求类型错误'));
    }

    $res = get_mo_join($type);

    if($res){
        wp_send_json(array('code'=>1,'html'=>$res));
    }

    wp_send_json(array('code'=>1,'html'=>'<div class="mo-topbar-list pix-moment-circle-user-dropdown-list"><div class="empty pix-moment-circle-user-dropdown-empty">空空如也</div></div>'));
    
}

// 获取圈子数据
function get_mo_num_data($term_id){
    //$join = get_user_meta($user_id, 'user_mo_joined_count', true);
    $join = get_term_meta($term_id,'mo_joined_count',true);
    $count = get_term_by( 'id', $term_id, 'moments' );
    $count = $count->count;

    $res = array(
        'count' => $count,
        'join' => $join ? $join : '0',
    );

    return $res;
}

// 付费圈子名称和价格
function get_mj_data($term_id,$t_data){
    $term_data = get_term_by('id', $term_id, 'moments');
    $title = $term_data->name;
    $pay_data = get_term_meta( $term_id, 'mo_join_pay', true );
    $pay_data = $pay_data ? $pay_data : '';
    $price = $pay_data[$t_data];

    return array(
        'subject' => '圈子订阅:'.$title,
        'total_amount' => round($price,2),
    );

}

// 圈子订阅时间倍率
function mj_time_x(){
    $array = array(
        'mp' => 30,
        'qp' => 90,
        'hp' => 180,
        'op' => 365,
        'fp' => 0,
    );

    return $array;
}

// 更新圈子订阅回调
function update_pay_mj($data){
    $user_id = $data['user_id'];
    $pay_time = $data['pay_time'];
    $term_id = $data['data_id'];
    $mj_data = get_user_meta( $user_id, 'mj_'.$term_id.'', true );
    $mj_data = !empty($mj_data) ? $mj_data : array();
    $m = $data['order_extra'];
    $mj_days = mj_time_x()[$m];
    $now = current_time('timestamp');
    $current_end_time = !empty($mj_data['end_time']) ? absint($mj_data['end_time']) : 0;

    if(!empty($mj_data)){
        $base_time = $current_end_time > $now ? $current_end_time : $now;
        $end_time = $mj_days != 0 ? $base_time + 86400 * $mj_days : 0;
        $mj_data['pay_time'] = $pay_time;
        $mj_data['term_id'] = $term_id;
        $mj_data['end_time'] = $end_time;
    } else {
        $end_time = $mj_days != 0 ? $now + 86400 * $mj_days : 0;
        $mj_data = array(
            'pay_time' => $pay_time,
            'term_id' => $term_id,
            'end_time' => $end_time,
        );
    }

    update_user_meta($user_id, 'mj_'.$term_id.'', $mj_data);
    update_user_join($user_id,$term_id);
}

// 注册圈子订阅提醒的 8 小时 cron 周期
add_filter('cron_schedules', 'pix_moment_add_eight_hour_cron_schedule');
function pix_moment_add_eight_hour_cron_schedule($schedules){
    if(!isset($schedules['pix_every_8_hours'])){
        $schedules['pix_every_8_hours'] = array(
            'interval' => 8 * HOUR_IN_SECONDS,
            'display'  => '每 8 小时一次',
        );
    }

    return $schedules;
}

// 定时检查付费圈子订阅，并发送到期提醒
add_action('init', 'pix_moment_schedule_subscription_expiry_notice');
function pix_moment_schedule_subscription_expiry_notice(){
    $hook = 'pix_moment_subscription_expiry_notice';
    $event = function_exists('wp_get_scheduled_event') ? wp_get_scheduled_event($hook) : false;

    if(!$event){
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'pix_every_8_hours', $hook);
    } elseif($event->schedule !== 'pix_every_8_hours'){
        wp_unschedule_event($event->timestamp, $hook);
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'pix_every_8_hours', $hook);
    }
}

add_action('pix_moment_subscription_expiry_notice', 'pix_moment_send_subscription_expiry_notices');
function pix_moment_send_subscription_expiry_notices(){
    if(!function_exists('ppo_send_private_message')){
        return;
    }

    $terms = get_terms(array(
        'taxonomy'   => 'moments',
        'hide_empty' => false,
        'fields'     => 'ids',
    ));

    if(is_wp_error($terms) || empty($terms)){
        return;
    }

    $now = current_time('timestamp');

    foreach($terms as $term_id){
        $term_id = absint($term_id);
        if(!$term_id || get_term_meta($term_id, 'mo_join_type', true) !== 'pay'){
            continue;
        }

        $term = get_term($term_id, 'moments');
        if(!$term || is_wp_error($term)){
            continue;
        }

        $term_url = get_term_link($term_id, 'moments');
        if(is_wp_error($term_url)){
            $term_url = home_url();
        }

        $meta_key = 'mj_' . $term_id;
        $user_ids = get_users(array(
            'meta_key' => $meta_key,
            'fields'   => 'ids',
            'number'   => -1,
        ));

        foreach($user_ids as $user_id){
            $user_id = absint($user_id);
            $mj_data = get_user_meta($user_id, $meta_key, true);
            if(!$user_id || !is_array($mj_data) || empty($mj_data)){
                continue;
            }

            $end_time = isset($mj_data['end_time']) ? absint($mj_data['end_time']) : 0;
            if($end_time === 0){
                continue;
            }

            $remaining = $end_time - $now;
            if($remaining <= 0 || $remaining > DAY_IN_SECONDS){
                continue;
            }

            // 记录当前结束时间，避免同一订阅周期重复提醒。
            $notice_key = 'mj_expire_notice_' . $term_id;
            $notice_end_time = absint(get_user_meta($user_id, $notice_key, true));
            if($notice_end_time === $end_time){
                continue;
            }

            $expire_time = wp_date('Y年m月d日 H:i', $end_time);
            $title = '<h3>圈子订阅即将到期</h3>';
            $message = sprintf(
                '<div class="bot-msg-content">您订阅的「%s」将于 %s 到期，请及时续费。</div>',
                esc_html($term->name),
                esc_html($expire_time)
            );
            $message .= '<div class="bot-msg-bottom"><a href="' . esc_url($term_url) . '" class="btn-primary">前往续费</a></div>';

            if(ppo_send_private_message('moment_bot', $user_id, $title . $message)){
                update_user_meta($user_id, $notice_key, $end_time);
            }
        }
    }
}

// 圈子开启的功能
function get_mj_func($term_id){
    $arr = get_term_meta($term_id, 'mo_join_fun', true);
    $type = get_term_meta($term_id, 'mo_set_type', true);
    $type = $type ? $type : 'global';
    if($type == 'global'){
        $arr = get_op('mo_join_fun');
        $arr = !empty($arr) ? $arr : array('gallery');
    } else if($type == 'single'){
        if(empty($arr)){ 
            $arr = array('gallery');
        }
    }
    

    return $arr;
}

// 是否允许插入卡片
function is_allow_card($user_id,$term_id){
    $vip = get_user_meta( $user_id, 'ppo_vip', true );
    $level_key = '';
    if(function_exists('ppo_get_user_level_info')){
        $lv_data = ppo_get_user_level_info($user_id);
        if(is_array($lv_data) && !empty($lv_data['lv'])){
            $level_key = 'lv'.intval($lv_data['lv']);
        }
    }
    $type = get_term_meta($term_id, 'mo_set_type', true);
    $type = $type ? $type : 'global';
    if($type == 'global'){
        $arr = get_op('allow_card_group');
    } else if($type == 'single'){
        $arr = get_term_meta( $term_id, 'allow_card_group', true );
    }

    $arr = !empty($arr) ? $arr : array();

    if(function_exists('ppo_user_group_allowed') && ppo_user_group_allowed($arr, $vip, $level_key)){
        return true;
    } else {
        return false;
    }
}

// 圈子数据重建
function rebuild_moments_panel(){
    $html = '<div class="rebuild-box">
                <div class="title">输入需要重建的圈子ID</div>
                    <input type="text" class="moments_rebuild" name="moments_rebuild" value="">
                <p>请输入需要重建圈子的ID，请谨慎操作</p>
                <div class="tips"></div>
                <a class="rebuild-btn button button-primary">确认重建</a>
            </div>';
    echo $html;
}

add_action('wp_ajax_rebuild_moments_data', 'rebuild_moments_data');
function rebuild_moments_data(){
    if(!is_user_logged_in()){
        wp_send_json(array('code'=>0,'msg'=>'请先登录'));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json(array('code'=>0,'msg'=>'无权操作'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $term_id = !empty($_POST['id']) ? $_POST['id'] : '';
    if(!empty($term_id)){
        $term_id = intval($term_id);
        $is_show = get_term_meta($term_id, 'mo_show_type', true);
        $hide_arr = get_option( 'moments_hide');
        $hide_arr = !empty($hide_arr) ? $hide_arr : array();
        if(in_array($is_show, array('join', 'private'), true)){
            
            if(!in_array($term_id,$hide_arr)){
                $hide_arr[] = $term_id;
            }
         
        } else {
            $key = array_search($term_id, $hide_arr);
            if($key !== false){
                unset($hide_arr[$key]);
            }
        }
        update_option( 'moments_hide', $hide_arr );

        $msg = array('msg' => '重建成功');
    } else {
        $msg = array('msg' => '重建失败');
    }

    wp_send_json( $msg );
    
} 

// 获取未加入且不显示的圈子id数组,用于排除隐藏圈子中已加入的
function get_mo_hide_unjoin($user_id){
    $hide_moments = get_option('moments_hide');
    $hide_moments = is_array($hide_moments) ? $hide_moments : (empty($hide_moments) ? array() : (array)$hide_moments);
    $user_join = get_user_meta($user_id, 'user_mo_joined', true);
    $user_join = !empty($user_join) ? array_filter(array_map('absint', explode(',', $user_join))) : array();
    $user_join = array_values(array_filter($user_join, function($term_id) use ($user_id){
        return pix_moment_user_joined_term($user_id, $term_id);
    }));

    $hide = array_diff($hide_moments, $user_join);
    return $hide ? $hide : array();
}

// 片刻发文字数限制
function mo_word_max($term_id){
    $type = get_term_meta( $term_id, 'mo_set_type', true );
    
    if($type == 'single' && !empty($term_id)){
        $max = get_term_meta( $term_id, 'mo_text_num', true );
        $max =  $max ? $max : '5-240';
    } else {
        $max = get_op('mo_text_num','5-240');
    }

    $max = explode('-', $max);

    return array('min'=>intval($max[0]),'max'=>intval($max[1]));
}

// 图片数量限制
function mo_gallery_num($term_id){
    $type = get_term_meta( $term_id, 'mo_set_type', true );
    
    if($type == 'single' && !empty($term_id)){
        $num = get_term_meta( $term_id, 'gallery_num', true );
        $num =  $num  ? $num  : '9';
    } else {
        $num = get_op('mo_gallery_num','9');
    }

    return intval($num);
}

// 文件数量限制
function mo_file_num($term_id){
    $type = get_term_meta( $term_id, 'mo_set_type', true );
    
    if($type == 'single' && !empty($term_id)){
        $num = get_term_meta( $term_id, 'file_num', true );
        $num =  $num  ? $num  : '3';
    } else {
        $num = get_op('mo_file_num','3');
    }

    return intval($num);
}

// 用户发布圈子权限
function user_moments_power($user_id , $term_id,$type){
    $power = get_user_power($user_id);
    $func = get_mj_func($term_id);
    $allow_card = is_allow_card($user_id,$term_id);
    $res = false;

    if(!$term_id){
        return true;
    }

    $is_privileged = user_can($user_id, 'manage_options') || get_term_meta($term_id, 'mo_owner', true) == $user_id;

    switch ($type) {
        case 'gallery':
            $res = in_array('gallery', $func) && ($is_privileged || in_array('up_image',$power)) ? true : false;
            break;
        
        case 'video':
            $res = in_array('video', $func) && ($is_privileged || in_array('up_video',$power)) ? true : false;
            break;

        case 'audio':
            $res = in_array('audio', $func) ? true : false;
            break; 

        case 'file':
            $res = in_array('file', $func) && ($is_privileged || in_array('up_file',$power)) ? true : false;
            break;    
            
        case 'card':
            $res = in_array('card', $func) && ($is_privileged || $allow_card) ? true : false;
            break;      
    }

    return $res;
}

function pix_moment_user_can_post_to_term($user_id, $term_id){
    $term_id = absint($term_id);
    if(!$term_id){
        return false;
    }

    $term = get_term($term_id, 'moments');
    if(!$term || is_wp_error($term)){
        return false;
    }

    if(current_user_can('manage_options') || get_term_meta($term_id, 'mo_owner', true) == $user_id){
        return true;
    }

    return pix_moment_user_joined_term($user_id, $term_id);
}

function pix_moment_get_permission_profile($user_id, $term_id){
    $term_id = absint($term_id);
    $term = $term_id ? get_term($term_id, 'moments') : false;
    if(!$term || is_wp_error($term)){
        return new WP_Error('moment_term_invalid', '圈子不存在');
    }

    $set_type = get_term_meta($term_id, 'mo_set_type', true);
    $set_type = $set_type ? $set_type : 'global';
    $words_num = mo_word_max($term_id);
    $gallery_num = max(1, (int)mo_gallery_num($term_id));
    $file_num = max(1, (int)mo_file_num($term_id));
    $join_type = get_term_meta($term_id, 'mo_join_type', true);

    if($set_type === 'single'){
        $mo_join_fun = get_term_meta($term_id, 'mo_join_fun', true);
        $card_group = get_term_meta($term_id, 'allow_card_group', true);
        $card_num = get_term_meta($term_id, 'card_num', true);
        $gallery_link = get_term_meta($term_id, 'gallery_link', true);
    } else {
        $mo_join_fun = get_op('mo_join_fun');
        $card_group = get_op('allow_card_group');
        $card_num = get_op('mo_card_num', 3);
        $gallery_link = get_op('mo_gallery_link');
    }

    $mo_join_fun = is_array($mo_join_fun) && !empty($mo_join_fun) ? $mo_join_fun : array('gallery');
    $card_group = is_array($card_group) ? $card_group : array();
    $card_num = max(1, (int)($card_num ? $card_num : 3));

    $mo_func_arr = array('gallery','video','audio','file','card');
    $mo_user_power = array();
    foreach($mo_func_arr as $value){
        $mo_user_power[$value] = user_moments_power($user_id, $term_id, $value);
    }

    return array(
        'user_id' => (int)$user_id,
        'term_id' => $term_id,
        'term' => $term,
        'joined' => pix_moment_user_can_post_to_term($user_id, $term_id),
        'join_type' => $join_type,
        'mo_type' => $set_type,
        'words_num' => $words_num,
        'gallery_num' => $gallery_num,
        'gallery_link' => $gallery_link ? true : false,
        'mo_join_fun' => $mo_join_fun,
        'card_group' => $card_group,
        'card_num' => $card_num,
        'file_num' => $file_num,
        'media_max_size' => array(
            'image' => (float)get_op('image_max_size', 3),
            'video' => (float)get_op('video_max_size', 20),
            'file' => (float)get_op('file_max_size', 10),
        ),
        'mo_user_power' => $mo_user_power,
    );
}

function pix_moment_upload_policy($user_id, $term_id, $kind){
    $profile = pix_moment_get_permission_profile($user_id, $term_id);
    if(is_wp_error($profile)){
        return $profile;
    }

    if(!$profile['joined']){
        return new WP_Error('moment_not_joined', '请先加入该圈子后再上传');
    }

    $kind = sanitize_key($kind);
    $map = array(
        'image' => 'gallery',
        'video' => 'video',
        'file' => 'file',
    );

    if(!isset($map[$kind])){
        return new WP_Error('moment_upload_type_invalid', '上传类型不正确');
    }

    $power_key = $map[$kind];
    if(empty($profile['mo_user_power'][$power_key])){
        return new WP_Error('moment_upload_forbidden', '当前圈子或用户组未开启该上传权限');
    }

    $mimes = array(
        'image' => array('image/jpeg', 'image/png', 'image/gif', 'image/webp'),
        'video' => array('video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'),
        'file' => array(
            'text/plain',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/zip',
        ),
    );

    return array(
        'kind' => $kind,
        'term_id' => $profile['term_id'],
        'max_size' => $profile['media_max_size'][$kind],
        'limit' => $kind === 'image' ? $profile['gallery_num'] : ($kind === 'video' ? 1 : $profile['file_num']),
        'mimes' => $mimes[$kind],
        'profile' => $profile,
    );
}

// 获取圈子所有数据
function get_current_mo_data(){
    check_ajax_referer('moment_ajax', 'security');

    $user_id = get_current_user_id();
    $term_id = isset($_POST['term_id']) && $_POST['term_id'] ? (int)$_POST['term_id'] : 0;
    $pid = isset($_POST['pid']) && $_POST['pid'] ? (int)$_POST['pid'] : 0;

    // 如果没有 term_id 但有 pid，从 post 的 taxonomy 获取
    if (!$term_id && $pid) {
        $terms = get_the_terms($pid, 'moments');
        if ($terms && !is_wp_error($terms)) {
            $term_id = (int)$terms[0]->term_id;
        }
    }

    if (!$term_id) {
        wp_send_json(array('status' => 0, 'msg' => '无法获取圈子信息'));
    }

    $mo_data = pix_moment_get_permission_profile($user_id, $term_id);
    if(is_wp_error($mo_data)){
        wp_send_json(array('status' => 0, 'msg' => $mo_data->get_error_message()));
    }
    unset($mo_data['term']);

    wp_send_json($mo_data);
}
add_action( 'wp_ajax_get_current_mo_data', 'get_current_mo_data' );

// 片子编辑按钮
function mo_edit_btn($pid){
    $user_id = get_current_user_id();
    if (!$user_id) return '';

    $post = get_post($pid);
    if (!$post) return '';

    $author_id = $post->post_author;
    $term_id = get_category_id_by_post_id($pid, 'moments');

    $is_admin = current_user_can('manage_options');
    $is_owner = $term_id ? get_term_meta($term_id, 'mo_owner', true) == $user_id : false;
    $is_author = $user_id == $author_id;

    if (!$is_admin && !$is_owner && !$is_author) return '';

    $m_sticky = is_sticky($pid) ? 'unstick' : 'stick';
    $m_sticky_text = is_sticky($pid) ? '取消置顶' : '置顶片刻';
    $m_hot = get_post_meta($pid, 'moment_hot', true) ? 'unhot' : 'hot';
    $m_hot_text = get_post_meta($pid, 'moment_hot', true) ? '取消加精' : '加精';

    $moment_link = get_permalink($pid);
    $html = '<div class="edit-panel item pix-moment-card-action-menu-wrap hs-dropdown [--placement:top] [--offset:2] [--auto-close:true]">
                <a class="mo-edit-btn pix-moment-card-action-more hs-dropdown-toggle" aria-haspopup="menu" aria-expanded="false" aria-label="片刻操作"><i class="ri-settings-4-line"></i></a>
                <div class="edit-drop-box pix-moment-card-action-menu hs-dropdown-menu hidden" pid="'.$pid.'" moment-link="'.$moment_link.'" role="menu">
                    <div class="pix-dropdown-motion pix-dropdown-animated pix-dropdown-slide-up" data-hs-dropdown-transition>';

    if ($is_admin || $is_owner) {
        $html .= '<a href="javascript:;" class="mo-edit-top pix-moment-card-action-menu-item" state="'.$m_sticky.'"><i class="ri-arrow-up-line"></i>'.$m_sticky_text.'</a>
                  <a href="javascript:;" class="mo-edit-hot pix-moment-card-action-menu-item" state="'.$m_hot.'"><i class="ri-vip-diamond-line"></i>'.$m_hot_text.'</a>';
    }

    if ($is_admin || $is_owner || $is_author) {
        $html .= '<a href="'.esc_url($moment_link).'" class="mo-view-page pix-moment-card-action-menu-item"><i class="ri-external-link-line"></i>查看详情</a>
                  <a href="'.home_url('/moment-edit?pid='.$pid.'').'" target="_blank" class="mo-edit-page pix-moment-card-action-menu-item"><i class="ri-edit-line"></i>编辑</a>
                  <a href="javascript:;" class="mo-delete pix-moment-card-action-menu-item pix-moment-card-action-menu-item-danger"><i class="ri-delete-bin-7-line"></i>删除</a>';
    }

    $html .= '<a href="javascript:;" class="code-copy pix-moment-card-action-menu-item" data-clipboard-text="'.$moment_link.'""><i class="ri-file-copy-line"></i>复制链接</a>
                    </div>
                </div>
            </div>';

    return $html;
}

// 判断片刻是否加精
function is_moment_hot($pid){
    $res = get_post_meta($pid, 'moment_hot', true) ? true : false;
    return $res;
}

// 获取单篇片刻数据
function get_single_moment_data(){
    check_ajax_referer('moment_ajax', 'security');

    $pid = $_POST['pid'];
    $user_id = get_current_user_id();

    // 权限检查：只有管理员、圈子所有者或文章作者才能获取编辑数据
    if (!moment_auth($pid, $user_id, false)) {
        wp_send_json(array('status'=>0,'msg'=>'无权获取此片刻编辑数据'));
    }

    $mo_type = get_post_meta($pid, 'moment_type', true);

    if($mo_type == 'gallery'){
        //$mo_type = 'ga';
        $mo_data = get_post_meta($pid, 'moment_ga', true);
    } else {
        $mo_data = get_post_meta($pid, 'moment_'.$mo_type.'', true);
    }

    

    $pix_items = pix_moment_edit_pix_items($mo_type, $mo_data);
    $trem = get_the_terms($pid, 'moments');
    $tag = get_the_terms($pid, 'moment_tag');
    $term_id = (!empty($trem) && !is_wp_error($trem)) ? (int)$trem[0]->term_id : 0;
    $term_name = (!empty($trem) && !is_wp_error($trem)) ? $trem[0]->name : '';
    $tag_id = (!empty($tag) && !is_wp_error($tag)) ? (int)$tag[0]->term_id : 0;
    $tag_name = (!empty($tag) && !is_wp_error($tag)) ? $tag[0]->name : '';
    $term_img = $term_id ? get_term_meta( $term_id, 'mo_cat_img', true ) : '';

    if($mo_type == 'card'){
        $mo_data = get_cards_data_arr($mo_data);
    } else if($mo_type == 'video'){
        $mo_data = get_video_edit_data($mo_data);
    } else if($mo_type == 'file'){
        $mo_data = get_file_edit_data($mo_data);
    }

    $data = array(
        'pid' => $pid,
        'user_id' => $user_id,
        'action' => 'edit',
        'type' => $mo_type,
        'mo_data' => $mo_data,
        'pix_items' => $pix_items,
        'content' => get_post($pid)->post_content,
        'title' => get_post($pid)->post_title,
        'tag_id' => $tag_id,
        'tag_name' => $tag_name,
        'term_id' => $term_id,
        'term_name' => $term_name,
        'term_link' => $term_id && !is_wp_error(get_term_link($term_id, 'moments')) ? get_term_link($term_id, 'moments') : home_url('/'),
        'moment_url' => get_permalink($pid),
        'term_img' => $term_img ? $term_img : THEME_URL.'/img/modef.png',
    );    
    wp_send_json($data);

}
add_action( 'wp_ajax_get_single_moment_data', 'get_single_moment_data' );

//获取视频编辑数据
function get_video_edit_data($data){
    $video_data = array();
    foreach($data as $key => $val){

        if($val['type'] == 'bili'){
            $video_data[$key] = array(
                'bvid' => $val['bvid'],
                'video_type' => 'bili',
            );
        } else {
            $att_id = $val['att_id'];
            $video_id = $val['video_id'];
            $attachment_path = get_attached_file( $video_id );
            $file_size = filesize( $attachment_path );
            $extension = get_post( $video_id )->post_mime_type;
            $file_name = basename($val['url']);
            $video_data[$key] = array(
                'name' => $file_name,
                'type' => $extension,
                'file' => $val['url'],
                'size' => $file_size,
                'att_id' => $att_id,
                'video_id' => $video_id,
                'video_type' => 'local',
                'data' => array(
                        'thumbnail' => $val['cover'],
                        'readerForce' => true,
                        'url' => $val['url'],
                        'isMain' => false,
                        'action' => 'edit',
                        ),
            );
        }
    }
    return $video_data;
}

// 获取文件编辑数据
function get_file_edit_data($data){
    $file_data = array();
    foreach($data as $key => $val){
       $file_id = $val['attach_id'];
       $attachment_path = get_attached_file( $file_id );
       $file_size = filesize( $attachment_path );
       $extension = get_post( $file_id )->post_mime_type;
       $file_name = $val['file_title'];
       $file_data[] = array(
        'name' => $file_name,
        'type' => $extension,
        'file' => $val['url'],
        'size' => $file_size,
        'file_id' => $file_id,
        'data' => array(
                'readerForce' => true,
                'url' => $val['url'],
                'isMain' => false,
                'action' => 'edit',
                ),
    );
    }
    return $file_data;
};

// 插入预览音乐
add_action( 'wp_ajax_ajax_preview_music', 'ajax_preview_music' );
function ajax_preview_music(){
    if(!is_user_logged_in()){
        wp_send_json(array('status'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $aid = isset($_POST['aid']) ? $_POST['aid'] : '';
    if(!empty($aid)){
        $html = '<div class="mo-audio-wrap"><div style="margin:-11px"></div><iframe frameborder="no" border="0" marginwidth="0" marginheight="0" width=330 height=86 src="//music.163.com/outchain/player?type=2&id='.$aid.'&auto=0&height=66" allowtransparency="true"></iframe></div><a class="remove-audio"><i class="ri-close-line"></i></a>';
        $msg = array('status' => 1, 'html' => $html);
    } else {
        $msg = array('status' => 0, 'msg' => '音乐生成失败');
    }
    wp_send_json($msg);
}

// 创建圈子模态
add_action( 'wp_ajax_cr_moments', 'cr_moments' );
function pix_can_create_moment_circle($user_id = 0){
    $user_id = $user_id ? absint($user_id) : get_current_user_id();
    if(!$user_id){
        return false;
    }
    if(current_user_can('manage_options')){
        return true;
    }
    $user_power = get_user_power($user_id);
    return is_array($user_power) && in_array('cr_moment', $user_power, true);
}

function cr_moments(){
    if(!is_user_logged_in()){
        wp_send_json(array('status'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $user_id = get_current_user_id();
    $moments_label = ppo_moment_label('moments');
    if(!pix_can_create_moment_circle($user_id)){
        wp_send_json(array('status'=>0,'msg'=>'您没有创建'.$moments_label.'的权限'));
    }

    ob_start();

    get_template_part( 'inc/layouts/moment','create');

    $html = ob_get_clean();

    wp_send_json(array('status'=>1,'html'=>$html));
}

// 创建新的圈子
add_action( 'wp_ajax_insert_moments', 'insert_moments' );
function insert_moments(){
    if(!is_user_logged_in()){
        wp_send_json(array('status'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $base_data = isset($_POST['base_data']) && is_array($_POST['base_data']) ? wp_unslash($_POST['base_data']) : array();
    $cr_data = isset($_POST['cr_data']) && is_array($_POST['cr_data']) ? wp_unslash($_POST['cr_data']) : array();
    $join_data = isset($_POST['join_data']) ? wp_unslash($_POST['join_data']) : '';
    $img_data = isset($_POST['img_data']) && is_array($_POST['img_data']) ? wp_unslash($_POST['img_data']) : array();
    $user_id = get_current_user_id();
    $moments_label = ppo_moment_label('moments');

    if(!pix_can_create_moment_circle($user_id)){
        $msg = array('status' => 0, 'msg' => '您没有创建'.$moments_label.'的权限');
        wp_send_json($msg);
    }

    if(!empty($base_data) && !empty($cr_data) && $join_data !== '' && !empty($user_id)){
        $title = isset($base_data['title']) ? sanitize_text_field($base_data['title']) : '';
        $desc = isset($base_data['desc']) ? sanitize_textarea_field($base_data['desc']) : '';
        $raw_slug = isset($base_data['slug']) ? trim(sanitize_text_field($base_data['slug'])) : '';
        if(!preg_match('/^[A-Za-z]+$/', $raw_slug)){
            wp_send_json(array('status' => 0, 'msg' => $moments_label.'别名只能填写英文字母'));
        }
        $slug = sanitize_title($raw_slug);
        $allowed_tags = get_moments_tag_arr();
        $tag = isset($cr_data['ppo_moments_tag']) ? sanitize_text_field($cr_data['ppo_moments_tag']) : '';
        if(!$tag || !in_array($tag, array_values($allowed_tags), true)){
            $tag = reset($allowed_tags);
        }
        $join_type = isset($cr_data['mo_join_type']) ? sanitize_key($cr_data['mo_join_type']) : 'free';
        if(!in_array($join_type, array('free', 'pay', 'limits'), true)){
            $join_type = 'free';
        }
        $show_type = isset($cr_data['mo_show_type']) ? sanitize_key($cr_data['mo_show_type']) : 'show';
        if($show_type === 'open'){
            $show_type = 'show';
        }
        if(!in_array($show_type, array('show', 'join', 'private'), true)){
            $show_type = 'show';
        }
        $show_num = isset($cr_data['show_num']) ? max(0, absint($cr_data['show_num'])) : 3;
        if($show_type !== 'join'){
            $show_num = 0;
        }
        $cr_data = array(
            'ppo_moments_tag' => $tag,
            'mo_join_type' => $join_type,
            'mo_show_type' => $show_type,
            'show_num' => $show_num,
            'mo_pay_credit_only' => !empty($cr_data['mo_pay_credit_only']) ? '1' : '0',
        );
        if($join_type === 'free'){
            $free_join = sanitize_key(is_array($join_data) ? reset($join_data) : $join_data);
            $join_data = in_array($free_join, array('free', 'verify'), true) ? $free_join : 'free';
        } elseif($join_type === 'pay'){
            $allowed_pay_keys = array('mp', 'qp', 'hp', 'op', 'fp');
            $pay_rows = array();
            $has_pay_price = false;
            $join_data = is_array($join_data) ? $join_data : array();
            foreach($join_data as $v){
                if(!is_array($v) || empty($v['name'])){
                    continue;
                }
                $pay_key = sanitize_key($v['name']);
                if(!in_array($pay_key, $allowed_pay_keys, true)){
                    continue;
                }
                $price = isset($v['price']) ? round((float)$v['price'], 2) : 0;
                if($price > 0){
                    $has_pay_price = true;
                }
                $pay_rows[] = array(
                    'name' => $pay_key,
                    'price' => $price,
                );
            }
            if(!$has_pay_price){
                wp_send_json(array('status' => 0, 'msg' => '付费'.$moments_label.'至少填写一个金额'));
            }
            $join_data = $pay_rows;
        } elseif($join_type === 'limits'){
            $allowed_levels = array_keys(all_lv_merge());
            $join_data = is_array($join_data) ? $join_data : array();
            $join_data = array_values(array_intersect(array_map('sanitize_text_field', $join_data), $allowed_levels));
        }

        if(!$title){
            wp_send_json(array('status' => 0, 'msg' => $moments_label.'名称不能为空'));
        }

         $term_data = wp_insert_term( $title, 'moments', array(
            'description' => $desc,
            'slug' => $slug,
        ) );

        if(!is_wp_error($term_data)){
            $term_id = $term_data['term_id'];

            // 更新圈子meta           
            update_term_meta($term_id, 'mo_owner', $user_id);

            update_term_meta($term_id, 'mo_cat_banner', isset($img_data['banner_img']) ? esc_url_raw($img_data['banner_img']) : '');
            update_term_meta($term_id, 'mo_cat_img', isset($img_data['logo_img']) ? esc_url_raw($img_data['logo_img']) : '');

            foreach($cr_data as $key => $val){
                if($key === 'mo_pay_credit_only'){
                    continue;
                }
                update_term_meta($term_id, sanitize_key($key), is_array($val) ? array_map('sanitize_text_field', $val) : sanitize_text_field($val));
            }
            update_term_meta($term_id, 'mo_pay_credit_only', ($join_type === 'pay' && !empty($cr_data['mo_pay_credit_only'])) ? '1' : '0');
            update_term_meta($term_id, 'mo_set_type', 'global');

            $hide_arr = get_option('moments_hide');
            $hide_arr = is_array($hide_arr) ? array_map('absint', $hide_arr) : array();
            if(in_array($show_type, array('join', 'private'), true)){
                if(!in_array(absint($term_id), $hide_arr, true)){
                    $hide_arr[] = absint($term_id);
                }
                update_option('moments_hide', $hide_arr);
            } else {
                $hide_key = array_search(absint($term_id), $hide_arr, true);
                if($hide_key !== false){
                    unset($hide_arr[$hide_key]);
                    update_option('moments_hide', array_values($hide_arr));
                }
            }

            $type = $join_type;

           switch ($type) {
                case 'free':
                    update_term_meta($term_id, 'mo_free_join_type', sanitize_key(is_array($join_data) ? reset($join_data) : $join_data));
                    break;
                case 'pay':
                    $pay_data = array();
                    $has_pay_price = false;
                    $join_data = is_array($join_data) ? $join_data : array();
                    foreach($join_data as $k => $v){
                        if(!is_array($v) || empty($v['name'])){
                            continue;
                        }
                        $price = isset($v['price']) ? round((float)$v['price'], 2) : 0;
                        if($price > 0){
                            $has_pay_price = true;
                        }
                        $pay_data[sanitize_key($v['name'])] = $price;
                    }
                    if(!$has_pay_price){
                        wp_delete_term(absint($term_id), 'moments');
                        wp_send_json(array('status' => 0, 'msg' => '付费圈子至少填写一个金额'));
                    }
                    update_term_meta($term_id, 'mo_join_pay', $pay_data);
                    break;
                case 'limits':
                    $join_data = is_array($join_data) ? $join_data : array();
                    update_term_meta($term_id, 'mo_join_limits', array_map('sanitize_text_field', $join_data));
                    break;
            }

            update_user_join($user_id, $term_id);

            $term_link = get_term_link(absint($term_id), 'moments');
            $msg = array(
                'status' => 1,
                'msg' => '创建成功',
                'term_id' => $term_id,
                'term_link' => !is_wp_error($term_link) ? esc_url_raw($term_link) : ''
            );
        } else {
            $error_msg = $term_data->get_error_message();
            $msg = array('status' => 0, 'msg' => '创建失败：' . $error_msg);
        }

        wp_send_json($msg);
        
    } else {
        $msg = array('status' => 0, 'msg' => '数据不完整');
        wp_send_json($msg);
    }
        
}

// 圈子图片上传
add_action( 'wp_ajax_upload_mos_img', 'upload_mos_img' );
function pix_moment_upload_circle_image($field, $context){
    if(empty($_FILES[$field]) || !isset($_FILES[$field]['error']) || (int) $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE){
        return null;
    }

    $config = pix_upload_get_context($context);
    if(!$config){
        return new WP_Error('invalid_upload_context', '上传场景不存在');
    }

    return pix_upload_insert_attachment($_FILES[$field], $config);
}

function upload_mos_img(){
    if(!is_user_logged_in()){
        wp_send_json(array('status'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    if(!pix_can_create_moment_circle(get_current_user_id())){
        wp_send_json(array('status'=>0,'msg'=>'您没有创建圈子的权限'));
    }

    $banner_img = THEME_URL.'/img/banner.jpg';
    $logo_img = THEME_URL.'/img/modef.png';
    $files_for_limit = array();

    foreach(array('bannerFile', 'logoFile') as $field){
        if(!empty($_FILES[$field]) && isset($_FILES[$field]['error']) && (int) $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE){
            $files_for_limit[] = $_FILES[$field];
        }
    }

    if(!empty($files_for_limit)){
        pix_upload_check_rate_limit(get_current_user_id());
        pix_upload_check_daily_limits(get_current_user_id(), $files_for_limit);
    }

    $banner = pix_moment_upload_circle_image('bannerFile', 'circle_banner');
    if(is_wp_error($banner)){
        wp_send_json(array('status'=>0,'msg'=>$banner->get_error_message()));
    }
    if(!empty($banner['url'])){
        $banner_img = esc_url_raw($banner['url']);
    }

    $logo = pix_moment_upload_circle_image('logoFile', 'circle_logo');
    if(is_wp_error($logo)){
        if(!empty($banner['id'])){
            wp_delete_attachment(absint($banner['id']), true);
        }
        wp_send_json(array('status'=>0,'msg'=>$logo->get_error_message()));
    }
    if(!empty($logo['url'])){
        $logo_img = esc_url_raw($logo['url']);
    }

    if(!empty($files_for_limit)){
        pix_upload_record_daily_usage(get_current_user_id(), count($files_for_limit), pix_upload_files_total_size($files_for_limit));
    }
   
    $msg = array('status' => 1, 'banner_img' => $banner_img, 'logo_img' => $logo_img);

    wp_send_json($msg);
}

// 获取圈子加入申请列表
function get_mo_wait_list($term_id){
    $wait_join = get_term_meta( $term_id, 'mo_wait_join', true );
    $html = '';
    if ( !empty($wait_join ) ) { 
        foreach ( $wait_join as $user ) { 
            $user_id = $user['user_id'];
            $time = $user['time'];
            $user_data = get_user_by( 'id', $user_id );
            $html .= '<div class="mo-wait-item pix-moment-manage-request-item" uid="'.$user_id.'" term_id="'.$term_id.'">
                        <div class="left pix-moment-manage-request-main">
                            <a href="'.get_author_posts_url( $user_id ).'" class="avatar pix-moment-manage-request-avatar"><img src="'.get_u_avatar($user_id,'url').'"></a>
                            <div class="info pix-moment-manage-request-info"><span class="name pix-moment-manage-request-name"><a href="'.get_author_posts_url( $user_id ).'">'.$user_data->display_name.'</a> - 申请加入</span><span class="time pix-moment-manage-request-time">'.$time.'</span></div>
                        </div>
                        <div class="right pix-moment-manage-request-actions">
                            <a href="javascript:;" class="mo-allow-join pix-moment-manage-action pix-moment-manage-action-primary" action="allow">批准</a>
                            <a href="javascript:;" class="mo-refuse-join pix-moment-manage-action pix-moment-manage-action-danger" action="refuse">拒绝</a>
                        </div>
                    </div>';
        } 
    } else { 
        $html = '<div class="nodata pix-moment-manage-empty-state"><img class="pix-moment-manage-empty-img" src="'.THEME_URL.'/img/empty.png"></div>';
    } 

    return $html;
    
}

// 批准加入圈子
add_action( 'wp_ajax_mo_allow_join', 'mo_allow_join' );
function mo_allow_join(){
    if(!is_user_logged_in()){
        wp_send_json(array('status'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $uid = $_POST['uid'];
    $term_id = $_POST['term_id'];
    $type = $_POST['type'];
    $current_user_id = get_current_user_id();

    // 权限检查：必须是管理员或圈子所有者才能审批加入
    $is_admin = current_user_can('manage_options');
    $is_owner = get_term_meta($term_id, 'mo_owner', true) == $current_user_id;

    if (!$is_admin && !$is_owner) {
        wp_send_json(array('status'=>0,'msg'=>'无权操作此申请'));
    }

    // 批准和拒绝都剔除申请列表
    update_wait_join($uid,$term_id);

    // 获取圈子名称
    $term = get_term($term_id, 'moments');
    $term_name = $term ? $term->name : '该圈子';

    if($type == 'allow'){

        // 申请通过更新圈子加入数据
        update_user_join($uid,$term_id);

        // 发送通知
        $term_url = get_term_link($term_id,'moments');
        if (is_wp_error($term_url)) {
            $term_url = home_url();
        }
        $title = '<h3>加入圈子通过</h3>';
        $message = sprintf('<div class="bot-msg-content">您申请加入「%s」已通过，现在可以参与圈子活动了</div>', $term_name);
        $message .= '<div class="bot-msg-bottom"><a href="'.$term_url.'" class="btn-primary">前往圈子</a></div>';
        if (function_exists('ppo_send_private_message')) {
            ppo_send_private_message('moment_bot', $uid, $title . $message);
        }

        $msg = array('status' => 1, 'msg' => '已批准申请');

    } else {

        // 发送拒绝通知
        $title = '<h3>加入圈子被拒绝</h3>';
        $message = sprintf('很遗憾，您申请加入「%s」未通过审核', $term_name);
        if (function_exists('ppo_send_private_message')) {
            ppo_send_private_message('moment_bot', $uid, $title . $message);
        }

        $msg = array('status' => 1, 'msg' => '已拒绝申请');
    }

    wp_send_json($msg);
}

// 更新圈子申请队列
function update_wait_join($user_id,$term_id){
    $wait_join = get_term_meta( $term_id, 'mo_wait_join', true );
    foreach ($wait_join as $key => $value) {
        if ($value['user_id'] == $user_id) {
            unset($wait_join[$key]);
            // 如果只需要删除一个匹配的元素，可以在此后使用break退出循环
            break;
        }
    }
    
    // 重新索引数组
    $wait_join = array_values($wait_join);

    update_term_meta( $term_id, 'mo_wait_join', $wait_join );
}

// 移除圈友
add_action( 'wp_ajax_mo_remove_member', 'mo_remove_member' );
function mo_remove_member(){
    if(!is_user_logged_in()){
        wp_send_json(array('status'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $uid = isset($_POST['uid']) ? absint($_POST['uid']) : 0;
    $term_id = isset($_POST['term_id']) ? absint($_POST['term_id']) : 0;
    $reason = isset($_POST['reason']) ? sanitize_text_field(wp_unslash($_POST['reason'])) : '';

    if(!$uid || !$term_id){
        wp_send_json(array('status'=>0,'msg'=>'缺少圈友或圈子信息'));
    }

    if(!ppo_moment_can_manage_term($term_id)){
        wp_send_json(array('status'=>0,'msg'=>'无权管理此圈子'));
    }

    $owner_id = absint(get_term_meta($term_id, 'mo_owner', true));
    if($owner_id === $uid){
        wp_send_json(array('status'=>0,'msg'=>'不能移除圈主'));
    }

    $term_joined = get_term_meta($term_id, 'mo_joined', true);
    $term_joined_arr = array_values(array_filter(array_map('absint', explode(',', (string)$term_joined))));
    $new_term_joined_arr = array_values(array_diff($term_joined_arr, array($uid)));

    $user_joined = get_user_meta($uid, 'user_mo_joined', true);
    $user_joined_arr = array_values(array_filter(array_map('absint', explode(',', (string)$user_joined))));
    $new_user_joined_arr = array_values(array_diff($user_joined_arr, array($term_id)));

    if(count($new_term_joined_arr) === count($term_joined_arr) && count($new_user_joined_arr) === count($user_joined_arr)){
        wp_send_json(array('status'=>0,'msg'=>'该用户不在圈子中'));
    }

    update_term_meta($term_id, 'mo_joined', implode(',', $new_term_joined_arr));
    update_term_meta($term_id, 'mo_joined_count', count($new_term_joined_arr));
    update_user_meta($uid, 'user_mo_joined', implode(',', $new_user_joined_arr));
    update_user_meta($uid, 'user_mo_joined_count', count($new_user_joined_arr));

    $term = get_term($term_id, 'moments');
    $term_name = ($term && !is_wp_error($term)) ? $term->name : '该圈子';

    if(function_exists('ppo_send_private_message')){
        $title = '<h3>已被移出圈子</h3>';
        $message = sprintf('<div class="bot-msg-content">您已被移出「%s」</div>', esc_html($term_name));
        if($reason){
            $message .= sprintf('<div class="bot-msg-content">原因：%s</div>', esc_html($reason));
        }
        ppo_send_private_message('moment_bot', $uid, $title . $message);
    }

    wp_send_json(array('status'=>1,'msg'=>'已移除圈友'));
}

// 获取待审片刻
function get_pending_moment($term_id,$base_url,$total_pages,$current){
    $current = $current ? $current : 1;
    $posts_html = '';
    $type = array(
        'type' => 'pending',
    );
    $args = array(
        'post_type' => 'moment',
        'post_status' => 'pending',
        'posts_per_page' => 10,
        'paged' => $current,
        'tax_query' => array(
            array(
                'taxonomy' => 'moments',
                'field'    => 'term_id',
                'terms'    => $term_id,
            ),
        ),
    );

    $query = new WP_Query( $args );

    if( $query->have_posts() ) {
        ob_start();
        while ($query->have_posts()) : $query->the_post();
        get_template_part( 'tpl/content','moment',$type);
    endwhile; 
    $posts_html = ob_get_contents(); // we pass the posts to variable
    ob_end_clean(); // clear the buffer
    wp_reset_postdata(); 
    } else {
        $posts_html = '<div class="nodata pix-moment-manage-empty-state"><img class="pix-moment-manage-empty-img" src="'.THEME_URL.'/img/empty.png"></div>';
    }

    $total_pages = $query->max_num_pages;
    $base_url = $base_url ? $base_url : home_url('/moment-manage?term_id='.$term_id.'');

    $page_nav_html = gl_paginate($base_url,$total_pages,$current); 

    return $posts_html.'<div class="gl-paginate pix-moment-manage-pagination pix-moment-manage-review-pagination" action="mo_manage_review_nav" total="'.$total_pages.'" term_id="'.$term_id.'" content=".moment-manage-inner">'.$page_nav_html.'</div>';

}

// 片刻审核
add_action( 'wp_ajax_mo_pending_check', 'mo_pending_check' );
function mo_pending_check(){
    if(!is_user_logged_in()){
        wp_send_json(array('status'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $pid = $_POST['pid'];
    $type = $_POST['type'];
    $uid = $_POST['uid'];
    $current_user_id = get_current_user_id();

    // 获取片刻所属的圈子
    $term_id = get_category_id_by_post_id($pid, 'moments');
    if (!$term_id) {
        wp_send_json(array('status'=>0,'msg'=>'无法获取圈子信息'));
    }

    // 权限检查：必须是管理员或圈子所有者才能审核
    $is_admin = current_user_can('manage_options');
    $is_owner = get_term_meta($term_id, 'mo_owner', true) == $current_user_id;

    if (!$is_admin && !$is_owner) {
        wp_send_json(array('status'=>0,'msg'=>'无权操作此审核'));
    }

    if($type == 'allow'){

        $post_data = array(
            'ID' => $pid,
            'post_status' => 'publish',
        );

        wp_update_post($post_data);
        ppo_notify_moment_result($pid, 'approved');
        $msg = array('status' => 1, 'msg' => '通过审核');

    } else {

        ppo_notify_moment_result($pid, 'rejected');
        wp_delete_post($pid, true);

        $msg = array('status' => 1, 'msg' => '删除已片刻');
    }

    wp_send_json($msg);
}

// 片刻鉴权 判断是否是圈主或管理员或文章作者
function moment_auth($pid,$user_id,$term_id){
    $admin = current_user_can('manage_options');
    $author_id = '';
    $term_id = $term_id ? $term_id : false;
    //如果有pid则获取圈子id和作者id
    if($pid){
        $term_id = get_category_id_by_post_id($pid,'moments');
        $author_id = get_post_field('post_author', $pid);
    }

    $owner = get_term_meta($term_id, 'mo_owner', true);
    
    if($user_id == $owner || $user_id == $author_id || $admin){
        return true;
    } else {
        return false;
    }
    
}

function ppo_moment_can_manage_term($term_id, $user_id = null){
    $term_id = absint($term_id);
    if(!$term_id){
        return false;
    }

    $term = get_term($term_id, 'moments');
    if(!$term || is_wp_error($term)){
        return false;
    }

    $user_id = $user_id ? absint($user_id) : get_current_user_id();
    if(!$user_id){
        return false;
    }

    return current_user_can('manage_options') || (int)get_term_meta($term_id, 'mo_owner', true) === (int)$user_id;
}

// 根据文章id获取分类id
function get_category_id_by_post_id($post_id,$type) {
    $terms = get_the_terms($post_id, $type); // 'category' 是 WordPress 的默认分类 taxonomy
    if (!is_wp_error($terms) && !empty($terms)) {
        $category_id = $terms[0]->term_id; // 获取第一个分类的ID
        return $category_id;
    }
    return false; // 如果没有找到分类或出错，返回false
}

// 获取圈子圈友列表
function get_mo_manage_user($term_id,$base_url,$total_pages,$current){
    $users = get_term_meta( $term_id, 'mo_joined', true );
    $users = array_filter(array_map('absint', explode(',', (string)$users)));
    $html = '';
    $base_url = $base_url ? $base_url : home_url('/moment-manage?term_id='.$term_id.'');    
    $per_page = 10;
    $current = $current ? $current : 1;

    $user_query = new WP_User_Query( array( 
        'include' => $users,
        'number' => $per_page,
        'paged' => $current,
        ) );

        $max = $user_query->get_total();
        $total_pages = $total_pages ? $total_pages : ceil($max / $per_page);


        foreach ( $user_query->results as $user ) {
            $html .= '<div class="mo-user-item pix-moment-manage-user-item">
                    <div class="left pix-moment-manage-user-main">
                        <a class="avatar pix-moment-manage-user-avatar" href="'.get_author_posts_url( $user->ID ).'"><img src="' . get_u_avatar($user->ID,'url') . '"></a>
                        <div class="info pix-moment-manage-user-info">
                            <a href="'.get_author_posts_url( $user->ID ).'">' . $user->display_name . '</a>
                        </div>
                    </div>

                    <div class="right pix-moment-manage-user-action">
                        <a href="javascript:void(0);" class="mo-del-user pix-moment-manage-action pix-moment-manage-action-danger" uid="' . $user->ID . '" term_id="' . $term_id . '">移除圈子</a>
                    </div>
                        
                    </div>';
        }

        if(empty($html)){
            $html = '<div class="nodata pix-moment-manage-empty-state"><img class="pix-moment-manage-empty-img" src="'.THEME_URL.'/img/empty.png"></div>';
        }


        $page_nav_html = gl_paginate($base_url,$total_pages,$current); 

        return $html.'<div class="gl-paginate pix-moment-manage-pagination pix-moment-manage-user-pagination" action="mo_manage_user_nav" total="'.$total_pages.'" term_id="'.$term_id.'" content=".moment-manage-inner">'.$page_nav_html.'</div>';

}

// 圈友ajax分页
function mo_manage_user_nav(){
    if(!is_user_logged_in()){
        wp_send_json(array('status'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $term_id = isset($_POST['term_id']) ? absint($_POST['term_id']) : 0;
    if(!ppo_moment_can_manage_term($term_id)){
        wp_send_json(array('status'=>0,'msg'=>'无权管理此圈子'));
    }

    $base_url = isset($_POST['baseurl']) ? esc_url_raw(wp_unslash($_POST['baseurl'])) : false;
    $total_pages = isset($_POST['total']) ? absint($_POST['total']) : false;
    $current = isset($_POST['paged']) ? max(1, absint($_POST['paged'])) : 1;

   $html = get_mo_manage_user($term_id,$base_url,$total_pages,$current);

    wp_send_json( array('status' => 1, 'html' => $html) );

}
add_action( 'wp_ajax_mo_manage_user_nav', 'mo_manage_user_nav' );

// ajax待审翻页
function mo_manage_review_nav(){
    if(!is_user_logged_in()){
        wp_send_json(array('status'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $term_id = isset($_POST['term_id']) ? absint($_POST['term_id']) : 0;
    if(!ppo_moment_can_manage_term($term_id)){
        wp_send_json(array('status'=>0,'msg'=>'无权管理此圈子'));
    }

    $base_url = isset($_POST['baseurl']) ? esc_url_raw(wp_unslash($_POST['baseurl'])) : false;
    $total_pages = isset($_POST['total']) ? absint($_POST['total']) : false;
    $current = isset($_POST['paged']) ? max(1, absint($_POST['paged'])) : 1;

   $html = get_pending_moment($term_id,$base_url,$total_pages,$current);

    wp_send_json( array('status' => 1, 'html' => $html) );

}
add_action( 'wp_ajax_mo_manage_review_nav', 'mo_manage_review_nav' );

// 全局页码分页
function gl_paginate($base_url,$total_pages,$current){
    ob_start(); 
    echo paginate_links(array(
        'base' => trailingslashit($base_url) . 'page/%#%/',
        'format' => '?paged=%#%',
        'current' => $current,
        'total' => $total_pages,
        'end_size' => 1,
        'mid_size' => 1,
        'prev_text' => '上一页',
        'next_text' => '下一页',
        'type' => 'list'
    ));

    $page_nav_html = ob_get_clean(); 

    return $page_nav_html;
}


// 圈子管理页面内容
function mo_manage_content(){
    if(!is_user_logged_in()){
        wp_send_json(array('status'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('moment_ajax', 'security');

    $term_id = isset($_POST['term_id']) ? absint($_POST['term_id']) : 0;
    if(!ppo_moment_can_manage_term($term_id)){
        wp_send_json(array('status'=>0,'msg'=>'无权管理此圈子'));
    }

    $type = isset($_POST['ajax_type']) ? sanitize_key($_POST['ajax_type']) : '';
    $base_url = '';
    $current = 1;
    $total_pages = '';
    $output = '';

    switch ($type) {
        case 'review_mo':
            $output = get_pending_moment($term_id,$base_url,$total_pages,$current);
            break;
        
        case 'review_join':
            $output = get_mo_wait_list($term_id);
            break;

        case 'review_mo_user':
            $output = get_mo_manage_user($term_id,$base_url,$total_pages,$current);
            break;    
        default:
            wp_send_json(array('status' => 0, 'msg' => '请求类型错误'));
    }

    if(!empty($output)){
        wp_send_json(array('status' => 1, 'html' => $output));
    } else {
        wp_send_json(array('status' => 0, 'msg' => '没有数据'));
    }
}
add_action( 'wp_ajax_mo_manage_content', 'mo_manage_content' );

// 圈子待加入提示
function mo_wait_join_notice($term_id){
    $wait_join = get_term_meta( $term_id, 'mo_wait_join', true );
    $wait_join = !empty($wait_join) ? $wait_join : array();
    $count = count($wait_join);
    if($count > 0){
        return '<span class="mo-pending-notice pix-moment-manage-notice"><div class="dot pix-moment-manage-notice-dot"></div></span>';
    } else {
        return '';
    }
}

// 圈子待审片刻入提示
function mo_posts_notice($term_id){
    $args = array(
        'post_type' => 'moment',
        'post_status' => 'pending',
        'tax_query' => array(
            array(
                'taxonomy' => 'moments',
                'field'    => 'term_id',
                'terms'    => $term_id,
            ),
        ),
    );

    $query = new WP_Query( $args );
    $max = $query->found_posts;

    if($max > 0){
        return '<span class="mo-pending-notice pix-moment-manage-notice"><div class="dot pix-moment-manage-notice-dot"></div></span>';
    } else {
        return '';
    }
}

function mo_all_notice_dot($term_id){
    $join = mo_wait_join_notice($term_id);
    $posts = mo_posts_notice($term_id);
    if($join > 0 || $posts > 0){
        return '<span class="mo-pending-notice pix-moment-manage-notice"><div class="dot pix-moment-manage-notice-dot"></div></span>';
    } else {
        return '';
    }
}

// ==================== 圈子删除清理 ====================

function ppo_cleanup_circle_metas($term_id) {
    $term_id = (int)$term_id;
    if ($term_id <= 0) return false;

    $meta_keys = array(
        'mo_owner',
        'mo_cat_banner',
        'mo_cat_img',
        'mo_joined',
        'mo_joined_count',
        'mo_wait_join',
        'mo_join_type',
        'mo_free_join_type',
        'mo_join_pay',
        'mo_pay_credit_only',
        'mo_join_limits',
        'mo_join_fun',
        'mo_set_type',
        'mo_show_type',
        'show_num',
        'mo_text_num',
    );

    foreach ($meta_keys as $key) {
        delete_term_meta($term_id, $key);
    }

    return true;
}
add_action('delete_moments', 'ppo_cleanup_circle_metas');

function ppo_delete_moment_on_circle_delete($term_id) {
    $term = get_term($term_id);
    if (!$term || is_wp_error($term) || $term->taxonomy !== 'moments') return;

    $args = array(
        'post_type' => 'moment',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'moments',
                'field' => 'term_id',
                'terms' => $term_id,
            ),
        ),
    );

    $posts = get_posts($args);

    foreach ($posts as $post) {
        wp_delete_post($post->ID, true);
    }

    ppo_cleanup_circle_metas($term_id);
}
add_action('delete_term', 'ppo_delete_moment_on_circle_delete', 10, 1);

// ==================== REST API Functions ====================

function ppo_rest_get_moment_post($id) {
    $id = intval($id);
    if ($id <= 0) {
        return null;
    }

    $post = get_post($id);
    if (!$post || $post->post_type !== 'moment') {
        return null;
    }

    return $post;
}

function ppo_rest_get_moments($request) {
    $page = max(1, absint($request->get_param('page') ?: 1));
    $per_page = min(30, max(1, absint($request->get_param('per_page') ?: 10)));
    $cat = absint($request->get_param('category') ?: 0);
    $filter = sanitize_key($request->get_param('filter') ?: 'new');

    $sticky = get_option('sticky_posts');
    $params = array(
        'post_type' => 'moment',
        'post_status' => 'publish',
        'paged' => $page,
        'posts_per_page' => $per_page,
    );

    if ($cat > 0) {
        $params['tax_query'] = array(
            array(
                'taxonomy' => 'moments',
                'field' => 'term_id',
                'terms' => $cat,
            ),
        );
    }

    if ($filter === 'new') {
        $params['orderby'] = 'date';
        $params['order'] = 'DESC';
    } elseif ($filter === 'hot') {
        $params['meta_key'] = 'moment_hot';
        $params['meta_value'] = '1';
        $params['orderby'] = 'date';
    }

    $query = new WP_Query($params);
    $moments = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $pid = get_the_ID();
            $mo_type = get_post_meta($pid, 'moment_type', true);
            $term = get_the_terms($pid, 'moments');
            $term = !empty($term) ? $term[0] : null;
            $author = get_userdata(get_post_field('post_author', $pid));

            $moment = array(
                'id' => $pid,
                'title' => get_the_title(),
                'content' => get_post_field('post_content', $pid),
                'type' => $mo_type,
                'author' => array(
                    'id' => $author->ID,
                    'name' => $author->display_name,
                    'avatar' => get_u_avatar($author->ID, 'url'),
                    'url' => get_author_posts_url($author->ID),
                ),
                'category' => $term ? array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                ) : null,
                'date' => get_the_date('c'),
                'comment_count' => get_comments_number($pid),
                'like_count' => get_moment_like_count($pid),
                'is_liked' => is_moment_liked($pid),
            );

            $moments[] = $moment;
        }
        wp_reset_postdata();
    }

    return array(
        'moments' => $moments,
        'total' => $query->found_posts,
        'total_pages' => $query->max_num_pages,
        'current_page' => $page,
    );
}

function ppo_rest_get_moment($request) {
    $id = intval($request->get_param('id'));

    $post = ppo_rest_get_moment_post($id);
    if (!$post) {
        return new WP_Error('not_found', '片刻不存在', array('status' => 404));
    }

    $mo_type = get_post_meta($id, 'moment_type', true);
    $term = get_the_terms($id, 'moments');
    $term = !empty($term) ? $term[0] : null;
    $author = get_userdata($post->post_author);

    $moment = array(
        'id' => $id,
        'title' => $post->post_title,
        'content' => $post->post_content,
        'type' => $mo_type,
        'data' => get_post_meta($id, 'moment_' . $mo_type, true),
        'author' => array(
            'id' => $author->ID,
            'name' => $author->display_name,
            'avatar' => get_u_avatar($author->ID, 'url'),
            'url' => get_author_posts_url($author->ID),
        ),
        'category' => $term ? array(
            'id' => $term->term_id,
            'name' => $term->name,
        ) : null,
        'date' => $post->post_date,
        'comment_count' => get_comments_number($id),
        'like_count' => get_moment_like_count($id),
        'is_liked' => is_moment_liked($id),
        'is_owner' => moment_auth($id, get_current_user_id(), false),
    );

    return $moment;
}

function ppo_rest_create_moment($request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('unauthorized', '请先登录', array('status' => 401));
    }

    if (function_exists('pix_content_submission_guard')) {
        $guard = pix_content_submission_guard('moment', $request->get_param('logincaptcha'), $request->get_param('pix_guard'));
        if (!empty($guard['code'])) {
            return new WP_Error('content_guard_failed', $guard['msg'], array('status' => 400));
        }
    }

    $content = wp_kses_post((string) $request->get_param('content'));
    $title = sanitize_text_field((string) $request->get_param('title'));
    $cat_id = absint($request->get_param('category_id'));
    $tag_id = absint($request->get_param('tag_id'));
    $moment_type = $request->get_param('type') ? sanitize_key($request->get_param('type')) : 'text';
    $moment_data = $request->get_param('moment_data');
    $allowed_types = array('text', 'gallery', 'card', 'audio', 'video', 'file');

    if (trim(wp_strip_all_tags($content)) === '') {
        return new WP_Error('missing_content', '内容不能为空', array('status' => 400));
    }

    if (!in_array($moment_type, $allowed_types, true)) {
        return new WP_Error('invalid_type', '片刻类型不正确', array('status' => 400));
    }

    $term = $cat_id ? get_term($cat_id, 'moments') : false;
    if (!$cat_id || !$term || is_wp_error($term)) {
        return new WP_Error('invalid_category', '请选择有效圈子', array('status' => 400));
    }

    if ($tag_id > 0) {
        $tag = get_term($tag_id, 'moment_tag');
        if (!$tag || is_wp_error($tag)) {
            return new WP_Error('invalid_tag', '话题不存在', array('status' => 400));
        }
    }

    $is_admin = current_user_can('manage_options');
    $is_owner = get_term_meta($cat_id, 'mo_owner', true) == $user_id;
    if (function_exists('pix_user_is_plain_user') && pix_user_is_plain_user($user_id)) {
        $allow_moment = function_exists('pix_normal_user_can') ? pix_normal_user_can('normal_user_allow_moment', true, $user_id) : true;
        if (!$allow_moment) {
            return new WP_Error('normal_user_moment_disabled', '普通用户暂不能发布片刻', array('status' => 403));
        }
    }

    if (!$is_admin && !$is_owner && !pix_moment_user_can_post_to_term($user_id, $cat_id)) {
        return new WP_Error('not_joined', '请先加入该圈子后再发布', array('status' => 403));
    }

    $forbidden_word = pix_check_forbidden_words($content);
    if ($forbidden_word) {
        return new WP_Error('forbidden_words', pix_forbidden_words_message($forbidden_word), array('status' => 400));
    }

    $max_word = mo_word_max($cat_id);
    $max = isset($max_word['max']) ? (int) $max_word['max'] : 800;
    $min = isset($max_word['min']) ? (int) $max_word['min'] : 0;
    $content_length = pix_moment_content_length($content);
    if ($content_length > $max) {
        return new WP_Error('content_too_long', '内容不能超过' . $max . '个字', array('status' => 400));
    }
    if ($content_length < $min) {
        return new WP_Error('content_too_short', '内容不能少于' . $min . '个字', array('status' => 400));
    }

    $validated_data = pix_moment_validate_payload($moment_type, $moment_data, $user_id, $cat_id);
    if (is_wp_error($validated_data)) {
        return new WP_Error($validated_data->get_error_code(), $validated_data->get_error_message(), array('status' => 400));
    }

    $push_data = array(
        'moment_type' => $moment_type,
    );

    switch ($moment_type) {
        case 'gallery':
            $push_data['moment_ga'] = mo_gallery_data($validated_data);
            break;
        case 'card':
            $push_data['moment_card'] = $validated_data;
            break;
        case 'audio':
            $push_data['moment_audio'] = $validated_data;
            break;
        case 'video':
            $push_data['moment_video'] = mo_video_data($validated_data);
            break;
        case 'file':
            $push_data['moment_file'] = mo_file_data($validated_data);
            break;
    }

    $post_status = ($is_admin || $is_owner) ? 'publish' : 'pending';

    $insert_moment = array(
        'post_title' => $title,
        'post_type' => 'moment',
        'post_author' => $user_id,
        'post_content' => $content,
        'post_status' => $post_status,
        'comment_status' => 'open',
        'tax_input' => array(
            'moments' => array($cat_id),
            'moment_tag' => array($tag_id),
        ),
        'meta_input' => $push_data,
    );

    $res = wp_insert_post($insert_moment);

    if (is_wp_error($res)) {
        return new WP_Error('create_failed', $res->get_error_message(), array('status' => 500));
    }

    return array(
        'status' => 1,
        'message' => $post_status === 'pending' ? '片刻发布成功，等待审核' : '片刻发布成功',
        'id' => $res,
        'post_status' => $post_status,
        'url' => get_permalink($res),
    );
}

function ppo_rest_update_moment($request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('unauthorized', '请先登录', array('status' => 401));
    }

    $id = intval($request->get_param('id'));

    if (!ppo_rest_get_moment_post($id)) {
        return new WP_Error('not_found', '片刻不存在', array('status' => 404));
    }

    if (!moment_auth($id, $user_id, false)) {
        return new WP_Error('forbidden', '无权修改此片刻', array('status' => 403));
    }

    $post = get_post($id);
    $content = $request->has_param('content') ? wp_kses_post((string) $request->get_param('content')) : $post->post_content;
    $title = $request->has_param('title') ? sanitize_text_field((string) $request->get_param('title')) : $post->post_title;
    $cat_id = absint($request->get_param('category_id') ?: get_category_id_by_post_id($id, 'moments'));
    $tag_id = absint($request->get_param('tag_id') ?: 0);
    $moment_type = $request->get_param('type') ? sanitize_key($request->get_param('type')) : get_post_meta($id, 'moment_type', true);
    $moment_data = $request->get_param('moment_data');
    $allowed_types = array('text', 'gallery', 'card', 'audio', 'video', 'file');

    if (!in_array($moment_type, $allowed_types, true)) {
        return new WP_Error('invalid_type', '片刻类型不正确', array('status' => 400));
    }

    $term = $cat_id ? get_term($cat_id, 'moments') : false;
    if (!$cat_id || !$term || is_wp_error($term)) {
        return new WP_Error('invalid_category', '请选择有效圈子', array('status' => 400));
    }

    if ($tag_id > 0) {
        $tag = get_term($tag_id, 'moment_tag');
        if (!$tag || is_wp_error($tag)) {
            return new WP_Error('invalid_tag', '话题不存在', array('status' => 400));
        }
    }

    $is_admin = current_user_can('manage_options');
    $is_owner = get_term_meta($cat_id, 'mo_owner', true) == $user_id;
    if (!$is_admin && !$is_owner && !pix_moment_user_can_post_to_term($user_id, $cat_id)) {
        return new WP_Error('not_joined', '请先加入该圈子后再发布', array('status' => 403));
    }

    $forbidden_word = pix_check_forbidden_words($content);
    if ($forbidden_word) {
        return new WP_Error('forbidden_words', pix_forbidden_words_message($forbidden_word), array('status' => 400));
    }

    $max_word = mo_word_max($cat_id);
    $max = isset($max_word['max']) ? (int) $max_word['max'] : 800;
    $min = isset($max_word['min']) ? (int) $max_word['min'] : 0;
    $content_length = pix_moment_content_length($content);
    if ($content_length > $max) {
        return new WP_Error('content_too_long', '内容不能超过' . $max . '个字', array('status' => 400));
    }
    if ($content_length < $min) {
        return new WP_Error('content_too_short', '内容不能少于' . $min . '个字', array('status' => 400));
    }

    $validated_data = $request->has_param('moment_data') || $request->has_param('type')
        ? pix_moment_validate_payload($moment_type, $moment_data, $user_id, $cat_id)
        : null;
    if (is_wp_error($validated_data)) {
        return new WP_Error($validated_data->get_error_code(), $validated_data->get_error_message(), array('status' => 400));
    }

    $update_data = array(
        'ID' => $id,
        'post_title' => $title,
        'post_content' => $content,
        'post_status' => ($is_admin || $is_owner) ? 'publish' : 'pending',
    );

    if ($validated_data !== null) {
        update_post_meta($id, 'moment_type', $moment_type);
        switch ($moment_type) {
            case 'gallery':
                update_post_meta($id, 'moment_ga', mo_gallery_data($validated_data));
                break;
            case 'card':
                update_post_meta($id, 'moment_card', $validated_data);
                break;
            case 'audio':
                update_post_meta($id, 'moment_audio', $validated_data);
                break;
            case 'video':
                update_post_meta($id, 'moment_video', mo_video_data($validated_data));
                break;
            case 'file':
                update_post_meta($id, 'moment_file', mo_file_data($validated_data));
                break;
        }
    }

    $res = wp_update_post($update_data);

    if (is_wp_error($res)) {
        return new WP_Error('update_failed', $res->get_error_message(), array('status' => 500));
    }

    wp_set_object_terms($id, array($cat_id), 'moments');
    if ($tag_id > 0) {
        wp_set_object_terms($id, array($tag_id), 'moment_tag');
    }

    return array(
        'status' => 1,
        'message' => $update_data['post_status'] === 'pending' ? '更新成功，等待审核' : '更新成功',
        'post_status' => $update_data['post_status'],
        'url' => get_permalink($id),
    );
}

function ppo_rest_delete_moment($request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('unauthorized', '请先登录', array('status' => 401));
    }

    $id = intval($request->get_param('id'));

    if (!ppo_rest_get_moment_post($id)) {
        return new WP_Error('not_found', '片刻不存在', array('status' => 404));
    }

    if (!moment_auth($id, $user_id, false)) {
        return new WP_Error('forbidden', '无权删除此片刻', array('status' => 403));
    }

    $res = wp_delete_post($id, true);

    if (!$res) {
        return new WP_Error('delete_failed', '删除失败', array('status' => 500));
    }

    return array(
        'status' => 1,
        'message' => '删除成功',
    );
}

function ppo_rest_like_moment($request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('unauthorized', '请先登录', array('status' => 401));
    }

    $id = intval($request->get_param('id'));
    $post = ppo_rest_get_moment_post($id);

    if (!$post) {
        return new WP_Error('not_found', '片刻不存在', array('status' => 404));
    }

    $likes = get_post_meta($id, 'moment_likes', true);
    $likes = is_array($likes) ? array_values(array_unique(array_map('absint', $likes))) : array();
    $user_like = get_user_meta($user_id, 'post_likes', true);
    $user_like = is_array($user_like) ? array_values(array_unique(array_map('absint', $user_like))) : array();

    if (!in_array($user_id, $likes)) {
        $likes[] = $user_id;
        $likes = array_values(array_unique($likes));
        update_post_meta($id, 'moment_likes', $likes);

        if (!in_array($id, $user_like, true)) {
            $user_like[] = $id;
            update_user_meta($user_id, 'post_likes', array_values(array_unique($user_like)));
        }

        if ((int) $post->post_author !== $user_id) {
            ppo_msg_add(array(
                'receive_user' => $post->post_author,
                'send_id' => $user_id,
                'type' => 'post_like',
                'title' => '赞了您的片刻',
                'content' => '',
                'related_id' => $id,
            ));
        }

        do_action('ppo_like_content', $user_id, '片刻', $id);
    }

    update_post_meta($id, 'likes_count', count($likes));

    return array(
        'status' => 1,
        'message' => '已点赞',
        'like_count' => count($likes),
    );
}

function ppo_rest_unlike_moment($request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('unauthorized', '请先登录', array('status' => 401));
    }

    $id = intval($request->get_param('id'));
    $post = ppo_rest_get_moment_post($id);

    if (!$post) {
        return new WP_Error('not_found', '片刻不存在', array('status' => 404));
    }

    $likes = get_post_meta($id, 'moment_likes', true);
    $likes = is_array($likes) ? array_values(array_unique(array_map('absint', $likes))) : array();

    $likes = array_values(array_filter($likes, function($uid) use ($user_id) {
        return $uid != $user_id;
    }));

    update_post_meta($id, 'moment_likes', $likes);
    update_post_meta($id, 'likes_count', count($likes));

    $user_like = get_user_meta($user_id, 'post_likes', true);
    $user_like = is_array($user_like) ? array_values(array_unique(array_map('absint', $user_like))) : array();
    $user_like = array_values(array_filter($user_like, function($pid) use ($id) {
        return $pid != $id;
    }));
    update_user_meta($user_id, 'post_likes', $user_like);
    ppo_msg_delete_like_post($user_id, $post->post_author, $id);

    return array(
        'status' => 1,
        'message' => '已取消点赞',
        'like_count' => count($likes),
    );
}

function get_moment_like_count($pid) {
    $likes = get_post_meta($pid, 'moment_likes', true);
    return is_array($likes) ? count($likes) : 0;
}

function is_moment_liked($pid) {
    $user_id = get_current_user_id();
    if (!$user_id) return false;

    $likes = get_post_meta($pid, 'moment_likes', true);
    return is_array($likes) && in_array($user_id, $likes);
}

// ==================== 内容举报功能 ====================

function ppo_report_moment($request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('unauthorized', '请先登录', array('status' => 401));
    }

    $pid = $request->get_param('id');
    $reason = $request->get_param('reason');
    $description = $request->get_param('description');

    $post = get_post($pid);
    if (!$post || $post->post_type !== 'moment') {
        return new WP_Error('not_found', '片刻不存在', array('status' => 404));
    }

    if (empty($reason)) {
        return new WP_Error('missing_reason', '请选择举报原因', array('status' => 400));
    }

    $reports = get_post_meta($pid, 'moment_reports', true);
    $reports = is_array($reports) ? $reports : array();

    foreach ($reports as $report) {
        if ($report['user_id'] == $user_id && $report['status'] === 'pending') {
            return new WP_Error('already_reported', '您已经举报过此内容，请等待处理', array('status' => 400));
        }
    }

    $new_report = array(
        'user_id' => $user_id,
        'reason' => sanitize_text_field($reason),
        'description' => sanitize_textarea_field($description),
        'time' => current_time('mysql'),
        'status' => 'pending',
    );

    $reports[] = $new_report;
    update_post_meta($pid, 'moment_reports', $reports);

    $report_count = get_post_meta($pid, 'moment_report_count', true);
    update_post_meta($pid, 'moment_report_count', intval($report_count) + 1);

    return array(
        'status' => 1,
        'message' => '举报成功，我们会尽快处理',
    );
}

function ajax_report_moment() {
    check_ajax_referer('moment_ajax', 'security');

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json(array('status' => 0, 'msg' => '请先登录'));
    }

    $pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
    $reason = isset($_POST['reason']) ? sanitize_text_field($_POST['reason']) : '';
    $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';

    $post = get_post($pid);
    if (!$post || $post->post_type !== 'moment') {
        wp_send_json(array('status' => 0, 'msg' => '片刻不存在'));
    }

    if (empty($reason)) {
        wp_send_json(array('status' => 0, 'msg' => '请选择举报原因'));
    }

    $reports = get_post_meta($pid, 'moment_reports', true);
    $reports = is_array($reports) ? $reports : array();

    foreach ($reports as $report) {
        if ($report['user_id'] == $user_id && $report['status'] === 'pending') {
            wp_send_json(array('status' => 0, 'msg' => '您已经举报过此内容，请等待处理'));
        }
    }

    $new_report = array(
        'user_id' => $user_id,
        'reason' => $reason,
        'description' => $description,
        'time' => current_time('mysql'),
        'status' => 'pending',
    );

    $reports[] = $new_report;
    update_post_meta($pid, 'moment_reports', $reports);

    $report_count = get_post_meta($pid, 'moment_report_count', true);
    update_post_meta($pid, 'moment_report_count', intval($report_count) + 1);

    wp_send_json(array('status' => 1, 'msg' => '举报成功，我们会尽快处理'));
}
add_action('wp_ajax_report_moment', 'ajax_report_moment');

function get_moment_report_reasons() {
    return array(
        'spam' => '垃圾信息',
        'violence' => '暴力血腥',
        'porn' => '色情低俗',
        'politics' => '政治敏感',
        'copyright' => '侵权抄袭',
        'other' => '其他',
    );
}

function check_user_reported_moment($pid, $user_id) {
    $reports = get_post_meta($pid, 'moment_reports', true);
    if (!is_array($reports)) return false;

    foreach ($reports as $report) {
        if ($report['user_id'] == $user_id && $report['status'] === 'pending') {
            return true;
        }
    }
    return false;
}

// ==================== 审核结果通知功能 ====================

function ppo_add_moment_notification($user_id, $type, $moment_id, $extra = array()) {
    $notifications = get_user_meta($user_id, 'moment_notifications', true);
    $notifications = is_array($notifications) ? $notifications : array();

    $notification = array(
        'type' => $type,
        'moment_id' => $moment_id,
        'time' => current_time('mysql'),
        'read' => false,
    );

    if (!empty($extra)) {
        $notification = array_merge($notification, $extra);
    }

    $notifications[] = $notification;
    update_user_meta($user_id, 'moment_notifications', $notifications);

    $notification_count = get_user_meta($user_id, 'moment_notification_count', true);
    update_user_meta($user_id, 'moment_notification_count', intval($notification_count) + 1);

    return true;
}

function ppo_get_moment_notifications($user_id, $limit = 20) {
    $notifications = get_user_meta($user_id, 'moment_notifications', true);
    if (!is_array($notifications)) return array();

    $notifications = array_slice($notifications, -$limit, $limit);
    return array_reverse($notifications);
}

function ppo_mark_notification_read($user_id, $index = -1) {
    $notifications = get_user_meta($user_id, 'moment_notifications', true);
    if (!is_array($notifications)) return false;

    if ($index >= 0 && isset($notifications[$index])) {
        $notifications[$index]['read'] = true;
    } else {
        foreach ($notifications as $key => $notif) {
            $notifications[$key]['read'] = true;
        }
    }

    update_user_meta($user_id, 'moment_notifications', $notifications);
    update_user_meta($user_id, 'moment_notification_count', 0);

    return true;
}

function ppo_get_unread_notification_count($user_id) {
    return get_user_meta($user_id, 'moment_notification_count', true) ?: 0;
}

function ppo_notify_moment_result($pid, $result) {
    $post = get_post($pid);
    if (!$post) return false;

    $author_id = $post->post_author;
    $moment_title = mb_substr($post->post_title, 0, 20, 'utf-8');
    if (mb_strlen($post->post_title, 'utf-8') > 20) {
        $moment_title .= '...';
    }

    if ($result === 'approved') {
        $notification_type = 'moment_approved';
        $title = '<h3>审核通过</h3>';
        $message = sprintf('您的片刻「%s」已审核通过', $moment_title);
    } elseif ($result === 'rejected') {
        $notification_type = 'moment_rejected';
        $title = '<h3>审核未通过</h3>';
        $message = sprintf('您的片刻「%s」未通过审核', $moment_title);
    } else {
        return false;
    }

    ppo_add_moment_notification($author_id, $notification_type, $pid, array(
        'message' => $message,
    ));

    ppo_send_private_message('moment_bot', $author_id, $title . $message);

    return true;
}

function ppo_get_notifications_api($request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('unauthorized', '请先登录', array('status' => 401));
    }

    $limit = $request->get_param('limit') ? (int)$request->get_param('limit') : 20;
    $notifications = ppo_get_moment_notifications($user_id, $limit);

    return array(
        'notifications' => $notifications,
        'unread_count' => ppo_get_unread_notification_count($user_id),
    );
}

function ppo_mark_notifications_read_api($request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('unauthorized', '请先登录', array('status' => 401));
    }

    $index = $request->get_param('index');
    ppo_mark_notification_read($user_id, $index !== null ? (int)$index : -1);

    return array(
        'status' => 1,
        'message' => '已标记为已读',
    );
}

function ppo_get_notification_count_api($request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('unauthorized', '请先登录', array('status' => 401));
    }

    return array(
        'count' => ppo_get_unread_notification_count($user_id),
    );
}

// 用戶中心片刻回調
function ppo_render_user_moment_html($user_id, $page = 1, $per_page = 10) {
    $args = [
        'author'         => $user_id,
        'post_type'      => 'moment',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
    ];

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) {
        echo '<div class="user-moment-list pix-user-home-moment-list pix-modern-moment">';

        while ($query->have_posts()) {
            $query->the_post();
            ?>
            <div class="user-moment-item pix-user-home-moment-item">
               <?php get_template_part('tpl/content', 'mgrid'); ?>
            </div>
            <?php
        }

        echo '</div>';

        echo '<div class="pix-user-home-pagination-wrap">';
        echo ppo_htmx_pager([
            'base_url'    => '/wp-json/ppo/v1/user-moment',
            'user_id'     => $user_id,
            'total_pages' => $query->max_num_pages,
            'current'     => $page,
            'target'      => '#user-content',
            'push_url'    => true,
            'push_url_base' => get_author_posts_url($user_id),
            'query_args'  => ['tab' => 'moment'],
            'skeleton' => 'moment-list',
            'class'       => 'pix-user-home-pagination',
            'wpnonce'     => true,
        ]);
        echo '</div>';
    } else {
        echo '<div class="nodata pix-user-home-empty pix-user-home-empty-state"><img src="'.THEME_URL.'/img/empty.png" alt="暂无数据"></div>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}

function ppo_get_user_moment($request) {
    $user_id  = intval($request->get_param('user_id'));
    $page     = max(1, intval($request->get_param('page')));

    if ($target = $request->get_param('target')) {
        $_GET['target'] = sanitize_text_field($target);
    }
    if ($push_url_base = $request->get_param('push_url_base')) {
        $_GET['push_url_base'] = sanitize_text_field($push_url_base);
    }

    $html = ppo_render_user_moment_html($user_id, $page, 20);

    echo $html;
    exit;
}

// 用户圈子回调
function ppo_get_user_moments($request) {
    $user_id  = intval($request->get_param('user_id'));
    $page     = max(1, intval($request->get_param('page')));

    if ($target = $request->get_param('target')) {
        $_GET['target'] = sanitize_text_field($target);
    }
    if ($push_url_base = $request->get_param('push_url_base')) {
        $_GET['push_url_base'] = sanitize_text_field($push_url_base);
    }

    $per_page = defined('PPO_USER_GRID_PER_PAGE') ? PPO_USER_GRID_PER_PAGE : 9;
    $html = ppo_render_user_moments_html($user_id, $page, $per_page);

    echo $html;
    exit;
}

function ppo_render_user_moments_html($user_id, $page = 1, $per_page = 10) {
    $moment_label = ppo_moment_label('moment');
    $user_label = ppo_moment_label('user');
    $user_join = get_user_meta($user_id, 'user_mo_joined', true);
    $joined_ids = $user_join ? array_filter(explode(',', $user_join)) : [];
    $total    = count($joined_ids);
    $total_pages = ceil($total / $per_page);
    $offset = ($page - 1) * $per_page;
    $current_page_ids = array_slice($joined_ids, $offset, $per_page);

    $html = '';

    $terms = [];

    if (!empty($current_page_ids)) {
        $terms = get_terms([
            'taxonomy'   => 'moments',
            'include'    => $current_page_ids,
            'hide_empty' => false,
        ]);
    }

    ob_start();
    if(is_array($terms) && !empty($terms)){
        echo '<div class="user-moments-list pix-user-home-moments-list">';
        foreach ($terms as $k => $term) {
            $v = $term->term_id;
            $title = $term->name;
            $count = $term->count;
            $term_link = get_term_link((int)$v, 'moments');
            $banner = get_term_meta( $v, 'mo_cat_banner', true );
            $img = get_term_meta($v, 'mo_cat_img', true);
            $banner = $banner ? $banner : THEME_URL.'/img/banner.jpg';
            $img = !empty($img) ? $img : THEME_URL.'/img/modef.png';
            $mo_data = get_mo_num_data($v);
    
            echo '<div class="pix-user-home-moments-item"><a class="user-moments-a pix-user-home-moments-card" href="'.esc_url($term_link).'">
                    <div class="inner pix-user-home-moments-inner">
                        <div class="bg-banner pix-user-home-moments-banner"><img class="lazy" data-src="'.esc_url($banner).'" alt="'.esc_attr($title).'"></div>
                            <div class="bottom pix-user-home-moments-info">
                            <div class="bg-cover"></div>
                            <div class="left pix-user-home-moments-cover"><img class="lazy" data-src="'.esc_url($img).'" alt="'.esc_attr($title).'"></div>
                                <div class="right pix-user-home-moments-copy">
                                    <div class="title pix-user-home-moments-title">'.esc_html($title).'</div>
                                    <div class="info pix-user-home-moments-meta"><span>'.absint($count).esc_html($moment_label).'</span> · <span>'.absint($mo_data['join']).esc_html($user_label).'</span></div>
                                </div>   
                            </div>                                        
                    </div>
                </a></div>';
            
        }
        echo '</div>';
        echo '<div class="pix-user-home-pagination-wrap">';
        echo ppo_htmx_pager([
            'base_url'    => '/wp-json/ppo/v1/user-moments',
            'user_id'     => $user_id,
            'total_pages' => $total_pages,
            'current'     => $page,
            'target'      => '#user-content',
            'push_url'    => true,
            'push_url_base' => get_author_posts_url($user_id),
            'query_args'  => ['tab' => 'moments'],
            'skeleton' => 'moments-list',
            'class'       => 'pix-user-home-pagination',
        ]);
        echo '</div>';
  
    } else {
        echo '<div class="nodata pix-user-home-empty pix-user-home-empty-state"><img src="'.THEME_URL.'/img/empty.png" alt="暂无数据"></div>';
    }

    return ob_get_clean();
}
