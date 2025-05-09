<div class="wpalw-container">
    <div class="wp-animated-live-wall" data-wall-id="<?php echo esc_attr($wall_id); ?>" data-columns="<?php echo esc_attr($columns); ?>" data-rows="<?php echo esc_attr($rows); ?>" data-animation-speed="<?php echo isset($wall['animation_speed']) ? esc_attr($wall['animation_speed']) : '5000'; ?>" data-transition="<?php echo isset($wall['transition']) ? esc_attr($wall['transition']) : '400'; ?>" data-gap="<?php echo isset($wall['gap']) ? esc_attr($wall['gap']) : '4'; ?>" style="grid-gap: <?php echo esc_attr(isset($wall['gap']) && $wall['gap'] !== '' ? $wall['gap'] : '4'); ?>px;">
        <?php
        $total_allowed_images = intval($columns) * intval($rows);
        $visible_images = array_slice($images, 0, $total_allowed_images);
        $hidden_images = array_slice($images, $total_allowed_images);

        // Ausgabe der sichtbaren Bilder
        foreach ($visible_images as $image_id) :
            $image_data = wp_get_attachment_image_src($image_id, 'medium_large');
            $image_full = wp_get_attachment_image_src($image_id, 'full');
            $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);

            if ($image_data) :
                // Srcset für Responsive Images generieren
                $srcset = array();
                $small = wp_get_attachment_image_src($image_id, 'medium');
                $large = wp_get_attachment_image_src($image_id, 'large');

                if ($small) $srcset[] = $small[0] . ' 300w';
                if ($image_data) $srcset[] = $image_data[0] . ' 768w';
                if ($large) $srcset[] = $large[0] . ' 1024w';
                if ($image_full) $srcset[] = $image_full[0] . ' ' . $image_full[1] . 'w';

                $srcset_attr = !empty($srcset) ? implode(', ', $srcset) : '';
        ?>
                <div class="wall-tile">
                    <img src="<?php echo esc_url($image_data[0]); ?>" srcset="<?php echo esc_attr($srcset_attr); ?>" sizes="(max-width: 480px) calc(50vw - 8px), (max-width: 768px) calc(33vw - 8px), calc(25vw - 8px)" alt="<?php echo esc_attr($alt_text); ?>" data-id="<?php echo esc_attr($image_id); ?>">
                </div>
            <?php
            endif;
        endforeach;

        // Versteckte Bilder für Rotation
        if (!empty($hidden_images)) : ?>
            <div class="wpalw-hidden-images" style="display:none;">
                <?php foreach ($hidden_images as $image_id) :
                    $image_data = wp_get_attachment_image_src($image_id, 'medium_large');
                    $image_full = wp_get_attachment_image_src($image_id, 'full');
                    $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);

                    if ($image_data) :
                        // Srcset für Responsive Images generieren
                        $srcset = array();
                        $small = wp_get_attachment_image_src($image_id, 'medium');
                        $large = wp_get_attachment_image_src($image_id, 'large');

                        if ($small) $srcset[] = $small[0] . ' 300w';
                        if ($image_data) $srcset[] = $image_data[0] . ' 768w';
                        if ($large) $srcset[] = $large[0] . ' 1024w';
                        if ($image_full) $srcset[] = $image_full[0] . ' ' . $image_full[1] . 'w';

                        $srcset_attr = !empty($srcset) ? implode(', ', $srcset) : '';
                ?>
                        <div data-id="<?php echo esc_attr($image_id); ?>">
                            <img src="<?php echo esc_url($image_data[0]); ?>" srcset="<?php echo esc_attr($srcset_attr); ?>" sizes="(max-width: 480px) calc(50vw - 8px), (max-width: 768px) calc(33vw - 8px), calc(25vw - 8px)" alt="<?php echo esc_attr($alt_text); ?>">
                        </div>
                <?php endif;
                endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>