<?php
// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete the plugin options from the database
delete_option('wp_feature_remover_options');

// If you have any custom tables, you can drop them here
// global $wpdb;
// $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}your_custom_table");

// If you've added any roles or capabilities, remove them
// remove_role('your_custom_role');
// $role = get_role('administrator');
// $role->remove_cap('your_custom_capability');

// Clear any cached data that may have been stored
wp_cache_flush();

// If you've scheduled any cron jobs, clear them
// wp_clear_scheduled_hook('your_cron_hook');

// If you've registered any post types or taxonomies, you might want to clear posts
// $posts = get_posts(array('post_type' => 'your_custom_post_type', 'numberposts' => -1));
// foreach ($posts as $post) {
//     wp_delete_post($post->ID, true);
// }

// You can add any other cleanup operations here