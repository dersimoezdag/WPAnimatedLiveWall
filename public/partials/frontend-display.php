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

// Sammle alle Bildquellen für JavaScript
$all_image_urls = array();
if (isset($images) && is_array($images)) {
    foreach ($images as $image_id) {
        $image = wp_get_attachment_image_src($image_id, 'large');
        if ($image) {
            $all_image_urls[] = $image[0];
        }
    }
}
$all_image_urls_json = json_encode($all_image_urls);
?>

<div id="wpalw-<?php echo $wall_id; ?>" class="wp-animated-live-wall"
    data-rows="<?php echo $rows; ?>"
    data-columns="<?php echo $columns; ?>"
    data-animation-speed="<?php echo $animation_speed; ?>"
    data-transition="<?php echo $transition; ?>"
    data-gap="<?php echo $gap; ?>"
    data-effects='<?php echo $effects_json; ?>'
    data-all-image-urls='<?php echo $all_image_urls_json; ?>'
    style="grid-template-columns: repeat(<?php echo $columns; ?>, 1fr); grid-gap: <?php echo $gap; ?>px;"><?php
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