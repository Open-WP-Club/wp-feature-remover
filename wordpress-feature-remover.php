<?php

/**
 * Plugin Name: WP Feature Remover
 * Description: Removes and optimizes unnecessary WordPress features
 * Version: 0.0.3
 * Author: Gabriel Kanev - Open WP Club
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Wrap the plugin initialization in a function
function wp_feature_remover_init()
{
    // Only load the class if it hasn't been loaded already
    if (!class_exists('WordPress_Feature_Remover')) {
        require_once plugin_dir_path(__FILE__) . 'class-wordpress-feature-remover.php';
    }

    global $wordpress_feature_remover;
    $wordpress_feature_remover = new WordPress_Feature_Remover();
}

// Hook the init function to the 'plugins_loaded' action
add_action('plugins_loaded', 'wp_feature_remover_init');

// Add a link to the plugin's settings page on the Plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wp_feature_remover_add_settings_link');
function wp_feature_remover_add_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=wp-feature-remover">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// Activation hook
function wp_feature_remover_activate()
{
    // Ensure the class is loaded
    require_once plugin_dir_path(__FILE__) . 'class-wordpress-feature-remover.php';

    // Instead of calling a private method, we'll create a new instance
    // and let the constructor handle the initialization
    $remover = new WordPress_Feature_Remover();
}
register_activation_hook(__FILE__, 'wp_feature_remover_activate');
