<?php
/**
 * The template for displaying moment topic archive pages.
 *
 * @package pix
 */

get_header();

$term = get_queried_object();
$term_id = $term instanceof WP_Term ? absint($term->term_id) : 0;
$topic_name = $term instanceof WP_Term ? $term->name : single_term_title('', false);
$topic_desc = $term instanceof WP_Term ? term_description($term_id, 'moment_tag') : '';
$topic_count = $term instanceof WP_Term ? absint($term->count) : 0;
$moment_label = function_exists('ppo_moment_label') ? ppo_moment_label('moment') : '片刻';
?>

<div class="pix-content home-box home-classic pix-archive-wrap pix-moment-topic-archive-wrap">
    <div class="pix-archive-shell">
        <div class="center-content full-width">
            <main id="primary" class="site-main pix-archive-page pix-moment-topic-archive cls-content">
                <section class="pix-archive-hero pix-moment-topic-hero">
                    <div class="pix-archive-mark pix-moment-topic-mark"><i class="ri-hashtag"></i></div>
                    <div class="pix-search-kicker">Topic Archive</div>
                    <h1><?php echo esc_html($topic_name); ?></h1>
                    <?php if ($topic_desc) : ?>
                        <div class="pix-archive-desc"><?php echo wp_kses_post($topic_desc); ?></div>
                    <?php else : ?>
                        <p>这里汇总了当前话题下的公开<?php echo esc_html($moment_label); ?>，你可以继续向下浏览全部动态。</p>
                    <?php endif; ?>
                    <div class="pix-archive-meta">
                        <span><i class="ri-message-3-line"></i><?php echo esc_html(number_format_i18n($topic_count)); ?> 条<?php echo esc_html($moment_label); ?></span>
                    </div>
                </section>

                <section class="pix-archive-body pix-moment-topic-body">
                    <div class="pix-search-section-head pix-moment-topic-head">
                        <div>
                            <span>Moments</span>
                            <h2>话题归档</h2>
                        </div>
                        <p>按发布时间排序</p>
                    </div>

                    <div class="pix-modern pix-modern-moment pix-moment-topic-list-shell">
                        <div class="home-moment-content cls-content pix-moment-content pix-moment-grid-scope">
                            <?php get_template_part('inc/layouts/cls', 'moment', array('catid' => 0, 'tagid' => $term_id)); ?>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</div>

<?php
get_footer();
