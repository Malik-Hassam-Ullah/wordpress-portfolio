<?php
/**
 * Single Project Template for Hassam Portfolio
 */

get_header();

$badge = get_post_meta(get_the_ID(), '_project_badge', true) ?: 'Featured Project';
$icon = get_post_meta(get_the_ID(), '_project_icon', true) ?: 'fa-solid fa-code';
$tags = get_post_meta(get_the_ID(), '_project_tags', true);
$github = get_post_meta(get_the_ID(), '_project_github', true) ?: 'https://github.com/Malik-Hassam-Ullah';
$tag_array = $tags ? array_map('trim', explode(',', $tags)) : [];
?>

<div class="project-single-container" style="min-height: 80vh; padding: 100px 20px 80px; max-width: 900px; margin: 0 auto; position: relative; z-index: 1;">
    <div style="margin-bottom: 30px;">
        <a href="<?php echo esc_url(home_url('/#projects')); ?>" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-arrow-left"></i> Back to All Projects
        </a>
    </div>

    <article class="glass-card" style="padding: 40px; border-radius: 20px; background: rgba(18, 24, 46, 0.75); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 20px 50px rgba(0,0,0,0.4);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
            <span class="project-badge" style="background: rgba(0, 242, 254, 0.15); color: #00f2fe; border: 1px solid rgba(0, 242, 254, 0.3); padding: 6px 16px; border-radius: 99px; font-size: 0.85rem; font-weight: 600;">
                <?php echo esc_html($badge); ?>
            </span>
            <div style="font-size: 2rem; color: #00f2fe;">
                <i class="<?php echo esc_attr($icon); ?>"></i>
            </div>
        </div>

        <h1 class="gradient-text" style="font-size: 2.5rem; font-weight: 800; margin-bottom: 15px; line-height: 1.2;">
            <?php the_title(); ?>
        </h1>

        <?php if (!empty($tag_array)): ?>
        <div class="project-tags" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 25px;">
            <?php foreach ($tag_array as $tag): ?>
                <span style="background: rgba(255, 255, 255, 0.05); color: #94a3b8; border: 1px solid rgba(255, 255, 255, 0.1); padding: 4px 12px; border-radius: 6px; font-size: 0.85rem; font-family: 'Fira Code', monospace;">
                    <?php echo esc_html($tag); ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="project-content" style="color: #cbd5e1; font-size: 1.1rem; line-height: 1.8; margin-bottom: 35px;">
            <?php the_content(); ?>
        </div>

        <div style="display: flex; gap: 15px; flex-wrap: wrap; padding-top: 25px; border-top: 1px solid rgba(255, 255, 255, 0.08);">
            <a href="<?php echo esc_url($github); ?>" target="_blank" rel="noopener" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 10px;">
                <i class="fa-brands fa-github" style="font-size: 1.2rem;"></i>
                <span>View Source on GitHub</span>
            </a>
            <?php if (current_user_can('edit_posts')): ?>
            <a href="<?php echo esc_url(get_edit_post_link()); ?>" class="btn btn-glass" style="display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-pen-to-square"></i> Edit in WP Admin
            </a>
            <?php endif; ?>
        </div>
    </article>
</div>



<?php
get_footer();
