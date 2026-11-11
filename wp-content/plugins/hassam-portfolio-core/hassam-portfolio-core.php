<?php
/**
 * Plugin Name: Hassam Portfolio Core & Dashboard Manager
 * Plugin URI: https://github.com/Malik-Hassam-Ullah/PORTFOLIO
 * Description: Complete portfolio manager for Malik Hassam Ullah. Includes Custom Post Types for Projects, Elementor integration, Dashboard control widgets, and modern UI styles.
 * Version: 1.0.0
 * Author: Malik Hassam Ullah
 * Author URI: https://github.com/Malik-Hassam-Ullah
 * Text Domain: hassam-portfolio
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HASSAM_PORTFOLIO_VERSION', '1.0.0');
define('HASSAM_PORTFOLIO_PATH', plugin_dir_path(__FILE__));
define('HASSAM_PORTFOLIO_URL', plugin_dir_url(__FILE__));

class HassamPortfolioCore {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('init', [$this, 'register_post_types']);
        add_action('init', [$this, 'register_taxonomies']);
        add_action('add_meta_boxes', [$this, 'add_project_meta_boxes']);
        add_action('save_post_portfolio_project', [$this, 'save_project_meta']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widgets']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        // Register Shortcodes
        add_shortcode('hassam_portfolio_hero', [$this, 'render_hero']);
        add_shortcode('hassam_portfolio_stats', [$this, 'render_stats']);
        add_shortcode('hassam_portfolio_about', [$this, 'render_about']);
        add_shortcode('hassam_portfolio_skills', [$this, 'render_skills']);
        add_shortcode('hassam_portfolio_projects', [$this, 'render_projects']);
        add_shortcode('hassam_portfolio_experience', [$this, 'render_experience']);
        add_shortcode('hassam_portfolio_contact', [$this, 'render_contact']);
        add_shortcode('hassam_full_portfolio', [$this, 'render_full_portfolio']);

        // Filter body class
        add_filter('body_class', [$this, 'add_body_classes']);

        // Custom template for single projects
        add_filter('single_template', [$this, 'load_single_project_template']);

        // Contact form handlers
        add_action('admin_post_nopriv_submit_portfolio_contact', [$this, 'handle_contact_submission']);
        add_action('admin_post_submit_portfolio_contact', [$this, 'handle_contact_submission']);

        // Custom columns for messages in WP Admin
        add_filter('manage_portfolio_message_posts_columns', [$this, 'set_message_columns']);
        add_action('manage_portfolio_message_posts_custom_column', [$this, 'render_message_column'], 10, 2);
    }

    public function set_message_columns($columns) {
        return [
            'cb' => '<input type="checkbox" />',
            'title' => __('Subject / Title', 'hassam-portfolio'),
            'sender_name' => __('Sender Name', 'hassam-portfolio'),
            'sender_email' => __('Sender Email', 'hassam-portfolio'),
            'message_snippet' => __('Message Snippet', 'hassam-portfolio'),
            'date' => __('Received Date', 'hassam-portfolio'),
        ];
    }

    public function render_message_column($column, $post_id) {
        if ($column === 'sender_name') {
            echo esc_html(get_post_meta($post_id, '_sender_name', true) ?: '-');
        } elseif ($column === 'sender_email') {
            $email = get_post_meta($post_id, '_sender_email', true);
            if ($email) {
                echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
            } else {
                echo '-';
            }
        } elseif ($column === 'message_snippet') {
            $content = get_post_field('post_content', $post_id);
            echo esc_html(wp_trim_words($content, 12, '...'));
        }
    }

    public function load_single_project_template($template) {
        global $post;
        if ($post && $post->post_type === 'portfolio_project') {
            $custom_template = HASSAM_PORTFOLIO_PATH . 'templates/single-project.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    public function handle_contact_submission() {
        if (!isset($_POST['name']) || !isset($_POST['email'])) {
            wp_redirect(home_url('/#contact'));
            exit;
        }

        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $subject = sanitize_text_field($_POST['subject'] ?? 'Portfolio Contact');
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        // Insert message post
        wp_insert_post([
            'post_title'   => $subject . ' - from ' . $name,
            'post_content' => $message,
            'post_type'    => 'portfolio_message',
            'post_status'  => 'publish',
            'meta_input'   => [
                '_sender_name'    => $name,
                '_sender_email'   => $email,
                '_sender_subject' => $subject,
            ]
        ]);

        wp_redirect(home_url('/?contact_success=1#contact'));
        exit;
    }

    public function add_body_classes($classes) {
        $classes[] = 'hassam-portfolio-theme';
        return $classes;
    }

    /**
     * Register Custom Post Type for Projects
     */
    public function register_post_types() {
        $labels = [
            'name'                  => _x('Projects', 'Post Type General Name', 'hassam-portfolio'),
            'singular_name'         => _x('Project', 'Post Type Singular Name', 'hassam-portfolio'),
            'menu_name'             => __('Projects (Portfolio)', 'hassam-portfolio'),
            'name_admin_bar'        => __('Project', 'hassam-portfolio'),
            'archives'              => __('Project Archives', 'hassam-portfolio'),
            'attributes'            => __('Project Attributes', 'hassam-portfolio'),
            'parent_item_colon'     => __('Parent Project:', 'hassam-portfolio'),
            'all_items'             => __('All Projects', 'hassam-portfolio'),
            'add_new_item'          => __('Add New Project', 'hassam-portfolio'),
            'add_new'               => __('Add New', 'hassam-portfolio'),
            'new_item'              => __('New Project', 'hassam-portfolio'),
            'edit_item'             => __('Edit Project', 'hassam-portfolio'),
            'update_item'           => __('Update Project', 'hassam-portfolio'),
            'view_item'             => __('View Project', 'hassam-portfolio'),
            'view_items'            => __('View Projects', 'hassam-portfolio'),
            'search_items'          => __('Search Project', 'hassam-portfolio'),
            'not_found'             => __('Not found', 'hassam-portfolio'),
            'not_found_in_trash'    => __('Not found in Trash', 'hassam-portfolio'),
            'featured_image'        => __('Project Cover Image', 'hassam-portfolio'),
            'set_featured_image'    => __('Set cover image', 'hassam-portfolio'),
            'remove_featured_image' => __('Remove cover image', 'hassam-portfolio'),
            'use_featured_image'    => __('Use as cover image', 'hassam-portfolio'),
        ];

        $args = [
            'label'                 => __('Project', 'hassam-portfolio'),
            'description'           => __('Malik Hassam Ullah Portfolio Projects', 'hassam-portfolio'),
            'labels'                => $labels,
            'supports'              => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'elementor'],
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-portfolio',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => true,
        ];

        register_post_type('portfolio_project', $args);

        // Register Messages Post Type for Contact Form Inquiries
        $message_labels = [
            'name'               => __('Messages', 'hassam-portfolio'),
            'singular_name'      => __('Message', 'hassam-portfolio'),
            'menu_name'          => __('Messages / Inquiries', 'hassam-portfolio'),
            'all_items'          => __('All Messages', 'hassam-portfolio'),
            'view_item'          => __('View Message', 'hassam-portfolio'),
            'search_items'       => __('Search Messages', 'hassam-portfolio'),
            'not_found'          => __('No messages found', 'hassam-portfolio'),
        ];

        register_post_type('portfolio_message', [
            'labels'             => $message_labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_position'      => 6,
            'menu_icon'          => 'dashicons-email-alt',
            'supports'           => ['title', 'editor', 'custom-fields'],
            'capabilities'       => [
                'create_posts' => false, // Only created via contact form
            ],
            'map_meta_cap'       => true,
        ]);
    }

    /**
     * Register Taxonomy for Project Categories
     */
    public function register_taxonomies() {
        $labels = [
            'name'                       => _x('Categories', 'Taxonomy General Name', 'hassam-portfolio'),
            'singular_name'              => _x('Category', 'Taxonomy Singular Name', 'hassam-portfolio'),
            'menu_name'                  => __('Categories', 'hassam-portfolio'),
            'all_items'                  => __('All Categories', 'hassam-portfolio'),
            'parent_item'                => __('Parent Category', 'hassam-portfolio'),
            'parent_item_colon'          => __('Parent Category:', 'hassam-portfolio'),
            'new_item_name'              => __('New Category Name', 'hassam-portfolio'),
            'add_new_item'               => __('Add New Category', 'hassam-portfolio'),
            'edit_item'                  => __('Edit Category', 'hassam-portfolio'),
            'update_item'                => __('Update Category', 'hassam-portfolio'),
            'view_item'                  => __('View Category', 'hassam-portfolio'),
            'separate_items_with_commas' => __('Separate categories with commas', 'hassam-portfolio'),
            'add_or_remove_items'        => __('Add or remove categories', 'hassam-portfolio'),
            'choose_from_most_used'      => __('Choose from the most used', 'hassam-portfolio'),
            'popular_items'              => __('Popular Categories', 'hassam-portfolio'),
            'search_items'               => __('Search Categories', 'hassam-portfolio'),
            'not_found'                  => __('Not Found', 'hassam-portfolio'),
        ];

        $args = [
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
        ];

        register_taxonomy('project_category', ['portfolio_project'], $args);
    }

    /**
     * Custom Meta Boxes for Projects
     */
    public function add_project_meta_boxes() {
        add_meta_box(
            'project_details_meta',
            __('Project Technical Details & Links', 'hassam-portfolio'),
            [$this, 'render_project_meta_box'],
            'portfolio_project',
            'normal',
            'high'
        );
    }

    public function render_project_meta_box($post) {
        wp_nonce_field('save_project_meta_action', 'project_meta_nonce');

        $badge = get_post_meta($post->ID, '_project_badge', true);
        $icon = get_post_meta($post->ID, '_project_icon', true);
        $github = get_post_meta($post->ID, '_project_github', true);
        $demo = get_post_meta($post->ID, '_project_demo', true);
        $tags = get_post_meta($post->ID, '_project_tags', true);
        $category_slug = get_post_meta($post->ID, '_project_filter_slug', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="project_badge"><?php _e('Badge / Subtitle', 'hassam-portfolio'); ?></label></th>
                <td>
                    <input type="text" id="project_badge" name="project_badge" value="<?php echo esc_attr($badge); ?>" class="regular-text" placeholder="e.g. 3D Graphics / Full-Stack Web">
                    <p class="description"><?php _e('Short tag shown in the top badge of the card.', 'hassam-portfolio'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="project_icon"><?php _e('FontAwesome Icon Class', 'hassam-portfolio'); ?></label></th>
                <td>
                    <input type="text" id="project_icon" name="project_icon" value="<?php echo esc_attr($icon); ?>" class="regular-text" placeholder="e.g. fa-solid fa-cubes-stacked">
                </td>
            </tr>
            <tr>
                <th><label for="project_tags"><?php _e('Tech Stack Tags (Comma Separated)', 'hassam-portfolio'); ?></label></th>
                <td>
                    <input type="text" id="project_tags" name="project_tags" value="<?php echo esc_attr($tags); ?>" class="large-text" placeholder="Python, Pygame, OpenGL, VPython">
                </td>
            </tr>
            <tr>
                <th><label for="project_github"><?php _e('GitHub Repository URL', 'hassam-portfolio'); ?></label></th>
                <td>
                    <input type="url" id="project_github" name="project_github" value="<?php echo esc_attr($github); ?>" class="large-text" placeholder="https://github.com/Malik-Hassam-Ullah/...">
                </td>
            </tr>
            <tr>
                <th><label for="project_demo"><?php _e('Live Demo URL (Optional)', 'hassam-portfolio'); ?></label></th>
                <td>
                    <input type="url" id="project_demo" name="project_demo" value="<?php echo esc_attr($demo); ?>" class="large-text" placeholder="https://...">
                </td>
            </tr>
            <tr>
                <th><label for="project_filter_slug"><?php _e('Filter Category Slug', 'hassam-portfolio'); ?></label></th>
                <td>
                    <select id="project_filter_slug" name="project_filter_slug">
                        <option value="web" <?php selected($category_slug, 'web'); ?>>Web & Django</option>
                        <option value="mobile" <?php selected($category_slug, 'mobile'); ?>>Mobile & AI</option>
                        <option value="game" <?php selected($category_slug, 'game'); ?>>3D & Games</option>
                        <option value="desktop" <?php selected($category_slug, 'desktop'); ?>>Desktop & Multithreading</option>
                        <option value="cms" <?php selected($category_slug, 'cms'); ?>>WordPress & CMS</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_project_meta($post_id) {
        if (!isset($_POST['project_meta_nonce']) || !wp_verify_nonce($_POST['project_meta_nonce'], 'save_project_meta_action')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = ['project_badge', 'project_icon', 'project_tags', 'project_github', 'project_demo', 'project_filter_slug'];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
    }

    /**
     * Admin Dashboard Widget
     */
    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'hassam_portfolio_dashboard_widget',
            __('⚡ Malik Hassam Ullah — Portfolio Hub', 'hassam-portfolio'),
            [$this, 'render_dashboard_widget']
        );
    }

    /**
     * Admin Dashboard Menu & Pages
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Portfolio Hub', 'hassam-portfolio'),
            __('Portfolio Hub', 'hassam-portfolio'),
            'read',
            'portfolio-hub',
            [$this, 'render_admin_hub_page'],
            'dashicons-laptop',
            2
        );
    }

    public function render_admin_hub_page() {
        $project_count = wp_count_posts('portfolio_project')->publish;
        $message_count = wp_count_posts('portfolio_message')->publish;
        $home_page = get_page_by_path('home');
        $home_edit_url = $home_page ? admin_url('post.php?post=' . $home_page->ID . '&action=elementor') : admin_url('edit.php?post_type=page');
        
        $top_projects = get_posts([
            'post_type' => 'portfolio_project',
            'posts_per_page' => 3,
            'orderby' => 'menu_order date',
            'order' => 'ASC'
        ]);

        $recent_messages = get_posts([
            'post_type' => 'portfolio_message',
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        ?>
        <div class="wrap portfolio-admin-wrap">
            <!-- Header Hero -->
            <div class="portfolio-admin-hero">
                <h1>Malik Hassam Ullah <span>Portfolio Hub</span></h1>
                <div class="subtitle">I am a Computer Science Student</div>
                <p class="desc">Computer Science student (7th Semester @ IST, KICSIT) passionate about building robust web applications, 3D graphics, intelligent mobile apps, and scalable backends.</p>
                <div class="portfolio-admin-actions">
                    <a href="<?php echo esc_url($home_edit_url); ?>" class="btn-admin btn-admin-primary">
                        <i class="fa-solid fa-pen-ruler"></i> Edit Front Page with Elementor
                    </a>
                    <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="btn-admin btn-admin-secondary">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> View Live Portfolio
                    </a>
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=portfolio_project')); ?>" class="btn-admin btn-admin-success">
                        <i class="fa-solid fa-plus"></i> Add New Project
                    </a>
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=portfolio_message')); ?>" class="btn-admin btn-admin-secondary">
                        <i class="fa-solid fa-envelope"></i> Inquiries & Messages (<?php echo esc_html($message_count); ?>)
                    </a>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="portfolio-admin-stats">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="fa-solid fa-folder-open"></i></div>
                    <div>
                        <div class="admin-stat-number"><?php echo esc_html($project_count); ?></div>
                        <div class="admin-stat-label">Total Projects Built</div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="fa-solid fa-layer-group"></i></div>
                    <div>
                        <div class="admin-stat-number">12</div>
                        <div class="admin-stat-label">Tech Stack Mastered</div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div>
                        <div class="admin-stat-number">7th</div>
                        <div class="admin-stat-label">Semester @ IST KICSIT</div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="fa-solid fa-award"></i></div>
                    <div>
                        <div class="admin-stat-number">ZRAB</div>
                        <div class="admin-stat-label">WordPress Certified</div>
                    </div>
                </div>
            </div>

            <!-- 2-Column: Skills & Projects -->
            <div class="portfolio-admin-grid">
                <!-- Skills Box -->
                <div class="admin-box">
                    <div class="admin-box-header">
                        <h2><i class="fa-solid fa-code" style="color: #2563eb;"></i> Core Skills & Technologies</h2>
                        <span style="font-size: 0.85rem; color: #64748b;">Top 3 Pillars</span>
                    </div>
                    <div class="admin-skills-list">
                        <div class="admin-skill-item">
                            <div class="admin-skill-item-header">
                                <strong><i class="fa-brands fa-python" style="color: #2563eb; font-size: 1.1rem;"></i> Python & Django</strong>
                                <span class="admin-skill-badge">Advanced 90%</span>
                            </div>
                            <p>Django full-stack development, MySQL ORM, HuggingFace T5 AI models, and Pygame/OpenGL 3D game engines.</p>
                            <div class="admin-progress"><div class="admin-progress-bar" style="width: 90%;"></div></div>
                        </div>

                        <div class="admin-skill-item">
                            <div class="admin-skill-item-header">
                                <strong><i class="fa-brands fa-wordpress" style="color: #2563eb; font-size: 1.1rem;"></i> Web & WordPress</strong>
                                <span class="admin-skill-badge">Expert 92%</span>
                            </div>
                            <p>ZRAB Solutions certified. Elementor custom page builder, responsive HTML5/CSS3, JavaScript, and Bootstrap UI.</p>
                            <div class="admin-progress"><div class="admin-progress-bar" style="width: 92%;"></div></div>
                        </div>

                        <div class="admin-skill-item">
                            <div class="admin-skill-item-header">
                                <strong><i class="fa-brands fa-android" style="color: #2563eb; font-size: 1.1rem;"></i> Android & Systems</strong>
                                <span class="admin-skill-badge">Proficient 85%</span>
                            </div>
                            <p>Android Studio (Java/Kotlin), Firebase cloud sync, Retrofit API calls, C# .NET multithreading, and C++ OOP.</p>
                            <div class="admin-progress"><div class="admin-progress-bar" style="width: 85%;"></div></div>
                        </div>
                    </div>
                </div>

                <!-- Projects Box -->
                <div class="admin-box">
                    <div class="admin-box-header">
                        <h2><i class="fa-solid fa-laptop-code" style="color: #2563eb;"></i> Top Featured Projects</h2>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=portfolio_project')); ?>" class="button button-small">View All (<?php echo esc_html($project_count); ?>)</a>
                    </div>
                    <div class="admin-projects-list">
                        <?php foreach ($top_projects as $p): 
                            $badge = get_post_meta($p->ID, '_project_badge', true) ?: 'Project';
                            $tags = get_post_meta($p->ID, '_project_tags', true);
                            $github = get_post_meta($p->ID, '_project_github', true);
                            $tag_array = $tags ? array_map('trim', explode(',', $tags)) : [];
                        ?>
                        <div class="admin-project-item">
                            <div class="admin-project-item-info">
                                <h4><?php echo esc_html($p->post_title); ?> <span class="admin-skill-badge" style="font-size: 0.72rem; margin-left: 6px;"><?php echo esc_html($badge); ?></span></h4>
                                <p><?php echo esc_html(wp_trim_words($p->post_content, 10, '...')); ?></p>
                                <div class="admin-project-tags">
                                    <?php foreach (array_slice($tag_array, 0, 3) as $t): ?>
                                        <span><?php echo esc_html($t); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="admin-project-actions">
                                <a href="<?php echo esc_url(get_edit_post_link($p->ID)); ?>" class="button button-small" title="Edit in WordPress"><i class="fa-solid fa-pen"></i></a>
                                <?php if ($github): ?>
                                <a href="<?php echo esc_url($github); ?>" target="_blank" class="button button-small" title="View GitHub"><i class="fa-brands fa-github"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Messages Box -->
            <div class="admin-box">
                <div class="admin-box-header">
                    <h2><i class="fa-solid fa-inbox" style="color: #2563eb;"></i> Recent Contact Messages & Inquiries</h2>
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=portfolio_message')); ?>" class="button button-small">View All Inquiries</a>
                </div>
                <?php if (!empty($recent_messages)): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_messages as $m): 
                            $s_name = get_post_meta($m->ID, '_sender_name', true) ?: '-';
                            $s_email = get_post_meta($m->ID, '_sender_email', true) ?: '-';
                            $s_subject = get_post_meta($m->ID, '_sender_subject', true) ?: $m->post_title;
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($s_name); ?></strong></td>
                            <td><a href="mailto:<?php echo esc_attr($s_email); ?>"><?php echo esc_html($s_email); ?></a></td>
                            <td><?php echo esc_html($s_subject); ?></td>
                            <td><?php echo esc_html(wp_trim_words($m->post_content, 12, '...')); ?></td>
                            <td><?php echo esc_html(get_the_date('M j, Y g:i a', $m->ID)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="color: #64748b; font-size: 0.95rem; margin: 0;">No messages received yet. When visitors fill out the contact form on your portfolio website, their messages will appear right here!</p>
                <?php endif; ?>
            </div>

            <!-- Live Site Preview Box -->
            <div class="portfolio-preview-box">
                <div class="admin-box-header" style="border:none; margin-bottom: 12px; padding: 0;">
                    <h2><i class="fa-solid fa-desktop" style="color: #2563eb;"></i> Live Portfolio Frontend Preview</h2>
                    <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="button button-secondary">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Fullscreen
                    </a>
                </div>
                <iframe src="<?php echo esc_url(home_url('/')); ?>" class="preview-frame"></iframe>
            </div>
        </div>
        <?php
    }

    /**
     * Admin Dashboard Widget on Main Screen
     */
    public function render_dashboard_widget() {
        $project_count = wp_count_posts('portfolio_project')->publish;
        $message_count = wp_count_posts('portfolio_message')->publish;
        $home_page = get_page_by_path('home');
        $home_edit_url = $home_page ? admin_url('post.php?post=' . $home_page->ID . '&action=elementor') : admin_url('edit.php?post_type=page');
        ?>
        <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 15px;">
                <h3 style="margin-top:0; color:#0f172a; font-size:1.15rem; font-weight:700;">Welcome, Malik Hassam Ullah 👋</h3>
                <p style="color:#2563eb; font-weight:600; font-size:0.92rem; margin:0 0 8px 0;">Computer Science Student (7th Semester @ IST, KICSIT)</p>
                <p style="color:#64748b; font-size:0.88rem; line-height: 1.5; margin:0 0 16px 0;">Your portfolio is running clean on <strong>Astra Theme</strong> with <strong>Elementor</strong> visual builder.</p>
                <div style="display:flex; gap:10px; flex-wrap: wrap;">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=portfolio-hub')); ?>" class="button button-primary" style="background:#2563eb; border-color:#2563eb; font-weight:600;">
                        <span class="dashicons dashicons-laptop" style="margin-top:2px;"></span> Open Portfolio Hub
                    </a>
                    <a href="<?php echo esc_url($home_edit_url); ?>" class="button" style="font-weight:600;">
                        <span class="dashicons dashicons-edit" style="margin-top:2px;"></span> Edit with Elementor
                    </a>
                    <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="button" style="font-weight:600;">
                        <span class="dashicons dashicons-external" style="margin-top:2px;"></span> View Live Site
                    </a>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; text-align: center;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <strong style="font-size: 20px; color: #2563eb; display: block;"><?php echo esc_html($project_count); ?></strong>
                    <span style="font-size: 11px; color: #64748b;">Projects</span>
                </div>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <strong style="font-size: 20px; color: #2563eb; display: block;">12</strong>
                    <span style="font-size: 11px; color: #64748b;">Tech Stack</span>
                </div>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <strong style="font-size: 20px; color: #2563eb; display: block;">7th</strong>
                    <span style="font-size: 11px; color: #64748b;">Semester</span>
                </div>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <strong style="font-size: 20px; color: #2563eb; display: block;">ZRAB</strong>
                    <span style="font-size: 11px; color: #64748b;">Certified</span>
                </div>
            </div>
        </div>
        <?php
    }

    public function enqueue_assets() {
        wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], '6.5.1');
        wp_enqueue_style('hassam-fonts', 'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&family=Fira+Code:wght@400;500;600&display=swap', [], null);
        wp_enqueue_style('hassam-portfolio-style', HASSAM_PORTFOLIO_URL . 'assets/css/portfolio.css', [], HASSAM_PORTFOLIO_VERSION);
        wp_enqueue_script('hassam-portfolio-script', HASSAM_PORTFOLIO_URL . 'assets/js/portfolio.js', ['jquery'], HASSAM_PORTFOLIO_VERSION, true);
        wp_localize_script('hassam-portfolio-script', 'hassam_data', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'theme_url' => HASSAM_PORTFOLIO_URL
        ]);
    }

    public function enqueue_admin_assets() {
        wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], '6.5.1');
        wp_enqueue_style('hassam-admin-style', HASSAM_PORTFOLIO_URL . 'assets/css/portfolio-admin.css', [], HASSAM_PORTFOLIO_VERSION);
    }

    /**
     * Render Hero Section
     */
    public function render_hero() {
        ob_start();
        ?>
        <section id="home" class="hero-section">
            <div class="container">
                <div class="hero-clean-wrap">
                    <h1 class="hero-title">
                        Hi, I'm <span class="gradient-text">Malik Hassam Ullah</span>
                    </h1>

                    <h2 class="hero-subtitle">
                        I am a Computer Science Student
                    </h2>

                    <p class="hero-description">
                        Computer Science student (7th Semester @ IST, KICSIT) passionate about building robust web applications, 3D graphics, intelligent mobile apps, and scalable backends.
                    </p>

                    <div class="hero-cta-group">
                        <a href="#projects" class="btn btn-primary">
                            <span>Explore Projects</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        <a href="#contact" class="btn btn-outline">
                            <i class="fa-solid fa-envelope"></i>
                            <span>Contact Me</span>
                        </a>
                        <a href="https://github.com/Malik-Hassam-Ullah" target="_blank" rel="noopener" class="btn btn-glass">
                            <i class="fa-brands fa-github"></i>
                            <span>GitHub Profile</span>
                        </a>
                    </div>

                    <div class="social-links-bar">
                        <span class="social-label">Connect:</span>
                        <a href="https://github.com/Malik-Hassam-Ullah" target="_blank" rel="noopener" class="social-icon" title="GitHub">
                            <i class="fa-brands fa-github"></i>
                        </a>
                        <a href="https://linkedin.com/in/malik-hassam-6383b1287" target="_blank" rel="noopener" class="social-icon" title="LinkedIn">
                            <i class="fa-brands fa-linkedin"></i>
                        </a>
                        <a href="mailto:hassammalik638@gmail.com" class="social-icon" title="Email">
                            <i class="fa-solid fa-envelope"></i>
                        </a>
                        <a href="tel:03065520204" class="social-icon" title="Phone">
                            <i class="fa-solid fa-phone"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Stats Section
     */
    public function render_stats() {
        ob_start();
        ?>
        <div class="container" style="margin-top: -30px; margin-bottom: 60px;">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-folder-open"></i></div>
                    <div class="stat-info">
                        <h3 class="stat-number" data-target="9">9</h3>
                        <p class="stat-label">Major Projects Built</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div>
                    <div class="stat-info">
                        <h3 class="stat-number" data-target="12">12</h3>
                        <p class="stat-label">Tech Stack Mastered</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div class="stat-info">
                        <h3 class="stat-number">7th</h3>
                        <p class="stat-label">Semester @ IST KICSIT</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-award"></i></div>
                    <div class="stat-info">
                        <h3 class="stat-number">ZRAB</h3>
                        <p class="stat-label">WordPress Certified</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render About Section
     */
    public function render_about() {
        ob_start();
        ?>
        <section id="about" class="section">
            <div class="container">
                <div class="section-header text-center">
                    <h2 class="section-title">About <span class="gradient-text">Me</span></h2>
                    <div class="header-divider"></div>
                </div>

                <div class="about-grid">
                    <div class="about-card glass-card">
                        <div class="card-icon"><i class="fa-solid fa-user-gear"></i></div>
                        <h3>Professional Profile</h3>
                        <p>
                            I am a <strong>Computer Science student currently in my 7th semester</strong> at the <em>Institute of Space Technology (KICSIT Campus)</em>. I specialize in designing and engineering practical, modular software applications spanning desktop, mobile, full-stack web, and 3D graphics domains.
                        </p>
                        <p>
                            With hands-on experience in <strong>Python, C#, C++, Android, Django, and WordPress</strong>, I take pride in building robust applications with clean architecture, object-oriented principles, and separated logic/rendering.
                        </p>

                        <div class="info-chips">
                            <div class="info-chip"><i class="fa-solid fa-location-dot"></i> Kahuta, Rawalpindi, PK</div>
                            <div class="info-chip"><i class="fa-solid fa-envelope"></i> hassammalik638@gmail.com</div>
                            <div class="info-chip"><i class="fa-solid fa-phone"></i> +92 306 5520204</div>
                            <div class="info-chip"><i class="fa-solid fa-language"></i> English, Urdu, Punjabi</div>
                        </div>
                    </div>

                    <div class="about-highlights">
                        <div class="highlight-item glass-card">
                            <div class="highlight-icon"><i class="fa-solid fa-cubes"></i></div>
                            <div>
                                <h4>Modular & Clean Architecture</h4>
                                <p>Building software with separated rendering, UI, logic layers, and reusable OOP components.</p>
                            </div>
                        </div>

                        <div class="highlight-item glass-card">
                            <div class="highlight-icon"><i class="fa-solid fa-brain"></i></div>
                            <div>
                                <h4>AI & NLP Integration</h4>
                                <p>Connecting Android apps to custom Python backend services utilizing HuggingFace T5 summarizer models.</p>
                            </div>
                        </div>

                        <div class="highlight-item glass-card">
                            <div class="highlight-icon"><i class="fa-solid fa-vr-cardboard"></i></div>
                            <div>
                                <h4>3D Graphics & Game Loops</h4>
                                <p>Crafting 3D Tetris games with Pygame, OpenGL, VPython, collision detection, and audio engines.</p>
                            </div>
                        </div>

                        <div class="highlight-item glass-card">
                            <div class="highlight-icon"><i class="fa-solid fa-mobile-screen-button"></i></div>
                            <div>
                                <h4>Full-Stack & Mobile Ecosystems</h4>
                                <p>Developing Android solutions with Firebase & Retrofit alongside Django + MySQL web applications.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Skills Section
     */
    /**
     * Render Skills Section (Top 3 Core Skillsets)
     */
    public function render_skills() {
        ob_start();
        ?>
        <section id="skills" class="section bg-alt">
            <div class="container">
                <div class="section-header text-center">
                    <h2 class="section-title">Skills & <span class="gradient-text">Technologies</span></h2>
                    <p style="color: var(--text-muted); max-width: 600px; margin: 10px auto 0;">Core competencies honed across academic software engineering, mobile development, and full-stack projects.</p>
                    <div class="header-divider" style="margin-top: 15px;"></div>
                </div>

                <div class="skills-grid">
                    <!-- Skill 1: Python & Full-Stack -->
                    <div class="skill-card glass-card">
                        <div class="skill-header">
                            <i class="fa-brands fa-python skill-icon icon-python"></i>
                            <div>
                                <h3>Python & Django</h3>
                                <span class="skill-level">Advanced</span>
                            </div>
                        </div>
                        <p>Django full-stack development, MySQL ORM, HuggingFace T5 AI models, and Pygame/OpenGL 3D game engines.</p>
                        <div class="progress-bar"><div class="progress" style="width: 90%;"></div></div>
                    </div>

                    <!-- Skill 2: Modern Web & WordPress -->
                    <div class="skill-card glass-card">
                        <div class="skill-header">
                            <i class="fa-brands fa-wordpress skill-icon icon-wordpress"></i>
                            <div>
                                <h3>Web & WordPress</h3>
                                <span class="skill-level">Certified Expert</span>
                            </div>
                        </div>
                        <p>ZRAB Solutions certified. Elementor custom page builder, responsive HTML5/CSS3, JavaScript, and Bootstrap UI.</p>
                        <div class="progress-bar"><div class="progress" style="width: 92%;"></div></div>
                    </div>

                    <!-- Skill 3: Mobile & Systems -->
                    <div class="skill-card glass-card">
                        <div class="skill-header">
                            <i class="fa-brands fa-android skill-icon icon-android"></i>
                            <div>
                                <h3>Android & Systems</h3>
                                <span class="skill-level">Proficient</span>
                            </div>
                        </div>
                        <p>Android Studio (Java/Kotlin), Firebase cloud sync, Retrofit API calls, C# .NET multithreading, and C++ OOP.</p>
                        <div class="progress-bar"><div class="progress" style="width: 85%;"></div></div>
                    </div>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Featured Projects Section (Top 3 Featured)
     */
    public function render_projects($atts = []) {
        $query_args = [
            'post_type'      => 'portfolio_project',
            'posts_per_page' => 3,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order date',
            'order'          => 'ASC'
        ];

        $projects = new WP_Query($query_args);
        ob_start();
        ?>
        <section id="projects" class="section">
            <div class="container">
                <div class="section-header text-center">
                    <h2 class="section-title">Featured <span class="gradient-text">Projects</span></h2>
                    <p style="color: var(--text-muted); max-width: 600px; margin: 10px auto 0;">Hand-picked highlight projects demonstrating core expertise in 3D graphics, mobile development, and full-stack engineering.</p>
                    <div class="header-divider" style="margin-top: 15px;"></div>
                </div>

                <div class="projects-grid">
                    <?php if ($projects->have_posts()): ?>
                        <?php while ($projects->have_posts()): $projects->the_post(); 
                            $badge = get_post_meta(get_the_ID(), '_project_badge', true) ?: 'Project';
                            $icon = get_post_meta(get_the_ID(), '_project_icon', true) ?: 'fa-solid fa-code';
                            $tags = get_post_meta(get_the_ID(), '_project_tags', true);
                            $github = get_post_meta(get_the_ID(), '_project_github', true) ?: 'https://github.com/Malik-Hassam-Ullah';
                            $cat_slug = get_post_meta(get_the_ID(), '_project_filter_slug', true) ?: 'web';
                            $tag_array = $tags ? array_map('trim', explode(',', $tags)) : [];
                        ?>
                        <div class="project-card glass-card" data-category="<?php echo esc_attr($cat_slug); ?>">
                            <div class="project-badge"><?php echo esc_html($badge); ?></div>
                            <div class="project-icon-top"><i class="<?php echo esc_attr($icon); ?>"></i></div>
                            <h3 class="project-title"><?php the_title(); ?></h3>
                            <div class="project-desc">
                                <?php the_excerpt(); ?>
                            </div>
                            <?php if (!empty($tag_array)): ?>
                            <div class="project-tags">
                                <?php foreach ($tag_array as $tag): ?>
                                    <span><?php echo esc_html($tag); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <div class="project-footer">
                                <a href="<?php echo esc_url($github); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                                    <i class="fa-brands fa-github"></i> View on GitHub
                                </a>
                                <?php if (current_user_can('edit_posts')): ?>
                                <a href="<?php echo esc_url(get_edit_post_link(get_the_ID())); ?>" class="project-link" title="Edit in Dashboard">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; wp_reset_postdata(); ?>
                    <?php endif; ?>
                </div>

                <div style="text-align: center; margin-top: 45px;">
                    <a href="https://github.com/Malik-Hassam-Ullah" target="_blank" rel="noopener" class="btn btn-outline" style="padding: 12px 30px;">
                        <i class="fa-brands fa-github"></i>
                        <span>See All Projects on GitHub</span>
                    </a>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Education & Experience Section
     */
    public function render_experience() {
        ob_start();
        ?>
        <section id="experience" class="section bg-alt">
            <div class="container">
                <div class="section-header text-center">
                    <h2 class="section-title">Education & <span class="gradient-text">Experience</span></h2>
                    <div class="header-divider"></div>
                </div>

                <div class="timeline-grid">
                    <!-- Education Timeline -->
                    <div class="timeline-column">
                        <h3 class="timeline-column-title"><i class="fa-solid fa-graduation-cap"></i> Education</h3>
                        <div class="timeline">
                            <div class="timeline-item glass-card">
                                <div class="timeline-dot"></div>
                                <div class="timeline-date">2023 – 2027 (Expected)</div>
                                <h4 class="timeline-title">Bachelor of Science in Computer Science</h4>
                                <h5 class="timeline-sub">Institute of Space Technology, KICSIT Campus</h5>
                                <span class="badge badge-primary">Current 7th Semester</span>
                                <p class="timeline-desc">
                                    Focusing on Data Structures, Object-Oriented Programming, Operating Systems, Web Technologies, Database Systems, Multithreading, and AI/NLP models.
                                </p>
                            </div>

                            <div class="timeline-item glass-card">
                                <div class="timeline-dot"></div>
                                <div class="timeline-date">2021 – 2023</div>
                                <h4 class="timeline-title">FSc Pre-Engineering</h4>
                                <h5 class="timeline-sub">KIT College, Kahuta</h5>
                                <p class="timeline-desc">
                                    Rigorous foundation in Higher Mathematics, Physics, and analytical problem-solving skills.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Experience Timeline -->
                    <div class="timeline-column">
                        <h3 class="timeline-column-title"><i class="fa-solid fa-briefcase"></i> Experience & Certifications</h3>
                        <div class="timeline">
                            <div class="timeline-item glass-card">
                                <div class="timeline-dot"></div>
                                <div class="timeline-date">2023 – Present</div>
                                <h4 class="timeline-title">Student Developer & Project Builder</h4>
                                <h5 class="timeline-sub">Academic & Portfolio Projects | Kahuta, Rawalpindi</h5>
                                <p class="timeline-desc">
                                    • Architected and coded modular software across C++, Python, C#, Android, HTML/CSS, Django, and MySQL.<br>
                                    • Designed separated logic, rendering, UI, audio, and reusable code structures for 3D games, NLP servers, and desktop tools.
                                </p>
                            </div>

                            <div class="timeline-item glass-card">
                                <div class="timeline-dot"></div>
                                <div class="timeline-date">Certification</div>
                                <h4 class="timeline-title">WordPress Developer Course</h4>
                                <h5 class="timeline-sub">ZRAB Solutions</h5>
                                <span class="badge badge-success"><i class="fa-solid fa-certificate"></i> Certified</span>
                                <p class="timeline-desc">
                                    Mastered WordPress installation, custom page building, business website architecture, e-commerce solutions, forms, and CMS administration.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Contact Section
     */
    public function render_contact() {
        ob_start();
        ?>
        <section id="contact" class="section">
            <div class="container">
                <div class="section-header text-center">
                    <h2 class="section-title">Get In <span class="gradient-text">Touch</span></h2>
                    <div class="header-divider"></div>
                </div>

                <div class="contact-grid">
                    <div class="contact-info-wrapper">
                        <div class="contact-card glass-card">
                            <div class="contact-icon"><i class="fa-solid fa-envelope"></i></div>
                            <div>
                                <h4>Email Me</h4>
                                <p><a href="mailto:hassammalik638@gmail.com" class="contact-link">hassammalik638@gmail.com</a></p>
                                <button class="copy-btn" data-copy="hassammalik638@gmail.com"><i class="fa-solid fa-copy"></i> Copy Email</button>
                            </div>
                        </div>

                        <div class="contact-card glass-card">
                            <div class="contact-icon"><i class="fa-solid fa-phone"></i></div>
                            <div>
                                <h4>Call / WhatsApp</h4>
                                <p><a href="tel:03065520204" class="contact-link">+92 306 5520204</a></p>
                                <button class="copy-btn" data-copy="03065520204"><i class="fa-solid fa-copy"></i> Copy Number</button>
                            </div>
                        </div>

                        <div class="contact-card glass-card">
                            <div class="contact-icon"><i class="fa-solid fa-location-dot"></i></div>
                            <div>
                                <h4>Location</h4>
                                <p>Kahuta, Rawalpindi, Punjab, Pakistan</p>
                            </div>
                        </div>

                        <div class="contact-card glass-card">
                            <div class="contact-icon"><i class="fa-solid fa-globe"></i></div>
                            <div>
                                <h4>Online Profiles</h4>
                                <div class="social-btn-group">
                                    <a href="https://github.com/Malik-Hassam-Ullah" target="_blank" rel="noopener" class="btn btn-sm btn-outline">
                                        <i class="fa-brands fa-github"></i> GitHub
                                    </a>
                                    <a href="https://linkedin.com/in/malik-hassam-6383b1287" target="_blank" rel="noopener" class="btn btn-sm btn-outline">
                                        <i class="fa-brands fa-linkedin"></i> LinkedIn
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="contact-form-wrapper glass-card">
                        <h3>Send Me a Message</h3>
                        <form id="contactForm" class="contact-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                            <input type="hidden" name="action" value="submit_portfolio_contact">
                            <div class="form-group">
                                <label for="formName">Your Name</label>
                                <input type="text" id="formName" name="name" class="form-control" placeholder="John Doe" required>
                            </div>
                            <div class="form-group">
                                <label for="formEmail">Your Email</label>
                                <input type="email" id="formEmail" name="email" class="form-control" placeholder="john@example.com" required>
                            </div>
                            <div class="form-group">
                                <label for="formSubject">Subject</label>
                                <input type="text" id="formSubject" name="subject" class="form-control" placeholder="Project Inquiry / Job Opportunity" required>
                            </div>
                            <div class="form-group">
                                <label for="formMessage">Message</label>
                                <textarea id="formMessage" name="message" rows="5" class="form-control" placeholder="Hello Hassam, I'd like to discuss a project..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa-solid fa-paper-plane"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <div id="toastContainer" class="toast-container"></div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Full Portfolio
     */
    public function render_full_portfolio() {
        return $this->render_hero() .
               $this->render_stats() .
               $this->render_about() .
               $this->render_skills() .
               $this->render_projects() .
               $this->render_experience() .
               $this->render_contact();
    }
}

// Initialize
HassamPortfolioCore::instance();

