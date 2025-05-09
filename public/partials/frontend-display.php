<div class="wpalw-container">
    <div class="wpalw-grid" data-columns="<?php echo esc_attr($columns); ?>">
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
                            data-all-images="<?php echo esc_attr(json_encode(array($image_id))); ?>"
                            data-current-index="0"
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