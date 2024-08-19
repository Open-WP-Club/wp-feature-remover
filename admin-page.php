<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function wp_feature_remover_admin_page()
{
    global $wordpress_feature_remover;
    $options = get_option('wp_feature_remover_options');
?>
    <div class="wrap">
        <h1>WordPress Feature Remover Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_feature_remover_options_group');
            ?>
            <div class="wp-feature-remover-tabs">
                <?php
                foreach ($wordpress_feature_remover->features as $category => $category_features) {
                    $category_title = ucfirst($category);
                    echo "<button class='wp-feature-remover-tab-link' data-tab='{$category}'>{$category_title}</button>";
                }
                ?>
            </div>
            <?php
            foreach ($wordpress_feature_remover->features as $category => $category_features) {
                echo "<div id='{$category}' class='wp-feature-remover-tab-content'>";
                echo "<h2>" . ucfirst($category) . " Features</h2>";
                echo "<label class='wp-feature-remover-toggle-all'>";
                echo "<input type='checkbox' class='toggle-category' data-category='{$category}' /> Toggle All</label>";
                echo "<div class='wp-feature-remover-grid'>";
                foreach ($category_features as $id => $feature) {
                    $checked = isset($options[$id]) ? checked($options[$id], true, false) : '';
                    echo "<div class='wp-feature-remover-feature'>";
                    echo "<label>";
                    echo "<input type='checkbox' id='{$id}' name='wp_feature_remover_options[{$id}]' value='1' {$checked} />";
                    echo " {$feature['title']}</label>";
                    echo "<p class='description'>{$feature['description']}</p>";
                    echo "</div>";
                }
                echo "</div>";
                echo "</div>";
            }
            ?>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}
