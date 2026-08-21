<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

//
// Set a unique slug-like ID
//
$prefix = '_ppo_taxonomy_options';
$cash = THEME_URL . '/img/icon/balance.png';

//
// Create taxonomy options
//
CSF::createTaxonomyOptions( $prefix, array(
  'taxonomy' => 'category',
) );

//
// Create a section
//
CSF::createSection( $prefix, array(
  'fields' => array(

    //
    // A text field
    //
    array(
      'id'    => 'seo_title',
      'type'  => 'text',
      'title' => 'SEO标题',
    ),

    array(
      'id'    => 'seo_keywords',
      'type'  => 'text',
      'title' => 'SEO关键词',
    ),

    array(
      'id'    => 'cat_banner',
      'type'  => 'upload',
      'title' => '分类封面',
      'preview' => true,
      'library' => 'image',
    ),

  )
) );

//片刻分类设置
$moments_cat = '_ppo_moments_options';

CSF::createTaxonomyOptions( $moments_cat, array(
  'taxonomy' => 'moments',
  'class'      => 'pix-options tax-content',
  'data_type'  => 'unserialize',
) );

CSF::createSection( $moments_cat, array(
  'title' => '片刻圈子设置',
  'fields' => array(

    array(
      'id'      => 'seo_title',
      'type'    => 'text',
      'title'   => 'SEO标题',
    ),

    array(
      'id'      => 'seo_keywords',
      'type'    => 'text',
      'title'   => 'SEO关键词',
    ),

    array(
      'id'         => 'ppo_moments_tag',
      'type'       => 'radio',
      'title'      => '圈子所属标签',
      'inline'       => true,
      'options'    => 'get_moments_tag_arr',
      'default'    => '广场',
    ),

    array(
      'id'           => 'mo_cat_img',
      'type'         => 'upload',
      'title'        => '圈子图标',
      'library'      => 'image',
      'placeholder'  => 'http(s)://',
      'button_title' => '添加图标',
      'remove_title' => '移除图标',
      'preview'       => true,
    ),

    array(
      'id'           => 'mo_cat_banner',
      'type'         => 'upload',
      'title'        => '圈子封面',
      'library'      => 'image',
      'placeholder'  => 'http(s)://',
      'button_title' => '添加封面',
      'remove_title' => '移除封面',
      'preview'       => true,
    ),

    array(
      'id'          => 'mo_owner',
      'type'        => 'select',
      'title'       => '圈主',
      'placeholder' => '设置圈主',
      'chosen'      => true,
      'ajax'        => true,
      'options'     => 'users',
      'default'     => 1,
      'desc'        => '默认为管理员'
    ),

    // 圈子类型
    array(
      'id'         => 'mo_join_type',
      'type'       => 'radio',
      'title'      => '圈子加入方式',
      'inline'       => true,
      'options'    => array(
        'free' => '免费',
        'pay' => '付费',
        'limits' => '权限',
      ),
      'default' => 'free'
    ),

    array(
      'id'          => 'mo_free_join_type',
      'type'        => 'select',
      'title'       => '免费圈子配置',
      'options'     => array(
        'free'  => '直接加入',
        'verify'  => '审核后加入',
      ),
      'default'     => 'free',
      'desc'        => '当圈子为免费时，可以选择直接加入或者审核后加入',
      'dependency' => array( 'mo_join_type', '==', 'free' ),
    ),

    array(
      'id'         => 'mo_join_limits',
      'type'       => 'checkbox',
      'title'      => '权限圈子配置',
      'inline'       => true,
      'options'    => 'all_lv_merge',
      'check_all' => true,
      'check_all_text' => '全选/取消全选',
      'default'    => true,
      'dependency' => array( 'mo_join_type', '==', 'limits' ),
    ),

    array(
      'id'     => 'mo_join_pay',
      'type'   => 'fieldset',
      'title'  => '付费圈子配置',
      'fields' => array(
        array(
          'id'    => 'mp',
          'type'  => 'text',
          'title' => '月付金额',
          'before' => $cash,
          'class' => 'mini-input'
        ),

        array(
          'id'    => 'qp',
          'type'  => 'text',
          'title' => '季付金额',
          'before' => $cash,
          'class' => 'mini-input'
        ),

        array(
          'id'    => 'hp',
          'type'  => 'text',
          'title' => '半年金额',
          'before' => $cash,
          'class' => 'mini-input'
        ),

        array(
          'id'    => 'op',
          'type'  => 'text',
          'title' => '年付金额',
          'before' => $cash,
          'class' => 'mini-input'
        ),

        array(
          'id'    => 'fp',
          'type'  => 'text',
          'title' => '永久金额',
          'before' => $cash,
          'class' => 'mini-input'
        ),
      
      ),
      'dependency' => array( 'mo_join_type', '==', 'pay' ),
    ),

    array(
      'id'      => 'mo_pay_credit_only',
      'type'    => 'switcher',
      'title'   => '仅使用积分支付',
      'label'   => '开启后，用户只能使用积分购买该圈子；关闭后使用余额、支付宝、微信支付',
      'default' => false,
      'dependency' => array( 'mo_join_type', '==', 'pay' ),
    ),

    array(
      'id'          => 'mo_show_type',
      'type'        => 'select',
      'title'       => '内容查看权限',
      'options'     => array(
        'show'    => '公开查看全部',
        'join'    => '公开前 N 篇，加入后查看全部',
        'private' => '加入后查看',
      ),
      'default'     => 'show',
      'desc'        => '公开查看全部：未加入用户也能查看全部内容。公开前 N 篇：未加入用户只能查看指定数量内容，加入后查看全部。加入后查看：必须加入圈子后才能查看内容。
                        <br>*此处修改后，请前往<a href="'.ppo_get_admin_csf_url('片刻设置/重建圈子数据').'" style="color:red" target="_blank">重建圈子数据</a>进行数据重建，否则可能无法正常显示',
    ),

    array(
      'id'          => 'show_num',
      'type'        => 'number',
      'title'       => '允许查看内容数量',
      'unit'        => '篇',
      'default'     => 3,
      'desc'        => '未加入用户可查看的内容数量，填 0 则不公开内容',
      'dependency' => array( 'mo_show_type', '==', 'join' ),
    ),

    array(
      'type'    => 'subheading',
      'content' => '圈子权限设置',
    ),


    array(
      'id'          => 'mo_set_type',
      'type'        => 'select',
      'title'       => '圈子配置类型',
      'options'     => array(
        'single'  => '单独配置圈子权限',
        'global'  => '使用全局配置',
      ),
      'default'     => 'global',
      'desc'        => '您可以单独配置每个圈子，也可以使用全局配置',
    ),
    


    array(
      'id'         => 'mo_join_fun',
      'type'       => 'checkbox',
      'title'      => '圈子功能开启',
      'inline'       => true,
      'options'    => array(
          'gallery' => '图集',
          'video' => '视频',
          'audio' => '音乐',
          'file' => '文件',
          'card' => '卡片',
      ),
      'check_all' => true,
      'check_all_text' => '全选/取消全选',
      'default'    => array( 'gallery', 'video','card' ),
      'desc'  => '当此处开启之后，并且用户组有此权限，才可以使用此功能',
      'dependency' => array( 'mo_set_type', '==', 'single' ),
    ),

    array(
      'id'         => 'allow_card_group',
      'type'       => 'checkbox',
      'title'      => '允许插入卡片的用户组',
      'inline'       => true,
      'options'    => 'all_lv_merge',
      'check_all' => true,
      'check_all_text' => '全选/取消全选',
      'default'    => true,
      'dependency' => array( 'mo_set_type', '==', 'single' ),
    ),

    array(
      'id'          => 'gallery_num',
      'type'        => 'number',
      'title'       => '最大图集数量',
      'unit'        => '张',
      'default'     => 9,
      'dependency' => array( 'mo_set_type', '==', 'single' ),
    ),

    array(
      'id'      => 'gallery_link',
      'type'    => 'switcher',
      'title'   => '是否允许外链图集',
      'label'   => '开启后，用户可插入外链图片',
      'default' => true,
      'dependency' => array( 'mo_set_type', '==', 'single' ),
    ),

    array(
      'id'          => 'card_num',
      'type'        => 'number',
      'title'       => '最多插入卡片数量',
      'unit'        => '个',
      'default'     => 3,
      'dependency' => array( 'mo_set_type', '==', 'single' ),
    ),

    array(
      'id'          => 'file_num',
      'type'        => 'number',
      'title'       => '最多上传文件数量',
      'unit'        => '个',
      'default'     => 3,
      'dependency' => array( 'mo_set_type', '==', 'single' ),
    ),

    array(
      'id'      => 'mo_text_num',
      'type'    => 'text',
      'title'   => '发布字数限制',
      'default' => '5-240',
      'desc'  => '5-240代表，最少5个字最多240个字，请以此格式填写',
      'dependency' => array( 'mo_set_type', '==', 'single' ),
    ),


  )
) );


