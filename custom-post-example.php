<?php
/*
Plugin Name: Box Widget Plugin
Description: A simple plugin to create a custom post type called "Box Widget".
Version: 1.0
Author: Ruby
*/


function create_box_widget_post_type() {
    $labels = array(
        'name' => __('Box Widgets Tab'),
        'singular_name' => __('Box Widget post'),
        'menu_name' => __('Box Widgets menuu'),
        'add_new_item' => __('Add New Box Widget here'),
        'new_item' => __('New Box Widget ni'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'box-widget'),
        'menu_position' => 5,
        'supports' => array('title', 'editor'),
        'description' => 'Custom post type for Box Widgets',
    );

    register_post_type('box_widget', $args);
}

add_action('init', 'create_box_widget_post_type');

// Register Shortcode for displaying Box Widget content
function box_widget_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '', // Default empty id
    ), $atts);

    if (empty($atts['id'])) {
        return 'No ID provided';
    }

    $post_id = intval($atts['id']);
    $post = get_post($post_id);

    if ($post && $post->post_type === 'box_widget') {
        return apply_filters('the_content', $post->post_content); // Display the post content
    } else {
        return 'Box Widget not found';
    }
}

add_shortcode('box_widget', 'box_widget_shortcode');

function my_custom_flush_rewrite_rules() {
    flush_rewrite_rules();
}
add_action('init', 'my_custom_flush_rewrite_rules');

// Add custom meta box
function add_box_widget_meta_box() {
    add_meta_box(
        'box_widget_meta_box', // ID
        'Box Widget ID', // Title
        'render_box_widget_meta_box', // Callback function
        'box_widget', // Post type
        'side', // Context (normal, side, advanced)
        'default' // Priority
    );
}
add_action('add_meta_boxes', 'add_box_widget_meta_box');

// Render the custom meta box
function render_box_widget_meta_box($post) {
    // Retrieve existing value from post meta
    $box_widget_id = get_post_meta($post->ID, '_box_widget_id', true);
    ?>
    <label for="box_widget_id"><?php _e('Box Widget ID:', 'textdomain'); ?></label>
    <input type="text" id="box_widget_id" name="box_widget_id" value="<?php echo esc_attr($box_widget_id); ?>" readonly />
    <?php
}

// Save the custom meta box data
function save_box_widget_meta_box_data($post_id) {
    // Check if our nonce is set
    if (!isset($_POST['box_widget_meta_box_nonce'])) {
        return $post_id;
    }
    $nonce = $_POST['box_widget_meta_box_nonce'];

    // Verify that the nonce is valid
    if (!wp_verify_nonce($nonce, 'save_box_widget_meta_box_data')) {
        return $post_id;
    }

    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // Check the user's permissions
    if ('box_widget' === $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
    } else {
        return $post_id;
    }

    // Sanitize and save the data
    $box_widget_id = sanitize_text_field($_POST['box_widget_id']);
    update_post_meta($post_id, '_box_widget_id', $box_widget_id);
}
add_action('save_post', 'save_box_widget_meta_box_data');

// Add nonce for security
function add_box_widget_meta_box_nonce() {
    wp_nonce_field('save_box_widget_meta_box_data', 'box_widget_meta_box_nonce');
}
add_action('edit_form_after_title', 'add_box_widget_meta_box_nonce');
