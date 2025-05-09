(function ($) {
  'use strict';

  $(document).ready(function () {
    // Initialize all live walls on the page
    $('.wpalw-container').each(function () {
      initLiveWall($(this));
    });

    /**
     * Initialize a specific live wall
     */
    function initLiveWall($container) {
      // Get all images in this wall
      var $images = $container.find('.wpalw-image');

      if ($images.length === 0) {
        return;
      }

      var wallId = $container.data('wall-id');

      // Collect all image URLs from this wall
      var allImageUrls = collectAllImageUrls($images);

      if (allImageUrls.length <= 1) {
        return; // Need at least 2 images to switch
      }

      // Start animations after a short delay to ensure all images are loaded
      setTimeout(function () {
        startSingleTileChanges($container, $images, allImageUrls);
      }, 1000);
    }

    /**
     * Collect all image URLs from a set of images
     */
    function collectAllImageUrls($images) {
      var urls = [];
      $images.each(function () {
        var src = $(this).attr('src');
        if (src) {
          urls.push(src);
        }
      });
      return urls;
    }

    /**
     * Start changing one random tile at a time
     */
    function startSingleTileChanges($container, $images, allImageUrls) {
      var animationSpeed = parseInt(wpalw_data.animation_speed) || 5000;

      // Keep track of currently displayed images
      var currentImageMap = {};

      // Initialize the image map with current images
      $images.each(function () {
        var src = $(this).attr('src');
        currentImageMap[src] = true;
      });

      // Function to change a random tile
      function changeRandomTile() {
        // Select a random tile
        var randomIndex = Math.floor(Math.random() * $images.length);
        var $randomImage = $($images[randomIndex]);

        // Switch this image (ensuring no duplicates when possible)
        switchImage($randomImage, allImageUrls, currentImageMap);

        // Schedule the next change after the animation speed delay
        setTimeout(changeRandomTile, animationSpeed);
      }

      // Start the process
      changeRandomTile();
    }

    /**
     * Switch an image to a random one, avoiding duplicates if possible
     */
    function switchImage($image, allImageUrls, currentImageMap) {
      var currentSrc = $image.attr('src');

      // Remove the current image from the map before selecting a new one
      delete currentImageMap[currentSrc];

      // Find images that aren't currently displayed anywhere
      var availableImages = [];
      for (var i = 0; i < allImageUrls.length; i++) {
        if (!currentImageMap[allImageUrls[i]]) {
          availableImages.push(allImageUrls[i]);
        }
      }

      var newSrc;

      // If we have available images that aren't displayed elsewhere, use one
      if (availableImages.length > 0) {
        var randomAvailableIndex = Math.floor(Math.random() * availableImages.length);
        newSrc = availableImages[randomAvailableIndex];
      } else {
        // Otherwise just pick any image that's different from the current one
        do {
          var randomIndex = Math.floor(Math.random() * allImageUrls.length);
          newSrc = allImageUrls[randomIndex];
        } while (newSrc === currentSrc && allImageUrls.length > 1);
      }

      // Add the new image to the current image map
      currentImageMap[newSrc] = true;

      // Perform the animation
      $image.fadeOut(400, function () {
        $image.attr('src', newSrc);
        $image.fadeIn(400);
      });
    }
  });
})(jQuery);
