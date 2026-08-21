<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

//
// Set a unique slug-like ID
//
$moment_name = get_op('moment_name','片刻');
$moments_name = get_op('moments_name','圈子');
$prefix = 'ppo_options';
$pix_theme_version = wp_get_theme()->get('Version');
$pix_admin_settings_url = admin_url('admin.php?page=pix-settings');
$pix_setup_guide_url = function_exists('ppo_get_admin_csf_url') ? ppo_get_admin_csf_url('site_panel') : $pix_admin_settings_url . '#tab=site_panel';

//
// Create options
//
CSF::createOptions( $prefix, array(
  'framework_title' => '<div class="pix-admin-header-brand"><img src="'. THEME_URL .'/img/logo-text.png" alt="Glintide"><span class="pix-admin-header-version">v' . esc_html($pix_theme_version) . '</span></div>',
  'menu_title'    => 'Glintide 设置',
  'menu_slug'     => 'pix-settings',
  'menu_position' => 100.1,
  'class'         => 'pix-options',
  'menu_icon'     => THEME_URL .'/img/opicon.png',
) );

// 经典模式设置（整体从外观-自定义迁移）
// 站点设置
CSF::createSection( $prefix, array(
  'id'    => 'site_panel',
  'title' => '站点设置',
  'icon'  => 'ri-global-line',
  'priority' => 2,
) );

CSF::createSection( $prefix, array(
      'id'     => 'global_basic_setting',
      'parent' => 'site_panel',
      'title'  => '站点信息',
      'fields' => array(

        array(
          'id'    => 'site_logo',
          'type'  => 'media',
          'title' => '站点LOGO(深色)',
          'library' => 'image',
        ),

        array(
          'id'    => 'site_logo_w',
          'type'  => 'media',
          'title' => '站点LOGO(浅色)',
          'library' => 'image',
        ),

        array(
          'id'      => 'logo_text',
          'type'    => 'text',
          'title'   => '文字LOGO',
        ),

        array(
          'id'      => 'mobile_logo_h',
          'type'    => 'slider',
          'title'   => '移动端LOGO高度',
          'desc'    => '控制手机端顶栏与抽屉菜单中 LOGO 的显示高度',
          'min'     => 0,
          'max'     => 100,
          'step'    => 1,
          'unit'    => 'px',
          'output'  => array(
            'height'     => '.pix-mobile-topbar-logo img, .pix-mobile-drawer-logo img',
            'max-height' => '.pix-mobile-topbar-logo img, .pix-mobile-drawer-logo img',
          ),
        ),

        array(
          'id'      => 'classic_logo_h',
          'type'    => 'slider',
          'title'   => '桌面端LOGO高度',
          'desc'    => '控制电脑端顶部导航中 LOGO 的显示高度',
          'min'     => 0,
          'max'     => 100,
          'step'    => 1,
          'unit'    => 'px',
          'default' => 22,
          'output'  => '.classic-logo a img',
          'output_mode' => 'height',
        ),

        array(
          'id'      => 'favicon',
          'type'    => 'media',
          'title'   => '网站图标（favicon）',
          'library' => 'image',
        ),

        array(
          'id'          => 'admin_login_logo',
          'type'        => 'media',
          'title'       => 'WP 后台登录页 LOGO',
          'library'     => 'image',
          'desc'        => '用于 wp-admin 登录页，建议上传 64 x 64px 的正方形 PNG 或 SVG。未设置时使用站点深色 LOGO。',
        ),

        array(
          'id'         => 'def_thum_type',
          'type'       => 'radio',
          'title'      => '自定义缩略图类型',
          'inline'     => true,
          'options'    => array(
            'local' => '本地上传',
            'link'  => '外链',
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
          'desc'        => '可上传多个，建议尺寸小一点',
          'dependency'  => array( 'def_thum_type', '==', 'local' ),
        ),

        array(
          'id'          => 'def_thum_link',
          'type'        => 'textarea',
          'title'       => '自定义外链缩略图',
          'desc'        => '一行一个，请保证图片源稳定',
          'dependency'  => array( 'def_thum_type', '==', 'link' ),
        ),

      ),
    ) );

    //全局样式
    CSF::createSection( $prefix, array(
      'parent' => 'site_panel',
      'title'  => '全局样式',
      'fields' => array(

        array(
          'id'                              => 'bg-c',
          'type'                            => 'background',
          'title'                           => '背景色/图案',
          'background_gradient'             => true,
          'background_origin'               => true,
          'background_clip'                 => true,
          'background_blend_mode'           => true,
          'output'                          => 'body',
          'default'                         => array(
            'background-color'              => '#f3efff',
            'background-gradient-color'     => '#e7f1ff',
            'background-gradient-direction' => '135deg',
            'background-size'               => 'cover',
            'background-position'           => 'center center',
            'background-repeat'             => 'no-repeat',
          ),
        ),

      ),
    ) );

    //页脚设置
    CSF::createSection( $prefix, array(
      'id'     => 'global_footer_setting',
      'parent' => 'site_panel',
      'title'  => '页脚设置',
      'fields' => array(

        array(
          'id'      => 'footer_text',
          'type'    => 'wp_editor',
          'title'   => '页脚版权信息',
          'desc'    => '支持HTML和短代码',
          'tinymce' => true,
          'default' => '© ' . date('Y') . ' ' . get_bloginfo('name') . ' All rights reserved.',
        ),

        array(
          'id'           => 'footer_icp',
          'type'         => 'link',
          'title'        => '备案号',
          'desc'         => '填写ICP备案号及链接，显示在页脚右侧',
          'add_title'    => '添加备案号',
          'edit_title'   => '修改备案号',
          'remove_title' => '移除备案号',
        ),

        array(
          'id'      => 'footer_bg',
          'type'    => 'color',
          'title'   => '页脚背景色',
          'output'  => '.site-footer',
          'output_mode' => 'background-color',
          'default' => '#ffffff',
        ),

        array(
          'id'      => 'footer_text_color',
          'type'    => 'color',
          'title'   => '页脚文本色',
          'output'  => '.site-footer',
          'output_mode' => 'color',
          'default' => '#39364bff',
        ),

        array(
          'id'      => 'back_top_bg_color',
          'type'    => 'color',
          'title'   => '回到顶部按钮颜色',
          'output'  => '.pix-global-back-top',
          'output_mode' => 'background-color',
          'default' => '#3157ff',
        ),

        array(
          'id'      => 'back_top_icon_color',
          'type'    => 'color',
          'title'   => '回到顶部图标颜色',
          'output'  => '.pix-global-back-top',
          'output_mode' => 'color',
          'default' => '#ffffff',
        ),

        array(
          'id'          => 'back_top_radius',
          'type'        => 'number',
          'title'       => '回到顶部按钮圆角',
          'unit'        => 'px',
          'output'      => '.pix-global-back-top',
          'output_mode' => 'border-radius',
          'default'     => 16,
        ),

        array(
          'id'      => 'footer_widgets',
          'type'    => 'switcher',
          'title'   => '显示页脚小工具',
          'default' => true,
        ),

      ),
    ) );

    //移动端底部菜单
    // 导航
CSF::createSection( $prefix, array(
  'id'    => 'nav_panel',
  'title' => '导航',
  'icon'  => 'ri-menu-line',
  'priority' => 3,
) );

CSF::createSection( $prefix, array(
      'id'     => 'header_mode_setting',
      'parent' => 'nav_panel',
      'title'  => '头部模式',
      'fields' => array(

        array(
          'id'      => 'header_mode',
          'type'    => 'radio',
          'title'   => '头部模式',
          'inline'  => true,
          'options' => array(
            'classic'  => '原头部（顶部导航栏）',
            'floating' => '悬浮工具栏（无头部）',
          ),
          'default' => 'floating',
          'desc'    => '选择显示原顶部导航栏，或移除头部改用右下角悬浮工具栏',
        ),

        array(
          'id'      => 'content_radius',
          'type'    => 'slider',
          'title'   => '内容区顶部圆角',
          'desc'    => '内容区左上、右上角的圆角大小',
          'min'     => 0,
          'max'     => 40,
          'step'    => 1,
          'unit'    => 'px',
          'default' => 12,
        ),

        array(
          'id'      => 'site_top_margin_classic',
          'type'    => 'slider',
          'title'   => '经典模式距顶部距离',
          'desc'    => '经典头部模式下，页面内容距浏览器顶部的距离',
          'min'     => 0,
          'max'     => 200,
          'step'    => 1,
          'unit'    => 'px',
          'default' => 60,
        ),

      ),
    ) );

CSF::createSection( $prefix, array(
      'id'     => 'global_mobile_bottom_nav',
      'parent' => 'nav_panel',
      'title'  => '移动端菜单',
      'fields' => array(

        array(
          'id'    => 'mobile_bottom_nav_tabs',
          'type'  => 'tabbed',
          'title' => '',
          'tabs'  => array(
            array(
              'title'  => '基础设置',
              'icon'   => 'ri-settings-3-line',
              'fields' => array(
                array(
                  'id'      => 'mobile_bottom_nav_enable',
                  'type'    => 'switcher',
                  'title'   => '启用移动端底部菜单',
                  'default' => pix_mobile_bottom_nav_default('mobile_bottom_nav_enable', true),
                ),
                array(
                  'id'      => 'mobile_bottom_nav_show_title',
                  'type'    => 'switcher',
                  'title'   => '显示菜单标题',
                  'desc'    => '关闭后底部菜单仅显示图标；发布按钮始终只显示加号。',
                  'default' => pix_mobile_bottom_nav_default('mobile_bottom_nav_show_title', false),
                ),
              ),
            ),
            array(
              'title'  => '按钮 1',
              'icon'   => 'ri-home-5-line',
              'fields' => array(
                array(
                  'id'      => 'mobile_bottom_nav_item_1_title',
                  'type'    => 'text',
                  'title'   => '按钮标题',
                  'default' => pix_mobile_bottom_nav_default('mobile_bottom_nav_item_1_title', '首页'),
                ),
                array(
                  'id'      => 'mobile_bottom_nav_item_1_icon',
                  'type'    => 'icon',
                  'title'   => '按钮图标',
                  'default' => pix_mobile_bottom_nav_default('mobile_bottom_nav_item_1_icon', 'ri-home-5-line'),
                ),
                array(
                  'id'      => 'mobile_bottom_nav_item_1_url',
                  'type'    => 'text',
                  'title'   => '按钮链接',
                  'default' => pix_mobile_bottom_nav_default('mobile_bottom_nav_item_1_url', home_url('/')),
                ),
              ),
            ),
            array(
              'title'  => '按钮 2',
              'icon'   => 'ri-bubble-chart-line',
              'fields' => array(
                array(
                  'id'      => 'mobile_bottom_nav_item_2_title',
                  'type'    => 'text',
                  'title'   => '按钮标题',
                  'default' => pix_mobile_bottom_nav_default('mobile_bottom_nav_item_2_title', ppo_moment_label('moment')),
                ),
                array(
                  'id'      => 'mobile_bottom_nav_item_2_icon',
                  'type'    => 'icon',
                  'title'   => '按钮图标',
                  'default' => pix_mobile_bottom_nav_default('mobile_bottom_nav_item_2_icon', 'ri-bubble-chart-line'),
                ),
                array(
                  'id'      => 'mobile_bottom_nav_item_2_url',
                  'type'    => 'text',
                  'title'   => '按钮链接',
                  'default' => pix_mobile_bottom_nav_default('mobile_bottom_nav_item_2_url', get_post_type_archive_link('moment') ? get_post_type_archive_link('moment') : home_url('/' . ppo_moment_slug('moment_slug', 'moment') . '/')),
                ),
              ),
            ),
          ),
        ),

      ),
    ) );

    //用户菜单
    CSF::createSection( $prefix, array(
      'id'     => 'global_user_menu',
      'parent' => 'nav_panel',
      'title'  => '用户菜单',
      'icon'   => 'ri-user-settings-line',
      'fields' => array(

        array(
          'id'            => 'user_menu_items',
          'type'          => 'sorter',
          'title'         => '菜单项目',
          'enabled_title' => '已启用',
          'disabled_title'=> '未启用',
          'desc'          => '控制头像下拉菜单和移动端用户抽屉显示的项目，可拖动调整顺序，最多展示 8 项。',
          'default'       => array(
            'enabled' => array(
              'center'  => '个人中心',
              'submit'  => '投稿管理',
              'order'   => '我的订单',
              'task'    => '任务中心',
              'account' => '账号设置',
            ),
            'disabled' => array(
              'wallet'  => '我的钱包',
              'vip'     => '会员订阅',
              'collect' => '我的收藏',
              'message' => '消息中心',
              'comment' => '我的评论',
            ),
          ),
        ),

      ),
    ) );

    //经典模式导航
    CSF::createSection( $prefix, array(
      'id'     => 'classic_nav_setting',
      'parent' => 'nav_panel', 
      'title'  => '顶部导航',
      'fields' => array(

        array(
          'type'    => 'submessage',
          'style'   => 'notice',
          'class'   => 'tab-msg-w',
          'content' => '基础设置',
        ),

        array(
          'id'            => 'classic_nav_base_tab',
          'type'          => 'tabbed',
          'title'         => '',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(
                
                array(
                  'id'    => 'nav_padding',
                  'type'  => 'spacing',
                  'title' => '内边距',
                  'top'  => false,
                  'bottom' => false,
                  'units' => array( 'px' ),
                  'output'      => '.classic-header-bg',
                  'output_mode' => 'padding',
                ),

                array(
                  'id'      => 'nav_height',
                  'type'    => 'slider',
                  'title'   => '导航高度',
                  'min'     => 0,
                  'max'     => 100,
                  'step'    => 1,
                  'unit'    => 'px',
                  'default' => 72,
                  'output'  => '.classic-header',
                  'output_mode' => 'height',
                ),

                array(
                  'id'      => 'classic_nav_align',
                  'type'    => 'select',
                  'title'   => '菜单位置',
                  'options' => array(
                    'left'   => '左侧',
                    'center' => '居中',
                  ),
                  'default' => 'left',
                  'desc'    => '菜单显示在头部左侧（紧跟Logo）或正中间',
                ),

                array(
                  'id'      => 'site_top_margin',
                  'type'    => 'slider',
                  'title'   => '距顶部距离',
                  'desc'    => '控制页面内容距浏览器顶部的距离（桌面端，移动端自动适配）',
                  'min'     => 0,
                  'max'     => 200,
                  'step'    => 1,
                  'unit'    => 'px',
                  'default' => 65,
                ),

              )
            ),
            array(
              'title'     => '设计',
              'fields'    => array(
                
                array(
                  'id'          => 'nav_bg',
                  'type'        => 'color',
                  'title'       => '导航背景色',
                  'output'      => '.classic-header-bg',
                  'output_mode' => 'background-color',
                  'default'     => '#ffffff',
                ),

                array(
                  'id'          => 'sticky_bg',
                  'type'        => 'color',
                  'title'       => '吸附状态背景色',
                  'output'      => '.classic-header-bg.pix-sticky-fixed',
                  'output_mode' => 'background-color',
                  'default'     => 'rgb(245 246 255 / 80%)',
                  'subtitle'    => '设置为半透状态，会有毛玻璃效果'
                ),

              )
            ),
          )
        ),

        array(
          'type'    => 'submessage',
          'style'   => 'notice',
          'class'   => 'tab-msg-w',
          'content' => 'LOGO设置',
        ),

        array(
          'id'            => 'classic_logo_tab',
          'type'          => 'tabbed',
          'title'         => '',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(
                
                array(
                  'id'         => 'classic_logo_type',
                  'type'       => 'button_set',
                  'title'      => '站点LOGO类型',
                  'options'    => array(
                    'text'  => '标题 | 描述',
                    'img' => '图像',
                  ),
                  'default'    => 'text'
                ),
        
                array(
                  'id'      => 'classic_title',
                  'type'    => 'text',
                  'title'   => '站点标题',
                  'default' => 'PIXIT',
                  'dependency' => array( 'classic_logo_type', '==', 'text' ),
                ),
        
                array(
                  'id'      => 'classic_des',
                  'type'    => 'text',
                  'title'   => '站点描述',
                  'default' => 'born for design',
                  'dependency' => array( 'classic_logo_type', '==', 'text' ),
                ),
        
                array(
                  'id'           => 'classic_logo',
                  'type'         => 'upload',
                  'title'        => '站点LOGO',
                  'library'      => 'image',
                  'placeholder'  => 'http://',
                  'button_title' => '添加LOGO',
                  'remove_title' => '移除LOGO',
                  'preview'      => true,
                  'dependency' => array( 'classic_logo_type', '==', 'img' ),
                ),
        
                array(
                  'id'    => 'classic_logo_mar',
                  'type'  => 'spacing',
                  'title' => '左右边距',
                  'top'  => false,
                  'bottom' => false,
                  'units' => array( 'px' ),
                  'default' => array(
                    'left'  => '20',
                    'right' => '20',
                    'unit'  => 'px',
                  ),
                  'output'      => '.classic-header .logo-area',
                  'output_mode' => 'margin',
                ),

              )
            ),
            array(
              'title'     => '设计',
              'fields'    => array(
                
                array(
                  'id'      => 'classic_title_c',
                  'type'    => 'typography',
                  'title'   => '标题样式',
                  'font_family'   => false,
                  'line_height'   => false,
                  'text_align'   => false,
                  'preview'   => false,
                  'default' => array(
                    'color'       => '#23282d',
                    'font-size'   => '15',
                    'unit'        => 'px',
                  ),
                  'output'  => '.classic-logo h4',
                ),

                array(
                  'id'      => 'classic_des_c',
                  'type'    => 'typography',
                  'title'   => '描述样式',
                  'font_family'   => false,
                  'line_height'   => false,
                  'text_align'   => false,
                  'preview'   => false,
                  'default' => array(
                    'color'       => '#23282d',
                    'font-size'   => '15',
                    'unit'        => 'px',
                  ),
                  'output'  => '.classic-logo span.des',
                ),

              )
            ),
          )
        ),

        

        array(
          'type'    => 'submessage',
          'style'   => 'notice',
          'class'   => 'tab-msg-w',
          'content' => '菜单设置',
        ),

        array(
          'id'    => 'classic_nav_on',
          'type'  => 'switcher',
          'title' => '开启菜单',
          'default' => false,
          'desc'  => '是否开启经典模式顶部菜单，此处不支持二级菜单'
          ),
  
        array(
          'id'          => 'classic_nav_id',
          'type'        => 'select',
          'title'       => '选择菜单',
          'placeholder' => '选择菜单',
          'options'     => 'menus', 
        ),

        array(
          'id'            => 'classic_nav',
          'type'          => 'tabbed',
          'title'         => '',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(
                
                array(
                  'id'        => 'classic_effects_type',
                  'type'      => 'image_select',
                  'title'     => '悬浮类型(悬浮条|悬浮块)',
                  'class'     => 'prinav',
                  'options'   => array(
                      'line' => THEME_URL .'/img/mod/tnavst1.png',
                      'boxt' => THEME_URL .'/img/mod/tnavst2.png',    
                  ),
                  'default'   => 'line',
                  ),

                  array(
                    'id'         => 'effects',
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
                      'id'      => 'padding',
                      'type'    => 'slider',
                      'title'   => '项目间距',
                      'min'     => 0,
                      'max'     => 100,
                      'step'    => 1,
                      'unit'    => 'px',
                      'default' => 15,
                      'output_mode' => 'padding',
                      'output'  => '.classic-nav ul li a'
                    ),

                    array(
                      'id'    => 'classic_nav_mar',
                      'type'  => 'spacing',
                      'title' => '左右边距',
                      'top'  => false,
                      'bottom' => false,
                      'units' => array( 'px' ),
                      'output'      => '.classic-header .center-area',
                      'output_mode' => 'margin',
                    ),

              )
            ),
            array(
              'title'     => '设计',
              'fields'    => array(
                
                array(
                  'id'      => 'font',
                  'type'    => 'typography',
                  'title'   => '字体样式',
                  'font_family'   => false,
                  'line_height'   => false,
                  'text_align'   => false,
                  'preview'   => false,
                  'default' => array(
                    'color'       => '#0a0000',
                    'font-size'   => '15',
                    'unit'        => 'px',
                  ),
                  'output'  => '.classic-nav ul li a',
                ),

                array(
                  'id'      => 'hover_text',
                  'type'    => 'color',
                  'title'   => '悬停',
                  'default' => '#0400ff',
                  'output'      => '.classic-nav ul li a:hover',
                  'output_mode' => 'color'
                ),

                array(
                  'id'                              => 'line_color',
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
                  'output'   => '.classic-nav.line ul li a .nav-link-item:before',
                  'dependency' => array( 'classic_effects_type', '==', 'line', 'all' ),
                  ),

                  array(
                    'id'       => 'line_h',
                    'type'     => 'dimensions',
                    'title'    => '悬浮条高度',
                    'width'    => false,
                    'units'   => array('px'),
                    'default'  => array(
                        'height' => '2',
                    ),
                    'output' => '.classic-nav.line ul li a .nav-link-item:before',
                    'dependency' => array( 'classic_effects_type', '==', 'line', 'all' ),
                    ),

                  array(
                    'id'    => 'line_top',
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
                    'output' => '.classic-nav.line ul li a .nav-link-item:before',
                    'dependency' => array( 'classic_effects_type', '==', 'line', 'all' ),
                    ),
        
                    array(
                    'id'    => 'line_ra',
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
                    'output' => '.classic-nav.line ul li a .nav-link-item:before',
                    'dependency' => array( 'classic_effects_type', '==', 'line', 'all' ),
                    ),  

                    //box------------------------------------------------------------
                    //悬浮块背景色
                    array(
                      'id'                              => 'box_color',
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
                      'output'                          => '.classic-nav.boxt ul li a:after',
                      'dependency' => array( 'classic_effects_type', '==', 'boxt', 'all' ),
                      ),

          
                      //悬浮块高度
                      array(
                      'id'       => 'box_h',
                      'type'     => 'dimensions',
                      'title'    => '悬浮块高度',
                      'width'    => false,
                      'units'   => array('%','px'),
                      'default'  => array(
                          'height' => '50',
                          'unit'   => '%',
                      ),
                      'output' => '.classic-nav.boxt ul li a:after',
                      'desc' => '建议数值：50%-80%',
                      'dependency' => array( 'classic_effects_type', '==', 'boxt', 'all' ),
                      ),
          
                      //悬浮块圆角
                      array(
                      'id'    => 'box_ra',
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
                      'output' => '.classic-nav.boxt ul li a:after',
                      'dependency' => array( 'classic_effects_type', '==', 'boxt', 'all' ),
                      ),
          
                      //悬浮块描边
                      array(
                      'id'     => 'box_border',
                      'type'   => 'border',
                      'title'  => '悬浮块描边',
                      'all'   => true,
                      'output' => '.classic-nav.boxt ul li a:after',
                      'default' => array(
                          'color'  => 'rgba(255,255,255,0)',
                          'all'    => '0',
                      ),
                      'dependency' => array( 'classic_effects_type', '==', 'boxt', 'all' ),
                      ),

              )
            ),
          )
        ),
  
      )
    ) );

    // 经典banner
    // 首页
CSF::createSection( $prefix, array(
  'id'    => 'home_panel',
  'title' => '首页',
  'icon'  => 'ri-home-4-line',
  'priority' => 4,
) );

CSF::createSection( $prefix, array(
      'id'     => 'classic_banner_setting',
      'parent' => 'home_panel', 
      'title'  => '顶部封面',
      'fields' => array(

        array(
          'type'    => 'submessage',
          'style'   => 'notice',
          'class'   => 'tab-msg-w',
          'content' => '封面设置',
        ),

        array(
          'id'      => 'cls_banner_h',
          'type'    => 'slider',
          'title'   => '封面高度',
          'min'     => 100,
          'max'     => 400,
          'step'    => 1,
          'unit'    => 'px',
          'default' => 200,
          'output'  => '.cls-banner',
          'output_mode' => 'height',
        ),

        array(
          'id'      => 'cls_banner_h_moment',
          'type'    => 'slider',
          'title'   => '片刻页封面高度',
          'desc'    => '单独控制片刻首页与片刻详情页的封面高度，留空时跟随上方「封面高度」',
          'min'     => 100,
          'max'     => 400,
          'step'    => 1,
          'unit'    => 'px',
          'output'  => '.cls-banner-moment',
          'output_mode' => 'height',
        ),

        array(
          'id'      => 'cls_banner_radius_t',
          'type'    => 'slider',
          'title'   => '封面圆角(上面)',
          'desc'    => '控制封面上方左、右两个角的圆角大小',
          'min'     => 0,
          'max'     => 60,
          'step'    => 1,
          'unit'    => 'px',
          'default' => 0,
          'output'  => array(
            'border-top-left-radius'  => '.cls-banner, .cls-banner img, .cls-banner:before',
            'border-top-right-radius' => '.cls-banner, .cls-banner img, .cls-banner:before',
          ),
        ),

        array(
          'id'      => 'cls_banner_radius_b',
          'type'    => 'slider',
          'title'   => '封面圆角(下面)',
          'desc'    => '控制封面下方左、右两个角的圆角大小',
          'min'     => 0,
          'max'     => 60,
          'step'    => 1,
          'unit'    => 'px',
          'default' => 0,
          'output'  => array(
            'border-bottom-left-radius'  => '.cls-banner, .cls-banner img, .cls-banner:before',
            'border-bottom-right-radius' => '.cls-banner, .cls-banner img, .cls-banner:before',
          ),
        ),

        array(
          'id'          => 'cls_banner_cover',
          'type'        => 'color',
          'title'       => '封面遮罩',
          'output'      => '.cls-banner:before',
          'output_mode' => 'background-color',
          'default' => 'rgb(53 60 172 / 23%)',
          'subtitle'    => '调整成半透明色，让文字更突出',
        ),

        array(
          'id'         => 'cls_banner_type',
          'type'       => 'radio',
          'title'      => '封面类型',
          'inline'     => true,
          'options'    => array(
            'upload' => '媒体库上传',
            'link' => '自定义外链',
          ),
          'default'    => 'upload'
        ),

        array(
          'id'          => 'cls_banner_upload',
          'type'        => 'gallery',
          'title'       => '封面上传',
          'add_title'   => '添加封面',
          'edit_title'  => '编辑封面',
          'clear_title' => '移除封面',
          'subtitle'    => '可上传多张封面',
          'dependency' => array( 'cls_banner_type', '==', 'upload'),
        ),

        array(
          'id'      => 'cls_banner_link',
          'type'    => 'textarea',
          'title'   => '自定义封面外链',
          'placeholder' => '一行一个封面链接',
          'dependency' => array( 'cls_banner_type', '==', 'link'),
        ),

        array(
          'type'    => 'submessage',
          'style'   => 'notice',
          'class'   => 'tab-msg-w',
          'content' => '封面内容',
        ),

        array(
          'id'         => 'cls_banner_content',
          'type'       => 'radio',
          'title'      => '内容类型',
          'inline'     => true,
          'options'    => array(
            'text' => '自定义文字展示',
            'ava' => '头像信息展示',
          ),
          'default'    => 'ava'
        ),

        array(
          'id'            => 'opt-wp-editor-2',
          'type'          => 'wp_editor',
          'title'         => '',
          'tinymce'       => true,
          'quicktags'     => true,
          'media_buttons' => true,
          'height'        => '200px',
          'dependency' => array( 'cls_banner_content', '==', 'text'),
        ),

        array(
          'type'    => 'content',
          'content' => '头像信息机制：未登录状态将显示站长头像，昵称以及个人描述，登录后则显示登录者信息',
          'dependency' => array( 'cls_banner_content', '==', 'ava'),
        ),

        array(
          'id'          => 'cls_banner_name',
          'type'        => 'color',
          'title'       => '昵称',
          'output'      => '.cls-banner-info .info .name',
          'output_mode' => 'color',
          'default' => '#ffffff',
        ),

        array(
          'id'          => 'cls_banner_des',
          'type'        => 'color',
          'title'       => '个人描述',
          'output'      => '.cls-banner-info .info .des',
          'output_mode' => 'color',
          'default' => '#dad5ff',
        ),

        array(
          'id'      => 'cls_banner_avas',
          'type'    => 'slider',
          'title'   => '头像尺寸',
          'min'     => 0,
          'max'     => 64,
          'step'    => 1,
          'unit'    => 'px',
          'output'      => '.cls-banner-info .ava img',
          'output_mode' => 'width',
          'default' => 64,
        ),

        array(
          'id'          => 'cls_banner_avar',
          'type'        => 'spacing',
          'title'       => '头像圆角',
          'output'      => '.cls-banner-info .ava img',
          'output_mode' => 'border-radius', 
          'all'         => true,
          'default'     => array(
            'all'       => '8',
            'unit'      => 'px',
          ),
        ),

    )
    ) );  

    //首页设置
    CSF::createSection( $prefix, array(
      'id'     => 'classic_home_setting',
      'parent' => 'home_panel', 
      'title'  => '首页设置',
      'fields' => array(

        array(
          'id'         => 'cls_home_type',
          'type'       => 'radio',
          'title'      => '首页类型',
          'inline'  => true,
          'options'    => array(
            'blog' => '博客',
            'moment' => '片刻',
          ),
          'default'    => 'blog',
        ),

        array(
          'id'          => 'cls_show_cats',
          'type'        => 'select',
          'title'       => '文章首页筛选分类',
          'placeholder' => '选择分类',
          'chosen'      => true,
          'multiple'    => true,
          'sortable'    => true,
          'options'     => 'categories',
          'desc'        => '如果设定了默认展示分类，此处就不要重复添加',
          'dependency' => array( 'cls_home_type', '==', 'blog'),
        ),

        array(
          'id'          => 'cls_show_cats_de',
          'type'        => 'select',
          'title'       => '文章默认展示分类',
          'placeholder' => '选择分类',
          'chosen'      => true,
          'options'     => 'categories',
          'desc'        => '默认展示的分类文章，不选择则默认全部分类',
          'dependency' => array( 'cls_home_type', '==', 'blog'),
        ),

        array(
          'id'          => 'moment_default_cat',
          'type'        => 'select',
          'title'       => '发布默认圈子',
          'placeholder' => '不设置默认圈子',
          'options'     => 'categories',
          'chosen'      => true,
          'query_args'  => array(
            'taxonomy'  => 'moments',
            'hide_empty' => false,
          ),
          'desc'        => '设置后，在片刻首页发布时会默认选中该圈子；如果用户未加入该圈子，则仍需手动选择可发布的圈子。',
          'dependency' => array( 'cls_home_type', '==', 'moment'),
        ),

        // 片刻首页设置
        array(
          'id'    => 'mos_home_hot_show',
          'type'  => 'switcher',
          'title' => '开启片刻首页圈子推荐',
          'dependency' => array( 'cls_home_type', '==', 'moment'),
          'default' => false,
        ),


        array(
          'id'          => 'mos_home_hot',
          'type'        => 'select',
          'title'       => '片刻首页圈子推荐展示',
          'placeholder' => '选择圈子',
          'options'     => 'categories',
          'chosen'      => true,
          'multiple'    => true,
          'sortable'    => true,
          'settings'     => array(
            'number' => 6,
          ),
          'query_args'  => array(
            'taxonomy'  => 'moments',
          ),
          'desc'        => '选择多个圈子，首页推荐展示,最多展示6个',
          'dependency' => array( 'cls_home_type|mos_home_hot_show', '==|==', 'moment|true'),
        ),

        // 展示方式
        array(
          'id'      => 'mos_home_show_type',
          'type'    => 'radio',
          'title'   => '推荐圈子显示方式',
          'options'    => array(
            'grid' => '网格',
            'slider' => '轮播',
          ),
          'inline'  => true,
          'default' => 'grid',
          'dependency' => array( 'cls_home_type|mos_home_hot_show', '==|==', 'moment|true'),
        ),

        // array(
        //   'type'     => 'callback',
        //   'function' => 'ppo_cu_line',
        // ),

        array(
          'id'    => 'mos_home_notice_show',
          'type'  => 'switcher',
          'title' => '开启片刻首页公告',
          'dependency' => array( 'cls_home_type', '==', 'moment'),
          'default' => false,
        ),

        array(
          'id'      => 'mos_home_notice',
          'type'    => 'textarea',
          'title'   => '片刻首页公告',
          'placeholder' => '公告内容',
          'default' => '这是一条公告',
          'dependency' => array( 'cls_home_type|mos_home_notice_show', '==|==', 'moment|true'),
        ),

        array(
          'id'    => 'mos_home_notice_link',
          'type'  => 'link',
          'title' => '片刻首页公告链接',
          'desc'  => '链接文字不用填写',
          'dependency' => array( 'cls_home_type|mos_home_notice_show', '==|==', 'moment|true'),
        ),

    )
    ) );  
// 侧栏布局（三栏布局设置，原位于外观-自定义-经典模式-外观设置）// 侧栏布局（三栏布局设置，原位于外观-自定义-经典模式-外观设置）
CSF::createSection( $prefix, array(
  'id'     => 'classic_sidebar_setting',
  'parent' => 'home_panel',
  'title'  => '侧栏布局',
  'icon'   => 'ri-layout-3-column',
  'fields' => array(

    //侧边栏宽度
    array(
      'id'      => 'sidebar_width',
      'type'    => 'slider',
      'title'   => '侧边栏宽度',
      'min'     => 240,
      'max'     => 640,
      'step'    => 10,
      'unit'    => 'px',
      'default' => 320,
      'output'  => array('.left-widget','.right-widget'),
      'output_mode' => 'max-width',
    ),

    array(
      'id'      => 'classic_center_width',
      'type'    => 'slider',
      'title'   => '中间内容宽度',
      'min'     => 0,
      'max'     => 1920,
      'step'    => 10,
      'unit'    => 'px',
      'default' => 640,
      'output'  => array('body.classic .home-classic .center-content'),
      'output_mode' => 'max-width',
    ),

    array(
      'id'      => 'author_width',
      'type'    => 'slider',
      'title'   => '用户主页宽度',
      'min'     => 0,
      'max'     => 1920,
      'step'    => 10,
      'unit'    => 'px',
      'default' => 1280,
      'desc'    => '一般不需要修改，保持1280px就可以，特殊情况可进行适当调整'
    ),

     array(
      'id'      => 'dashboard_width',
      'type'    => 'slider',
      'title'   => '用户中心宽度',
      'min'     => 0,
      'max'     => 1920,
      'step'    => 10,
      'unit'    => 'px',
      'default' => 1280,
      'desc'    => '一般不需要修改，保持1280px就可以，特殊情况可进行适当调整'
    ),

    array(
      'id'          => 'cls_center_bg',
      'type'        => 'color',
      'title'       => '中间内容背景色',
      'output'      => '.cls-content',
      'output_mode' => 'background-color',
      'default' => '#ffffff'
      ),

    array(
      'id'          => 'cls_wid_bg',
      'type'        => 'color',
      'title'       => '左右边栏背景色',
      'output'      => '.home-classic',
      'output_mode' => 'background-color',
      'default' => '#f6f7ff'
      ),

    array(
      'id'          => 'cls_left_wid',
      'type'  => 'switcher',
      'title' => '开启左边栏',
      'desc'  => '关闭后首页左侧栏不再显示',
      'default' => true,
    )  ,

    array(
      'id'          => 'cls_right_wid',
      'type'  => 'switcher',
      'title' => '开启右边栏',
      'desc'  => '关闭后首页右侧栏不再显示',
      'default' => true,
    ),

  )
) ); 


// 文章设置
// 内容
CSF::createSection( $prefix, array(
  'id'    => 'content_panel',
  'title' => '内容',
  'icon'  => 'ri-article-line',
  'priority' => 5,
) );

// 片刻设置（重建）
CSF::createSection( $prefix, array(
  'id'     => 'moment_set',
  'parent' => 'content_panel',
  'title'  => '片刻设置',
  'icon'   => 'ri-chat-3-line',
  'fields' => array(
    array(
      'id'      => 'moment_name',
      'type'    => 'text',
      'title'   => '片刻名称',
      'default' => '片刻',
      'class'   => 'mini-input',
    ),
    array(
      'id'      => 'moment_slug',
      'type'    => 'text',
      'title'   => '片刻别名',
      'default' => 'moment',
      'desc'    => '修改后需在「固定链接」页面重新保存一次',
      'class'   => 'mini-input',
    ),
    array(
      'id'      => 'moments_name',
      'type'    => 'text',
      'title'   => '圈子名称(片刻的分类)',
      'default' => '圈子',
      'class'   => 'mini-input',
    ),
    array(
      'id'      => 'moments_slug',
      'type'    => 'text',
      'title'   => '圈子别名',
      'default' => 'moments',
      'desc'    => '修改后需在「固定链接」页面重新保存一次',
      'class'   => 'mini-input',
    ),
    array(
      'id'      => 'moments_owner',
      'type'    => 'text',
      'title'   => '圈主名称',
      'default' => '圈主',
      'class'   => 'mini-input',
    ),
    array(
      'id'      => 'moments_user',
      'type'    => 'text',
      'title'   => '圈友名称',
      'default' => '圈友',
      'class'   => 'mini-input',
    ),
    array(
      'id'      => 'moment_nav',
      'type'    => 'radio',
      'title'   => '片刻翻页方式',
      'options' => array(
        'btn'     => '加载更多按钮',
        'pagenav' => '页码分页',
        'scroll'  => '滚动加载',
      ),
      'inline'  => true,
      'default' => 'btn',
    ),
    array(
      'id'          => 'moments_tag',
      'type'        => 'repeater',
      'title'       => '圈子标签',
      'button_title' => '添加标签',
      'fields'      => array(
        array(
          'id'    => 'name',
          'type'  => 'text',
          'title' => '标签名称',
          'class' => 'mini-input',
        ),
      ),
    ),
  ),
) );

// 片刻全局（重建）
CSF::createSection( $prefix, array(
  'id'     => 'moment_global',
  'parent' => 'content_panel',
  'title'  => '片刻全局',
  'icon'   => 'ri-global-line',
  'fields' => array(
    array(
      'id'      => 'mo_join_fun',
      'type'    => 'checkbox',
      'title'   => '圈子功能开启',
      'options' => array(
        'gallery' => '图集',
        'video'   => '视频',
        'card'    => '卡片',
      ),
      'default' => array('gallery', 'video', 'card'),
    ),
    array(
      'id'      => 'allow_card_group',
      'type'    => 'checkbox',
      'title'   => '允许插入卡片的用户组',
      'options' => 'all_lv_merge',
    ),
    array(
      'id'      => 'mo_gallery_num',
      'type'    => 'number',
      'title'   => '最大图集数量',
      'default' => 9,
      'unit'    => '张',
    ),
    array(
      'id'      => 'mo_gallery_link',
      'type'    => 'switcher',
      'title'   => '允许外链图集',
      'default' => true,
    ),
    array(
      'id'      => 'mo_card_num',
      'type'    => 'number',
      'title'   => '最多插入卡片数量',
      'default' => 3,
      'unit'    => '个',
    ),
    array(
      'id'      => 'mo_file_num',
      'type'    => 'number',
      'title'   => '最多上传文件数量',
      'default' => 3,
      'unit'    => '个',
    ),
    array(
      'id'      => 'mo_text_num',
      'type'    => 'text',
      'title'   => '发布字数限制',
      'desc'    => '格式：最小-最大，例如 5-240',
      'default' => '5-240',
      'class'   => 'mini-input',
    ),
  ),
) );

// 圈子重建（重建）
CSF::createSection( $prefix, array(
  'id'     => 'moment_rebuild',
  'parent' => 'content_panel',
  'title'  => '圈子重建',
  'icon'   => 'ri-refresh-line',
  'fields' => array(
    array(
      'type'     => 'callback',
      'function' => 'rebuild_moments_panel',
    ),
  ),
) );

CSF::createSection( $prefix, array(
  'title'  => '文章设置',
  'icon'   => 'ri-article-line',
  'parent' => 'content_panel',
  'fields' => array(

    array(
      'id'         => 'post_nav',
      'type'       => 'radio',
      'title'      => '文章翻页方式',
      'options'    => array(
        'pagenav' => '页码分页',
        'btn' => '加载更多按钮',
        'scroll' => '滚动加载',
        //'scroll' => '无限滚动加载',
      ),
      'default'    => 'btn',
      'inline'     => true,
    ),

    array(
      'id'      => 'post_image_lightbox',
      'type'    => 'switcher',
      'title'   => '文章图片灯箱效果',
      'label'   => '开启后，文章正文中的图片会自动添加 Fancybox 灯箱效果',
      'default' => true,
    ),

    array(
      'id'      => 'post_toc_enable',
      'type'    => 'switcher',
      'title'   => '文章标题目录',
      'label'   => '开启后，桌面端文章内页右侧显示标题目录，点击目录可滚动到对应标题',
      'default' => false,
    ),

    array(
      'id'      => 'post_toc_levels',
      'type'    => 'radio',
      'title'   => '目录标题层级',
      'options' => array(
        'h2-h3' => 'H2-H3',
        'h1-h3' => 'H1-H3',
        'h2-h4' => 'H2-H4',
      ),
      'default' => 'h2-h3',
      'inline'  => true,
      'dependency' => array( 'post_toc_enable', '==', true ),
    ),
    

  )
 ) ); 

//评论设置
CSF::createSection( $prefix, array(
  'title'  => '评论设置',
  'icon'   => 'ri-chat-3-line',
  'parent' => 'content_panel',
  'fields' => array(

    array(
      'id'         => 'comment_nav',
      'type'       => 'radio',
      'title'      => '评论翻页方式',
      'options'    => array(
        'pagenav' => '页码分页',
        'btn' => '加载更多按钮',
        //'scroll' => '无限滚动加载',
      ),
      'default'    => 'pagenav',
      'inline'     => true,
    ),

    array(
      'id'      => 'comment_per_page',
      'type'    => 'spinner',
      'title'   => '每页评论数',
      'desc'    => '用于评论页码分页和加载更多，不再调用 WordPress 默认每页评论数',
      'default' => 10,
      'min'     => 1,
      'max'     => 50,
      'step'    => 1,
      'unit'    => '条',
    ),

    array(
      'id'      => 'comment_url_info',
      'type'    => 'switcher',
      'title'   => '游客网址信息',
      'label'   => '开启后显示游客评论网址表单，关闭则不显示',
      'default' => true
    ),

    // 评论ip归属地
    array(
      'id'      => 'comment_location',
      'type'    => 'switcher',
      'title'   => '评论IP归属地',
      'desc'   => '开启后提交的评论才会显示归属地，如果您服务器使用的加速代理之类的优化，可能获取不到IP，IP在国外也获取不到，又或者您开启了科学，也可能获取不到',
      'default' => false
    ),

    array(
      'id'      => 'comment_image_enable',
      'type'    => 'switcher',
      'title'   => '评论图片上传',
      'label'   => '允许用户在片刻评论中上传图片',
      'default' => true
    ),

    array(
      'id'      => 'comment_image_limit',
      'type'    => 'spinner',
      'title'   => '评论图片数量',
      'desc'    => '单条评论最多允许上传或插入的图片数量',
      'default' => 4,
      'min'     => 1,
      'max'     => 12,
      'step'    => 1,
      'unit'    => '张',
      'dependency' => array('comment_image_enable', '==', true),
    ),

    array(
      'id'      => 'comment_image_max_size',
      'type'    => 'spinner',
      'title'   => '评论图片大小',
      'desc'    => '单张评论图片最大体积',
      'default' => 2,
      'min'     => 1,
      'max'     => 20,
      'step'    => 1,
      'unit'    => 'MB',
      'dependency' => array('comment_image_enable', '==', true),
    ),


  )
) );

//媒体设置
CSF::createSection( $prefix, array(
  'title'  => '媒体设置',
  'icon'   => 'ri-image-line',
  'parent' => 'content_panel',
  'fields' => array(

    array(
      'id'         => 'allow_image_group',
      'type'       => 'checkbox',
      'title'      => '允许上传图片的用户组',
      'inline'       => true,
      'options'    => 'all_lv_merge',
      'check_all' => true,
      'check_all_text' => '全选/取消全选',
      'default'    => array()
    ),

    array(
      'id'          => 'image_max_size',
      'type'        => 'number',
      'title'       => '允许上传图片最大体积',
      'unit'        => 'MB',
      'default'     => 3,
    ),

    array(
      'type'    => 'subheading',
      'content' => '图片压缩设置',
    ),

    array(
      'id'      => 'image_compress_enable',
      'type'    => 'switcher',
      'title'   => '开启图片前端压缩',
      'label'   => '开启后，图片会在浏览器中压缩后再上传；大小限制仍按用户选择的原始文件判断。',
      'default' => true,
    ),

    array(
      'id'      => 'image_convert_webp',
      'type'    => 'switcher',
      'title'   => '转换为 WebP',
      'label'   => '开启后，非 GIF 图片会转换为 WebP 格式上传。',
      'default' => false,
      'dependency' => array( 'image_compress_enable', '==', true ),
    ),

    array(
      'id'          => 'image_compress_quality',
      'type'        => 'number',
      'title'       => '图片压缩质量',
      'unit'        => '%',
      'default'     => 86,
      'desc'        => '建议 75-90，数值越高画质越好、体积越大。',
      'dependency' => array( 'image_compress_enable', '==', true ),
    ),

    array(
      'id'          => 'image_compress_width',
      'type'        => 'select',
      'title'       => '图片缩放尺寸',
      'options'     => array(
        'original' => '保持原尺寸，仅压缩质量',
        '2560'     => '最长边缩放到 2560px',
        '1920'     => '最长边缩放到 1920px',
        '1600'     => '最长边缩放到 1600px',
        '1280'     => '最长边缩放到 1280px',
      ),
      'default'     => '1920',
      'dependency' => array( 'image_compress_enable', '==', true ),
    ),

    array(
      'id'         => 'allow_video_group',
      'type'       => 'checkbox',
      'title'      => '允许上传视频的用户组',
      'inline'       => true,
      'options'    => 'all_lv_merge',
      'check_all' => true,
      'check_all_text' => '全选/取消全选',
      'default'    => array()
    ),

    array(
      'id'          => 'video_max_size',
      'type'        => 'number',
      'title'       => '允许上传视频最大体积',
      'unit'        => 'MB',
      'default'     => 20,
    ),

    array(
      'id'         => 'allow_file_group',
      'type'       => 'checkbox',
      'title'      => '允许上传文件的用户组',
      'inline'       => true,
      'options'    => 'all_lv_merge',
      'check_all' => true,
      'check_all_text' => '全选/取消全选',
      'default'    => true
    ),

    array(
      'id'          => 'file_max_size',
      'type'        => 'number',
      'title'       => '允许上传文件最大体积',
      'unit'        => 'MB',
      'default'     => 10,
    ),

    array(
      'type'    => 'subheading',
      'content' => 'PixUploader 2.0 上传限制',
    ),

    array(
      'id'          => 'pix_upload_rate_per_minute',
      'type'        => 'number',
      'title'       => '每用户每分钟上传请求数',
      'unit'        => '次',
      'default'     => 20,
      'desc'        => '限制 PixUploader 2.0 的服务端上传请求频率，0 表示不限制。',
    ),

    array(
      'id'          => 'pix_upload_daily_file_limit',
      'type'        => 'number',
      'title'       => '每用户每日上传附件数',
      'unit'        => '个',
      'default'     => 100,
      'desc'        => '按成功上传的附件数量统计，0 表示不限制。',
    ),

    array(
      'id'          => 'pix_upload_daily_size_limit',
      'type'        => 'number',
      'title'       => '每用户每日上传总量',
      'unit'        => 'MB',
      'default'     => 300,
      'desc'        => '按成功上传的文件体积统计，0 表示不限制。媒体库总容量限制后续单独处理。',
    ),

    array(
      'id'      => 'pix_upload_include_wp_library',
      'type'    => 'switcher',
      'title'   => '前台媒体库包含后台上传附件',
      'label'   => '开启后，前台“我的媒体”会显示当前用户在后台媒体库上传的普通附件；这些附件只能插入，不能在前台删除。',
      'default' => true,
    ),

  )
) );


// 用户中心
// 用户
CSF::createSection( $prefix, array(
  'id'    => 'user_panel',
  'title' => '用户',
  'icon'  => 'ri-user-smile-line',
  'priority' => 6,
) );

CSF::createSection( $prefix, array(
  'parent' => 'user_panel', 
  'title'  => '用户中心',
  'icon'   => 'ri-user-line',
  'fields' => array(

    array(
      'id'           => 'user_avatar',
      'type'         => 'upload',
      'title'        => '用户默认头像',
      'library'      => 'image',
      'placeholder'  => 'http://',
      'button_title' => '添加头像',
      'remove_title' => '移除头像',
      'preview'      => true,
      'default'      => THEME_URL.'/img/ava.png',
    ),

    array(
      'type'    => 'subheading',
      'content' => '普通用户权限',
    ),

    array(
      'id'      => 'normal_user_allow_moment',
      'type'    => 'switcher',
      'title'   => '普通用户允许发片刻',
      'default' => true,
      'desc'    => '关闭后，非会员普通用户将不能发布片刻内容。',
    ),

    array(
      'id'      => 'normal_user_allow_comment',
      'type'    => 'switcher',
      'title'   => '普通用户允许发表评论',
      'default' => true,
      'desc'    => '关闭后，普通用户无法发送评论。',
    ),

    array(

      'id'      => 'normal_user_allow_create_circle',
      'type'    => 'switcher',
      'title'   => '普通用户允许创建圈子',
      'default' => false,
      'desc'    => '开启后，普通用户可在前台创建圈子；管理员和会员仍按原有权限执行。',
    ),

    array(
      'id'      => 'normal_user_allow_edit_profile',
      'type'    => 'switcher',
      'title'   => '普通用户允许修改个人资料',
      'default' => true,
      'desc'    => '控制昵称、简介、网址、性别等基础资料修改，不影响绑定邮箱、手机号和修改密码。',
    ),

    array(
      'id'         => 'normal_user_private_msg_rule',
      'type'       => 'button_set',
      'title'      => '普通用户私信规则',
      'options'    => array(
        'off'        => '关闭',
        'mutual'     => '仅互关',
        'follow_once'=> '关注后一条',
        'follow'     => '关注后不限',
        'open'       => '完全开放',
      ),
      'default'    => 'follow_once',
      'desc'       => '管理员不受限制，会员仍按原有规则执行。',
    ),

    array(
      'type'    => 'subheading',
      'content' => '普通用户上传',
    ),

    array(
      'id'      => 'normal_user_allow_upload_image',
      'type'    => 'switcher',
      'title'   => '普通用户图片上传总开关',
      'default' => true,
      'desc'    => '关闭后，普通用户所有图片上传场景都会被禁止。',
    ),

    array(
      'id'      => 'normal_user_allow_upload_avatar',
      'type'    => 'switcher',
      'title'   => '普通用户允许上传头像',
      'default' => true,
      'desc'    => '控制用户中心头像上传和头像切换。',
    ),

    array(
      'id'      => 'normal_user_allow_upload_cover',
      'type'    => 'switcher',
      'title'   => '普通用户允许上传个人封面',
      'default' => true,
      'desc'    => '控制用户主页封面图上传。',
    ),

    array(
      'id'      => 'normal_user_allow_upload_comment_image',
      'type'    => 'switcher',
      'title'   => '普通用户允许上传评论图片',
      'default' => true,
      'desc'    => '仍会受到评论图片上传总开关和大小、数量限制影响。',
    ),

    array(
      'id'      => 'normal_user_allow_upload_moment_image',
      'type'    => 'switcher',
      'title'   => '普通用户允许上传片刻图片',
      'default' => true,
      'desc'    => '控制片刻发布器中的图片附件和视频封面。',
    ),

    array(
      'id'      => 'normal_user_allow_upload_post_image',
      'type'    => 'switcher',
      'title'   => '普通用户允许上传文章图片',
      'default' => false,
      'desc'    => '预留给后续前台发文使用，包含文章缩略图等文章图片场景。',
    ),

    array(
      'id'      => 'normal_user_allow_upload_video',
      'type'    => 'switcher',
      'title'   => '普通用户允许上传视频',
      'default' => false,
      'desc'    => '开启后普通用户可上传视频类附件。',
    ),

    array(
      'id'      => 'normal_user_allow_upload_file',
      'type'    => 'switcher',
      'title'   => '普通用户允许上传文件',
      'default' => false,
      'desc'    => '开启后普通用户可上传文档、压缩包等文件类附件。',
    ),
   
  )
) );  

Option_Level::res();

// 消息设置
CSF::createSection( $prefix, array(
  'parent' => 'user_panel',
  'title'  => '消息通知',
  'icon'   => 'ri-mail-line',
  'fields' => array(

    array(
      'id'        => 'notice_bot',
      'type'      => 'fieldset',
      'title'     => '消息机器人',
      'fields'    => array(
        
        array(
          'id'        => 'sys_bot',
          'type'      => 'fieldset',
          'title'     => '系统机器人',
          'fields'    => array(
            array(
              'id'    => 'name',
              'type'  => 'text',
              'title' => '机器人名称',
            ),
            array(
              'id'    => 'avatar',
              'type'  => 'media',
              'title' => '机器人头像',
              'library' => 'image',
            ),
            array(
              'id'    => 'des',
              'type'  => 'text',
              'title' => '机器人描述',
            ),
          ),
          'default' => array('name' => '系统小助手', 'avatar' => THEME_URL.'/img/icon/sys-bot.png', 'des' => '为您发送系统消息的小助手'),
        ),

         array(
          'id'        => 'moment_bot',
          'type'      => 'fieldset',
          'title'     => '片刻机器人',
          'fields'    => array(
            array(
              'id'    => 'name',
              'type'  => 'text',
              'title' => '机器人名称',
            ),
            array(
              'id'    => 'avatar',
              'type'  => 'media',
              'title' => '机器人头像',
              'library' => 'image',
            ),
            array(
              'id'    => 'des',
              'type'  => 'text',
              'title' => '机器人描述',
            ),
          ),
          'default' => array('name' => '片刻小助手', 'avatar' => THEME_URL.'/imgicon/moment-bot.png', 'des' => '为您发送片刻消息的小助手'),
        ),

      ),
     
    ),
    

   
  )
) ); 

CSF::createSection( $prefix, array(
  'parent' => 'user_panel', 
  'title'  => '登录设置',
  'icon'   => 'ri-key-2-line',
  'fields' => array(

    // 开启注册
    array(
      'id'    => 'reg_open',
      'type'  => 'switcher',
      'title' => '开启注册',
      'default' => true,
      'desc' => '建议将wp设置->常规中的<任何人都可以注册>的勾选项去掉，防止机器人注册。',
    ),

    array(
      'type'    => 'subheading',
      'content' => '登录设置',
    ),

     //是否开启行为验证
     array(
      'id'    => 'log_action_verify',
      'type'  => 'switcher',
      'title' => '是否开启登录行为验证',
      'default' => true,
      'desc' => '开启后请前往安全设置中，设置行为验证方式和参数',
    ),

    // 管理员跳过行为验证
    array(
      'id'    => 'admin_captcha',
      'type'  => 'switcher',
      'title' => '管理员跳过登录行为验证',
      'default' => true,
      'desc' => '开启后，管理员登录不需要行为验证',
    ),

    // 是否开启免密登录
    array(
      'id'    => 'free_login',
      'type'  => 'switcher',
      'title' => '是否开启免密登录',
      'default' => false,
      'desc' => '开启后用户可通过接收手机验证码，通过验证登录',
    ),

    //免密验证方式
    array(
      'id'         => 'nopass_check_type',
      'type'       => 'button_set',
      'title'      => '免密验证方式',
      'options'    => array(
        'phone'  => '手机验证',
        'email' => '邮箱验证',
        'all' => '两者皆可',
      ),
      'default'    => 'all',
      'desc' => '手机验证请前往短信验证设置中设置，邮箱验证请安装xx插件，两者皆可可自动识别并发送验证码',
      'dependency' => array( 'free_login', '==', 'true' ),
    ),

    //默认登录类型
    array(
      'id'         => 'def_login',
      'type'       => 'button_set',
      'title'      => '默认登录方式',
      'options'    => array(
        'normal'  => '账户密码登录',
        'nopass' => '免密登录',
      ),
      'default'    => 'normal',
      'dependency' => array( 'free_login', '==', 'true' ),
    ),

    array(
      'type'    => 'subheading',
      'content' => '注册设置',
    ),

    //注册验证方式
    array(
      'id'         => 'reg_check_type',
      'type'       => 'button_set',
      'title'      => '注册验证方式',
      'options'    => array(
        'phone'  => '手机验证',
        'email' => '邮箱验证',
        'all' => '手机和邮箱',
        'invite' => '邀请码',
        'normal' => '无',
      ),
      'default'    => 'normal',
      'desc' => '手机验证 : 请前往短信验证设置中设置<br>邮箱验证 : 请安装xx插件<br>手机和邮箱 : 可自动识别并发送验证码<br>邀请码 : 在后台生成后分发给他人，有邀请码的人才能注册'
    ),

    // 开启行为验证
    array(
      'id'    => 'reg_action_verify',
      'type'  => 'switcher',
      'title' => '提交注册行为验证',
      'default' => true,
      'desc' => '开启后请前往安全设置中，设置行为验证方式和参数',
      'dependency' => array( 'reg_check_type', '==', 'normal' ),
    ),

    // 注册时必填昵称
    array(
      'id'         => 'reg_nickname_required',
      'type'       => 'switcher',
      'title'      => '注册时必填昵称',
      'default'    => true,
      'desc'       => '关闭后注册表单将不显示昵称输入框',
    ),

    //新用户注册消息
    array(
      'id'      => 'reg_msg',
      'type'    => 'textarea',
      'title'   => '新用户注册消息',
      'default' => '恭喜您注册成功！',
      'desc' => '新用户注册成功后，消息中心会有一条系统消息，在此设置消息内容。支持变量：{name}、{nickname}、{username}'
    ),

    //协议
    array(
      'id'      => 'reg_privacy',
      'type'    => 'text',
      'title'   => '隐私政策链接',
      'desc' => '填写后将会显示在注册表单中'
    ),

    array(
      'id'      => 'reg_protocol',
      'type'    => 'text',
      'title'   => '用户协议链接',
      'desc' => '填写后将会显示在注册表单中'
    ),

    array(
      'type'    => 'subheading',
      'content' => '密码找回',
    ),

    array(
      'id'         => 'pwd_check_type',
      'type'       => 'button_set',
      'title'      => '密码找回验证方式',
      'options'    => array(
        'phone'  => '手机验证',
        'email' => '邮箱验证',
        'all' => '两者皆可',
      ),
      'default'    => 'all',
      'desc' => '手机验证请前往短信验证设置中设置，邮箱验证请安装xx插件，两者皆可可自动识别并发送验证码',
    ),

  )
) );   

CSF::createSection( $prefix, array(
  'parent' => 'user_panel', 
  'title'  => '社交登录设置',
  'icon'   => 'ri-share-forward-box-line',
  'fields' => array(


    // qq
    array(
      'id'    => 'open_qq',
      'type'  => 'switcher',
      'title' => '开启QQ登录',
      'default' => false,
    ),

    array(
      'id'     => 'open_qq_data',
      'type'   => 'fieldset',
      'title'  => 'QQ登录设置',
      'fields' => array(
        array(
          'id'    => 'appid',
          'type'  => 'text',
          'title' => 'APP ID',
        ),
        array(
          'id'    => 'appkey',
          'type'  => 'text',
          'title' => 'APP Key',
        ),
      ),
      'dependency' => array( 'open_qq', '==', 'true' ),
    ),

    // weibo
    array(
      'id'    => 'open_weibo',
      'type'  => 'switcher',
      'title' => '开启微博登录',
      'default' => false,
    ),

    array(
      'id'     => 'open_weibo_data',
      'type'   => 'fieldset',
      'title'  => '微博登录设置',
      'fields' => array(
        array(
          'id'    => 'appid',
          'type'  => 'text',
          'title' => 'App Key',
        ),
        array(
          'id'    => 'appkey',
          'type'  => 'text',
          'title' => 'App Secret',
        ),
      ),
      'dependency' => array( 'open_weibo', '==', 'true' ),
    ),

    // weixin
    array(
      'id'    => 'open_weixin',
      'type'  => 'switcher',
      'title' => '开启微信登录',
      'default' => false,
    ),

    array(
      'id'     => 'open_weixin_data',
      'type'   => 'fieldset',
      'title'  => '微信登录设置',
      'fields' => array(
        array(
          'id'    => 'appid',
          'type'  => 'text',
          'title' => 'appID',
        ),
        array(
          'id'    => 'appkey',
          'type'  => 'text',
          'title' => 'appsecret',
        ),
      ),
      'dependency' => array( 'open_weixin', '==', 'true' ),
    ),

    // weixin
    array(
      'id'    => 'open_juhe',
      'type'  => 'switcher',
      'title' => '开启彩虹聚合登录',
      'default' => false,
    ),

    array(
      'id'     => 'open_juhe_data',
      'type'   => 'fieldset',
      'title'  => '彩虹聚合登录设置',
      'fields' => array(
        array(
          'id'    => 'juhe_url',
          'type'  => 'text',
          'title' => '接口网址',
        ),
        array(
          'id'    => 'appid',
          'type'  => 'text',
          'title' => 'AppID',
        ),
        array(
          'id'    => 'appkey',
          'type'  => 'text',
          'title' => 'AppKey',
        ),
        array(
          'id'          => 'juhe_type',
          'type'        => 'select',
          'title'       => '选择登录方式',
          'chosen'      => true,
          'multiple'    => true,
          'placeholder' => '选择多个登录方式',
          'options'     => array(
            'qq'  => 'QQ',
            'wx'  => '微信',
            'alipay'  => '支付宝',
            'sina'  => '微博',
            'baidu'  => '百度',
            'xiaomi'  => '小米',
            'microsoft'  => '微软',
            'facebook'  => 'Facebook',
            'dingtalk'  => '钉钉',
            'gitee'  => 'Gitee',
            'github'  => 'GitHub',
            'google'  => '谷歌',
            'huawei' => '华为',
          ),
        ),
      ),
      'dependency' => array( 'open_juhe', '==', 'true' ),
    ),


  )
) );  

CSF::createSection( $prefix, array(
  'parent' => 'user_panel', 
  'title'  => '短信验证设置',
  'icon'   => 'ri-smartphone-line',
  'fields' => array(

    array(
      'id'         => 'sms_type',
      'type'       => 'hidden',
      'default'    => 'aliyunsms',
    ),

    array(
      'type'    => 'content',
      'content' => '<div class="csf-fieldset-content"><strong>当前仅支持：阿里云号码认证短信</strong><p>该方案目前对个人主体相对友好，已接入验证码发送逻辑。聚合数据、中正云等旧选项暂未实现，已从后台移除，避免误选后无法发送验证码。</p></div>',
    ),

    // 阿里云
    array(
      'id'     => 'alisms',
      'type'   => 'fieldset',
      'title'  => '阿里云短信配置',
      'fields' => array(
        array(
          'id'    => 'keyid',
          'type'  => 'text',
          'title' => 'AccessKey ID',
        ),
        array(
          'id'    => 'keysecret',
          'type'  => 'text',
          'title' => 'AccessKey Secret',
        ),
        /* array(
          'id'    => 'signname',
          'type'  => 'text',
          'title' => '签名名称',
        ),
        array(
          'id'    => 'tplcode',
          'type'  => 'text',
          'title' => '模板CODE',
        ), */
      ),
    ),


  )
) );     

//扩展设置
// 安全
CSF::createSection( $prefix, array(
  'id'    => 'safe_panel',
  'title' => '安全',
  'icon'  => 'ri-shield-check-line',
  'priority' => 7,
) );

CSF::createSection( $prefix, array(
  'parent' => 'safe_panel', 
  'title'  => '安全验证',
  'icon'   => 'ri-shield-check-line',
  'fields' => array(

    array(
      'id'         => 'captcha_type',
      'type'       => 'radio',
      'title'      => '安全验证方式',
      'inline'     => true,
      'options'    => array(
        'normal' => '无',
        'ppoc' => 'PPOC验证(主题自带)',
        'pixcap' => 'Pixcap无感验证(主题自带)',
        'code' => '随机验证码',
        'geetest' => 'GEETEST4.0(第三方)',
      ),
      'default'    => 'normal',
      'desc'  => 'GEETEST4.0需前往<a href="https://www.geetest.com/" target="_blank" style="color:#2a2fff">极验证官网</a>进行申请，第四代行为验证'
    ),

    array(
      'id'      => 'geetest_id',
      'type'    => 'text',
      'title'   => '验证ID',
      'dependency' => array( 'captcha_type', '==', 'geetest' ),
    ),

    array(
      'id'      => 'geetest_key',
      'type'    => 'text',
      'title'   => '验证KEY',
      'dependency' => array( 'captcha_type', '==', 'geetest' ),
    ),

    array(
      'id'      => 'ppoc_offset',
      'type'    => 'text',
      'title'   => '滑动误差',
      'desc'    => '建议3-10之间，数值越小，需要滑块匹配的误差越小',
      'default' => '8',
      'dependency' => array( 'captcha_type', '==', 'ppoc' ),
    ),

    array(
      'id'      => 'pixcap_mode',
      'type'    => 'radio',
      'title'   => 'Pixcap显示模式',
      'inline'  => true,
      'options' => array(
        'hidden' => '隐藏无感',
        'bubble' => '悬浮气泡',
        'inline' => '内联卡片',
      ),
      'default' => 'bubble',
      'desc'    => '仅作用于登录、注册、发码、找回密码和用户中心安全操作。评论提交和片刻发布会使用自动轻量气泡，不跟随此显示模式。',
      'dependency' => array( 'captcha_type', '==', 'pixcap' ),
    ),

    array(
      'id'      => 'content_protect_type',
      'type'    => 'radio',
      'title'   => '评论/片刻防刷',
      'inline'  => true,
      'options' => array(
        'pixcap' => 'Pixcap',
        'smart'  => '仅风控',
        'off'    => '关闭',
      ),
      'default' => 'pixcap',
      'desc'    => '仅作用于评论提交和片刻发布，不会调用滑块验证。选择 Pixcap 时会自动完成验证并显示轻量气泡；如需更轻量可切换为“仅风控”。',
    ),

    array(
      'type'    => 'subheading',
      'content' => 'PIXCAP参数',
    ),

    array(
      'id'      => 'pixcap_theme',
      'type'    => 'select',
      'title'   => 'Pixcap主题',
      'options' => array(
        'business' => 'Business',
        'default' => 'Default',
        'light' => 'Light',
        'dark' => 'Dark',
        'aqua' => 'Aqua',
        'caramel' => 'Caramel',
        'cupcake' => 'Cupcake',
        'cyberpunk' => 'Cyberpunk',
        'lime' => 'Lime',
        'wireframe' => 'Wireframe',
      ),
      'default' => 'business',
      'desc'    => 'Pixcap通用主题。登录/注册/发码会按显示模式渲染，评论/片刻防刷会使用同一主题的轻量气泡。',
    ),

    array(
      'id'      => 'pixcap_cost',
      'type'    => 'number',
      'title'   => 'Pixcap计算强度',
      'desc'    => 'Pixcap通用计算强度，默认 50000。数值越高，机器人批量请求成本越高，用户设备验证时间也会增加。建议 30000-80000。',
      'default' => 50000,
    ),

  )
) );

CSF::createSection( $prefix, array(
  'parent' => 'safe_panel',
  'title'  => '内容安全',
  'icon'   => 'ri-shield-user-line',
  'fields' => array(

    array(
      'id'      => 'forbidden_words_enable',
      'type'    => 'switcher',
      'title'   => '启用违禁词拦截',
      'label'   => '发布片刻、评论、私信时检查违禁词',
      'default' => true,
    ),

    array(
      'id'      => 'forbidden_words_list',
      'type'    => 'textarea',
      'title'   => '违禁词词库',
      'desc'    => '每行一个词，也支持用英文逗号分隔。命中后会阻止提交并提示命中的词语。',
      'default' => "傻逼\n脑残\n去死\n滚蛋\n垃圾东西\n人渣\n操你\n草你\n妈的\n辱骂词\n违禁词\n敏感词",
      'sanitize' => false,
    ),

    array(
      'type'    => 'subheading',
      'content' => '私信防刷',
    ),

    array(
      'id'      => 'private_msg_rate_per_minute',
      'type'    => 'number',
      'title'   => '私信每分钟限制',
      'desc'    => '限制单个用户每分钟可发送的私信数量，0 表示不限制。',
      'default' => 10,
    ),

    array(
      'id'      => 'private_msg_rate_per_5min',
      'type'    => 'number',
      'title'   => '私信5分钟限制',
      'desc'    => '限制单个用户每 5 分钟可发送的私信数量，0 表示不限制。',
      'default' => 30,
    ),

    array(
      'id'      => 'private_msg_duplicate_window',
      'type'    => 'number',
      'title'   => '重复私信间隔',
      'desc'    => '相同用户向同一接收者重复发送完全相同内容的冷却秒数，0 表示不限制。',
      'default' => 20,
    ),

  )
) );


//模块设置---------------------------------------------------------------------------------- end

//备份设置---------------------------------------------------------------------------------- start
// 扩展
CSF::createSection( $prefix, array(
  'id'    => 'extend_panel',
  'title' => '扩展',
  'icon'  => 'ri-puzzle-line',
  'priority' => 8,
) );

CSF::createSection( $prefix, array(
  'parent' => 'extend_panel',
  'title'  => 'SEO设置',
  'icon'   => 'ri-search-line',
  'fields' => array(

    array(
      'id'          => 'seo_home_title',
      'type'        => 'text',
      'title'       => '首页标题',
      'desc'        => '留空则使用 站点标题 - 副标题',
    ),

    array(
      'id'          => 'seo_home_description',
      'type'        => 'textarea',
      'title'       => '首页描述',
      'desc'        => '留空则使用站点副标题',
    ),

    array(
      'id'          => 'seo_home_keywords',
      'type'        => 'text',
      'title'       => '首页关键词',
      'desc'        => '多个关键词用英文逗号分隔',
    ),

    array(
      'id'          => 'seo_meta_description',
      'type'        => 'switcher',
      'title'       => '启用meta描述',
      'label'       => '为文章/页面自动生成meta description',
      'default'     => true,
    ),

  )
) );


//安全设置
CSF::createSection( $prefix, array(
  'title'  => '备份设置',
  'icon'   => 'ri-database-2-line',
  'fields' => array(


    array(
      'type' => 'backup',
    ),

  )
) ); 


