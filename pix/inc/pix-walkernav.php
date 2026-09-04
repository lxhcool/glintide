<?php
//menu--------------------------------------------
if( ! class_exists( 'pix_Walker_Nav_Menu' )  ) {
    class pix_Walker_Nav_Menu extends Walker_Nav_Menu{
      
      function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
        //if(is_object( $output)){
          $id_field = $this->db_fields['id'];

          if ( is_object( $args[0] ) ) {
              $args[0]->has_children = !empty( $children_elements[$element->$id_field] );
          }
  
          return parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
        //}
  
    }

      public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        
        $c = '';
        if (is_object( $args) && $args->has_children ) {
          $c = '<i class="ri-arrow-down-s-line drop_icon"></i>';
          $item->classes[] = 'has_children';
        }

        $meta = get_post_meta( $item->ID, '_pix_menu_options', true );
        $icon = isset($meta['nav_icon']) ? $meta['nav_icon'] : '';
        $img = isset($meta['nav_img']) ? $meta['nav_img'] : '';
        $tips = 'uk-tooltip="title: '.$item->title.'; pos: right; container:body.lbc"';

        if( ! empty( $icon ) ) {
          $item->title = '<i class="'.$icon .'"></i><span class="nav_title">'.$item->title.'</span>'.$c.'';
        } else if(! empty( $img ) ) {
            $item->title = '<img src="'. $img .'"></img>' . '<span class="nav_title">'.$item->title.'</span>'.$c.'';
        } else {
            $item->title = $item->title;
        }
   
        
        
        parent::start_el( $output, $item, $depth, $args, $id );
  
      }
      


    }
  }