<?php
if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

//
// Set a unique slug-like ID
//
$prefix = 'ppo_options';

CSF::createSection( $prefix, array(
    'title'  => 'IP归属地',
    'parent' => 'extend_panel',
    'icon'   => 'ri-map-pin-2-line',
    'fields' => array(

        array(
            'id'         => 'ip_srvice',
            'type'       => 'radio',
            'title'      => 'IP服务商',
            'options'    => array(
              'tx' => '腾讯地图',
              'gd' => '高德地图',
              'tpy' => '太平洋',
            ),
            'default'    => 'tpy',
            'inline'     => true,
          ),

          array(
            'id'      => 'gdip_key',
            'type'    => 'text',
            'title'   => '高德地图key',
            'dependency' => array( 'ip_srvice', '==', 'gd' ),
          ),

          array(
            'id'      => 'txip_key',
            'type'    => 'text',
            'title'   => '腾讯地图key',
            'dependency' => array( 'ip_srvice', '==', 'tx' ),
          ),

          array(
            'id'         => 'ip_city',
            'type'       => 'radio',
            'title'      => '归属地精确值',
            'options'    => array(
              'province' => '省份',
              'city' => '城市',
            ),
            'default'    => 'province',
            'inline'     => true,
          ),
    ),
) ); 


CSF::createSection( $prefix, array(
'title'  => '自定义按钮',
'parent' => 'extend_panel',
'icon'   => 'ri-toggle-line',
'fields' => array(

    array(
        'type'    => 'submessage',
        'style'   => 'info',
        'content' => '<i class="ri-hashtag"></i>可添加多个不同样式的按钮，然后在轮播等其他地方调用，按钮唯一ID不可重复，不然会报错',
      ),

    array(
        'id'        => 'pixbtn',
        'type'      => 'group',
        'title'     => '自定义按钮组',
        'fields'    => array(
            
            array(
                'id'    => 'btn_id',
                'type'  => 'text',
                'title' => '按钮唯一ID',
                'subtitle' => '按钮唯一ID,英文或数组，例如btn1，必填！',
                'default' => 'btn1'
                ),

            array(
                'id'         => 'btn_animate',
                'type'       => 'radio',
                'title'      => '悬浮动画',
                'inline'     => true,
                'options'    => array(
                    'normal' => '默认',
                    'moveup' => '上移',
                    'slideh' => '背景上滑',
                    'slidew' => '背景横滑',
                    'slidet' => '文本上滑',
                    'spread' => '扩散',
                    'shadow' => '阴影',
                ),
                'default'    => 'normal'
                ),    

            array(
                'id'    => 'btn_title',
                'type'  => 'text',
                'title' => '按钮文本',
                'default' => 'BUY NOW'
                ),
            
            array(
            'id'    => 'btn_link',
            'type'  => 'text',
            'title' => '链接',
            'default' => '#'
            ),
        
            array(
            'id'        => 'btn_color',
            'type'      => 'color_group',
            'title'     => '颜色',
            'options'   => array(
                'bg' => '背景色',
                'text' => '文本色',
                'text_hover' => '文本悬浮色',
                'bg_hover' => '背景悬浮色',
            ),
            'default'   => array(
                'bg' => '#ffffff',
                'text' => '#0a0a0a',
                'text_hover' => '#ffffff',
                'bg_hover' => '#2d2d2d',
            )
            ),
        
            array(
            'id'      => 'btn_border',
            'type'    => 'border',
            'title'   => '描边',
            'all'   => true,
            'default' => array(
                'all'   => '0',
                'color'  => 'rgba(0,102,191,0)',
                'unit'    => 'px',
            ),
            ),
        
            array(
            'id'       => 'btn_round',
            'type'     => 'spacing',
            'title'    => '按钮圆角',
            'default'  => array(
                'top'    => '8',
                'right'  => '8',
                'bottom' => '8',
                'left'   => '8',
                'unit'   => 'px',
            ),
            ),

            array(
                'id'       => 'btn_padding',
                'type'     => 'spacing',
                'title'    => '按钮内边距',
                'subtitle' => '可用来调整按钮宽高',
                'bottom'  => false,
                'right' => false,
                'units' => array( 'px' ),
                'top_icon' => '<i class="fas fa-text-width"></i>',
                'left_icon' => '<i class="fas fa-text-height"></i>',
                'default'  => array(
                  'top'    => '16',
                  'left'   => '24',
                  'unit'   => 'px',
                ),
              ),

        ),
      ),

    


),
) ); 
