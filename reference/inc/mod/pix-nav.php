<?php
//经典导航函数

//经典导航高度
function cls_nav_height(){
    $data = get_cu('classic_nav_base_tab');
    if(is_array($data)){
      $h = $data['nav_height'];
    } else {
      $h = '72';
    }

    return $h;
}

//walkernav
function ppo_wp_nav_menu_objects( $items, $args ) {

    foreach ( $items as &$item ) {
      $c = '';
      if(in_array("menu-item-has-children", $item->classes)){
        $c = pix_icon('ri-arrow-down-s-line drop_icon');
      }

      $meta = get_post_meta( $item->ID, '_ppo_menu_options', true );
      $icon = isset($meta['nav_icon']) ? $meta['nav_icon'] : '';
      $img = isset($meta['nav_img']) ? $meta['nav_img'] : '';

      if( ! empty( $icon ) ) {
        $item->title = '<span class="nav-link-item" text-data="'.$item->title.'"><i class="'.$icon .'"></i><span class="nav_title">'.$item->title.'</span>'.$c.'</span>';
      } else if(! empty( $img ) ) {
          $item->title = '<span class="nav-link-item" text-data="'.$item->title.'"><img src="'. $img .'">' . '<span class="nav_title">'.$item->title.'</span>'.$c.'</span>';
      } else {
          $item->title = $item->title;
      }
  
  
    }
  
    return $items;
  
  }
  
add_filter( 'wp_nav_menu_objects', 'ppo_wp_nav_menu_objects', 10, 2 );


//菜单活动项
add_filter('nav_menu_css_class' , 'special_nav_class' , 10 , 2);
function special_nav_class ($classes, $item) {
    if (in_array('current-post-ancestor', $classes) || in_array('current-page-ancestor', $classes) || in_array('current-menu-item', $classes) ){
        $classes[] = 'active-nav';
    }
    return $classes;
}


