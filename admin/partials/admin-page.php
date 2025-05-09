<div class="wrap wpalw-admin-page">
    <h1><?php _e('Animated Live Wall', 'wp-animated-live-wall'); ?></h1>

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

                <div class="wpalw-actions">
                    <button type="button" class="button button-primary" id="wpalw-add-images">
                        <span class="dashicons dashicons-plus"></span> <?php _e('Add Images', 'wp-animated-live-wall'); ?>
                    </button>
                </div>

                <div class="wpalw-image-list" id="wpalw-image-list">
                    <?php
                    $images = get_option('wpalw_images', array());
                    if (!empty($images)) :
                        foreach ($images as $image_id) :
                            $image_url = wp_get_attachment_image_src($image_id, 'medium');
                            if ($image_url) :
                    ?>
                                <div class="wpalw-image-item" data-id="<?php echo esc_attr($image_id); ?>">
                                    <input type="hidden" name="wpalw_images[]" value="<?php echo esc_attr($image_id); ?>">
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

            <form method="post" action="options.php">
                <?php
                settings_fields('wpalw_settings');
                $options = get_option('wpalw_options', array(
                    'animation_speed' => 5000,
                    'columns' => 4,
                ));
                ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="wpalw_animation_speed"><?php _e('Animation Speed (ms)', 'wp-animated-live-wall'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="wpalw_animation_speed" name="wpalw_options[animation_speed]"
                                value="<?php echo esc_attr($options['animation_speed']); ?>" min="1000" step="100">
                            <p class="description"><?php _e('Time in milliseconds between image changes (minimum 1000ms).', 'wp-animated-live-wall'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="wpalw_columns"><?php _e('Grid Columns', 'wp-animated-live-wall'); ?></label>
                        </th>
                        <td>
                            <select id="wpalw_columns" name="wpalw_options[columns]">
                                <?php for ($i = 1; $i <= 12; $i++) : ?>
                                    <option value="<?php echo $i; ?>" <?php selected($options['columns'], $i); ?>>
                                        <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <p class="description"><?php _e('Number of columns in the image grid.', 'wp-animated-live-wall'); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Settings', 'wp-animated-live-wall')); ?>
            </form>
        </div>

        <div id="shortcode-tab" class="tab-pane">
            <h2><?php _e('Shortcode', 'wp-animated-live-wall'); ?></h2>
            <p><?php _e('Use the following shortcode to display the animated live wall on any page or post:', 'wp-animated-live-wall'); ?></p>

            <div class="wpalw-shortcode-box">
                <code>[animated_live_wall]</code>
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
                        <td><code>columns</code></td>
                        <td><?php echo esc_html($options['columns']); ?></td>
                        <td><?php _e('Number of columns in the image grid.', 'wp-animated-live-wall'); ?></td>
                    </tr>
                </tbody>
            </table>

            <h4><?php _e('Example', 'wp-animated-live-wall'); ?></h4>
            <div class="wpalw-shortcode-box">
                <code>[animated_live_wall columns="6"]</code>
                <button type="button" class="button wpalw-copy-shortcode">
                    <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'wp-animated-live-wall'); ?>
                </button>
            </div>
        </div>
    </div>
</div>