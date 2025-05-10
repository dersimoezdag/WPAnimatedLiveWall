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
            $all_effect_keys = array_keys($this->get_available_transition_effects());
            $default_wall = array(
                'id' => 'default',
                'name' => __('Default Wall', 'wp-animated-live-wall'),
                'images' => array(),
                'animation_speed' => 5000,
                'columns' => 4,
                'rows' => 3,
                'tiles_at_once' => 1,
                'selected_effects' => !empty($all_effect_keys) ? $all_effect_keys : ['crossfade'], // Ensure all effects are selected for the default wall                'transition' => 400,
                'gap' => 4,
                'keyvisual_mode' => false,
                'keyvisual_title' => '',
                'keyvisual_subtitle' => '',
                'keyvisual_bgcolor' => 'rgba(44, 62, 80, 0.8)'
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
            __('Animated Live Wall', 'wp-animated-live-wall'),
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
        // Register wall settings
        register_setting(
            'wpalw_settings',
            'wpalw_walls',
            array($this, 'sanitize_walls_setting')
        );

        // Register global settings with a different group to avoid conflicts
        register_setting(
            'wpalw_global_settings', // Use a different group for globals
            'wpalw_global_options',
            array($this, 'sanitize_global_options_setting')
        );
    }

    /**
     * Get available transition effects.
     *
     * @return array Available transition effects with key and name.
     */
    public function get_available_transition_effects()
    {
        return array(
            'crossfade' => __('Improved Crossfade', 'wp-animated-live-wall'),
            'zoomfade' => __('Zoom Fade', 'wp-animated-live-wall'),
            'slideup' => __('Slide Up', 'wp-animated-live-wall'),
            'slidedown' => __('Slide Down', 'wp-animated-live-wall'),
            'slideleft' => __('Slide Left', 'wp-animated-live-wall'),
            'slideright' => __('Slide Right', 'wp-animated-live-wall'),
            'rotate' => __('Rotate', 'wp-animated-live-wall'),
            'blurfade' => __('Blur Fade', 'wp-animated-live-wall'),
            'flip' => __('3D Flip', 'wp-animated-live-wall'),
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

        $sanitized_walls_array = array();
        $available_effects_keys = array_keys($this->get_available_transition_effects());

        foreach ($input as $wall_to_sanitize) {
            $sanitized_wall_data = array(
                'id' => isset($wall_to_sanitize['id']) ? sanitize_key($wall_to_sanitize['id']) : 'wall_' . time() . '_' . mt_rand(100, 999),
                'name' => isset($wall_to_sanitize['name']) ? sanitize_text_field($wall_to_sanitize['name']) : __('New Wall', 'wp-animated-live-wall'),
                'images' => isset($wall_to_sanitize['images']) && is_array($wall_to_sanitize['images']) ? array_map('absint', $wall_to_sanitize['images']) : array(),
                'animation_speed' => isset($wall_to_sanitize['animation_speed']) ? absint($wall_to_sanitize['animation_speed']) : 5000,
                'transition' => isset($wall_to_sanitize['transition']) ? absint($wall_to_sanitize['transition']) : 400,
                'gap' => isset($wall_to_sanitize['gap']) ? intval($wall_to_sanitize['gap']) : 4,
                'columns' => isset($wall_to_sanitize['columns']) ? absint($wall_to_sanitize['columns']) : 4,
                'rows' => isset($wall_to_sanitize['rows']) ? absint($wall_to_sanitize['rows']) : 3,
                'selected_effects' => array(),
                'tiles_at_once' => isset($wall_to_sanitize['tiles_at_once']) ? absint($wall_to_sanitize['tiles_at_once']) : 1,                // keyvisual fields
                'keyvisual_mode' => isset($wall_to_sanitize['keyvisual_mode']) ? (bool)$wall_to_sanitize['keyvisual_mode'] : false,
                'keyvisual_title' => isset($wall_to_sanitize['keyvisual_title']) ? sanitize_text_field($wall_to_sanitize['keyvisual_title']) : '',
                'keyvisual_subtitle' => isset($wall_to_sanitize['keyvisual_subtitle']) ? sanitize_text_field($wall_to_sanitize['keyvisual_subtitle']) : '',
                'keyvisual_bgcolor' => isset($wall_to_sanitize['keyvisual_bgcolor']) ? sanitize_text_field($wall_to_sanitize['keyvisual_bgcolor']) : 'rgba(44, 62, 80, 0.8)',
            );

            if (isset($wall_to_sanitize['selected_effects']) && is_array($wall_to_sanitize['selected_effects'])) {
                $temp_effects = [];
                foreach ($wall_to_sanitize['selected_effects'] as $effect_key) {
                    if (in_array(sanitize_key($effect_key), $available_effects_keys, true)) {
                        $temp_effects[] = sanitize_key($effect_key);
                    }
                }
                $sanitized_wall_data['selected_effects'] = $temp_effects;
            }

            if (empty($sanitized_wall_data['selected_effects'])) {
                $sanitized_wall_data['selected_effects'] = ['crossfade'];
            }

            if ($sanitized_wall_data['animation_speed'] < 1000) {
                $sanitized_wall_data['animation_speed'] = 1000;
            }

            if ($sanitized_wall_data['transition'] < 100) {
                $sanitized_wall_data['transition'] = 100;
            } elseif ($sanitized_wall_data['transition'] > 2000) {
                $sanitized_wall_data['transition'] = 2000;
            }

            if ($sanitized_wall_data['gap'] < 0) {
                $sanitized_wall_data['gap'] = 0;
            } elseif ($sanitized_wall_data['gap'] > 20) {
                $sanitized_wall_data['gap'] = 20;
            }

            if ($sanitized_wall_data['columns'] < 1) $sanitized_wall_data['columns'] = 1;
            if ($sanitized_wall_data['columns'] > 12) $sanitized_wall_data['columns'] = 12;

            if ($sanitized_wall_data['rows'] < 1) $sanitized_wall_data['rows'] = 1;
            if ($sanitized_wall_data['rows'] > 12) $sanitized_wall_data['rows'] = 12;

            if ($sanitized_wall_data['tiles_at_once'] < 1) {
                $sanitized_wall_data['tiles_at_once'] = 1;
            }
            $max_tiles = $sanitized_wall_data['columns'] * $sanitized_wall_data['rows'];
            if ($max_tiles > 0 && $sanitized_wall_data['tiles_at_once'] > $max_tiles) {
                $sanitized_wall_data['tiles_at_once'] = $max_tiles;
            } elseif ($sanitized_wall_data['tiles_at_once'] > 20) {
                $sanitized_wall_data['tiles_at_once'] = 20;
            }

            $sanitized_walls_array[] = $sanitized_wall_data;
        }

        return $sanitized_walls_array;
    }

    /**
     * Sanitize global options settings.
     */
    public function sanitize_global_options_setting($input)
    {
        // Get the existing settings to preserve data
        $existing_settings = get_option('wpalw_global_options', array());
        $sanitized = $existing_settings; // Start with existing settings

        // Only update what was submitted in the form
        if (isset($input['default_rows'])) {
            $sanitized['default_rows'] = absint($input['default_rows']);
            if ($sanitized['default_rows'] < 1 || $sanitized['default_rows'] > 12) {
                $sanitized['default_rows'] = 3;
            }
        }

        if (isset($input['default_columns'])) {
            $sanitized['default_columns'] = absint($input['default_columns']);
            if ($sanitized['default_columns'] < 1 || $sanitized['default_columns'] > 12) {
                $sanitized['default_columns'] = 4;
            }
        }

        if (isset($input['default_animation_speed'])) {
            $sanitized['default_animation_speed'] = absint($input['default_animation_speed']);
            if ($sanitized['default_animation_speed'] < 1000) {
                $sanitized['default_animation_speed'] = 5000;
            }
        }

        if (isset($input['default_gap'])) {
            $sanitized['default_gap'] = absint($input['default_gap']);
            if ($sanitized['default_gap'] > 20) {
                $sanitized['default_gap'] = 20;
            }
        }

        if (isset($input['default_tiles_at_once'])) {
            $sanitized['default_tiles_at_once'] = absint($input['default_tiles_at_once']);
            if ($sanitized['default_tiles_at_once'] < 1) {
                $sanitized['default_tiles_at_once'] = 1;
            } else if ($sanitized['default_tiles_at_once'] > 5) {
                $sanitized['default_tiles_at_once'] = 5;
            }
        }

        return $sanitized;
    }

    /**
     * Enqueue admin styles and scripts.
     */    public function enqueue_admin_styles_scripts($hook)
    {
        if ('settings_page_wp-animated-live-wall' !== $hook) {
            return;
        }

        wp_enqueue_media();

        // Load WordPress color picker
        wp_enqueue_style('wp-color-picker');

        wp_enqueue_style(
            'wpalw-admin-style',
            WPALW_PLUGIN_URL . 'admin/css/admin-style.css',
            array(),
            WPALW_VERSION
        );
        wp_enqueue_script(
            'wpalw-admin-script',
            WPALW_PLUGIN_URL . 'admin/js/admin-script.js',
            array('jquery', 'jquery-ui-sortable', 'jquery-ui-tabs', 'wp-color-picker'),
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
        $available_effects = $this->get_available_transition_effects(); // Pass available effects to the admin page scope
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
            'animation_speed' => '', // Animation speed in milliseconds
            'transition' => '',      // Transition time in milliseconds
            'gap' => '',             // Gap between images in pixels
            'effects' => '',         // Comma-separated list of effects
            'tiles_at_once' => '',   // Number of tiles to change simultaneously
            'keyvisual_mode' => '',  // Enable keyvisual overlay (true/false)
            'keyvisual_title' => '', // Title for keyvisual
            'keyvisual_subtitle' => '', // Subtitle for keyvisual
            'keyvisual_bgcolor' => '', // Background color for keyvisual text
        ), $atts, 'animated_live_wall');

        $wall_id = sanitize_key($atts['id']);

        // Get all walls
        $walls = get_option('wpalw_walls', array());
        $global_settings = get_option('wpalw_global_options', array());

        // Find the requested wall
        $current_wall_settings = null;
        foreach ($walls as $w) {
            if ($w['id'] === $wall_id) {
                $current_wall_settings = $w;
                break;
            }
        }

        // If wall not found, try to use default wall if it exists
        if (!$current_wall_settings) {
            foreach ($walls as $w) {
                if ($w['id'] === 'default') {
                    $current_wall_settings = $w;
                    break;
                }
            }
        }

        // If still not found, or no images, return error or nothing
        if (!$current_wall_settings) {
            return '<p>' . __('Live wall not found.', 'wp-animated-live-wall') . '</p>';
        }
        if (empty($current_wall_settings['images'])) {
            return '<p>' . __('No images found for this live wall.', 'wp-animated-live-wall') . '</p>';
        }

        // Prepare data for the frontend display
        // Priority: Shortcode atts > Wall settings > Global settings > Hardcoded defaults
        $default_columns = isset($global_settings['default_columns']) ? absint($global_settings['default_columns']) : 4;
        $default_rows = isset($global_settings['default_rows']) ? absint($global_settings['default_rows']) : 3;
        $default_animation_speed = isset($global_settings['default_animation_speed']) ? absint($global_settings['default_animation_speed']) : 5000;
        $default_transition = isset($global_settings['default_transition']) ? absint($global_settings['default_transition']) : 400;
        $default_gap = isset($global_settings['default_gap']) ? absint($global_settings['default_gap']) : 4;
        $default_tiles_at_once = isset($global_settings['default_tiles_at_once']) ? absint($global_settings['default_tiles_at_once']) : 1;

        // Resolve settings
        $columns = !empty($atts['columns']) ? absint($atts['columns']) : (isset($current_wall_settings['columns']) ? absint($current_wall_settings['columns']) : $default_columns);
        $rows = !empty($atts['rows']) ? absint($atts['rows']) : (isset($current_wall_settings['rows']) ? absint($current_wall_settings['rows']) : $default_rows);
        $animation_speed = isset($current_wall_settings['animation_speed']) ? absint($current_wall_settings['animation_speed']) : $default_animation_speed;
        $transition = isset($current_wall_settings['transition']) ? absint($current_wall_settings['transition']) : $default_transition;
        $tiles_at_once = !empty($atts['tiles_at_once']) ? absint($atts['tiles_at_once']) : (isset($current_wall_settings['tiles_at_once']) ? absint($current_wall_settings['tiles_at_once']) : $default_tiles_at_once);
        $keyvisual_mode = isset($current_wall_settings['keyvisual_mode']) ? (bool)$current_wall_settings['keyvisual_mode'] : false;
        $keyvisual_title = isset($current_wall_settings['keyvisual_title']) ? $current_wall_settings['keyvisual_title'] : '';
        $keyvisual_subtitle = isset($current_wall_settings['keyvisual_subtitle']) ? $current_wall_settings['keyvisual_subtitle'] : '';
        $keyvisual_bgcolor = isset($current_wall_settings['keyvisual_bgcolor']) ? $current_wall_settings['keyvisual_bgcolor'] : 'rgba(44, 62, 80, 0.8)';

        if (isset($current_wall_settings['gap']) && $current_wall_settings['gap'] !== '') {
            $gap = absint($current_wall_settings['gap']);
        } else {
            $gap = $default_gap;
        }
        if ($gap < 0) $gap = 0;
        if ($gap > 20) $gap = 20;
        $images = $current_wall_settings['images'];

        // Verarbeite die Effekte aus dem Shortcode, falls vorhanden
        $selected_effects = isset($current_wall_settings['selected_effects']) && !empty($current_wall_settings['selected_effects']) ? $current_wall_settings['selected_effects'] : ['crossfade'];

        // Wenn der effects-Parameter im Shortcode gesetzt ist, überschreibt er die Wall-Einstellungen
        if (!empty($atts['effects'])) {
            $shortcode_effects = array_map('trim', explode(',', $atts['effects']));
            $available_effects_keys = array_keys($this->get_available_transition_effects());

            // Filtere ungültige Effekte heraus
            $valid_effects = array_filter($shortcode_effects, function ($effect) use ($available_effects_keys) {
                return in_array($effect, $available_effects_keys);
            });

            if (!empty($valid_effects)) {
                $selected_effects = $valid_effects;
            }
        }

        // Prüfen, ob das keyvisual_mode Attribut im Shortcode gesetzt ist
        if (isset($atts['keyvisual_mode']) && $atts['keyvisual_mode'] !== '') {
            // Wenn keyvisual_mode im Shortcode gesetzt ist, hat es Priorität
            if ($atts['keyvisual_mode'] === 'true' || $atts['keyvisual_mode'] === '1' || $atts['keyvisual_mode'] === true) {
                $keyvisual_mode = true;
                $keyvisual_title = !empty($atts['keyvisual_title']) ?
                    $atts['keyvisual_title'] : $keyvisual_title;
                $keyvisual_subtitle = !empty($atts['keyvisual_subtitle']) ?
                    $atts['keyvisual_subtitle'] : $keyvisual_subtitle;
            } else if ($atts['keyvisual_mode'] === 'false' || $atts['keyvisual_mode'] === '0' || $atts['keyvisual_mode'] === false) {
                $keyvisual_mode = false;
            }
        }
        $wall_data_for_template = array(
            'wall_id' => $wall_id,
            'columns' => $columns,
            'rows' => $rows,
            'animation_speed' => $animation_speed,
            'transition' => $transition,
            'gap' => $gap,
            'images' => $images,
            'tiles_at_once' => $tiles_at_once,
            'selected_effects' => $selected_effects, // Übergebe das Array direkt, nicht als JSON
            'keyvisual_mode' => $keyvisual_mode,
            'keyvisual_title' => $keyvisual_title,
            'keyvisual_subtitle' => $keyvisual_subtitle,
            'keyvisual_bgcolor' => !empty($atts['keyvisual_bgcolor']) ? $atts['keyvisual_bgcolor'] : $keyvisual_bgcolor,
        );

        ob_start();
        extract($wall_data_for_template);
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
            wp_send_json_error(__('Permission denied', 'wp-animated-live-wall'));
        }

        $wall_data_from_post = isset($_POST['wall_data']) ? $_POST['wall_data'] : null;

        if (!$wall_data_from_post || !is_array($wall_data_from_post)) {
            wp_send_json_error(__('Invalid wall data', 'wp-animated-live-wall'));
            return;
        }

        $is_settings_update = isset($wall_data_from_post['id']) && !empty($wall_data_from_post['id']);
        if (!$is_settings_update && !isset($wall_data_from_post['name'])) {
            wp_send_json_error(__('Wall name is required for new walls', 'wp-animated-live-wall'));
            return;
        }

        $walls = get_option('wpalw_walls', array());
        $available_effects_map = $this->get_available_transition_effects();
        $available_effects_keys = array_keys($available_effects_map);

        $wall_id_to_save = isset($wall_data_from_post['id']) ? sanitize_key($wall_data_from_post['id']) : null;
        $is_new_wall = empty($wall_id_to_save);

        $current_wall_data_for_processing = array();

        if ($is_new_wall) { // New wall
            $current_wall_data_for_processing = array(
                'id' => 'wall_' . time() . '_' . mt_rand(100, 999),
                'name' => __('New Wall', 'wp-animated-live-wall'), // Default name, will be overridden
                'images' => array(),
                'animation_speed' => 5000,
                'transition' => 400,
                'gap' => 4,
                'columns' => 4,
                'rows' => 3,
                'selected_effects' => $available_effects_keys, // Default to all available for new wall
                'keyvisual_mode' => false,
                'keyvisual_title' => '',
                'keyvisual_subtitle' => ''
            );
        } else { // Existing wall
            $wall_found_for_update = false;
            foreach ($walls as $key => $wall) {
                if ($wall['id'] === $wall_id_to_save) {
                    $current_wall_data_for_processing = $wall; // Start with existing data
                    if (!isset($current_wall_data_for_processing['selected_effects'])) { // For backward compatibility
                        $current_wall_data_for_processing['selected_effects'] = $available_effects_keys;
                    }
                    $wall_found_for_update = true;
                    break;
                }
            }
            if (!$wall_found_for_update) {
                wp_send_json_error(__('Wall not found for update.', 'wp-animated-live-wall'));
                return;
            }
        }

        // Update fields from POST data
        if (isset($wall_data_from_post['name'])) {
            $current_wall_data_for_processing['name'] = sanitize_text_field($wall_data_from_post['name']);
        }
        if (isset($wall_data_from_post['animation_speed'])) {
            $current_wall_data_for_processing['animation_speed'] = absint($wall_data_from_post['animation_speed']);
        }
        if (isset($wall_data_from_post['transition'])) {
            $current_wall_data_for_processing['transition'] = absint($wall_data_from_post['transition']);
        }
        if (array_key_exists('gap', $wall_data_from_post)) {
            $posted_gap = $wall_data_from_post['gap'];

            if ($posted_gap === "0" || $posted_gap === 0) {
                $current_wall_data_for_processing['gap'] = 0;
            } elseif ($posted_gap === null || $posted_gap === '') {
                $current_wall_data_for_processing['gap'] = 4;
            } elseif (is_numeric($posted_gap)) {
                $gap_val = intval($posted_gap);
                if ($gap_val <= 0) {
                    $current_wall_data_for_processing['gap'] = 0;
                } elseif ($gap_val > 20) {
                    $current_wall_data_for_processing['gap'] = 20;
                } else {
                    $current_wall_data_for_processing['gap'] = $gap_val;
                }
            } else {
                $current_wall_data_for_processing['gap'] = 4;
            }
        } elseif ($is_new_wall && !isset($current_wall_data_for_processing['gap'])) {
            $current_wall_data_for_processing['gap'] = 4;
        }

        if (isset($wall_data_from_post['columns'])) {
            $current_wall_data_for_processing['columns'] = absint($wall_data_from_post['columns']);
        }
        if (isset($wall_data_from_post['rows'])) {
            $current_wall_data_for_processing['rows'] = absint($wall_data_from_post['rows']);
        }

        if (isset($wall_data_from_post['tiles_at_once'])) {
            $current_wall_data_for_processing['tiles_at_once'] = absint($wall_data_from_post['tiles_at_once']);
        }

        if (isset($wall_data_from_post['selected_effects'])) {
            if (is_array($wall_data_from_post['selected_effects'])) {
                $sanitized_effects = [];
                foreach ($wall_data_from_post['selected_effects'] as $effect_key) {
                    if (in_array(sanitize_key($effect_key), $available_effects_keys, true)) {
                        $sanitized_effects[] = sanitize_key($effect_key);
                    }
                }
                $current_wall_data_for_processing['selected_effects'] = $sanitized_effects;

                if (empty($sanitized_effects)) {
                    $current_wall_data_for_processing['selected_effects'] = ['crossfade'];
                }
            } else {
                $effect_key = sanitize_key($wall_data_from_post['selected_effects']);
                if (in_array($effect_key, $available_effects_keys, true)) {
                    $current_wall_data_for_processing['selected_effects'] = [$effect_key];
                } else {
                    $current_wall_data_for_processing['selected_effects'] = ['crossfade'];
                }
            }
        } elseif ($is_new_wall) {
            $current_wall_data_for_processing['selected_effects'] = $available_effects_keys;
        }

        // Handle keyvisual settings
        if (isset($wall_data_from_post['keyvisual_mode'])) {
            $keyvisual_mode_value = filter_var($wall_data_from_post['keyvisual_mode'], FILTER_VALIDATE_BOOLEAN);
            $current_wall_data_for_processing['keyvisual_mode'] = $keyvisual_mode_value;

            if ($keyvisual_mode_value) {
                $current_wall_data_for_processing['keyvisual_title'] = isset($wall_data_from_post['keyvisual_title']) ? sanitize_text_field($wall_data_from_post['keyvisual_title']) : '';
                $current_wall_data_for_processing['keyvisual_subtitle'] = isset($wall_data_from_post['keyvisual_subtitle']) ? sanitize_text_field($wall_data_from_post['keyvisual_subtitle']) : '';

                // Save the background color setting
                $current_wall_data_for_processing['keyvisual_bgcolor'] = isset($wall_data_from_post['keyvisual_bgcolor']) ? sanitize_text_field($wall_data_from_post['keyvisual_bgcolor']) : 'rgba(44, 62, 80, 0.8)';
            }
        } else {
            // If keyvisual_mode not present in POST data, set it to false
            $current_wall_data_for_processing['keyvisual_mode'] = false;
            $current_wall_data_for_processing['keyvisual_title'] = '';
            $current_wall_data_for_processing['keyvisual_subtitle'] = '';
            $current_wall_data_for_processing['keyvisual_bgcolor'] = 'rgba(44, 62, 80, 0.8)';
        }

        $temp_sanitized_array = $this->sanitize_walls_setting([$current_wall_data_for_processing]);
        $final_wall_data = $temp_sanitized_array[0];

        if ($is_new_wall) {
            $walls[] = $final_wall_data;
        } else {
            $updated_walls_array = [];
            foreach ($walls as $wall_item) {
                if ($wall_item['id'] === $wall_id_to_save) {
                    $updated_walls_array[] = $final_wall_data;
                } else {
                    $updated_walls_array[] = $wall_item;
                }
            }
            $walls = $updated_walls_array;
        }

        update_option('wpalw_walls', $walls);

        wp_send_json_success(array(
            'id' => $final_wall_data['id'],
            'name' => $final_wall_data['name'],
            'message' => __('Wall settings saved successfully.', 'wp-animated-live-wall')
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
                    $walls[$key]['images'] = array_values($walls[$key]['images']);
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
     * Shortcode
     */
    public function shortcode($atts)
    {
        $global_settings = get_option('wpalw_global_options', array());

        $default_rows = isset($global_settings['default_rows']) ? $global_settings['default_rows'] : 3;
        $default_columns = isset($global_settings['default_columns']) ? $global_settings['default_columns'] : 4;
        $default_animation_speed = isset($global_settings['default_animation_speed']) ? $global_settings['default_animation_speed'] : 5000;
        $default_gap = isset($global_settings['default_gap']) ? $global_settings['default_gap'] : 4;
        $default_transition = isset($global_settings['default_transition']) ? $global_settings['default_transition'] : 400;
        $attributes = shortcode_atts(array(
            'rows' => $default_rows,
            'columns' => $default_columns,
            'animation_speed' => $default_animation_speed,
            'gap' => $default_gap,
            'transition' => $default_transition,
            'images' => '',
            'effects' => '' // Optional: Komma-getrennte Liste von Effekten
        ), $atts);

        if (empty($attributes['images'])) {
            return '';
        }

        $image_ids = array_map('trim', explode(',', $attributes['images']));

        $total_cells = (int)$attributes['rows'] * (int)$attributes['columns'];

        $visible_image_ids = array_slice($image_ids, 0, $total_cells);

        $hidden_image_ids = array_slice($image_ids, $total_cells);

        $output = '<div class="wp-animated-live-wall" ';
        $output .= 'data-rows="' . esc_attr($attributes['rows']) . '" ';
        $output .= 'data-columns="' . esc_attr($attributes['columns']) . '" ';
        $output .= 'data-animation-speed="' . esc_attr($attributes['animation_speed']) . '" ';
        $output .= 'data-transition="' . esc_attr($attributes['transition']) . '" ';
        $output .= 'data-gap="' . esc_attr($attributes['gap']) . '" ';
        $output .= 'data-tiles-at-once="' . esc_attr($attributes['tiles_at_once']) . '" ';

        // Verarbeite Effekte, falls angegeben
        $effects = array('crossfade'); // Standard-Effekt
        if (!empty($attributes['effects'])) {
            $effects = array_map('trim', explode(',', $attributes['effects']));
            $available_effects = array_keys($this->get_available_transition_effects());
            $effects = array_filter($effects, function ($effect) use ($available_effects) {
                return in_array($effect, $available_effects);
            });

            if (empty($effects)) {
                $effects = array('crossfade');
            }
        }

        $output .= 'data-effects=\'' . json_encode($effects) . '\' ';
        $output .= 'style="grid-template-columns: repeat(' . esc_attr($attributes['columns']) . ', 1fr); grid-gap: ' . esc_attr($attributes['gap']) . 'px;">';

        foreach ($visible_image_ids as $index => $image_id) {
            $image_data = $this->get_square_image($image_id);
            if ($image_data) {
                $output .= '<div class="wall-tile">';
                $output .= '<img src="' . esc_url($image_data['src']) . '" ';
                $output .= 'srcset="' . esc_attr($image_data['srcset']) . '" ';
                $output .= 'sizes="' . esc_attr($image_data['sizes']) . '" ';
                $output .= 'data-id="' . esc_attr($image_id) . '" ';
                $output .= 'alt="">';
                $output .= '</div>';
            }
        }

        if (!empty($hidden_image_ids)) {
            $output .= '<div class="wpalw-hidden-images" style="display:none;">';
            foreach ($hidden_image_ids as $image_id) {
                $image_data = $this->get_square_image($image_id);
                if ($image_data) {
                    $output .= '<div data-id="' . esc_attr($image_id) . '">';
                    $output .= '<img src="' . esc_url($image_data['src']) . '" ';
                    $output .= 'srcset="' . esc_attr($image_data['srcset']) . '" ';
                    $output .= 'sizes="' . esc_attr($image_data['sizes']) . '" ';
                    $output .= 'alt="">';
                    $output .= '</div>';
                }
            }
            $output .= '</div>';
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
        if (!wp_attachment_is_image($image_id)) {
            return false;
        }

        $small = wp_get_attachment_image_src($image_id, 'wpalw-square-small');
        $medium = wp_get_attachment_image_src($image_id, 'wpalw-square-medium');
        $large = wp_get_attachment_image_src($image_id, 'wpalw-square-large');
        $full = wp_get_attachment_image_src($image_id, 'full');

        if (!$medium) {
            return false;
        }

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
