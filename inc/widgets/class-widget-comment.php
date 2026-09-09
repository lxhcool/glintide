<?php
/**
 * 小工具:评论列表
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_Comment extends Glintide_Widget {

	public static $id          = 'glintide_comment_widget';
	public static $title       = 'PPO · 评论列表';
	public static $description = '展示最新评论列表';
	public static $classname   = 'ppo-widget glintide_comment_widget';

	public static function fields() {
		return array(
			array(
				'id'      => 'sort_by',
				'type'    => 'radio',
				'title'   => '排序方式',
				'options' => array(
					'newest'  => '最新评论',
					'popular' => '按文章点赞量',
				),
				'default' => 'newest',
			),
			array(
				'id'      => 'comment_count',
				'type'    => 'number',
				'title'   => '显示数量',
				'default' => 5,
			),
		);
	}

	public static function render( $instance ) {
		$sort_by = isset( $instance['sort_by'] ) ? $instance['sort_by'] : 'newest';
		$count   = isset( $instance['comment_count'] ) ? intval( $instance['comment_count'] ) : 5;

		$args = array(
			'number' => $count,
			'status' => 'approve',
			'type'   => 'comment',
		);

		if ( $sort_by === 'newest' ) {
			$args['orderby'] = 'comment_date';
			$args['order']   = 'DESC';
		}

		$comments = get_comments( $args );

		if ( $sort_by !== 'newest' ) {
			usort(
				$comments,
				function( $a, $b ) {
					$a_likes = intval( get_comment_meta( $a->comment_ID, 'like_count', true ) ?: 0 );
					$b_likes = intval( get_comment_meta( $b->comment_ID, 'like_count', true ) ?: 0 );
					return $b_likes - $a_likes;
				}
			);
		}

		$html  = '<div class="glintide-comment-widget wid-item">';

		if ( empty( $comments ) ) {
			$html .= '<div class="glintide-comment-empty">暂无评论</div>';
		} else {
			foreach ( $comments as $comment ) {
				$user_id         = $comment->user_id;
				$author_name     = $comment->comment_author;
				$post_url        = get_permalink( $comment->comment_post_ID );
				$avatar_url      = $user_id ? glintide_get_avatar_url( $user_id ) : '';
				$avatar_fallback  = function_exists( 'glintide_get_default_avatar_url' ) ? glintide_get_default_avatar_url() : GLINTIDE_URL . '/assets/images/default-avatar.png';
				$comment_date    = human_time_diff( strtotime( $comment->comment_date ), current_time( 'timestamp' ) ) . '前';
				$comment_content = self::excerpt( $comment->comment_content );
				$like_count      = get_comment_meta( $comment->comment_ID, 'like_count', true ) ?: 0;

				$modal = 'post' === get_post_type( $comment->comment_post_ID ) ? ' data-glintide-modal="' . esc_attr( $comment->comment_post_ID ) . '" data-glintide-no-pjax' : '';
				$html .= '<a href="' . esc_url( $post_url ) . '" class="glintide-comment-item"' . $modal . '>';
				$html .= $avatar_url ? '<img src="' . esc_url( $avatar_url ) . '" alt="' . esc_attr( $author_name ) . '" class="glintide-comment-avatar" data-glintide-avatar data-glintide-avatar-fallback="' . esc_url( $avatar_fallback ) . '">' : '<div class="glintide-comment-avatar glintide-comment-avatar-empty"><i class="ri-user-3-line"></i></div>';
				$html .= '<div class="glintide-comment-body">';
				$html .= '<div class="glintide-comment-meta">';
				$html .= '<span class="glintide-comment-author">' . esc_html( $author_name ) . '</span>';
				$html .= '<span class="glintide-comment-time">' . esc_html( $comment_date ) . '</span>';
				$html .= '<span class="glintide-comment-likes"><i class="ri-thumb-up-line"></i>' . intval( $like_count ) . '</span>';
				$html .= '</div>';
				$html .= '<div class="glintide-comment-content">' . esc_html( $comment_content ) . '</div>';
				$html .= '</div>';
				$html .= '</a>';
			}
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * 评论内容摘要
	 */
	protected static function excerpt( $content ) {
		$content   = (string) $content;
		$has_image = preg_match( '/<img\b[^>]*>/i', $content );
		$content   = preg_replace( '/<img\b[^>]*>/i', ' [图片] ', $content );
		$content   = wp_strip_all_tags( $content );
		$content   = preg_replace( '/\s+/u', ' ', $content );
		$content   = trim( $content );

		if ( $content === '' && $has_image ) {
			return '[图片]';
		}

		return wp_trim_words( $content, 100, '...' );
	}
}

// 前端渲染函数(CSF 按 widget ID 调用)
if ( ! function_exists( 'glintide_comment_widget' ) ) {
	function glintide_comment_widget( $args, $instance ) {
		echo $args['before_widget'];
		echo Glintide_Widget_Comment::render( $instance );
		echo $args['after_widget'];
	}
}