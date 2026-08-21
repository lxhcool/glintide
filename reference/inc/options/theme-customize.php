<?php if ( ! defined( 'ABSPATH' )  ) { die; }

if ( ! function_exists( 'pix_mobile_bottom_nav_default' ) ) {
  function pix_mobile_bottom_nav_default( $option, $default = null ) {
    $options = get_option( 'ppo_customizer', array() );
    $tabs = isset( $options['mobile_bottom_nav_tabs'] ) && is_array( $options['mobile_bottom_nav_tabs'] )
      ? $options['mobile_bottom_nav_tabs']
      : array();

    if ( array_key_exists( $option, $tabs ) ) {
      return $tabs[$option];
    }

    return isset( $options[$option] ) ? $options[$option] : $default;
  }
}
// Control core classes for avoid errors
if( class_exists( 'CSF' ) ) {

    //
    // Set a unique slug-like ID
    $prefix = 'ppo_customizer';
  
    //
    // Create customize options
    CSF::createCustomizeOptions( $prefix, array(
      'database'        => 'option',
      'transport'       => 'refresh',
      'capability'      => 'manage_options',
      'save_defaults'   => true,
      'enqueue_webfont' => true,
      'async_webfont'   => false,
      'output_css'      => true,
    ) );

  

    
    // PPO-导航构建器（功能未开发，暂时隐藏）
    // CSF::createSection( $prefix, array(
    //   'id' => 'nav-builder',
    //   'title'  => 'PPO-导航构建器',
    //   'priority' => 10,
    //   'description' => '6666'
    // ));

    // //拖拽容器
    // CSF::createSection( $prefix, array(
    //   'id' => 'hb-drag-box',
    //   'title'  => '导航布局模块',
    //   'fields' => array(
  
    //     array(
    //       'id'    => 'hblist',
    //       'title' => '导航元素',
    //       'type'  => 'hblist',
    //       'class' => 'hb-drag-wrap',
    //     ),
  
    //   )
    // ) );

    // //盛放容器
    // CSF::createSection( $prefix, array(
    //   'id' => 'nav-group',
    //   'title'  => '导航域',
    //   'fields' => array(
  
    //     //
    //     // A text field
    //     array(
    //       'id'      => 'nav-desktop-items',
    //       'type'     => 'hbgroup',
    //       'title'   => '导航构建器',
    //     ),
  
    //   )
    // ) ); 

    //主要行
    CSF::createSection( $prefix, array(
      'id' => 'hb-center',
      'title'  => '主要行',
      'parent'  => 'nav-builder',
      'fields' => array(

        array(
          'id'            => 'hb_center_tab',
          'type'          => 'tabbed',
          'title'         => '',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(

                array(
                  'id'         => 'type',
                  'type'       => 'button_set',
                  'title'      => '容器结构',
                  'options'    => array(
                    'norwidth'  => '默认',
                    'box' => '盒子',
                    'fullwidth' => '全宽',
                  ),
                  'default'    => 'norwidth'
                ),

                array(
                  'id'         => 'sticky',
                  'type'       => 'button_set',
                  'title'      => '吸附效果',
                  'options'    => array(
                    'normal'  => '跟随',
                    'sticky' => '始终固定',
                    'showup' => '回滚显示',
                  ),
                  'default'    => 'normal'
                ),
               
                array(
                  'id'      => 'height',
                  'type'    => 'slider',
                  'title'   => '最小高度',
                  'min'     => 0,
                  'max'     => 200,
                  'step'    => 1,
                  'unit'    => 'px',
                  'default' => 100,
                  'output_mode' => 'min-height',
                  'output'    => '.center-nav-row.main-nav-item',

                ),

              )
            ),
            array(
              'title'     => '设计',
              'fields'    => array(

                array(
                  'id'          => 'bg',
                  'type'        => 'color',
                  'title'       => '背景色',
                  'output'      => '.center-nav-row.main-nav-item , .center-nav-row.main-nav-item.box .nav-bg-box',
                  'default'     => '#ffffff', 
                  'output_mode' => 'background-color' 
                ),
                
                array(
                  'id'           => 'img',
                  'type'         => 'upload',
                  'title'        => '导航背景图',
                  'library'      => 'image',
                  'placeholder'  => 'http://',
                  'preview'      => true,
                  'button_title' => '添加图片',
                  'remove_title' => '移除图片',
                  ),

                array(
                'id'    => 'glass',
                'type'  => 'switcher',
                'title' => '吸附状态毛玻璃',
                'default' => true,
                'desc'  => '导航条吸附状态下，毛玻璃效果'
                ),  

                array(
                  'id'          => 'glass_bg',
                  'type'        => 'color',
                  'title'       => '吸附状态背景色',
                  'output'      => '.center-nav-row.main-nav-item.pix-sticky-fixed , .center-nav-row.main-nav-item.box.pix-sticky-fixed .nav-bg-box',
                  'output_mode' => 'background-color',
                  'desc'  => '请设置为半透明效果',
                  'default'  => 'rgba(255,255,255,0.65)',
                  ),

                array(
                'id'          => 'glass_text',
                'type'        => 'color',
                'title'       => '吸附状态文本色',
                'output'      => array(
                           '.center-nav-row.main-nav-item.pix-sticky-fixed #primary_menu > li > a'
                            ),
                'output_mode' => 'color',
                'default'  => '#0a0a0a',
                ),  

                array(
                  'id'     => 'border_bottom',
                  'type'   => 'border',
                  'title'  => '底部描边',
                  'top'    => false,
                  'left'    => false,
                  'right'    => false,
                  'output' => '.center-nav-row.main-nav-item',
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
                    'id'    => 'shadow',
                    'type'  => 'switcher',
                    'title' => '阴影',
                    'default' => false,
                    ),  

              )
            ),
          )
        ),
        
      )
      ) );
      

    //顶部行
    CSF::createSection( $prefix, array(
      'id' => 'hb-top',
      'title'  => '顶部行',
      'parent'  => 'nav-builder',
      'fields' => array(

        array(
          'id'            => 'hb_top_tab',
          'type'          => 'tabbed',
          'title'         => '',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(

                array(
                  'id'         => 'type',
                  'type'       => 'button_set',
                  'title'      => '容器结构',
                  'options'    => array(
                    'norwidth'  => '默认',
                    'box' => '盒子',
                    'fullwidth' => '全宽',
                  ),
                  'default'    => 'norwidth'
                ),
               
                array(
                  'id'      => 'height',
                  'type'    => 'slider',
                  'title'   => '最小高度',
                  'min'     => 0,
                  'max'     => 200,
                  'step'    => 1,
                  'unit'    => 'px',
                  'default' => 80,
                  'output_mode' => 'min-height',
                  'output'    => '.top-nav-row.main-nav-item',

                ),

              )
            ),
            array(
              'title'     => '设计',
              'fields'    => array(

                array(
                  'id'          => 'bg',
                  'type'        => 'color',
                  'title'       => '背景色',
                  'output'      => '.top-nav-row.main-nav-item , .top-nav-row.main-nav-item.box .nav-bg-box',
                  'default'     => '#ffffff', 
                  'output_mode' => 'background-color' 
                ),
                
                array(
                  'id'           => 'img',
                  'type'         => 'upload',
                  'title'        => '导航背景图',
                  'library'      => 'image',
                  'placeholder'  => 'http://',
                  'preview'      => true,
                  'button_title' => '添加图片',
                  'remove_title' => '移除图片',
                  ),


                array(
                  'id'     => 'border_bottom',
                  'type'   => 'border',
                  'title'  => '底部描边',
                  'top'    => false,
                  'left'    => false,
                  'right'    => false,
                  'output' => '.top-nav-row.main-nav-item',
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
                    'id'    => 'shadow',
                    'type'  => 'switcher',
                    'title' => '阴影',
                    'default' => false,
                    ),  

              )
            ),
          )
        ),
        
      )
      ) );

      //底部行
    CSF::createSection( $prefix, array(
      'id' => 'hb-bottom',
      'title'  => '底部行',
      'parent'  => 'nav-builder',
      'fields' => array(

        array(
          'id'            => 'hb_bottom_tab',
          'type'          => 'tabbed',
          'title'         => '',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(

                array(
                  'id'         => 'type',
                  'type'       => 'button_set',
                  'title'      => '容器结构',
                  'options'    => array(
                    'norwidth'  => '默认',
                    'box' => '盒子',
                    'fullwidth' => '全宽',
                  ),
                  'default'    => 'norwidth'
                ),
               
                array(
                  'id'      => 'height',
                  'type'    => 'slider',
                  'title'   => '最小高度',
                  'min'     => 0,
                  'max'     => 200,
                  'step'    => 1,
                  'unit'    => 'px',
                  'default' => 80,
                  'output_mode' => 'min-height',
                  'output'    => '.bottom-nav-row.main-nav-item',

                ),

              )
            ),
            array(
              'title'     => '设计',
              'fields'    => array(

                array(
                  'id'          => 'bg',
                  'type'        => 'color',
                  'title'       => '背景色',
                  'output'      => '.bottom-nav-row.main-nav-item , .bottom-nav-row.main-nav-item.box .nav-bg-box',
                  'default'     => '#ffffff', 
                  'output_mode' => 'background-color' 
                ),
                
                array(
                  'id'           => 'img',
                  'type'         => 'upload',
                  'title'        => '导航背景图',
                  'library'      => 'image',
                  'placeholder'  => 'http://',
                  'preview'      => true,
                  'button_title' => '添加图片',
                  'remove_title' => '移除图片',
                  ),


                array(
                  'id'     => 'border_bottom',
                  'type'   => 'border',
                  'title'  => '底部描边',
                  'top'    => false,
                  'left'    => false,
                  'right'    => false,
                  'output' => '.bottom-nav-row.main-nav-item',
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
                    'id'    => 'shadow',
                    'type'  => 'switcher',
                    'title' => '阴影',
                    'default' => false,
                    ),  

              )
            ),
          )
        ),
        
      )
      ) );

    //html
    CSF::createSection( $prefix, array(
      'id' => 'hb-html',
      'title'  => '自定义html',
      'parent'  => 'nav-builder',
      'fields' => array(   

        array(
          'id'            => 'html_tab',
          'type'          => 'tabbed',
          'title'         => '',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(

                array(
                  'id'            => 'html_content',
                  'type'          => 'wp_editor',
                  'title'         => '自定义html',
                  'tinymce'       => true,
                  'quicktags'     => true,
                  'media_buttons' => true,
                  'height'        => '200px',
                  'desc'          => '您可以自定义一些HTML内容'
                ),

                array(
                  'id'         => 'login',
                  'type'       => 'button_set',
                  'title'      => '可见性',
                  'multiple' => true,
                  'options'    => array(
                    'login'  => '登录',
                    'logout' => '未登录',
                  ),
                  'default'    => array( 'login', 'logout')
                ),

              )
            ),
            array(
              'title'     => '设计',
              'fields'    => array(
                array(
                  'id'          => 'margin',
                  'type'        => 'spacing',
                  'title'       => '外边距',
                  'output'      => '',
                  'output_mode' => 'margin', // or margin, relative
                  'default'     => array(
                    'top'       => '0',
                    'right'     => '0',
                    'bottom'    => '0',
                    'left'      => '0',
                    'unit'      => 'px',
                  ),
                ),
              )
            ),
          )
        ),

      )
      ) ); 

      //dark
    CSF::createSection( $prefix, array(
      'id' => 'hb-dark',
      'title'  => '暗黑切换',
      'parent'  => 'nav-builder',
      'fields' => array(   

        array(
          'id'            => 'dark_tab',
          'type'          => 'tabbed',
          'title'         => '',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(

                array(
                  'id'      => 'size',
                  'type'    => 'slider',
                  'title'   => '图标尺寸',
                  'min'     => 0,
                  'max'     => 42,
                  'step'    => 1,
                  'unit'    => 'px',
                  'default' => 16,
                  'output_mode' => 'font-size',
                  'output'  => '.hb_dark_inner i',
                ),

                array(
                  'id'         => 'icon_type',
                  'type'       => 'button_set',
                  'title'      => '图标类型',
                  'options'    => array(
                    'line'  => '线性',
                    'fill' => '填充',
                  ),
                  'default'    => 'line'
                ),

              )
            ),
            array(
              'title'     => '设计',
              'fields'    => array(

                array(
                  'type'    => 'submessage',
                  'style'   => 'notice',
                  'class'   => 'tab-msg',
                  'content' => '暗夜图标',
                ),

                array(
                  'id'          => 'dark_color',
                  'type'        => 'color',
                  'title'       => '初始颜色',
                  'output'      => '.hb-sun-icon',
                  'output_mode' => 'color',
                  'default' => '#0a0000',
                ),

                array(
                  'id'          => 'dark_hover',
                  'type'        => 'color',
                  'title'       => '悬浮颜色',
                  'output'      => '.hb-sun-icon:hover',
                  'output_mode' => 'color',
                  'default' => '#0a0000',
                ),

                array(
                  'id'          => 'dark_bg',
                  'type'        => 'color',
                  'title'       => '背景色',
                  'output'      => '',
                  'output_mode' => 'background-color',
                  'default' => '#ffffff',
                ),

                array(
                  'type'    => 'submessage',
                  'style'   => 'notice',
                  'class'   => 'tab-msg',
                  'content' => '白昼图标',
                ),

                array(
                  'id'          => 'moon_color',
                  'type'        => 'color',
                  'title'       => '初始颜色',
                  'output'      => '.hb-moon-icon',
                  'output_mode' => 'color',
                  'default' => '#ffffff',
                ),

                array(
                  'id'          => 'moon_hover',
                  'type'        => 'color',
                  'title'       => '悬浮颜色',
                  'output'      => '.hb-moon-icon:hover',
                  'output_mode' => 'color',
                  'default' => '#ffffff',
                ),

                array(
                  'id'          => 'moon_bg',
                  'type'        => 'color',
                  'title'       => '背景色',
                  'output'      => '',
                  'output_mode' => 'background-color',
                  'default' => '#ffffff',
                ),

                array(
                  'type'    => 'submessage',
                  'style'   => 'notice',
                  'class'   => 'tab-msg',
                  'content' => '其他',
                ),

                array(
                  'id'          => 'round',
                  'type'        => 'spacing',
                  'title'       => '圆角',
                  'output'      => '.hb_dark_inner',
                  'output_mode' => 'border-radius', // or margin, relative
                  'default'     => array(
                    'top'       => '5',
                    'right'     => '5',
                    'bottom'    => '5',
                    'left'      => '5',
                    'unit'      => 'px',
                  ),
                ),

                array(
                  'id'          => 'padding',
                  'type'        => 'spacing',
                  'title'       => '内边距',
                  'output'      => '.hb_dark_inner',
                  'output_mode' => 'padding', // or margin, relative
                  'default'     => array(
                    'top'       => '10',
                    'right'     => '10',
                    'bottom'    => '10',
                    'left'      => '10',
                    'unit'      => 'px',
                  ),
                ),

                array(
                  'id'          => 'margin',
                  'type'        => 'spacing',
                  'title'       => '外边距',
                  'output'      => '.hb_dark_inner',
                  'output_mode' => 'margin',
                  'default'     => array(
                    'top'       => '0',
                    'right'     => '0',
                    'bottom'    => '0',
                    'left'      => '0',
                    'unit'      => 'px',
                  ),
                ),



              )
            ),
          )
        ),

      )
      ) ); 

    //btna
    CSF::createSection( $prefix, array(
      'id' => 'hb-btna',
      'title'  => '按钮1',
      'parent'  => 'nav-builder',
      'fields' => array(  

        array(
          'id'            => 'btna_tab',
          'type'          => 'tabbed',
          'title'         => '',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(

                array(
                  'id'         => 'login',
                  'type'       => 'button_set',
                  'title'      => '可见性',
                  'multiple' => true,
                  'options'    => array(
                    'login'  => '登录',
                    'logout' => '未登录',
                  ),
                  'default'    => array( 'login', 'logout')
                ),

                array(
                  'id'    => 'icon',
                  'type'  => 'icon',
                  'title' => '图标',
                ),

                array(
                  'id'      => 'title',
                  'type'    => 'text',
                  'title'   => '文本',
                  'default' => '按钮'
                ),

                array(
                  'id'      => 'url',
                  'type'    => 'text',
                  'title'   => '链接',
                  'default' => '#'
                ),

                array(
                  'id'      => 'new',
                  'type'    => 'switcher',
                  'title'   => '新标签页打开',
                  'default' => true
                ),

                array(
                  'id'      => 'nofollow',
                  'type'    => 'switcher',
                  'title'   => '链接nofollow',
                  'default' => false
                ),

                array(
                  'id'      => 'css',
                  'type'    => 'text',
                  'title'   => 'css类',
                  'subtitle' => '多个类请用空格分开'
                ),

              )
            ),
            array(
              'title'     => '设计',
              'fields'    => array(
                
                array(
                  'id'      => 'title_c',
                  'type'    => 'typography',
                  'title'   => '文本样式',
                  'font_family'   => false,
                  'line_height'   => false,
                  'text_align'   => false,
                  'preview'   => false,
                  'default' => array(
                    'color'       => '#ffffff',
                    'font-size'   => '14',
                    'unit'        => 'px',
                  ),
                  'output'  => '.hb-btn.hb-btna a',
                ),

                array(
                  'id'          => 'hover_text',
                  'type'        => 'color',
                  'title'       => '文本悬浮',
                  'output'      => '.hb-btn.hb-btna a:hover',
                  'default'     => '#ffffff', 
                  'output_mode' => 'color' 
                ),

                array(
                  'id'          => 'bg',
                  'type'        => 'color',
                  'title'       => '背景',
                  'default'     => '#092dff',
                  'output'      => '.hb-btn.hb-btna a',
                  'output_mode' => 'background-color' 
                ),

                array(
                  'id'          => 'hover_bg',
                  'type'        => 'color',
                  'title'       => '背景悬浮',
                  'default'     => '#000cff',
                  'output'      => '.hb-btn.hb-btna a:hover',
                  'output_mode' => 'background-color' 
                ),

                array(
                  'id'          => 'hover_border',
                  'type'        => 'color',
                  'title'       => '描边悬浮',
                  'default'     => '#000cff',
                  'output'      => '.hb-btn.hb-btna a:hover',
                  'output_mode' => 'border-color' 
                ),

                array(
                  'id'    => 'border',
                  'type'  => 'border',
                  'title' => '描边',
                  'all'   => true,
                  'default' => array(
                    'style'  => 'solid',
                    'color'  => 'transparent',
                    'unit'          => 'px',
                  ),
                  'output'  => '.hb-btn.hb-btna a',
                ),

                array(
                  'id'          => 'round',
                  'type'        => 'spacing',
                  'title'       => '圆角',
                  'output'      => '.hb-btn.hb-btna a',
                  'output_mode' => 'border-radius', // or margin, relative
                  'default'     => array(
                    'top'       => '5',
                    'right'     => '5',
                    'bottom'    => '5',
                    'left'      => '5',
                    'unit'      => 'px',
                  ),
                ),

                array(
                  'id'          => 'padding',
                  'type'        => 'spacing',
                  'title'       => '内边距',
                  'output'      => '.hb-btn.hb-btna a',
                  'output_mode' => 'padding', // or margin, relative
                  'default'     => array(
                    'top'       => '7',
                    'right'     => '20',
                    'bottom'    => '7',
                    'left'      => '20',
                    'unit'      => 'px',
                  ),
                ),

                array(
                  'id'          => 'margin',
                  'type'        => 'spacing',
                  'title'       => '外边距',
                  'output'      => '.hb-btn.hb-btna a',
                  'output_mode' => 'margin', // or margin, relative
                  'default'     => array(
                    'top'       => '0',
                    'right'     => '0',
                    'bottom'    => '0',
                    'left'      => '0',
                    'unit'      => 'px',
                  ),
                ),

              )
            ),
          )
        ),

      )
      ) );  

      //btna
    CSF::createSection( $prefix, array(
      'id' => 'hb-btnb',
      'title'  => '按钮2',
      'parent'  => 'nav-builder',
      'fields' => array(  

        array(
          'id'            => 'btnb_tab',
          'type'          => 'tabbed',
          'title'         => '',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(

                array(
                  'id'         => 'login',
                  'type'       => 'button_set',
                  'title'      => '可见性',
                  'multiple' => true,
                  'options'    => array(
                    'login'  => '登录',
                    'logout' => '未登录',
                  ),
                  'default'    => array( 'login', 'logout')
                ),

                array(
                  'id'    => 'icon',
                  'type'  => 'icon',
                  'title' => '图标',
                ),

                array(
                  'id'      => 'title',
                  'type'    => 'text',
                  'title'   => '文本',
                  'default' => '按钮'
                ),

                array(
                  'id'      => 'url',
                  'type'    => 'text',
                  'title'   => '链接',
                  'default' => '#'
                ),

                array(
                  'id'      => 'new',
                  'type'    => 'switcher',
                  'title'   => '新标签页打开',
                  'default' => true
                ),

                array(
                  'id'      => 'nofollow',
                  'type'    => 'switcher',
                  'title'   => '链接nofollow',
                  'default' => false
                ),

                array(
                  'id'      => 'css',
                  'type'    => 'text',
                  'title'   => 'css类',
                  'subtitle' => '多个类请用空格分开'
                ),

              )
            ),
            array(
              'title'     => '设计',
              'fields'    => array(
                
                array(
                  'id'      => 'title_c',
                  'type'    => 'typography',
                  'title'   => '文本样式',
                  'font_family'   => false,
                  'line_height'   => false,
                  'text_align'   => false,
                  'preview'   => false,
                  'default' => array(
                    'color'       => '#ffffff',
                    'font-size'   => '14',
                    'unit'        => 'px',
                  ),
                  'output'  => '.hb-btn.hb-btnb a',
                ),

                array(
                  'id'          => 'hover_text',
                  'type'        => 'color',
                  'title'       => '文本悬浮',
                  'output'      => '.hb-btn.hb-btnb a:hover',
                  'default'     => '#ffffff', 
                  'output_mode' => 'color' 
                ),

                array(
                  'id'          => 'bg',
                  'type'        => 'color',
                  'title'       => '背景',
                  'default'     => '#092dff',
                  'output'      => '.hb-btn.hb-btnb a',
                  'output_mode' => 'background-color' 
                ),

                array(
                  'id'          => 'hover_bg',
                  'type'        => 'color',
                  'title'       => '背景悬浮',
                  'default'     => '#000cff',
                  'output'      => '.hb-btn.hb-btnb a:hover',
                  'output_mode' => 'background-color' 
                ),

                array(
                  'id'          => 'hover_border',
                  'type'        => 'color',
                  'title'       => '描边悬浮',
                  'default'     => '#000cff',
                  'output'      => '.hb-btn.hb-btnb a:hover',
                  'output_mode' => 'border-color' 
                ),

                array(
                  'id'    => 'border',
                  'type'  => 'border',
                  'title' => '描边',
                  'all'   => true,
                  'default' => array(
                    'style'  => 'solid',
                    'color'  => 'transparent',
                    'unit'          => 'px',
                  ),
                  'output'  => '.hb-btn.hb-btnb a',
                ),

                array(
                  'id'          => 'round',
                  'type'        => 'spacing',
                  'title'       => '圆角',
                  'output'      => '.hb-btn.hb-btnb a',
                  'output_mode' => 'border-radius', // or margin, relative
                  'default'     => array(
                    'top'       => '5',
                    'right'     => '5',
                    'bottom'    => '5',
                    'left'      => '5',
                    'unit'      => 'px',
                  ),
                ),

                array(
                  'id'          => 'padding',
                  'type'        => 'spacing',
                  'title'       => '内边距',
                  'output'      => '.hb-btn.hb-btnb a',
                  'output_mode' => 'padding', // or margin, relative
                  'default'     => array(
                    'top'       => '7',
                    'right'     => '20',
                    'bottom'    => '7',
                    'left'      => '20',
                    'unit'      => 'px',
                  ),
                ),

                array(
                  'id'          => 'margin',
                  'type'        => 'spacing',
                  'title'       => '外边距',
                  'output'      => '.hb-btn.hb-btnb a',
                  'output_mode' => 'margin', // or margin, relative
                  'default'     => array(
                    'top'       => '0',
                    'right'     => '0',
                    'bottom'    => '0',
                    'left'      => '0',
                    'unit'      => 'px',
                  ),
                ),

              )
            ),
          )
        ),

      )
      ) ); 
        
    //logo
    CSF::createSection( $prefix, array(
      'id' => 'hb-logo',
      'title'  => '站点LOGO',
      'parent'  => 'nav-builder',
      'fields' => array(

        array(
          'id'            => 'logo_tab',
          'type'          => 'tabbed',
          'title'         => '',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(
                array(
                  'id'           => 'hb-site-logo',
                  'type'         => 'upload',
                  'title'        => 'LOGO图像',
                  'library'      => 'image',
                  'placeholder'  => 'http://',
                  'button_title' => '上传logo',
                  'remove_title' => '移除logo',
                  'preview'      => true, 
                ),

                array(
                  'id'      => 'logo-h',
                  'type'    => 'slider',
                  'title'   => 'LOGO高度',
                  'min'     => 0,
                  'max'     => 200,
                  'step'    => 1,
                  'unit'    => 'px',
                  'default' => 32,
                  'output_mode' => 'height',
                  'output'      => '.hb-logo-box a',
                ),

                array(
                  'id'         => 'title_on',
                  'type'       => 'switcher',
                  'title'      => '显示站点标题',
                  'default'    => true
                ),

                array(
                  'id'      => 'title',
                  'type'    => 'text',
                  'title'   => '站点标题',
                  'default' => 'PIXIT',
                  'dependency' => array( 'title_on', '==', 'true' )
                ),

                array(
                  'id'         => 'des_on', 
                  'type'       => 'switcher',
                  'title'      => '显示站点描述',
                  'default'    => false
                ),

                array(
                  'id'      => 'des',
                  'type'    => 'text',
                  'title'   => '站点描述',
                  'default' => '又一个PIXIT站点',
                  'dependency' => array( 'des_on', '==', 'true' )
                ),

                array(
                  'id'         => 'logo_pos',
                  'type'       => 'button_set',
                  'title'      => 'LOGO位置',
                  'options'    => array(
                    'left'  => '左',
                    'right' => '右',
                    'top' => '顶部',
                  ),
                  'default'    => 'left'
                ),

                array(
                  'id'         => 'logo_algin',
                  'type'       => 'button_set',
                  'title'      => '内容对齐',
                  'options'    => array(
                    'left-aligin'  => '居左',
                    'center-aligin' => '居中',
                    'right-aligin' => '居右',
                  ),
                  'default'    => 'left-aligin'
                ),

              )
            ),
            array(
              'title'     => '设计',
              'fields'    => array(
                
                array(
                  'id'      => 'title_c',
                  'type'    => 'typography',
                  'title'   => '标题样式',
                  'font_family'   => false,
                  'line_height'   => false,
                  'text_align'   => false,
                  'preview'   => false,
                  'default' => array(
                    'color'       => '#0a0000',
                    'font-size'   => '16',
                    'unit'        => 'px',
                  ),
                  'output'  => '.hb-logo-title .title',
                ),

                array(
                  'id'      => 'des_c',
                  'type'    => 'typography',
                  'title'   => '描述样式',
                  'font_family'   => false,
                  'line_height'   => false,
                  'text_align'   => false,
                  'preview'   => false,
                  'default' => array(
                    'color'       => '#0a0000',
                    'font-size'   => '12',
                    'unit'        => 'px',
                  ),
                  'output'  => '.hb-logo-title .des',
                ),

                array(
                  'id'       => 'hb_logo_margin',
                  'type'     => 'spacing',
                  'title'    => '边距',
                  'output'      => '.hb-logo',
                  'output_mode' => 'margin',
                  'default'  => array(
                    'top'    => '0',
                    'right'  => '0',
                    'bottom' => '0',
                    'left'   => '0',
                    'unit'   => 'px',
                  ),
                ),


              )
            ),
          )
        ),
  
      
  
      )
    ) );

    //主菜单 
    CSF::createSection( $prefix, array(
      'id' => 'hb-main-nav',
      'title'  => '主菜单',
      'parent'  => 'nav-builder',
      'fields' => array(

        array(
          'id'          => 'main_nav_id',
          'type'        => 'select',
          'title'       => '选择菜单',
          'placeholder' => '选择菜单',
          'options'     => 'menus', 
        ),

        array(
          'id'            => 'main_nav_tab',
          'type'          => 'tabbed',
          'title'         => '一级菜单选项',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(

                array(
                  'id'        => 'effects_type',
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
                    'output'  => '#primary_menu > li > a'
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
                    'font-size'   => '16',
                    'unit'        => 'px',
                  ),
                  'output'  => '.primary-nav ul li a',
                ),

                array(
                  'id'      => 'hover_text',
                  'type'    => 'color',
                  'title'   => '悬停',
                  'default' => '#0400ff',
                  'output'      => '#primary_menu > li a:hover',
                  'output_mode' => 'color'
                ),

                array(
                  'id'       => 'margin',
                  'type'     => 'spacing',
                  'title'    => '导航边距',
                  'top'  => false,
                  'bottom' => false,
                  'default'  => array(
                    'right'  => '0',
                    'left'   => '0',
                    'unit'   => 'px',
                  ),
                  'output'  => '.primary-nav',
                  'output_mode' => 'margin'
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
                  'output'   => '.primary-nav.line ul li a .nav-link-item:before',
                  'dependency' => array( 'effects_type', '==', 'line', 'all' ),
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
                    'output' => '.primary-nav.line ul li a .nav-link-item:before',
                    'dependency' => array( 'effects_type', '==', 'line', 'all' ),
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
                    'output' => '.primary-nav.line ul li a .nav-link-item:before',
                    'dependency' => array( 'effects_type', '==', 'line', 'all' ),
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
                    'output' => '.primary-nav.line ul li a .nav-link-item:before',
                    'dependency' => array( 'effects_type', '==', 'line', 'all' ),
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
                      'output'                          => '.primary-nav.boxt ul li a:after',
                      'dependency' => array( 'effects_type', '==', 'boxt', 'all' ),
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
                      'output' => '.primary-nav.boxt ul li a:after',
                      'desc' => '建议数值：50%-80%',
                      'dependency' => array( 'effects_type', '==', 'boxt', 'all' ),
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
                      'output' => '.primary-nav.boxt ul li a:after',
                      'dependency' => array( 'effects_type', '==', 'boxt', 'all' ),
                      ),
          
                      //悬浮块描边
                      array(
                      'id'     => 'box_border',
                      'type'   => 'border',
                      'title'  => '悬浮块描边',
                      'all'   => true,
                      'output' => '.primary-nav.boxt ul li a:after',
                      'default' => array(
                          'color'  => 'rgba(255,255,255,0)',
                          'all'    => '0',
                      ),
                      'dependency' => array( 'effects_type', '==', 'boxt', 'all' ),
                      ),

              )
            ),
          )
        ),

        //二级菜单------------------------------------------------------------------------------------
        array(
          'id'            => 'sub_nav_tab',
          'type'          => 'tabbed',
          'title'         => '下拉菜单选项',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(
                
                array(
                  'id'         => 'effects',
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

                  array(
                    'id'          => 'round',
                    'type'        => 'spacing',
                    'title'       => '下拉菜单背景圆角',
                    'output'      => '.primary-nav li ul',
                    'output_mode' => 'border-radius', // or margin, relative
                    'default'     => array(
                        'top'       => '10',
                        'right'     => '10',
                        'bottom'    => '10',
                        'left'      => '10',
                        'unit'      => 'px',
                    ),
                    ),

                  array(
                    'id'      => 'top',
                    'type'    => 'slider',
                    'title'   => '下拉面板位置',
                    'min'     => 70,
                    'max'     => 100,
                    'step'    => 1,
                    'unit'    => '%',
                    'default' => 100,
                    'output_mode' => 'top',
                    'output'    => '.primary-nav li:hover .sub-menu'
                  ),  

                   array(
                    'id'     => 'border',
                    'type'   => 'border',
                    'title'  => '下拉菜单面板描边',
                    'all'   => true,
                    'output' => '.primary-nav li ul',
                    'default' => array(
                        'color'  => 'rgba(255,255,255,0)',
                        'all'    => '0',
                    ),
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
                    'color'       => '#474747',
                    'font-size'   => '14',
                    'unit'        => 'px',
                  ),
                  'output'  => '.primary-nav li ul li a',
                ),

                array(
                  'id'      => 'hover_drop',
                  'type'    => 'color',
                  'title'   => '下拉背景',
                  'default' => '#ffffff',
                  'output'      => '#primary_menu li ul',
                  'output_mode' => 'background-color'
                ),

                array(
                  'id'      => 'hover_text',
                  'type'    => 'color',
                  'title'   => '链接悬停',
                  'default' => '#0400ff',
                  'output'      => '#primary_menu li ul li a:hover',
                  'output_mode' => 'color'
                ),

                array(
                  'id'      => 'hover_bg',
                  'type'    => 'color',
                  'title'   => '悬浮链接背景',
                  'default' => '#e0e0ff',
                  'output'      => '#primary_menu li ul li a:hover',
                  'output_mode' => 'background-color'
                ),

              )
            ),
          )
        ),
  
      
  
      )
    ) );

    //副菜单
    CSF::createSection( $prefix, array(
      'id' => 'hb-sub-nav',
      'title'  => '副菜单',
      'parent'  => 'nav-builder',
      'fields' => array(

        array(
          'id'          => 'sec_nav_id',
          'type'        => 'select',
          'title'       => '选择菜单',
          'placeholder' => '选择菜单',
          'options'     => 'menus', 
        ),

        array(
          'id'            => 'sec_nav_tab',
          'type'          => 'tabbed',
          'title'         => '一级菜单选项',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(

                array(
                  'id'        => 'sub_effects_type',
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
                    'output'  => '#sec_menu > li > a'
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
                    'font-size'   => '14',
                    'unit'        => 'px',
                  ),
                  'output'  => '.sec-nav ul li a',
                ),

                array(
                  'id'      => 'hover_text',
                  'type'    => 'color',
                  'title'   => '悬停',
                  'default' => '#0400ff',
                  'output'      => '#sec_menu > li a:hover',
                  'output_mode' => 'color'
                ),

                array(
                  'id'       => 'margin',
                  'type'     => 'spacing',
                  'title'    => '导航边距',
                  'top'  => false,
                  'bottom' => false,
                  'default'  => array(
                    'right'  => '0',
                    'left'   => '0',
                    'unit'   => 'px',
                  ),
                  'output'  => '.sec-nav',
                  'output_mode' => 'margin'
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
                  'output'   => '.sec-nav.line ul li a .nav-link-item:before',
                  'dependency' => array( 'sub_effects_type', '==', 'line', 'all' ),
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
                    'output' => '.sec-nav.line ul li a .nav-link-item:before',
                    'dependency' => array( 'sub_effects_type', '==', 'line', 'all' ),
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
                    'output' => '.sec-nav.line ul li a .nav-link-item:before',
                    'dependency' => array( 'sub_effects_type', '==', 'line', 'all' ),
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
                    'output' => '.sec-nav.line ul li a .nav-link-item:before',
                    'dependency' => array( 'sub_effects_type', '==', 'line', 'all' ),
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
                      'output'                          => '.sec-nav.boxt ul li a:after',
                      'dependency' => array( 'sub_effects_type', '==', 'boxt', 'all' ),
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
                      'output' => '.sec-nav.boxt ul li a:after',
                      'desc' => '建议数值：50%-80%',
                      'dependency' => array( 'sub_effects_type', '==', 'boxt', 'all' ),
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
                      'output' => '.sec-nav.boxt ul li a:after',
                      'dependency' => array( 'sub_effects_type', '==', 'boxt', 'all' ),
                      ),
          
                      //悬浮块描边
                      array(
                      'id'     => 'box_border',
                      'type'   => 'border',
                      'title'  => '悬浮块描边',
                      'all'   => true,
                      'output' => '.sec-nav.boxt ul li a:after',
                      'default' => array(
                          'color'  => 'rgba(255,255,255,0)',
                          'all'    => '0',
                      ),
                      'dependency' => array( 'sub_effects_type', '==', 'boxt', 'all' ),
                      ),

              )
            ),
          )
        ),

        //二级菜单------------------------------------------------------------------------------------
        array(
          'id'            => 'secsub_nav_tab',
          'type'          => 'tabbed',
          'title'         => '下拉菜单选项',
          'class'         => 'hb-tab_warp',
          'tabs'          => array(
            array(
              'title'     => '常规',
              'fields'    => array(
                
                array(
                  'id'         => 'effects',
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

                  array(
                    'id'          => 'round',
                    'type'        => 'spacing',
                    'title'       => '下拉菜单背景圆角',
                    'output'      => '.sec-nav li ul',
                    'output_mode' => 'border-radius', // or margin, relative
                    'default'     => array(
                        'top'       => '10',
                        'right'     => '10',
                        'bottom'    => '10',
                        'left'      => '10',
                        'unit'      => 'px',
                    ),
                    ),

                  array(
                    'id'      => 'top',
                    'type'    => 'slider',
                    'title'   => '下拉面板位置',
                    'min'     => 70,
                    'max'     => 100,
                    'step'    => 1,
                    'unit'    => '%',
                    'default' => 100,
                    'output_mode' => 'top',
                    'output'    => '.sec-nav li:hover .sub-menu'
                  ),  

                   array(
                    'id'     => 'border',
                    'type'   => 'border',
                    'title'  => '下拉菜单面板描边',
                    'all'   => true,
                    'output' => '.sec-nav li ul',
                    'default' => array(
                        'color'  => 'rgba(255,255,255,0)',
                        'all'    => '0',
                    ),
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
                    'color'       => '#474747',
                    'font-size'   => '13',
                    'unit'        => 'px',
                  ),
                  'output'  => '.sec-nav li ul li a',
                ),

                array(
                  'id'      => 'hover_drop',
                  'type'    => 'color',
                  'title'   => '下拉背景',
                  'default' => '#ffffff',
                  'output'      => '#sec_menu li ul',
                  'output_mode' => 'background-color'
                ),

                array(
                  'id'      => 'hover_text',
                  'type'    => 'color',
                  'title'   => '链接悬停',
                  'default' => '#0400ff',
                  'output'      => '#sec_menu li ul li a:hover',
                  'output_mode' => 'color'
                ),

                array(
                  'id'      => 'hover_bg',
                  'type'    => 'color',
                  'title'   => '悬浮链接背景',
                  'default' => '#e0e0ff',
                  'output'      => '#sec_menu li ul li a:hover',
                  'output_mode' => 'background-color'
                ),

              )
            ),
          )
        ),     
  
      )
    ) );

    CSF::createSection( $prefix, array(
      'id' => 'hb-draglist',
      'title'  => 'hb-draglist',
      'parent'  => 'nav-builder',
      'fields' => array(
  
        array(
          'type'    => 'heading',
          'content' => '这里什么也没有',
        ),
  
      )
    ) );
    

   
        
  }
  
