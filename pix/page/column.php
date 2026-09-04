<?php
/*
	Template Name: 专题
*/
get_header(); 

function column_show(){
	$html = '';
	$term_lists = get_terms( array(
		'taxonomy'     => 'category',
		'include'      => get_op('column_show'),
		'count'        => true,
		'hide_empty'   => 0,
		'orderby'      => 'include',
		'hide_empty' => true
	) );
	if(isset($term_lists) && is_array($term_lists)){	
	foreach($term_lists as $term_list){
		
		$term_id = $term_list->term_id;
		$term_name = $term_list->name;
		$count = $term_list->count;
		$des = $term_list->description;
		$link = get_category_link($term_id);
		$cat_img = get_term_meta( $term_id, 'cat_img', true );
		$de_img = get_bloginfo('template_directory').'/img/banner.jpg';
		$cat_img = $cat_img ? $cat_img : $de_img;
		$html .=  '<div class="column_item"><div class="column_inner round12">
				<div class="cat_img"><a href="'.$link .'"><img src="'.$cat_img.'"><div class="meta"><div class="title">'.$term_name.'</div><p><span>'.$count.'POSTS</span></p></div></a></div>
				'.column_list($term_id).'
				</div></div>';
		
	}
		
}
	return $html;
	
}

function column_list($id){
	$html = '';
	$arr = array(
		'numberposts' => 2,
		'category' => $id,
		'orderby' => 'date',
		'order' => 'DESC',
		'post_type' => 'post',
	);

	$lists = get_posts($arr);
	$html .= '<ul class="column_lists">';
	if(isset($lists) && is_array($lists)){
		foreach($lists as $list){
			$title = $list->post_title;
			$date = $list->post_date;
			$id = $list->ID;
			$link = get_permalink($id);
			$html .= '<li><a href="'.$link.'"><i class="iconfont icon-huati"></i>'.$title.'</a><span>'.timeago(get_gmt_from_date($date)).'</span></li>';
		}
	}

	$html .= '</ul>';

	return $html;
}

?>

<div class="column_page page_wrap" uk-height-viewport="offset-top: true">
	<div class="page_inner uk-grid-small uk-child-width-1-2@m" uk-grid>
		<?php echo column_show(); ?>
	</div>
</div>


<?php
get_footer();

