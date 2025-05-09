<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get all walls
$walls = get_option('wpalw_walls', array());

// Get global settings
$global_settings = get_option('wpalw_global_options', array());
$default_rows = isset($global_settings['default_rows']) ? $global_settings['default_rows'] : 3;
$default_columns = isset($global_settings['default_columns']) ? $global_settings['default_columns'] : 4;

// Get current wall ID from GET parameter or use default
$current_wall_id = isset($_GET['wall']) ? sanitize_text_field($_GET['wall']) : 'default';

// Get active tab from URL
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'global';

// Find current wall
$current_wall = null;
foreach ($walls as $wall) {
    if ($wall['id'] === $current_wall_id) {
        $current_wall = $wall;
        break;
    }
}

// If wall not found, use first available wall
if (!$current_wall && !empty($walls)) {
    $current_wall = $walls[0];
    $current_wall_id = $current_wall['id'];
}
?>

<div class="wrap">
    <h1><?php echo esc_html__('Animated Live Wall', 'wp-animated-live-wall'); ?></h1>

    <div id="wpalw-admin-tabs" class="nav-tab-wrapper">
        <a href="#tab-global" class="nav-tab <?php echo $active_tab === 'global' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('Global Settings', 'wp-animated-live-wall'); ?></a>
        <a href="#tab-walls" class="nav-tab <?php echo $active_tab === 'walls' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('Manage Walls', 'wp-animated-live-wall'); ?></a>
    </div>

    <!-- Global Settings Tab -->
    <div id="tab-global" class="wpalw-tab-content" style="<?php echo $active_tab === 'global' ? '' : 'display:none;'; ?>">
        <form method="post" action="options.php" class="wpalw-settings-form">
            <?php settings_fields('wpalw_settings'); ?>
            <h2><?php echo esc_html__('Global Settings', 'wp-animated-live-wall'); ?></h2>
            <p class="description"><?php echo esc_html__('Set default values for all animated live walls.', 'wp-animated-live-wall'); ?></p>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Default Rows', 'wp-animated-live-wall'); ?></th>
                    <td>
                        <input type="number" name="wpalw_global_options[default_rows]" value="<?php echo esc_attr($default_rows); ?>" min="1" max="12" step="1" />
                        <p class="description">
                            <?php echo esc_html__('Default number of rows for new walls and shortcodes.', 'wp-animated-live-wall'); ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Default Columns', 'wp-animated-live-wall'); ?></th>
                    <td>
                        <input type="number" name="wpalw_global_options[default_columns]" value="<?php echo esc_attr($default_columns); ?>" min="1" max="12" step="1" />
                        <p class="description">
                            <?php echo esc_html__('Default number of columns for new walls and shortcodes.', 'wp-animated-live-wall'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Global Settings', 'wp-animated-live-wall')); ?>
        </form>
    </div>

    <!-- Manage Walls Tab -->
    <div id="tab-walls" class="wpalw-tab-content" style="<?php echo $active_tab === 'walls' ? '' : 'display:none;'; ?>">
        <div class="wpalw-walls-bar">
            <div class="wpalw-walls-dropdown">
                <select id="wpalw-select-wall">
                    <?php foreach ($walls as $wall) : ?>
                        <option value="<?php echo esc_attr($wall['id']); ?>" <?php selected($wall['id'], $current_wall_id); ?>>
                            <?php echo esc_html($wall['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="button" class="button" id="wpalw-add-wall">
                    <span class="dashicons dashicons-plus"></span> <?php _e('Add New Wall', 'wp-animated-live-wall'); ?>
                </button>

                <?php if ($current_wall && $current_wall['id'] !== 'default') : ?>
                    <button type="button" class="button" id="wpalw-remove-wall" data-id="<?php echo esc_attr($current_wall['id']); ?>">
                        <span class="dashicons dashicons-trash"></span> <?php _e('Delete Wall', 'wp-animated-live-wall'); ?>
                    </button>
                <?php endif; ?>
            </div>

            <div class="wpalw-wall-title">
                <input type="text" id="wpalw-wall-name" value="<?php echo $current_wall ? esc_attr($current_wall['name']) : ''; ?>" placeholder="<?php _e('Wall Name', 'wp-animated-live-wall'); ?>">
                <button type="button" class="button button-primary" id="wpalw-save-wall-name" data-id="<?php echo $current_wall ? esc_attr($current_wall['id']) : ''; ?>">
                    <?php _e('Save Name', 'wp-animated-live-wall'); ?>
                </button>
            </div>
        </div>

        <div class="nav-tab-wrapper">
            <a href="#images-tab" class="nav-tab nav-tab-active"><?php _e('Images', 'wp-animated-live-wall'); ?></a>
            <a href="#settings-tab" class="nav-tab"><?php _e('Settings', 'wp-animated-live-wall'); ?></a>
            <a href="#shortcode-tab" class="nav-tab"><?php _e('Shortcode', 'wp-animated-live-wall'); ?></a>
        </div>

        <div class="tab-content">
            <div id="images-tab" class="tab-pane active">
                <h2><?php _e('Manage Live Wall Images', 'wp-animated-live-wall'); ?></h2>
                <p><?php _e('Add, remove, and reorder the images for your animated live wall.', 'wp-animated-live-wall'); ?></p>

                <form method="post" action="options.php" id="wpalw-images-form">
                    <?php settings_fields('wpalw_settings'); ?>
                    <input type="hidden" id="wpalw-current-wall-id" name="wpalw_current_wall_id" value="<?php echo esc_attr($current_wall_id); ?>">

                    <div class="wpalw-actions">
                        <button type="button" class="button button-primary" id="wpalw-add-images" data-wall-id="<?php echo esc_attr($current_wall_id); ?>">
                            <span class="dashicons dashicons-plus"></span> <?php _e('Add Images', 'wp-animated-live-wall'); ?>
                        </button>
                    </div>

                    <div class="wpalw-image-list" id="wpalw-image-list" data-wall-id="<?php echo esc_attr($current_wall_id); ?>">
                        <?php
                        $images = $current_wall ? $current_wall['images'] : array();
                        if (!empty($images)) :
                            foreach ($images as $image_id) :
                                $image_url = wp_get_attachment_image_src($image_id, 'medium');
                                if ($image_url) :
                        ?>
                                    <div class="wpalw-image-item" data-id="<?php echo esc_attr($image_id); ?>">
                                        <input type="hidden" name="wpalw_image_ids[]" value="<?php echo esc_attr($image_id); ?>">
                                        <div class="wpalw-image-preview">
                                            <img src="<?php echo esc_url($image_url[0]); ?>" alt="">
                                        </div>
                                        <div class="wpalw-image-actions">
                                            <a href="#" class="wpalw-remove-image">
                                                <span class="dashicons dashicons-trash"></span>
                                            </a>
                                        </div>
                                    </div>
                        <?php
                                endif;
                            endforeach;
                        endif;
                        ?>
                    </div>

                    <?php submit_button(__('Save Images', 'wp-animated-live-wall')); ?>
                </form>
            </div>

            <div id="settings-tab" class="tab-pane">
                <h2><?php _e('Live Wall Settings', 'wp-animated-live-wall'); ?></h2>
                <p><?php _e('Configure the behavior and appearance of your animated live wall.', 'wp-animated-live-wall'); ?></p>

                <form method="post" action="options.php" id="wpalw-settings-form">
                    <?php
                    settings_fields('wpalw_settings');
                    $animation_speed = $current_wall ? $current_wall['animation_speed'] : 5000;
                    $columns = $current_wall ? $current_wall['columns'] : 4;
                    ?>

                    <input type="hidden" name="wpalw_wall_id" value="<?php echo esc_attr($current_wall_id); ?>">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wpalw_animation_speed"><?php _e('Animation Speed (ms)', 'wp-animated-live-wall'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="wpalw_animation_speed" name="wpalw_animation_speed" value="<?php echo esc_attr($animation_speed); ?>" min="1000" step="100">
                                <p class="description"><?php _e('Time in milliseconds between image changes (minimum 1000ms).', 'wp-animated-live-wall'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wpalw_columns"><?php _e('Grid Columns', 'wp-animated-live-wall'); ?></label>
                            </th>
                            <td>
                                <select id="wpalw_columns" name="wpalw_columns">
                                    <?php for ($i = 1; $i <= 12; $i++) : ?>
                                        <option value="<?php echo $i; ?>" <?php selected($columns, $i); ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <p class="description"><?php _e('Number of columns in the image grid.', 'wp-animated-live-wall'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wpalw_rows"><?php _e('Grid Rows', 'wp-animated-live-wall'); ?></label>
                            </th>
                            <td>
                                <select id="wpalw_rows" name="wpalw_rows">
                                    <?php
                                    $rows = $current_wall ? (isset($current_wall['rows']) ? $current_wall['rows'] : 3) : 3;
                                    for ($i = 1; $i <= 12; $i++) :
                                    ?>
                                        <option value="<?php echo $i; ?>" <?php selected($rows, $i); ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <p class="description"><?php _e('Number of rows in the image grid.', 'wp-animated-live-wall'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(__('Save Settings', 'wp-animated-live-wall')); ?>
                </form>
            </div>

            <div id="shortcode-tab" class="tab-pane">
                <h2><?php _e('Shortcode', 'wp-animated-live-wall'); ?></h2>
                <p><?php _e('Use the following shortcode to display this specific live wall on any page or post:', 'wp-animated-live-wall'); ?></p>

                <div class="wpalw-shortcode-box">
                    <code>[animated_live_wall id="<?php echo esc_attr($current_wall_id); ?>"]</code>
                    <button type="button" class="button wpalw-copy-shortcode">
                        <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'wp-animated-live-wall'); ?>
                    </button>
                </div>

                <h3><?php _e('Shortcode Parameters', 'wp-animated-live-wall'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Parameter', 'wp-animated-live-wall'); ?></th>
                            <th><?php _e('Default', 'wp-animated-live-wall'); ?></th>
                            <th><?php _e('Description', 'wp-animated-live-wall'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>id</code></td>
                            <td>default</td>
                            <td><?php _e('ID of the live wall to display.', 'wp-animated-live-wall'); ?></td>
                        </tr>
                        <tr>
                            <td><code>columns</code></td>
                            <td><?php echo esc_html($columns); ?></td>
                            <td><?php _e('Number of columns in the image grid.', 'wp-animated-live-wall'); ?></td>
                        </tr>
                        <tr>
                            <td><code>rows</code></td>
                            <td><?php echo esc_html($rows); ?></td>
                            <td><?php _e('Number of rows in the image grid.', 'wp-animated-live-wall'); ?></td>
                        </tr>
                    </tbody>
                </table>

                <h4><?php _e('Example', 'wp-animated-live-wall'); ?></h4>
                <div class="wpalw-shortcode-box">
                    <code>[animated_live_wall id="<?php echo esc_attr($current_wall_id); ?>" columns="6" rows="4"]</code>
                    <button type="button" class="button wpalw-copy-shortcode">
                        <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'wp-animated-live-wall'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Haupttab-Navigation
        $('#wpalw-admin-tabs a').click(function(e) {
            e.preventDefault();
            var target = $(this).attr('href');

            // Update active tab
            $('#wpalw-admin-tabs a').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            // Show target content, hide others
            $('.wpalw-tab-content').hide();
            $(target).show();

            // Wenn Manage Walls-Tab ausgewählt wird, zeige standardmäßig den Images-Tab
            if (target === '#tab-walls') {
                $('.nav-tab-wrapper a[href="#images-tab"]').addClass('nav-tab-active').siblings().removeClass('nav-tab-active');
                $('.tab-pane').removeClass('active').hide();
                $('#images-tab').addClass('active').show();
            }
        });

        // Subtab-Navigation für Wall-Einstellungen
        $('.nav-tab-wrapper a').click(function(e) {
            e.preventDefault();

            // Aktualisiere aktiven Subtab
            $(this).parent().find('a').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            // Zeige Ziel-Content, verstecke andere
            var target = $(this).attr('href');
            $('.tab-pane').removeClass('active').hide();
            $(target).addClass('active').show();
        });

        // Initialer Zustand: Falls Manage Walls aktiv ist, zeige Images-Tab
        if ($('#tab-walls').is(':visible')) {
            $('.nav-tab-wrapper a[href="#images-tab"]').addClass('nav-tab-active').siblings().removeClass('nav-tab-active');
            $('.tab-pane').removeClass('active').hide();
            $('#images-tab').addClass('active').show();
        }

        // Kopieren von Shortcodes in die Zwischenablage
        $('.wpalw-copy-shortcode').click(function() {
            var shortcodeText = $(this).prev('code').text();
            navigator.clipboard.writeText(shortcodeText).then(function() {
                // Feedback für den Benutzer
                var originalText = $('.wpalw-copy-shortcode').html();
                $('.wpalw-copy-shortcode').html('<span class="dashicons dashicons-yes"></span> Kopiert!');
                setTimeout(function() {
                    $('.wpalw-copy-shortcode').html(originalText);
                }, 2000);
            });
        });
    });
</script>