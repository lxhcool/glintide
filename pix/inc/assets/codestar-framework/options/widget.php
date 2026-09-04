<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.



//社交资料
CSF::createWidget( 'user_widget', array(
  'title'       => 'PIX-博主社交信息',
  'classname'   => 'admin_widget',
  'description' => '展示博主社交资料',
  'fields'      => array(

    array(
      'id'    => 'title',
      'type'  => 'text',
      'title' => '菜单名称',
      'default' => 'Follow Me'
    ),

    array(
      'id'        => 'admin_info',
      'type'      => 'group',
      'title'     => '博主社交资料',
      'fields'    => array(
        array(
          'id'    => 'ad_title',
          'type'  => 'text',
          'title' => '名称',
        ),

        array(
          'id'     => 's_btn_bg',
          'type'   => 'color',
          'title'  => '按钮背景颜色',
          'default' => '#2f2f2f'
        ),

        array(
          'id'      => 'ad_icon',
          'type'    => 'icon',
          'title'   => '图标',
          'default' => 'ri-home-line',
          'desc' => '图标和图片二选一'
        ),

        array(
          'id'           => 'ad_img',
          'type'         => 'upload',
          'title'        => '图标图片',
          'library'      => 'image',
          'button_title' => '上传图像',
          'remove_title' => '移除图像',
          'desc' => '建议18x18(px)方形的PNG图像'
        ),

        array(
          'id'         => 'ad_show_type',
          'type'       => 'radio',
          'title'      => '信息展示方式',
          'inline'  => true,
          'options'    => array(
          'link'    => '跳转链接',
          'qrcode'    => '二维码',					
          ),
          'default'    => 'link',
        ),
     
        array(
          'id'    => 'ad_link',
          'type'  => 'text',
          'title' => '自定义链接',
          'dependency' => array( 'ad_show_type', '==', 'link' ),
        ),

        array(
          'id'    => 'ad_open_new',
          'type'  => 'switcher',
          'title' => '新窗口打开',
          'dependency' => array( 'ad_show_type', '==', 'link' ),
        ),

        array(
          'id'           => 'ad_qrcode',
          'type'         => 'upload',
          'title'        => '二维码',
          'library'      => 'image',
          'button_title' => '上传图像',
          'remove_title' => '移除图像',
          'desc' => '建议尺寸不要太大',
          'dependency' => array( 'ad_show_type', '==', 'qrcode' ),
        ),

      ),
    ),

  )
) );

if( ! function_exists( 'user_widget' ) ) {
  function user_widget( $args, $instance ) {

    echo $args['before_widget'];
    $arr = $instance['admin_info'];
    $title = $instance['title'];
		
		adminsocial_widget($arr, $title);
		
    echo $args['after_widget'];

  }
}

//基本信息
CSF::createWidget( 'adinfo_widget', array(
  'title'       => 'PIX-博主基础信息',
  'classname'   => 'adinfo_widget',
  'description' => '展示博主个人资料',
  'fields'      => array(

    array(
      'id'    => 'title',
      'type'  => 'text',
      'title' => '菜单名称',
      'default' => 'About Me'
    ),

    array(
      'id'        => 'base_info',
      'type'      => 'group',
      'title'     => 'PIX-博主个人资料',
      'fields'    => array(
        array(
          'id'    => 'ba_title',
          'type'  => 'text',
          'title' => '名称',
        ),

        array(
          'id'      => 'ba_icon',
          'type'    => 'icon',
          'title'   => '图标',
          'default' => 'ri-home-line',
          'desc' => '图标和图片二选一'
        ),
        array(
          'id'           => 'ba_img',
          'type'         => 'upload',
          'title'        => '图片',
          'library'      => 'image',
          'button_title' => '上传图像',
          'remove_title' => '移除图像',
          'desc' => '建议18x18(px)方形的PNG图像'
        ),
        
        array(
          'id'    => 'ba_des',
          'type'  => 'text',
          'title' => '描述',
        ),

        array(
          'id'    => 'ba_link',
          'type'  => 'text',
          'title' => '自定义链接',
          'desc'  => '可不填'
        ),

        array(
          'id'    => 'ba_open_new',
          'type'  => 'switcher',
          'title' => '新窗口打开',
        ),
      ),
    ),

  )
) );

if( ! function_exists( 'adinfo_widget' ) ) {
  function adinfo_widget( $args, $instance ) {

    echo $args['before_widget'];
    $arr = $instance['base_info'];
    $title = $instance['title'];
		
		admininfo_widget($arr, $title);
		
    echo $args['after_widget'];

  }
}

//文章展示
CSF::createWidget( 'posts_show_widget', array(
  'title'       => 'PIX-文章展示',
  'classname'   => 'posts_show_widget',
  'description' => '展示站点文章，PIX小工具',
  'fields'      => array(

    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
			'default' => '热门文章'
    ),

    array(
      'id'      => 'posts_number',
      'type'    => 'text',
      'title'   => '文章数量',
      'default' => '6'
    ),
		
		array(
			'id'          => 'show_style',
			'type'        => 'select',
			'title'       => '展示形式',
			'options'     => array(
				'no_img'  => '无图列表',
				'small_img'  => '小图列表',
				//'large_img'  => '大图列表',
				//'first_large' => '首个大图'
			),
			'default'     => 'small_img'
		),

   	array(
			'id'          => 'show_order',
			'type'        => 'select',
			'title'       => '排序标准',
			'options'     => array(
				'date'  => '最新发布',
				'views'  => '浏览量',
				'rand'  => '随机',
				'comments' => '评论数'
			),
			'default'     => 'views'
		 ),

  )
) );

//
// Front-end display of widget example 1
// Attention: This function named considering above widget base id.
//
if( ! function_exists( 'posts_show_widget' ) ) {
  function posts_show_widget( $args, $instance ) {

    echo $args['before_widget'];

    //if ( ! empty( $instance['title'] ) ) {
      //echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
    //}

		//获取参数
    $title = $instance['title'];
    $posts_number = $instance['posts_number'];
    $show_style = $instance['show_style'];
    $show_order = $instance['show_order'];
    $title = $instance['title'];
		
		
		pix_posts_show_widget($posts_number,$show_style,$show_order,$title);

    echo $args['after_widget'];

  }
}

//专题展示
CSF::createWidget( 'hot_cat', array(
  'title'       => 'PIX-专题推荐',
  'classname'   => 'hot_cat',
  'description' => '展示推荐的分类专题',
  'fields'      => array(

    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
      'default' => '专题推荐'
    ),
		
		array(
			'id'          => 'widget_cat_select',
			'type'        => 'select',
			'title'       => '专题选择',
			'chosen'      => true,
			'multiple'    => true,
			'placeholder' => '选择一个或多个分类',
			'options'     => 'categories',
		),	

  )
) );


if( ! function_exists( 'hot_cat' ) ) {
  function hot_cat( $args, $instance ) {

    echo $args['before_widget'];

    //if ( ! empty( $instance['title'] ) ) {
      //echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
    //}

    // var_dump( $args ); // Widget arguments
    // var_dump( $instance ); // Saved values from database
  
    $cat = $instance['widget_cat_select'];
    $title = $instance['title'];
		
		cst_cats_show_widget($cat,$title);
		
    echo $args['after_widget'];

  }
}

//
// 最近评论
//
CSF::createWidget( 'cst_widget_comment', array(
  'title'       => 'PIX-最新评论',
  'classname'   => 'cst_widget_comment',
  'description' => '展示站最新评论，PIX小工具',
  'fields'      => array(

    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
			'default' => '最新评论'
    ),

    array(
      'id'      => 'number',
      'type'    => 'text',
      'title'   => '评论数量',
      'default' => '5'
    ),
		
		array(
			'id'          => 'post_type',
			'type'        => 'select',
			'title'       => '评论类型',
			'options'     => array(
				'post'  => '文章',
				'moment'  => '片刻',
			),
			'default'     => 'post'
		),

  )
) );

if( ! function_exists( 'cst_widget_comment' ) ) {
  function cst_widget_comment( $args, $instance ) {

    echo $args['before_widget'];

    //if ( ! empty( $instance['title'] ) ) {
      //echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
    //}

    // var_dump( $args ); // Widget arguments
    // var_dump( $instance ); // Saved values from database
  
    $number = $instance['number'];
    $type = $instance['post_type'];
    $title = $instance['title'];
		
		cst_widget_com($number,$type, $title);
		
    echo $args['after_widget'];

  }
}

//
// 站点统计
//
CSF::createWidget( 'pix_widget_tongji', array(
  'title'       => 'PIX-站点统计',
  'classname'   => 'pix_widget_tongji',
  'description' => '站点统计，PIX小工具',
  'fields'      => array(

    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
			'default' => '站点统计'
    ),

    array(
      'id'         => 'site_tongji',
      'type'       => 'select',
      'title'      => '统计类型',
      'chosen'      => true,
      'multiple'    => true,
      'sortable'   => true,
      'placeholder' => '选择一个或多个统计',
      'options'    => array(
        'posts_count' => '文章数',
        'moments_count' => '片刻数',
        'tags_count' => '标签数',
        'comments_count' => '评论数',
        'cat_count' => '分类数',
        'links_count' => '友链数',
        'site_day' => '运行时间',
      ),
      'default'    => array( 'posts_count', 'moments_count' )
    ),

    array(
      'id'      => 'build_date',
      'type'    => 'text',
      'title'   => '建站日期',
			'default' => '1992-10-12',
      'desc'    => '格式：2022-1-1'
    ),

  )
) );

if( ! function_exists( 'pix_widget_tongji' ) ) {
  function pix_widget_tongji( $args, $instance ) {

    echo $args['before_widget'];

    //if ( ! empty( $instance['title'] ) ) {
      //echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
    //}

    // var_dump( $args ); // Widget arguments
    // var_dump( $instance ); // Saved values from database
  
    $data = $instance['site_tongji'];
    $title = $instance['title'];
    $build_date = $instance['build_date'];
		
		pix_site_tongji($data,$title,$build_date);
		
    echo $args['after_widget'];

  }
}

//一言小工具
CSF::createWidget( 'pix_widget_yiyan', array(
  'title'       => 'PIX-一言',
  'classname'   => 'pix_widget_yiyan',
  'description' => '一言，PIX小工具',
  'fields'      => array(

    array(
      'id'      => 'title',
      'type'    => 'text',
      'title'   => '标题',
			'default' => '一言'
    ),  

    array(
      'id'           => 'yiyan_bg',
      'type'         => 'upload',
      'title'        => '背景图',
      'library'      => 'image',
      'placeholder'  => 'http(s)://',
      'button_title' => '添加背景',
      'remove_title' => '移除背景',
      'default'     => THEME_URL.'/img/banner.jpg',
    ),

  )
) );

if( ! function_exists( 'pix_widget_yiyan' ) ) {
  function pix_widget_yiyan( $args, $instance ) {

    echo $args['before_widget'];

    //if ( ! empty( $instance['title'] ) ) {
      //echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
    //}

    // var_dump( $args ); // Widget arguments
    // var_dump( $instance ); // Saved values from database
  
    $title = $instance['title'];
    $yiyan_bg = $instance['yiyan_bg'];
		
		pix_site_yiyan($yiyan_bg,$title);
		
    echo $args['after_widget'];

  }
}


