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

class WordPress_Feature_Remover
{
    public $features;
    private $options;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('init', array($this, 'remove_features'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        $this->init_features();
        $this->init_options();
    }

    private function init_features()
    {
        $this->features = array(
            'security' => array(
                'remove_generator_tag' => array(
                    'title' => 'Remove Generator Tag',
                    'description' => 'Removes the WordPress version generator tag from the HTML head.'
                ),
                'remove_script_style_versions' => array(
                    'title' => 'Remove Script/Style Versions',
                    'description' => 'Removes version query string from scripts and styles.'
                ),
                'disable_xmlrpc' => array(
                    'title' => 'Disable XML-RPC',
                    'description' => 'Disables the XML-RPC API.'
                ),
                'disable_file_editing' => array(
                    'title' => 'Disable File Editing',
                    'description' => 'Disables the built-in file editor.'
                ),
                'hide_wp_version' => array(
                    'title' => 'Hide WordPress Version',
                    'description' => 'Removes WordPress version from various locations.'
                ),
                'disable_user_enumeration' => array(
                    'title' => 'Disable User Enumeration',
                    'description' => 'Prevents user enumeration attacks.'
                ),
            ),
            'optimization' => array(
                'remove_jquery_migrate' => array(
                    'title' => 'Remove jQuery Migrate',
                    'description' => 'Removes jQuery Migrate script from the front-end.'
                ),
                'disable_emojis' => array(
                    'title' => 'Disable Emojis',
                    'description' => 'Disables the built-in WordPress emoji functionality.'
                ),
                'disable_embeds' => array(
                    'title' => 'Disable Embeds',
                    'description' => 'Disables the oEmbed feature.'
                ),
                'disable_self_pingbacks' => array(
                    'title' => 'Disable Self Pingbacks',
                    'description' => 'Prevents the site from sending pingbacks to itself.'
                ),
                'remove_shortlink' => array(
                    'title' => 'Remove Shortlink',
                    'description' => 'Removes the shortlink tag from the HTML head.'
                ),
                'remove_wlw_manifest' => array(
                    'title' => 'Remove WLW Manifest',
                    'description' => 'Removes the Windows Live Writer manifest link.'
                ),
                'remove_rsd_link' => array(
                    'title' => 'Remove RSD Link',
                    'description' => 'Removes the Really Simple Discovery link.'
                ),
                'remove_dns_prefetch' => array(
                    'title' => 'Remove DNS Prefetch',
                    'description' => 'Removes dns-prefetch links from the HTML head.'
                ),
            ),
            'comments' => array(
                'optimize_comment_script' => array(
                    'title' => 'Optimize Comment Script',
                    'description' => 'Loads comment reply script only when necessary.'
                ),
                'remove_recent_comments_style' => array(
                    'title' => 'Remove Recent Comments Style',
                    'description' => 'Removes inline styles for the recent comments widget.'
                ),
                'disable_comment_hyperlinks' => array(
                    'title' => 'Disable Comment Hyperlinks',
                    'description' => 'Prevents automatic hyperlinks in comments.'
                ),
            ),
            'feeds' => array(
                'remove_feed_generator' => array(
                    'title' => 'Remove Feed Generator',
                    'description' => 'Removes the generator tag from RSS feeds.'
                ),
                'remove_feed_links' => array(
                    'title' => 'Remove Feed Links',
                    'description' => 'Removes RSS feed links from the HTML head.'
                ),
                'disable_feeds' => array(
                    'title' => 'Disable Feeds',
                    'description' => 'Completely disables RSS, Atom and RDF feeds.'
                ),
            ),
            'miscellaneous' => array(
                'reduce_heartbeat_interval' => array(
                    'title' => 'Reduce Heartbeat Interval',
                    'description' => 'Increases the interval of the WordPress Heartbeat API.'
                ),
                'normalize_favicon' => array(
                    'title' => 'Normalize Favicon',
                    'description' => 'Provides a default favicon to prevent 404 errors.'
                ),
                'normalize_login_logo_url' => array(
                    'title' => 'Normalize Login Logo URL',
                    'description' => 'Changes the login logo link to your sites homepage.'
                ),
                'normalize_login_logo_title' => array(
                    'title' => 'Normalize Login Logo Title',
                    'description' => 'Changes the login logo title to your sites name.'
                ),
                'disable_login_language' => array(
                    'title' => 'Disable Login Language Selector',
                    'description' => 'Removes the language selector from the login page.'
                )
            ),
        );
    }

    private function init_options()
    {
        $default_options = array();
        foreach ($this->features as $category => $category_features) {
            foreach ($category_features as $feature => $feature_info) {
                $default_options[$feature] = false;
            }
        }
        
        $existing_options = get_option('wp_feature_remover_options', array());
        $this->options = wp_parse_args($existing_options, $default_options);
        
        update_option('wp_feature_remover_options', $this->options);
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
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap wp-feature-remover">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wp_feature_remover_options_group');
                $this->do_settings_sections('wp-feature-remover');
                submit_button('Save Changes');
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

            foreach ($category_features as $id => $feature) {
                add_settings_field(
                    $id,
                    $feature['title'],
                    array($this, 'create_checkbox'),
                    'wp-feature-remover',
                    "wp_feature_remover_section_{$category}",
                    array('id' => $id, 'description' => $feature['description'])
                );
            }
        }
    }

    public function do_settings_sections($page)
    {
        global $wp_settings_sections, $wp_settings_fields;

        if (!isset($wp_settings_sections[$page])) {
            return;
        }

        echo '<div class="wp-feature-remover-tabs">';
        foreach ((array) $wp_settings_sections[$page] as $section) {
            $section_id = str_replace('wp_feature_remover_section_', '', $section['id']);
            echo "<button class='wp-feature-remover-tab-link' data-tab='{$section_id}'>" . $section['title'] . "</button>";
        }
        echo '</div>';

        echo '<div class="wp-feature-remover-content">';
        foreach ((array) $wp_settings_sections[$page] as $section) {
            $section_id = str_replace('wp_feature_remover_section_', '', $section['id']);
            echo "<div id='{$section_id}' class='wp-feature-remover-tab-content'>";
            echo "<h2>{$section['title']}</h2>";
            call_user_func($section['callback'], $section);
            if (isset($wp_settings_fields[$page][$section['id']])) {
                echo '<div class="wp-feature-remover-toggle-all">';
                echo "<label><input type='checkbox' class='toggle-category' data-category='{$section_id}' /> Toggle All {$section['title']}</label>";
                echo '</div>';
                echo '<div class="wp-feature-remover-grid">';
                $this->do_settings_fields($page, $section['id']);
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    protected function do_settings_fields($page, $section)
    {
        global $wp_settings_fields;

        if (!isset($wp_settings_fields[$page][$section])) {
            return;
        }

        foreach ((array) $wp_settings_fields[$page][$section] as $field) {
            echo '<div class="wp-feature-remover-feature">';
            call_user_func($field['callback'], $field['args']);
            echo '</div>';
        }
    }

    public function sanitize($input)
    {
        $new_input = array();
        foreach ($this->features as $category => $category_features) {
            foreach ($category_features as $feature => $feature_info) {
                $new_input[$feature] = isset($input[$feature]) ? (bool)$input[$feature] : false;
            }
        }
        return $new_input;
    }

    public function print_section_info($args)
    {
        $category = str_replace('wp_feature_remover_section_', '', $args['id']);
        printf('<p>Manage %s features below:</p>', ucfirst($category));
    }

    public function create_checkbox($args)
    {
        $id = $args['id'];
        $checked = isset($this->options[$id]) && $this->options[$id] ? 'checked' : '';

        printf(
            '<input type="checkbox" id="%1$s" name="wp_feature_remover_options[%1$s]" value="1" %2$s />',
            esc_attr($id),
            $checked
        );
        printf(
            '<label for="%1$s"> %2$s</label>',
            esc_attr($id),
            esc_html($args['description'])
        );
    }

    public function enqueue_admin_assets($hook)
    {
        if ('settings_page_wp-feature-remover' !== $hook) {
            return;
        }

        wp_enqueue_style('wp-feature-remover-admin', plugins_url('admin-style.css', __FILE__));
        wp_enqueue_script('wp-feature-remover-admin', plugins_url('admin-script.js', __FILE__), array('jquery'), false, true);
    }

    public function remove_features()
    {
        foreach ($this->features as $category => $category_features) {
            foreach ($category_features as $feature => $feature_info) {
                if (!empty($this->options[$feature]) && method_exists($this, $feature)) {
                    $this->$feature();
                }
            }
        }
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

    public function remove_version_query_arg($src)
    {
        if (strpos($src, 'ver=') !== false) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
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
                if (strpos($link, $home) === 0) {
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
        add_filter('the_generator', '__return_empty_string');
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

    public function disable_feed()
    {
        wp_die(__('No feed available, please visit the <a href="' . esc_url(home_url('/')) . '">homepage</a>!'));
    }

    private function reduce_heartbeat_interval()
    {
        add_filter('heartbeat_settings', function ($settings) {
            $settings['interval'] = 60; // Change to 60 seconds
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

    public function disable_emojis_tinymce($plugins)
    {
        if (is_array($plugins)) {
            return array_diff($plugins, array('wpemoji'));
        } else {
            return array();
        }
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

// Activation hook
function wp_feature_remover_activate()
{
    $remover = new WordPress_Feature_Remover();
    $remover->init_options();
}
register_activation_hook(__FILE__, 'wp_feature_remover_activate');