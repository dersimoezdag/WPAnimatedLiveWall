<?php

/**
 * The main plugin class.
 */
class WP_Animated_Live_Wall
{
    /**
     * Initialize the plugin.
     */
    public function run()
    {
        // Register activation and deactivation hooks
        register_activation_hook(WPALW_PLUGIN_BASENAME, array($this, 'activate'));
        register_deactivation_hook(WPALW_PLUGIN_BASENAME, array($this, 'deactivate'));

        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles_scripts'));

        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles_scripts'));
        add_shortcode('animated_live_wall', array($this, 'live_wall_shortcode'));

        // AJAX handlers
        add_action('wp_ajax_wpalw_save_wall', array($this, 'ajax_save_wall'));
        add_action('wp_ajax_wpalw_remove_wall', array($this, 'ajax_remove_wall'));
        add_action('wp_ajax_wpalw_save_image', array($this, 'ajax_save_image'));
        add_action('wp_ajax_wpalw_remove_image', array($this, 'ajax_remove_image'));
        add_action('wp_ajax_wpalw_update_image_order', array($this, 'ajax_update_image_order'));
    }
    /**
     * Plugin activation.
     */
    public function activate()
    {
        // Default settings
        if (!get_option('wpalw_walls')) {
            $default_wall = array(
                'id' => 'default',
                'name' => __('Default Wall', 'wp-animated-live-wall'),
                'images' => array(),
                'animation_speed' => 5000,
                'columns' => 4,
                'rows' => 3
            );

            update_option('wpalw_walls', array($default_wall));
        }
    }

    /**
     * Plugin deactivation.
     */
    public function deactivate()
    {
        // Clean up if necessary
    }

    /**
     * Add admin menu items.
     */
    public function add_admin_menu()
    {
        add_submenu_page(
            'options-general.php',
            __('Animated Live Wall', 'wp-animated-live-wall'),
            __('Live Wall', 'wp-animated-live-wall'),
            'manage_options',
            'wp-animated-live-wall',
            array($this, 'admin_page')
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings()
    {
        register_setting(
            'wpalw_settings',
            'wpalw_walls',
            array($this, 'sanitize_walls_setting')
        );

        register_setting(
            'wpalw_settings',
            'wpalw_global_options',
            array($this, 'sanitize_global_options_setting')
        );
    }

    /**
     * Sanitize walls settings.
     */
    public function sanitize_walls_setting($input)
    {
        if (!is_array($input)) {
            return array();
        }

        $sanitized = array();

        foreach ($input as $wall) {
            if (!isset($wall['id']) || !isset($wall['name'])) {
                continue;
            }
            $sanitized_wall = array(
                'id' => sanitize_key($wall['id']),
                'name' => sanitize_text_field($wall['name']),
                'images' => isset($wall['images']) && is_array($wall['images']) ? $wall['images'] : array(),
                'animation_speed' => isset($wall['animation_speed']) ? absint($wall['animation_speed']) : 5000,
                'columns' => isset($wall['columns']) ? absint($wall['columns']) : 4,
                'rows' => isset($wall['rows']) ? absint($wall['rows']) : 3
            );

            // Ensure animation speed is at least 1000ms
            if ($sanitized_wall['animation_speed'] < 1000) {
                $sanitized_wall['animation_speed'] = 1000;
            }

            // Ensure columns is between 1 and 12
            if ($sanitized_wall['columns'] < 1 || $sanitized_wall['columns'] > 12) {
                $sanitized_wall['columns'] = 4;
            }

            // Ensure rows is between 1 and 12
            if ($sanitized_wall['rows'] < 1 || $sanitized_wall['rows'] > 12) {
                $sanitized_wall['rows'] = 3;
            }

            $sanitized[] = $sanitized_wall;
        }

        return $sanitized;
    }

    /**
     * Sanitize global options settings.
     */
    public function sanitize_global_options_setting($input)
    {
        $sanitized = array();

        // Sanitize default rows and columns
        if (isset($input['default_rows'])) {
            $sanitized['default_rows'] = absint($input['default_rows']);
            // Ensure rows is between 1 and 12
            if ($sanitized['default_rows'] < 1 || $sanitized['default_rows'] > 12) {
                $sanitized['default_rows'] = 3;
            }
        }

        if (isset($input['default_columns'])) {
            $sanitized['default_columns'] = absint($input['default_columns']);
            // Ensure columns is between 1 and 12
            if ($sanitized['default_columns'] < 1 || $sanitized['default_columns'] > 12) {
                $sanitized['default_columns'] = 4;
            }
        }

        return $sanitized;
    }

    /**
     * Enqueue admin styles and scripts.
     */
    public function enqueue_admin_styles_scripts($hook)
    {
        if ('settings_page_wp-animated-live-wall' !== $hook) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_style(
            'wpalw-admin-style',
            WPALW_PLUGIN_URL . 'admin/css/admin-style.css',
            array(),
            WPALW_VERSION
        );

        wp_enqueue_script(
            'wpalw-admin-script',
            WPALW_PLUGIN_URL . 'admin/js/admin-script.js',
            array('jquery', 'jquery-ui-sortable', 'jquery-ui-tabs'),
            WPALW_VERSION,
            true
        );

        wp_localize_script('wpalw-admin-script', 'wpalw_data', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpalw-admin-nonce'),
            'i18n' => array(
                'select_images' => __('Select Images', 'wp-animated-live-wall'),
                'remove' => __('Remove', 'wp-animated-live-wall'),
                'confirm_remove_wall' => __('Are you sure you want to delete this wall?', 'wp-animated-live-wall'),
                'new_wall' => __('New Wall', 'wp-animated-live-wall')
            ),
        ));
    }

    /**
     * Enqueue frontend styles and scripts.
     */
    public function enqueue_frontend_styles_scripts()
    {
        wp_enqueue_style(
            'wpalw-frontend-style',
            WPALW_PLUGIN_URL . 'public/css/frontend-style.css',
            array(),
            WPALW_VERSION
        );

        wp_enqueue_script(
            'wpalw-frontend-script',
            WPALW_PLUGIN_URL . 'public/js/frontend-script.js',
            array('jquery'),
            WPALW_VERSION,
            true
        );

        // We'll pass specific options via the shortcode
    }

    /**
     * Render admin page.
     */
    public function admin_page()
    {
        require_once WPALW_PLUGIN_DIR . 'admin/partials/admin-page.php';
    }
    /**
     * Live wall shortcode callback.
     */
    public function live_wall_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'id' => 'default',
            'columns' => '',
            'rows' => '',
        ), $atts, 'animated_live_wall');

        $wall_id = sanitize_key($atts['id']);

        // Get all walls
        $walls = get_option('wpalw_walls', array());

        // Find the requested wall
        $wall = null;
        foreach ($walls as $w) {
            if ($w['id'] === $wall_id) {
                $wall = $w;
                break;
            }
        }

        // If wall not found, try to use default
        if (!$wall) {
            foreach ($walls as $w) {
                if ($w['id'] === 'default') {
                    $wall = $w;
                    break;
                }
            }

            // If still not found, return error
            if (!$wall) {
                return '<p>' . __('Live wall not found.', 'wp-animated-live-wall') . '</p>';
            }
        }

        // Check if wall has images
        if (empty($wall['images'])) {
            return '<p>' . __('No images found for the live wall.', 'wp-animated-live-wall') . '</p>';
        }

        // Override columns if specified in shortcode
        $columns = !empty($atts['columns']) ? absint($atts['columns']) : $wall['columns'];

        // Override rows if specified in shortcode
        $rows = !empty($atts['rows']) ? absint($atts['rows']) : (isset($wall['rows']) ? $wall['rows'] : 3);        // Set data for JavaScript
        wp_localize_script('wpalw-frontend-script', 'wpalw_data', array(
            'animation_speed' => $wall['animation_speed'],
        ));

        // Get images for display
        $images = $wall['images'];

        ob_start();
        include WPALW_PLUGIN_DIR . 'public/partials/frontend-display.php';
        return ob_get_clean();
    }

    /**
     * AJAX handler to save a wall.
     */
    public function ajax_save_wall()
    {
        check_ajax_referer('wpalw-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $wall_data = isset($_POST['wall_data']) ? $_POST['wall_data'] : null;

        if (!$wall_data || !isset($wall_data['name'])) {
            wp_send_json_error('Invalid wall data');
        }

        $walls = get_option('wpalw_walls', array());        // Generate ID if new wall
        if (empty($wall_data['id'])) {
            $wall_data['id'] = 'wall_' . time() . '_' . mt_rand(100, 999);
            $wall_data['images'] = array();
            $wall_data['animation_speed'] = 5000;
            $wall_data['columns'] = 4;
            $wall_data['rows'] = 3;
            $walls[] = $wall_data;
        } else {
            // Update existing wall
            foreach ($walls as $key => $wall) {
                if ($wall['id'] === $wall_data['id']) {
                    // Update name and other properties
                    $walls[$key]['name'] = sanitize_text_field($wall_data['name']);

                    if (isset($wall_data['animation_speed'])) {
                        $walls[$key]['animation_speed'] = absint($wall_data['animation_speed']);
                    }

                    if (isset($wall_data['columns'])) {
                        $walls[$key]['columns'] = absint($wall_data['columns']);
                    }

                    if (isset($wall_data['rows'])) {
                        $walls[$key]['rows'] = absint($wall_data['rows']);
                    }

                    break;
                }
            }
        }

        update_option('wpalw_walls', $walls);

        wp_send_json_success(array(
            'id' => $wall_data['id'],
            'name' => sanitize_text_field($wall_data['name'])
        ));
    }

    /**
     * AJAX handler to remove a wall.
     */
    public function ajax_remove_wall()
    {
        check_ajax_referer('wpalw-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $wall_id = isset($_POST['wall_id']) ? sanitize_key($_POST['wall_id']) : '';

        if (empty($wall_id)) {
            wp_send_json_error('Invalid wall ID');
        }

        // Can't remove default wall
        if ($wall_id === 'default') {
            wp_send_json_error('Cannot remove default wall');
        }

        $walls = get_option('wpalw_walls', array());
        $updated_walls = array();

        foreach ($walls as $wall) {
            if ($wall['id'] !== $wall_id) {
                $updated_walls[] = $wall;
            }
        }

        update_option('wpalw_walls', $updated_walls);

        wp_send_json_success();
    }

    /**
     * AJAX handler to save an image to a specific wall.
     */
    public function ajax_save_image()
    {
        check_ajax_referer('wpalw-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $image_id = isset($_POST['image_id']) ? absint($_POST['image_id']) : 0;
        $wall_id = isset($_POST['wall_id']) ? sanitize_key($_POST['wall_id']) : '';

        if (!$image_id || empty($wall_id)) {
            wp_send_json_error('Invalid data');
        }

        $walls = get_option('wpalw_walls', array());

        foreach ($walls as $key => $wall) {
            if ($wall['id'] === $wall_id) {
                if (!in_array($image_id, $wall['images'])) {
                    $walls[$key]['images'][] = $image_id;
                }
                break;
            }
        }

        update_option('wpalw_walls', $walls);

        $image_data = wp_get_attachment_image_src($image_id, 'medium');

        wp_send_json_success(array(
            'id' => $image_id,
            'url' => $image_data[0],
        ));
    }

    /**
     * AJAX handler to remove an image from a specific wall.
     */
    public function ajax_remove_image()
    {
        check_ajax_referer('wpalw-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $image_id = isset($_POST['image_id']) ? absint($_POST['image_id']) : 0;
        $wall_id = isset($_POST['wall_id']) ? sanitize_key($_POST['wall_id']) : '';

        if (!$image_id || empty($wall_id)) {
            wp_send_json_error('Invalid data');
        }

        $walls = get_option('wpalw_walls', array());

        foreach ($walls as $key => $wall) {
            if ($wall['id'] === $wall_id) {
                $index = array_search($image_id, $wall['images']);
                if ($index !== false) {
                    unset($walls[$key]['images'][$index]);
                    $walls[$key]['images'] = array_values($walls[$key]['images']); // Reindex array
                }
                break;
            }
        }

        update_option('wpalw_walls', $walls);

        wp_send_json_success();
    }

    /**
     * AJAX handler to update image order in a wall.
     */
    public function ajax_update_image_order()
    {
        check_ajax_referer('wpalw-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $wall_id = isset($_POST['wall_id']) ? sanitize_key($_POST['wall_id']) : '';
        $image_ids = isset($_POST['image_ids']) ? array_map('absint', $_POST['image_ids']) : array();

        if (empty($wall_id) || empty($image_ids)) {
            wp_send_json_error('Invalid data');
        }

        $walls = get_option('wpalw_walls', array());

        foreach ($walls as $key => $wall) {
            if ($wall['id'] === $wall_id) {
                $walls[$key]['images'] = $image_ids;
                break;
            }
        }

        update_option('wpalw_walls', $walls);

        wp_send_json_success();
    }

    /**
     * Shortcode for square and responsive tiles.
     */
    public function shortcode($atts)
    {
        // Get global settings
        $global_settings = get_option('wpalw_global_options', array());

        // Default values from global settings or fallback if not set
        $default_rows = isset($global_settings['default_rows']) ? $global_settings['default_rows'] : 3;
        $default_columns = isset($global_settings['default_columns']) ? $global_settings['default_columns'] : 4;

        $attributes = shortcode_atts(array(
            'rows' => $default_rows,
            'columns' => $default_columns,
            'images' => ''
        ), $atts);

        if (empty($attributes['images'])) {
            return '';
        }

        $image_ids = array_map('trim', explode(',', $attributes['images']));

        // Build grid with CSS classes for responsive square tiles
        $output = '<div class="wp-animated-live-wall" ';
        $output .= 'data-rows="' . esc_attr($attributes['rows']) . '" ';
        $output .= 'data-columns="' . esc_attr($attributes['columns']) . '" ';
        $output .= 'style="grid-template-columns: repeat(' . esc_attr($attributes['columns']) . ', 1fr);">';

        foreach ($image_ids as $image_id) {
            // Get responsive square image data
            $image_data = $this->get_square_image($image_id);
            if ($image_data) {
                $output .= '<div class="wall-tile">';
                $output .= '<img src="' . esc_url($image_data['src']) . '" ';
                $output .= 'srcset="' . esc_attr($image_data['srcset']) . '" ';
                $output .= 'sizes="' . esc_attr($image_data['sizes']) . '" ';
                $output .= 'alt="">';
                $output .= '</div>';
            }
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Get square image data with responsive srcset.
     * 
     * @param int $image_id The attachment ID
     * @return array|false Image data array or false if not found
     */
    private function get_square_image($image_id)
    {
        // Check if image exists
        if (!wp_attachment_is_image($image_id)) {
            return false;
        }

        // Get various square image sizes for responsive srcset
        $small = wp_get_attachment_image_src($image_id, 'wpalw-square-small');
        $medium = wp_get_attachment_image_src($image_id, 'wpalw-square-medium');
        $large = wp_get_attachment_image_src($image_id, 'wpalw-square-large');
        $full = wp_get_attachment_image_src($image_id, 'full');

        if (!$medium) {
            return false;
        }

        // Build srcset for responsive images
        $srcset = array();
        if ($small) $srcset[] = $small[0] . ' 300w';
        if ($medium) $srcset[] = $medium[0] . ' 600w';
        if ($large) $srcset[] = $large[0] . ' 900w';
        if ($full) $srcset[] = $full[0] . ' ' . $full[1] . 'w';

        return array(
            'src' => $medium[0],
            'srcset' => implode(', ', $srcset),
            'sizes' => '(max-width: 480px) calc(50vw - 8px), (max-width: 768px) calc(33vw - 8px), calc(25vw - 8px)'
        );
    }
}
