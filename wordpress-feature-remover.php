<?php
/**
 * Plugin Name: WP Feature Remover
 * Description: Removes and optimizes unnecessary WordPress features
 * Version: 0.2.0
 * Author: Gabriel Kanev - Open WP Club (Refactored and Enhanced by Assistant)
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
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        $this->init_features();
    }

    private function init_features()
    {
        $this->features = array(
            'security' => array(
                'generator_tag' => array(
                    'title' => 'Remove Generator Tag',
                    'description' => 'Removes the WordPress version number from the HTML source.',
                    'callback' => 'remove_generator_tag'
                ),
                'script_style_versions' => array(
                    'title' => 'Remove Script/Style Versions',
                    'description' => 'Removes version numbers from script and style URLs to improve caching.',
                    'callback' => 'remove_script_style_versions'
                ),
            ),
            'optimization' => array(
                'jquery_migrate' => array(
                    'title' => 'Remove jQuery Migrate',
                    'description' => 'Removes the jQuery Migrate script to reduce page load time.',
                    'callback' => 'remove_jquery_migrate'
                ),
                'emojis' => array(
                    'title' => 'Disable Emojis',
                    'description' => 'Disables the built-in emoji functionality to reduce page load time.',
                    'callback' => 'disable_emojis'
                ),
            ),
            'feeds' => array(
                'feed_generator' => array(
                    'title' => 'Remove Feed Generator',
                    'description' => 'Removes the generator tag from RSS feeds.',
                    'callback' => 'remove_feed_generator'
                ),
                'feed_links' => array(
                    'title' => 'Remove Feed Links',
                    'description' => 'Removes RSS feed links from the HTML head.',
                    'callback' => 'remove_feed_links'
                ),
                'disable_feeds' => array(
                    'title' => 'Disable Feeds',
                    'description' => 'Completely disables RSS, Atom and RDF feeds.',
                    'callback' => 'disable_feeds'
                ),
            ),
            'comments' => array(
                'comment_script' => array(
                    'title' => 'Optimize Comment Script',
                    'description' => 'Loads comment reply script only on pages with comments.',
                    'callback' => 'optimize_comment_script'
                ),
                'recent_comments_style' => array(
                    'title' => 'Remove Recent Comments Style',
                    'description' => 'Removes inline CSS for recent comments widget.',
                    'callback' => 'remove_recent_comments_style'
                ),
                'comment_hyperlinks' => array(
                    'title' => 'Disable Comment Hyperlinks',
                    'description' => 'Prevents automatic hyperlink creation in comments.',
                    'callback' => 'disable_comment_hyperlinks'
                ),
            ),
            'miscellaneous' => array(
                'wlw_manifest' => array(
                    'title' => 'Remove WLW Manifest',
                    'description' => 'Removes Windows Live Writer manifest link.',
                    'callback' => 'remove_wlw_manifest'
                ),
                'rsd_link' => array(
                    'title' => 'Remove RSD Link',
                    'description' => 'Removes Really Simple Discovery link.',
                    'callback' => 'remove_rsd_link'
                ),
                'shortlink' => array(
                    'title' => 'Remove Shortlink',
                    'description' => 'Removes shortlink from the HTML head.',
                    'callback' => 'remove_shortlink'
                ),
                'dns_prefetch' => array(
                    'title' => 'Remove DNS Prefetch',
                    'description' => 'Removes DNS prefetch hints from the HTML head.',
                    'callback' => 'remove_dns_prefetch'
                ),
                'heartbeat' => array(
                    'title' => 'Reduce Heartbeat Interval',
                    'description' => 'Increases the interval of the WordPress Heartbeat API.',
                    'callback' => 'reduce_heartbeat_interval'
                ),
                'favicon' => array(
                    'title' => 'Normalize Favicon',
                    'description' => 'Provides a default favicon to prevent 404 errors.',
                    'callback' => 'normalize_favicon'
                ),
                'login_logo_url' => array(
                    'title' => 'Normalize Login Logo URL',
                    'description' => 'Changes the login logo link to your site's homepage.',
                    'callback' => 'normalize_login_logo_url'
                ),
                'login_logo_title' => array(
                    'title' => 'Normalize Login Logo Title',
                    'description' => 'Changes the login logo title to your site's name.',
                    'callback' => 'normalize_login_logo_title'
                ),
                'login_language' => array(
                    'title' => 'Disable Login Language Selector',
                    'description' => 'Removes the language selector from the login page.',
                    'callback' => 'disable_login_language_selector'
                ),
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
                ?>
                <div class="wp-feature-remover-tabs">
                    <?php
                    foreach ($this->features as $category => $category_features) {
                        $category_title = ucfirst($category);
                        echo "<button class='wp-feature-remover-tab-link' data-tab='{$category}'>{$category_title}</button>";
                    }
                    ?>
                </div>
                <?php
                foreach ($this->features as $category => $category_features) {
                    echo "<div id='{$category}' class='wp-feature-remover-tab-content'>";
                    echo "<h2>" . ucfirst($category) . " Features</h2>";
                    echo "<table class='form-table'>";
                    foreach ($category_features as $id => $feature) {
                        $checked = isset($this->options[$id]) ? checked($this->options[$id], true, false) : '';
                        echo "<tr>";
                        echo "<th scope='row'>{$feature['title']}</th>";
                        echo "<td>";
                        echo "<label><input type='checkbox' id='{$id}' name='wp_feature_remover_options[{$id}]' value='1' {$checked} />";
                        echo " Enable</label>";
                        echo "<p class='description'>{$feature['description']}</p>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    echo "</div>";
                }
                ?>
                <?php submit_button(); ?>
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
    }

    public function sanitize($input)
    {
        $new_input = array();
        foreach ($this->features as $category => $category_features) {
            foreach ($category_features as $id => $feature) {
                $new_input[$id] = isset($input[$id]) ? true : false;
            }
        }
        return $new_input;
    }

    public function remove_features()
    {
        $this->options = get_option('wp_feature_remover_options');

        if (!$this->options) {
            return;
        }

        foreach ($this->features as $category_features) {
            foreach ($category_features as $id => $feature) {
                if (isset($this->options[$id]) && $this->options[$id] && method_exists($this, $feature['callback'])) {
                    $this->{$feature['callback']}();
                }
            }
        }
    }

    public function enqueue_admin_scripts($hook)
    {
        if ('settings_page_wp-feature-remover' !== $hook) {
            return;
        }
        wp_enqueue_style('wp-feature-remover-admin', plugins_url('admin-styles.css', __FILE__), array(), '0.2.0');
        wp_enqueue_script('wp-feature-remover-admin', plugins_url('admin-scripts.js', __FILE__), array('jquery'), '0.2.0', true);
    }

    // Feature removal methods
    private function remove_generator_tag()
    {
        remove_action('wp_head', 'wp_generator');
    }

    private function remove_script_style_versions($src)
    {
        if (strpos($src, 'ver=')) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
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

    private function remove_wlw_manifest()
    {
        remove_action('wp_head', 'wlwmanifest_link');
    }

    private function remove_rsd_link()
    {
        remove_action('wp_head', 'rsd_link');
    }

    private function remove_shortlink()
    {
        remove_action('wp_head', 'wp_shortlink_wp_head');
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

    private function disable_login_language_selector()
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

// Activation hook
register_activation_hook(__FILE__, 'wp_feature_remover_activate');
function wp_feature_remover_activate()
{
    // You can add any initialization code here
    // For example, set default options if they don't exist
    if (!get_option('wp_feature_remover_options')) {
        update_option('wp_feature_remover_options', array());
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'wp_feature_remover_deactivate');
function wp_feature_remover_deactivate()
{
    // You can add any cleanup code here
    // For example, remove the plugin's options
    // delete_option('wp_feature_remover_options');
}