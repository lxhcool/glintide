<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

//
// Metabox of the PAGE
// Set a unique slug-like ID
//
$prefix_page_opts = '_pix_page_options';

//
// Create a metabox
//
CSF::createMetabox( $prefix_page_opts, array(
  'title'        => '落地页设置',
  'post_type'    => 'page',
  'page_templates' => 'page/landpage.php',
  'show_restore' => true,
  'data_type' => 'unserialize'
) );

//
// Create a section
//
CSF::createSection( $prefix_page_opts, array(
  'title'  => '落地页内容',
  'icon'   => 'fas fa-rocket',
  'fields' => array(

    array(
      'id'        => 'land_type',
      'type'      => 'image_select',
      'title'     => '落地页风格',
      'options'   => array(
        'card' => THEME_URL .'/img/theme/landcard.png',
        'simple' => THEME_URL .'/img/theme/landsimple.png',
      ),
      'default'   => 'card'
    ),

    array(
      'id'      => 'land_dark',
      'type'    => 'switcher',
      'title'   => '暗黑模式',
      'default' => false
    ),

    array(
      'id'           => 'land_logo',
      'type'         => 'upload',
      'title'        => 'LOGO或头像',
      'library'      => 'image',
      'button_title' => '上传图像',
      'remove_title' => '移除图像',
      'default'		 => THEME_URL .'/img/avatar.png',
    ),

    array(
      'id'           => 'land_feature',
      'type'         => 'upload',
      'title'        => '封面头图',
      'library'      => 'image',
      'button_title' => '上传封面',
      'remove_title' => '移除封面',
      'default'		 => THEME_URL .'/img/landimg.jpg',
    ),

    array(
      'id'    => 'land_title',
      'type'  => 'text',
      'title' => '标题或名称',
      'default'		 => '我的博客',
    ),

    array(
      'id'            => 'land_des',
      'type'          => 'wp_editor',
      'title'         => '描述',
      'tinymce'       => true,
      'media_buttons' => true,
      'height'        => '100px',
      'default' => 'WELCOME TO MY BLOG 欢迎访问PIXIT , 接下来请享受丝滑的体验.',
    ),

    array(
      'id'    => 'land_diy',
      'type'  => 'text',
      'title' => '底部自定义文案',
      'default'		 => '- Born for design',
    ),

    array(
      'id'       => 'land_html',
      'type'     => 'code_editor',
      'title'    => '自定义HTML',
      'settings' => array(
        'theme'  => 'monokai',
        'mode'   => 'htmlmixed',
      ),
      'desc' => 'css javascript php',
    ),

    array(
      'id'        => 'land_btn',
      'type'      => 'group',
      'title'     => '按钮组',
      'fields'    => array(

        array(
          'id'      => 'icon',
          'type'    => 'icon',
          'title'   => '菜单字体图标',
          'default' => 'ri-home-line'
        ),
        
        array(
          'id'           => 'link',
          'type'         => 'link',
          'title'        => '按钮链接',
          'add_title'    => '添加链接',
          'edit_title'   => '编辑链接',
          'remove_title' => '移除链接',
        ),
      ),
    ),

  )
) );



//
// Metabox of the PAGE and POST both.
// Set a unique slug-like ID
//
$prefix_moment_opts = '_pix_moment_options';

//
// Create a metabox
//
CSF::createMetabox( $prefix_moment_opts, array(
  'title'     => '片刻文章设置',
  'post_type' => array( 'moment'),
) );

//
// Create a section
//
CSF::createSection( $prefix_moment_opts, array(
  'fields' => array(

 



  )
) );

//文章页设置
$prefix_posts_opts = '_pix_posts_options';

//
// Create a metabox
//
CSF::createMetabox( $prefix_posts_opts, array(
  'title'     => '文章设置',
  'post_type' => 'post',
) );

//
// Create a section
//
CSF::createSection( $prefix_posts_opts, array(
  'title'  => '音乐设置',
  'fields' => array(

    array(
      'id'    => 'mu_on',
      'type'  => 'switcher',
      'title' => '开启文章音乐',
      'default' => false
    ),

    array(
      'id'         => 'mus_type',
      'type'       => 'button_set',
      'title'      => '歌曲列表类型',
      'options'    => array(
        'list'  => '歌曲列表',
        'album' => '专辑列表',
      ),
      'default'    => 'list'
    ),

    array(
      'id'         => 'mus_source',
      'type'       => 'button_set',
      'title'      => '歌曲源',
      'options'    => array(
        'netease'  => '网易云',
        'tencent' => 'QQ音乐',
        'kugou' => '酷狗音乐',
        'kuwo' => '酷我音乐',
      ),
      'default'    => 'netease'
    ),

    array(
      'id'    => 'plays_id',
      'type'  => 'text',
      'title' => '列表ID',
    ),

    array(
      'id'    => 'mu_des',
      'type'  => 'textarea',
      'title' => '专辑描述',
    ),

  )
) );
