(function ($) {
  'use strict';

  // Alle verfügbaren Animations-Effekte
  const effectHandlers = {
    crossfade: function (tile, nextImage) {
      // Improved Crossfade - Standard einfacher Überblendeffekt
      $(nextImage)
        .css({
          opacity: 0,
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          objectFit: 'cover'
        })
        .appendTo(tile);

      $(nextImage).animate(
        {
          opacity: 1
        },
        options.transition,
        function () {
          tile.find('img:first').remove();
        }
      );
    },
    zoomfade: function (tile, nextImage) {
      // Zoom Fade - Bild zoomt leicht herein während es eingeblendet wird
      $(nextImage)
        .css({
          opacity: 0,
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          objectFit: 'cover',
          transform: 'scale(1.1)'
        })
        .appendTo(tile);

      $(nextImage).animate(
        {
          opacity: 1,
          transform: 'scale(1)'
        },
        options.transition,
        function () {
          tile.find('img:first').remove();
        }
      );
    },
    slideup: function (tile, nextImage) {
      // Slide Up - Bild schiebt sich von unten nach oben ein
      $(nextImage)
        .css({
          position: 'absolute',
          top: '100%',
          left: 0,
          width: '100%',
          height: '100%',
          objectFit: 'cover'
        })
        .appendTo(tile);

      tile.find('img:first').animate(
        {
          top: '-100%'
        },
        options.transition
      );

      $(nextImage).animate(
        {
          top: 0
        },
        options.transition,
        function () {
          tile.find('img:first').remove();
        }
      );
    },
    slidedown: function (tile, nextImage) {
      // Slide Down - Bild schiebt sich von oben nach unten ein
      $(nextImage)
        .css({
          position: 'absolute',
          top: '-100%',
          left: 0,
          width: '100%',
          height: '100%',
          objectFit: 'cover'
        })
        .appendTo(tile);

      tile.find('img:first').animate(
        {
          top: '100%'
        },
        options.transition
      );

      $(nextImage).animate(
        {
          top: 0
        },
        options.transition,
        function () {
          tile.find('img:first').remove();
        }
      );
    },
    slideleft: function (tile, nextImage) {
      // Slide Left - Bild schiebt sich von rechts nach links ein
      $(nextImage)
        .css({
          position: 'absolute',
          top: 0,
          left: '100%',
          width: '100%',
          height: '100%',
          objectFit: 'cover'
        })
        .appendTo(tile);

      tile.find('img:first').animate(
        {
          left: '-100%'
        },
        options.transition
      );

      $(nextImage).animate(
        {
          left: 0
        },
        options.transition,
        function () {
          tile.find('img:first').remove();
        }
      );
    },
    slideright: function (tile, nextImage) {
      // Slide Right - Bild schiebt sich von links nach rechts ein
      $(nextImage)
        .css({
          position: 'absolute',
          top: 0,
          left: '-100%',
          width: '100%',
          height: '100%',
          objectFit: 'cover'
        })
        .appendTo(tile);

      tile.find('img:first').animate(
        {
          left: '100%'
        },
        options.transition
      );

      $(nextImage).animate(
        {
          left: 0
        },
        options.transition,
        function () {
          tile.find('img:first').remove();
        }
      );
    },
    rotate: function (tile, nextImage) {
      // Rotate - Dreht und blendet das neue Bild ein während das alte ausgeblendet wird
      var currentImg = tile.find('img:first');
      currentImg.css({
        position: 'absolute',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        objectFit: 'cover',
        transformOrigin: 'center center',
        zIndex: 1
      });
      $(nextImage)
        .css({
          opacity: 0,
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          objectFit: 'cover',
          transform: 'rotate(-30deg) scale(0.8)',
          transformOrigin: 'center center',
          zIndex: 2
        })
        .appendTo(tile);
      var duration = options.transition;
      var start = null;
      function animateRotate(ts) {
        if (!start) start = ts;
        var progress = Math.min((ts - start) / duration, 1);
        // Altes Bild rausdrehen
        var oldAngle = 0 + 30 * progress;
        var oldScale = 1 - 0.2 * progress;
        var oldOpacity = 1 - progress;
        currentImg.css({
          opacity: oldOpacity,
          transform: 'rotate(' + oldAngle + 'deg) scale(' + oldScale + ')'
        });
        // Neues Bild reindrehen
        var newAngle = -30 + 30 * progress;
        var newScale = 0.8 + 0.2 * progress;
        var newOpacity = progress;
        $(nextImage).css({
          opacity: newOpacity,
          transform: 'rotate(' + newAngle + 'deg) scale(' + newScale + ')'
        });
        if (progress < 1) {
          requestAnimationFrame(animateRotate);
        } else {
          currentImg.remove();
        }
      }
      requestAnimationFrame(animateRotate);
    },
    blurfade: function (tile, nextImage) {
      // Blur Fade - Weichzeichnen-Effekt beim Überblenden
      var currentImg = tile.find('img:first');
      currentImg.css({
        filter: 'blur(0px)',
        position: 'absolute',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        objectFit: 'cover',
        zIndex: 1
      });
      $(nextImage)
        .css({
          opacity: 0,
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          objectFit: 'cover',
          filter: 'blur(5px)',
          zIndex: 2
        })
        .appendTo(tile);
      var duration = options.transition;
      var start = null;
      function animateBlur(ts) {
        if (!start) start = ts;
        var progress = Math.min((ts - start) / duration, 1);
        // Altes Bild ausblenden und bluren
        var oldOpacity = 1 - progress;
        var oldBlur = 0 + 5 * progress;
        currentImg.css({
          opacity: oldOpacity,
          filter: 'blur(' + oldBlur + 'px)'
        });
        // Neues Bild einblenden und schärfen
        var newOpacity = progress;
        var newBlur = 5 - 5 * progress;
        $(nextImage).css({
          opacity: newOpacity,
          filter: 'blur(' + newBlur + 'px)'
        });
        if (progress < 1) {
          requestAnimationFrame(animateBlur);
        } else {
          currentImg.remove();
        }
      }
      requestAnimationFrame(animateBlur);
    },
    flip: function (tile, nextImage) {
      // 3D Flip - Dreht das Element wie eine Karte um
      tile.css({
        perspective: '1000px',
        position: 'relative'
      });
      var currentImg = tile.find('img:first');
      currentImg.css({
        position: 'absolute',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        objectFit: 'cover',
        backfaceVisibility: 'hidden',
        transform: 'rotateY(0deg)',
        zIndex: 1
      });
      $(nextImage)
        .css({
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          objectFit: 'cover',
          backfaceVisibility: 'hidden',
          transform: 'rotateY(-180deg)',
          zIndex: 2
        })
        .appendTo(tile);
      var duration = options.transition;
      var start = null;
      function animateFlip(ts) {
        if (!start) start = ts;
        var progress = Math.min((ts - start) / duration, 1);
        var oldAngle = 0 + 180 * progress;
        var newAngle = -180 + 180 * progress;
        currentImg.css({
          transform: 'rotateY(' + oldAngle + 'deg)'
        });
        $(nextImage).css({
          transform: 'rotateY(' + newAngle + 'deg)'
        });
        if (progress < 1) {
          requestAnimationFrame(animateFlip);
        } else {
          currentImg.remove();
          tile.css({ perspective: 'none' });
        }
      }
      requestAnimationFrame(animateFlip);
    }
  };

  // Optionen für die Animation
  var options = {
    animationSpeed: 5000, // Zeit zwischen den Bildwechseln
    transition: 400, // Dauer der Überblendung
    availableEffects: ['crossfade'], // Standard-Effekt
    randomize: true, // Zufälliger Effekt aus den verfügbaren
    tilesAtOnce: 1 // Standardmäßig 1 Kachel gleichzeitig
  };
  function init() {
    $('.wp-animated-live-wall').each(function () {
      var $wall = $(this);

      // Debug: Zeige alle data-Attribute an
      // console.log('Wall data attributes:', $wall.data());

      // Zufällige ID generieren, falls keine vorhanden ist
      var wallId = $wall.data('id');
      if (!wallId) {
        wallId = 'wall-' + Math.random().toString(36).substr(2, 9);
        $wall.attr('data-id', wallId);
      }

      // Optionen aus den data-Attributen lesen
      var wallOptions = {
        rows: parseInt($wall.data('rows'), 10) || 3,
        columns: parseInt($wall.data('columns'), 10) || 4,
        columns_sm: parseInt($wall.data('columns-sm'), 10) || 2,
        columns_md: parseInt($wall.data('columns-md'), 10) || 3,
        columns_lg: parseInt($wall.data('columns-lg'), 10) || 4,
        columns_xl: parseInt($wall.data('columns-xl'), 10) || 5,
        animationSpeed: parseInt($wall.data('animation-speed'), 10) || options.animationSpeed,
        transition: parseInt($wall.data('transition'), 10) || options.transition,
        tilesAtOnce: isNaN(parseInt($wall.data('tiles-at-once'), 10)) ? options.tilesAtOnce : parseInt($wall.data('tiles-at-once'), 10),
        animating: false, // Flag zur Kontrolle der Animation
        tilesAnimated: 0 // Zähler für animierte Kacheln
      }; // Ausgewählte Effekte aus dem data-effects Attribut lesen
      var selectedEffects = $wall.data('effects');

      // Versuche das Parsen, falls der Wert als String vorliegt
      if (typeof selectedEffects === 'string') {
        try {
          selectedEffects = JSON.parse(selectedEffects);
        } catch (e) {
          console.error('Fehler beim Parsen der Effekte:', e);
          selectedEffects = null;
        }
      }

      if (selectedEffects && Array.isArray(selectedEffects) && selectedEffects.length > 0) {
        wallOptions.availableEffects = selectedEffects;
      } else {
        wallOptions.availableEffects = ['crossfade']; // Fallback auf Standardeffekt
      }

      // Setze die Wand-spezifischen Optionen
      $wall.data('options', wallOptions);

      // Initialisiere die Animation der Wand
      initWallAnimation($wall);
    });
  }
  function initWallAnimation(wall) {
    var wallOptions = wall.data('options');
    var tiles = wall.find('.wall-tile');

    // Initialisiere die komplette Bildliste für Rotationen
    var allImageUrls = wall.data('all-image-urls');

    if (allImageUrls && Array.isArray(allImageUrls) && allImageUrls.length > 0) {
      // Speichere alle Bildquellen in der Wand für die Rotation
      wall.data('all-images', allImageUrls);

      // Prüfe, ob genug Bilder für die Wand vorhanden sind
      var totalCells = wallOptions.rows * wallOptions.columns;
      var totalImages = allImageUrls.length;

      // Speichere diese Information in den Wandoptionen
      wallOptions.hasEnoughImages = totalImages >= totalCells;
    } else {
      // Fallback: Sammle nur die aktuell sichtbaren Bilder
      var visibleImages = [];
      tiles.find('img').each(function () {
        var src = $(this).attr('src');
        if (visibleImages.indexOf(src) === -1) {
          visibleImages.push(src);
        }
      });
      wall.data('all-images', visibleImages);

      // Prüfe, ob genug Bilder für die Wand vorhanden sind
      var totalCells = wallOptions.rows * wallOptions.columns;
      wallOptions.hasEnoughImages = visibleImages.length >= totalCells;
    }

    // Starte die erste Animation nach einer kurzen Verzögerung
    setTimeout(function () {
      animateNextTile(wall, wallOptions);
    }, 1000);
  }
  function animateNextTile(wall, wallOptions) {
    // Wenn bereits eine Animation läuft, nichts tun
    if (wallOptions.animating) {
      return;
    }

    var tiles = wall.find('.wall-tile');
    if (tiles.length === 0) return;

    // Bestimme wie viele Kacheln gleichzeitig wechseln sollen
    var tilesAtOnce = parseInt(wallOptions.tilesAtOnce, 10) || 1;
    // Stelle sicher, dass nicht mehr Kacheln animiert werden als vorhanden sind
    tilesAtOnce = Math.min(tilesAtOnce, tiles.length);

    // Reset des Zählers für animierte Kacheln
    wallOptions.tilesAnimated = 0;
    wallOptions.tilesInAnimation = tilesAtOnce;

    // Markiere die Wand als "animierend"
    wallOptions.animating = true;

    // Erstelle ein Array mit allen Kachel-Indizes
    var allTileIndices = [];
    for (var i = 0; i < tiles.length; i++) {
      allTileIndices.push(i);
    }

    // Mische die Indizes, um zufällige Kacheln auszuwählen
    shuffleArray(allTileIndices);

    // Wähle die ersten X Kacheln aus dem gemischten Array
    var selectedTileIndices = allTileIndices.slice(0, tilesAtOnce);

    for (var j = 0; j < selectedTileIndices.length; j++) {
      (function (tileIndex) {
        setTimeout(function () {
          var tile = tiles.eq(tileIndex);
          animateSingleTile(tile, wall, wallOptions);
        }, j * 150); // Kleine zeitliche Versetzung zwischen den Kacheln
      })(selectedTileIndices[j]);
    }

    // Hilfsfunktion zum Mischen eines Arrays (Fisher-Yates Shuffle)
    function shuffleArray(array) {
      for (var i = array.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var temp = array[i];
        array[i] = array[j];
        array[j] = temp;
      }
    }
  }
  function animateSingleTile(tile, wall, wallOptions) {
    // Sammle alle aktuell sichtbaren Bilder und ihre Quellen
    var allVisibleSources = [];
    wall.find('.wall-tile img').each(function () {
      allVisibleSources.push($(this).attr('src'));
    });
    // Sammle alle Bilder, die gerade als nextImage animiert werden (opacity < 1)
    var animatingSources = [];
    wall.find('.wall-tile img').each(function () {
      var $img = $(this);
      if ($img.css('opacity') < 1) {
        animatingSources.push($img.attr('src'));
      }
    });
    // Hole die komplette Liste aller Bilder aus dem data-all-images Attribut der Wand
    var allAvailableImages = wall.data('all-images');
    if (!allAvailableImages || allAvailableImages.length === 0) {
      allAvailableImages = [];
      var imageUrlsFromData = wall.data('all-image-urls');
      if (imageUrlsFromData && Array.isArray(imageUrlsFromData) && imageUrlsFromData.length > 0) {
        allAvailableImages = imageUrlsFromData;
      } else {
        wall.find('.wall-tile img').each(function () {
          var src = $(this).attr('src');
          if (allAvailableImages.indexOf(src) === -1) {
            allAvailableImages.push(src);
          }
        });
      }
      wall.data('all-images', allAvailableImages);
    }
    var hasEnoughImages = wallOptions.hasEnoughImages || false;
    var unusedImages = [];
    var currentImg = tile.find('img').attr('src');
    // Verhindere, dass ein Bild gleichzeitig irgendwo sichtbar oder animiert ist
    var forbiddenSources = allVisibleSources.concat(animatingSources);
    forbiddenSources = forbiddenSources.filter(function (src, idx, arr) {
      return arr.indexOf(src) === idx;
    });
    if (hasEnoughImages) {
      unusedImages = allAvailableImages.filter(function (src) {
        return forbiddenSources.indexOf(src) === -1;
      });
    } else {
      unusedImages = allAvailableImages.filter(function (src) {
        return src !== currentImg && forbiddenSources.indexOf(src) === -1;
      });
    }
    if (unusedImages.length === 0) {
      // Wenn keine unused images verfügbar sind, setze das Animationsflag zurück und plane die nächste Animation
      wallOptions.animating = false;
      setTimeout(function () {
        animateNextTile(wall, wallOptions);
      }, wallOptions.animationSpeed);
      return;
    }
    // Zufälliges Bild aus den ungenutzten Bildern auswählen
    var randomImgSrc = unusedImages[Math.floor(Math.random() * unusedImages.length)];
    var nextImage = document.createElement('img');
    nextImage.src = randomImgSrc;
    // Wähle einen zufälligen Effekt aus den verfügbaren
    var effect = 'crossfade'; // Standard-Effekt als Fallback
    if (wallOptions.availableEffects && wallOptions.availableEffects.length > 0) {
      effect = wallOptions.availableEffects[Math.floor(Math.random() * wallOptions.availableEffects.length)];

      // Wenn der zufällig gewählte Effekt nicht implementiert ist, nutze crossfade
      if (!effectHandlers[effect]) {
        console.warn('Effekt nicht gefunden:', effect);
        effect = 'crossfade';
      }
    }

    // Stelle sicher, dass die globale options Variable mit den aktuellen Wall-Optionen übereinstimmt
    options.transition = wallOptions.transition;
    options.animationSpeed = wallOptions.animationSpeed;

    // Warte bis die Animation abgeschlossen ist, um das Flag zurückzusetzen
    var effectDuration = wallOptions.transition;

    // Führe den Effekt aus
    effectHandlers[effect](tile, nextImage);
    // Nach Abschluss der Animation
    setTimeout(function () {
      wallOptions.tilesAnimated++;
      // Prüfe, ob alle Kacheln dieser Animationsrunde bearbeitet wurden
      if (wallOptions.tilesAnimated >= wallOptions.tilesInAnimation) {
        wallOptions.animating = false;
        // Berechne die Zeit bis zur nächsten Animation
        var nextDelay = Math.max(2000, wallOptions.animationSpeed);
        setTimeout(function () {
          animateNextTile(wall, wallOptions);
        }, nextDelay);
      }
    }, effectDuration + 100); // Extra Zeit für sicheres Beenden der Animation
  }
  function adjustGrid() {
    $('.wp-animated-live-wall').each(function () {
      var $wall = $(this);
      var wallOptions = $wall.data('options') || {};

      // Configure responsive behavior based on screen width
      const breakpoints = {
        sm: 576, // Small screens (mobile)
        md: 768, // Medium screens (tablets)
        lg: 992, // Large screens (small desktops)
        xl: 1200 // Extra large screens (large desktops)
      };

      // Get current screen width
      const screenWidth = window.innerWidth;

      // Determine number of columns based on screen size
      let actualColumns = wallOptions.columns || parseInt($wall.data('columns'), 10) || 4;
      let actualRows = wallOptions.rows || parseInt($wall.data('rows'), 10) || 3;

      // Check for responsive column and row settings
      if (screenWidth < breakpoints.sm) {
        // Small screens - mobile
        const smColumns = parseInt($wall.data('columns-sm'), 10);
        const smRows = parseInt($wall.data('rows-sm'), 10);
        if (!isNaN(smColumns)) actualColumns = smColumns;
        if (!isNaN(smRows)) actualRows = smRows;
      } else if (screenWidth < breakpoints.md) {
        // Medium screens - large mobile/small tablets
        const mdColumns = parseInt($wall.data('columns-md'), 10);
        const mdRows = parseInt($wall.data('rows-md'), 10);
        if (!isNaN(mdColumns)) actualColumns = mdColumns;
        if (!isNaN(mdRows)) actualRows = mdRows;
      } else if (screenWidth < breakpoints.lg) {
        // Large screens - tablets/small laptops
        const lgColumns = parseInt($wall.data('columns-lg'), 10);
        const lgRows = parseInt($wall.data('rows-lg'), 10);
        if (!isNaN(lgColumns)) actualColumns = lgColumns;
        if (!isNaN(lgRows)) actualRows = lgRows;
      } else if (screenWidth >= breakpoints.xl) {
        // Extra large screens - desktops
        const xlColumns = parseInt($wall.data('columns-xl'), 10);
        const xlRows = parseInt($wall.data('rows-xl'), 10);
        if (!isNaN(xlColumns)) actualColumns = xlColumns;
        if (!isNaN(xlRows)) actualRows = xlRows;
      }

      // Ensure we have valid numbers for columns and rows
      actualColumns = Math.max(1, Math.min(12, actualColumns));
      actualRows = Math.max(1, Math.min(12, actualRows));

      // Update the wallOptions with the current values
      wallOptions.currentColumns = actualColumns;
      wallOptions.currentRows = actualRows;

      // Calculate total required tiles
      const requiredTiles = actualColumns * actualRows;

      // Get current number of tiles
      const currentTiles = $wall.find('.wall-tile').length;

      // Add or remove tiles as needed
      adjustTiles($wall, currentTiles, requiredTiles);

      // Apply grid layout
      $wall.css({
        'grid-template-columns': 'repeat(' + actualColumns + ', 1fr)'
      });
    });
  }
  // Function to add or remove tiles as needed
  function adjustTiles($wall, currentCount, requiredCount) {
    if (currentCount === requiredCount) {
      // No adjustment needed
      return;
    }

    // All available image URLs
    const allImageUrls = $wall.data('all-images') || [];
    if (allImageUrls.length === 0) {
      return;
    }

    // Get wall options
    const wallOptions = $wall.data('options') || {};
    if (currentCount > requiredCount) {
      // Need to remove excess tiles
      const $tiles = $wall.find('.wall-tile');

      $tiles.slice(requiredCount).remove();

      // Update hasEnoughImages flag
      wallOptions.hasEnoughImages = allImageUrls.length >= requiredCount;
    } else {
      // Need to add more tiles
      const tilesToAdd = requiredCount - currentCount;
      const $container = $wall;

      // Check if we have enough unique images
      const uniqueImagesNeeded = Math.max(requiredCount, allImageUrls.length);

      // If we don't have enough unique images, clone some existing ones
      if (allImageUrls.length < uniqueImagesNeeded && allImageUrls.length > 0) {
        // Clone images until we have at least the required number
        const originalLength = allImageUrls.length;
        for (let i = 0; i < uniqueImagesNeeded - originalLength; i++) {
          // Cycle through original images
          allImageUrls.push(allImageUrls[i % originalLength]);
        }

        // Update the stored images
        $wall.data('all-images', allImageUrls);
      }
      for (let i = 0; i < tilesToAdd; i++) {
        // Create a new tile with a random image
        const randomImgSrc = allImageUrls[Math.floor(Math.random() * allImageUrls.length)];

        // Create tile with proper styling
        const $newTile = $('<div class="wall-tile" style="position: relative; overflow: hidden;"></div>');
        const $newImg = $('<img src="' + randomImgSrc + '" style="width: 100%; height: 100%; object-fit: cover;">');

        $newTile.append($newImg);
        $container.append($newTile);
      }

      // Update hasEnoughImages flag
      wallOptions.hasEnoughImages = allImageUrls.length >= requiredCount;
    }
  } // Hilfsfunktion zur Neuinitialisierung der Wand bei Bildschirmgrößenänderungen
  function reinitializeWall() {
    // Alle Animationen pausieren
    $('.wp-animated-live-wall').each(function () {
      var $wall = $(this);
      var wallOptions = $wall.data('options') || {};

      // Animation pausieren
      wallOptions.animating = true;

      // Speichere die aktuelle Konfiguration für einen Vergleich nach der Anpassung
      wallOptions.previousColumns = wallOptions.currentColumns || wallOptions.columns;
      wallOptions.previousRows = wallOptions.currentRows || wallOptions.rows;
    });

    // Grid anpassen
    adjustGrid();

    // Nach einer kurzen Pause die Animation wieder starten
    setTimeout(function () {
      $('.wp-animated-live-wall').each(function () {
        var $wall = $(this);
        var wallOptions = $wall.data('options') || {};

        // Prüfen, ob sich die Struktur geändert hat
        const columnsChanged = wallOptions.previousColumns !== wallOptions.currentColumns;
        const rowsChanged = wallOptions.previousRows !== wallOptions.currentRows;

        // Wenn sich die Struktur geändert hat, aktualisiere auch die Kacheln-Verwaltung
        if (columnsChanged || rowsChanged) {
          // Aktualisiere die Bilder-Distribution
          const totalTiles = wallOptions.currentColumns * wallOptions.currentRows;
          wallOptions.tilesInAnimation = Math.min(wallOptions.tilesAtOnce, totalTiles);

          // Prüfe, ob genug Bilder vorhanden sind
          const allImages = $wall.data('all-images') || [];
          wallOptions.hasEnoughImages = allImages.length >= totalTiles;
        }

        // Animation wieder starten
        wallOptions.animating = false;
        animateNextTile($wall, wallOptions);
      });
    }, 500);
  }

  // Call on page load
  $(window).on('load', function () {
    adjustGrid();
  });

  // Debounce-Funktion für Resize-Events
  var resizeTimeout;
  $(window).on('resize', function () {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function () {
      reinitializeWall();
    }, 250);
  });

  // DOM ready
  $(document).ready(function () {
    init();
  });
})(jQuery);
