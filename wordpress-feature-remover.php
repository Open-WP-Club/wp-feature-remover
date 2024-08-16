<?php

/**
 * Plugin Name: WordPress Feature Remover
 * Description: Removes and optimizes unnecessary WordPress features
 * Version: 2.1
 * Author: Your Name
 */

class WordPress_Feature_Remover
{
    private $options;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('init', array($this, 'remove_features'));
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
        $this->options = get_option('wp_feature_remover_options');
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

        add_settings_section(
            'wp_feature_remover_section',
            'Select Features to Remove',
            array($this, 'print_section_info'),
            'wp-feature-remover'
        );

        $features = array(
            'generator_tag' => 'Remove Generator Tag',
            'script_style_versions' => 'Remove Script/Style Versions',
            'wlw_manifest' => 'Remove WLW Manifest',
            'rsd_link' => 'Remove RSD Link',
            'shortlink' => 'Remove Shortlink',
            'feed_generator' => 'Remove Feed Generator',
            'feed_links' => 'Remove Feed Links',
            'disable_feeds' => 'Disable Feeds',
            'dns_prefetch' => 'Remove DNS Prefetch',
            'jquery_migrate' => 'Remove jQuery Migrate',
            'emojis' => 'Disable Emojis',
            'comment_script' => 'Optimize Comment Script',
            'recent_comments_style' => 'Remove Recent Comments Style',
            'comment_hyperlinks' => 'Disable Comment Hyperlinks',
            'heartbeat' => 'Reduce Heartbeat Interval',
            'favicon' => 'Normalize Favicon',
            'login_logo_url' => 'Normalize Login Logo URL',
            'login_logo_title' => 'Normalize Login Logo Title',
            'login_language' => 'Disable Login Language Selector'
        );

        foreach ($features as $id => $title) {
            add_settings_field(
                $id,
                $title,
                array($this, 'create_checkbox'),
                'wp-feature-remover',
                'wp_feature_remover_section',
                array('id' => $id)
            );
        }
    }

    public function sanitize($input)
    {
        $new_input = array();
        foreach ($input as $key => $value) {
            $new_input[$key] = (isset($input[$key])) ? true : false;
        }
        return $new_input;
    }

    public function print_section_info()
    {
        print 'Select which features you want to remove or optimize:';
    }

    public function create_checkbox($args)
    {
        $id = $args['id'];
        $checked = isset($this->options[$id]) ? checked($this->options[$id], true, false) : '';
        echo "<input type='checkbox' id='$id' name='wp_feature_remover_options[$id]' value='1' $checked />";
    }

    public function remove_features()
    {
        $options = get_option('wp_feature_remover_options');

        if (!$options) {
            return;
        }

        if (isset($options['generator_tag']) && $options['generator_tag']) {
            remove_action('wp_head', 'wp_generator');
        }

        if (isset($options['script_style_versions']) && $options['script_style_versions']) {
            add_filter('style_loader_src', array($this, 'remove_version_query_arg'), 10, 2);
            add_filter('script_loader_src', array($this, 'remove_version_query_arg'), 10, 2);
        }

        if (isset($options['wlw_manifest']) && $options['wlw_manifest']) {
            remove_action('wp_head', 'wlwmanifest_link');
        }

        if (isset($options['rsd_link']) && $options['rsd_link']) {
            remove_action('wp_head', 'rsd_link');
        }

        if (isset($options['shortlink']) && $options['shortlink']) {
            remove_action('wp_head', 'wp_shortlink_wp_head');
        }

        if (isset($options['feed_generator']) && $options['feed_generator']) {
            add_filter('the_generator', '__return_false');
        }

        if (isset($options['feed_links']) && $options['feed_links']) {
            remove_action('wp_head', 'feed_links', 2);
            remove_action('wp_head', 'feed_links_extra', 3);
        }

        if (isset($options['disable_feeds']) && $options['disable_feeds']) {
            add_action('do_feed', array($this, 'disable_feed'), 1);
            add_action('do_feed_rdf', array($this, 'disable_feed'), 1);
            add_action('do_feed_rss', array($this, 'disable_feed'), 1);
            add_action('do_feed_rss2', array($this, 'disable_feed'), 1);
            add_action('do_feed_atom', array($this, 'disable_feed'), 1);
            add_action('do_feed_rss2_comments', array($this, 'disable_feed'), 1);
            add_action('do_feed_atom_comments', array($this, 'disable_feed'), 1);
            remove_action('wp_head', 'feed_links', 2);
            remove_action('wp_head', 'feed_links_extra', 3);
        }

        if (isset($options['dns_prefetch']) && $options['dns_prefetch']) {
            add_filter('wp_resource_hints', array($this, 'remove_dns_prefetch'), 10, 2);
        }

        if (isset($options['jquery_migrate']) && $options['jquery_migrate']) {
            add_action('wp_default_scripts', array($this, 'remove_jquery_migrate'));
        }

        if (isset($options['emojis']) && $options['emojis']) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
        }

        if (isset($options['comment_script']) && $options['comment_script']) {
            add_action('wp_enqueue_scripts', array($this, 'optimize_comment_script'));
        }

        if (isset($options['recent_comments_style']) && $options['recent_comments_style']) {
            add_action('widgets_init', array($this, 'remove_recent_comments_style'));
        }

        if (isset($options['comment_hyperlinks']) && $options['comment_hyperlinks']) {
            remove_filter('comment_text', 'make_clickable', 9);
        }

        if (isset($options['heartbeat']) && $options['heartbeat']) {
            add_filter('heartbeat_settings', array($this, 'reduce_heartbeat_frequency'));
        }

        if (isset($options['favicon']) && $options['favicon']) {
            add_action('do_faviconico', array($this, 'unset_default_favicon'));
        }

        if (isset($options['login_logo_url']) && $options['login_logo_url']) {
            add_filter('login_headerurl', array($this, 'custom_login_logo_url'));
        }

        if (isset($options['login_logo_title']) && $options['login_logo_title']) {
            add_filter('login_headertext', array($this, 'custom_login_logo_title'));
        }

        if (isset($options['login_language']) && $options['login_language']) {
            add_filter('login_display_language_dropdown', '__return_false');
        }
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

    public function remove_dns_prefetch($hints, $relation_type)
    {
        if ('dns-prefetch' === $relation_type) {
            return array_diff(wp_dependencies_unique_hosts(), $hints);
        }
        return $hints;
    }

    public function remove_jquery_migrate($scripts)
    {
        if (!is_admin() && isset($scripts->registered['jquery'])) {
            $script = $scripts->registered['jquery'];
            if ($script->deps) {
                $script->deps = array_diff($script->deps, array('jquery-migrate'));
            }
        }
    }

    public function optimize_comment_script()
    {
        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        } else {
            wp_dequeue_script('comment-reply');
        }
    }

    public function remove_recent_comments_style()
    {
        global $wp_widget_factory;
        remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
    }

    public function reduce_heartbeat_frequency($settings)
    {
        $settings['interval'] = 60;
        return $settings;
    }

    public function unset_default_favicon()
    {
        exit('data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
    }

    public function custom_login_logo_url()
    {
        return home_url();
    }

    public function custom_login_logo_title()
    {
        return get_bloginfo('name');
    }
}

$wordpress_feature_remover = new WordPress_Feature_Remover();
