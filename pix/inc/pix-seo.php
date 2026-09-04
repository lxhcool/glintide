<?php
//头部title seo 
function gettitle(){
    if (function_exists('is_tag') && is_tag()) {
        single_tag_title('Tag Archive for "'); echo '" - ';
      } elseif (is_archive()) {
        wp_title(''); echo ' 分类 - ';
      } elseif (is_search()) {
        echo '搜索 "'.get_search_query().'" - ';
      } elseif (!(is_404()) && (is_single()) || (is_page())) {
        
          if ( get_post_type( get_the_ID() ) == 'moment' ) {
            $des = get_the_excerpt(get_the_ID());
            $title = get_the_title(get_the_ID());
            if($title){ 
              echo $title . ' - ';
            } else {
              echo $des ? $des.' - ' : '片刻 - ';
            }
          } else {
            wp_title(''); echo ' - ';
          }
        
      } elseif (is_404()) {
        echo 'Not Found - ';
      }
     if (is_home()) {
        bloginfo('name'); echo ' - '; bloginfo('description');
      } else {
        bloginfo('name');
     }

}


/*-----------------------------------------------------------------------------------*/
/* 自定义keywords&description
/*-----------------------------------------------------------------------------------*/
function getKeywords() {
    $keywords = "";
    if (is_home() || is_front_page()) {
        $keywords = get_op('pix_keywords');
    } else if (is_single()) {
        $categories = get_the_category(get_the_ID());
        $tags = wp_get_post_tags(get_the_ID());
        foreach ($categories as $category ) {
            $keywords = $keywords.$category->name.", ";
        }
        foreach ($tags as $tag ) {
            $keywords = $keywords.$tag->name.", ";
        }
        $keywords = $keywords.get_op('pix_keywords');
    } else if (is_category() || is_tag()) {
        $keywords = single_term_title( '', false );
    } else {
        $keywords = wp_title(",",true, "right");
    }

    echo $keywords;
}

function getDescription() {
    if (is_single()) {
        $description = get_the_excerpt(get_the_ID());
    } else {
        $description = get_op('pix_description');
    }
    echo $description;
}