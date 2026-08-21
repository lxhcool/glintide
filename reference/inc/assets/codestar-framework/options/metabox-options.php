<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

$pix_payment_options = get_option('ppo_options');
$pix_cash_name = (is_array($pix_payment_options) && !empty($pix_payment_options['cash_name'])) ? $pix_payment_options['cash_name'] : '余额';
$pix_credit_name = (is_array($pix_payment_options) && !empty($pix_payment_options['credit_name'])) ? $pix_payment_options['credit_name'] : '积分';

//
// Metabox of the PAGE
// Set a unique slug-like ID
//
$prefix_page_opts = '_ppo_page_options';

//
// Create a metabox
//
CSF::createMetabox( $prefix_page_opts, array(
  'title'        => 'PPO页面设置',
  'post_type'    => 'page',
  'context'      => 'side',
  'show_restore' => true,
) );

//
// Create a section
//
CSF::createSection( $prefix_page_opts, array(
  'title'  => '页面布局',
  'icon'   => 'fas fa-rocket',
  'fields' => array(

    array(
      'id'         => 'page_layout',
      'type'       => 'radio',
      'title'      => '页面布局',
      'options'    => array(
        'global' => '跟随全局',
        'full' => '全宽布局',
      ),
      'inline'      => true,
      'desc'       => '跟随全局布局(单栏/双栏/三栏)，或自定义全宽布局',
      'default'    => 'global'
    ),

  )
) );


//
// Metabox of the POST
// Set a unique slug-like ID
//
$prefix_post_opts = '_ppo_post_options';

//
// Create a metabox
//
CSF::createMetabox( $prefix_post_opts, array(
  'title'        => '文章付费下载',
  'post_type'    => 'post',
  'show_restore' => true,
  'priority'     => 'high',
  'class'      => 'pix-options',
) );

//
// Create a section
//
CSF::createSection( $prefix_post_opts, array(
  'fields' => array(

    array(
      'id'         => 'dd_type',
      'type'       => 'radio',
      'title'      => '付费类型',
      'inline'      => true,
      'options'    => array(
        'close' => '关闭',
        'read' => '付费阅读',
        'down' => '付费下载',
        'video' => '付费视频',
        'gallary' => '付费图集',
      ),
      'default'    => 'close'
    ),

    array(
      'id'     => 'dd_box_data',
      'type'   => 'fieldset',
      'title'  => '付费资源设置',
      'dependency' => array( 'dd_type', '!=', 'close' ),
      'fields' => array(
        array(
          'id'         => 'pay_type',
          'type'       => 'radio',
          'title'      => '支付(查看)类型',
          'inline'      => true,
          'options'    => array(
            'cash' => $pix_cash_name.'支付',
            'credit' => $pix_credit_name.'支付',
            'login' => '登录可见',
            'limits' => '等级限制',
            'cmt' => '评论可见',
            'pwd' => '自定义密码',
            'open' => '无限制(免费)',
          ),
          'desc'       => '如选'.$pix_credit_name.'支付，请先配置'.$pix_credit_name.'设置',
          'default'    => 'cash'
        ),

        array(
          'id'         => 'lv_allow',
          'type'       => 'checkbox',
          'title'      => '允许购买的用户组',
          'options'    => 'all_lv_merge',
          'inline'     => true,
          'check_all' => true,
          'check_all_text' => '全选/取消全选',
          'dependency' => array( 'pay_type', 'any', 'cash,credit' ),
          'desc'       => '选中的用户组可以才有购买资源的资格'
        ),

        array(
          'id'         => 'limits_check',
          'type'       => 'checkbox',
          'title'      => '允许下载(查看)的用户组',
          'options'    => 'all_lv_merge',
          'inline'     => true,
          'check_all' => true,
          'check_all_text' => '全选/取消全选',
          'dependency' => array( 'pay_type', '==', 'limits' ),
          'desc'       => '选中的用户组才可以下载或查看资源'
        ),

        // 现金
        array(
          'id'    => 'cash_price',
          'type'  => 'number',
          'title' => '原价',
          'unit'        => '元',
          'default'     => 25,
          'dependency' => array( 'pay_type', '==', 'cash' ),
        ),
        array(
          'id'    => 'n_cash',
          'type'  => 'number',
          'title' => '执行价',
          'unit'        => '元',
          'class'   => 'mini',
          'desc'    => '如填，则以此价为准，可用于活动，不填则以原价为准; 若两者都不填则免费',
          'dependency' => array( 'pay_type', '==', 'cash' ),
        ),

        // 积分
        array(
          'id'    => 'credit_price',
          'type'  => 'number',
          'title' => $pix_credit_name.'售价',
          'unit'        => $pix_credit_name,
          'default'     => 25,
          'dependency' => array( 'pay_type', '==', 'credit' ),
        ),

        array(
          'type'     => 'vip_discount',
          'id'       => 'vip_discount',
          'title'    => 'vip折扣',
          'desc'     => '可为每个VIP等级设置单独的折扣,如:7折，则输入0.7',
          'dependency' => array( 'pay_type', 'any', 'cash,credit' ),
        ),

        /* array(
          'id'    => 'sell_num',
          'type'  => 'number',
          'title' => '自定义销量',
          'unit'        => '',
          'desc'    => '可以增加',
          'dependency' => array(
            array( 'pay_type', 'any', 'cash,credit' ),
            array( 'dd_type',   '==', 'down','all' ),
          ),
        ), */

        //密码输入
        array(
          'id'    => 'down_pwd',
          'type'  => 'text',
          'title' => '自定义密码',
          'desc'    => '设定密码，然后可以扫码获取密码，4位密码',
          'class' => 'mini-input',
          'dependency' => array( 'pay_type', '==', 'pwd' ),
        ),

        array(
          'id'    => 'down_pwd_html',
          'type'  => 'textarea',
          'title' => '密码提示内容',
          'desc'    => '（选填）输入一些提示之类的，支持html',
          'class' => 'mini',
          'dependency' => array( 'pay_type', '==', 'pwd' ),
        ),

        array(
          'id'           => 'pwd_qrcode',
          'type'         => 'upload',
          'title'        => '二维码',
          'library'      => 'image',
          'placeholder'  => 'http(s)://',
          'button_title' => '添加二维码',
          'remove_title' => '移除二维码',
          'desc'    => '（选填）上传公众号或其他平台二维码，支持外链',
          'class' => 'mini',
          'dependency' => array( 'pay_type', '==', 'pwd' ),
        ),

        //付费信息
        array(
          'id'    => 'title',
          'type'  => 'text',
          'title' => '资源标题',
          'desc'    => '不填则显示文章标题',
          'dependency' => array( 'dd_type', '==', 'down','all' ),
        ),

        array(
          'id'    => 'des',
          'type'  => 'text',
          'title' => '资源描述',
          'desc'    => '选填，对付费内容简单描述',
          'class'   => 'mini',
          'dependency' => array( 'dd_type', '==', 'down','all' ),
        ),

        array(
          'id'          => 'gallery',
          'type'        => 'gallery',
          'title'       => '资源预览图集',
          'add_title'   => '添加图集',
          'edit_title'  => '编辑图集',
          'clear_title' => '移除图集',
          'class'   => 'mini',
          'dependency' => array( 'dd_type', '==', 'down','all' ),
        ),

        array(
          'id'    => 'info',
          'type'  => 'textarea',
          'title' => '资源详情',
          'desc'    => '选填，一行一个，格式：标题|信息</br><code>文件大小|2MB</code>',
          'class'   => 'mini',
          'dependency' => array( 'dd_type', '==', 'down','all' ),
        ),

        array(
          'id'    => 'hide_html',
          'type'  => 'textarea',
          'title' => '隐藏信息',
          'desc'    => '选填，付费后显示的定制内容，支持HTML',
          'class'   => 'mini',
          'dependency' => array( 'dd_type', '==', 'down','all' ),
        ),

        array(
          'id'    => 'hide_tips',
          'type'  => 'textarea',
          'title' => '自定义提示',
          'desc'    => '选填，一些对隐藏内容的提示之类的,仅在未解锁时显示',
          'dependency' => array( 'dd_type', '==', 'read','all' ),
        ),



      ),
    ),

    // 下载
    array(
      'id'        => 'down_item',
      'type'      => 'group',
      'title'     => '下载资源设置',
      'dependency' => array( 'dd_type', '==', 'down' ),
      'fields'    => array(
        array(
          'id'    => 'title',
          'type'  => 'text',
          'title' => '资源标题',
        ),
        array(
          'id'           => 'link',
          'type'         => 'upload',
          'title'        => '资源地址',
          'placeholder'  => 'http(s)://',
          'button_title' => '添加资源',
          'remove_title' => '移除资源',
          'desc'         => '可以上传文件，或直接填写网盘地址，迅雷下载地址等'
        ),
        array(
          'id'    => 'tqm',
          'type'  => 'text',
          'title' => '提取码',
          'class' => 'mini-input',
          'desc'  => '（选填）如果是网盘链接，可填写提取码'
        ),
        array(
          'id'    => 'jym',
          'type'  => 'text',
          'title' => '解压码',
          'class' => 'mini-input',
          'desc'  => '（选填）可填写压缩包密码'
        ),
      ),
    ),

    array(
      'id'            => 'down_item_html',
      'type'          => 'wp_editor',
      'title'         => '下载资源额外内容',
      'tinymce'       => true,
      'quicktags'     => false,
      'media_buttons' => true,
      'height'        => '400px',
      'sanitize' => false,
      'dependency' => array( 'dd_type', '==', 'down' ),
    ),

    // 图集
    array(
      'id'          => 'gallery_item',
      'type'        => 'gallery',
      'title'       => '付费图集设置',
      'add_title'   => '添加图集',
      'edit_title'  => '编辑图集',
      'clear_title' => '移除图集',
      'desc'    => '添加付费查看的图集',
      'dependency' => array( 'dd_type', '==', 'gallary' ),
    ),

    array(
      'id'      => 'gallery_num',
      'type'    => 'spinner',
      'title'   => '免费查看',
      'default' => 3,
      'desc'    => '设置可免费预览的图片数量，取前几张',
      'class'   => 'mini',
      'dependency' => array( 'dd_type', '==', 'gallary' ),
    ),

    // 视频
    array(
      'id'           => 'video_item',
      'type'         => 'upload',
      'title'        => '付费视频设置',
      'library'      => 'video',
      'placeholder'  => 'http://',
      'button_title' => '添加视频',
      'remove_title' => '移除视频',
      'desc'         => '上传付费视频资源，或外部视频链接',
      'dependency' => array( 'dd_type', '==', 'video' ),
    ),

    array(
      'id'           => 'video_poster',
      'type'         => 'upload',
      'title'        => '视频封面',
      'library'      => 'image',
      'placeholder'  => 'http://',
      'button_title' => '添加视频',
      'remove_title' => '移除视频',
      'preview'      => true,
      'desc'         => '上传付费视频封面，或外部封面链接',
      'class'   => 'mini',
      'dependency' => array( 'dd_type', '==', 'video' ),
    ),

  )
) );

//
// Metabox of the PAGE and POST both.
// Set a unique slug-like ID
//
$prefix_meta_opts = '_prefix_meta_options';

//
// Create a metabox
//
/* CSF::createMetabox( $prefix_meta_opts, array(
  'title'     => 'Custom Options',
  'post_type' => array( 'post', 'page' ),
  'context'   => 'side',
) );

//
// Create a section
//
CSF::createSection( $prefix_meta_opts, array(
  'fields' => array(

    

  )
) ); */
