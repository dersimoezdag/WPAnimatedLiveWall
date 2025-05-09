<div class="wpalw-container" data-wall-id="<?php echo esc_attr($wall_id); ?>">
    <div class="wpalw-grid" data-columns="<?php echo esc_attr($columns); ?>" data-rows="<?php echo esc_attr($rows); ?>">
        <?php
        foreach ($images as $image_id) :
            $image_full = wp_get_attachment_image_src($image_id, 'full');
            $image_medium = wp_get_attachment_image_src($image_id, 'medium_large');
            $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);

            if ($image_full && $image_medium) :
        ?>
                <div class="wpalw-grid-item">
                    <div class="wpalw-image-container">
                        <img
                            src="<?php echo esc_url($image_medium[0]); ?>"
                            data-wall-id="<?php echo esc_attr($wall_id); ?>"
                            data-image-id="<?php echo esc_attr($image_id); ?>"
                            alt="<?php echo esc_attr($alt_text); ?>"
                            class="wpalw-image">
                    </div>
                </div>
        <?php
            endif;
        endforeach;
        ?>
    </div>
</div>