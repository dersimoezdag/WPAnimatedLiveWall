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

    // Setze alle Bilder auf 100% Größe und object-fit cover
    tiles
      .css({
        position: 'relative',
        overflow: 'hidden'
      })
      .find('img')
      .css({
        width: '100%',
        height: '100%',
        objectFit: 'cover'
      });

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

      // Get the desired rows and columns
      var rows = parseInt(wallOptions.rows, 10) || 3;
      var columns = parseInt(wallOptions.columns, 10) || 4;

      // Configure responsive behavior based on screen width
      const breakpoints = {
        small: 350, // Small mobile screens
        medium: 576, // Tablets and larger mobile
        large: 768
      };

      // Get current screen width
      const screenWidth = window.innerWidth;

      // Adjust columns based on screen size
      let actualColumns = columns;

      if (screenWidth < breakpoints.small) {
        // On very small screens, ensure at least one column but no more than 2
        actualColumns = Math.min(2, columns);

        // Make sure the last row is fully filled
        while ((rows * actualColumns) % actualColumns !== 0) {
          actualColumns--;
          if (actualColumns < 1) actualColumns = 1;
        }
      } else if (screenWidth < breakpoints.medium) {
        // On small screens, reduce columns but ensure at least 2
        actualColumns = Math.max(2, Math.min(3, columns));

        // Make sure the last row is fully filled
        while ((rows * columns) % actualColumns !== 0) {
          actualColumns--;
          if (actualColumns < 2) break;
        }
      } else if (screenWidth < breakpoints.large) {
        // On medium screens, use slightly reduced columns if original is high
        actualColumns = columns > 4 ? columns - 1 : columns;

        // Make sure the last row is fully filled
        while ((rows * columns) % actualColumns !== 0) {
          actualColumns--;
          if (actualColumns < 2) break;
        }
      }

      // Update columns for responsive layout
      columns = actualColumns;

      // Apply grid layout
      $wall.css({
        'grid-template-columns': 'repeat(' + columns + ', 1fr)'
      });
    });
  }

  // Call on page load
  $(window).on('load', function () {
    adjustGrid();
  });

  // Call on window resize
  $(window).on('resize', function () {
    adjustGrid();
  });

  // DOM ready
  $(document).ready(function () {
    init();
  });
})(jQuery);
