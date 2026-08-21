<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

//
// Set a unique slug-like ID
//
$prefix = 'ppo_options';

//
// Create options
//
CSF::createOptions( $prefix, array(
  'menu_title' => 'PIX主题设置',
  'menu_slug'  => 'pix-settings',
  'class'      => 'pix-options',
  'show_in_customizer' => true,
) );

//模块设置 - 2
CSF::createSection( $prefix, array(
  'id'    => 'mod_set', // Set a unique slug-like ID
  'title' => '模块布局',
  'priority' => 2,
  'icon'   => 'fa fa-dice-d6',
) );

//扩展设置 - 3
CSF::createSection( $prefix, array(
  'id'    => 'expand_set', // Set a unique slug-like ID
  'title' => '扩展设置',
  'priority' => 3,
  'icon'   => 'fa fa-dice-d6',
) );


//基础设置 全局设置
CSF::createSection( $prefix, array(
  'title'  => '基础设置',
  'icon'   => 'fa fa-bread-slice',
  'priority' => 1,
  'fields' => array(

    //站点logo
    array(
      'id'           => 'site_logo',
      'type'         => 'upload',
      'title'        => '站点LOGO(深色)',
      'library'      => 'image',
      'button_title' => '上传图像',
      'remove_title' => '移除图像',
      'preview'      => true,
      'default'		 => THEME_URL .'/img/logo.png',
    ),

     //浅色logo
     array(
      'id'           => 'site_logo_w',
      'type'         => 'upload',
      'title'        => '站点LOGO(浅色)',
      'library'      => 'image',
      'button_title' => '上传图像',
      'remove_title' => '移除图像',
      'preview'      => true,
      'default'		 => THEME_URL .'/img/logo.png',
    ),

    //LOGO高度
    array(
      'id'          => 'logo_height',
      'type'        => 'number',
      'title'       => 'LOOGO高度尺寸',
      'unit'        => 'px',
      'output'      => '.top-logo img',
      'output_mode' => 'height',
      'default'     => 32,
      'desc'        => '可自行调整logo高度，宽度等比缩放'
    ),

    //文字LOGO
    array(
      'id'      => 'logo_text',
      'type'    => 'text',
      'title'   => '文字LOGO',
      'default' => get_bloginfo( 'name' ),
      'desc'    => '如果不设置LOGO图片，则使用文字LOGO'
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
      //'default'     => THEME_URL.'/img/banner.jpg',
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


  )
) );   

//模块设置---------------------------------------------------------------------------------- start

//外观色彩
CSF::createSection( $prefix, array(
  'title'  => '主题外观',
  'parent' => 'mod_set',
  'icon'   => 'fa fa-rocket',
  'fields' => array(

    array(
      'type'    => 'subheading',
      'content' => '主题风格设置',
    ),

    //主题风格
    array(
	    'id'         => 'theme_style',
	    'type'       => 'radio',
	    'title'      => '主题风格',
	    'inline'  => true,
	    'options'    => array(
	  	'classic'    => 'PIX经典',
		  'custom'    => '自定义模块',	  				
	    ),
	    'default'    => 'classic',
	  ),

    //经典模式详细设置
    array(
      'id'         => 'custom-body',
      'type'       => 'button_set',
      'title'      => '内容块风格',
      'options'    => array(
        'box' => '盒子',
        'card'  => '卡片',
      ),
      'default'    => 'card',
      'dependency' => array( 'theme_style', '==', 'classic' ),
    ),

    array(
      'type'    => 'subheading',
      'content' => '区块样式设置(小工具宽度自动计算,无需设置,计算方式：只启用一个侧边栏为1280px-640px,启用两个则除以2)',
    ),

    //主体宽度
    array(
      'id'          => 'body-width',
      'type'        => 'number',
      'title'       => '主体页面宽度',
      'unit'        => 'px',
      'output'      => '.pix-content',
      'output_mode' => 'max-width',
      'default'     => 1280,
      'desc'        => '请直接填写宽度数字，此处指页面主体内容宽度,中间内容+小工具的总宽度'
    ),

    array(
      'id'          => 'center-width',
      'type'        => 'number',
      'title'       => '中间内容宽度',
      'unit'        => 'px',
      'output'      => '.center-content',
      'output_mode' => 'width',
      'default'     => 640,
      'desc'        => '请直接填写宽度数字，此处指中间内容宽度，不包含小工具宽度'
    ),

    array(
      'id'          => 'nav_height',
      'type'        => 'number',
      'title'       => '顶部主导航高度',
      'unit'        => 'px',
      'output'      => array('.main-nav-box','.box-nav-main'),
      'output_mode' => 'height',
      'default'     => 74,
      'desc'        => '默认高度74px'
    ),

    array(
      'id'          => 'sub_nav_height',
      'type'        => 'number',
      'title'       => '顶部副导航高度',
      'unit'        => 'px',
      'output'      => '.sub-nav',
      'output_mode' => 'height',
      'default'     => 54,
      'desc'        => '默认高度54px'
    ),

    array(
      'id'          => 'box-round',
      'type'        => 'number',
      'title'       => '全局区块圆角',
      'unit'        => 'px',
      'output'      => array('.home-box','.b-nav-box .box-nav-main','.b-nav-box .sub-nav','.box','.sub-menu','.sub-menu li a','.round','.slide-content','.navst2 .left-nav-main ul li a'),
      'output_mode' => 'border-radius',
      'default'     => 10,
    ),

    //块投影 
    array(
      'id'    => 'box_shadow',
      'type'  => 'switcher',
      'title' => '区块投影',
      'default' => false,
      'desc'  => '开启后，区块将增加投影'
    ),

    //导航投影
    array(
      'id'    => 'nav_shadow',
      'type'  => 'switcher',
      'title' => '导航投影',
      'default' => true,
      'desc'  => '开启后，导航将增加投影'
    ),

    //字体设置
    array(
      'id'          => 'web_fonts',
      'type'        => 'select',
      'title'       => '字体设置',
      'options'     => array(
        'normal'  => '关闭',
        'CangErYuYangTiW02-2'  => '苍耳渔阳体',
        'SmileySans-Oblique-2'  => '得意黑',
        'OPPOSans-R-2'  => 'OPPOSans',
        'PangMenZhengDaoXiXianTi-2'  => '庞门正道细线体',
        'HarmonyOS_Sans_SC_Regular'  => '鸿蒙黑体',
        'LXGWWenKaiGB-Regular'  => '霞鹜文楷',
        'DingTalkJinBuTi'  => '钉钉进步体',
        'fusion-pixel'  => '缝合怪像素字体',
      ),
      'default'     => 'normal'
    ),

    array(
      'type'    => 'subheading',
      'content' => '主题色彩搭配',
    ),

    //主体背景色
    array(
      'id'                              => 'bg-c',
      'type'                            => 'background',
      'title'                           => '背景色/图案',
      'background_gradient'             => true,
      'background_origin'               => true,
      'background_clip'                 => true,
      'background_blend_mode'           => true,
      'default'                         => array(
        'background-color'              => '#f3efff',
        'background-gradient-color'     => '#e7f1ff',
        'background-gradient-direction' => '135deg',
        'background-size'               => 'cover',
        'background-position'           => 'center center',
        'background-repeat'             => 'no-repeat',
      ),
      'output'                          => 'body'
    ),

    //主题标识色
    array(
      'id'          => 'theme_color',
      'type'        => 'color',
      'title'       => '主题标识色',
      'output' => array( 'background-color' => '','color' => '.top-nav ul li a:hover,.sub-nav-box ul li a:hover'),
      'default' => '#2119ff',
    ),

    //主体包裹块颜色
    array(
      'id'          => 'home-box-c',
      'type'        => 'color',
      'title'       => '主体包裹块背景色',
      'output'      => '.home-box',
      'output_mode' => 'background-color',
      'default' => 'transparent'
    ),

    //小工具块
    array(
      'id'          => 'wid-box-c',
      'type'        => 'color',
      'title'       => '小工具块背景色',
      'output'      => '.wid-box',
      'output_mode' => 'background-color',
      'default' => '#ffffff'
    ),

    //顶部导航背景色
    array(
      'id'          => 'top_header_c',
      'type'        => 'color',
      'title'       => '顶部导航背景色',
      'output'      => array('.hd-bg','.hd-bg .sub-menu'),
      'output_mode' => 'background-color',
      'default' => '#ffffff'
    ),

    //顶部导航文字色
    array(
      'id'          => 'top_header_f_c',
      'type'        => 'color',
      'title'       => '顶部导航文字色',
      'output'      => array('.hd-bg ul li a','a.top-icon'),
      'output_mode' => 'color',
      'default' => '#000000'
    ),

    //顶部二级导航背景色
    array(
      'id'          => 'top_sub_header_c',
      'type'        => 'color',
      'title'       => '顶部副导航背景色',
      'output'      => array('.sub-hd-bg' , '.sub-hd-bg .sub-menu'),
      'output_mode' => 'background-color',
      'default' => '#ffffff'
    ),

    //顶部二级导航文字色
    array(
      'id'          => 'top_sub_header_f_c',
      'type'        => 'color',
      'title'       => '顶部副导航文字色',
      'output'      => '.sub-hd-bg ul li a',
      'output_mode' => 'color',
      'default' => '#000000',
    ),


  )
) ); 


//导航设置
CSF::createSection( $prefix, array(
  'title'  => '导航设置',
  'parent' => 'mod_set',
  'icon'   => 'fa fa-bars',
  'fields' => array(

    //导航类型
    array(
      'id'        => 'nav_style',
      'type'      => 'image_select',
      'title'     => '导航类型',
      'options'   => array(
        'nav1' => THEME_URL .'/img/mod/nav1.png',
        'nav2' => THEME_URL .'/img/mod/nav2.png',
        'nav3' => THEME_URL .'/img/mod/nav3.png',   
        'nav4' => THEME_URL .'/img/mod/nav4.png',  
        'nav5' => THEME_URL .'/img/mod/nav5.png',  
        'nav6' => THEME_URL .'/img/mod/nav6.png',     
      ),
      'default'   => 'nav1',
      'desc'      => '仅超级布局模式下可选择',
    ),

    //常规导航设置
    array(
      'id'     => 'nav1_set',
      'type'   => 'fieldset',
      'title'  => '导航详细设置',
      'fields' => array(
        
        array(
          'type'    => 'submessage',
          'style'   => 'info',
          'content' => '导航总设置',
        ),

        array(
          'id'         => 'nav_width',
          'type'       => 'button_set',
          'title'      => '导航宽度',
          'options'    => array(
            'full'  => '全宽',
            'inherit' => '继承主体',
          ),
          'default'    => 'inherit'
        ),

        array(
          'id'         => 'nav_sticky',
          'type'       => 'button_set',
          'title'      => '导航栏滚动状态',
          'options'    => array(
            'normal'  => '跟随',
            'sticky' => '始终固定',
            'showup' => '回滚显示',
          ),
          'default'    => 'sticky',
        ),

        array(
          'id'    => 'scroll_glass',
          'type'  => 'switcher',
          'title' => '吸附状态毛玻璃',
          'default' => true,
          'dependency' => array( 'nav_sticky', 'any', 'sticky,showup' ),
          'desc'  => '导航条吸附状态下，毛玻璃效果'
        ),

        array(
          'id'          => 'glass_bg',
          'type'        => 'color',
          'title'       => '吸附状态背景色',
          'output'      => array('.top-header.nav2.showup.active .sub-top-nav-box','.top-nav-box.pix-sticky-fixed','.top-header.nav3.active'),
          'output_mode' => 'background-color',
          'dependency' => array( 'nav_sticky', 'any', 'sticky,showup' ),
          'desc'  => '请设置为半透明效果',
          'default'  => 'rgba(255,255,255,0.65)',
        ),

        array(
          'id'          => 'glass_text',
          'type'        => 'color',
          'title'       => '吸附状态文本色',
          'output'      => array(
                      '.top-header.nav2.showup.active .sub-top-nav-box #top_sub_menu > li > a',
                      '.top-nav-box.pix-sticky-fixed #top_menu > li > a',
                      '.top-nav-box.pix-sticky-fixed .tool-box li a',
                      '.top-header.nav3.active #top_menu > li > a', 
                      '.top-header.nav3.active .tool-box li a',
                    ),
          'output_mode' => 'color',
          'dependency' => array( 'nav_sticky', 'any', 'sticky,showup' ),
          'default'  => '#0a0a0a',
        ),

        array(
          'id'     => 'nav_border',
          'type'   => 'border',
          'title'  => '导航底部边框',
          'top'    => false,
          'left'    => false,
          'right'    => false,
          'output' => '.top-nav-box',
          'default' => array(
            'top'    => '0',
            'right'  => '0',
            'bottom' => '0',
            'left'   => '0',
            'style'  => 'solid',
            'color'  => '#ffffff',
            'unit'   => 'px',
          ),
        ),

        array(
          'type'    => 'submessage',
          'style'   => 'info',
          'content' => '主导航设置',
        ),

        array(
          'id'          => 'nav_h',
          'type'        => 'number',
          'title'       => '导航高度',
          'output'      => '.top-nav-box',
          'unit'        => 'px',
          'output_mode' => 'height',
          'default'     => 74,
        ),
        

        array(
          'id'           => 'nav_img',
          'type'         => 'upload',
          'title'        => '导航背景图',
          'library'      => 'image',
          'placeholder'  => 'http://',
          'preview'      => true,
          'button_title' => '添加图片',
          'remove_title' => '移除图片',
        ),

        array(
          'id'         => 'item_order',
          'type'       => 'button_set',
          'title'      => '项目位置',
          'options'    => array(
            'menu_left'  => '菜单居左',
            'menu_center' => '菜单居中',
            'menu_right' => '菜单居右',
            'logo_center' => 'LOGO居中',
          ),
          'default'    => 'menu_left',
          'desc'       => '此项分菜单块居中，或LOGO块居中，其中菜单居中又分类行内居中，居左，居右',
          'dependency' => array( 'nav_style', 'not-any', 'nav5,nav6','all' ),
        ),

        array(
          'id'         => 'nav5_item_order',
          'type'       => 'button_set',
          'title'      => '项目位置',
          'options'    => array(
            'menu_left'  => '菜单居左',
            'menu_right' => '菜单居右',
            'logo_center' => 'LOGO居中',
          ),
          'default'    => 'menu_left',
          'dependency' => array( 'nav_style', '==', 'nav5','all' ),
        ),

        array(
          'id'         => 'nav6_item_order',
          'type'       => 'button_set',
          'title'      => '项目位置',
          'options'    => array(
            'menu_left'  => '菜单居左',
            'menu_center' => '菜单居中 | 搜索居左',
            'menu_right' => '菜单居右 | 搜索居左',
          ),
          'default'    => 'menu_left',
          'dependency' => array( 'nav_style', '==', 'nav6','all' ),
        ),

        array(
          'id'        => 'topitem_style',
          'type'      => 'image_select',
          'title'     => '顶部菜单项样式',
          'options'   => array(
            'tnavst1' => THEME_URL .'/img/mod/tnavst1.png',
            'tnavst2' => THEME_URL .'/img/mod/tnavst2.png',    
          ),
          'default'   => 'tnavst1',
        ),


      ),

      //'dependency' => array( 'nav_style', '==', 'nav1' ),
    ),

     //导航颜色
     array(
      'id'            => 'top_nav_color',
      'type'          => 'tabbed',
      'title'         => '导航颜色与特效',
      'tabs'          => array(
        array(
          'title'     => '一级导航',
          'fields'    => array(

            array(
              'type'    => 'submessage',
              'style'   => 'info',
              'content' => '顶部导航栏及菜单项颜色和效果',
            ),  

            array(
              'id'         => 'main_link_effects',
              'type'       => 'radio',
              'title'      => '链接悬浮效果',
              'inline'     => true,
              'options'    => array(
                'normal' => '无效果',
                'up' => '上移',
                'bold' => '变粗',
                'main_high' => '突出',
              ),
              'default'    => 'normal'
            ),

            array(
              'id'        => 'main_color',
              'type'      => 'color_group',
              'title'     => '颜色设置',
              'options'   => array(
                'nav_bg' => '导航条背景',
                'nav_text' => '文本色',
                'hover_text' => '悬浮文本色',
              ),
                'default'   => array(
                'nav_bg' => '#ffffff',
                'nav_text' => '#0a0a0a',
                'hover_text' => '#0400ff',
              )
            ),

            array(
              'id'                              => 'tnavst1_effects',
              'type'                            => 'background',
              'title'                           => '悬浮条颜色',
              'background_gradient'             => true,
              'background_image'                => false,
              'background_position'             => false,
              'background_repeat'               => false,
              'background_attachment'           => false,
              'background_size'                 => false,
              'default'                         => array(
                'background-color'              => '#2c28ff',
                'background-gradient-color'     => '#5697ff',
                'background-gradient-direction' => 'to right',
              ),
              'output'                          => '.top-nav.tnavst1 ul li a .nav-link-item:before',
              'dependency' => array( 'topitem_style', '==', 'tnavst1', 'all' ),
            ),

            array(
              'id'       => 'tnavst1_effects_h',
              'type'     => 'dimensions',
              'title'    => '悬浮条高度',
              'width'    => false,
              'units'   => array('px'),
              'default'  => array(
                'height' => '2',
              ),
              'output' => '.top-nav.tnavst1 ul li a .nav-link-item:before',
              'dependency' => array( 'topitem_style', '==', 'tnavst1', 'all' ),
            ),

            array(
              'id'    => 'tnavst1_effects_top',
              'type'  => 'spacing',
              'title' => '悬浮条顶部距离',
              'left'  => false,
              'right' => false,
              'bottom' => false,
              'default'     => array(
                'top'       => '70',
                'unit'     => '%',
              ),
              'output_mode' => 'relative',
              'output' => '.top-nav.tnavst1 ul li a .nav-link-item:before',
              'dependency' => array( 'topitem_style', '==', 'tnavst1', 'all' ),
            ),

            array(
              'id'    => 'tnavst1_effects_ra',
              'type'  => 'spacing',
              'title' => '悬浮条圆角',
              'default'     => array(
                'top'       => '2',
                'right'     => '2',
                'bottom'    => '2',
                'left'      => '2',
                'unit'     => 'px',
              ),
              'output_mode' => 'border-radius',
              'output' => '.top-nav.tnavst1 ul li a .nav-link-item:before',
              'dependency' => array( 'topitem_style', '==', 'tnavst1', 'all' ),
            ),

            //悬浮块背景色
            array(
              'id'                              => 'tnavst2_effects',
              'type'                            => 'background',
              'title'                           => '悬浮块颜色',
              'background_gradient'             => true,
              'background_image'                => false,
              'background_position'             => false,
              'background_repeat'               => false,
              'background_attachment'           => false,
              'background_size'                 => false,
              'default'                         => array(
                'background-color'              => '#2c28ff',
                'background-gradient-color'     => '#5697ff',
                'background-gradient-direction' => 'to right',
              ),
              'output'                          => '.top-nav.tnavst2 ul li a:after',
              'dependency' => array( 'topitem_style', '==', 'tnavst2', 'all' ),
            ),

            //悬浮块高度
            array(
              'id'       => 'tnavst2_effects_h',
              'type'     => 'dimensions',
              'title'    => '悬浮块高度',
              'width'    => false,
              'units'   => array('%','px'),
              'default'  => array(
                'height' => '50',
                'unit'   => '%',
              ),
              'output' => '.top-nav.tnavst2 ul li a:after',
              'desc' => '建议数值：50%-80%',
              'dependency' => array( 'topitem_style', '==', 'tnavst2', 'all' ),
            ),

            //悬浮块圆角
            array(
              'id'    => 'tnavst2_effects_ra',
              'type'  => 'spacing',
              'title' => '悬浮块圆角',
              'default'     => array(
                'top'       => '8',
                'right'     => '8',
                'bottom'    => '8',
                'left'      => '8',
                'unit'     => 'px',
              ),
              'output_mode' => 'border-radius',
              'output' => '.top-nav.tnavst2 ul li a:after',
              'dependency' => array( 'topitem_style', '==', 'tnavst2', 'all' ),
            ),

            //悬浮块描边
            array(
              'id'     => 'tnavst2_effects_border',
              'type'   => 'border',
              'title'  => '悬浮块描边',
              'all'   => true,
              'output' => '.top-nav.tnavst2 ul li a:after',
              'default' => array(
                'color'  => 'rgba(255,255,255,0)',
                'all'    => '0',
              ),
              'dependency' => array( 'topitem_style', '==', 'tnavst2', 'all' ),
            ),

            //菜单项间距
            array(
              'id'      => 'nav_item_spac',
              'type'    => 'slider',
              'title'   => '菜单项间距',
              'min'     => 4,
              'max'     => 20,
              'step'    => 1,
              'unit'    => 'px',
              'default' => 12,
            ),


          )
        ),


        //二级菜单
        array(
          'title'     => '二级导航',
          'fields'    => array(

            array(
              'type'    => 'submessage',
              'style'   => 'info',
              'content' => '顶部二级菜单颜色和效果',
            ),  

            array(
              'id'         => 'sub_link_effects',
              'type'       => 'radio',
              'title'      => '链接悬浮效果',
              'inline'     => true,
              'options'    => array(
                'normal' => '无效果',
                'move' => '右移',
                'disperse' => '分散',
                'high' => '突出',
              ),
              'default'    => 'normal'
            ),

            //颜色
            array(
              'id'        => 'sub_color',
              'type'      => 'color_group',
              'title'     => '颜色设置',
              'options'   => array(
                'sub_bg' => '二级导航背景',
                'sub_text' => '文本色',
                'hover_sub' => '悬浮文本色',
                'hover_subg' => '悬浮块背景色(可设置成透明)',
              ),
              'default'   => array(
                'sub_bg' => '#ffffff',
                'sub_text' => '#0a0a0a',
                'hover_sub' => '#000cff',
                'hover_subg' => '#ffffff',
              )
            ),

            //二级菜单圆角
            array(
              'id'          => 'sub_round',
              'type'        => 'spacing',
              'title'       => '二级菜单背景圆角',
              'output'      => '.top-nav .sub-menu',
              'output_mode' => 'border-radius', // or margin, relative
              'default'     => array(
                'top'       => '10',
                'right'     => '10',
                'bottom'    => '10',
                'left'      => '10',
                'unit'      => 'px',
              ),
            ),

            //悬浮块圆角
            array(
              'id'          => 'sub_hover_round',
              'type'        => 'spacing',
              'title'       => '悬浮块圆角',
              'output'      => '.top-header .top-nav li ul li a',
              'output_mode' => 'border-radius', // or margin, relative
              'all'         => true,
              'default'     => array(
                'all'       => '8',
                'unit'      => 'px',
              ),
            ),

            //二级菜单描边
            array(
              'id'     => 'sub_border',
              'type'   => 'border',
              'title'  => '二级菜单面板描边',
              'all'   => true,
              'output' => '.top-nav .sub-menu',
              'default' => array(
                'color'  => 'rgba(255,255,255,0)',
                'all'    => '0',
              ),
            ),

          )
        ),
      )
    ),

    //复合导航副导航设置
    array(
      'id'     => 'sub_nav',
      'type'   => 'fieldset',
      'title'  => '复合导航-副导航设置',
      'fields' => array(

        array(
          'type'    => 'submessage',
          'style'   => 'info',
          'content' => '复合导航-副导航设置',
        ),

        array(
          'id'          => 'sub_nav_h',
          'type'        => 'number',
          'title'       => '副导航高度',
          'output'      => '.sub-top-nav-box',
          'unit'        => 'px',
          'output_mode' => 'height',
          'default'     => 60,
        ),

        array(
          'id'        => 'sub_nav_color',
          'type'      => 'color_group',
          'title'     => '颜色设置',
          'options'   => array(
            'nav_bg' => '导航条背景',
            'nav_text' => '导航条文本',
            'sub_bg' => '二级菜单背景',
            'sub_text' => '二级菜单文本',
            'hover_text' => '文本悬浮色',
            'hover_sub_text' => '二级菜单文本悬浮色',
            'hover_sub_bg' => '二级菜单悬浮块颜色',
          ),
          'default'   => array(
            'nav_bg' => '#ffffff',
            'nav_text' => '#0a0a0a',
            'sub_bg' => '#ffffff',
            'sub_text' => '#0a0a0a',
            'hover_text' => '#2b1dff',
            'hover_sub_text' => '#2b1dff',
            'hover_sub_bg' => '#ffffff',
          )
        ),

        //副导航圆角
        array(
          'id'          => 'top_subnav_round',
          'type'        => 'spacing',
          'title'       => '副导航二级菜单背景圆角',
          'output'      => '.sub-top-nav-box .sub-menu',
          'output_mode' => 'border-radius', // or margin, relative
          'default'     => array(
            'top'       => '10',
            'right'     => '10',
            'bottom'    => '10',
            'left'      => '10',
            'unit'      => 'px',
          ),
        ),

        //副导航描边
        array(
          'id'     => 'top_subnav_border',
          'type'   => 'border',
          'title'  => '副导航二级菜单面板描边',
          'all'   => true,
          'output' => '.sub-top-nav-box .sub-menu',
          'default' => array(
            'color'  => 'rgba(255,255,255,0)',
            'all'    => '0',
          ),
        ),

      ),
      'dependency' => array( 'nav_style', '==', 'nav2' ),
    ),

    //侧边菜单动画
    array(
      'id'     => 'fixed_nav',
      'type'   => 'fieldset',
      'title'  => '侧边导航面板设置',
      'fields' => array(

        array(
          'type'    => 'submessage',
          'style'   => 'info',
          'content' => '侧边菜单详细设置',
        ),

        array(
          'id'        => 'fitem_style',
          'type'      => 'image_select',
          'title'     => '侧边菜单项样式',
          'options'   => array(
            'navst1' => THEME_URL .'/img/mod/fnav1.png',
            'navst2' => THEME_URL .'/img/mod/fnav2.png',    
          ),
          'default'   => 'navst1',
          'desc'      => '菜单项在悬停或激活状态下的样式',
        ),

        array(
          'id'                              => 'bg',
          'type'                            => 'background',
          'title'                           => '面板背景色/图案',
          'background_gradient'             => true,
          'background_origin'               => true,
          'background_clip'                 => true,
          'background_blend_mode'           => true,
          'default'                         => array(
            'background-color'              => '#ffffff',
            'background-gradient-color'     => '#ffffff',
            'background-gradient-direction' => '135deg',
            'background-size'               => 'cover',
            'background-position'           => 'center center',
            'background-repeat'             => 'no-repeat',
          ),
          'output'                          => '.left-fixed'
        ),

        array(
          'id'                              => 'left_active_link_bg',
          'type'                            => 'background',
          'title'                           => '父级链接激活背景色',
          'background_gradient'             => true,
          'background_image'                => false,
          'background_position'             => false,
          'background_repeat'               => false,
          'background_attachment'           => false,
          'background_size'                 => false,
          'default'                         => array(
            'background-color'              => '#ffffff',
            'background-gradient-color'     => '#ffffff',
            'background-gradient-direction' => '135deg',
          ),
          'output'                          => '.left-nav-main ul li.active-nav > a'
        ),

        array(
          'id'        => 'left_link_color',
          'type'      => 'color_group',
          'title'     => '父级链接颜色',
          'options'   => array(
            'text' => '链接文本色',
            'ac_text' => '激活文本色',
            'hover_text' => '悬浮文本色',
            'hover_bg' => '悬浮背景色',
          ),
          'default'   => array(
            'text' => '#0a0a0a',
            'ac_text' => '#ffffff',
            'hover_text' => '#6c3ae0',
            'hover_bg' => '#ededff',
          )
        ),

        array(
          'id'        => 'left_sublink_color',
          'type'      => 'color_group',
          'title'     => '子级链接颜色',
          'options'   => array(
            'text' => '链接文本色',
            'hover_text' => '悬浮文本色',
            'hover_bg' => '悬浮背景色',
          ),
          'default'   => array(
            'text' => '#4c4c4c',
            'hover_text' => '#1700c9',
            'hover_bg' => '#ededff',
          )
        ),

        
        

      ),
      'dependency' => array( 'nav_style', '==', 'nav6' ),
    ),

  )
) );    

//顶部幻灯
CSF::createSection( $prefix, array(
  'title'  => '轮播设置',
  'parent' => 'mod_set',
  'icon'   => 'fas fa-images',
  'fields' => array(

    //轮播开关
    array(
      'id'    => 'slide_on',
      'type'  => 'switcher',
      'title' => '轮播开关',
      'default' => false,
    ),

    //轮播样式
    array(
      'id'        => 'slide_style',
      'type'      => 'image_select',
      'title'     => '轮播样式',
      'options'   => array(
        'slide1' => THEME_URL .'/img/mod/s1.png',
        'slide2' => THEME_URL .'/img/mod/s2.png',
        'slide3' => THEME_URL .'/img/mod/s3.png',    
        'slide4' => THEME_URL .'/img/mod/s4.png',  
        'slide5' => THEME_URL .'/img/mod/s5.png',
      ),
      'default'   => 'slide1',
      'desc'      => '幻灯1模式下可以调整全宽和跟随主体，其他模式下只能跟随主体',
    ),

    //添加轮播项目
    array(
      'id'        => 'slide_item',
      'type'      => 'group',
      'title'     => '添加轮播图',
      'accordion_title_auto' => false,
      'accordion_title_number' => true,
      'fields'    => array(

        array(
          'id'           => 'slide_image',
          'type'         => 'upload',
          'title'        => '轮播背景图',
          'library'      => 'image',
          'button_title' => '媒体库上传',
          'remove_title' => '移除图片',
          'preview'      => true,
          'desc'         => '可以从媒体库上传，也可以直接输入图片链接',
        ),

        array(
          'id'                              => 'slide_color_bg',
          'type'                            => 'background',
          'title'                           => '幻灯背景色',
          'background_gradient'             => true,
          'background_image'                => false,
          'background_position'             => false,
          'background_repeat'               => false,
          'background_attachment'           => false,
          'background_size'                 => false,
          'default'                         => array(
            'background-color'              => '#ffffff',
            'background-gradient-color'     => '#ffffff',
            'background-gradient-direction' => 'to right',
          ),
        ),
        
        array(
          'id'      => 'slide_url_on',
          'type'    => 'switcher',
          'title'   => '轮播层链接开关',
          'default' => true,
          'desc'    => '开启后，点击轮播任何位置都会跳转链接，如果叠加层有自定义链接按钮，可关闭此处'
        ),

        array(
          'id'           => 'slide_link',
          'type'         => 'link',
          'title'        => '轮播标题|链接',
          'add_title'    => '添加链接',
          'edit_title'   => '修改链接',
          'remove_title' => '移除链接',
          'desc'         => '链接文字可不填，开启叠加层后，此标题将不显示',
          'dependency' => array( 'slide_url_on', '==', 'true', '', 'visible' ),
        ),

        array(
          'id'      => 'title_style',
          'type'    => 'typography',
          'title'   => '标题样式',
          'font_family' => false,
          'preview'     => false,
          'text_align' => false,
          'default' => array(
            'color'       => '#ffffff',
            'font-size'   => '32',
            'line-height' => '32',
            'letter-spacing' => '1',
            'unit'        => 'px',
          ),
        ),

        array(
          'type'    => 'submessage',
          'style'   => 'info',
          'content' => '<i class="ri-hashtag"></i>轮播叠加层',
        ),

        array(
          'id'      => 'slide_layer_on',
          'type'    => 'switcher',
          'title'   => '叠加层开关',
          'default' => false,
          'desc'    => '开启后可以叠加文字和图片图层，制作丰富的轮播图'
        ),
       
        //叠加块
        array(
          'id'            => 'slide_layer',
          'type'          => 'tabbed',
          'title'         => '轮播叠加层',
          'dependency' => array( 'slide_layer_on', '==', 'true', '', 'visible' ),
          'tabs'          => array(
            //文字层
           array(
              'title'     => '文字层',
              'icon'      => 'fa fa-quote-right',
              'fields'    =>  array(
                array(
                  'id'            => 'layer_content',
                  'type'          => 'accordion',
                  'title'         => '',
                  'accordions'    => array(
                    array(
                      'title'     => '标题',
                      'fields'    => array(
                        array(
                          'id'    => 'layer_title',
                          'type'  => 'textarea',
                          'placeholder' => '填写叠加层标题',
                          'title' => '',
                        ),
                        array(
                          'id'      => 'title_style',
                          'type'    => 'typography',
                          'title'   => '文本样式',
                          'font_family' => false,
                          'preview'     => false,
                          'default' => array(
                            'color'       => '#ffffff',
                            'font-size'   => '32',
                            'line-height' => '20',
                            'letter-spacing' => '1',
                            'unit'        => 'px',
                          ),
                        ),
                        array(
                          'id'      => 'title_bold',
                          'type'    => 'switcher',
                          'title'   => '粗体/常规',
                          'label'   => '是否开启粗体?',
                          'default' => true
                        ),

                        array(
                          'id'    => 'layer_title_pax',
                          'type'  => 'textarea',
                          'subtitle' => '一行一个,具体内容参见下方【视差动画参数】',
                          'title' => '视差动画',
                        ),
                        
                      )
                    ),
                    array(
                      'title'     => '描述',
                      'fields'    => array(
                        array(
                          'id'    => 'layer_des',
                          'type'  => 'textarea',
                          'placeholder' => '填写叠加层描述',
                          'title' => '',
                        ),
                        array(
                          'id'      => 'des_style',
                          'type'    => 'typography',
                          'title'   => '文本样式',
                          'font_family' => false,
                          'preview'     => false,
                          'default' => array(
                            'color'       => '#ffffff',
                            'font-size'   => '16',
                            'line-height' => '20',
                            'letter-spacing' => '1',
                            'unit'        => 'px',
                          ),
                        ),
                        array(
                          'id'    => 'layer_des_pax',
                          'type'  => 'textarea',
                          'subtitle' => '一行一个,具体内容参见下方【视差动画参数】',
                          'title' => '视差动画',
                        ),
                      )
                    ),

                    //自定义
                    array(
                      'title'     => '自定义',
                      'fields'    => array(
                          array(
                            'id'            => 'layer_custom',
                            'type'          => 'wp_editor',
                            'title'         => '',
                            'desc'          => '可以使用短代码添加按钮等元素',
                            'tinymce'       => true,
                            'quicktags'     => true,
                            'media_buttons' => true,
                            'height'        => '100px',
                          ),

                          array(
                            'id'    => 'layer_custom_pax',
                            'type'  => 'textarea',
                            'subtitle' => '一行一个,具体内容参见下方【视差动画参数】',
                            'title' => '视差动画',
                          ),
                      )
                    ),
          
                  )
                ),
          
                //对齐
                array(
                  'id'         => 'layer_text_align',
                  'type'       => 'radio',
                  'title'      => '文字层对齐',
                  'inline'     => true,
                  'options'    => array(
                    'left' => '居左',
                    'center' => '居中',
                    'right' => '居右',
                  ),
                  'default'    => 'left'
                ),
          
                //偏移
                array(
                  'id'       => 'layer_text_offset',
                  'type'     => 'spacing',
                  'title'    => '文字层偏移',
                  'subtitle' => '输入正负值，调整上下左右偏移量',
                  'units' => array( 'px' ),
                  'bottom'    => false,
                  'right'    => false,
                  'default'  => array(
                    'top'    => '50',
                    'left'   => '100',
                    'unit'   => 'px',
                  ),
                ),
              )
              ),
      
            //图片层
            array(
              'title'     => '图片层',
              'icon'      => 'fa fa-images',
              'fields'    => array(
                array(
                  'id'           => 'layer_image',
                  'type'         => 'upload',
                  'title'        => '叠加图像',
                  'library'      => 'image',
                  'button_title' => '媒体库上传',
                  'remove_title' => '移除图片',
                  'preview'      => true,
                  'desc'         => '最好是PNG格式',
                ),
      
                //缩放比例
                array(
                  'id'          => 'layer_img_s',
                  'type'        => 'number',
                  'title'       => '图片缩放比例',
                  'unit'        => '%',
                  'default'     => 80,
                ),
      
                //对齐
                array(
                  'id'         => 'layer_img_align',
                  'type'       => 'radio',
                  'title'      => '文字层对齐',
                  'inline'     => true,
                  'options'    => array(
                    'left' => '居左',
                    'center' => '居中',
                    'right' => '居右',
                  ),
                  'default'    => 'left'
                ),
      
                //偏移
                array(
                  'id'       => 'layer_img_offset',
                  'type'     => 'spacing',
                  'title'    => '图像层偏移',
                  'subtitle' => '输入正负值，调整上下左右偏移量',
                  'units' => array( 'px' ),
                  'bottom'    => false,
                  'right'    => false,
                  'default'  => array(
                    'top'    => '50',
                    'left'   => '100',
                    'unit'   => 'px',
                  ),
                ),

                array(
                  'id'    => 'layer_image_pax',
                  'type'  => 'textarea',
                  'subtitle' => '一行一个,具体内容参见下方【视差动画参数】',
                  'title' => '视差动画',
                ),
              )
            ),


          )
        ),

        //叠加层布局
        array(
          'id'         => 'addlayer_order',
          'type'       => 'button_set',
          'title'      => '叠加层布局',
          'dependency' => array( 'slide_layer_on', '==', 'true', '', 'visible' ),
          'options'    => array(
            'hor'  => '横向',
            'ver' => '纵向',
          ),
          'default'    => 'hor'
        ),

        //叠加层反转
        array(
          'id'    => 'addlayer_rev',
          'type'  => 'switcher',
          'title' => '叠加层顺序反转',
          'default' => true,
          'text_on'    => '反转',
          'text_off'   => '默认',
          'text_width' => 70,
          'dependency' => array( 'slide_layer_on', '==', 'true', '', 'visible' ),
        ),

      ),
      'accordion_title_prefix' => '轮播',
    ),

    //视差参数
    array(
      'id'            => 'slide_px_info',
      'type'          => 'accordion',
      'title'         => '视差动画参数',
      'accordions'    => array(
        array(
          'title'     => '点击查看',
          'fields'    => array(
            array(
              'type'     => 'callback',
              'function' => 'slide_px_info',
            ),
          )
        ),
      )
    ),
    

    //轮播5右侧菜单
    array(
      'type'    => 'subheading',
      'content' => '轮播右侧菜单(仅幻灯5可用)',
      'dependency' => array( 'slide_style', '==', 'slide5' ),
    ),

    array(
      'id'        => 'slide_nav',
      'type'      => 'group',
      'title'     => '轮播右侧菜单',
      'fields'    => array(

        array(
          'id'           => 'img',
          'type'         => 'upload',
          'title'        => '图标',
          'library'      => 'image',
          'button_title' => '媒体库上传',
          'remove_title' => '移除图片',
          'preview'      => true,
          'desc'         => '可以从媒体库上传，也可以直接输入图片链接',
        ),

        array(
          'id'           => 'link',
          'type'         => 'link',
          'title'        => '链接',
          'add_title'    => '添加链接',
          'edit_title'   => '编辑链接',
          'remove_title' => '移除链接',
        ),

      ),
      'dependency' => array( 'slide_style', '==', 'slide5' ),
      
    ),


    array(
      'type'    => 'subheading',
      'content' => '轮播总设置',
    ),

    array(
      'id'          => 'slide_ainimate',
      'type'        => 'radio',
      'title'       => '轮播动画',
      'inline'      => true,
      'options'     => array(
        'slide'  => '滑动',
        'fade'  => '淡入',
        'coverflow'  => '覆盖',
        'creative'  => '缩放',
      ),
      'default'     => 'slide'
    ),

    array(
      'id'    => 'slide_auto',
      'type'  => 'switcher',
      'title' => '自动轮播',
      'default' => true,
    ),

    array(
      'id'    => 'slide_title_on',
      'type'  => 'switcher',
      'title' => '标题显示',
      'default' => true,
    ),

    array(
      'id'          => 'slide_w',
      'type'        => 'select',
      'title'       => '轮播宽度',
      'options'     => array(
        'full'  => '全宽',
        'box'  => '跟随主体',
      ),
      'default'     => 'box',
      'dependency' => array( 'slide_style', '==', 'slide1' ),
    ),

    array(
      'id'          => 'slide_h',
      'type'        => 'spinner',
      'title'       => '轮播高度',
      'min'         => 200,
      'max'         => 2000,
      'step'        => 10,
      'unit'        => 'px',
      'output'      => '.swiper',
      'output_mode' => 'height',
      'default'     => 700,
    ),

    array(
      'id'    => 'slide_h_viewport',
      'type'  => 'switcher',
      'title' => '全屏高度',
      'default' => false,
      'subtitle' => '开启后轮播高度强制匹配视窗高度',
    ),

    array(
      'id'    => 'slide_v',
      'type'  => 'number',
      'title' => '动画速度(像素/毫秒)',
      'default'  => '2',
      'desc'  => '直接填入数字，默认为2,数字越大越快',
    ),

  )
) ); 

//首页设置
CSF::createSection( $prefix, array(
  'title'  => '首页设置',
  'parent' => 'mod_set',
  'icon'   => 'fa fa-rocket',
  'fields' => array(




  )
) ); 

//底部设置
CSF::createSection( $prefix, array(
  'title'  => '底部设置',
  'parent' => 'mod_set',
  'icon'   => 'fa fa-rocket',
  'fields' => array(



  )
) ); 


//模块设置---------------------------------------------------------------------------------- end

//备份设置---------------------------------------------------------------------------------- start
CSF::createSection( $prefix, array(
  'title'  => '备份设置',
  'icon'   => 'fa fa-rocket',
  'fields' => array(


    array(
      'type' => 'backup',
    ),

  )
) ); 

