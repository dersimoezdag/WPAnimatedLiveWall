<?php

/**
 * Plugin Name: WP Animated Live Wall
 * Plugin URI: https://github.com/dersimoezdag/WPAnimatedLiveWall
 * Description: A WordPress plugin that displays an animated live wall with photo tiles that randomly switch after page load.
 * Version: 1.2.0
 * Author: Dersim Özdag
 * Author URI: https://github.com/dersimoezdag
 * Text Domain: wp-animated-live-wall
 * License: GPL-2.0+
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('WPALW_VERSION', '1.2.0');
define('WPALW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPALW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPALW_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once WPALW_PLUGIN_DIR . 'includes/class-wp-animated-live-wall.php';

// Initialize the plugin
function run_wp_animated_live_wall()
{
    $plugin = new WP_Animated_Live_Wall();
    $plugin->run();
}
run_wp_animated_live_wall();
