<?php
/**
 * Glintide 内容卡片
 *
 * 注册独立的内容卡片类型，提供后台发布字段和前台多媒体渲染能力。
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * 内容卡片类型配置。
 *
 * @return array
 */
function glintide_card_type_options() {
	return array(
		'text'  => array(
			'label'       => '文字',
			'icon'        => 'ri-file-text-line',
			'description' => '发布一段文字或随笔',
		),
		'photo' => array(
			'label'       => '照片',
			'icon'        => 'ri-image-2-line',
			'description' => '发布一张或一组照片',
		),
		'music' => array(
			'label'       => '音乐',
			'icon'        => 'ri-music-2-line',
			'description' => '发布音频与封面信息',
		),
		'video' => array(
			'label'       => '视频',
			'icon'        => 'ri-video-line',
			'description' => '发布视频或视频链接',
		),
		'link'  => array(
			'label'       => '链接',
			'icon'        => 'ri-links-line',
			'description' => '分享一个外部网页或资源',
		),
		'quote' => array(
			'label'       => '引用',
			'icon'        => 'ri-double-quotes-l',
			'description' => '记录一句话或一段摘录',
		),
		'code'  => array(
			'label'       => '代码',
			'icon'        => 'ri-code-s-slash-line',
			'description' => '展示一段代码片段',
		),
	);
}

/**
 * 注册内容卡片自定义文章类型。
 */
function glintide_register_content_card_type() {
	$labels = array(
		'name'               => '内容卡片',
		'singular_name'      => '内容卡片',
		'menu_name'          => '内容卡片',
		'name_admin_bar'     => '内容卡片',
		'add_new'            => '发布卡片',
		'add_new_item'       => '发布内容卡片',
		'new_item'           => '新内容卡片',
		'edit_item'          => '编辑内容卡片',
		'view_item'          => '查看内容卡片',
		'all_items'          => '全部卡片',
		'search_items'       => '搜索卡片',
		'not_found'          => '暂无内容卡片',
		'not_found_in_trash' => '回收站暂无内容卡片',
	);

	register_post_type(
		'glintide_card',
		array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-format-gallery',
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'has_archive'        => false,
			'rewrite'            => array(
				'slug'       => 'content-card',
				'with_front' => false,
			),
			'supports'           => array( 'title', 'editor', 'thumbnail', 'author' ),
			'taxonomies'         => array( 'category', 'post_tag' ),
			'show_in_nav_menus'  => false,
		)
	);
}
add_action( 'init', 'glintide_register_content_card_type' );

/**
 * 主题切换时刷新内容卡片的固定链接规则。
 */
function glintide_flush_content_card_rewrites() {
	glintide_register_content_card_type();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'glintide_flush_content_card_rewrites' );

/**
 * 主题已经启用时，为新加入的内容卡片规则做一次性刷新。
 */
function glintide_maybe_flush_content_card_rewrites() {
	$rewrite_version = get_option( 'glintide_card_rewrite_version', '' );

	if ( GLINTIDE_VERSION !== $rewrite_version ) {
		flush_rewrite_rules( false );
		update_option( 'glintide_card_rewrite_version', GLINTIDE_VERSION );
	}
}
add_action( 'init', 'glintide_maybe_flush_content_card_rewrites', 20 );

/**
 * 首次运行时创建一组可直接预览的内容卡片。
 *
 * 每种类型只补齐缺少的内容，不会覆盖已有卡片；使用固定标记避免重复创建。
 */
function glintide_seed_content_cards() {
	$seed_version = 'glintide-card-content-pack-v1';

	if ( $seed_version === get_option( 'glintide_card_seed_version', '' ) ) {
		return;
	}

	$card_content = array(
		'text'  => array(
			'title'   => '把今天写给明天',
			'content' => "有些想法只在今天出现一次，值得先把它写下来。\n\n等时间往前走，我们再回来看看当时的自己。",
		),
		'photo' => array(
			'title'   => '雨停之后，城市开始发亮',
			'content' => '记录一束从窗边落下来的光，也记录城市安静下来的几分钟。',
			'meta'    => array(
				'_glintide_card_gallery' => GLINTIDE_URL . '/assets/images/banner.jpg',
			),
		),
		'music' => array(
			'title'   => '夜航：给还没睡的人',
			'content' => '把音量调到合适的位置，让这一段旋律陪你走完今晚。',
			'meta'    => array(
				'_glintide_card_music_artist' => 'Night Notes',
				'_glintide_card_music_url'    => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
				'_glintide_card_gallery'      => GLINTIDE_URL . '/assets/images/banner.jpg',
			),
		),
		'video' => array(
			'title'   => '花影在风里经过',
			'content' => '一段不需要解释的移动镜头，适合在内容流里偶遇。',
			'meta'    => array(
				'_glintide_card_video_url' => 'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4',
				'_glintide_card_gallery'   => GLINTIDE_URL . '/assets/images/banner.jpg',
			),
		),
		'link'  => array(
			'title'   => '今天读到：MDN 的渐进增强',
			'content' => '把链接留在这里，等下一次需要时再打开。',
			'meta'    => array(
				'_glintide_card_link_url'   => 'https://developer.mozilla.org/en-US/docs/Glossary/Progressive_Enhancement',
				'_glintide_card_link_label' => '阅读渐进增强的完整说明',
			),
		),
		'quote' => array(
			'title'   => '留一句话给自己',
			'content' => '真正重要的事情，往往不需要很响亮。\n它会在你准备好时，安静地被想起。',
			'meta'    => array(
				'_glintide_card_quote_source' => '日常记录',
			),
		),
		'code'  => array(
			'title'   => '一个更轻的卡片查询',
			'content' => "const cards = document.querySelectorAll('.glintide-card');\n\ncards.forEach(function (card) {\n    card.classList.add('is-ready');\n});",
			'meta'    => array(
				'_glintide_card_code_language' => 'JavaScript',
			),
		),
	);

	$author_id = get_current_user_id();
	if ( ! $author_id ) {
		$admins = get_users(
			array(
				'role'   => 'administrator',
				'number' => 1,
				'fields' => 'ID',
			)
		);
		$author_id = ! empty( $admins ) ? absint( $admins[0] ) : 0;
	}

	foreach ( $card_content as $type => $content ) {
		$existing = get_posts(
			array(
				'post_type'      => 'glintide_card',
				'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
				'posts_per_page' => 1,
				'meta_key'       => '_glintide_card_type',
				'meta_value'     => $type,
				'fields'         => 'ids',
			)
		);

		if ( ! empty( $existing ) ) {
			continue;
		}

		$post_args = array(
			'post_title'   => $content['title'],
			'post_content' => $content['content'],
			'post_status'  => 'publish',
			'post_type'    => 'glintide_card',
		);

		if ( $author_id ) {
			$post_args['post_author'] = $author_id;
		}

		$post_id = wp_insert_post( $post_args, true );
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, '_glintide_card_type', $type );
		update_post_meta( $post_id, '_glintide_card_seed', $seed_version );

		if ( ! empty( $content['meta'] ) ) {
			foreach ( $content['meta'] as $meta_key => $meta_value ) {
				update_post_meta( $post_id, $meta_key, $meta_value );
			}
		}
	}

	update_option( 'glintide_card_seed_version', $seed_version );
}
add_action( 'init', 'glintide_seed_content_cards', 30 );

/**
 * 添加内容卡片设置面板。
 */
function glintide_add_content_card_meta_boxes() {
	add_meta_box(
		'glintide-card-details',
		'卡片内容设置',
		'glintide_render_content_card_meta_box',
		'glintide_card',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_glintide_card', 'glintide_add_content_card_meta_boxes' );

/**
 * 输出内容卡片设置面板。
 *
 * @param WP_Post $post 当前文章对象。
 */
function glintide_render_content_card_meta_box( $post ) {
	$type_options = glintide_card_type_options();
	$selected     = glintide_card_get_type( $post->ID );
	$artist       = get_post_meta( $post->ID, '_glintide_card_music_artist', true );
	$music_url    = get_post_meta( $post->ID, '_glintide_card_music_url', true );
	$video_url    = get_post_meta( $post->ID, '_glintide_card_video_url', true );
	$link_url      = get_post_meta( $post->ID, '_glintide_card_link_url', true );
	$link_label    = get_post_meta( $post->ID, '_glintide_card_link_label', true );
	$quote_source  = get_post_meta( $post->ID, '_glintide_card_quote_source', true );
	$code_language = get_post_meta( $post->ID, '_glintide_card_code_language', true );
	$gallery       = get_post_meta( $post->ID, '_glintide_card_gallery', true );
	$gallery       = is_array( $gallery ) ? implode( "\n", $gallery ) : (string) $gallery;

	wp_nonce_field( 'glintide_save_content_card', 'glintide_content_card_nonce' );
	?>
	<div class="glintide-card-editor" data-glintide-card-editor>
		<p class="glintide-card-editor-intro">选择卡片类型后填写对应内容。照片和音乐封面可以使用右侧的“特色图片”，文字、引用和代码直接填写上方正文。</p>

		<fieldset class="glintide-card-type-fieldset">
			<legend>卡片类型</legend>
			<div class="glintide-card-type-options">
				<?php foreach ( $type_options as $type => $type_data ) : ?>
					<label class="glintide-card-type-option<?php echo $selected === $type ? ' is-selected' : ''; ?>">
						<input type="radio" name="glintide_card_type" value="<?php echo esc_attr( $type ); ?>" <?php checked( $selected, $type ); ?>>
						<span class="glintide-card-type-option-content">
							<span class="glintide-card-type-option-title"><i class="<?php echo esc_attr( $type_data['icon'] ); ?>" aria-hidden="true"></i><?php echo esc_html( $type_data['label'] ); ?></span>
							<span class="glintide-card-type-option-description"><?php echo esc_html( $type_data['description'] ); ?></span>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<div class="glintide-card-field-group<?php echo 'text' === $selected ? ' is-active' : ''; ?>" data-glintide-card-fields="text" aria-hidden="<?php echo 'text' === $selected ? 'false' : 'true'; ?>">
			<p class="description">文字卡片直接使用上方编辑器中的正文。</p>
		</div>

		<div class="glintide-card-field-group<?php echo 'photo' === $selected ? ' is-active' : ''; ?>" data-glintide-card-fields="photo" aria-hidden="<?php echo 'photo' === $selected ? 'false' : 'true'; ?>">
			<label for="glintide-card-gallery"><strong>照片组（可选）</strong></label>
			<p class="description">右侧“特色图片”作为首图；需要多张照片时，每行填写一个图片地址，或使用媒体库选择。</p>
			<div class="glintide-card-media-actions">
				<button type="button" class="button" data-glintide-card-media-select>从媒体库选择照片</button>
			</div>
			<textarea id="glintide-card-gallery" name="glintide_card_gallery" rows="5" placeholder="https://example.com/photo-1.jpg&#10;https://example.com/photo-2.jpg"><?php echo esc_textarea( $gallery ); ?></textarea>
		</div>

		<div class="glintide-card-field-group<?php echo 'music' === $selected ? ' is-active' : ''; ?>" data-glintide-card-fields="music" aria-hidden="<?php echo 'music' === $selected ? 'false' : 'true'; ?>">
			<div class="glintide-card-field-row">
				<label for="glintide-card-music-artist"><strong>音乐人 / 作者</strong></label>
				<input type="text" id="glintide-card-music-artist" name="glintide_card_music_artist" value="<?php echo esc_attr( $artist ); ?>" placeholder="例如：坂本龙一">
			</div>
			<div class="glintide-card-field-row">
				<label for="glintide-card-music-url"><strong>音频地址</strong></label>
				<input type="url" id="glintide-card-music-url" name="glintide_card_music_url" value="<?php echo esc_attr( $music_url ); ?>" placeholder="https://example.com/track.mp3">
				<p class="description">建议使用 mp3、m4a、ogg 或 wav 文件地址。</p>
			</div>
		</div>

		<div class="glintide-card-field-group<?php echo 'video' === $selected ? ' is-active' : ''; ?>" data-glintide-card-fields="video" aria-hidden="<?php echo 'video' === $selected ? 'false' : 'true'; ?>">
			<label for="glintide-card-video-url"><strong>视频地址</strong></label>
			<input type="url" id="glintide-card-video-url" name="glintide_card_video_url" value="<?php echo esc_attr( $video_url ); ?>" placeholder="https://example.com/video.mp4 或 YouTube / Vimeo 链接">
			<p class="description">支持 mp4、webm、ogg 直链，以及 YouTube、Vimeo 链接。</p>
		</div>

		<div class="glintide-card-field-group<?php echo 'link' === $selected ? ' is-active' : ''; ?>" data-glintide-card-fields="link" aria-hidden="<?php echo 'link' === $selected ? 'false' : 'true'; ?>">
			<div class="glintide-card-field-row">
				<label for="glintide-card-link-url"><strong>链接地址</strong></label>
				<input type="url" id="glintide-card-link-url" name="glintide_card_link_url" value="<?php echo esc_attr( $link_url ); ?>" placeholder="https://example.com/article">
			</div>
			<div class="glintide-card-field-row">
				<label for="glintide-card-link-label"><strong>链接说明（可选）</strong></label>
				<input type="text" id="glintide-card-link-label" name="glintide_card_link_label" value="<?php echo esc_attr( $link_label ); ?>" placeholder="例如：阅读完整文章">
			</div>
		</div>

		<div class="glintide-card-field-group<?php echo 'quote' === $selected ? ' is-active' : ''; ?>" data-glintide-card-fields="quote" aria-hidden="<?php echo 'quote' === $selected ? 'false' : 'true'; ?>">
			<label for="glintide-card-quote-source"><strong>引用来源（可选）</strong></label>
			<input type="text" id="glintide-card-quote-source" name="glintide_card_quote_source" value="<?php echo esc_attr( $quote_source ); ?>" placeholder="例如：村上春树《挪威的森林》">
			<p class="description">引用内容直接填写上方正文编辑器，卡片会自动使用独立的引用样式。</p>
		</div>

		<div class="glintide-card-field-group<?php echo 'code' === $selected ? ' is-active' : ''; ?>" data-glintide-card-fields="code" aria-hidden="<?php echo 'code' === $selected ? 'false' : 'true'; ?>">
			<label for="glintide-card-code-language"><strong>代码语言（可选）</strong></label>
			<input type="text" id="glintide-card-code-language" name="glintide_card_code_language" value="<?php echo esc_attr( $code_language ); ?>" placeholder="例如：PHP、JavaScript、CSS">
			<p class="description">代码内容直接填写上方正文编辑器，建议切换到代码编辑模式以保留缩进。</p>
		</div>
	</div>
	<?php
}

/**
 * 更新单个内容卡片元数据。
 *
 * @param int    $post_id 文章 ID。
 * @param string $key     元数据键。
 * @param string $value   元数据值。
 */
function glintide_update_content_card_meta( $post_id, $key, $value ) {
	if ( '' === $value ) {
		delete_post_meta( $post_id, $key );
		return;
	}

	update_post_meta( $post_id, $key, $value );
}

/**
 * 保存内容卡片字段。
 *
 * @param int     $post_id 文章 ID。
 * @param WP_Post $post    文章对象。
 */
function glintide_save_content_card_meta( $post_id, $post ) {
	if ( ! isset( $_POST['glintide_content_card_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( wp_unslash( $_POST['glintide_content_card_nonce'] ), 'glintide_save_content_card' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || ( $post && 'glintide_card' !== $post->post_type ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$type_options = glintide_card_type_options();
	$type         = isset( $_POST['glintide_card_type'] ) ? sanitize_key( wp_unslash( $_POST['glintide_card_type'] ) ) : 'text';
	$type         = isset( $type_options[ $type ] ) ? $type : 'text';
	update_post_meta( $post_id, '_glintide_card_type', $type );

	$artist    = isset( $_POST['glintide_card_music_artist'] ) ? sanitize_text_field( wp_unslash( $_POST['glintide_card_music_artist'] ) ) : '';
	$music_url = isset( $_POST['glintide_card_music_url'] ) ? esc_url_raw( wp_unslash( $_POST['glintide_card_music_url'] ) ) : '';
	$video_url = isset( $_POST['glintide_card_video_url'] ) ? esc_url_raw( wp_unslash( $_POST['glintide_card_video_url'] ) ) : '';
	$link_url      = isset( $_POST['glintide_card_link_url'] ) ? esc_url_raw( wp_unslash( $_POST['glintide_card_link_url'] ) ) : '';
	$link_label    = isset( $_POST['glintide_card_link_label'] ) ? sanitize_text_field( wp_unslash( $_POST['glintide_card_link_label'] ) ) : '';
	$quote_source  = isset( $_POST['glintide_card_quote_source'] ) ? sanitize_text_field( wp_unslash( $_POST['glintide_card_quote_source'] ) ) : '';
	$code_language = isset( $_POST['glintide_card_code_language'] ) ? sanitize_text_field( wp_unslash( $_POST['glintide_card_code_language'] ) ) : '';

	glintide_update_content_card_meta( $post_id, '_glintide_card_music_artist', $artist );
	glintide_update_content_card_meta( $post_id, '_glintide_card_music_url', $music_url );
	glintide_update_content_card_meta( $post_id, '_glintide_card_video_url', $video_url );
	glintide_update_content_card_meta( $post_id, '_glintide_card_link_url', $link_url );
	glintide_update_content_card_meta( $post_id, '_glintide_card_link_label', $link_label );
	glintide_update_content_card_meta( $post_id, '_glintide_card_quote_source', $quote_source );
	glintide_update_content_card_meta( $post_id, '_glintide_card_code_language', $code_language );

	$gallery_raw   = isset( $_POST['glintide_card_gallery'] ) ? (string) wp_unslash( $_POST['glintide_card_gallery'] ) : '';
	$gallery_urls  = array();
	$gallery_lines = preg_split( '/\r\n|\r|\n/', $gallery_raw );

	foreach ( $gallery_lines as $gallery_line ) {
		$gallery_url = esc_url_raw( trim( $gallery_line ) );
		if ( $gallery_url && ! in_array( $gallery_url, $gallery_urls, true ) ) {
			$gallery_urls[] = $gallery_url;
		}
	}

	glintide_update_content_card_meta( $post_id, '_glintide_card_gallery', implode( "\n", $gallery_urls ) );
}
add_action( 'save_post_glintide_card', 'glintide_save_content_card_meta', 10, 2 );

/**
 * 加载内容卡片后台资源。
 *
 * @param string $hook_suffix 当前后台页面。
 */
function glintide_content_card_admin_assets( $hook_suffix ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

	if ( ! $screen || 'glintide_card' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style(
		'glintide-card-admin',
		GLINTIDE_URL . '/assets/css/glintide-card-admin.css',
		array(),
		filemtime( GLINTIDE_DIR . '/assets/css/glintide-card-admin.css' )
	);
	wp_enqueue_script(
		'glintide-card-admin',
		GLINTIDE_URL . '/assets/js/glintide-card-admin.js',
		array( 'jquery' ),
		filemtime( GLINTIDE_DIR . '/assets/js/glintide-card-admin.js' ),
		true
	);
}
add_action( 'admin_enqueue_scripts', 'glintide_content_card_admin_assets' );

/**
 * 后台列表添加卡片类型列。
 *
 * @param array $columns 原有列。
 * @return array
 */
function glintide_content_card_admin_columns( $columns ) {
	$new_columns = array();

	foreach ( $columns as $column_key => $column_label ) {
		$new_columns[ $column_key ] = $column_label;
		if ( 'title' === $column_key ) {
			$new_columns['glintide_card_type'] = '类型';
		}
	}

	return $new_columns;
}
add_filter( 'manage_glintide_card_posts_columns', 'glintide_content_card_admin_columns' );

/**
 * 输出后台列表的卡片类型。
 *
 * @param string $column  列名。
 * @param int    $post_id 文章 ID。
 */
function glintide_content_card_admin_column( $column, $post_id ) {
	if ( 'glintide_card_type' !== $column ) {
		return;
	}

	$type_data = glintide_card_type_options();
	$type      = glintide_card_get_type( $post_id );
	$label     = isset( $type_data[ $type ]['label'] ) ? $type_data[ $type ]['label'] : '文字';

	echo '<span class="glintide-admin-card-type glintide-admin-card-type--' . esc_attr( $type ) . '">' . esc_html( $label ) . '</span>';
}
add_action( 'manage_glintide_card_posts_custom_column', 'glintide_content_card_admin_column', 10, 2 );

/**
 * 获取内容卡片类型。
 *
 * 普通 WordPress 文章作为兼容内容时默认为文字卡片。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_type( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$type    = sanitize_key( (string) get_post_meta( $post_id, '_glintide_card_type', true ) );
	$options = glintide_card_type_options();

	return isset( $options[ $type ] ) ? $type : 'text';
}

/**
 * 获取卡片类型配置。
 *
 * @param int $post_id 文章 ID。
 * @return array
 */
function glintide_card_get_type_data( $post_id = 0 ) {
	$type    = glintide_card_get_type( $post_id );
	$options = glintide_card_type_options();

	return isset( $options[ $type ] ) ? $options[ $type ] : $options['text'];
}

/**
 * 获取照片地址集合。
 *
 * @param int $post_id 文章 ID。
 * @return array
 */
function glintide_card_get_image_urls( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$images  = array();

	$featured = get_the_post_thumbnail_url( $post_id, 'large' );
	if ( $featured ) {
		$images[] = esc_url_raw( $featured );
	}

	$gallery = get_post_meta( $post_id, '_glintide_card_gallery', true );
	$gallery = is_array( $gallery ) ? $gallery : preg_split( '/\r\n|\r|\n/', (string) $gallery );

	foreach ( $gallery as $image ) {
		if ( is_numeric( $image ) ) {
			$image = wp_get_attachment_image_url( absint( $image ), 'large' );
		}

		$image = esc_url_raw( trim( (string) $image ) );
		if ( $image && ! in_array( $image, $images, true ) ) {
			$images[] = $image;
		}
	}

	if ( empty( $images ) && function_exists( 'glintide_get_thumb' ) ) {
		$legacy_thumb = glintide_get_thumb( $post_id );
		if ( $legacy_thumb ) {
			$images[] = esc_url_raw( $legacy_thumb );
		}
	}

	return array_values( array_filter( $images ) );
}

/**
 * 获取卡片正文摘要。
 *
 * @param int $post_id 文章 ID。
 * @param int $words   字数。
 * @return string
 */
function glintide_card_get_summary( $post_id = 0, $words = 36 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$excerpt = (string) get_post_field( 'post_excerpt', $post_id );
	$content = $excerpt ? $excerpt : glintide_card_get_plain_content( $post_id );
	$content = trim( (string) preg_replace( '/\s+/', ' ', wp_strip_all_tags( $content ) ) );

	return $content ? wp_trim_words( $content, max( 1, absint( $words ) ), '…' ) : '';
}

/**
 * 获取音乐地址。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_music_url( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	return esc_url_raw( (string) get_post_meta( $post_id, '_glintide_card_music_url', true ) );
}

/**
 * 获取音乐人名称。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_music_artist( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	return sanitize_text_field( (string) get_post_meta( $post_id, '_glintide_card_music_artist', true ) );
}

/**
 * 获取视频地址。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_video_url( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	return esc_url_raw( (string) get_post_meta( $post_id, '_glintide_card_video_url', true ) );
}

/**
 * 获取链接地址。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_link_url( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	return esc_url_raw( (string) get_post_meta( $post_id, '_glintide_card_link_url', true ) );
}

/**
 * 获取链接说明。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_link_label( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	return sanitize_text_field( (string) get_post_meta( $post_id, '_glintide_card_link_label', true ) );
}

/**
 * 获取引用来源。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_quote_source( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	return sanitize_text_field( (string) get_post_meta( $post_id, '_glintide_card_quote_source', true ) );
}

/**
 * 获取代码语言。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_code_language( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	return sanitize_text_field( (string) get_post_meta( $post_id, '_glintide_card_code_language', true ) );
}

/**
 * 获取正文的纯文本版本。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_plain_content( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$content = (string) get_post_field( 'post_content', $post_id );
	$content = strip_shortcodes( $content );
	$content = wp_strip_all_tags( $content );

	return trim( (string) preg_replace( '/\s+/', ' ', $content ) );
}

/**
 * 获取代码正文，保留换行与缩进。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_code_content( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$content = (string) get_post_field( 'post_content', $post_id );
	$content = strip_shortcodes( $content );
	$content = preg_replace( '/<br\s*\/?>/i', "\n", $content );
	$content = preg_replace( '/<\/p>\s*<p[^>]*>/i', "\n", $content );
	$content = wp_strip_all_tags( $content );
	$charset = get_bloginfo( 'charset' ) ? get_bloginfo( 'charset' ) : 'UTF-8';

	return trim( html_entity_decode( (string) $content, ENT_QUOTES, $charset ) );
}

/**
 * 获取音频 MIME 类型。
 *
 * @param string $url 音频地址。
 * @return string
 */
function glintide_card_get_audio_mime( $url ) {
	$path = wp_parse_url( $url, PHP_URL_PATH );
	$ext  = strtolower( pathinfo( (string) $path, PATHINFO_EXTENSION ) );
	$mime = array(
		'mp3'  => 'audio/mpeg',
		'm4a'  => 'audio/mp4',
		'mp4'  => 'audio/mp4',
		'ogg'  => 'audio/ogg',
		'wav'  => 'audio/wav',
		'webm' => 'audio/webm',
	);

	return isset( $mime[ $ext ] ) ? $mime[ $ext ] : '';
}

/**
 * 获取视频 MIME 类型。
 *
 * @param string $url 视频地址。
 * @return string
 */
function glintide_card_get_video_mime( $url ) {
	$path = wp_parse_url( $url, PHP_URL_PATH );
	$ext  = strtolower( pathinfo( (string) $path, PATHINFO_EXTENSION ) );
	$mime = array(
		'mp4'  => 'video/mp4',
		'm4v'  => 'video/mp4',
		'webm' => 'video/webm',
		'ogv'  => 'video/ogg',
		'ogg'  => 'video/ogg',
	);

	return isset( $mime[ $ext ] ) ? $mime[ $ext ] : '';
}

/**
 * 判断是否为浏览器可直接播放的视频地址。
 *
 * @param string $url 视频地址。
 * @return bool
 */
function glintide_card_is_direct_video_url( $url ) {
	$path = wp_parse_url( $url, PHP_URL_PATH );
	$ext  = strtolower( pathinfo( (string) $path, PATHINFO_EXTENSION ) );

	return in_array( $ext, array( 'mp4', 'm4v', 'webm', 'ogv', 'ogg' ), true );
}

/**
 * 将 YouTube / Vimeo 地址转换为安全的嵌入地址。
 *
 * @param string $url 视频地址。
 * @return string
 */
function glintide_card_get_video_embed_url( $url ) {
	$parts = wp_parse_url( $url );
	$host  = isset( $parts['host'] ) ? strtolower( preg_replace( '/^www\./', '', $parts['host'] ) ) : '';
	$path  = isset( $parts['path'] ) ? trim( $parts['path'], '/' ) : '';

	if ( in_array( $host, array( 'youtube.com', 'm.youtube.com', 'youtu.be' ), true ) ) {
		$video_id = '';
		if ( 'youtu.be' === $host ) {
			$video_id = strtok( $path, '/' );
		} elseif ( preg_match( '#(?:embed|shorts|live)/([^/?]+)#', $path, $matches ) ) {
			$video_id = $matches[1];
		} elseif ( ! empty( $parts['query'] ) ) {
			parse_str( $parts['query'], $query_args );
			$video_id = isset( $query_args['v'] ) ? $query_args['v'] : '';
		}

		if ( $video_id && preg_match( '/^[A-Za-z0-9_-]+$/', $video_id ) ) {
			return 'https://www.youtube.com/embed/' . $video_id;
		}
	}

	if ( 'vimeo.com' === $host || 'player.vimeo.com' === $host ) {
		if ( preg_match( '/(?:video\/)?([0-9]+)/', $path, $matches ) ) {
			return 'https://player.vimeo.com/video/' . $matches[1];
		}
	}

	return '';
}

/**
 * 输出卡片媒体内容。
 *
 * @param int    $post_id 文章 ID。
 * @param string $context card 或 single。
 * @return string
 */
function glintide_card_media_html( $post_id = 0, $context = 'card' ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$context = 'single' === $context ? 'single' : 'card';
	$type    = glintide_card_get_type( $post_id );
	$data    = glintide_card_get_type_data( $post_id );
	$title   = get_the_title( $post_id ) ? get_the_title( $post_id ) : $data['label'];
	$images  = glintide_card_get_image_urls( $post_id );
	$base    = 'glintide-card-media glintide-card-media--' . $type;

	if ( 'text' === $type ) {
		return '<div class="' . esc_attr( $base ) . ' glintide-card-media--text-mark" aria-hidden="true"><i class="' . esc_attr( $data['icon'] ) . '"></i></div>';
	}

	if ( 'photo' === $type ) {
		if ( empty( $images ) ) {
			return '<div class="' . esc_attr( $base ) . ' glintide-card-media--empty"><i class="ri-image-line" aria-hidden="true"></i><span>暂未添加照片</span></div>';
		}

		$visible_count = min( 4, count( $images ) );
		$html          = '<div class="' . esc_attr( $base ) . '"><div class="glintide-card-photo-grid glintide-card-photo-grid--' . absint( $visible_count ) . '">';
		$permalink     = get_permalink( $post_id );

		foreach ( array_slice( $images, 0, 4 ) as $index => $image ) {
			$image_href = 'single' === $context ? $image : $permalink;
			$target     = 'single' === $context ? ' target="_blank" rel="noopener"' : '';
			$html      .= '<a class="glintide-card-photo-link" href="' . esc_url( $image_href ) . '"' . $target . ' aria-label="' . esc_attr( '查看' . $title ) . '">';
			$html      .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $title ) . '" width="640" height="480" loading="lazy" decoding="async">';
			if ( 3 === $index && count( $images ) > 4 ) {
				$html .= '<span class="glintide-card-photo-more">+' . absint( count( $images ) - 4 ) . '</span>';
			}
			$html .= '</a>';
		}

		$html .= '</div></div>';
		return $html;
	}

	if ( 'music' === $type ) {
		$music_url = glintide_card_get_music_url( $post_id );
		$artist    = glintide_card_get_music_artist( $post_id );
		$cover     = isset( $images[0] ) ? $images[0] : '';
		$html      = '<div class="' . esc_attr( $base ) . '"><div class="glintide-card-music-cover">';

		if ( $cover ) {
			$html .= '<img src="' . esc_url( $cover ) . '" alt="' . esc_attr( $title ) . '" width="160" height="160" loading="lazy" decoding="async">';
		} else {
			$html .= '<i class="ri-music-2-line" aria-hidden="true"></i>';
		}

		$html .= '</div><div class="glintide-card-music-content">';
		if ( $artist ) {
			$html .= '<span class="glintide-card-music-artist">' . esc_html( $artist ) . '</span>';
		}

		if ( $music_url ) {
			$mime  = glintide_card_get_audio_mime( $music_url );
			$html .= '<audio class="glintide-card-audio" controls preload="none"><source src="' . esc_url( $music_url ) . '"' . ( $mime ? ' type="' . esc_attr( $mime ) . '"' : '' ) . '><span>当前浏览器不支持音频播放。</span></audio>';
		} else {
			$html .= '<span class="glintide-card-media-note">音频地址未设置</span>';
		}

		$html .= '</div></div>';
		return $html;
	}

	if ( 'link' === $type ) {
		$link_url   = glintide_card_get_link_url( $post_id );
		$link_label = glintide_card_get_link_label( $post_id );
		$link_host  = $link_url ? wp_parse_url( $link_url, PHP_URL_HOST ) : '';
		$link_host  = $link_host ? preg_replace( '/^www\./', '', $link_host ) : '';

		if ( ! $link_url ) {
			return '<div class="' . esc_attr( $base ) . ' glintide-card-media--empty"><i class="ri-links-line" aria-hidden="true"></i><span>链接地址未设置</span></div>';
		}

		$link_label = $link_label ? $link_label : '打开链接';
		$link_host  = $link_host ? $link_host : '外部资源';

		return '<div class="' . esc_attr( $base ) . '"><a class="glintide-card-link-panel" href="' . esc_url( $link_url ) . '" target="_blank" rel="noopener noreferrer"><span class="glintide-card-link-icon"><i class="ri-links-line" aria-hidden="true"></i></span><span class="glintide-card-link-content"><strong>' . esc_html( $link_label ) . '</strong><small>' . esc_html( $link_host ) . '</small></span><i class="ri-arrow-right-up-line glintide-card-link-arrow" aria-hidden="true"></i></a></div>';
	}

	if ( 'quote' === $type ) {
		$quote  = glintide_card_get_plain_content( $post_id );
		$source = glintide_card_get_quote_source( $post_id );

		if ( ! $quote ) {
			$quote = '还没有添加引用内容。';
		} elseif ( 'card' === $context ) {
			$quote = wp_trim_words( $quote, 60, '…' );
		}

		$html = '<div class="' . esc_attr( $base ) . '"><blockquote class="glintide-card-quote-block"><span class="glintide-card-quote-mark" aria-hidden="true">“</span><p>' . nl2br( esc_html( $quote ) ) . '</p>';
		if ( $source ) {
			$html .= '<cite>— ' . esc_html( $source ) . '</cite>';
		}
		$html .= '</blockquote></div>';

		return $html;
	}

	if ( 'code' === $type ) {
		$code      = glintide_card_get_code_content( $post_id );
		$language  = glintide_card_get_code_language( $post_id );
		$language  = $language ? $language : 'CODE';
		$lines     = $code ? substr_count( $code, "\n" ) + 1 : 0;
		$line_text = $lines ? $lines . ' 行' : '空代码片段';

		$html = '<div class="' . esc_attr( $base ) . '"><div class="glintide-card-code-head"><span>' . esc_html( strtoupper( $language ) ) . '</span><span>' . esc_html( $line_text ) . '</span></div><pre class="glintide-card-code-block"><code>';
		$html .= $code ? esc_html( $code ) : '<span class="glintide-card-code-empty">还没有添加代码。</span>';
		$html .= '</code></pre></div>';

		return $html;
	}

	if ( 'video' === $type ) {
		$video_url = glintide_card_get_video_url( $post_id );
		$poster    = isset( $images[0] ) ? $images[0] : '';

		if ( ! $video_url ) {
			return '<div class="' . esc_attr( $base ) . ' glintide-card-media--empty"><i class="ri-video-line" aria-hidden="true"></i><span>视频地址未设置</span></div>';
		}

		if ( glintide_card_is_direct_video_url( $video_url ) ) {
			$mime = glintide_card_get_video_mime( $video_url );
			$html = '<div class="' . esc_attr( $base ) . '"><video class="glintide-card-video" controls preload="metadata"' . ( $poster ? ' poster="' . esc_url( $poster ) . '"' : '' ) . '><source src="' . esc_url( $video_url ) . '"' . ( $mime ? ' type="' . esc_attr( $mime ) . '"' : '' ) . '><span>当前浏览器不支持视频播放。</span></video></div>';
			return $html;
		}

		$embed_url = glintide_card_get_video_embed_url( $video_url );
		if ( $embed_url ) {
			return '<div class="' . esc_attr( $base ) . '"><iframe class="glintide-card-video-embed" src="' . esc_url( $embed_url ) . '" title="' . esc_attr( $title ) . '" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>';
		}

		return '<div class="' . esc_attr( $base ) . ' glintide-card-media--external"><a href="' . esc_url( $video_url ) . '" target="_blank" rel="noopener noreferrer"><i class="ri-external-link-line" aria-hidden="true"></i><span>打开视频链接</span></a></div>';
	}

	return '';
}

/**
 * 获取首页内容卡片查询。
 *
 * @param int $paged 当前页码。
 * @return WP_Query
 */
function glintide_get_card_feed_query( $paged = 1 ) {
	$per_page = (int) apply_filters( 'glintide_card_feed_per_page', 12 );

	return new WP_Query(
		array(
			'post_type'           => array( 'glintide_card', 'post' ),
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, $per_page ),
			'paged'               => max( 1, absint( $paged ) ),
			'ignore_sticky_posts' => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
		)
	);
}

/**
 * 输出首页内容卡片分页。
 *
 * @param WP_Query $query  内容卡片查询。
 * @param int       $paged 当前页码。
 */
function glintide_card_feed_pagination( $query, $paged = 1 ) {
	if ( ! $query instanceof WP_Query || $query->max_num_pages < 2 ) {
		return;
	}

	$big   = 999999999;
	$base  = str_replace( (string) $big, '%#%', esc_url( get_pagenum_link( $big ) ) );
	$links = paginate_links(
		array(
			'base'      => $base,
			'format'    => '?paged=%#%',
			'current'   => max( 1, absint( $paged ) ),
			'total'     => $query->max_num_pages,
			'mid_size'  => 1,
			'end_size'  => 1,
			'prev_text' => '<i class="ri-arrow-left-line" aria-hidden="true"></i><span class="screen-reader-text">上一页</span>',
			'next_text' => '<i class="ri-arrow-right-line" aria-hidden="true"></i><span class="screen-reader-text">下一页</span>',
			'type'      => 'list',
		)
	);

	if ( $links ) {
		echo '<nav class="glintide-card-pagination" aria-label="内容卡片分页">' . wp_kses_post( $links ) . '</nav>';
	}
}
