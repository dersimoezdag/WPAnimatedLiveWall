(function ($) {
  'use strict';

  $(document).ready(function () {
    // Tab navigation
    $('.nav-tab').on('click', function (e) {
      e.preventDefault();

      $('.nav-tab').removeClass('nav-tab-active');
      $(this).addClass('nav-tab-active');

      var tabId = $(this).attr('href');
      $('.tab-pane').removeClass('active');
      $(tabId).addClass('active');
    });

    // Image selection
    var mediaFrame;
    $('#wpalw-add-images').on('click', function (e) {
      e.preventDefault();

      if (mediaFrame) {
        mediaFrame.open();
        return;
      }

      mediaFrame = wp.media({
        title: wpalw_data.i18n.select_images,
        button: {
          text: wpalw_data.i18n.select_images
        },
        multiple: true
      });

      mediaFrame.on('select', function () {
        var attachments = mediaFrame.state().get('selection').toJSON();

        attachments.forEach(function (attachment) {
          addImage(attachment);
        });
      });

      mediaFrame.open();
    });

    // Remove image
    $(document).on('click', '.wpalw-remove-image', function (e) {
      e.preventDefault();

      var item = $(this).closest('.wpalw-image-item');
      item.fadeOut('fast', function () {
        item.remove();
      });
    });

    // Make image list sortable
    $('#wpalw-image-list').sortable({
      placeholder: 'wpalw-sortable-placeholder',
      cursor: 'move'
    });

    // Copy shortcode
    $('.wpalw-copy-shortcode').on('click', function () {
      var shortcode = $(this).prev('code').text();
      var tempInput = $('<input>');
      $('body').append(tempInput);
      tempInput.val(shortcode).select();
      document.execCommand('copy');
      tempInput.remove();

      var button = $(this);
      var originalText = button.html();
      button.html('<span class="dashicons dashicons-yes"></span> Copied!');
      setTimeout(function () {
        button.html(originalText);
      }, 2000);
    });

    // Helper function to add new image to the list
    function addImage(attachment) {
      var imageId = attachment.id;
      var imageUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

      // Check if image already exists
      if ($('#wpalw-image-list').find('[data-id="' + imageId + '"]').length) {
        return;
      }

      var template =
        '<div class="wpalw-image-item" data-id="' +
        imageId +
        '">' +
        '<input type="hidden" name="wpalw_images[]" value="' +
        imageId +
        '">' +
        '<div class="wpalw-image-preview">' +
        '<img src="' +
        imageUrl +
        '" alt="">' +
        '</div>' +
        '<div class="wpalw-image-actions">' +
        '<a href="#" class="wpalw-remove-image">' +
        '<span class="dashicons dashicons-trash"></span>' +
        '</a>' +
        '</div>' +
        '</div>';

      $('#wpalw-image-list').append(template);
    }

    // AJAX save image
    function ajaxSaveImage(imageId) {
      $.ajax({
        url: wpalw_data.ajax_url,
        type: 'POST',
        data: {
          action: 'wpalw_save_image',
          image_id: imageId,
          nonce: wpalw_data.nonce
        },
        success: function (response) {
          if (response.success) {
            console.log('Image saved successfully');
          }
        }
      });
    }
  });
})(jQuery);
