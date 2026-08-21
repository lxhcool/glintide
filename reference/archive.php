<?php
/**
 * The template for displaying archive pages
 *
 * @package pix
 */

get_header();

if (!function_exists('pix_archive_get_cover_url')) {
    function pix_archive_get_cover_url($value) {
        if (is_array($value)) {
            if (!empty($value['url'])) {
                return $value['url'];
            }
            if (!empty($value['id'])) {
                $url = wp_get_attachment_image_url(absint($value['id']), 'full');
                return $url ? $url : '';
            }
            return '';
        }

        if (is_numeric($value)) {
            $url = wp_get_attachment_image_url(absint($value), 'full');
            return $url ? $url : '';
        }

        return is_string($value) ? trim($value) : '';
    }
}

$paged = max(1, (int) get_query_var('paged'));
$total_posts = isset($wp_query->found_posts) ? (int) $wp_query->found_posts : 0;
$max_pages = isset($wp_query->max_num_pages) ? (int) $wp_query->max_num_pages : 1;

$archive_title = wp_strip_all_tags(get_the_archive_title());
$archive_desc = get_the_archive_description();
$archive_kicker = 'Archive';
$archive_cover = '';

if (is_category()) {
    $archive_title = single_cat_title('', false);
    $archive_kicker = 'Category';
    $archive_term = get_queried_object();
    if ($archive_term instanceof WP_Term) {
        $archive_meta = get_term_meta($archive_term->term_id, '_ppo_taxonomy_options', true);
        if (is_array($archive_meta) && !empty($archive_meta['cat_banner'])) {
            $archive_cover = pix_archive_get_cover_url($archive_meta['cat_banner']);
        }
        if (!$archive_cover) {
            $archive_cover = pix_archive_get_cover_url(get_term_meta($archive_term->term_id, 'cat_banner', true));
        }
    }
} elseif (is_tag()) {
    $archive_title = single_tag_title('', false);
    $archive_kicker = 'Tag';
} elseif (is_date()) {
    if (is_year()) {
        $archive_title = sprintf('%s 年', (int) get_query_var('year'));
    } elseif (is_month()) {
        $archive_title = sprintf('%1$s 年 %2$s 月', (int) get_query_var('year'), (int) get_query_var('monthnum'));
    } elseif (is_day()) {
        $archive_title = sprintf('%1$s 年 %2$s 月 %3$s 日', (int) get_query_var('year'), (int) get_query_var('monthnum'), (int) get_query_var('day'));
    }
    $archive_kicker = 'Date Archive';
} elseif (is_post_type_archive()) {
    $archive_kicker = 'Post Type';
} elseif (is_tax()) {
    $archive_title = single_term_title('', false);
    $archive_kicker = 'Taxonomy';
}

$archive_title = $archive_title ? $archive_title : '内容归档';
$archive_hero_classes = array('pix-archive-hero');
if ($archive_cover) {
    $archive_hero_classes[] = 'has-cover';
}
$archive_hero_style = $archive_cover ? '--pix-archive-cover:url("' . esc_url_raw($archive_cover) . '");background-image:var(--pix-archive-cover);' : '';
$archive_ajax_attrs = array(
    'data-action' => 'cls_load_posts',
    'data-context' => 'archive',
    'data-max' => $max_pages,
    'data-append' => '#blog-item',
    'data-base-url' => get_pagenum_link(1),
);

if (is_category() || is_tag() || is_tax()) {
    $term = get_queried_object();
    if ($term instanceof WP_Term) {
        $archive_ajax_attrs['data-archive-type'] = is_category() ? 'category' : (is_tag() ? 'tag' : 'tax');
        $archive_ajax_attrs['data-term-id'] = $term->term_id;
        $archive_ajax_attrs['data-taxonomy'] = $term->taxonomy;
    }
} elseif (is_date()) {
    $archive_ajax_attrs['data-archive-type'] = 'date';
    $archive_ajax_attrs['data-year'] = get_query_var('year');
    $archive_ajax_attrs['data-monthnum'] = get_query_var('monthnum');
    $archive_ajax_attrs['data-day'] = get_query_var('day');
} elseif (is_post_type_archive()) {
    $archive_ajax_attrs['data-archive-type'] = 'post_type';
    $archive_ajax_attrs['data-post-type'] = get_query_var('post_type');
}
?>

<div class="pix-content home-box home-classic pix-archive-wrap">
    <div class="pix-archive-shell">
        <div class="center-content full-width">
            <main id="primary" class="site-main pix-archive-page cls-content">
                <section class="<?php echo esc_attr(implode(' ', $archive_hero_classes)); ?>"<?php echo $archive_hero_style ? ' style="' . esc_attr($archive_hero_style) . '"' : ''; ?>>
                    <div class="pix-archive-hero-content">
                        <div class="pix-search-kicker"><?php echo esc_html($archive_kicker); ?></div>
                        <h1><?php echo esc_html($archive_title); ?></h1>
                        <?php if ($archive_desc) : ?>
                            <div class="pix-archive-desc"><?php echo wp_kses_post($archive_desc); ?></div>
                        <?php else : ?>
                            <p>这里汇总了当前归档下的公开内容，你可以继续向下浏览全部结果。</p>
                        <?php endif; ?>
                        <div class="pix-archive-meta">
                            <span><i class="ri-file-list-3-line"></i><?php echo esc_html(number_format_i18n($total_posts)); ?> 条内容</span>
                            <?php if ($max_pages > 1) : ?>
                                <span><i class="ri-pages-line"></i>第 <?php echo esc_html(number_format_i18n($paged)); ?> / <?php echo esc_html(number_format_i18n($max_pages)); ?> 页</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <section class="pix-archive-body">
                    <?php if (have_posts()) : ?>
                        <div class="pix-search-section-head">
                            <div>
                                <span>Posts</span>
                                <h2>归档内容</h2>
                            </div>
                            <p>按发布时间排序</p>
                        </div>

                        <div id="blog-item" class="pix-search-results-grid pix-archive-results p-item box-p">
                            <?php
                            while (have_posts()) :
                                the_post();
                                if ('post' === get_post_type()) {
                                    get_template_part('tpl/content', 'grid');
                                } else {
                                    get_template_part('tpl/content', 'search');
                                }
                            endwhile;
                            ?>
                        </div>

                        <?php
                        $pagination = paginate_links(array(
                            'current' => $paged,
                            'total' => $max_pages,
                            'prev_text' => '<i class="ri-arrow-left-s-line"></i>',
                            'next_text' => '<i class="ri-arrow-right-s-line"></i>',
                            'type' => 'list',
                        ));

                        if ($pagination) :
                        ?>
                            <div class="pix-archive-pagination pagination-box"<?php
                                foreach ($archive_ajax_attrs as $attr => $value) {
                                    if ($value !== '' && $value !== null) {
                                        echo ' ' . esc_attr($attr) . '="' . esc_attr($value) . '"';
                                    }
                                }
                            ?>>
                                <?php echo wp_kses_post($pagination); ?>
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="pix-search-empty">
                            <div class="pix-search-empty-icon"><i class="ri-inbox-archive-line"></i></div>
                            <h2>暂无内容</h2>
                            <p>这个归档下暂时没有公开内容，可以先回到首页看看最新更新。</p>
                            <div class="pix-404-actions pix-archive-empty-actions">
                                <a class="pix-404-primary" href="<?php echo esc_url(home_url('/')); ?>"><i class="ri-home-4-line"></i> 回到首页</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>
</div>

<?php
get_footer();
