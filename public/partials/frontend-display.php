<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Sicherstellen, dass die selected_effects ein Array ist
if (!isset($selected_effects) || !is_array($selected_effects) || empty($selected_effects)) {
    $selected_effects = array('crossfade');
}

// JSON für JavaScript vorbereiten
$effects_json = json_encode($selected_effects);

// Sammle alle Bildquellen für JavaScript - stelle sicher, dass alle Bilder übergeben werden
$all_image_urls = array();
if (isset($images) && is_array($images)) {
    // Calculate appropriate image size based on rows and columns
    $tile_count = $rows * $columns;
    $image_size = 'medium'; // Default fallback

    // Use thumbnail for many tiles, medium for average, large for few tiles
    if ($tile_count > 16) {
        $image_size = 'thumbnail';
    } elseif ($tile_count <= 6) {
        $image_size = 'large';
    }

    foreach ($images as $image_id) {
        $image = wp_get_attachment_image_src($image_id, $image_size);
        if ($image) {
            $all_image_urls[] = $image[0];
        }
    }
}
$all_image_urls_json = json_encode($all_image_urls);

// Prüfe, ob genügend Bilder für die Wand vorhanden sind
$has_enough_images = count($all_image_urls) >= ($rows * $columns);
?>

<div class="wpalw-container<?php echo (isset($keyvisual_mode) && $keyvisual_mode) ? ' wpalw-keyvisual-container' : ''; ?>" id="wpalw-container-<?php echo $wall_id; ?>"> <?php if (isset($keyvisual_mode) && $keyvisual_mode) : ?>

    <div class="wpalw-keyvisual wpalw-keyvisual-<?php echo esc_attr($keyvisual_position); ?>">
        <?php if (!empty($keyvisual_title)) : ?>
        <h1 class="wpalw-keyvisual-title" style="background-color: <?php echo esc_attr($keyvisual_bgcolor); ?>;">
            <?php echo htmlspecialchars($keyvisual_title, ENT_QUOTES, 'UTF-8'); ?>
        </h1>
        <?php endif; ?>
        <?php if (!empty($keyvisual_subtitle)) : ?>
        <p class="wpalw-keyvisual-subtitle" style="background-color: <?php echo esc_attr($keyvisual_bgcolor); ?>;">
            <?php echo htmlspecialchars($keyvisual_subtitle, ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div id="wpalw-<?php echo $wall_id; ?>" class="wp-animated-live-wall" data-rows="<?php echo $rows; ?>" data-columns="<?php echo $columns; ?>" data-animation-speed="<?php echo $animation_speed; ?>" data-transition="<?php echo $transition; ?>" data-gap="<?php echo $gap; ?>" data-effects='<?php echo $effects_json; ?>' data-all-image-urls='<?php echo $all_image_urls_json; ?>' data-tiles-at-once="<?php echo $tiles_at_once; ?>" style="grid-template-columns: repeat(<?php echo $columns; ?>, 1fr); grid-gap: <?php echo $gap; ?>px;"><?php
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    // Bilder anzeigen
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    $counter = 0;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    $max_tiles = $rows * $columns;

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    foreach ($images as $key => $image_id) :
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        // Nur so viele Bilder anzeigen, wie Kacheln vorhanden sind
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        if ($counter >= $max_tiles) break;

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        $image = wp_get_attachment_image_src($image_id, 'large');
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        if ($image) :
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            $counter++;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    ?>
        <div class="wall-tile">
            <img src="<?php echo $image[0]; ?>" alt="" />
        </div>
        <?php
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        endif;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    endforeach;
        ?>
    </div>
</div>