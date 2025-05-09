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
        transition: parseInt($wall.data('transition'), 10) || options.transition
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

      // Initialisiere die Kacheln
      initTiles($wall);
    });
  }

  function initTiles(wall) {
    var wallOptions = wall.data('options');

    // Starte die Animation mit zufälligen Verzögerungen für jede Kachel
    wall.find('.wall-tile').each(function (index) {
      var tile = $(this);

      // Setze die Position für absolute Positionierung der Bilder
      tile.css({
        position: 'relative',
        overflow: 'hidden'
      });

      // Setze das erste Bild auf 100% Größe und object-fit cover
      tile.find('img').css({
        width: '100%',
        height: '100%',
        objectFit: 'cover'
      });

      // Zufällige Startverzögerung für jede Kachel
      var delay = Math.random() * wallOptions.animationSpeed;

      // Starte die Animation mit der berechneten Verzögerung
      setTimeout(function () {
        animateTile(tile, wallOptions);
      }, delay);
    });
  }

  function animateTile(tile, wallOptions) {
    // Periodisches Wechseln der Bilder
    setTimeout(function () {
      // Wähle zufällig ein Bild aus der versteckten Sammlung oder von anderen Kacheln
      var wall = tile.closest('.wp-animated-live-wall');
      var allImages = wall.find('.wall-tile img').toArray();

      // Aktuelles Bild ausschließen
      var currentImg = tile.find('img').attr('src');
      var otherImages = allImages.filter(function (img) {
        return $(img).attr('src') !== currentImg;
      });

      if (otherImages.length === 0) {
        return; // Keine anderen Bilder verfügbar
      }

      // Zufälliges Bild auswählen
      var randomImg = otherImages[Math.floor(Math.random() * otherImages.length)];
      var nextImage = document.createElement('img');
      nextImage.src = $(randomImg).attr('src');

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
      console.log('Anwendung des Effekts:', effect);
      effectHandlers[effect](tile, nextImage);

      // Starte die nächste Animation
      animateTile(tile, wallOptions);
    }, wallOptions.animationSpeed);
  }

  // DOM ready
  $(document).ready(function () {
    init();
  });
})(jQuery);
