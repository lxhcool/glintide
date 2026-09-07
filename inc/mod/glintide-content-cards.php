<?php
/**
 * Glintide 内容卡片
 *
 * 为普通文章提供内容类型字段和前台多媒体渲染能力。
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
			'label'       => '文章',
			'icon'        => 'ri-file-text-line',
			'description' => '发布一篇图文文章或随笔',
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
	);
}

/**
 * 首次运行时创建一组可直接预览的文章内容。
 *
 * 每种类型只补齐缺少的内容，不会覆盖已有卡片；使用固定标记避免重复创建。
 */
function glintide_seed_content_cards() {
	$seed_version = 'glintide-card-content-pack-v4';

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
				'_glintide_card_gallery' => GLINTIDE_URL . '/assets/images/banner.jpg' . "\n" . GLINTIDE_URL . '/assets/images/banner.jpg?v=2' . "\n" . GLINTIDE_URL . '/assets/images/banner.jpg?v=3',
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
		$type_values = array( $type );
		$existing    = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => '_glintide_card_type',
						'value'   => $type_values,
						'compare' => 'IN',
					),
				),
				'fields'         => 'ids',
			)
		);

		if ( ! empty( $existing ) ) {
			// 版本升级时,修复内容与最新模板不一致的种子卡片(一次性,修复后写入新标记)
			$existing_id  = (int) $existing[0];
			$existing_seed = get_post_meta( $existing_id, '_glintide_card_seed', true );
			if ( $existing_seed !== $seed_version
				&& get_post_field( 'post_content', $existing_id ) !== $content['content'] ) {
				wp_update_post(
					array(
						'ID'           => $existing_id,
						'post_content' => $content['content'],
					)
				);
			}
			if ( $existing_seed !== $seed_version ) {
				// 更新演示文章的内容类型和媒体字段
				update_post_meta( $existing_id, '_glintide_card_type', $type );
				foreach ( $content['meta'] as $meta_key => $meta_value ) {
					if ( '_glintide_card_type' === $meta_key ) {
						continue;
					}
					update_post_meta( $existing_id, $meta_key, $meta_value );
				}
				update_post_meta( $existing_id, '_glintide_card_seed', $seed_version );
			}
			continue;
		}

		$post_args = array(
			'post_title'   => $content['title'],
			'post_content' => $content['content'],
			'post_status'  => 'publish',
			'post_type'    => 'post',
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
		'内容类型与媒体设置',
		'glintide_render_content_card_meta_box',
		'post',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_post', 'glintide_add_content_card_meta_boxes' );

/**
 * 在文章标题上方输出内容类型选择器。
 *
 * @param WP_Post $post 当前文章对象。
 */
function glintide_render_content_card_type_above_title( $post ) {
	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
		return;
	}

	$type_options = glintide_card_type_options();
	$selected     = glintide_card_get_type( $post->ID );
	?>
	<div class="glintide-card-type-bar" data-glintide-card-editor>
		<fieldset class="glintide-card-type-fieldset">
			<div class="glintide-card-type-options">
				<?php foreach ( $type_options as $type => $type_data ) : ?>
					<label class="glintide-card-type-option<?php echo $selected === $type ? ' is-selected' : ''; ?>">
						<input type="radio" name="glintide_card_type" value="<?php echo esc_attr( $type ); ?>" <?php checked( $selected, $type ); ?>>
						<span class="glintide-card-type-option-title"><i class="<?php echo esc_attr( $type_data['icon'] ); ?>" aria-hidden="true"></i><?php echo esc_html( $type_data['label'] ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</fieldset>
	</div>
	<?php
}
add_action( 'edit_form_top', 'glintide_render_content_card_type_above_title' );

/**
 * 输出内容卡片设置面板。
 *
 * @param WP_Post $post 当前文章对象。
 */
function glintide_render_content_card_meta_box( $post ) {
	$selected     = glintide_card_get_type( $post->ID );
	$artist       = get_post_meta( $post->ID, '_glintide_card_music_artist', true );
	$music_title  = get_post_meta( $post->ID, '_glintide_card_music_title', true );
	$music_cover  = get_post_meta( $post->ID, '_glintide_card_music_cover', true );
	$article_cover = get_post_meta( $post->ID, '_glintide_card_article_cover', true );
	$article_cover_preview_url = $article_cover ? ( is_numeric( $article_cover ) ? wp_get_attachment_image_url( absint( $article_cover ), 'thumbnail' ) : esc_url( $article_cover ) ) : '';
	$music_url    = get_post_meta( $post->ID, '_glintide_card_music_url', true );
	$music_source = get_post_meta( $post->ID, '_glintide_card_music_source', true );
	$music_source = in_array( $music_source, array( 'remote', 'upload' ), true ) ? $music_source : ( false !== stripos( (string) $music_url, 'music.163.com' ) ? 'remote' : 'upload' );
	$video_url    = get_post_meta( $post->ID, '_glintide_card_video_url', true );
	$video_source = get_post_meta( $post->ID, '_glintide_card_video_source', true );
	$video_source = in_array( $video_source, array( 'bilibili', 'youtube', 'upload' ), true ) ? $video_source : ( glintide_card_is_direct_video_url( $video_url ) ? 'upload' : 'youtube' );
	$link_url      = get_post_meta( $post->ID, '_glintide_card_link_url', true );
	$link_label    = get_post_meta( $post->ID, '_glintide_card_link_label', true );
	$gallery       = get_post_meta( $post->ID, '_glintide_card_gallery', true );
	$gallery       = is_array( $gallery ) ? implode( "\n", $gallery ) : (string) $gallery;
	$gallery_active = 'photo' === $selected;

	wp_nonce_field( 'glintide_save_content_card', 'glintide_content_card_nonce' );
	?>
	<div class="glintide-card-editor">
		<div class="glintide-card-field-group<?php echo 'text' === $selected ? ' is-active' : ''; ?>" data-glintide-card-fields="text" aria-hidden="<?php echo 'text' === $selected ? 'false' : 'true'; ?>">
			<div class="glintide-card-field-row glintide-card-field-row--cover">
				<label><strong>文章封面（可选）</strong></label>
				<div class="glintide-card-cover-picker" data-glintide-cover>
					<div class="glintide-card-cover-preview">
						<?php if ( $article_cover_preview_url ) : ?>
							<img src="<?php echo esc_url( $article_cover_preview_url ); ?>" alt="">
						<?php else : ?>
							<span class="glintide-card-cover-empty"><i class="ri-image-line" aria-hidden="true"></i></span>
						<?php endif; ?>
					</div>
					<p class="glintide-card-cover-actions">
						<button type="button" class="button glintide-card-cover-add"><?php echo $article_cover_preview_url ? '更换封面' : '选择封面'; ?></button>
						<button type="button" class="button glintide-card-cover-clear"<?php echo $article_cover_preview_url ? '' : ' style="display:none"'; ?>>清除</button>
					</p>
					<p class="description">不设置时使用特色图片、正文图片或主题默认图。</p>
					<input type="hidden" class="glintide-card-cover-input" name="glintide_card_article_cover" value="<?php echo esc_attr( $article_cover ); ?>">
				</div>
			</div>
		</div>

		<div class="glintide-card-field-group<?php echo $gallery_active ? ' is-active' : ''; ?>" data-glintide-card-fields="photo" aria-hidden="<?php echo $gallery_active ? 'false' : 'true'; ?>">
			<label><strong>照片组（可多选）</strong></label>
			<p class="description">照片卡片最多显示 9 张，也可以继续使用右侧“特色图片”。</p>
			<div class="glintide-card-gallery" data-glintide-card-gallery>
				<ul class="glintide-card-gallery-list">
					<?php
					$seed_ids = array_filter( array_map( 'trim', preg_split( '/[\r\n,]+/', (string) $gallery ) ) );
					foreach ( $seed_ids as $seed_entry ) {
						if ( is_numeric( $seed_entry ) ) {
							$thumb = wp_get_attachment_image_src( (int) $seed_entry, 'thumbnail' );
							$thumb = $thumb ? $thumb[0] : '';
						} else {
							$thumb = $seed_entry;
						}
						if ( $thumb ) {
							echo '<li><img src="' . esc_url( $thumb ) . '" alt=""></li>';
						}
					}
					?>
				</ul>
				<p class="glintide-card-gallery-actions">
					<button type="button" class="button glintide-card-gallery-add"><?php echo empty( $gallery ) ? '添加图片' : '编辑图片'; ?></button>
					<button type="button" class="button glintide-card-gallery-clear"<?php echo empty( $gallery ) ? ' style="display:none"' : ''; ?>>清除</button>
				</p>
				<input type="hidden" class="glintide-card-gallery-input" name="glintide_card_gallery" value="<?php echo esc_attr( $gallery ); ?>">
			</div>
		</div>

		<div class="glintide-card-field-group<?php echo 'music' === $selected ? ' is-active' : ''; ?>" data-glintide-card-fields="music" aria-hidden="<?php echo 'music' === $selected ? 'false' : 'true'; ?>">
			<div class="glintide-card-music-source" data-glintide-music-source>
				<label><strong>音乐来源</strong></label>
				<div class="glintide-card-source-options">
					<label class="glintide-card-source-option<?php echo 'remote' === $music_source ? ' is-selected' : ''; ?>">
						<input type="radio" name="glintide_card_music_source" value="remote" <?php checked( $music_source, 'remote' ); ?>><span><i class="ri-link-m" aria-hidden="true"></i>音频地址</span>
					</label>
					<label class="glintide-card-source-option<?php echo 'upload' === $music_source ? ' is-selected' : ''; ?>">
						<input type="radio" name="glintide_card_music_source" value="upload" <?php checked( $music_source, 'upload' ); ?>><span><i class="ri-upload-2-line" aria-hidden="true"></i>自己上传</span>
					</label>
				</div>
			</div>
			<div class="glintide-card-field-row">
				<label for="glintide-card-music-url"><strong class="glintide-card-music-url-label">音频地址</strong></label>
				<div class="glintide-card-music-url-control">
					<input type="url" id="glintide-card-music-url" name="glintide_card_music_url" value="<?php echo esc_attr( $music_url ); ?>" placeholder="例如：https://music.163.com/#/song?id=287035">
					<button type="button" class="button glintide-card-music-resolve">自动获取信息</button>
					<button type="button" class="button glintide-card-music-upload">选择音频</button>
				</div>
				<p class="description glintide-card-music-status" aria-live="polite">音频地址会自动获取歌名、作者和封面。</p>
			</div>
			<div class="glintide-card-music-manual" data-glintide-music-manual<?php echo 'upload' === $music_source ? ' style="display:block"' : ''; ?>>
			<div class="glintide-card-field-row">
				<label for="glintide-card-music-title"><strong>歌名</strong></label>
				<input type="text" id="glintide-card-music-title" name="glintide_card_music_title" value="<?php echo esc_attr( $music_title ); ?>" placeholder="例如：夜航">
			</div>
			<div class="glintide-card-field-row">
				<label for="glintide-card-music-artist"><strong>音乐人 / 作者</strong></label>
				<input type="text" id="glintide-card-music-artist" name="glintide_card_music_artist" value="<?php echo esc_attr( $artist ); ?>" placeholder="例如：坂本龙一">
			</div>
			<div class="glintide-card-field-row glintide-card-field-row--cover">
				<label><strong>封面</strong></label>
				<div class="glintide-card-cover-picker" data-glintide-cover>
					<div class="glintide-card-cover-preview">
						<?php
						$cover_preview_url = '';
						if ( $music_cover ) {
							if ( is_numeric( $music_cover ) ) {
								$cover_preview_url = wp_get_attachment_image_url( absint( $music_cover ), 'thumbnail' );
							} else {
								$cover_preview_url = $music_cover;
							}
						}
						if ( $cover_preview_url ) {
							echo '<img src="' . esc_url( $cover_preview_url ) . '" alt="">';
						} else {
							echo '<span class="glintide-card-cover-empty"><i class="ri-image-line" aria-hidden="true"></i></span>';
						}
						?>
					</div>
					<p class="glintide-card-cover-actions">
						<button type="button" class="button glintide-card-cover-add"><?php echo $cover_preview_url ? '更换封面' : '选择封面'; ?></button>
						<button type="button" class="button glintide-card-cover-clear"<?php echo $cover_preview_url ? '' : ' style="display:none"'; ?>>清除</button>
					</p>
					<p class="description">不选择则使用文章特色图或图组第一张,都没有则使用默认封面。</p>
					<input type="hidden" class="glintide-card-cover-input" name="glintide_card_music_cover" value="<?php echo esc_attr( $music_cover ); ?>">
				</div>
			</div>
			</div>
		</div>

		<div class="glintide-card-field-group<?php echo 'video' === $selected ? ' is-active' : ''; ?>" data-glintide-card-fields="video" aria-hidden="<?php echo 'video' === $selected ? 'false' : 'true'; ?>">
			<div class="glintide-card-video-source" data-glintide-video-source>
				<label><strong>视频来源</strong></label>
				<div class="glintide-card-source-options">
					<?php foreach ( array( 'bilibili' => array( '哔哩哔哩', 'ri-live-line' ), 'youtube' => array( 'YouTube', 'ri-youtube-line' ), 'upload' => array( '自己上传', 'ri-upload-2-line' ) ) as $source_key => $source_data ) : ?>
						<label class="glintide-card-source-option<?php echo $video_source === $source_key ? ' is-selected' : ''; ?>">
							<input type="radio" name="glintide_card_video_source" value="<?php echo esc_attr( $source_key ); ?>" <?php checked( $video_source, $source_key ); ?>><span><i class="<?php echo esc_attr( $source_data[1] ); ?>" aria-hidden="true"></i><?php echo esc_html( $source_data[0] ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="glintide-card-field-row">
				<label for="glintide-card-video-url"><strong>视频地址</strong></label>
				<div class="glintide-card-video-url-control">
					<input type="url" id="glintide-card-video-url" name="glintide_card_video_url" value="<?php echo esc_attr( $video_url ); ?>" placeholder="<?php echo esc_attr( 'bilibili' === $video_source ? '例如：https://www.bilibili.com/video/BV1xx411c7mD/' : ( 'youtube' === $video_source ? '例如：https://www.youtube.com/watch?v=dQw4w9WgXcQ' : '选择媒体库中的视频文件' ) ); ?>">
					<button type="button" class="button glintide-card-video-upload">选择视频</button>
				</div>
				<p class="description glintide-card-video-status">支持哔哩哔哩和 YouTube 视频链接。</p>
			</div>
		</div>

		<div class="glintide-card-field-group<?php echo 'link' === $selected ? ' is-active' : ''; ?>" data-glintide-card-fields="link" aria-hidden="<?php echo 'link' === $selected ? 'false' : 'true'; ?>">
			<div class="glintide-card-field-row">
				<label for="glintide-card-link-url"><strong>链接地址</strong></label>
				<input type="url" id="glintide-card-link-url" name="glintide_card_link_url" value="<?php echo esc_attr( $link_url ); ?>" placeholder="例如：https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps">
			</div>
			<div class="glintide-card-field-row">
				<label for="glintide-card-link-label"><strong>链接说明（可选）</strong></label>
				<input type="text" id="glintide-card-link-label" name="glintide_card_link_label" value="<?php echo esc_attr( $link_label ); ?>" placeholder="例如：阅读完整文章">
			</div>
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

	if ( wp_is_post_revision( $post_id ) || ( $post && 'post' !== $post->post_type ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$type_options = glintide_card_type_options();
	$type         = isset( $_POST['glintide_card_type'] ) ? sanitize_key( wp_unslash( $_POST['glintide_card_type'] ) ) : 'text';
	$type         = isset( $type_options[ $type ] ) ? $type : 'text';
	update_post_meta( $post_id, '_glintide_card_type', $type );

	$artist       = isset( $_POST['glintide_card_music_artist'] ) ? sanitize_text_field( wp_unslash( $_POST['glintide_card_music_artist'] ) ) : '';
	$music_url    = isset( $_POST['glintide_card_music_url'] ) ? esc_url_raw( wp_unslash( $_POST['glintide_card_music_url'] ) ) : '';
	$music_source = isset( $_POST['glintide_card_music_source'] ) ? sanitize_key( wp_unslash( $_POST['glintide_card_music_source'] ) ) : 'upload';
	$music_source = in_array( $music_source, array( 'remote', 'upload' ), true ) ? $music_source : 'upload';
	$music_title  = isset( $_POST['glintide_card_music_title'] ) ? sanitize_text_field( wp_unslash( $_POST['glintide_card_music_title'] ) ) : '';
	$music_cover  = isset( $_POST['glintide_card_music_cover'] ) ? wp_unslash( $_POST['glintide_card_music_cover'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$music_cover  = is_numeric( $music_cover ) ? absint( $music_cover ) : esc_url_raw( $music_cover );
	$article_cover = isset( $_POST['glintide_card_article_cover'] ) ? wp_unslash( $_POST['glintide_card_article_cover'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$article_cover = is_numeric( $article_cover ) ? absint( $article_cover ) : esc_url_raw( $article_cover );
	$video_url = isset( $_POST['glintide_card_video_url'] ) ? esc_url_raw( wp_unslash( $_POST['glintide_card_video_url'] ) ) : '';
	$video_source = isset( $_POST['glintide_card_video_source'] ) ? sanitize_key( wp_unslash( $_POST['glintide_card_video_source'] ) ) : 'youtube';
	$video_source = in_array( $video_source, array( 'bilibili', 'youtube', 'upload' ), true ) ? $video_source : 'youtube';
	$link_url      = isset( $_POST['glintide_card_link_url'] ) ? esc_url_raw( wp_unslash( $_POST['glintide_card_link_url'] ) ) : '';
	$link_label    = isset( $_POST['glintide_card_link_label'] ) ? sanitize_text_field( wp_unslash( $_POST['glintide_card_link_label'] ) ) : '';

	glintide_update_content_card_meta( $post_id, '_glintide_card_music_artist', $artist );
	glintide_update_content_card_meta( $post_id, '_glintide_card_music_url', $music_url );
	glintide_update_content_card_meta( $post_id, '_glintide_card_music_source', $music_source );
	glintide_update_content_card_meta( $post_id, '_glintide_card_music_title', $music_title );
	glintide_update_content_card_meta( $post_id, '_glintide_card_music_cover', $music_cover );
	glintide_update_content_card_meta( $post_id, '_glintide_card_article_cover', $article_cover );
	glintide_update_content_card_meta( $post_id, '_glintide_card_video_url', $video_url );
	glintide_update_content_card_meta( $post_id, '_glintide_card_video_source', $video_source );
	glintide_update_content_card_meta( $post_id, '_glintide_card_link_url', $link_url );
	glintide_update_content_card_meta( $post_id, '_glintide_card_link_label', $link_label );

	$gallery_raw = isset( $_POST['glintide_card_gallery'] ) ? (string) wp_unslash( $_POST['glintide_card_gallery'] ) : '';
	$gallery_raw = trim( preg_replace( '/\s+/', ' ', $gallery_raw ) );

	$gallery_ids = $gallery_raw ? array_filter( array_map( 'trim', explode( ',', $gallery_raw ) ), 'strlen' ) : array();
	if ( count( $gallery_ids ) > 9 ) {
		$gallery_ids = array_slice( $gallery_ids, 0, 9 );
	}
	$gallery_raw = implode( ',', $gallery_ids );

	glintide_update_content_card_meta( $post_id, '_glintide_card_gallery', $gallery_raw );
}
add_action( 'save_post_post', 'glintide_save_content_card_meta', 10, 2 );

/**
 * 加载内容卡片后台资源。
 *
 * @param string $hook_suffix 当前后台页面。
 */
function glintide_content_card_admin_assets( $hook_suffix ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

	if ( ! $screen || 'post' !== $screen->post_type ) {
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
	wp_localize_script(
		'glintide-card-admin',
		'glintideMusicAdmin',
		array(
			'restUrl' => rest_url( 'glintide/v1/netease-song' ),
		)
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
add_filter( 'manage_post_posts_columns', 'glintide_content_card_admin_columns' );

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
	$label     = isset( $type_data[ $type ]['label'] ) ? $type_data[ $type ]['label'] : '文章';

	echo '<span class="glintide-admin-card-type glintide-admin-card-type--' . esc_attr( $type ) . '">' . esc_html( $label ) . '</span>';
}
add_action( 'manage_post_posts_custom_column', 'glintide_content_card_admin_column', 10, 2 );

/**
 * 在文章列表提供内容类型筛选。
 *
 * @param string $post_type 当前文章类型。
 */
function glintide_content_card_admin_type_filter( $post_type ) {
	if ( 'post' !== $post_type ) {
		return;
	}

	$selected = isset( $_GET['glintide_card_type'] ) ? sanitize_key( wp_unslash( $_GET['glintide_card_type'] ) ) : '';
	$options  = glintide_card_type_options();

	echo '<select name="glintide_card_type"><option value="">全部内容类型</option>';
	foreach ( $options as $type => $data ) {
		echo '<option value="' . esc_attr( $type ) . '"' . selected( $selected, $type, false ) . '>' . esc_html( $data['label'] ) . '</option>';
	}
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'glintide_content_card_admin_type_filter' );

/**
 * 应用文章列表的内容类型筛选。
 *
 * @param WP_Query $query 当前查询。
 */
function glintide_content_card_admin_type_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'post' !== $query->get( 'post_type' ) ) {
		return;
	}

	$type = isset( $_GET['glintide_card_type'] ) ? sanitize_key( wp_unslash( $_GET['glintide_card_type'] ) ) : '';
	if ( ! $type || ! isset( glintide_card_type_options()[ $type ] ) ) {
		return;
	}

	$query->set(
		'meta_query',
		array(
			array(
				'key'   => '_glintide_card_type',
				'value' => $type,
			),
		)
	);
}
add_action( 'pre_get_posts', 'glintide_content_card_admin_type_query' );

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

	$article_cover = 'text' === glintide_card_get_type( $post_id ) ? get_post_meta( $post_id, '_glintide_card_article_cover', true ) : '';
	if ( $article_cover ) {
		$article_cover_url = is_numeric( $article_cover ) ? wp_get_attachment_image_url( absint( $article_cover ), 'large' ) : esc_url_raw( $article_cover );
		if ( $article_cover_url ) {
			$images[] = esc_url_raw( $article_cover_url );
		}
	}

	$featured = get_the_post_thumbnail_url( $post_id, 'large' );
	if ( $featured ) {
		$images[] = esc_url_raw( $featured );
	}

	$gallery = get_post_meta( $post_id, '_glintide_card_gallery', true );
	$gallery = is_array( $gallery ) ? $gallery : preg_split( '/[\r\n,]+/', (string) $gallery );

	foreach ( $gallery as $image ) {
		$image = trim( (string) $image );
		if ( '' === $image ) {
			continue;
		}

		if ( is_numeric( $image ) ) {
			$image = wp_get_attachment_image_url( absint( $image ), 'large' );
		} else {
			$image = esc_url_raw( $image );
		}

		if ( $image && ! in_array( $image, $images, true ) ) {
			$images[] = esc_url_raw( $image );
		}
	}

	if ( empty( $images ) && function_exists( 'glintide_get_thumb' ) ) {
		$legacy_thumb = glintide_get_thumb( $post_id );
		if ( $legacy_thumb ) {
			$images[] = esc_url_raw( $legacy_thumb );
		}
	}

	if ( empty( $images ) ) {
		$images[] = esc_url_raw( GLINTIDE_URL . '/assets/images/banner.jpg' );
	}

	return array_values( array_filter( $images ) );
}

/**
 * 获取卡片的首张真实图片,不自动回退到主题默认图。
 *
 * 文章卡片需要区分“没有配图”和“使用默认媒体”,避免默认封面占据文章内容区。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_primary_image_url( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$article_cover = get_post_meta( $post_id, '_glintide_card_article_cover', true );
	if ( $article_cover ) {
		$article_cover_url = is_numeric( $article_cover ) ? wp_get_attachment_image_url( absint( $article_cover ), 'large' ) : esc_url_raw( $article_cover );
		if ( $article_cover_url ) {
			return esc_url_raw( $article_cover_url );
		}
	}

	$featured = get_the_post_thumbnail_url( $post_id, 'large' );

	if ( $featured ) {
		return esc_url_raw( $featured );
	}

	$gallery = get_post_meta( $post_id, '_glintide_card_gallery', true );
	$gallery = is_array( $gallery ) ? $gallery : preg_split( '/[\r\n,]+/', (string) $gallery );

	foreach ( $gallery as $image ) {
		$image = trim( (string) $image );
		if ( '' === $image ) {
			continue;
		}

		if ( is_numeric( $image ) ) {
			$image = wp_get_attachment_image_url( absint( $image ), 'large' );
		} else {
			$image = esc_url_raw( $image );
		}

		if ( $image ) {
			return esc_url_raw( $image );
		}
	}

	if ( function_exists( 'glintide_get_thumb' ) ) {
		$inline_image = glintide_get_thumb( $post_id );
		if ( $inline_image ) {
			return esc_url_raw( $inline_image );
		}
	}

	return '';
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
 * 获取歌曲名称。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_music_title( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$title   = trim( (string) get_post_meta( $post_id, '_glintide_card_music_title', true ) );
	if ( ! $title ) {
		$title = get_the_title( $post_id );
	}
	return sanitize_text_field( $title );
}

/**
 * 获取音乐封面 URL(可为附件 ID、URL 或空)。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_music_cover_url( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$cover   = (string) get_post_meta( $post_id, '_glintide_card_music_cover', true );

	if ( $cover ) {
		if ( is_numeric( $cover ) ) {
			$url = wp_get_attachment_image_url( absint( $cover ), 'large' );
			if ( $url ) {
				return $url;
			}
		} else {
			return esc_url_raw( $cover );
		}
	}

	// 退化:使用第一张 gallery 图
	$images = glintide_card_get_image_urls( $post_id );
	if ( ! empty( $images[0] ) ) {
		return $images[0];
	}

	// 退化:使用特色图
	if ( has_post_thumbnail( $post_id ) ) {
		$thumb = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'large' );
		if ( $thumb ) {
			return $thumb;
		}
	}

	return '';
}

/**
 * 获取音乐来源(网易云 / 自上传)。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function glintide_card_get_music_source( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$url     = glintide_card_get_music_url( $post_id );
	if ( $url && ( stripos( $url, 'music.163.com' ) !== false || stripos( $url, '163.com' ) !== false ) ) {
		return 'netease';
	}
	return 'upload';
}

/**
 * 解析网易云歌曲 ID 并返回嵌入播放器 URL。
 *
 * @param string $url 网易云链接。
 * @return string iframe URL,无则返回空。
 */
function glintide_card_get_netease_embed_url( $url ) {
	$url = trim( (string) $url );
	if ( ! $url ) {
		return '';
	}

	// 统一使用 REST 模块的 ID 解析(支持 #/song?id=、/song/、/m/song/、纯 ID 等)
	$id = function_exists( 'glintide_extract_netease_song_id' ) ? glintide_extract_netease_song_id( $url ) : '';

	if ( ! $id && stripos( $url, 'playlist' ) === false ) {
		// 兜底:自行解析,避免 REST 模块未加载时失效
		if ( preg_match( '/[?&#]id=(\d+)/', $url, $m ) ) {
			$id = $m[1];
		} elseif ( preg_match( '#/(?:m/)?song/(\d+)#', $url, $m ) ) {
			$id = $m[1];
		} elseif ( preg_match( '/^\d+$/', $url ) ) {
			$id = $url;
		}
	}

	if ( ! $id ) {
		return '';
	}

	return 'https://music.163.com/outchain/player?type=2&id=' . $id . '&auto=0&height=66';
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
 * 将 Bilibili / YouTube 地址转换为安全的嵌入地址。
 *
 * @param string $url 视频地址。
 * @return string
 */
function glintide_card_get_video_embed_url( $url ) {
	$parts = wp_parse_url( $url );
	$host  = isset( $parts['host'] ) ? strtolower( preg_replace( '/^www\./', '', $parts['host'] ) ) : '';
	$path  = isset( $parts['path'] ) ? trim( $parts['path'], '/' ) : '';

	if ( in_array( $host, array( 'bilibili.com', 'm.bilibili.com', 'www.bilibili.com' ), true ) ) {
		$video_id = '';
		if ( preg_match( '#video/(BV[0-9A-Za-z]+)#', $path, $matches ) ) {
			$video_id = $matches[1];
		} elseif ( ! empty( $parts['query'] ) ) {
			parse_str( $parts['query'], $query_args );
			$video_id = isset( $query_args['bvid'] ) ? $query_args['bvid'] : '';
		}

		if ( $video_id && preg_match( '/^BV[0-9A-Za-z]+$/', $video_id ) ) {
			return 'https://player.bilibili.com/player.html?bvid=' . rawurlencode( $video_id ) . '&page=1&high_quality=1&danmaku=0';
		}
	}

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

	return '';
}

/**
 * 媒体空状态占位。
 *
 * 所有类型的空数据统一走这里,保证占位比例、图标与文案结构一致。
 *
 * @param string $base  媒体容器 class。
 * @param string $icon  图标 class。
 * @param string $label 提示文案。
 * @return string
 */
function glintide_card_media_empty( $base, $icon, $label ) {
	return '<div class="' . esc_attr( $base ) . ' glintide-card-media--empty">'
		. '<span class="glintide-card-media-empty-icon"><i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i></span>'
		. '<span class="glintide-card-media-empty-text">' . esc_html( $label ) . '</span>'
		. '</div>';
}

/**
 * 输出点赞(爱心)按钮。
 *
 * @param int    $post_id 文章 ID。
 * @param string $extra   附加 class。
 * @return string
 */
function glintide_card_like_button( $post_id = 0, $extra = '' ) {
	$post_id   = $post_id ? absint( $post_id ) : get_the_ID();
	$likes     = absint( get_post_meta( $post_id, 'likes_count', true ) );
	$class     = 'post-likes glintide-card-like' . ( $extra ? ' ' . $extra : '' );
	$liked     = isset( $_COOKIE[ 'glintide_liked_' . $post_id ] ) ? ' is-liked' : '';
	$icon      = $liked ? 'ri-heart-3-fill' : 'ri-heart-3-line';

	return '<button type="button" class="' . esc_attr( $class . $liked ) . '" data-glintide-like="' . esc_attr( $post_id ) . '" aria-label="点赞" title="点赞" aria-pressed="' . ( $liked ? 'true' : 'false' ) . '">'
		. '<i class="ri-heart-3-icon ' . esc_attr( $icon ) . '" aria-hidden="true"></i>'
		. '</button>';
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
		// 文字卡片为纯排版设计(日期 + 正文 + 作者),不再渲染封面媒体
		return '';
	}

	if ( 'photo' === $type ) {
		if ( empty( $images ) ) {
			return glintide_card_media_empty( $base, 'ri-image-line', '暂未添加照片' );
		}

		$images          = array_slice( $images, 0, 9 );
		$total           = count( $images );
		$summary         = glintide_card_get_summary( $post_id, 18 );
		$author_id       = (int) get_post_field( 'post_author', $post_id );
		$author          = $author_id ? get_the_author_meta( 'display_name', $author_id ) : '';
		$author          = $author ? $author : get_bloginfo( 'name' );
		$author_avatar   = ( $author_id && function_exists( 'glintide_get_avatar_url' ) ) ? glintide_get_avatar_url( $author_id ) : '';
		$avatar_fallback = function_exists( 'glintide_get_default_avatar_url' ) ? glintide_get_default_avatar_url() : GLINTIDE_URL . '/assets/images/default-avatar.png';
		$photo_detail    = ( $summary && $summary !== $title ) ? $summary : $title;
		$base_cls        = $base . ' glintide-photo-frame glintide-photo-frame--count-' . absint( $total );
		$html            = '<div class="' . esc_attr( $base_cls ) . '" data-glintide-photo-gallery data-glintide-photo-carousel data-glintide-photo-title="' . esc_attr( $title ) . '" data-glintide-photo-total="' . absint( $total ) . '">';
		$html           .= '<div class="glintide-photo-grid" role="group" aria-label="' . esc_attr( '照片集,共 ' . $total . ' 张' ) . '">';

		foreach ( $images as $index => $image ) {
			$label = '查看第 ' . ( $index + 1 ) . ' 张照片';

			$html .= '<button type="button" class="glintide-photo-tile" data-glintide-photo-open data-glintide-photo-index="' . absint( $index ) . '" aria-label="' . esc_attr( $label ) . '">';
			$html .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $title . ' - 第 ' . ( $index + 1 ) . ' 张照片' ) . '" loading="eager" decoding="async">';
			$html .= '</button>';
		}

		$html .= '</div>';
		$html .= '<div class="glintide-photo-overlay">';
		$html .= '<div class="glintide-photo-bottom">';
		$html .= '<div class="glintide-photo-profile">';
		$html .= '<img class="glintide-photo-avatar" src="' . esc_url( $author_avatar ? $author_avatar : $avatar_fallback ) . '" alt="" loading="lazy" decoding="async" data-glintide-avatar data-glintide-avatar-fallback="' . esc_url( $avatar_fallback ) . '">';
		$html .= '<span class="glintide-photo-copy">';
		$html .= '<strong class="glintide-photo-title">' . esc_html( $author ) . '</strong>';
		$html .= '<small class="glintide-photo-summary">' . esc_html( $photo_detail ) . '</small>';
		$html .= '</span>';
		$html .= '</div>';
		$html .= glintide_card_like_button( $post_id, 'glintide-photo-like' );
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	if ( 'music' === $type ) {
		$music_url   = glintide_card_get_music_url( $post_id );
		$artist      = glintide_card_get_music_artist( $post_id );
		$music_title = glintide_card_get_music_title( $post_id );
		$cover       = glintide_card_get_music_cover_url( $post_id );
		$source      = glintide_card_get_music_source( $post_id );
		$embed_url   = ( 'netease' === $source ) ? glintide_card_get_netease_embed_url( $music_url ) : '';
		$has_url     = (bool) $music_url;

		// 网易云链接没有可直接使用的音频地址,由前端通过 REST 解析后再下载
		$download_ready = ( $has_url && 'netease' !== $source );

		$html  = '<div class="' . esc_attr( $base ) . ' glintide-music-card" data-glintide-music'
			. ' data-music-source="' . esc_attr( $source ) . '"'
			. ' data-music-url="' . esc_attr( $music_url ) . '"'
			. ' data-music-embed="' . esc_attr( $embed_url ) . '"'
			. '>';

		// 主体:唱片与真实封面叠放,右侧保留歌曲信息和播放入口
		$html .= '<div class="glintide-music-card-main">';
		$html .= '<div class="glintide-music-card-art">';
		$html .= '<img class="glintide-music-card-vinyl" src="' . esc_url( GLINTIDE_URL . '/assets/images/vinyl_ring.png' ) . '" alt="" aria-hidden="true">';
		$html .= '<img class="glintide-music-card-tonearm" src="' . esc_url( GLINTIDE_URL . '/assets/images/tonearm.png' ) . '" alt="" aria-hidden="true">';
		$html .= '<span class="glintide-music-card-cover">';
		if ( $cover ) {
			$html .= '<img class="glintide-music-card-cover-img" data-glintide-music-cover src="' . esc_url( $cover ) . '" alt="' . esc_attr( $music_title ) . '" loading="lazy" decoding="async">';
		} else {
			$html .= '<span class="glintide-music-card-cover-img glintide-music-card-cover-img--placeholder" data-glintide-music-cover aria-hidden="true"><i class="ri-music-2-fill"></i></span>';
		}
		$html .= '</span>';
		$html .= '</div>';

		$liked     = isset( $_COOKIE[ 'glintide_liked_' . $post_id ] ) ? ' is-liked' : '';
		$like_icon = $liked ? 'ri-heart-3-fill' : 'ri-heart-3-line';
		$html .= '<div class="glintide-music-card-info">';
		$html .= '<div class="glintide-music-card-top">';
		$html .= '<div class="glintide-music-card-meta">';
		$html .= '<h4 class="glintide-music-card-title">' . esc_html( $music_title ? $music_title : '未命名歌曲' ) . '</h4>';
		if ( $artist ) {
			$html .= '<p class="glintide-music-card-artist">' . esc_html( $artist ) . '</p>';
		}
		$html .= '</div>';
		$html .= '<div class="glintide-music-card-actions glintide-music-card-top-actions">';
		$html .= '<button type="button" class="glintide-music-card-action glintide-music-card-action--like' . $liked . '" data-glintide-like="' . esc_attr( $post_id ) . '" aria-pressed="' . ( $liked ? 'true' : 'false' ) . '" aria-label="点赞" title="点赞">'
			. '<i class="' . esc_attr( $like_icon ) . '" aria-hidden="true"></i>'
			. '</button>';
		$html .= '<a class="glintide-music-card-action glintide-music-card-action--download" data-glintide-music-download'
			. ( $download_ready ? '' : ' disabled aria-disabled="true"' )
			. ' href="' . ( $download_ready ? esc_url( $music_url ) : '#' ) . '"'
			. ' target="_blank" rel="noopener noreferrer" download aria-label="下载" title="下载音频">'
			. '<i class="ri-download-2-line" aria-hidden="true"></i>'
			. '</a>';
		$html .= '</div>';
		$html .= '</div>';
		// 状态提示:解析中 / 失败降级提示
		$html .= '<p class="glintide-music-card-note" data-glintide-music-note role="status" aria-live="polite" hidden></p>';

		// 右侧中部:播放按钮
		$html .= '<div class="glintide-music-card-play-row">';
		if ( $has_url ) {
			$html .= '<button type="button" class="glintide-music-card-play" data-glintide-music-toggle aria-label="播放" data-state="paused">'
				. '<i class="iconfont icon-icon_play-01" aria-hidden="true"></i>'
				. '</button>';
		} else {
			$html .= '<button type="button" class="glintide-music-card-play" disabled aria-label="音频地址未设置">'
				. '<i class="iconfont icon-icon_play-01" aria-hidden="true"></i>'
				. '</button>';
		}
		$html .= '</div>';

		// 右侧底部:播放进度与时间
		$html .= '<div class="glintide-music-card-bottom">';
		$html .= '<span class="glintide-music-card-time" data-glintide-music-time-current>00:00</span>';
		$html .= '<input type="range" class="glintide-music-card-seek" data-glintide-music-seek min="0" max="1000" step="1" value="0" aria-label="播放进度"'
			. ( $has_url ? '' : ' disabled' ) . '>';
		$html .= '<span class="glintide-music-card-time" data-glintide-music-time-duration>00:00</span>';
		$html .= '</div>';

		$html .= '</div>';
		$html .= '</div>';

		// 网易云直链失败时由前端提供官方歌曲页播放入口
		$html .= '<div class="glintide-music-card-fallback" data-glintide-music-fallback hidden></div>';

		// 音频元素:自上传音频直接设置 src,网易云由 JS 解析后注入
		if ( $has_url && 'netease' !== $source ) {
			$html .= '<audio class="glintide-card-music-audio" data-glintide-music-audio preload="metadata" src="' . esc_url( $music_url ) . '"></audio>';
		} else {
			$html .= '<audio class="glintide-card-music-audio" data-glintide-music-audio preload="metadata"></audio>';
		}

		$html .= '</div>'; // /base
		return $html;
	}

	if ( 'link' === $type ) {
		// 链接卡片为整卡锚点设计,在模板中直接输出,这里不再渲染独立媒体面板
		if ( ! glintide_card_get_link_url( $post_id ) ) {
			return glintide_card_media_empty( $base, 'ri-links-line', '链接地址未设置' );
		}
		return '';
	}

	if ( 'video' === $type ) {
		$video_url = glintide_card_get_video_url( $post_id );
		$poster    = isset( $images[0] ) ? $images[0] : '';

		if ( ! $video_url ) {
			return glintide_card_media_empty( $base, 'ri-video-line', '视频地址未设置' );
		}

		if ( glintide_card_is_direct_video_url( $video_url ) ) {
			$mime = glintide_card_get_video_mime( $video_url );

			// 自定义视频播放器:标题、全屏、跳转、播放、进度和音量均保持在视频表面上
			$html  = '<div class="' . esc_attr( $base ) . ' glintide-video-frame" data-glintide-video>';
			$html .= '<video class="glintide-card-video" playsinline preload="metadata"' . ( $poster ? ' poster="' . esc_url( $poster ) . '"' : '' ) . '><source src="' . esc_url( $video_url ) . '"' . ( $mime ? ' type="' . esc_attr( $mime ) . '"' : '' ) . '><span>当前浏览器不支持视频播放。</span></video>';
			$html .= '<div class="glintide-video-scrim" aria-hidden="true"></div>';
			$html .= '<div class="glintide-video-ui">';
			$html .= '<div class="glintide-video-topbar">';
			$html .= '<h3 class="glintide-video-title">' . esc_html( $title ) . '</h3>';
			$html .= '<button type="button" class="glintide-video-control glintide-video-fullscreen" data-glintide-video-fullscreen aria-label="全屏播放" title="全屏播放"><i class="ri-fullscreen-line" aria-hidden="true"></i></button>';
			$html .= '</div>';
			$html .= '<div class="glintide-video-center">';
			$html .= '<button type="button" class="glintide-video-play" data-glintide-video-toggle aria-label="播放视频" title="播放视频"><i class="ri-play-fill" aria-hidden="true"></i></button>';
			$html .= '</div>';
			$html .= '<div class="glintide-video-bottom">';
			$html .= '<span class="glintide-video-time" data-glintide-video-current>00:00</span>';
			$html .= '<input type="range" class="glintide-video-progress" data-glintide-video-progress min="0" max="0" step="0.1" value="0" aria-label="视频播放进度">';
			$html .= '<span class="glintide-video-time" data-glintide-video-duration>00:00</span>';
			$html .= '<button type="button" class="glintide-video-control glintide-video-volume" data-glintide-video-volume aria-label="静音" title="静音"><i class="ri-volume-up-line" aria-hidden="true"></i></button>';
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';

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
 * 校验首页卡片流筛选类型,只允许已知的卡片类型,非法值返回空字符串(表示不过滤)。
 *
 * @param mixed $type 前台传入的卡片类型。
 * @return string
 */
function glintide_sanitize_card_type_filter( $type ) {
	$valid = array( 'text', 'photo', 'music', 'video', 'link' );
	$type  = sanitize_key( (string) $type );

	return in_array( $type, $valid, true ) ? $type : '';
}

/**
 * 获取首页内容卡片查询。
 *
 * @param int   $paged 当前页码。
 * @param array $args  可选筛选参数:type(卡片类型)、search(关键词)。
 * @return WP_Query
 */
function glintide_get_card_feed_query( $paged = 1, $args = array() ) {
	$per_page = (int) apply_filters( 'glintide_card_feed_per_page', 12 );

	$args = wp_parse_args(
		$args,
		array(
			'type'   => '',
			'search' => '',
		)
	);

	$card_type = glintide_sanitize_card_type_filter( $args['type'] );
	$search    = sanitize_text_field( (string) $args['search'] );

	$query_args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => max( 1, $per_page ),
		'paged'               => max( 1, absint( $paged ) ),
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	);

	if ( '' !== $search ) {
		$query_args['s'] = $search;
	}

	if ( '' !== $card_type ) {
		if ( 'text' === $card_type ) {
			// text 是缺省类型,未写入 meta 的历史文章也算 text。
			$query_args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'   => '_glintide_card_type',
					'value' => $card_type,
				),
				array(
					'key'     => '_glintide_card_type',
					'compare' => 'NOT EXISTS',
				),
			);
		} else {
			$query_args['meta_query'] = array(
				array(
					'key'   => '_glintide_card_type',
					'value' => $card_type,
				),
			);
		}
	}

	return new WP_Query( $query_args );
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

/**
 * AJAX:按页返回首页内容卡片 HTML(无限瀑布流加载)。
 */
function glintide_card_feed_ajax() {
	$paged = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 0;
	$paged = max( 1, $paged );

	$query = glintide_get_card_feed_query(
		$paged,
		array(
			'type'   => isset( $_POST['type'] ) ? wp_unslash( $_POST['type'] ) : '',
			'search' => isset( $_POST['search'] ) ? wp_unslash( $_POST['search'] ) : '',
		)
	);

	$payload = array(
		'html'      => '',
		'hasMore'   => false,
		'paged'     => $paged,
		'maxPages'  => (int) $query->max_num_pages,
	);

	if ( ! $query->have_posts() ) {
		wp_send_json_success( $payload );
	}

	ob_start();
	while ( $query->have_posts() ) :
		$query->the_post();
		get_template_part( 'tpl/content', 'card' );
	endwhile;
	$html = ob_get_clean();

	wp_reset_postdata();

	wp_send_json_success(
		array(
			'html'      => $html,
			'hasMore'   => $paged < $query->max_num_pages,
			'paged'     => $paged,
			'maxPages'  => (int) $query->max_num_pages,
		)
	);
}

add_action( 'wp_ajax_glintide_card_feed', 'glintide_card_feed_ajax' );
add_action( 'wp_ajax_nopriv_glintide_card_feed', 'glintide_card_feed_ajax' );

/**
 * 渲染单条评论(弹窗评论列表用)。
 *
 * @param object $comment 评论对象。
 * @return array
 */
function glintide_card_comment_item( $comment ) {
	$author_id = (int) $comment->user_id;
	$avatar    = ( $author_id && function_exists( 'glintide_get_avatar_url' ) ) ? glintide_get_avatar_url( $author_id ) : '';
	if ( ! $avatar ) {
		$avatar = function_exists( 'glintide_get_default_avatar_url' ) ? glintide_get_default_avatar_url() : GLINTIDE_URL . '/assets/images/default-avatar.png';
	}

	return array(
		'id'      => (int) $comment->comment_ID,
		'author'  => $comment->comment_author,
		'avatar'  => $avatar,
		'date'    => get_comment_date( 'Y-m-d H:i', $comment ),
		'content' => esc_html( $comment->comment_content ),
	);
}

/**
 * AJAX:卡片详情(弹窗用)。
 */
function glintide_card_detail_ajax() {
	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$post    = get_post( $post_id );

	if ( ! $post || 'post' !== $post->post_type ) {
		wp_send_json_error( array( 'msg' => '内容不存在' ), 404 );
	}

	$type      = glintide_card_get_type( $post_id );
	$type_data = glintide_card_get_type_data( $post_id );
	$author_id = (int) $post->post_author;

	$author      = get_the_author_meta( 'display_name', $author_id );
	$author      = $author ? $author : get_bloginfo( 'name' );
	$avatar      = ( $author_id && function_exists( 'glintide_get_avatar_url' ) ) ? glintide_get_avatar_url( $author_id ) : '';
	if ( ! $avatar ) {
		$avatar = function_exists( 'glintide_get_default_avatar_url' ) ? glintide_get_default_avatar_url() : GLINTIDE_URL . '/assets/images/default-avatar.png';
	}

	$content_html = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );

	$liked = isset( $_COOKIE[ 'glintide_liked_' . $post_id ] ) && '1' === $_COOKIE[ 'glintide_liked_' . $post_id ];

	$comments = array();
	foreach ( get_comments(
		array(
			'post_id'      => $post_id,
			'status'       => 'approve',
			'orderby'      => 'comment_date_gmt',
			'order'        => 'ASC',
		)
	) as $comment ) {
		$comments[] = glintide_card_comment_item( $comment );
	}

	wp_send_json_success(
		array(
			'type'         => $type,
			'type_label'   => $type_data['label'],
			'title'        => get_the_title( $post_id ),
			'author'       => $author,
			'avatar'       => $avatar,
			'date'         => get_the_date( 'Y-m-d H:i', $post_id ),
			'content_html' => $content_html,
			'likes'        => absint( get_post_meta( $post_id, 'likes_count', true ) ),
			'liked'        => $liked,
			'comments'     => $comments,
			'comment_nonce'=> wp_create_nonce( 'glintide_card_comment_' . $post_id ),
			'permalink'    => get_permalink( $post_id ),
		)
	);
}

add_action( 'wp_ajax_glintide_card_detail', 'glintide_card_detail_ajax' );
add_action( 'wp_ajax_nopriv_glintide_card_detail', 'glintide_card_detail_ajax' );

/**
 * AJAX:发表卡片评论(弹窗用)。
 */
function glintide_card_comment_ajax() {
	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$nonce   = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	$content = isset( $_POST['comment'] ) ? trim( wp_unslash( $_POST['comment'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	if ( ! wp_verify_nonce( $nonce, 'glintide_card_comment_' . $post_id ) ) {
		wp_send_json_error( array( 'msg' => '会话已过期,请刷新后重试' ), 403 );
	}

	$post = get_post( $post_id );
	if ( ! $post || 'post' !== $post->post_type ) {
		wp_send_json_error( array( 'msg' => '内容不存在' ), 404 );
	}

	if ( '' === $content ) {
		wp_send_json_error( array( 'msg' => '评论内容不能为空' ) );
	}
	if ( mb_strlen( $content ) > 1000 ) {
		wp_send_json_error( array( 'msg' => '评论最长 1000 字' ) );
	}

	$user_id = get_current_user_id();
	$author  = $user_id ? get_the_author_meta( 'display_name', $user_id ) : '游客';
	$author  = $author ? $author : '游客';

	$comment_id = wp_insert_comment(
		array(
			'comment_post_ID'      => $post_id,
			'comment_content'      => $content,
			'comment_author'       => $author,
			'comment_author_email' => $user_id ? get_the_author_meta( 'email', $user_id ) : '',
			'user_id'              => $user_id,
			'comment_approved'     => 1,
			'comment_date'         => current_time( 'mysql' ),
		)
	);

	if ( ! $comment_id ) {
		wp_send_json_error( array( 'msg' => '评论发布失败' ) );
	}

	$comment = get_comment( $comment_id );

	wp_send_json_success(
		array(
			'comment' => glintide_card_comment_item( $comment ),
			'count'   => (int) get_comments_number( $post_id ),
		)
	);
}

add_action( 'wp_ajax_glintide_card_comment', 'glintide_card_comment_ajax' );
add_action( 'wp_ajax_nopriv_glintide_card_comment', 'glintide_card_comment_ajax' );

/**
 * 处理内容卡片点赞(支持登录用户与游客)。
 */
function glintide_card_handle_like() {
	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$post    = get_post( $post_id );

	if ( ! $post || 'post' !== $post->post_type ) {
		wp_send_json_error( array( 'msg' => '内容不存在' ), 404 );
	}

	$cookie_key = 'glintide_liked_' . $post_id;
	$liked      = false;

	if ( isset( $_COOKIE[ $cookie_key ] ) && '1' === $_COOKIE[ $cookie_key ] ) {
		$liked = true;
	} else {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$user_like = get_user_meta( $user_id, 'glintide_card_likes', true );
			$user_like = is_array( $user_like ) ? array_map( 'absint', $user_like ) : array();
			$liked     = in_array( $post_id, $user_like, true );
		}
	}

	$count = absint( get_post_meta( $post_id, 'likes_count', true ) );

	if ( $liked ) {
		$count = max( 0, $count - 1 );
		$state = false;
	} else {
		$count ++;
		$state = true;
	}

	update_post_meta( $post_id, 'likes_count', $count );

	if ( $state ) {
		if ( ! headers_sent() ) {
			setcookie( $cookie_key, '1', time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, false );
		}
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$user_like = get_user_meta( $user_id, 'glintide_card_likes', true );
			$user_like = is_array( $user_like ) ? array_map( 'absint', $user_like ) : array();
			$user_like[] = $post_id;
			update_user_meta( $user_id, 'glintide_card_likes', array_values( array_unique( $user_like ) ) );
		}
	} else {
		if ( ! headers_sent() ) {
			setcookie( $cookie_key, '0', time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, false );
		}
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$user_like = get_user_meta( $user_id, 'glintide_card_likes', true );
			$user_like = is_array( $user_like ) ? array_map( 'absint', $user_like ) : array();
			if ( ( $key = array_search( $post_id, $user_like, true ) ) !== false ) {
				unset( $user_like[ $key ] );
			}
			update_user_meta( $user_id, 'glintide_card_likes', array_values( array_unique( $user_like ) ) );
		}
	}

	wp_send_json_success( array( 'count' => $count, 'liked' => $state ) );
}

add_action( 'wp_ajax_glintide_card_like', 'glintide_card_handle_like' );
add_action( 'wp_ajax_nopriv_glintide_card_like', 'glintide_card_handle_like' );

