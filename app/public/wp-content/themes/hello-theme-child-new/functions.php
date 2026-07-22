<?php
/**
 * Hello Elementor Child Theme functions.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '1.0.0' );

/**
 * Load child theme stylesheet.
 */
function hello_elementor_child_scripts_styles() {
    wp_enqueue_style(
        'hello-elementor-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        [],
        filemtime( get_stylesheet_directory() . '/style.css' )
    );
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );

/**
 * Allow SVG uploads.
 */
function hello_elementor_child_allow_svg_uploads( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'hello_elementor_child_allow_svg_uploads' );

/**
 * Fix SVG preview size in media library.
 */
function hello_elementor_child_fix_svg_preview() {
    echo '<style>
        .attachment-266x266, .thumbnail img {
            width: 100% !important;
            height: auto !important;
        }
    </style>';
}
add_action( 'admin_head', 'hello_elementor_child_fix_svg_preview' );

/**
 * Automatically add missing alt attributes to images in content.
 */
function hello_elementor_child_add_missing_alt_tags( $content ) {
    // Find all <img> tags.
    preg_match_all( '/<img[^>]+>/i', $content, $images );

    if ( empty( $images[0] ) ) {
        return $content;
    }

    foreach ( $images[0] as $img ) {
        // Skip if the img already has an alt attribute.
        if ( false !== strpos( $img, 'alt=' ) ) {
            continue;
        }

        // Extract src.
        if ( preg_match( '/src="([^"]+)"/', $img, $src ) && ! empty( $src[1] ) ) {
            $alt_text = pathinfo( $src[1], PATHINFO_FILENAME );
            $alt_text = str_replace( array( '-', '_' ), ' ', $alt_text );
            $alt_text = trim( $alt_text );

            // Build a new img tag with alt attribute injected before src.
            $new_img = preg_replace(
                '/<img(.*?)src="/',
                '<img$1alt="' . esc_attr( $alt_text ) . '" src="',
                $img
            );

            // Replace original tag with updated one.
            $content = str_replace( $img, $new_img, $content );
        }
    }

    return $content;
}

add_filter( 'the_content', 'hello_elementor_child_add_missing_alt_tags' );
add_filter( 'widget_text', 'hello_elementor_child_add_missing_alt_tags' );
add_filter( 'widget_text_content', 'hello_elementor_child_add_missing_alt_tags' );

add_filter( 'wpcf7_validate_configuration', '__return_false' );


// Remove auto <p> tags from WordPress content
remove_filter('the_content', 'wpautop');

/**
 * Keep raw code intact on leadership page (no smart quote conversion).
 */
function hello_elementor_child_disable_texturize_for_leadership( $run_texturize ) {
    if ( is_admin() ) {
        return $run_texturize;
    }

    if ( is_page( 'leadership' ) ) {
        return false;
    }

    return $run_texturize;
}
add_filter( 'run_wptexturize', 'hello_elementor_child_disable_texturize_for_leadership' );

/**
 * Leadership page popup script (loaded in footer).
 */
function hello_elementor_child_enqueue_leadership_script() {
    if ( is_page( 'leadership' ) ) {
        wp_enqueue_script(
            'hello-leadership-popup',
            get_stylesheet_directory_uri() . '/assets/js/leadership-popup.js',
            array(),
            filemtime( get_stylesheet_directory() . '/assets/js/leadership-popup.js' ),
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_leadership_script', 30 );

/*  Register Custom Post Type for Videos */
// 1. Register Custom Post Type for Videos
function create_video_post_type() {
    register_post_type('youtube_video',
        array(
            'labels' => array(
                'name' => __('Videos'),
                'singular_name' => __('Video')
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'thumbnail'), // Removed 'custom-fields' as we are building a better one
            'menu_icon' => 'dashicons-video-alt3'
        )
    );
}
add_action('init', 'create_video_post_type');

// 2. Create the Custom Meta Box for the YouTube Link
function add_youtube_link_meta_box() {
    add_meta_box(
        'youtube_link_box',          // Unique ID
        'YouTube Video Link',        // Box title
        'youtube_link_meta_box_html',// Content callback
        'youtube_video',             // Post type
        'normal',                    // Context
        'high'                       // Priority
    );
}
add_action('add_meta_boxes', 'add_youtube_link_meta_box');

// HTML for the Meta Box
function youtube_link_meta_box_html($post) {
    $value = get_post_meta($post->ID, '_youtube_video_link', true);
    ?>
    <label for="youtube_video_link"><strong>Paste the full YouTube URL here:</strong></label>
    <input type="url" id="youtube_video_link" name="youtube_video_link" value="<?php echo esc_attr($value); ?>" style="width:100%; padding: 8px; margin-top: 10px;" placeholder="e.g., https://youtu.be/lIXQ9NkHc74" />
    <?php
}

// Save the Meta Box data
function save_youtube_link_meta_box_data($post_id) {
    if (array_key_exists('youtube_video_link', $_POST)) {
        update_post_meta(
            $post_id,
            '_youtube_video_link',
            sanitize_text_field($_POST['youtube_video_link'])
        );
    }
}
add_action('save_post', 'save_youtube_link_meta_box_data');

// 3. Create the Shortcode [video_gallery]
function video_gallery_shortcode() {
    $args = array(
        'post_type' => 'youtube_video',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    );
    $videos = new WP_Query($args);

    ob_start();
    ?>
    
    

    <div class="video-grid-container">
        <?php if ($videos->have_posts()) : while ($videos->have_posts()) : $videos->the_post(); 
            
            // Get the full YouTube link from our new Meta Box
            $youtube_url = get_post_meta(get_the_ID(), '_youtube_video_link', true);
            $youtube_id = '';
            
            // Extract the 11-character ID automatically
            if ($youtube_url) {
                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $youtube_url, $match);
                if (isset($match[1])) {
                    $youtube_id = $match[1];
                }
            }

            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
        ?>
            <div class="video-card" data-ytid="<?php echo esc_attr($youtube_id); ?>">
                <div class="video-thumbnail-wrapper">
                    <?php if($thumbnail_url): ?>
                        <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php the_title(); ?>">
                    <?php elseif($youtube_id): ?>
                        <img src="https://img.youtube.com/vi/<?php echo esc_attr($youtube_id); ?>/maxresdefault.jpg" alt="<?php the_title(); ?>">
                    <?php endif; ?>
                    <div class="play-icon"></div>
                </div>
                <h3 class="video-title"><?php the_title(); ?></h3>
            </div>
        <?php endwhile; wp_reset_postdata(); else : ?>
            <p>No videos found.</p>
        <?php endif; ?>
    </div>

    <div id="videoModal" class="video-modal">
        <div class="video-modal-content">
            <span class="close-modal">&times;</span>
            <iframe id="videoIframe" src="" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById("videoModal");
            var iframe = document.getElementById("videoIframe");
            var closeBtn = document.querySelector(".close-modal");
            var videoCards = document.querySelectorAll(".video-card");

            videoCards.forEach(function(card) {
                card.addEventListener('click', function() {
                    var ytId = this.getAttribute('data-ytid');
                    if(ytId) {
                        iframe.src = "https://www.youtube.com/embed/" + ytId + "?autoplay=1";
                        modal.style.display = "block";
                    }
                });
            });

            function closeModal() {
                modal.style.display = "none";
                iframe.src = ""; 
            }

            closeBtn.addEventListener('click', closeModal);
            
            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    closeModal();
                }
            });
        });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('video_gallery', 'video_gallery_shortcode');





//  1. ADD THE META BOX FOR EXTERNAL LINKS (Required to input the URLs)
function add_external_link_meta_box() {
    add_meta_box(
        'external_link_box',          
        'External Redirect Link (For Continue Reading Button)',        
        'external_link_meta_box_html',
        'post',                   
        'normal',                    
        'high'                       
    );
}
add_action('add_meta_boxes', 'add_external_link_meta_box');

function external_link_meta_box_html($post) {
    $value = get_post_meta($post->ID, '_external_redirect_url', true);
    ?>
    <label for="external_redirect_url"><strong>Paste the URL to your other website here:</strong></label>
    <p><em>If left blank, the button will default to the standard WordPress post page.</em></p>
    <input type="url" id="external_redirect_url" name="external_redirect_url" value="<?php echo esc_attr($value); ?>" style="width:100%; padding: 8px; margin-top: 5px;" placeholder="e.g., https://myotherwebsite.com/article-1" />
    <?php
}

function save_external_link_meta_box_data($post_id) {
    if (array_key_exists('external_redirect_url', $_POST)) {
        update_post_meta(
            $post_id,
            '_external_redirect_url',
            esc_url_raw($_POST['external_redirect_url'])
        );
    }
}
add_action('save_post', 'save_external_link_meta_box_data');


// =========================================================================
// 2. HELPER FUNCTION: GENERATES POST HTML (All Posts Use Same Layout)
// =========================================================================

function cbl_generate_posts_html($query) {
    ob_start();
    
    // Open the grid container for all posts
    echo '<div class="cbl-grid">';
    
    while ($query->have_posts()) : $query->the_post(); 
        $title = get_the_title();
        $date = get_the_date('j F Y');
        $excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 18, '...');
        $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'large');
        
        if (!$thumbnail) {
            $thumbnail = 'https://via.placeholder.com/800x450/222222/ffffff?text=No+Image';
        }

        // --- SMART LINK LOGIC ---
        $default_link = get_permalink();
        $external_link = get_post_meta(get_the_ID(), '_external_redirect_url', true);
        
        $link = !empty($external_link) ? $external_link : $default_link;
        $target = !empty($external_link) ? 'target="_blank" rel="noopener noreferrer"' : '';
        // ---------------------------------------------------

        // --- EVERY POST USES THIS IDENTICAL LAYOUT ---
        ?>
        <div class="cbl-grid-item">
            <div class="cbl-grid-image cbl-image">
                <a href="<?php echo esc_url($link); ?>" <?php echo $target; ?>>
                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($title); ?>">
                </a>
            </div>
            <div class="cbl-grid-content">
                <span class="cbl-date"><?php echo esc_html($date); ?></span>
                <h6><a href="<?php echo esc_url($link); ?>" <?php echo $target; ?>><?php echo esc_html($title); ?></a></h6>
                <p class="cbl-excerpt"><?php echo esc_html($excerpt); ?></p>
                
                <a href="<?php echo esc_url($link); ?>" class="cbl-read-more" <?php echo $target; ?>>CONTINUE READING &rsaquo;</a>
            </div>
        </div>
        <?php
        
    endwhile; 
    
    // Close the grid container
    echo '</div>'; 
    
    wp_reset_postdata();
    return ob_get_clean();
}

// =========================================================================
// 3. PAGINATION, AJAX, AND SHORTCODE 
// =========================================================================

function cbl_generate_pagination_html($max_pages, $current_page) {
    if ($max_pages <= 1) return '';
    
    $html = '<div class="cbl-pagination">';
    for ($i = 1; $i <= $max_pages; $i++) {
        $active_class = ($i == $current_page) ? ' active' : '';
        $html .= '<button class="cbl-page-btn' . $active_class . '" data-page="' . $i . '">' . $i . '</button>';
    }
    $html .= '</div>';
    return $html;
}

function cbl_ajax_handler() {
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $posts_per_page = isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : 6; 
    
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC'
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        echo cbl_generate_posts_html($query);
        echo cbl_generate_pagination_html($query->max_num_pages, $paged);
    }
    
    wp_die(); 
}
add_action('wp_ajax_cbl_load_page', 'cbl_ajax_handler');
add_action('wp_ajax_nopriv_cbl_load_page', 'cbl_ajax_handler');

function custom_blog_layout_shortcode($atts) {
    $atts = shortcode_atts(array('posts' => 9), $atts, 'custom_blog_layout'); 
    $posts_per_page = intval($atts['posts']);

    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged'          => 1, 
        'orderby'        => 'date',
        'order'          => 'DESC'
    );
    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>No posts found.</p>';
    }

    ob_start();
    ?>

    <div class="cbl-wrapper" id="cbl-ajax-wrapper" data-posts="<?php echo esc_attr($posts_per_page); ?>">
        <div id="cbl-ajax-content">
            <?php 
            echo cbl_generate_posts_html($query); 
            echo cbl_generate_pagination_html($query->max_num_pages, 1);
            ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('cbl-page-btn')) {
                    e.preventDefault();
                    
                    if(e.target.classList.contains('active')) return;
                    
                    var page = e.target.getAttribute('data-page');
                    var wrapper = document.getElementById('cbl-ajax-wrapper');
                    var container = document.getElementById('cbl-ajax-content');
                    var postsPerPage = wrapper.getAttribute('data-posts');
                    
                    container.style.opacity = '0.4';
                    
                    var formData = new FormData();
                    formData.append('action', 'cbl_load_page');
                    formData.append('paged', page);
                    formData.append('posts_per_page', postsPerPage);
                    
                    fetch('<?php echo esc_url(admin_url("admin-ajax.php")); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        container.innerHTML = data;
                        container.style.opacity = '1';
                        wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    })
                    .catch(error => {
                        console.error('Error fetching posts:', error);
                        container.style.opacity = '1';
                    });
                }
            });
        });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('custom_blog_layout', 'custom_blog_layout_shortcode');
/**
 * Register Testimonial Custom Post Type
 */
function hello_child_register_testimonial_cpt() {
    $labels = array(
        'name'                  => _x('Testimonials', 'Post Type General Name', 'hello-elementor-child'),
        'singular_name'         => _x('Testimonial', 'Post Type Singular Name', 'hello-elementor-child'),
        'menu_name'             => __('Testimonials', 'hello-elementor-child'),
        'name_admin_bar'        => __('Testimonial', 'hello-elementor-child'),
        'archives'              => __('Testimonial Archives', 'hello-elementor-child'),
        'attributes'            => __('Testimonial Attributes', 'hello-elementor-child'),
        'parent_item_colon'     => __('Parent Testimonial:', 'hello-elementor-child'),
        'all_items'             => __('All Testimonials', 'hello-elementor-child'),
        'add_new_item'          => __('Add New Testimonial', 'hello-elementor-child'),
        'add_new'               => __('Add New', 'hello-elementor-child'),
        'new_item'              => __('New Testimonial', 'hello-elementor-child'),
        'edit_item'             => __('Edit Testimonial', 'hello-elementor-child'),
        'update_item'           => __('Update Testimonial', 'hello-elementor-child'),
        'view_item'             => __('View Testimonial', 'hello-elementor-child'),
        'view_items'            => __('View Testimonials', 'hello-elementor-child'),
        'search_items'          => __('Search Testimonial', 'hello-elementor-child'),
        'not_found'             => __('No testimonials found.', 'hello-elementor-child'),
        'not_found_in_trash'    => __('No testimonials found in Trash.', 'hello-elementor-child'),
        'featured_image'        => __('Client Photo', 'hello-elementor-child'),
        'set_featured_image'    => __('Set client photo', 'hello-elementor-child'),
        'remove_featured_image' => __('Remove client photo', 'hello-elementor-child'),
        'use_featured_image'    => __('Use as client photo', 'hello-elementor-child'),
        'insert_into_item'      => __('Insert into testimonial', 'hello-elementor-child'),
        'uploaded_to_this_item' => __('Uploaded to this testimonial', 'hello-elementor-child'),
        'items_list'            => __('Testimonials list', 'hello-elementor-child'),
        'items_list_navigation' => __('Testimonials list navigation', 'hello-elementor-child'),
        'filter_items_list'     => __('Filter testimonials list', 'hello-elementor-child'),
    );

    $args = array(
        'label'                 => __('Testimonial', 'hello-elementor-child'),
        'description'           => __('Client testimonials and reviews', 'hello-elementor-child'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions'),
        'taxonomies'            => array(),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-testimonial',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'show_in_rest'          => true,
        'rewrite'               => array('slug' => 'testimonials'),
        'capability_type'       => 'post',
    );

    register_post_type('testimonial', $args);
}
add_action('init', 'hello_child_register_testimonial_cpt');
/**
 * Testimonial custom fields: customer name and designation
 */
function hello_child_testimonial_customer_meta_box() {
    add_meta_box(
        'testimonial_customer_details',
        __('Testimonial Details', 'hello-elementor-child'),
        'hello_child_render_testimonial_customer_meta_box',
        'testimonial',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'hello_child_testimonial_customer_meta_box');

function hello_child_render_testimonial_customer_meta_box($post) {
    wp_nonce_field('hello_child_save_testimonial_customer_meta_box', 'hello_child_testimonial_nonce');

    $customer_name = get_post_meta($post->ID, '_testimonial_customer_name', true);
    $designation   = get_post_meta($post->ID, '_testimonial_customer_designation', true);
    ?>
    <p>
        <label for="testimonial_customer_name"><strong><?php esc_html_e('Customer Name', 'hello-elementor-child'); ?></strong></label><br>
        <input type="text" id="testimonial_customer_name" name="testimonial_customer_name" value="<?php echo esc_attr($customer_name); ?>" style="width:100%;" placeholder="Enter customer name" />
    </p>
    <p>
        <label for="testimonial_customer_designation"><strong><?php esc_html_e('Designation', 'hello-elementor-child'); ?></strong></label><br>
        <input type="text" id="testimonial_customer_designation" name="testimonial_customer_designation" value="<?php echo esc_attr($designation); ?>" style="width:100%;" placeholder="Enter designation" />
    </p>
    <?php
}

function hello_child_save_testimonial_customer_meta_box($post_id) {
    if (!isset($_POST['hello_child_testimonial_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['hello_child_testimonial_nonce'])), 'hello_child_save_testimonial_customer_meta_box')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['testimonial_customer_name'])) {
        update_post_meta(
            $post_id,
            '_testimonial_customer_name',
            sanitize_text_field(wp_unslash($_POST['testimonial_customer_name']))
        );
    }

    if (isset($_POST['testimonial_customer_designation'])) {
        update_post_meta(
            $post_id,
            '_testimonial_customer_designation',
            sanitize_text_field(wp_unslash($_POST['testimonial_customer_designation']))
        );
    }
}
add_action('save_post_testimonial', 'hello_child_save_testimonial_customer_meta_box');
/**
 * Testimonial date custom field
 */
function hello_child_testimonial_date_meta_box() {
    add_meta_box(
        'testimonial_date_details',
        __('Testimonial Date', 'hello-elementor-child'),
        'hello_child_render_testimonial_date_meta_box',
        'testimonial',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'hello_child_testimonial_date_meta_box');

function hello_child_render_testimonial_date_meta_box($post) {
    wp_nonce_field('hello_child_save_testimonial_date_meta_box', 'hello_child_testimonial_date_nonce');

    $testimonial_date = get_post_meta($post->ID, '_testimonial_date', true);
    if (!empty($testimonial_date)) {
        $testimonial_date = date('Y-m-d', strtotime($testimonial_date));
    }
    ?>
    <p>
        <label for="testimonial_date"><strong><?php esc_html_e('Date', 'hello-elementor-child'); ?></strong></label><br>
        <input type="date" id="testimonial_date" name="testimonial_date" value="<?php echo esc_attr($testimonial_date); ?>" style="width:100%;" />
    </p>
    <p style="margin:0;color:#666;">
        <?php esc_html_e('Used for the testimonial card date display.', 'hello-elementor-child'); ?>
    </p>
    <?php
}

function hello_child_save_testimonial_date_meta_box($post_id) {
    if (!isset($_POST['hello_child_testimonial_date_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['hello_child_testimonial_date_nonce'])), 'hello_child_save_testimonial_date_meta_box')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['testimonial_date'])) {
        $testimonial_date = sanitize_text_field(wp_unslash($_POST['testimonial_date']));
        if ($testimonial_date !== '') {
            update_post_meta($post_id, '_testimonial_date', $testimonial_date);
        } else {
            delete_post_meta($post_id, '_testimonial_date');
        }
    }
}
add_action('save_post_testimonial', 'hello_child_save_testimonial_date_meta_box');

/**
 * Testimonial tabs shortcode: normal testimonials + video testimonials.
 * Usage: [testimonial_tabs]
 */
function hello_child_extract_youtube_id($youtube_url) {
    $youtube_id = '';

    if ($youtube_url) {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $youtube_url, $match);
        if (isset($match[1])) {
            $youtube_id = $match[1];
        }
    }

    return $youtube_id;
}

function hello_child_format_testimonial_date($raw_date) {
    if (empty($raw_date)) {
        return '';
    }

    $timestamp = strtotime($raw_date);
    if (!$timestamp) {
        return esc_html($raw_date);
    }

    return date_i18n('j M Y', $timestamp);
}

/**
 * Video Testimonial Custom Post Type
 */
function hello_child_register_video_testimonial_cpt() {
    $labels = array(
        'name'               => __('Video Testimonials', 'hello-elementor-child'),
        'singular_name'      => __('Video Testimonial', 'hello-elementor-child'),
        'menu_name'          => __('Video Testimonials', 'hello-elementor-child'),
        'name_admin_bar'     => __('Video Testimonial', 'hello-elementor-child'),
        'add_new'            => __('Add New', 'hello-elementor-child'),
        'add_new_item'       => __('Add New Video Testimonial', 'hello-elementor-child'),
        'new_item'           => __('New Video Testimonial', 'hello-elementor-child'),
        'edit_item'          => __('Edit Video Testimonial', 'hello-elementor-child'),
        'view_item'          => __('View Video Testimonial', 'hello-elementor-child'),
        'all_items'          => __('All Video Testimonials', 'hello-elementor-child'),
        'search_items'       => __('Search Video Testimonials', 'hello-elementor-child'),
        'not_found'          => __('No video testimonials found.', 'hello-elementor-child'),
        'not_found_in_trash' => __('No video testimonials found in Trash.', 'hello-elementor-child'),
    );

    register_post_type('video_testimonial', array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-format-video',
        'supports'           => array('title', 'thumbnail', 'page-attributes'),
        'rewrite'            => array('slug' => 'video-testimonials'),
        'exclude_from_search'=> false,
        'publicly_queryable' => true,
    ));
}
add_action('init', 'hello_child_register_video_testimonial_cpt');

function hello_child_video_testimonial_meta_box() {
    add_meta_box(
        'video_testimonial_details',
        __('Video Testimonial Details', 'hello-elementor-child'),
        'hello_child_render_video_testimonial_meta_box',
        'video_testimonial',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'hello_child_video_testimonial_meta_box');

function hello_child_render_video_testimonial_meta_box($post) {
    wp_nonce_field('hello_child_save_video_testimonial_meta_box', 'hello_child_video_testimonial_nonce');

    $video_link = get_post_meta($post->ID, '_video_testimonial_link', true);
    $image_url  = get_post_meta($post->ID, '_video_testimonial_image_url', true);
    ?>
    <p>
        <label for="video_testimonial_link"><strong><?php esc_html_e('YouTube Link', 'hello-elementor-child'); ?></strong></label><br>
        <input type="url" id="video_testimonial_link" name="video_testimonial_link" value="<?php echo esc_attr($video_link); ?>" style="width:100%;" placeholder="https://youtu.be/..." />
    </p>
    <p>
        <label for="video_testimonial_image_url"><strong><?php esc_html_e('Thumbnail Image URL', 'hello-elementor-child'); ?></strong></label><br>
        <input type="url" id="video_testimonial_image_url" name="video_testimonial_image_url" value="<?php echo esc_attr($image_url); ?>" style="width:100%;" placeholder="https://.../image.webp" />
        <span style="display:block;margin-top:6px;color:#666;"><?php esc_html_e('Used as the card image if no featured image is set.', 'hello-elementor-child'); ?></span>
    </p>
    <?php
}

function hello_child_save_video_testimonial_meta_box($post_id) {
    if (!isset($_POST['hello_child_video_testimonial_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['hello_child_video_testimonial_nonce'])), 'hello_child_save_video_testimonial_meta_box')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['video_testimonial_link'])) {
        $video_link = esc_url_raw(wp_unslash($_POST['video_testimonial_link']));
        if ($video_link !== '') {
            update_post_meta($post_id, '_video_testimonial_link', $video_link);
        } else {
            delete_post_meta($post_id, '_video_testimonial_link');
        }
    }

    if (isset($_POST['video_testimonial_image_url'])) {
        $image_url = esc_url_raw(wp_unslash($_POST['video_testimonial_image_url']));
        if ($image_url !== '') {
            update_post_meta($post_id, '_video_testimonial_image_url', $image_url);
        } else {
            delete_post_meta($post_id, '_video_testimonial_image_url');
        }
    }
}
add_action('save_post_video_testimonial', 'hello_child_save_video_testimonial_meta_box');



function hello_child_testimonial_tabs_shortcode_v1($atts) {
    static $instance = 0;
    $instance++;

    $atts = shortcode_atts(array(
        'testimonial_posts' => -1,
        'video_posts'       => -1,
    ), $atts, 'testimonial_tabs_v1');

    $testimonials_query = new WP_Query(array(
        'post_type'      => 'testimonial',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['testimonial_posts']),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    $videos_query = new WP_Query(array(
        'post_type'      => 'youtube_video',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['video_posts']),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    ob_start();
    ?>
    <div class="hello-testimonial-tabs" data-tabs-instance="<?php echo esc_attr($instance); ?>">
        <style>
.hello-testimonial-tabs{background:#202020;color:#fff;padding:0;width:100%;box-sizing:border-box}
.hello-testimonial-tabs__inner{width:min(100%,1540px);margin:0 auto;padding:0 32px;box-sizing:border-box}
.hello-testimonial-tabs__tablist{display:flex;gap:16px;flex-wrap:wrap;margin:0 0 44px}
.hello-testimonial-tabs__tab{appearance:none;border:1px solid #ff1f8e;background:transparent;color:#fff;border-radius:999px;padding:20px 36px;font-size:18px;font-weight:600;line-height:1;cursor:pointer;transition:background .25s ease,color .25s ease,transform .25s ease,box-shadow .25s ease;min-width:100px;text-align:center}
.hello-testimonial-tabs__tab.is-active{background:#ff1f8e;box-shadow:0 10px 24px #ff1f8e3d}
.hello-testimonial-tabs__tab:hover{transform:translateY(-1px)}
.hello-testimonial-tabs__heading{font-size:clamp(2.6rem,1.5rem + 3.2vw,5rem);line-height:1;letter-spacing:-.04em;margin:0 0 44px;font-weight:500}
.hello-testimonial-tabs__panel{display:none}
.hello-testimonial-tabs__panel.is-active{display:block}
.hello-testimonial-tabs__grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.hello-testimonial-tabs__card{background:#242424;border:1px solid #ffffff24;border-radius:22px;overflow:hidden;min-height:100%;box-shadow:0 18px 50px #00000024}
.hello-testimonial-tabs__card-inner{display:flex;flex-direction:column;height:100%}
.hello-testimonial-tabs__card-body{padding:34px 28px 28px;display:flex;flex-direction:column;gap:18px;flex:1}
.hello-testimonial-tabs__card-title{margin:0;font-size:1.5rem;line-height:1.15;font-weight:600;color:#fff}
.hello-testimonial-tabs__card-text{margin:0;color:#c8c8c8;font-size:1.02rem;line-height:1.7}
.hello-testimonial-tabs__meta{margin-top:auto;display:flex;flex-direction:column;gap:6px}
.hello-testimonial-tabs__name{margin:0;font-size:1.05rem;font-weight:600;color:#fff}
.hello-testimonial-tabs__date,.hello-testimonial-tabs__video-caption{margin:0;color:#efefef;font-size:.98rem;letter-spacing:.02em}
.hello-testimonial-tabs__video-media{position:relative;overflow:hidden;min-height:420px;aspect-ratio:1.18/1}
.hello-testimonial-tabs__video-media img{width:100%;height:100%;object-fit:cover;display:block}
.hello-testimonial-tabs__video-overlay{position:absolute;inset:0;background:linear-gradient(180deg,#0000 40%,#000000b8 100%);display:flex;align-items:center;justify-content:center;z-index:2}
.hello-testimonial-tabs__play{width:74px;height:74px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#ffffff1f;backdrop-filter:blur(8px);transition:transform .25s ease,background .25s ease}
.hello-testimonial-tabs__play img{width:52px;height:52px;object-fit:contain}
.hello-testimonial-tabs__video-link:hover .hello-testimonial-tabs__play{transform:scale(1.06);background:#ffffff2e}
.hello-testimonial-tabs__video-caption-wrap{position:absolute;left:22px;right:22px;bottom:20px;z-index:3}
.hello-testimonial-tabs__video-caption{font-size:1.05rem;line-height:1.4;color:#fff;text-shadow:0 2px 8px #000000d9}
.hello-testimonial-tabs__empty{color:#cfcfcf;padding:20px 0 0}
            @media (max-width: 1100px){
                .hello-testimonial-tabs__grid{grid-template-columns:repeat(2, minmax(0, 1fr));}
            }
            @media (max-width: 767px){
                .hello-testimonial-tabs{padding:0px 0;}
                .hello-testimonial-tabs__inner{padding:0 18px;}
                .hello-testimonial-tabs__tablist{gap:12px;margin-bottom:30px;}
                .hello-testimonial-tabs__tab{min-width:0;flex:1 1 calc(50% - 12px);padding:16px 18px;font-size:16px;}
                .hello-testimonial-tabs__heading{margin-bottom:28px;}
                .hello-testimonial-tabs__grid{grid-template-columns:1fr;gap:14px;}
                .hello-testimonial-tabs__card-body{padding:24px 20px 22px;}
                .hello-testimonial-tabs__card-title{font-size:1.25rem;}
                .hello-testimonial-tabs__video-media{min-height:320px;}
            }
        </style>

        <div class="hello-testimonial-tabs__inner">
            <div class="hello-testimonial-tabs__tablist" role="tablist" aria-label="Testimonials tabs">
                <button class="hello-testimonial-tabs__tab is-active" type="button" role="tab" aria-selected="true" aria-controls="hello-testimonial-panel-<?php echo esc_attr($instance); ?>-testimonial" id="hello-testimonial-tab-<?php echo esc_attr($instance); ?>-testimonial" data-target="testimonial">Testimonial</button>
                <button class="hello-testimonial-tabs__tab" type="button" role="tab" aria-selected="false" aria-controls="hello-testimonial-panel-<?php echo esc_attr($instance); ?>-video" id="hello-testimonial-tab-<?php echo esc_attr($instance); ?>-video" data-target="video">Video Testimonial</button>
            </div>

            <div class="hello-testimonial-tabs__heading-wrap">
                <h2 class="hello-testimonial-tabs__heading" data-heading="testimonial">Testimonial</h2>
                <h2 class="hello-testimonial-tabs__heading" data-heading="video" style="display:none;">Video Testimonial</h2>
            </div>

            <div class="hello-testimonial-tabs__panel is-active" role="tabpanel" id="hello-testimonial-panel-<?php echo esc_attr($instance); ?>-testimonial" aria-labelledby="hello-testimonial-tab-<?php echo esc_attr($instance); ?>-testimonial" data-panel="testimonial">
                <div class="hello-testimonial-tabs__grid">
                    <?php if ($testimonials_query->have_posts()) : ?>
                        <?php while ($testimonials_query->have_posts()) : $testimonials_query->the_post(); ?>
                            <?php
                            $customer_name = get_post_meta(get_the_ID(), '_testimonial_customer_name', true);
                            $designation   = get_post_meta(get_the_ID(), '_testimonial_customer_designation', true);
                            $testimonial_date = get_post_meta(get_the_ID(), '_testimonial_date', true);
                            $formatted_date = hello_child_format_testimonial_date($testimonial_date ?: get_the_date('Y-m-d'));
                            ?>
                            <article class="hello-testimonial-tabs__card">
                                <div class="hello-testimonial-tabs__card-inner">
                                    <div class="hello-testimonial-tabs__card-body">
                                        <h3 class="hello-testimonial-tabs__card-title"><?php the_title(); ?></h3>
                                        <p class="hello-testimonial-tabs__card-text"><?php echo esc_html(get_the_content()); ?></p>
                                        <div class="hello-testimonial-tabs__meta">
                                            <p class="hello-testimonial-tabs__name"><?php echo esc_html($customer_name ?: get_the_title()); ?></p>
                                            <?php if (!empty($designation)) : ?>
                                                <p class="hello-testimonial-tabs__date"><?php echo esc_html($designation); ?></p>
                                            <?php elseif (!empty($formatted_date)) : ?>
                                                <p class="hello-testimonial-tabs__date"><?php echo esc_html($formatted_date); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    <?php else : ?>
                        <p class="hello-testimonial-tabs__empty">No testimonials found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="hello-testimonial-tabs__panel" role="tabpanel" id="hello-testimonial-panel-<?php echo esc_attr($instance); ?>-video" aria-labelledby="hello-testimonial-tab-<?php echo esc_attr($instance); ?>-video" data-panel="video">
                <div class="hello-testimonial-tabs__grid">
                    <?php if ($videos_query->have_posts()) : ?>
                        <?php while ($videos_query->have_posts()) : $videos_query->the_post(); ?>
                            <?php
                            $youtube_url = get_post_meta(get_the_ID(), '_youtube_video_link', true);
                            $youtube_id = hello_child_extract_youtube_id($youtube_url);
                            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
                            if (!$thumbnail_url && $youtube_id) {
                                $thumbnail_url = 'https://img.youtube.com/vi/' . $youtube_id . '/hqdefault.jpg';
                            }
                            ?>
                            <article class="hello-testimonial-tabs__card">
                                <div class="hello-testimonial-tabs__card-inner">
                                    <div class="hello-testimonial-tabs__video-media">
                                        <?php if (!empty($thumbnail_url)) : ?>
                                            <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($youtube_url)) : ?>
                                            <a class="hello-testimonial-tabs__video-link" href="<?php echo esc_url($youtube_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="Open video testimonial: <?php echo esc_attr(get_the_title()); ?>">
                                                <span class="hello-testimonial-tabs__video-overlay">
                                                    <span class="hello-testimonial-tabs__play" aria-hidden="true">
                                                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M8 5.5V18.5L19 12L8 5.5Z" fill="currentColor"></path>
                                                        </svg>
                                                    </span>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                        <div class="hello-testimonial-tabs__video-caption-wrap">
                                            <h3 class="hello-testimonial-tabs__video-caption"><?php the_title(); ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    <?php else : ?>
                        <p class="hello-testimonial-tabs__empty">No video testimonials found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="hello-testimonial-video-modal" data-video-modal hidden>
            <div class="hello-testimonial-video-modal__backdrop" data-video-close></div>
            <div class="hello-testimonial-video-modal__dialog" role="dialog" aria-modal="true" aria-label="Video testimonial player">
                <button type="button" class="hello-testimonial-video-modal__close" data-video-close aria-label="Close video">&times;</button>
                <div class="hello-testimonial-video-modal__frame-wrap">
                    <iframe data-video-iframe src="" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var root = document.querySelector('.hello-testimonial-tabs[data-tabs-instance="<?php echo esc_js($instance); ?>"]');
            if (!root) return;

            var tabs = root.querySelectorAll('.hello-testimonial-tabs__tab');
            var panels = root.querySelectorAll('.hello-testimonial-tabs__panel');
            var headings = root.querySelectorAll('.hello-testimonial-tabs__heading');
            var tracks = root.querySelectorAll('[data-loop-track]');
            var modal = root.querySelector('[data-video-modal]');
            var iframe = root.querySelector('[data-video-iframe]');
            var closeTriggers = root.querySelectorAll('[data-video-close]');

            tracks.forEach(function(track) {
                if (track.getAttribute('data-looped') === '1') {
                    return;
                }

                var items = Array.from(track.children);
                if (!items.length) {
                    return;
                }

                var cloneCount = items.length;
                items.forEach(function(item) {
                    track.appendChild(item.cloneNode(true));
                });

                track.setAttribute('data-looped', '1');
                track.style.setProperty('--hello-marquee-duration', Math.max(18, cloneCount * 7) + 's');
            });

            function activateTab(target) {
                tabs.forEach(function(tab) {
                    var isActive = tab.getAttribute('data-target') === target;
                    tab.classList.toggle('is-active', isActive);
                    tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                panels.forEach(function(panel) {
                    panel.classList.toggle('is-active', panel.getAttribute('data-panel') === target);
                });

                headings.forEach(function(heading) {
                    heading.style.display = heading.getAttribute('data-heading') === target ? 'block' : 'none';
                });
            }

            var scrollLockY = 0;

            function lockPageScroll() {
                scrollLockY = window.scrollY || window.pageYOffset || 0;
                document.body.classList.add('hello-testimonial-scroll-lock');
                document.body.style.top = '-' + scrollLockY + 'px';
            }

            function unlockPageScroll() {
                document.body.classList.remove('hello-testimonial-scroll-lock');
                document.body.style.top = '';
                window.scrollTo(0, scrollLockY);
            }

            function openVideoModal(videoId, videoUrl) {
                if (!modal || !iframe) return;

                var embedUrl = videoId ? ('https://www.youtube.com/embed/' + videoId + '?autoplay=1') : videoUrl;
                if (!embedUrl) return;

                iframe.src = embedUrl;
                modal.hidden = false;
                lockPageScroll();
            }

            function closeVideoModal() {
                if (!modal || !iframe) return;

                iframe.src = '';
                modal.hidden = true;
                unlockPageScroll();
            }

            root.addEventListener('click', function(event) {
                var trigger = event.target.closest('.hello-testimonial-video-trigger');
                if (!trigger) return;

                event.preventDefault();
                openVideoModal(trigger.getAttribute('data-video-id'), trigger.getAttribute('data-video-url'));
            });

            closeTriggers.forEach(function(trigger) {
                trigger.addEventListener('click', closeVideoModal);
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal && !modal.hidden) {
                    closeVideoModal();
                }
            });

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    activateTab(tab.getAttribute('data-target'));
                });
            });

            activateTab('testimonial');
        });
        </script>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('testimonial_tabs_v1', 'hello_child_testimonial_tabs_shortcode_v1');

function hello_child_testimonial_tabs_shortcode_v2($atts) {
    static $instance = 0;
    $instance++;

    $atts = shortcode_atts(array(
        'testimonial_posts' => -1,
        'video_posts'       => -1,
    ), $atts, 'testimonial_tabs');

    $testimonials_query = new WP_Query(array(
        'post_type'      => 'testimonial',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['testimonial_posts']),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    $videos_query = new WP_Query(array(
        'post_type'      => 'video_testimonial',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['video_posts']),
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ));

    ob_start();
    ?>
    <div class="hello-testimonial-tabs" data-tabs-instance="<?php echo esc_attr($instance); ?>">
        <style>
            .hello-testimonial-tabs{background:#202020;color:#fff;padding:0px 0;width:100%;box-sizing:border-box;overflow:hidden;}
            .hello-testimonial-tabs__inner{width:min(100%, 1540px);margin:0 auto;padding:0 32px;box-sizing:border-box;}
            .hello-testimonial-tabs__tablist{display:flex;justify-content:center;gap:16px;flex-wrap:wrap;margin:0 0 28px;}
            .hello-testimonial-tabs__tab{appearance:none;border:1px solid #ff1f8e;background:transparent;color:#fff;border-radius:999px;padding:18px 34px;font-size:17px;font-weight:600;line-height:1;cursor:pointer;transition:background .25s ease,color .25s ease,transform .25s ease,box-shadow .25s ease;min-width:100px;text-align:center;}
            .hello-testimonial-tabs__tab.is-active{background:#ff1f8e;box-shadow:0 10px 24px rgba(255,31,142,.24);}
            .hello-testimonial-tabs__tab:hover{transform:translateY(-1px);}
            .hello-testimonial-tabs__heading-wrap{text-align:center;margin-bottom:28px;}
            .hello-testimonial-tabs__heading{font-size:clamp(2rem, 1.4rem + 2vw, 3.4rem);line-height:1.05;letter-spacing:-0.04em;margin:0;font-weight:500;}
            .hello-testimonial-tabs__panel{display:none;}
            .hello-testimonial-tabs__panel.is-active{display:block;}
            .hello-testimonial-carousel{position:relative;}
            .hello-testimonial-carousel__viewport{overflow:hidden;width:100%;}
            .hello-testimonial-carousel__track{display:flex;align-items:stretch;gap:18px;width:max-content;will-change:transform;animation:helloTestimonialMarquee var(--hello-marquee-duration, 30s) linear infinite;}
            .hello-testimonial-carousel__slide{flex:0 0 clamp(300px, 31vw, 430px);display:flex;align-self:stretch;}
            .hello-testimonial-carousel__card{background:#242424;border:1px solid rgba(255,255,255,.14);border-radius:22px;overflow:hidden;min-height:440px;height:100%;width:100%;box-shadow:0 18px 50px rgba(0,0,0,.14);display:flex;}
            .hello-testimonial-carousel__card-inner{display:flex;flex-direction:column;height:100%;width:100%;}
            .hello-testimonial-carousel__card-body{padding:30px 26px 26px;display:flex;flex-direction:column;gap:16px;flex:1;min-height:0;}
            .hello-testimonial-carousel__card-title{margin:0;font-size:1.2rem;line-height:1.15;font-weight:600;color:#fff;}
            .hello-testimonial-carousel__card-text{margin:0;color:#c8c8c8;font-size:.98rem;line-height:1.65;}
            .hello-testimonial-carousel__meta{margin-top:auto;display:flex;flex-direction:column;gap:5px;}
            .hello-testimonial-carousel__name{margin:0;font-size:1rem;font-weight:600;color:#fff;}
            .hello-testimonial-carousel__date,.hello-testimonial-carousel__video-caption{margin:0;color:#efefef;font-size:.9rem;letter-spacing:.02em;}
            .review-media-card{position:relative;overflow:hidden;min-height:440px;height:100%;width:100%;display:flex;flex-direction:column;border-radius:22px;background:#242424;border:1px solid rgba(255,255,255,.14);box-shadow:0 18px 50px rgba(0,0,0,.14);}
            .review-media-card img{width:100%;height:100%;object-fit:cover;display:block;}
            .video-play-overlay{position:absolute;inset:0;background:linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,.72) 100%);display:flex;align-items:center;justify-content:center;z-index:2;}
            .play-trigger-icon a,.play-trigger-icon button{display:inline-flex;align-items:center;justify-content:center;padding:8px 10px;border-radius:16px;transition:transform .25s ease,opacity .25s ease;background:transparent;border:0;cursor:pointer;}
            .play-trigger-icon a:hover,.play-trigger-icon button:hover{transform:scale(1.05);opacity:.96;}
            .play-trigger-icon img{display:block;width:78px;height:auto;}
            .video-text-bottom-label{position:absolute;left:22px;right:22px;bottom:20px;z-index:3;}
            .video-text-bottom-label h5{margin:0;font-size:.95rem;line-height:1.45;color:#fff;text-shadow:0 2px 8px rgba(0,0,0,.9);}
            .hello-testimonial-tabs__empty{color:#cfcfcf;padding:20px 0 0;}
            .hello-testimonial-video-modal[hidden]{display:none !important;}.hello-testimonial-scroll-lock{overflow:hidden;position:fixed;width:100%;touch-action:none;overscroll-behavior:none;}
            .hello-testimonial-video-modal{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:24px;overscroll-behavior:none;touch-action:none;}
            .hello-testimonial-video-modal__backdrop{position:absolute;inset:0;background:rgba(0,0,0,.78);}
            .hello-testimonial-video-modal__dialog{position:relative;width:min(100%, 980px);z-index:1;}
            .hello-testimonial-video-modal__frame-wrap{position:relative;padding-top:56.25%;background:#000;border-radius:18px;overflow:hidden;box-shadow:0 25px 70px rgba(0,0,0,.45);}
            .hello-testimonial-video-modal__frame-wrap iframe{position:absolute;inset:0;width:100%;height:100%;border:0;}
            .hello-testimonial-video-modal__close{position:absolute;top:-14px;right:-14px;width:40px;height:40px;border:0;border-radius:50%;background:#fff;color:#111;font-size:24px;line-height:1;cursor:pointer;z-index:2;box-shadow:0 12px 28px rgba(0,0,0,.3);}
            .hello-testimonial-video-modal__close:hover{transform:scale(1.05);}
            @keyframes helloTestimonialMarquee{
                from{transform:translate3d(0,0,0);}
                to{transform:translate3d(-50%,0,0);}
            }
            @media (prefers-reduced-motion: reduce){
                .hello-testimonial-carousel__track{animation:none;transform:none;}
            }
            @media (max-width: 1100px){
                .hello-testimonial-carousel__slide{flex-basis:clamp(280px, 46vw, 420px);}
            }
            @media (max-width: 767px){
                .hello-testimonial-tabs{padding:56px 0;}
                .hello-testimonial-tabs__inner{padding:0 18px;}
                .hello-testimonial-tabs__tablist{gap:12px;margin-bottom:22px;}
                .hello-testimonial-tabs__tab{min-width:0;flex:1 1 calc(50% - 12px);padding:15px 16px;font-size:15px;}
                .hello-testimonial-tabs__heading-wrap{margin-bottom:22px;}
                .hello-testimonial-tabs__heading{font-size:clamp(1.7rem, 1.2rem + 2vw, 2.4rem);}
                .hello-testimonial-carousel__track{gap:14px;}
                .hello-testimonial-carousel__slide{flex-basis:82vw;}
                .hello-testimonial-carousel__card,
                .review-media-card{min-height:360px;}
                .hello-testimonial-carousel__card-body{padding:22px 18px 20px;}
                .hello-testimonial-carousel__card-title{font-size:1.05rem;}
                .hello-testimonial-carousel__card-text{font-size:.94rem;line-height:1.6;}
                .hello-testimonial-carousel__date,.hello-testimonial-carousel__video-caption{font-size:.88rem;}
            }
        </style>

        <div class="hello-testimonial-tabs__inner">
            <div class="hello-testimonial-tabs__tablist" role="tablist" aria-label="Testimonials tabs">
                <button class="hello-testimonial-tabs__tab is-active" type="button" role="tab" aria-selected="true" aria-controls="hello-testimonial-panel-<?php echo esc_attr($instance); ?>-testimonial" id="hello-testimonial-tab-<?php echo esc_attr($instance); ?>-testimonial" data-target="testimonial">Testimonial</button>
                <button class="hello-testimonial-tabs__tab" type="button" role="tab" aria-selected="false" aria-controls="hello-testimonial-panel-<?php echo esc_attr($instance); ?>-video" id="hello-testimonial-tab-<?php echo esc_attr($instance); ?>-video" data-target="video">Video Testimonial</button>
            </div>

            <div class="hello-testimonial-tabs__heading-wrap">
                <h2 class="hello-testimonial-tabs__heading" data-heading="testimonial">Testimonial</h2>
                <h2 class="hello-testimonial-tabs__heading" data-heading="video" style="display:none;">Video Testimonial</h2>
            </div>

            <div class="hello-testimonial-tabs__panel is-active" role="tabpanel" id="hello-testimonial-panel-<?php echo esc_attr($instance); ?>-testimonial" aria-labelledby="hello-testimonial-tab-<?php echo esc_attr($instance); ?>-testimonial" data-panel="testimonial">
                <div class="hello-testimonial-carousel" data-carousel="testimonial">
                    <div class="hello-testimonial-carousel__viewport">
                        <div class="hello-testimonial-carousel__track" data-loop-track>
                            <?php if ($testimonials_query->have_posts()) : ?>
                                <?php while ($testimonials_query->have_posts()) : $testimonials_query->the_post(); ?>
                                    <?php
                                    $customer_name = get_post_meta(get_the_ID(), '_testimonial_customer_name', true);
                                    $designation   = get_post_meta(get_the_ID(), '_testimonial_customer_designation', true);
                                    $testimonial_date = get_post_meta(get_the_ID(), '_testimonial_date', true);
                                    $formatted_date = hello_child_format_testimonial_date($testimonial_date ?: get_the_date('Y-m-d'));
                                    ?>
                                    <div class="hello-testimonial-carousel__slide">
                                        <article class="hello-testimonial-carousel__card">
                                            <div class="hello-testimonial-carousel__card-inner">
                                                <div class="hello-testimonial-carousel__card-body">
                                                    <h3 class="hello-testimonial-carousel__card-title"><?php the_title(); ?></h3>
                                                    <p class="hello-testimonial-carousel__card-text"><?php echo esc_html(wp_strip_all_tags(get_the_content())); ?></p>
                                                    <div class="hello-testimonial-carousel__meta">
                                                        <p class="hello-testimonial-carousel__name"><?php echo esc_html($customer_name ?: get_the_title()); ?></p>
                                                        <?php if (!empty($designation)) : ?>
                                                            <p class="hello-testimonial-carousel__date"><?php echo esc_html($designation); ?></p>
                                                        <?php elseif (!empty($formatted_date)) : ?>
                                                            <p class="hello-testimonial-carousel__date"><?php echo esc_html($formatted_date); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                <?php endwhile; wp_reset_postdata(); ?>
                            <?php else : ?>
                                <p class="hello-testimonial-tabs__empty">No testimonials found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hello-testimonial-tabs__panel" role="tabpanel" id="hello-testimonial-panel-<?php echo esc_attr($instance); ?>-video" aria-labelledby="hello-testimonial-tab-<?php echo esc_attr($instance); ?>-video" data-panel="video">
                <div class="hello-testimonial-carousel" data-carousel="video">
                    <div class="hello-testimonial-carousel__viewport">
                        <div class="hello-testimonial-carousel__track" data-loop-track>
                            <?php if ($videos_query->have_posts()) : ?>
                                <?php while ($videos_query->have_posts()) : $videos_query->the_post(); ?>
                                    <?php
                                    $youtube_url = get_post_meta(get_the_ID(), '_video_testimonial_link', true);
                                    $image_url   = get_post_meta(get_the_ID(), '_video_testimonial_image_url', true);
                                    $image_alt   = get_post_meta(get_the_ID(), '_video_testimonial_image_alt', true);
                                    $thumbnail_url = $image_url ? $image_url : get_the_post_thumbnail_url(get_the_ID(), 'large');
                                    if (!$image_alt) {
                                        $image_alt = get_the_title();
                                    }
                                    ?>
                                    <div class="hello-testimonial-carousel__slide">
                                        <article class="review-media-card">
                                            <?php if (!empty($thumbnail_url)) : ?>
                                                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($image_alt); ?>">
                                            <?php endif; ?>
                                            <?php if (!empty($youtube_url)) : ?>
                                                <div class="video-play-overlay">
                                                    <div class="play-trigger-icon">
                                                        <button type="button" class="hello-testimonial-video-trigger" data-video-id="<?php echo esc_attr(hello_child_extract_youtube_id($youtube_url)); ?>" data-video-url="<?php echo esc_url($youtube_url); ?>" aria-label="Open video testimonial: <?php echo esc_attr(get_the_title()); ?>">
                                                            <img src="/wp-content/uploads/2026/05/youtube-new-icon.svg" alt="YouTube play icon">
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="video-text-bottom-label"><h5><?php the_title(); ?></h5></div>
                                        </article>
                                    </div>
                                <?php endwhile; wp_reset_postdata(); ?>
                            <?php else : ?>
                                <p class="hello-testimonial-tabs__empty">No video testimonials found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="hello-testimonial-video-modal" data-video-modal hidden>
            <div class="hello-testimonial-video-modal__backdrop" data-video-close></div>
            <div class="hello-testimonial-video-modal__dialog" role="dialog" aria-modal="true" aria-label="Video testimonial player">
                <button type="button" class="hello-testimonial-video-modal__close" data-video-close aria-label="Close video">&times;</button>
                <div class="hello-testimonial-video-modal__frame-wrap">
                    <iframe data-video-iframe src="" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var root = document.querySelector('.hello-testimonial-tabs[data-tabs-instance="<?php echo esc_js($instance); ?>"]');
            if (!root) return;

            var tabs = root.querySelectorAll('.hello-testimonial-tabs__tab');
            var panels = root.querySelectorAll('.hello-testimonial-tabs__panel');
            var headings = root.querySelectorAll('.hello-testimonial-tabs__heading');
            var tracks = root.querySelectorAll('[data-loop-track]');
            var modal = root.querySelector('[data-video-modal]');
            var iframe = root.querySelector('[data-video-iframe]');
            var closeTriggers = root.querySelectorAll('[data-video-close]');

            tracks.forEach(function(track) {
                if (track.getAttribute('data-looped') === '1') {
                    return;
                }

                var items = Array.from(track.children);
                if (!items.length) {
                    return;
                }

                var cloneCount = items.length;
                items.forEach(function(item) {
                    track.appendChild(item.cloneNode(true));
                });

                track.setAttribute('data-looped', '1');
                track.style.setProperty('--hello-marquee-duration', Math.max(18, cloneCount * 7) + 's');
            });

            function activateTab(target) {
                tabs.forEach(function(tab) {
                    var isActive = tab.getAttribute('data-target') === target;
                    tab.classList.toggle('is-active', isActive);
                    tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                panels.forEach(function(panel) {
                    panel.classList.toggle('is-active', panel.getAttribute('data-panel') === target);
                });

                headings.forEach(function(heading) {
                    heading.style.display = heading.getAttribute('data-heading') === target ? 'block' : 'none';
                });
            }

            var scrollLockY = 0;

            function lockPageScroll() {
                scrollLockY = window.scrollY || window.pageYOffset || 0;
                document.body.classList.add('hello-testimonial-scroll-lock');
                document.body.style.top = '-' + scrollLockY + 'px';
            }

            function unlockPageScroll() {
                document.body.classList.remove('hello-testimonial-scroll-lock');
                document.body.style.top = '';
                window.scrollTo(0, scrollLockY);
            }

            function openVideoModal(videoId, videoUrl) {
                if (!modal || !iframe) return;

                var embedUrl = videoId ? ('https://www.youtube.com/embed/' + videoId + '?autoplay=1') : videoUrl;
                if (!embedUrl) return;

                iframe.src = embedUrl;
                modal.hidden = false;
                lockPageScroll();
            }

            function closeVideoModal() {
                if (!modal || !iframe) return;

                iframe.src = '';
                modal.hidden = true;
                unlockPageScroll();
            }

            root.addEventListener('click', function(event) {
                var trigger = event.target.closest('.hello-testimonial-video-trigger');
                if (!trigger) return;

                event.preventDefault();
                openVideoModal(trigger.getAttribute('data-video-id'), trigger.getAttribute('data-video-url'));
            });

            closeTriggers.forEach(function(trigger) {
                trigger.addEventListener('click', closeVideoModal);
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal && !modal.hidden) {
                    closeVideoModal();
                }
            });

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    activateTab(tab.getAttribute('data-target'));
                });
            });

            activateTab('testimonial');
        });
        </script>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('testimonial_tabs', 'hello_child_testimonial_tabs_shortcode_v2');
/**
 * Fix previously imported video testimonial titles that may have had encoding issues.
 */
/**
 * Theme support for Gutenberg and cleaner front-end output.
 */
function hello_elementor_child_setup_theme() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'editor-styles' );
    add_editor_style( 'style.css' );
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );
}
add_action( 'after_setup_theme', 'hello_elementor_child_setup_theme' );

/**
 * Reduce front-end payload where the theme does not need legacy assets.
 */
function hello_elementor_child_frontend_cleanup() {
    if ( ! is_admin() ) {
        wp_dequeue_script( 'wp-embed' );
        wp_deregister_script( 'wp-embed' );
    }
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_frontend_cleanup', 100 );

/**
 * SEO fallbacks for pages when Yoast fields are missing.
 */
function hello_elementor_child_seo_map() {
    return array(
        'home' => array(
            'title' => 'Ceramic Coating & PPF in Mangalore | Ceramic Pro',
            'description' => 'Protect your vehicle with premium ceramic coating, PPF, CPF, and detailing services at Ceramic Pro Mangalore. Book today.',
        ),
        'about-us' => array(
            'title' => 'Ceramic Coating & PPF in Mangalore | Ceramic Pro',
            'description' => 'Protect your vehicle with premium ceramic coating, PPF, CPF, and detailing services at Ceramic Pro Mangalore. Book today.',
        ),
        'contact-us' => array(
            'title' => 'Contact Ceramic Pro Mangalore | Book an Appointment',
            'description' => 'Contact Ceramic Pro Mangalore for ceramic coating, PPF, detailing, and vehicle protection services. Book your appointment today.',
        ),
        'products' => array(
            'title' => 'Ceramic Pro Coatings in Mangalore | Complete Protection',
            'description' => 'Explore Ceramic Pro coatings for paint, leather, plastic, glass, and fabric. Advanced protection with long-lasting durability and shine.',
        ),
        'leadership' => array(
            'title' => 'Leadership - ceramic pro',
            'description' => '',
        ),
        'blogs' => array(
            'title' => 'Blogs | Ceramic Pro Mangalore',
            'description' => 'Read Ceramic Pro Mangalore blogs, updates, and detailing insights.',
        ),
        'blog' => array(
            'title' => 'Car Care & Ceramic Coating Blogs | Ceramic Pro',
            'description' => 'Explore expert blogs on ceramic coating, PPF, car detailing, paint protection, and vehicle maintenance from Ceramic Pro Mangalore.',
        ),
        'ceramic-pro' => array(
            'title' => 'Ceramic Pro Coating in Mangalore | Certified Protection',
            'description' => 'Protect your vehicle with certified Ceramic Pro coatings in Mangalore. Enjoy superior gloss, hydrophobic protection, and long-lasting durability.',
        ),
        'ceramic-coating' => array(
            'title' => 'Ceramic Coating | Ceramic Pro Mangalore',
            'description' => 'Protect your vehicle with ceramic coating services that improve gloss, repel contaminants, and simplify maintenance.',
        ),
        'paint-protection-film-ppf' => array(
            'title' => 'Paint Protection Film (PPF) | Ceramic Pro Mangalore',
            'description' => 'Shield your vehicle against scratches, chips, and road damage with premium paint protection film.',
        ),
        'interior-cleaning-polishing-conditioning' => array(
            'title' => 'Interior Cleaning, Polishing & Conditioning | Ceramic Pro Mangalore',
            'description' => 'Refresh and protect your vehicle with professional interior cleaning, polishing, and conditioning services.',
        ),
        'composite-protection-film-cpf' => array(
            'title' => 'Composite Protection Film (CPF) | Ceramic Pro Mangalore',
            'description' => 'Combine film strength and ceramic technology with composite protection film for advanced gloss and durability.',
        ),
        'furniture-coating' => array(
            'title' => 'Furniture Coating | Ceramic Pro Mangalore',
            'description' => 'Protect furniture surfaces from stains, scratches, and daily wear with premium furniture coating services.',
        ),
        'gallery' => array(
            'title' => 'Car Detailing Gallery in Mangalore | Premium Finishes',
            'description' => 'Explore our gallery of ceramic coating, PPF, detailing, and vehicle protection projects. See premium finishes and real transformation results.',
        ),
        'privacy-policy' => array(
            'title' => 'Privacy Policy - ceramic pro',
            'description' => '',
        ),
        'terms-conditions' => array(
            'title' => 'Terms & Conditions - ceramic pro',
            'description' => '',
        ),
    );
}

function hello_elementor_child_get_page_seo( $slug, $field ) {
    $map = hello_elementor_child_seo_map();
    return isset( $map[ $slug ][ $field ] ) ? $map[ $slug ][ $field ] : '';
}

function hello_elementor_child_filter_document_title( $title ) {
    if ( is_singular( 'page' ) ) {
        $slug = get_post_field( 'post_name', get_queried_object_id() );
        $fallback = hello_elementor_child_get_page_seo( $slug, 'title' );
        if ( $fallback ) {
            return $fallback;
        }
    }

    return $title;
}
add_filter( 'pre_get_document_title', 'hello_elementor_child_filter_document_title', 20 );

function hello_elementor_child_filter_yoast_title( $title ) {
    if ( is_singular( 'page' ) ) {
        $slug = get_post_field( 'post_name', get_queried_object_id() );
        $fallback = hello_elementor_child_get_page_seo( $slug, 'title' );
        if ( $fallback ) {
            return $fallback;
        }
    }

    return $title;
}
add_filter( 'wpseo_title', 'hello_elementor_child_filter_yoast_title', 20 );

function hello_elementor_child_filter_yoast_metadesc( $description ) {
    if ( is_singular( 'page' ) ) {
        $slug = get_post_field( 'post_name', get_queried_object_id() );
        $fallback = hello_elementor_child_get_page_seo( $slug, 'description' );
        if ( $fallback ) {
            return $fallback;
        }
    }

    return $description;
}
add_filter( 'wpseo_metadesc', 'hello_elementor_child_filter_yoast_metadesc', 20 );



