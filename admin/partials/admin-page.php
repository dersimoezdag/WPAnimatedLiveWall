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
$default_animation_speed = isset($global_settings['default_animation_speed']) ? $global_settings['default_animation_speed'] : 5000;
$default_gap = isset($global_settings['default_gap']) ? $global_settings['default_gap'] : 4;

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
        <a href="#tab-walls" class="nav-tab <?php echo $active_tab === 'walls' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('Manage Walls', 'wp-animated-live-wall'); ?></a>
        <a href="#tab-global" class="nav-tab <?php echo $active_tab === 'global' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('Global Settings', 'wp-animated-live-wall'); ?></a>
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

                <?php
                // Prüfe, ob genügend Bilder vorhanden sind
                $image_count = 0;
                $required_images = 0;

                if ($current_wall && isset($current_wall['images']) && is_array($current_wall['images'])) {
                    $image_count = count($current_wall['images']);
                    $rows = isset($current_wall['rows']) ? intval($current_wall['rows']) : 3;
                    $columns = isset($current_wall['columns']) ? intval($current_wall['columns']) : 4;
                    $required_images = $rows * $columns;

                    if ($image_count > 0 && $image_count < $required_images) : ?>
                        <div class="notice notice-warning inline">
                            <p>
                                <strong><?php _e('Hinweis:', 'wp-animated-live-wall'); ?></strong>
                                <?php printf(
                                    __('Sie haben %1$d Bilder ausgewählt, benötigen aber mindestens %2$d (Zeilen × Spalten), um alle Kacheln ohne Wiederholungen zu füllen. Bei zu wenigen Bildern können einige Bilder gleichzeitig mehrfach erscheinen.', 'wp-animated-live-wall'),
                                    $image_count,
                                    $required_images
                                ); ?>
                            </p>
                        </div>
                <?php endif;
                }
                ?>

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
                    $gap = isset($current_wall['gap']) ? $current_wall['gap'] : 4;
                    $transition = isset($current_wall['transition']) ? $current_wall['transition'] : 400;                    // Prepare selected effects for the form
                    $current_wall_selected_effects = array();
                    if ($current_wall && isset($current_wall['selected_effects']) && is_array($current_wall['selected_effects'])) {
                        $current_wall_selected_effects = $current_wall['selected_effects'];
                    } elseif (!empty($available_effects)) {
                        // Wenn keine ausgewählten Effekte definiert sind, standardmäßig alle verfügbaren auswählen
                        $current_wall_selected_effects = array_keys($available_effects);
                    } ?>

                    <input type="hidden" name="wpalw_wall_id" id="wpalw-wall-id" value="<?php echo esc_attr($current_wall_id); ?>">
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
                                <label for="wpalw_transition"><?php _e('Transition Duration (ms)', 'wp-animated-live-wall'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="wpalw_transition" name="wpalw_transition" value="<?php echo esc_attr($transition); ?>" min="100" max="2000" step="50">
                                <p class="description"><?php _e('Duration of the fade transition effect in milliseconds.', 'wp-animated-live-wall'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wpalw_gap"><?php _e('Grid Gap (px)', 'wp-animated-live-wall'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="wpalw_gap" name="wpalw_gap" value="<?php echo esc_attr($gap); ?>" min="0" max="20" step="1">
                                <p class="description"><?php _e('Space between images in pixels.', 'wp-animated-live-wall'); ?></p>
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
                        <tr>
                            <th scope="row">
                                <label for="wpalw_tiles_at_once"><?php _e('Changes at Once', 'wp-animated-live-wall'); ?></label>
                            </th>
                            <td>
                                <select id="wpalw_tiles_at_once" name="wpalw_tiles_at_once">
                                    <?php
                                    $tiles_at_once = $current_wall ? (isset($current_wall['tiles_at_once']) ? $current_wall['tiles_at_once'] : 1) : 1;
                                    for ($i = 1; $i <= 5; $i++) :
                                    ?>
                                        <option value="<?php echo $i; ?>" <?php selected($tiles_at_once, $i); ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <p class="description"><?php _e('Number of tiles that change simultaneously with each animation cycle.', 'wp-animated-live-wall'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Transition Effects', 'wp-animated-live-wall'); ?></th>
                            <td>
                                <?php if (!empty($available_effects)) : ?>
                                    <fieldset>
                                        <legend class="screen-reader-text"><span><?php _e('Transition Effects', 'wp-animated-live-wall'); ?></span></legend>
                                        <?php foreach ($available_effects as $effect_key => $effect_name) : ?>
                                            <label style="margin-right: 15px;">
                                                <input type="checkbox" name="wpalw_selected_effects[]" value="<?php echo esc_attr($effect_key); ?>" <?php checked(in_array($effect_key, $current_wall_selected_effects)); ?>>
                                                <?php echo esc_html($effect_name); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </fieldset>
                                    <p class="description"><?php _e('Select the transition effects to be used for this wall. If none are selected, it will default to Crossfade.', 'wp-animated-live-wall'); ?></p>
                                <?php else : ?>
                                    <p><?php _e('No transition effects available.', 'wp-animated-live-wall'); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wpalw_keyvisual_mode">Keyvisual Mode</label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpalw_keyvisual_mode" name="wpalw_keyvisual_mode" value="1" <?php checked(isset($current_wall['keyvisual_mode']) && $current_wall['keyvisual_mode']); ?>>
                                <span class="description">Enable Keyvisual (full width, overlay with title & subtitle)</span>
                            </td>
                        </tr>
                        <tr class="wpalw-keyvisual-fields" style="<?php echo (isset($current_wall['keyvisual_mode']) && $current_wall['keyvisual_mode']) ? '' : 'display:none;'; ?>">
                            <th scope="row">
                                <label for="wpalw_keyvisual_title">Keyvisual Title</label>
                            </th>
                            <td>
                                <input type="text" id="wpalw_keyvisual_title" name="wpalw_keyvisual_title" value="<?php echo isset($current_wall['keyvisual_title']) ? esc_attr($current_wall['keyvisual_title']) : ''; ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr class="wpalw-keyvisual-fields" style="<?php echo (isset($current_wall['keyvisual_mode']) && $current_wall['keyvisual_mode']) ? '' : 'display:none;'; ?>">
                            <th scope="row">
                                <label for="wpalw_keyvisual_subtitle">Keyvisual Subtitle</label>
                            </th>
                            <td>
                                <input type="text" id="wpalw_keyvisual_subtitle" name="wpalw_keyvisual_subtitle" value="<?php echo isset($current_wall['keyvisual_subtitle']) ? esc_attr($current_wall['keyvisual_subtitle']) : ''; ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr class="wpalw-keyvisual-fields" style="<?php echo (isset($current_wall['keyvisual_mode']) && $current_wall['keyvisual_mode']) ? '' : 'display:none;'; ?>">
                            <th scope="row">
                                <label for="wpalw_keyvisual_bgcolor">Text Background Color</label>
                            </th>
                            <td>
                                <input type="text" id="wpalw_keyvisual_bgcolor" name="wpalw_keyvisual_bgcolor" class="wpalw-color-picker" value="<?php echo isset($current_wall['keyvisual_bgcolor']) ? esc_attr($current_wall['keyvisual_bgcolor']) : 'rgba(44, 62, 80, 0.8)'; ?>">
                                <p class="description">Select the background color for the keyvisual text. Use rgba format for transparency.</p>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(__('Save Settings', 'wp-animated-live-wall'), 'primary', 'submit-wall-settings'); ?>
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
                        <tr>
                            <td><code>animation_speed</code></td>
                            <td><?php echo esc_html($animation_speed); ?></td>
                            <td><?php _e('Time in milliseconds between image changes.', 'wp-animated-live-wall'); ?></td>
                        </tr>
                        <tr>
                            <td><code>transition</code></td>
                            <td><?php echo esc_html($transition); ?></td>
                            <td><?php _e('Duration of transition effect in milliseconds.', 'wp-animated-live-wall'); ?></td>
                        </tr>
                        <tr>
                            <td><code>gap</code></td>
                            <td><?php echo esc_html($gap); ?></td>
                            <td><?php _e('Gap between images in pixels (0-20).', 'wp-animated-live-wall'); ?></td>
                        </tr>
                        <tr>
                            <td><code>effects</code></td>
                            <td><?php echo !empty($current_wall_selected_effects) ? esc_html(implode(',', $current_wall_selected_effects)) : 'crossfade'; ?></td>
                            <td><?php _e('Comma-separated list of transition effects to use. Available effects: crossfade,zoomfade,slideup,slidedown,slideleft,slideright,rotate,blurfade,flip', 'wp-animated-live-wall'); ?></td>
                        </tr>
                        <tr>
                            <td><code>tiles_at_once</code></td>
                            <td><?php echo esc_html($tiles_at_once); ?></td>
                            <td><?php _e('Number of tiles that change simultaneously with each animation cycle.', 'wp-animated-live-wall'); ?></td>
                        </tr>
                        <tr>
                            <td><code>keyvisual_mode</code></td>
                            <td><?php echo isset($current_wall['keyvisual_mode']) && $current_wall['keyvisual_mode'] ? 'true' : 'false'; ?></td>
                            <td><?php _e('Enable or disable the keyvisual (full-width overlay with title and subtitle).', 'wp-animated-live-wall'); ?></td>
                        </tr>
                        <tr>
                            <td><code>keyvisual_title</code></td>
                            <td><?php echo isset($current_wall['keyvisual_title']) ? esc_html($current_wall['keyvisual_title']) : ''; ?></td>
                            <td><?php _e('Title text for the keyvisual overlay.', 'wp-animated-live-wall'); ?></td>
                        </tr>
                        <tr>
                            <td><code>keyvisual_subtitle</code></td>
                            <td><?php echo isset($current_wall['keyvisual_subtitle']) ? esc_html($current_wall['keyvisual_subtitle']) : ''; ?></td>
                            <td><?php _e('Subtitle text for the keyvisual overlay.', 'wp-animated-live-wall'); ?></td>
                        </tr>
                        <tr>
                            <td><code>keyvisual_bgcolor</code></td>
                            <td><?php echo isset($current_wall['keyvisual_bgcolor']) ? esc_html($current_wall['keyvisual_bgcolor']) : 'rgba(44, 62, 80, 0.8)'; ?></td>
                            <td><?php _e('Background color for the keyvisual text (supports rgba for transparency).', 'wp-animated-live-wall'); ?></td>
                        </tr>
                        <tr>
                            <td><code>keyvisual_bgcolor</code></td>
                            <td><?php echo isset($current_wall['keyvisual_bgcolor']) ? esc_html($current_wall['keyvisual_bgcolor']) : 'rgba(44, 62, 80, 0.8)'; ?></td>
                            <td><?php _e('Background color for the keyvisual text. Use rgba format for transparency.', 'wp-animated-live-wall'); ?></td>
                        </tr>
                    </tbody>
                </table>
                <h4><?php _e('Examples', 'wp-animated-live-wall'); ?></h4>
                <div class="wpalw-shortcode-box">
                    <code>[animated_live_wall id="<?php echo esc_attr($current_wall_id); ?>" columns="6" rows="4"]</code>
                    <button type="button" class="button wpalw-copy-shortcode">
                        <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'wp-animated-live-wall'); ?>
                    </button>
                </div>

                <h5><?php _e('With Custom Animation', 'wp-animated-live-wall'); ?></h5>
                <div class="wpalw-shortcode-box">
                    <code>[animated_live_wall id="<?php echo esc_attr($current_wall_id); ?>" columns="4" rows="3" animation_speed="3000" transition="500" gap="5" effects="<?php echo !empty($current_wall_selected_effects) ? esc_attr(implode(',', array_slice($current_wall_selected_effects, 0, 3))) : 'crossfade,slideright,blurfade'; ?>" tiles_at_once="3"]</code>
                    <button type="button" class="button wpalw-copy-shortcode">
                        <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'wp-animated-live-wall'); ?>
                    </button>
                </div>
                <h5><?php _e('With Keyvisual', 'wp-animated-live-wall'); ?></h5>
                <div class="wpalw-shortcode-box">
                    <code>[animated_live_wall id="<?php echo esc_attr($current_wall_id); ?>" keyvisual_mode="true" keyvisual_title="<?php echo isset($current_wall['keyvisual_title']) ? esc_attr($current_wall['keyvisual_title']) : 'Mein Keyvisual Titel'; ?>" keyvisual_subtitle="<?php echo isset($current_wall['keyvisual_subtitle']) ? esc_attr($current_wall['keyvisual_subtitle']) : 'Ein ansprechender Untertitel'; ?>" keyvisual_bgcolor="<?php echo isset($current_wall['keyvisual_bgcolor']) ? esc_attr($current_wall['keyvisual_bgcolor']) : 'rgba(44, 62, 80, 0.8)'; ?>"]</code>
                    <button type="button" class="button wpalw-copy-shortcode">
                        <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'wp-animated-live-wall'); ?>
                    </button>
                </div>

                <h5><?php _e('Available Animation Effects', 'wp-animated-live-wall'); ?></h5>
                <p><?php _e('Here are all the animation effects that can be used in the "effects" parameter:', 'wp-animated-live-wall'); ?></p>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Effect Key', 'wp-animated-live-wall'); ?></th>
                            <th><?php _e('Description', 'wp-animated-live-wall'); ?></th>
                            <th><?php _e('Preview', 'wp-animated-live-wall'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($available_effects as $effect_key => $effect_name) : ?>
                            <tr>
                                <td><code><?php echo esc_html($effect_key); ?></code></td>
                                <td><?php echo esc_html($effect_name); ?></td>
                                <td>
                                    <label class="wpalw-effect-status">
                                        <?php if (isset($current_wall_selected_effects) && in_array($effect_key, $current_wall_selected_effects)) : ?>
                                            <span class="dashicons dashicons-yes-alt" style="color: green;" title="<?php _e('Active for this wall', 'wp-animated-live-wall'); ?>"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-no-alt" style="color: #ccc;" title="<?php _e('Not active for this wall', 'wp-animated-live-wall'); ?>"></span>
                                        <?php endif; ?>
                                        <?php _e('Used in this wall', 'wp-animated-live-wall'); ?>
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h4><?php _e('Generate Custom Shortcode', 'wp-animated-live-wall'); ?></h4>
                <p><?php _e('Generate a shortcode with your selected settings and animation effects.', 'wp-animated-live-wall'); ?></p>

                <div class="wpalw-shortcode-generator">
                    <div class="wpalw-shortcode-controls">
                        <button type="button" id="generate-custom-shortcode" class="button button-primary">
                            <?php _e('Generate Shortcode with Current Settings', 'wp-animated-live-wall'); ?>
                        </button>
                    </div>

                    <div class="wpalw-shortcode-preview" style="margin-top: 15px;">
                        <textarea id="custom-shortcode-preview" rows="3" style="width: 100%; font-family: monospace;" readonly></textarea>
                        <p class="description">
                            <?php _e('This shortcode includes your current wall settings and all selected animation effects.', 'wp-animated-live-wall'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Settings Tab -->
    <div id="tab-global" class="wpalw-tab-content" style="<?php echo $active_tab === 'global' ? '' : 'display:none;'; ?>">
        <form method="post" action="options.php" class="wpalw-settings-form">
            <?php settings_fields('wpalw_global_settings'); ?>
            <h2><?php echo esc_html__('Global Settings', 'wp-animated-live-wall'); ?></h2>
            <p class="description"><?php echo esc_html__('Set default values for all animated live walls.', 'wp-animated-live-wall'); ?></p>

            <!-- Hidden field to ensure we're only updating global settings, not wall data -->
            <input type="hidden" name="wpalw_action" value="update_global_settings">

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
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Default Animation Speed', 'wp-animated-live-wall'); ?></th>
                    <td>
                        <input type="number" name="wpalw_global_options[default_animation_speed]" value="<?php echo esc_attr($default_animation_speed); ?>" min="1000" step="100" />
                        <p class="description">
                            <?php echo esc_html__('Default animation speed in milliseconds (min. 1000ms).', 'wp-animated-live-wall'); ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Default Grid Gap', 'wp-animated-live-wall'); ?></th>
                    <td>
                        <input type="number" name="wpalw_global_options[default_gap]" value="<?php echo esc_attr($default_gap); ?>" min="0" max="20" step="1" />
                        <p class="description">
                            <?php echo esc_html__('Default gap between images in pixels (0-20px).', 'wp-animated-live-wall'); ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Default Tiles at Once', 'wp-animated-live-wall'); ?></th>
                    <td>
                        <select name="wpalw_global_options[default_tiles_at_once]">
                            <?php
                            $default_tiles_at_once = isset($global_settings['default_tiles_at_once']) ? $global_settings['default_tiles_at_once'] : 1;
                            for ($i = 1; $i <= 5; $i++) :
                            ?>
                                <option value="<?php echo $i; ?>" <?php selected($default_tiles_at_once, $i); ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <p class="description">
                            <?php echo esc_html__('Default number of tiles that change simultaneously with each animation cycle.', 'wp-animated-live-wall'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Global Settings', 'wp-animated-live-wall')); ?>
        </form>
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

        // Keyvisual mode toggle
        $('#wpalw_keyvisual_mode').change(function() {
            if ($(this).is(':checked')) {
                $('.wpalw-keyvisual-fields').show();
            } else {
                $('.wpalw-keyvisual-fields').hide();
            }
        });
    });
</script>