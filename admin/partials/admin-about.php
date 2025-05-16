<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wpalw-about-section">
    <div class="wpalw-about-header">
        <div class="wpalw-about-logo">
            <img src="<?php echo WPALW_PLUGIN_URL . 'admin/img/logo.png'; ?>" alt="Animated Live Wall Logo" onerror="this.src='<?php echo admin_url('images/wordpress-logo.svg'); ?>'; this.style.width='64px';">
        </div>
        <div class="wpalw-about-version">
            <h3>Animated Live Wall</h3>
            <p>Version <?php echo WPALW_VERSION; ?></p>
        </div>
    </div>

    <div class="wpalw-about-description">
        <p>Animated Live Wall is a powerful WordPress plugin that creates dynamic and interactive image galleries. The images alternate through various animation effects, creating a lively, constantly changing image wall.</p>
    </div>

    <h3>Features</h3>
    <ul class="wpalw-feature-list">
        <li>Manage multiple animated image walls</li>
        <li>Nine different transition effects</li>
        <li>Flexible grid settings (columns and rows)</li>
        <li>Responsive design for all screen sizes</li>
        <li>Key visual mode with title and subtitle overlay</li>
        <li>Easy integration via shortcode</li>
        <li>Full control over animation speed and transitions</li>
    </ul>

    <h3>Usage</h3>
    <p>Add an animated image wall to any page or post with the shortcode [animated_live_wall].</p>
    <p>Customize the appearance and behavior of each wall with the various parameters as described in the shortcode tab.</p>

    <h3>System Requirements</h3>
    <ul>
        <li>WordPress 5.0 or higher</li>
        <li>PHP 7.0 or higher</li>
        <li>JavaScript enabled in browser</li>
    </ul>

    <h3>Developed by</h3>
    <div class="wpalw-developer-info">
        <p>This plugin is developed and maintained by <a href="https://github.com/dersimoezdag/WPAnimatedLiveWall" target="_blank">Dersim Özdag</a>.</p>
        <p>For support, feature requests, or bug reports, please use the <a href="https://github.com/dersimoezdag/WPAnimatedLiveWall/issues" target="_blank">GitHub page</a>.</p>
    </div>


    <div class="wpalw-about-footer">
        <p class="wpalw-credits">Developed with ♥ for WordPress</p>
    </div>
</div>

<style>
    .wpalw-about-section {
        max-width: 800px;
        margin: 20px 0;
        background: #fff;
        padding: 25px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .wpalw-about-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }

    .wpalw-about-logo {
        margin-right: 20px;
    }

    .wpalw-about-logo img {
        width: 80px;
        height: auto;
    }

    .wpalw-about-version h3 {
        margin: 0 0 5px 0;
        font-size: 24px;
    }

    .wpalw-about-version p {
        margin: 0;
        color: #888;
    }

    .wpalw-about-description {
        margin-bottom: 25px;
        font-size: 15px;
        line-height: 1.6;
    }

    .wpalw-feature-list {
        margin: 0 0 25px 20px;
        list-style-type: square;
    }

    .wpalw-feature-list li {
        margin-bottom: 8px;
        line-height: 1.4;
    }

    .wpalw-developer-info {
        margin: 15px 0 20px;
        padding: 15px;
        background: #f9f9f9;
        border-left: 4px solid #0073aa;
        border-radius: 3px;
    }

    .wpalw-social-links {
        margin-top: 10px;
    }

    .wpalw-social-links a {
        display: inline-block;
        margin-right: 10px;
        text-decoration: none;
    }

    .wpalw-social-links .dashicons {
        font-size: 22px;
        width: 22px;
        height: 22px;
    }

    .wpalw-about-footer {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
        text-align: center;
        font-style: italic;
        color: #666;
    }
</style>