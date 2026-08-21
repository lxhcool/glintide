<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

//
// 推荐圈子
//
CSF::createWidget( 'moment_sug_cat', array(
  'title'       => 'PPO · 圈子推荐',
  'classname'   => 'ppo-widget moment_sug_cat',
  'description' => '自定义推荐圈子',
  'fields'      => array(

    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
    ),

    array(
      'id'          => 'mo_sug_list',
      'type'        => 'select',
      'title'       => '选择圈子',
      'chosen'      => true,
      'multiple'    => true,
      'sortable'    => true,
      'placeholder' => '选择推荐的圈子',
      'options'     => 'categories',
      'query_args'  => array(
        'taxonomy'  => 'moments',
      ),
      'desc'        => '可选择多个，可排序'
    ),

  )
) );

//
// Front-end display of widget example 1
// Attention: This function named considering above widget base id.
//
//
// 广告位
//
CSF::createWidget( 'pix_gg_banner', array(
  'title'       => 'PPO · 广告位',
  'classname'   => 'ppo-widget pix_gg_banner',
  'description' => '图片广告位，支持上传图片和添加外链',
  'icon'        => 'fas fa-image',
  'class'   => 'pix-options',
  'fields'      => array(

    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
    ),

     array(
      'id'           => 'gg_image',
      'type'         => 'upload',
      'title'        => '广告图片',
      'library'      => 'image',
      'placeholder'  => 'http://',
      'button_title' => '上传图片',
      'remove_title' => '删除图片',
      'preview'      => true,
      'desc'         => '可以上传图片或直接输入图片链接，外链时请添加http://或https://',
    ),

     array(
      'id'           => 'gg_link',
      'type'         => 'text',
      'title'        => '广告链接',
      'desc'         => '广告点击跳转链接',
    ),

     array(
      'id'           => 'gg_target',
      'type'         => 'switcher',
      'title'        => '是否在新窗口打开',
      'desc'         => '点击广告是否在新窗口打开',
      'default'      => true,
    ),

  )
) );

//
// Front-end display of widget
//
if ( ! function_exists( 'pix_gg_banner' ) ) {
  function pix_gg_banner( $args, $instance ) {  
    echo $args['before_widget'];

    echo pix_gg_banner_func( $instance );

    echo $args['after_widget'];
  }
}

//
// 图标网格
//
CSF::createWidget( 'pix_icon_grid', array(
  'title'       => 'PPO · 图标网格',
  'classname'   => 'ppo-widget pix_icon_grid',
  'description' => '以网格形式展示图标链接',
  'class'       => 'pix-options',
  'fields'      => array(

    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
    ),

    array(
      'id'      => 'per_row',
      'type'    => 'radio',
      'title'   => '每行显示',
      'options' => array(
        '3' => '3个',
        '4' => '4个',
      ),
      'inline' => true,
      'default' => '4',
    ),

    array(
      'id'     => 'icon_list',
      'type'   => 'group',
      'title'  => '图标列表',
      'fields' => array(

         array(
          'id'    => 'title',
          'type'  => 'text',
          'title' => '标题',
        ),

        array(
          'id'      => 'icon_type',
          'type'    => 'button_set',
          'title'   => '图标类型',
          'options' => array(
            'icon'  => '字体图标',
            'image' => '图片',
          ),
          'default' => 'icon',
        ),

        array(
          'id'        => 'icon',
          'type'      => 'icon',
          'title'     => '图标',
          'dependency' => array( 'icon_type', '==', 'icon' ),
        ),

        array(
          'id'         => 'icon_color',
          'type'       => 'color',
          'title'      => '图标颜色',
          'dependency' => array( 'icon_type', '==', 'icon' ),
        ),

        array(
          'id'                              => 'bg-color',
          'type'                            => 'background',
          'title'                           => '图标背景色',
          'background_image'                => false,
          'background_attachment'           => false,
          'background_size'                 => false,
          'background_position'             => false,
          'background_repeat'               => false,
          'background_gradient'             => true,
          'default'                         => array(
            'background-color'              => '#f0f0f0',
            'background-gradient-color'     => '#ddd',
            'background-gradient-direction' => 'to bottom',
          ),
          'dependency' => array( 'icon_type', '==', 'icon' ),
        ),

        array(
          'id'         => 'image',
          'type'       => 'upload',
          'title'      => '图片',
          'library'    => 'image',
          'dependency' => array( 'icon_type', '==', 'image' ),
          'preview'    => true,
          'desc'       => '图片需为正方形，支持外链图片',
        ),

        array(
          'id'    => 'link',
          'type'  => 'text',
          'title' => '链接',
        ),

        array(
          'id'      => 'target',
          'type'    => 'switcher',
          'title'   => '新窗口打开',
          'default' => true,
        ),

      ),
    ),

  )
) );

//
// 文章列表
//
CSF::createWidget( 'pix_post_list', array(
  'title'       => 'PPO · 文章列表',
  'classname'   => 'ppo-widget pix_post_list',
  'description' => '展示文章列表，支持多种排序和样式',
  'class'       => 'pix-options',
  'fields'      => array(

    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
    ),

    array(
      'id'      => 'list_style',
      'type'    => 'radio',
      'title'   => '列表样式',
      'options' => array(
        'normal'  => '普通列表',
        'featured' => '首个大图',
        'text'    => '纯文本',
      ),
      'inline' => true,
      'default' => 'normal',
    ),

    array(
      'id'      => 'order_by',
      'type'    => 'select',
      'title'   => '排序方式',
      'options' => array(
        'views'    => '浏览量',
        'comments' => '评论量',
        'likes'    => '点赞量',
        'favorites'=> '收藏量',
      ),
      'default' => 'views',
    ),

    array(
      'id'      => 'post_num',
      'type'    => 'number',
      'title'   => '显示数量',
      'default' => 5,
    ),

    array(
      'id'      => 'show_meta',
      'type'    => 'checkbox',
      'title'   => '显示元信息',
      'options' => array(
        'views'    => '浏览量',
        'comments' => '评论',
        'likes'    => '点赞',
        'date'     => '日期',
      ),
      'inline' => true,
      'default' => array('views', 'date'),
    ),

  )
) );

//
// 文章分类推荐
//
CSF::createWidget( 'pix_cat_recommend', array(
  'title'       => 'PPO · 分类推荐',
  'classname'   => 'ppo-widget pix_cat_recommend',
  'description' => '展示文章分类推荐',
  'class'       => 'pix-options',
  'fields'      => array(

    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
    ),

    array(
      'id'      => 'cat_style',
      'type'    => 'radio',
      'title'   => '显示样式',
      'options' => array(
        'banner' => '全宽图模式',
        'tag'    => '标签模式',
      ),
      'inline' => true,
      'default' => 'banner',
    ),

    array(
      'id'          => 'cat_list',
      'type'        => 'select',
      'title'       => '选择分类',
      'chosen'      => true,
      'multiple'    => true,
      'sortable'    => true,
      'placeholder' => '选择分类',
      'options'     => 'categories',
    ),

  )
) );

//
// Front-end display of category recommend
//
if ( ! function_exists( 'pix_cat_recommend' ) ) {
  function pix_cat_recommend( $args, $instance ) {
    echo $args['before_widget'];

    echo pix_cat_recommend_func( $instance );

    echo $args['after_widget'];
  }
}

//
// 用户信息
//
CSF::createWidget( 'pix_user_info', array(
  'title'       => 'PPO · 用户信息',
  'classname'   => 'ppo-widget pix_user_info',
  'description' => '展示用户登录信息',
  'class'       => 'pix-options',
  'fields'      => array(
    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
    ),
  )
) );

//
// Front-end display of user info
//
if ( ! function_exists( 'pix_user_info' ) ) {
  function pix_user_info( $args, $instance ) {
    echo $args['before_widget'];

    echo pix_user_info_func( $instance );

    echo $args['after_widget'];
  }
}

//
// Front-end display of post list
//
if ( ! function_exists( 'pix_post_list' ) ) {
  function pix_post_list( $args, $instance ) {
    echo $args['before_widget'];

    echo pix_post_list_func( $instance );

    echo $args['after_widget'];
  }
}

//
// Front-end display of icon grid
//
if ( ! function_exists( 'pix_icon_grid' ) ) {
  function pix_icon_grid( $args, $instance ) {
    echo $args['before_widget'];

    echo pix_icon_grid_func( $instance );

    echo $args['after_widget'];
  }
}

if ( ! function_exists( 'moment_sug_cat' ) ) {
  function moment_sug_cat( $args, $instance ) {

    echo $args['before_widget'];

    // if ( ! empty( $instance['title'] ) ) {
    //   echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
    // }
    echo mo_sug_list_func($instance);

    echo $args['after_widget'];

  }
}

//
// 菜单小工具
//
CSF::createWidget( 'pix_menu_widget', array(
  'title'       => 'PPO · 菜单组件',
  'classname'   => 'ppo-widget pix_menu_widget',
  'description' => '自定义菜单组件，支持多菜单和分组标题',
  'class'       => 'pix-options',
  'fields'      => array(
    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
    ),
    array(
      'id'        => 'menu_groups',
      'type'      => 'group',
      'title'     => '菜单分组',
      'fields'    => array(
        array(
          'id'    => 'group_title',
          'type'  => 'text',
          'title' => '分组标题',
        ),
        array(
          'id'          => 'menu_id',
          'type'        => 'select',
          'title'       => '选择菜单',
          'placeholder' => '选择菜单',
          'options'     => 'menus',
        ),
      ),
    ),
  )
) );

//
// Front-end display of menu widget
//
if ( ! function_exists( 'pix_menu_widget' ) ) {
  function pix_menu_widget( $args, $instance ) {
    echo $args['before_widget'];

    if ( ! empty( $instance['title'] ) ) {
      echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
    }
    echo pix_menu_widget_func( $instance );

    echo $args['after_widget'];
  }
}

//
// 评论小工具
//
CSF::createWidget( 'pix_comment_widget', array(
  'title'       => 'PPO · 评论列表',
  'classname'   => 'ppo-widget pix_comment_widget',
  'description' => '展示最新评论列表',
  'class'       => 'pix-options',
  'fields'      => array(
    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
    ),
    array(
      'id'      => 'sort_by',
      'type'    => 'radio',
      'title'   => '排序方式',
      'options' => array(
        'newest' => '最新评论',
        'popular' => '按文章点赞量',
      ),
      'default' => 'newest',
    ),
    array(
      'id'         => 'comment_count',
      'type'       => 'number',
      'title'      => '显示数量',
      'default'    => 5,
    ),
  )
) );

//
// Front-end display of comment widget
//
if ( ! function_exists( 'pix_comment_widget' ) ) {
  function pix_comment_widget( $args, $instance ) {
    echo $args['before_widget'];
    echo pix_comment_widget_func( $instance );
    echo $args['after_widget'];
  }
}

//
// 图片画廊小工具
//
CSF::createWidget( 'pix_gallery_widget', array(
  'title'       => 'PPO · 图片画廊',
  'classname'   => 'ppo-widget pix_gallery_widget',
  'description' => '展示图片画廊，支持九宫格布局',
  'class'       => 'pix-options',
  'fields'      => array(
    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
    ),
    array(
      'id'      => 'images',
      'type'    => 'gallery',
      'title'   => '图片集',
    ),
  )
) );

//
// Front-end display of gallery widget
//
if ( ! function_exists( 'pix_gallery_widget' ) ) {
  function pix_gallery_widget( $args, $instance ) {
    echo $args['before_widget'];
    echo pix_gallery_widget_func( $instance );
    echo $args['after_widget'];
  }
}

//
// Logo 小工具
//
CSF::createWidget( 'pix_logo_widget', array(
  'title'       => 'PPO · Logo',
  'classname'   => 'ppo-widget pix_logo_widget',
  'description' => '展示站点 Logo',
  'class'       => 'pix-options',
  'fields'      => array(
    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
    ),
    array(
      'id'      => 'logo_mode',
      'type'    => 'select',
      'title'   => '显示方式',
      'options' => array(
        'auto'  => '站点Logo设置',
        'image' => '自定义图片',
        'text'  => '站点标题文字',
      ),
      'default' => 'auto',
    ),
    array(
      'id'         => 'logo_image',
      'type'       => 'upload',
      'title'      => '自定义Logo图片',
      'dependency' => array( 'logo_mode', '==', 'image' ),
    ),
    array(
      'id'      => 'logo_link',
      'type'    => 'text',
      'title'   => '链接地址',
      'desc'    => '留空默认链接到首页',
    ),
    array(
      'id'      => 'logo_align',
      'type'    => 'select',
      'title'   => '对齐方式',
      'options' => array(
        'left'   => '左对齐',
        'center' => '居中',
        'right'  => '右对齐',
      ),
      'default' => 'left',
    ),
    array(
      'id'      => 'logo_width',
      'type'    => 'number',
      'title'   => '宽度',
      'desc'    => '单位 px，留空或 0 为自适应',
      'default' => 0,
    ),
    array(
      'id'      => 'logo_height',
      'type'    => 'number',
      'title'   => '高度',
      'desc'    => '单位 px，留空或 0 为自适应',
      'default' => 0,
    ),
  )
) );

//
// Front-end display of logo widget
//
if ( ! function_exists( 'pix_logo_widget' ) ) {
  function pix_logo_widget( $args, $instance ) {
    echo $args['before_widget'];
    echo pix_logo_widget_func( $instance );
    echo $args['after_widget'];
  }
}

//
// 一言小工具
//
CSF::createWidget( 'pix_hitokoto_widget', array(
  'title'       => 'PPO · 一言',
  'classname'   => 'ppo-widget pix_hitokoto_widget',
  'description' => '调用一言 API 展示随机句子，卡片式带背景图',
  'class'       => 'pix-options',
  'fields'      => array(
    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
    ),
    array(
      'id'      => 'show_from',
      'type'    => 'switcher',
      'title'   => '显示来源',
      'default' => true,
    ),
    array(
      'id'      => 'show_refresh',
      'type'    => 'switcher',
      'title'   => '显示刷新按钮',
      'default' => true,
    ),
    array(
      'id'      => 'bg_image',
      'type'    => 'upload',
      'title'   => '背景图',
      'desc'    => '留空则使用纯色背景',
    ),
    array(
      'id'      => 'overlay_color',
      'type'    => 'color',
      'title'   => '蒙层颜色',
      'default' => '#000000',
    ),
    array(
      'id'      => 'overlay_opacity',
      'type'    => 'slider',
      'title'   => '蒙层透明度',
      'desc'    => '数值越大蒙层越深，文字越清晰',
      'min'     => 0,
      'max'     => 100,
      'step'    => 1,
      'unit'    => '%',
      'default' => 55,
    ),
    array(
      'id'      => 'card_height',
      'type'    => 'number',
      'title'   => '卡片高度',
      'desc'    => '单位 px，留空或 0 为自适应',
      'default' => 0,
    ),
  )
) );

//
// Front-end display of hitokoto widget
//
if ( ! function_exists( 'pix_hitokoto_widget' ) ) {
  function pix_hitokoto_widget( $args, $instance ) {
    echo $args['before_widget'];
    echo pix_hitokoto_widget_func( $instance );
    echo $args['after_widget'];
  }
}

//
// 音乐播放器小工具
//
CSF::createWidget( 'pix_music_widget', array(
  'title'       => 'PPO · 音乐播放器',
  'classname'   => 'ppo-widget pix_music_widget',
  'description' => '网易云歌单播放器，支持播放/进度/音量/播放模式',
  'class'       => 'pix-options',
  'fields'      => array(
    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
    ),
    array(
      'id'      => 'playlist_url',
      'type'    => 'text',
      'title'   => '网易云歌单链接',
      'desc'    => '粘贴网易云歌单地址，如 https://music.163.com/#/playlist?id=3778678',
    ),
    array(
      'id'      => 'show_playlist',
      'type'    => 'switcher',
      'title'   => '显示播放列表',
      'default' => true,
    ),
    array(
      'id'      => 'default_volume',
      'type'    => 'slider',
      'title'   => '默认音量',
      'min'     => 0,
      'max'     => 100,
      'step'    => 1,
      'unit'    => '%',
      'default' => 65,
    ),
  )
) );

//
// Front-end display of music widget
//
if ( ! function_exists( 'pix_music_widget' ) ) {
  function pix_music_widget( $args, $instance ) {
    echo $args['before_widget'];
    echo pix_music_widget_func( $instance );
    echo $args['after_widget'];
  }
}

//
// 签到小工具
//
CSF::createWidget( 'pix_sign_widget', array(
  'title'       => 'PPO · 签到组件',
  'classname'   => 'ppo-widget pix_sign_widget',
  'description' => '展示签到信息和排行榜',
  'class'       => 'pix-options',
  'fields'      => array(
    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
    ),
    array(
      'id'      => 'rank_limit',
      'type'    => 'number',
      'title'   => '显示排行数量',
      'default' => 6,
    ),
  )
) );

//
// Front-end display of sign widget
//
if ( ! function_exists( 'pix_sign_widget' ) ) {
  function pix_sign_widget( $args, $instance ) {
    echo $args['before_widget'];
    echo pix_sign_widget_func( $instance );
    echo $args['after_widget'];
  }
}

add_action( 'admin_head-widgets.php', 'pix_widget_admin_badge_style' );
add_action( 'customize_controls_print_styles', 'pix_widget_admin_badge_style' );
function pix_widget_admin_badge_style() {
  ?>
  <style>
    .widgets-holder-wrap .widget[class*="ppo-widget"] .widget-title h3:before,
    .widgets-holder-wrap .widget[id*="pix_"] .widget-title h3:before,
    .widgets-holder-wrap .widget[id*="moment_sug_cat"] .widget-title h3:before,
    .customize-control-widget_form .widget[class*="ppo-widget"] .widget-title h3:before {
      content: "PPO";
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 28px;
      height: 18px;
      margin-right: 8px;
      border-radius: 5px;
      background: var(--color-pix-primary, #3157ff);
      color: #fff;
      font-size: 10px;
      font-weight: 700;
      line-height: 1;
      vertical-align: 1px;
    }
    .pix-admin-widget-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 22px;
      margin-right: 10px;
      border-radius: 6px;
      background: var(--color-pix-primary, #3157ff);
      color: #fff;
      font-size: 10px;
      font-weight: 700;
      line-height: 1;
      flex: 0 0 auto;
    }
    .block-editor-block-types-list__item-title .pix-admin-widget-badge {
      vertical-align: middle;
    }
  </style>
  <?php
}

add_action( 'admin_footer-widgets.php', 'pix_widget_admin_badge_script' );
function pix_widget_admin_badge_script() {
  ?>
  <script>
    (function() {
      function markPpoWidgets() {
        document.querySelectorAll('.block-editor-block-types-list__item-title').forEach(function(title) {
          var text = title.textContent.trim();
          if (text.indexOf('PPO ·') !== 0 || title.querySelector('.pix-admin-widget-badge')) {
            return;
          }
          var badge = document.createElement('span');
          badge.className = 'pix-admin-widget-badge';
          badge.textContent = 'PPO';
          title.insertBefore(badge, title.firstChild);
        });
      }

      markPpoWidgets();
      new MutationObserver(markPpoWidgets).observe(document.body, {
        childList: true,
        subtree: true
      });
    })();
  </script>
  <?php
}
