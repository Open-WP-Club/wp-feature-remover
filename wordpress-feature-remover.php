<?php

/**
 * Plugin Name: WP Feature Remover
 * Description: Removes and optimizes unnecessary WordPress features
 * Version: 0.0.1
 * Author: Gabriel Kanev - Open WP Club
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WordPress_Feature_Remover
{
    private $options;
    private $features;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('init', array($this, 'remove_features'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));

        $this->init_features();
    }

    private function init_features()
    {
        $this->features = array(
            'security' => array(
                'remove_generator_tag' => 'Remove Generator Tag',
                'remove_script_style_versions' => 'Remove Script/Style Versions',
                'disable_xmlrpc' => 'Disable XML-RPC',
                'disable_file_editing' => 'Disable File Editing',
                'hide_wp_version' => 'Hide WordPress Version',
                'disable_user_enumeration' => 'Disable User Enumeration',
            ),
            'optimization' => array(
                'remove_jquery_migrate' => 'Remove jQuery Migrate',
                'disable_emojis' => 'Disable Emojis',
                'disable_embeds' => 'Disable Embeds',
                'disable_self_pingbacks' => 'Disable Self Pingbacks',
                'remove_shortlink' => 'Remove Shortlink',
                'remove_wlw_manifest' => 'Remove WLW Manifest',
                'remove_rsd_link' => 'Remove RSD Link',
                'remove_dns_prefetch' => 'Remove DNS Prefetch',
            ),
            'comments' => array(
                'optimize_comment_script' => 'Optimize Comment Script',
                'remove_recent_comments_style' => 'Remove Recent Comments Style',
                'disable_comment_hyperlinks' => 'Disable Comment Hyperlinks',
            ),
            'feeds' => array(
                'remove_feed_generator' => 'Remove Feed Generator',
                'remove_feed_links' => 'Remove Feed Links',
                'disable_feeds' => 'Disable Feeds',
            ),
            'miscellaneous' => array(
                'reduce_heartbeat_interval' => 'Reduce Heartbeat Interval',
                'normalize_favicon' => 'Normalize Favicon',
                'normalize_login_logo_url' => 'Normalize Login Logo URL',
                'normalize_login_logo_title' => 'Normalize Login Logo Title',
                'disable_login_language' => 'Disable Login Language Selector',
            ),
        );
    }

    public function add_plugin_page()
    {
        add_options_page(
            'WP Feature Remover Settings',
            'Feature Remover',
            'manage_options',
            'wp-feature-remover',
            array($this, 'create_admin_page')
        );
    }

    public function create_admin_page()
    {
        $this->options = get_option('wp_feature_remover_options', array());
?>
        <div class="wrap">
            <h1>WordPress Feature Remover Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wp_feature_remover_options_group');
                do_settings_sections('wp-feature-remover');
                submit_button();
                ?>
            </form>
        </div>
<?php
    }

    public function page_init()
    {
        register_setting(
            'wp_feature_remover_options_group',
            'wp_feature_remover_options',
            array($this, 'sanitize')
        );

        foreach ($this->features as $category => $category_features) {
            add_settings_section(
                "wp_feature_remover_section_{$category}",
                ucfirst($category) . ' Features',
                array($this, 'print_section_info'),
                'wp-feature-remover'
            );

            foreach ($category_features as $id => $title) {
                add_settings_field(
                    $id,
                    $title,
                    array($this, 'create_checkbox'),
                    'wp-feature-remover',
                    "wp_feature_remover_section_{$category}",
                    array('id' => $id)
                );
            }
        }
    }

    public function sanitize($input)
    {
        $new_input = array();
        foreach ($this->features as $category_features) {
            foreach ($category_features as $id => $title) {
                $new_input[$id] = isset($input[$id]) ? true : false;
            }
        }
        return $new_input;
    }

    public function print_section_info($args)
    {
        $category = str_replace('wp_feature_remover_section_', '', $args['id']);
        print "Select which {$category} features you want to remove or optimize:";
    }

    public function create_checkbox($args)
    {
        $id = $args['id'];
        $checked = isset($this->options[$id]) ? checked($this->options[$id], true, false) : '';
        echo "<input type='checkbox' id='$id' name='wp_feature_remover_options[$id]' value='1' $checked />";
        echo "<label for='$id'>Enable</label>";
    }

    public function remove_features()
    {
        $this->options = get_option('wp_feature_remover_options', array());

        if (!empty($this->options)) {
            foreach ($this->options as $feature => $enabled) {
                if ($enabled && method_exists($this, $feature)) {
                    $this->$feature();
                }
            }
        }
    }

    public function enqueue_admin_styles($hook)
    {
        if ('settings_page_wp-feature-remover' !== $hook) {
            return;
        }
        wp_enqueue_style('wp-feature-remover-admin', plugins_url('admin-styles.css', __FILE__));
    }

    // Feature removal methods
    private function remove_generator_tag()
    {
        remove_action('wp_head', 'wp_generator');
    }

    private function remove_script_style_versions()
    {
        add_filter('style_loader_src', array($this, 'remove_version_query_arg'), 10, 2);
        add_filter('script_loader_src', array($this, 'remove_version_query_arg'), 10, 2);
    }

    private function disable_xmlrpc()
    {
        add_filter('xmlrpc_enabled', '__return_false');
    }

    private function disable_file_editing()
    {
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
    }

    private function hide_wp_version()
    {
        add_filter('the_generator', '__return_empty_string');
    }

    private function disable_user_enumeration()
    {
        if (!is_admin()) {
            if (isset($_REQUEST['author'])) {
                wp_die('Author parameter is forbidden.');
            }
        }
    }

    private function remove_jquery_migrate()
    {
        add_action('wp_default_scripts', function ($scripts) {
            if (!is_admin() && isset($scripts->registered['jquery'])) {
                $script = $scripts->registered['jquery'];
                if ($script->deps) {
                    $script->deps = array_diff($script->deps, array('jquery-migrate'));
                }
            }
        });
    }

    private function disable_emojis()
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        add_filter('tiny_mce_plugins', array($this, 'disable_emojis_tinymce'));
    }

    private function disable_embeds()
    {
        global $wp;
        $wp->public_query_vars = array_diff($wp->public_query_vars, array('embed'));
        remove_action('rest_api_init', 'wp_oembed_register_route');
        add_filter('embed_oembed_discover', '__return_false');
        remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
    }

    private function disable_self_pingbacks()
    {
        add_action('pre_ping', function (&$links) {
            $home = get_option('home');
            foreach ($links as $l => $link) {
                if (0 === strpos($link, $home)) {
                    unset($links[$l]);
                }
            }
        });
    }

    private function remove_shortlink()
    {
        remove_action('wp_head', 'wp_shortlink_wp_head', 10);
        remove_action('template_redirect', 'wp_shortlink_header', 11);
    }

    private function remove_wlw_manifest()
    {
        remove_action('wp_head', 'wlwmanifest_link');
    }

    private function remove_rsd_link()
    {
        remove_action('wp_head', 'rsd_link');
    }

    private function remove_dns_prefetch()
    {
        add_filter('wp_resource_hints', function ($hints, $relation_type) {
            if ('dns-prefetch' === $relation_type) {
                return array_diff(wp_dependencies_unique_hosts(), $hints);
            }
            return $hints;
        }, 10, 2);
    }

    private function optimize_comment_script()
    {
        add_action('wp_enqueue_scripts', function () {
            if (is_singular() && comments_open() && get_option('thread_comments')) {
                wp_enqueue_script('comment-reply');
            } else {
                wp_dequeue_script('comment-reply');
            }
        });
    }

    private function remove_recent_comments_style()
    {
        add_action('widgets_init', function () {
            global $wp_widget_factory;
            remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
        });
    }

    private function disable_comment_hyperlinks()
    {
        remove_filter('comment_text', 'make_clickable', 9);
    }

    private function remove_feed_generator()
    {
        add_filter('the_generator', '__return_false');
    }

    private function remove_feed_links()
    {
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'feed_links_extra', 3);
    }

    private function disable_feeds()
    {
        add_action('do_feed', array($this, 'disable_feed'), 1);
        add_action('do_feed_rdf', array($this, 'disable_feed'), 1);
        add_action('do_feed_rss', array($this, 'disable_feed'), 1);
        add_action('do_feed_rss2', array($this, 'disable_feed'), 1);
        add_action('do_feed_atom', array($this, 'disable_feed'), 1);
        add_action('do_feed_rss2_comments', array($this, 'disable_feed'), 1);
        add_action('do_feed_atom_comments', array($this, 'disable_feed'), 1);
    }

    private function reduce_heartbeat_interval()
    {
        add_filter('heartbeat_settings', function ($settings) {
            $settings['interval'] = 60;
            return $settings;
        });
    }

    private function normalize_favicon()
    {
        add_action('do_faviconico', function () {
            exit('data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
        });
    }

    private function normalize_login_logo_url()
    {
        add_filter('login_headerurl', function () {
            return home_url();
        });
    }

    private function normalize_login_logo_title()
    {
        add_filter('login_headertext', function () {
            return get_bloginfo('name');
        });
    }

    private function disable_login_language()
    {
        add_filter('login_display_language_dropdown', '__return_false');
    }

    public function remove_version_query_arg($src)
    {
        if (strpos($src, 'ver=')) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }

    public function disable_feed()
    {
        wp_redirect(home_url(), 301);
        exit;
    }

    public function disable_emojis_tinymce($plugins)
    {
        return is_array($plugins) ? array_diff($plugins, array('wpemoji')) : array();
    }
}

$wordpress_feature_remover = new WordPress_Feature_Remover();

// Add a link to the plugin's settings page on the Plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wp_feature_remover_add_settings_link');
function wp_feature_remover_add_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=wp-feature-remover">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
