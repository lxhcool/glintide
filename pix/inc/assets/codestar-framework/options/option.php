<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

//
// Set a unique slug-like ID
//
$prefix = 'pix_options'; 

//
// Create options
//
CSF::createOptions( $prefix, array(
  'menu_title' => 'PIX主题设置',
  'menu_slug'  => 'pix-settings',
  'menu_icon' => THEME_URL .'/img/admin-icon.png',
  'framework_title' => '<img src="'.THEME_URL .'/img/pixlogow.png" style="width:40px;">'.'<b style="font-size:22px;margin-left:15px">PIX</b>' .'<small class="oldVer" data-vs="'. _S_VERSION .'" style="color:#abb0f9;margin-left:10px">Release '. _S_VERSION .'</small>',
) );

CSF::createSection( $prefix, array(
  'title'  => '欢迎！',
  'icon'   => 'fa fa-home',
  'fields' => array(

    array(
      'type'     => 'callback',
      'function' => 'pix_active_theme',
    ),

  )
) ); 

//
//首页设置
//

CSF::createSection( $prefix, array(
  'id'    => 'base_set', // Set a unique slug-like ID
  'title' => '基础设置',
  'icon'   => 'fa fa-dice-d6',
) );

CSF::createSection( $prefix, array(
  'title'  => '常规设置',
  'parent' => 'base_set',
  'icon'   => 'fa fa-rocket',
  'fields' => array(

    array(
      'id'           => 'favicon',
      'type'         => 'upload',
      'title'        => 'Favicon',
      'library'      => 'image',
      'button_title' => '添加favicon',
      'remove_title' => '移除favicon',
      'default' => THEME_URL .'/img/favicon.ico',
      'desc'      => '建议制作一张400x400的png图像, 然后等比缩小到你想转换的ico尺寸,最后通过网上的工具转换成ico图标格式.',
    ),

    array(
      'id'         => 'site_pjax',
      'type'       => 'switcher',
      'title'      => '全站PJAX',
    ),

    //自定义字体 5.19
    array(
      'id'      => 'custom_fonts',
      'type'    => 'text',
      'title'   => '自定义字体链接',
      'default' => '',
    ),

    array(
	    'id'         => 'gravatar_source',
	    'type'       => 'radio',
	    'title'      => 'Gravatar头像源',
	    'inline'  => true,
	    'options'    => array(
	  	'geekzu'    => '极客族', 
      'loli'    => 'loli', 		
      'cravatar'    => 'cravatar',		
	    ),
	    'default'    => 'geekzu',
	  ),

    //博主自定义昵称
    array(
      'id'      => 'nice_name',
      'type'    => 'text',
      'title'   => '博主自定义昵称',
      'default' => '倒霉蛋',
    ),

    array(
      'id'      => 'admin_des',
      'type'    => 'text',
      'title'   => '博主简介',
      'default' => 'Born for design',
    ),

    //博主自定义头像
    array(
	    'id'         => 'avatar_type',
	    'type'       => 'radio',
	    'title'      => '博主头像源',
	    'inline'  => true,
	    'options'    => array(
	  	'email'    => 'Gravatar头像',
		  'custom'    => '自定义头像',	  				
	    ),
	    'default'    => 'custom',
	  ),

    array(
      'id'           => 'default_avatar',
      'type'         => 'upload',
      'title'        => '自定义头像',
      'library'      => 'image',
      'button_title' => '上传图像',
      'remove_title' => '移除图像',
      'default'		 => THEME_URL .'/img/avatar.png',
      'dependency' => array( 'avatar_type', '==', 'custom' ),
    ),

    //默认封面图
    array(
	    'id'         => 'def_thum_type',
	    'type'       => 'radio',
	    'title'      => '自定义缩略图类型',
	    'inline'  => true,
	    'options'    => array(
	  	'local'    => '本地上传',
		  'link'    => '外链',	  				
	    ),
	    'default'    => 'local',
	  ),

    array(
      'id'          => 'def_thum',
      'type'        => 'gallery',
      'title'       => '自定义默认缩略图',
      'add_title'   => '添加背景',
      'edit_title'  => '编辑背景',
      'clear_title' => '移除背景',
      'default'     => THEME_URL.'/img/banner.jpg',
      'desc'        => '可上传多个，建议尺寸小一点',
      'dependency' => array( 'def_thum_type', '==', 'local' ),
    ),

    array(
      'id'      => 'def_thum_link',
      'type'    => 'textarea',
      'title'   => '自定义外链缩略图',
      'desc'    => '一行一个，请保证图片源稳定，不然会拖慢网站速度',
      'dependency' => array( 'def_thum_type', '==', 'link' ),
    ),

    array(
      'id'          => 'column_show',
      'type'        => 'select',
      'title'       => '选择专题页展示的分类',
      'chosen'      => true,
      'multiple'    => true,
      'placeholder' => '选择分类',
      'options'     => 'categories',  
      'sortable'    => true,   
    ), 

    array(
      'id'          => 'linkscat_show',
      'type'        => 'select',
      'title'       => '选择友链页展示的分类',
      'chosen'      => true,
      'multiple'    => true,
      'placeholder' => '选择分类',
      'options'     => 'categories',  
      'query_args'  => array(
        'taxonomy'  => 'link_category',
      ),
      'sortable'    => true,   
    ), 

    array(
      'id'      => 'pix_keywords',
      'type'    => 'textarea',
      'title'   => '站点SEO关键字',
      'desc'    => '填写您站点的关键词，用英文逗号隔开',
    ),
	
	
		// 描述	
    array(
      'id'      => 'pix_description',
      'type'    => 'textarea',
      'title'   => '站点SEO描述',
      'desc'    => '站点的描述',
    ),

  )
) );

CSF::createSection( $prefix, array(
  'title'  => '顶部设置',
  'parent' => 'base_set',
  'icon'   => 'fa fa-horse-head',
  'fields' => array(

    array(
      'id'           => 'site_logo',
      'type'         => 'upload',
      'title'        => '常规LOGO',
      'library'      => 'image',
      'button_title' => '上传logo',
      'remove_title' => '移除logo',
      'default'		 => THEME_URL .'/img/avatar.png',
    ),

    array(
      'id'      => 'logo_height',
      'type'    => 'text',
      'title'   => '常规LOGO高度尺寸',
      'desc'    => '只填写数值即可'
    ),

    array(
      'id'           => 'm_site_logo',
      'type'         => 'upload',
      'title'        => '方形LOGO',
      'library'      => 'image',
      'button_title' => '上传logo',
      'remove_title' => '移除logo',
      'default'		 => THEME_URL .'/img/avatar.png',
      'desc'      => '建议制作一张48*48的方形LOGO',
    ),

    array(
      'id'           => 'mobile_logo',
      'type'         => 'upload',
      'title'        => '移动端LOGO',
      'library'      => 'image',
      'button_title' => '上传logo',
      'remove_title' => '移除logo',
      'default'		 => THEME_URL .'/img/avatar.png',
    ),

    array(
      'id'    => 'm_search_on',
      'type'  => 'switcher',
      'title' => '移动端顶部搜索按钮',
      'default' => true
    ),

    array(
	    'id'         => 'top_s_type',
	    'type'       => 'radio',
	    'title'      => 'WEB端顶部搜索样式',
	    'inline'  => true,
	    'options'    => array(
	  	'modal'    => '弹窗搜索',
		  'normal'    => '直接搜索',	  				
	    ),
	    'default'    => 'modal',
	  ),

    array(
      'id'         => 'top_nav_on_m',
      'type'       => 'switcher',
      'title'      => '移动端顶部菜单开关',
    ),

    array(
      'id'         => 'top_nav_on_web',
      'type'       => 'switcher',
      'title'      => 'WEB端顶部菜单开关',
    ),


  )
) );


//页脚信息
CSF::createSection( $prefix, array(
  'title'  => '底部设置',
  'icon'   => 'fa fa-bars',
  'parent' => 'base_set',
  'fields' => array(

    /*
    array(
      'id'      => 'beian_text',
      'type'    => 'text',
      'title'   => '备案号',
    ),

    array(
      'id'      => 'beian_link',
      'type'    => 'text',
      'title'   => '备案号链接',
    ),

    array(
      'id'    => 'footer_text_diy',
      'type'  => 'wp_editor',
      'title' => '页脚自定义信息',
    ),

    array(
      'id'    => 'copyright_sw',
      'type'  => 'switcher',
      'title' => '开启主题版权',
      'default' => true,
    ),

    */

    array(
      'id'        => 'sfooter_info',
      'type'      => 'group',
      'title'     => '底部信息',
      'fields'    => array(
        array(
          'id'    => 'sf_title',
          'type'  => 'text',
          'title' => '信息文本',
        ),

        array(
          'id'    => 'sf_link',
          'type'  => 'text',
          'title' => '信息链接',
        ),

        array(
          'id'           => 'sf_img',
          'type'         => 'upload',
          'title'        => '图标',
          'library'      => 'image',
          'button_title' => '上传图像',
          'remove_title' => '移除图像',
        ),

        array(
          'id'    => 'sf_open_new',
          'type'  => 'switcher',
          'title' => '新窗口打开',
          'default' => true
        ),
      ),

      //默认
      'default'   => array(
        array(
          'sf_title'    => 'THEME BY PIXIT',
          'sf_link'  => 'https://pixit.cn',
        ),
      ),
    ),

    array(
      'type'    => 'subheading',
      'content' => '移动端底部导航设置',
    ),


    array(
      'id'    => 'fpush_open',
      'type'  => 'switcher',
      'title' => '移动端底部导航',
      'default' => true,
    ),

    //底部左边
    array(
      'id'        => 'footmenu_left',
      'type'      => 'group',
      'title'     => '底部导航左侧区域',
      'fields'    => array(
        array(
          'id'    => 'title',
          'type'  => 'text',
          'title' => '菜单文本',
        ),

        array(
          'id'      => 'icon',
          'type'    => 'icon',
          'title'   => '图标',
          'default' => 'ri-home-line',
          'desc' => '图标和图片二选一'
        ),

        array(
          'id'         => 'fmenu_type',
          'type'       => 'radio',
          'title'      => '菜单类型',
          'inline'  => true,
          'options'    => array(
          'normal'    => '自定义链接',
          'top'    => '返回顶部',	  	
          'search'    => '搜索',
          'dark'    => '黑夜切换',			
          ),
          'default'    => 'normal',
        ),

        array(
          'id'    => 'link',
          'type'  => 'text',
          'title' => '信息链接',
          'dependency' => array( 'fmenu_type', '==', 'normal' ),
        ),

        array(
          'id'    => 'open_new',
          'type'  => 'switcher',
          'title' => '新窗口打开',
          'default' => false,
          'dependency' => array( 'fmenu_type', '==', 'normal' ),
        ),
      ),
      'dependency' => array( 'fpush_open', '==', 'true' ),

    ),

    //右边
    array(
      'id'        => 'footmenu_right',
      'type'      => 'group',
      'title'     => '底部导航右侧区域',
      'max'       => '3',
      'fields'    => array(
        array(
          'id'    => 'title',
          'type'  => 'text',
          'title' => '菜单文本',
        ),

        array(
          'id'      => 'icon',
          'type'    => 'icon',
          'title'   => '图标',
          'default' => 'ri-home-line',
          'desc' => '图标和图片二选一'
        ),

        array(
          'id'         => 'fmenu_type',
          'type'       => 'radio',
          'title'      => '菜单类型',
          'inline'  => true,
          'options'    => array(
          'normal'    => '自定义链接',
          'top'    => '返回顶部',	  	
          'search'    => '搜索',
          'dark'    => '黑夜切换',			
          ),
          'default'    => 'normal',
        ),

        array(
          'id'    => 'link',
          'type'  => 'text',
          'title' => '信息链接',
          'dependency' => array( 'fmenu_type', '==', 'normal' ),
        ),

        array(
          'id'    => 'open_new',
          'type'  => 'switcher',
          'title' => '新窗口打开',
          'default' => false,
          'dependency' => array( 'fmenu_type', '==', 'normal' ),
        ),
      ),
      'dependency' => array( 'fpush_open', '==', 'true' ),

    ),

  )
) );  


//首页设置
CSF::createSection( $prefix, array(
  'title'  => '外观设置',
  'icon'   => 'fa fa-bread-slice',
  'fields' => array(

    array(
      'id'        => 'theme_set',
      'type'      => 'image_select',
      'title'     => '主题风格',
      'options'   => array(
        'green-normal' => THEME_URL .'/img/theme/green.png',
        'purple-gay' => THEME_URL .'/img/theme/blue.png',
        'dark-theme' => THEME_URL .'/img/theme/dark.png',
      ),
      'default'   => 'green-normal'
    ),

    array(
      'id'        => 'layout_set',
      'type'      => 'image_select',
      'title'     => '主题布局',
      'options'   => array(
        'mod_single' => THEME_URL .'/img/theme/single.png',
        'lbc' => THEME_URL .'/img/theme/lbc.png',
        'mod_double' => THEME_URL .'/img/theme/double.png',
        'mod_third_s' => THEME_URL .'/img/theme/third-s.png',
        'mod_third' => THEME_URL .'/img/theme/third.png',
      ),
      'default'   => 'mod_double'
    ),

     //顶部封面随机图
     array(
	    'id'         => 'topbg_banner_type',
	    'type'       => 'radio',
	    'title'      => '顶部封面图来源',
	    'inline'  => true,
	    'options'    => array(
	  	'local'    => '本地上传',
		  'link'    => '外链',	  				
	    ),
	    'default'    => 'local',
	  ),

     array(
      'id'          => 'topbg_banner',
      'type'        => 'gallery',
      'title'       => '顶部封面图',
      'add_title'   => '添加背景',
      'edit_title'  => '编辑背景',
      'clear_title' => '移除背景',
      'default'     => THEME_URL.'/img/banner.jpg',
      'dependency' => array( 'topbg_banner_type', '==', 'local' ),
    ),

    array(
      'id'      => 'topbg_banner_link',
      'type'    => 'textarea',
      'title'   => '外链顶部封面图',
      'desc'    => '一行一个，请保证图片源稳定，不然会拖慢网站速度',
      'dependency' => array( 'topbg_banner_type', '==', 'link' ),
    ),

    //首页模式
    array(
	    'id'         => 'home_post_type',
	    'type'       => 'radio',
	    'title'      => '首页类型模式',
	    'inline'  => true,
	    'options'    => array(
	  	'moment'    => '片刻模式',
		  'post'    => '博客模型',	  				
	    ),
	    'default'    => 'moment',
	  ),


  )
) );    


//片刻设置
CSF::createSection( $prefix, array(
  'title'  => '片刻设置',
  'icon'   => 'fa fa-circle',
  'fields' => array(

    array(
      'id'          => 'moments_cat_list',
      'type'        => 'select',
      'title'       => '选择想要展示的片刻分类',
      'chosen'      => true,
      'multiple'    => true,
      'placeholder' => '选择分类',
      'options'     => 'categories',
      'query_args'  => array(
        'taxonomy'  => 'moments',
      ),   
      'sortable'    => true,   
    ), 

    array(
      'id'          => 'topics_de_cat',
      'type'        => 'select',
      'title'       => '选择默认片刻发布分类',
      'chosen'      => true,
      'multiple'    => false,
      'placeholder' => '选择分类',
      'options'     => 'categories',
      'query_args'  => array(
        'taxonomy'  => 'moments',
      ), 
    ), 

    array(
      'id'         => 'read_more_op',
      'type'       => 'switcher',
      'title'      => '阅读更多按钮',
    ),

    array(
      'id'      => 'read_more_num',
      'type'    => 'text',
      'title'   => '截取字数',
      'default'    => '160',
      'dependency' => array( 'read_more_op', '==', 'true' ),
    ),

    array(
      'id'      => 'min_push_num',
      'type'    => 'text',
      'title'   => '片刻发布最少字数限制',
      'default'    => '6',
      'desc'    => '如果不限制，请输入-1'
    ),

    //ip查询
    array(
      'id'      => 'gaode_key',
      'type'    => 'text',
      'title'   => '高德开放平台应用KEY',
    ),


  )
) );   

//常规文章
CSF::createSection( $prefix, array(
  'title'  => '文章设置',
  'icon'   => 'fa fa-book-open',
  'fields' => array(

    array(
      'id'          => 'posts_cat_list',
      'type'        => 'select',
      'title'       => '选择想要展示文章分类',
      'chosen'      => true,
      'multiple'    => true,
      'placeholder' => '选择分类',
      'options'     => 'categories',  
      'sortable'    => true,   
    ), 

    array(
	    'id'         => 'post_list_type',
	    'type'       => 'radio',
	    'title'      => '首页文章展示类型',
	    'inline'  => true,
	    'options'    => array(
	  	'normal'    => '常规',
		  'grid'    => '网格',	
      'card'    => '卡片',  				
	    ),
	    'default'    => 'card',
	  ),

    array(
      'id'    => 'post_word_max',
      'type'  => 'text',
      'title' => '文章列表摘要字数',
      'default'    => '100',
    ),

    array(
	    'id'         => 'post_pagenav',
	    'type'       => 'radio',
	    'title'      => '文章分页方式',
	    'inline'  => true,
	    'options'    => array(
	  	'more'    => '无限加载',
		  'nav'    => '上下页',					
	    ),
	    'default'    => 'nav',
	  ),

    array(
      'id'    => 'donate_on',
      'type'  => 'switcher',
      'title' => '文章打赏',
      'default'    => false
    ),

    array(
      'id'      => 'donate_des',
      'type'    => 'textarea',
      'title'   => '打赏文案',
      'default'    => '求求你赏口饭吃吧！',
      'dependency' => array( 'donate_on', '==', 'true' ),
    ),

    array(
      'id'           => 'donate_pic',
      'type'         => 'upload',
      'title'        => '打赏二维码',
      'library'      => 'image',
      'button_title' => '上传图像',
      'remove_title' => '移除图像',
      'default'		 => THEME_URL .'/img/avatar.png',
      'dependency' => array( 'donate_on', '==', 'true' ),
    ),

  )
) );  

//音乐设置
CSF::createSection( $prefix, array(
  'title'  => '音乐设置',
  'icon'   => 'fa fa-music',
  'fields' => array(

    array(
      'id'    => 'pix_mu_api',
      'type'  => 'text',
      'title' => '音乐API地址',
      'desc'  => '填写音乐api地址所在域名，例如 https://abc.com 即可，不填则使用本服务器作为api'
    ),

    array(
      'id'    => 'mu_api_key_on',
      'type'  => 'switcher',
      'title' => '开启音乐api密匙',
      'default'    => true
    ),
    

    array(
      'id'    => 'pix_mu_api_key',
      'type'  => 'text',
      'title' => '音乐API密匙',
      'default' => 'pix-music'
    ),

    array(
	    'id'         => 'music_cookie',
	    'type'       => 'radio',
	    'title'      => '音乐cookie',
	    'inline'  => true,
	    'options'    => array(
	  	'netease'    => '网易云',
		  'tencent'    => 'QQ音乐',	  	
      'kuwo'    => '酷我音乐',
      'kugou'    => '酷狗音乐',			
	    ),
	    'default'    => 'netease',
	  ),

    array(
      'id'      => 'netease_cookie',
      'type'    => 'textarea',
      'title'   => '网易云cookie值',
      'dependency' => array( 'music_cookie', '==', 'netease' ),
    ),

    array(
      'id'      => 'tencent_cookie',
      'type'    => 'textarea',
      'title'   => 'QQ音乐cookie值',
      'dependency' => array( 'music_cookie', '==', 'tencent' ),
    ),

    array(
      'id'      => 'kuwo_cookie',
      'type'    => 'textarea',
      'title'   => '酷我cookie值',
      'dependency' => array( 'music_cookie', '==', 'kuwo' ),
    ),

    array(
      'id'      => 'kugou_cookie',
      'type'    => 'textarea',
      'title'   => '酷狗cookie值',
      'dependency' => array( 'music_cookie', '==', 'kugou' ),
    ),



    array(
      'id'    => 'bgm_open',
      'type'  => 'switcher',
      'title' => '开启音乐播放器',
      'default'    => false
    ),

    array(
      'id'         => 'mu_type',
      'type'       => 'button_set',
      'title'      => '歌曲列表类型',
      'options'    => array(
        'list'  => '歌曲列表',
        'album' => '专辑列表',
      ),
      'default'    => 'list',
      'dependency' => array( 'bgm_open', '==', 'true' ),
    ),

    array(
      'id'         => 'mu_source',
      'type'       => 'button_set',
      'title'      => '歌曲源',
      'options'    => array(
        'netease'  => '网易云',
        'tencent' => 'QQ音乐',
        'kugou' => '酷狗音乐',
        'kuwo' => '酷我音乐',
      ),
      'default'    => 'netease',
      'dependency' => array( 'bgm_open', '==', 'true' ),
    ),

    array(
      'id'    => 'play_id',
      'type'  => 'text',
      'title' => '列表ID',
      'dependency' => array( 'bgm_open', '==', 'true' ),
    ),

  )
) ); 



//邮件设置
CSF::createSection( $prefix, array(
  'title'  => '评论设置',
  'icon'   => 'fa fa-envelope',
  'fields' => array(

    array(
      'id'    => 'com_close',
      'type'  => 'switcher',
      'title' => '关闭所有评论',
      'default' => false,
      'desc'  => '备案期间可开启'
    ),

    array(
      'id'    => 'com_robot',
      'type'  => 'switcher',
      'title' => '评论机器人验证',
      'default' => true,
    ),
    
  array(
    'id'    => 'server_push',
    'type'  => 'switcher',
    'title' => '开启server酱评论推送',
    'default' => false,
  ),

  array(
    'id'      => 'send_key',
    'type'    => 'text',
    'title'   => 'sendkey',
  'dependency' => array( 'server_push', '==', 'true' ),
  ),

 array(
   'id'    => 'site_smtp',
   'type'  => 'switcher',
   'title' => '开启发信辅助',
   'default' => false,
 ),

   array(
     'id'      => 'smtp_name',
     'type'    => 'text',
     'title'   => '发件人名称',
     'default' => 'wp事务官',
   'dependency' => array( 'site_smtp', '==', 'true' ),
   ),
 
 array(
   'id'      => 'smtp_server',
   'type'    => 'text',
   'title'   => 'SMTP服务器',
   'default' => 'smtp.163.com',
   'dependency' => array( 'site_smtp', '==', 'true' ),
 ),
 
 array(
   'id'      => 'smtp_port',
   'type'    => 'text',
   'title'   => 'smtp端口',
   'default' => '465',
   'dependency' => array( 'site_smtp', '==', 'true' ),
 ),
 
 array(
   'id'      => 'smtp_email',
   'type'    => 'text',
   'title'   => '邮箱账号',
   'dependency' => array( 'site_smtp', '==', 'true' ),
 ),
 
 
 array(
   'id'      => 'smtp_password',
   'type'    => 'text',
   'title'   => '邮箱密码',
   'dependency' => array( 'site_smtp', '==', 'true' ),
 ),
 
 array(
   'id'         => 'smtp_ssl',
   'type'       => 'radio',
   'title'      => 'SMTPSecure',
   'inline'  => true,
   'options'    => array(
   'ssl'   => 'SSL',
   'tls'    => 'TLS',
     ''    => 'NONE'
         
   ),
   'default'    => 'ssl',
   'dependency' => array( 'site_smtp', '==', 'true' ),
 ),
 
 array(
   'id'           => 'mail_logo',
   'type'         => 'upload',
   'title'        => '上传LOGO',
   'library'      => 'image',
   'button_title' => '上传LOGO',
   'remove_title' => '移除LOGO',
   'default'		 => THEME_URL .'/img/logo.png',
   'dependency' => array( 'site_smtp', '==', 'true' ),
 ),
 
 array(
   'id'    => 'comments_notify',
   'type'  => 'switcher',
   'title' => '评论邮件通知',
   'default' => false,
 ),


  )
) );

//扩展设置
CSF::createSection( $prefix, array(
  'title'  => '扩展设置',
  'icon'   => 'fa fa-tools',
  'fields' => array(

    // 开启七牛云缓存
    array(
      'id'      => 'qiniu_cdn',
      'type'    => 'switcher',
      'title'   => '开启全局CDN缓存',
      'label'   => '开启后全局图片将会缓存到七牛，本地不会删除',
      'default' => false
    ),  

    // 网站域名
    array(
      'id'         => 'qiniu_local_host',
      'type'       => 'text',
      'title'      => '网站域名',
      'dependency' => array( 'qiniu_cdn', '==', 'true' ),
    ),

    array(
      'id'         => 'qiniu_host',
      'type'       => 'text',
      'title'      => '七牛域名',
      'dependency' => array( 'qiniu_cdn', '==', 'true' ),
    ),

    //头部HTML代码
    array(
      'id'      => 'head_html',
      'type'    => 'textarea',
      'title'   => '头部HTML代码',
      'sanitize' => false,
      'desc'    => '你可以添加站点的<code>&lt;meta>、&lt;link>、&lt;style>、&lt;script></code>等标签，通常情况下，这里是用来放置第三方台验证站点所有权时使用的。'
    ),

    //底部HTML代码
    array(
      'id'      => 'footer_html',
      'type'    => 'textarea',
      'title'   => '底部HTML代码',
      'sanitize' => false,
      'desc'    => '你可以添加站点的<code>&lt;style>、&lt;script></code>等标签，通常情况下，这里是用来加载额外的JS、css文件，或者放置统计代码。'
    ),

    //自定义CSS
    array(
      'id'       => 'code_css',
      'type'     => 'code_editor',
      'title'    => '自定义CSS',
      'settings' => array(
        'theme'  => 'monokai',
        'mode'   => 'css',
      ),
    ),

    //自定义JS
    array(
      'id'       => 'code_js',
      'type'     => 'code_editor',
      'title'    => '自定义Javascript',
      'settings' => array(
        'theme'  => 'monokai',
        'mode'   => 'javascript',
      ),
      'default'  => 'console.log("pix number one!");',
    ),

  )
) );



//备份
CSF::createSection( $prefix, array(
  'title'  => '备份设置',
  'icon'   => 'fa fa-shield-alt',
  'fields' => array(

    array(
      'type' => 'backup',
    ),

  )
) );  

