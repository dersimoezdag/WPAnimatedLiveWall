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
      // Rotate - Dreht und blendet das neue Bild ein
      $(nextImage)
        .css({
          opacity: 0,
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          objectFit: 'cover',
          transform: 'rotate(-10deg) scale(0.8)'
        })
        .appendTo(tile);

      $(nextImage).animate(
        {
          opacity: 1,
          transform: 'rotate(0deg) scale(1)'
        },
        options.transition,
        function () {
          tile.find('img:first').remove();
        }
      );
    },
    blurfade: function (tile, nextImage) {
      // Blur Fade - Weichzeichnen-Effekt beim Überblenden
      // Erfordert, dass das aktuelle Bild verwischt wird und das neue scharf erscheint
      tile
        .find('img:first')
        .css({
          filter: 'blur(0px)'
        })
        .animate(
          {
            opacity: 0,
            filter: 'blur(5px)'
          },
          options.transition
        );

      $(nextImage)
        .css({
          opacity: 0,
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          objectFit: 'cover',
          filter: 'blur(5px)'
        })
        .appendTo(tile);

      $(nextImage).animate(
        {
          opacity: 1,
          filter: 'blur(0px)'
        },
        options.transition,
        function () {
          tile.find('img:first').remove();
        }
      );
    },
    flip: function (tile, nextImage) {
      // 3D Flip - Dreht das Element wie eine Karte um
      // Erstelle einen Container für den 3D-Effekt
      tile.css({
        perspective: '1000px'
      });

      const currentImg = tile.find('img:first');

      $(nextImage)
        .css({
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          objectFit: 'cover',
          transform: 'rotateY(-180deg)',
          backfaceVisibility: 'hidden'
        })
        .appendTo(tile);

      currentImg.css({
        backfaceVisibility: 'hidden'
      });

      // Animiere den Flip
      currentImg.animate(
        {
          transform: 'rotateY(180deg)'
        },
        options.transition
      );

      $(nextImage).animate(
        {
          transform: 'rotateY(0deg)'
        },
        options.transition,
        function () {
          currentImg.remove();
          // Reset perspective
          tile.css({
            perspective: 'none'
          });
        }
      );
    }
  };

  // Optionen für die Animation
  var options = {
    animationSpeed: 5000, // Zeit zwischen den Bildwechseln
    transition: 400, // Dauer der Überblendung
    availableEffects: ['crossfade'], // Standard-Effekt
    randomize: true // Zufälliger Effekt aus den verfügbaren
  };
  function init() {
    $('.wp-animated-live-wall').each(function () {
      var $wall = $(this);

      // Optionen aus den data-Attributen lesen
      var wallOptions = {
        rows: parseInt($wall.data('rows'), 10) || 3,
        columns: parseInt($wall.data('columns'), 10) || 4,
        animationSpeed: parseInt($wall.data('animation-speed'), 10) || options.animationSpeed,
        transition: parseInt($wall.data('transition'), 10) || options.transition,
        animating: false // Flag zur Kontrolle, dass nur eine Kachel gleichzeitig animiert wird
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

      console.log('Verfügbare Effekte für Wand:', wallOptions.availableEffects);

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
      console.log('Bildliste initialisiert mit ' + totalImages + ' Bildern für ' + totalCells + ' Zellen. Genug Bilder: ' + wallOptions.hasEnoughImages);
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
      console.log('Fallback-Bildliste mit ' + visibleImages.length + ' sichtbaren Bildern für ' + totalCells + ' Zellen. Genug Bilder: ' + wallOptions.hasEnoughImages);
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

    // Wähle eine zufällige Kachel aus
    var randomTileIndex = Math.floor(Math.random() * tiles.length);
    var randomTile = tiles.eq(randomTileIndex);

    // Markiere die Wand als "animierend"
    wallOptions.animating = true;

    // Führe die Animation für diese Kachel aus
    animateSingleTile(randomTile, wall, wallOptions);
  }
  function animateSingleTile(tile, wall, wallOptions) {
    // Sammle alle aktuell sichtbaren Bilder und ihre Quellen
    var allVisibleSources = [];
    wall.find('.wall-tile img').each(function () {
      allVisibleSources.push($(this).attr('src'));
    }); // Hole die komplette Liste aller Bilder aus dem data-all-images Attribut der Wand
    // Dieses Attribut sollte alle Bilder enthalten, die zur Wand gehören, nicht nur die aktuell sichtbaren
    var allAvailableImages = wall.data('all-images');

    // Wenn die Bildliste nicht vorhanden ist oder leer ist, initialisieren wir sie
    if (!allAvailableImages || allAvailableImages.length === 0) {
      allAvailableImages = [];

      // 1. Versuche zuerst, die Bilder aus dem data-all-image-urls Attribut zu holen (falls vorhanden)
      var imageUrlsFromData = wall.data('all-image-urls');
      if (imageUrlsFromData && Array.isArray(imageUrlsFromData) && imageUrlsFromData.length > 0) {
        allAvailableImages = imageUrlsFromData;
      }
      // 2. Wenn keine URL-Liste gefunden wurde, sammle zumindest die aktuell sichtbaren Bilder
      else {
        wall.find('.wall-tile img').each(function () {
          var src = $(this).attr('src');
          // Vermeide Duplikate
          if (allAvailableImages.indexOf(src) === -1) {
            allAvailableImages.push(src);
          }
        });
      }

      // Speichere die komplette Bildliste in der Wand für zukünftige Verwendung
      wall.data('all-images', allAvailableImages);
      console.log('Initialisierte Bildliste mit ' + allAvailableImages.length + ' Bildern für die Rotation');
    } // Debug-Ausgabe: Aktuell verfügbare Bilder und sichtbare Quellen vergleichen
    console.log('All available images:', allAvailableImages.length, 'Visible sources:', allVisibleSources.length); // Überprüfe, ob wir genügend Bilder haben (mehr als die Anzahl der Kacheln)
    var hasEnoughImages = wallOptions.hasEnoughImages || false;

    // Strategie anpassen basierend auf der Bildanzahl
    var unusedImages = [];
    var currentImg = tile.find('img').attr('src');

    if (hasEnoughImages) {
      // Wenn wir genug Bilder haben: Zeige keine doppelten Bilder
      unusedImages = allAvailableImages.filter(function (src) {
        // Wähle nur Bilder, die noch nicht sichtbar sind
        return allVisibleSources.indexOf(src) === -1;
      });
      console.log('Genug Bilder vorhanden. Nur ungenutzte Bilder verwenden:', unusedImages.length);
    } else {
      // Wenn wir zu wenig Bilder haben: Erlaube Duplikate, aber vermeide das aktuelle Bild
      unusedImages = allAvailableImages.filter(function (src) {
        // Verwende alle Bilder außer dem aktuell angezeigten in dieser Kachel
        return src !== currentImg;
      });
      console.log('Zu wenig Bilder. Nutze andere Bilder, auch wenn sie bereits sichtbar sind:', unusedImages.length);
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

    // Führe den ausgewählten Effekt aus
    console.log('Anwendung des Effekts:', effect, 'auf Kachel', tile.index());
    // Warte bis die Animation abgeschlossen ist, um das Flag zurückzusetzen
    var effectDuration = wallOptions.transition;

    // Führe den Effekt aus
    effectHandlers[effect](tile, nextImage);

    // Nach Abschluss der Animation
    setTimeout(function () {
      // Setze das Animationsflag zurück
      wallOptions.animating = false;

      // Berechne die Zeit bis zur nächsten Animation
      // Mindestzeit zwischen Animationen: 2000ms oder animationSpeed, je nachdem was größer ist
      var nextDelay = Math.max(2000, wallOptions.animationSpeed);

      // Plane die nächste Animation
      setTimeout(function () {
        animateNextTile(wall, wallOptions);
      }, nextDelay);
    }, effectDuration + 100); // Extra Zeit für sicheres Beenden der Animation
  }

  // DOM ready
  $(document).ready(function () {
    init();
  });
})(jQuery);
