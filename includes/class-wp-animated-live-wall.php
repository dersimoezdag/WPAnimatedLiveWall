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
        add_action('wp_ajax_wpalw_save_image', array($this, 'ajax_save_image'));
        add_action('wp_ajax_wpalw_remove_image', array($this, 'ajax_remove_image'));
    }

    /**
     * Plugin activation.
     */
    public function activate()
    {
        // Create custom database tables if needed
        // For now, we'll store settings in wp_options
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
        add_menu_page(
            __('Animated Live Wall', 'wp-animated-live-wall'),
            __('Live Wall', 'wp-animated-live-wall'),
            'manage_options',
            'wp-animated-live-wall',
            array($this, 'admin_page'),
            'dashicons-format-gallery',
            30
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings()
    {
        register_setting(
            'wpalw_settings',
            'wpalw_images',
            array($this, 'sanitize_images_setting')
        );

        register_setting(
            'wpalw_settings',
            'wpalw_options',
            array($this, 'sanitize_options_setting')
        );
    }

    /**
     * Sanitize images settings.
     */
    public function sanitize_images_setting($input)
    {
        return is_array($input) ? $input : array();
    }

    /**
     * Sanitize options settings.
     */
    public function sanitize_options_setting($input)
    {
        $sanitized = array();

        if (isset($input['animation_speed'])) {
            $sanitized['animation_speed'] = absint($input['animation_speed']);
            if ($sanitized['animation_speed'] < 1000) {
                $sanitized['animation_speed'] = 1000;
            }
        } else {
            $sanitized['animation_speed'] = 5000; // Default: 5 seconds
        }

        if (isset($input['columns'])) {
            $sanitized['columns'] = absint($input['columns']);
            if ($sanitized['columns'] < 1 || $sanitized['columns'] > 12) {
                $sanitized['columns'] = 4;
            }
        } else {
            $sanitized['columns'] = 4; // Default: 4 columns
        }

        return $sanitized;
    }

    /**
     * Enqueue admin styles and scripts.
     */
    public function enqueue_admin_styles_scripts($hook)
    {
        if ('toplevel_page_wp-animated-live-wall' !== $hook) {
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
            array('jquery', 'jquery-ui-sortable'),
            WPALW_VERSION,
            true
        );

        wp_localize_script('wpalw-admin-script', 'wpalw_data', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpalw-admin-nonce'),
            'i18n' => array(
                'select_images' => __('Select Images', 'wp-animated-live-wall'),
                'remove' => __('Remove', 'wp-animated-live-wall'),
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

        $options = get_option('wpalw_options', array(
            'animation_speed' => 5000,
            'columns' => 4,
        ));

        wp_localize_script('wpalw-frontend-script', 'wpalw_data', array(
            'animation_speed' => $options['animation_speed'],
        ));
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
            'columns' => '',
        ), $atts, 'animated_live_wall');

        $options = get_option('wpalw_options', array(
            'columns' => 4,
        ));

        $columns = !empty($atts['columns']) ? absint($atts['columns']) : $options['columns'];

        $images = get_option('wpalw_images', array());

        if (empty($images)) {
            return '<p>' . __('No images found for the live wall.', 'wp-animated-live-wall') . '</p>';
        }

        ob_start();
        include WPALW_PLUGIN_DIR . 'public/partials/frontend-display.php';
        return ob_get_clean();
    }

    /**
     * AJAX handler to save images.
     */
    public function ajax_save_image()
    {
        check_ajax_referer('wpalw-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $image_id = isset($_POST['image_id']) ? absint($_POST['image_id']) : 0;

        if (!$image_id) {
            wp_send_json_error('Invalid image ID');
        }

        $images = get_option('wpalw_images', array());

        if (!in_array($image_id, $images)) {
            $images[] = $image_id;
            update_option('wpalw_images', $images);
        }

        $image_data = wp_get_attachment_image_src($image_id, 'medium');

        wp_send_json_success(array(
            'id' => $image_id,
            'url' => $image_data[0],
        ));
    }

    /**
     * AJAX handler to remove images.
     */
    public function ajax_remove_image()
    {
        check_ajax_referer('wpalw-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $image_id = isset($_POST['image_id']) ? absint($_POST['image_id']) : 0;

        if (!$image_id) {
            wp_send_json_error('Invalid image ID');
        }

        $images = get_option('wpalw_images', array());
        $index = array_search($image_id, $images);

        if ($index !== false) {
            unset($images[$index]);
            $images = array_values($images); // Reindex array
            update_option('wpalw_images', $images);
        }

        wp_send_json_success();
    }
}
