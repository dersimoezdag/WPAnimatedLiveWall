(function ($) {
  'use strict';

  $(document).ready(function () {
    // Initialize live wall
    initLiveWall();

    function initLiveWall() {
      // Get all images in the grid
      var $images = $('.wpalw-image');

      if ($images.length === 0) {
        return;
      }

      // Collect all images from the wall for random switching
      var allImageUrls = collectAllImageUrls();

      // Start animations after a short delay to ensure all images are loaded
      setTimeout(function () {
        startRandomSwitching(allImageUrls);
      }, 1000);
    }

    // Collect all image URLs from the live wall
    function collectAllImageUrls() {
      var urls = [];
      $('.wpalw-image').each(function () {
        var src = $(this).attr('src');
        if (src) {
          urls.push(src);
        }
      });
      return urls;
    }

    // Start random switching of images
    function startRandomSwitching(allImageUrls) {
      if (allImageUrls.length <= 1) {
        return; // Need at least 2 images to switch
      }

      var animationSpeed = parseInt(wpalw_data.animation_speed) || 5000;

      // For each image, set a random interval to change
      $('.wpalw-image').each(function () {
        var $image = $(this);

        // Set individual random timing for each image
        var randomDelay = Math.floor(Math.random() * animationSpeed) + 1000;
        setInterval(function () {
          switchImage($image, allImageUrls);
        }, randomDelay);
      });
    }

    // Switch an image to a random one
    function switchImage($image, allImageUrls) {
      if (allImageUrls.length <= 1) {
        return; // Need at least 2 images to switch
      }

      var currentSrc = $image.attr('src');
      var currentIndex = allImageUrls.indexOf(currentSrc);

      // Randomly select a different image
      var newIndex;
      var attempts = 0;
      var maxAttempts = 10;

      do {
        newIndex = Math.floor(Math.random() * allImageUrls.length);
        attempts++;
      } while (newIndex === currentIndex && attempts < maxAttempts);

      // Get new image URL
      var newSrc = allImageUrls[newIndex];

      // Perform the animation
      $image.fadeOut(400, function () {
        $image.attr('src', newSrc);
        $image.fadeIn(400);
      });
    }
  });
})(jQuery);
