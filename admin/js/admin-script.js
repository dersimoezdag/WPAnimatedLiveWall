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

    // Haupttab-Navigation mit automatischer Auswahl des Images-Tabs
    $('#wpalw-admin-tabs a').click(function (e) {
      e.preventDefault();
      var target = $(this).attr('href');

      // Update active tab
      $('#wpalw-admin-tabs a').removeClass('nav-tab-active');
      $(this).addClass('nav-tab-active');

      // Show target content, hide others
      $('.wpalw-tab-content').hide();
      $(target).show();

      // Bei Wechsel zum Manage Walls-Tab automatisch den Images-Tab aktivieren
      if (target === '#tab-walls') {
        $('.nav-tab-wrapper a[href="#images-tab"]').addClass('nav-tab-active').siblings().removeClass('nav-tab-active');
        $('.tab-pane').hide();
        $('#images-tab').show();
      }
    });

    // Wall selection
    $('#wpalw-select-wall').on('change', function () {
      var wallId = $(this).val();
      window.location.href = 'options-general.php?page=wp-animated-live-wall&wall=' + wallId;
    });

    // Add new wall
    $('#wpalw-add-wall').on('click', function () {
      var wallName = prompt(wpalw_data.i18n.new_wall);

      if (!wallName) {
        return;
      }

      $.ajax({
        url: wpalw_data.ajax_url,
        type: 'POST',
        data: {
          action: 'wpalw_save_wall',
          nonce: wpalw_data.nonce,
          wall_data: {
            name: wallName
          }
        },
        success: function (response) {
          if (response.success) {
            window.location.href = 'options-general.php?page=wp-animated-live-wall&wall=' + response.data.id;
          }
        }
      });
    });

    // Remove wall
    $('#wpalw-remove-wall').on('click', function () {
      if (!confirm(wpalw_data.i18n.confirm_remove_wall)) {
        return;
      }

      var wallId = $(this).data('id');

      $.ajax({
        url: wpalw_data.ajax_url,
        type: 'POST',
        data: {
          action: 'wpalw_remove_wall',
          nonce: wpalw_data.nonce,
          wall_id: wallId
        },
        success: function (response) {
          if (response.success) {
            window.location.href = 'options-general.php?page=wp-animated-live-wall';
          }
        }
      });
    });

    // Save wall name
    $('#wpalw-save-wall-name').on('click', function () {
      var wallId = $(this).data('id');
      var wallName = $('#wpalw-wall-name').val();

      if (!wallName) {
        alert('Please enter a wall name');
        return;
      }

      $.ajax({
        url: wpalw_data.ajax_url,
        type: 'POST',
        data: {
          action: 'wpalw_save_wall',
          nonce: wpalw_data.nonce,
          wall_data: {
            id: wallId,
            name: wallName
          }
        },
        success: function (response) {
          if (response.success) {
            // Update the dropdown option
            $('#wpalw-select-wall option[value="' + wallId + '"]').text(wallName);

            // Show success message
            var button = $('#wpalw-save-wall-name');
            var originalText = button.text();
            button.text('Saved!');
            setTimeout(function () {
              button.text(originalText);
            }, 1500);
          }
        }
      });
    });

    // Image selection
    var mediaFrame;
    $('#wpalw-add-images').on('click', function (e) {
      e.preventDefault();

      var wallId = $(this).data('wall-id');

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
          addImage(attachment, wallId);
        });
      });

      mediaFrame.open();
    });

    // Remove image
    $(document).on('click', '.wpalw-remove-image', function (e) {
      e.preventDefault();

      var item = $(this).closest('.wpalw-image-item');
      var imageId = item.data('id');
      var wallId = $('#wpalw-image-list').data('wall-id');

      // AJAX call to remove image
      $.ajax({
        url: wpalw_data.ajax_url,
        type: 'POST',
        data: {
          action: 'wpalw_remove_image',
          nonce: wpalw_data.nonce,
          image_id: imageId,
          wall_id: wallId
        }
      });

      item.fadeOut('fast', function () {
        item.remove();
      });
    });

    // Make image list sortable
    $('#wpalw-image-list').sortable({
      placeholder: 'wpalw-sortable-placeholder',
      cursor: 'move',
      update: function () {
        updateImageOrder();
      }
    });

    // Save wall settings
    $('#wpalw-settings-form').on('submit', function (e) {
      e.preventDefault();

      var wallId = $('input[name="wpalw_wall_id"]').val();
      var animationSpeed = $('#wpalw_animation_speed').val();
      var columns = $('#wpalw_columns').val();
      var rows = $('#wpalw_rows').val();

      $.ajax({
        url: wpalw_data.ajax_url,
        type: 'POST',
        data: {
          action: 'wpalw_save_wall',
          nonce: wpalw_data.nonce,
          wall_data: {
            id: wallId,
            animation_speed: animationSpeed,
            columns: columns,
            rows: rows
          }
        },
        success: function (response) {
          if (response.success) {
            // Show success message
            var button = $('#wpalw-settings-form .button-primary');
            var originalText = button.val();
            button.val('Saved!');
            setTimeout(function () {
              button.val(originalText);
            }, 1500);
          }
        }
      });
    });

    // Save wall images
    $('#wpalw-images-form').on('submit', function (e) {
      e.preventDefault();
      updateImageOrder();
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
    function addImage(attachment, wallId) {
      var imageId = attachment.id;
      var imageUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

      // Check if image already exists
      if ($('#wpalw-image-list').find('[data-id="' + imageId + '"]').length) {
        return;
      }

      // AJAX call to save image
      $.ajax({
        url: wpalw_data.ajax_url,
        type: 'POST',
        data: {
          action: 'wpalw_save_image',
          nonce: wpalw_data.nonce,
          image_id: imageId,
          wall_id: wallId
        },
        success: function (response) {
          if (response.success) {
            var template =
              '<div class="wpalw-image-item" data-id="' +
              imageId +
              '">' +
              '<input type="hidden" name="wpalw_image_ids[]" value="' +
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
        }
      });
    }

    // Update image order in a wall
    function updateImageOrder() {
      var wallId = $('#wpalw-current-wall-id').val();
      var imageIds = [];

      // Collect image IDs in current order
      $('#wpalw-image-list .wpalw-image-item').each(function () {
        imageIds.push($(this).data('id'));
      });

      // Update order via AJAX
      $.ajax({
        url: wpalw_data.ajax_url,
        type: 'POST',
        data: {
          action: 'wpalw_update_image_order',
          nonce: wpalw_data.nonce,
          wall_id: wallId,
          image_ids: imageIds
        },
        success: function (response) {
          if (response.success) {
            // Show success message
            var button = $('#wpalw-images-form .button-primary');
            var originalText = button.val();
            button.val('Saved!');
            setTimeout(function () {
              button.val(originalText);
            }, 1500);
          }
        }
      });
    }
  });
})(jQuery);
