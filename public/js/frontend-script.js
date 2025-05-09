jQuery(document).ready(function ($) {
  // Initialisiere jede Animated Live Wall auf der Seite
  $('.wp-animated-live-wall').each(function () {
    const wall = $(this);

    // CSS-Variablen für das Grid aus Daten-Attributen setzen
    const rows = parseInt(wall.data('rows')) || 3;
    const columns = parseInt(wall.data('columns')) || 4;
    const animationSpeed = parseInt(wall.data('animation-speed')) || 5000;
    const transitionSpeed = parseInt(wall.data('transition')) || 400; // Standard 400ms
    const gapSize = wall.data('gap') !== undefined ? parseInt(wall.data('gap')) : 4;

    // CSS-Variablen setzen für korrektes Layout
    wall.css({
      '--rows': rows,
      '--columns': columns,
      'grid-template-columns': `repeat(${columns}, 1fr)`,
      'grid-template-rows': `repeat(${rows}, 1fr)`,
      'grid-gap': `${gapSize}px`
    });

    // Elemente für die Bildrotation finden
    const visibleTiles = wall.find('.wall-tile');
    const hiddenContainer = wall.find('.wpalw-hidden-images');

    if (hiddenContainer.length === 0 || visibleTiles.length === 0) return;
    const hiddenImageDivs = hiddenContainer.find('div[data-id]'); // Sicherstellen, dass wir die divs mit data-id bekommen
    if (hiddenImageDivs.length === 0) return;

    let rotationTimer;

    // Hilfsfunktion zum Aktualisieren der Bildattribute
    function updateMainImageAndSwapHidden(mainImgElement, newImgAttrs, newImgId, hiddenDivToUpdate, hiddenImgElementInsideDiv, oldMainImgAttrs, oldMainImgId) {
      const swapAttributes = ['src', 'srcset', 'sizes', 'alt'];

      // Update the main image element that is visible on the wall
      swapAttributes.forEach(attr => {
        mainImgElement.attr(attr, newImgAttrs[attr]);
      });
      mainImgElement.attr('data-id', newImgId);

      // Update the image in the hidden container (which now stores the old main image)
      if (hiddenDivToUpdate && hiddenImgElementInsideDiv) {
        swapAttributes.forEach(attr => {
          hiddenImgElementInsideDiv.attr(attr, oldMainImgAttrs[attr]);
        });
        hiddenDivToUpdate.attr('data-id', oldMainImgId);
      }
    }

    function getRandomTransitionEffect(params) {
      const { transitionSpeed, visibleImg, hiddenImg, visibleAttrs, hiddenAttrs, visibleId, hiddenId, hiddenDiv, wallTile, callback } = params;

      const effects = [
        // Verbesserter Crossfade-Effekt
        function () {
          const tempNewImage = $('<img>')
            .attr({
              src: hiddenAttrs.src,
              srcset: hiddenAttrs.srcset,
              sizes: hiddenAttrs.sizes,
              alt: hiddenAttrs.alt
              // class: 'wpalw-transitioning-img' // Optional für spezifisches CSS
            })
            .css({
              position: 'absolute',
              top: 0,
              left: 0,
              width: '100%',
              height: '100%',
              objectFit: 'cover',
              opacity: 0,
              zIndex: 2 // Stellt sicher, dass es über dem alten Bild liegt
            });

          wallTile.append(tempNewImage);

          // requestAnimationFrame stellt sicher, dass das Bild gerendert ist, bevor die Transition beginnt
          requestAnimationFrame(() => {
            visibleImg.css({
              transition: `opacity ${transitionSpeed / 1000}s ease-in-out`,
              opacity: 0
            });
            tempNewImage.css({
              transition: `opacity ${transitionSpeed / 1000}s ease-in-out`,
              opacity: 1
            });
          });

          setTimeout(() => {
            updateMainImageAndSwapHidden(visibleImg, hiddenAttrs, hiddenId, hiddenDiv, hiddenImg, visibleAttrs, visibleId);

            visibleImg.css({
              opacity: 1,
              transition: 'none' // Entfernt die spezifische Transition, um Konflikte zu vermeiden
            });

            tempNewImage.remove();
            if (callback) callback();
          }, transitionSpeed + 50); // Kleiner Puffer
        },
        // Hier können andere Effekte aus früheren Implementierungen stehen
        // Beispiel: Zoom-Fade (angepasst an die neue Struktur)
        function () {
          visibleImg.css({
            transition: `transform ${transitionSpeed / 1000}s ease-in-out, opacity ${transitionSpeed / 1000}s ease-in-out`,
            transform: 'scale(1.1)',
            opacity: '0'
          });

          setTimeout(() => {
            updateMainImageAndSwapHidden(visibleImg, hiddenAttrs, hiddenId, hiddenDiv, hiddenImg, visibleAttrs, visibleId);

            visibleImg.css({
              // Start new image scaled down and invisible
              transform: 'scale(0.9)',
              opacity: '0',
              transition: 'none' // Reset transition before applying new one
            });

            requestAnimationFrame(() => {
              // Animate in
              visibleImg.css({
                transition: `transform ${transitionSpeed / 1000}s ease-out, opacity ${transitionSpeed / 1000}s ease-in-out`,
                transform: 'scale(1)',
                opacity: '1'
              });
            });

            if (callback) setTimeout(callback, transitionSpeed + 50);
          }, transitionSpeed); // Gesamtdauer des Ausblendens
        }
        // Weitere Effekte (slide, rotate, blur-fade, flip) können hier ähnlich integriert werden
        // ... (Platzhalter für andere Effekte)
      ];

      // Wählt einen zufälligen Effekt aus (oder nur den ersten, wenn nur Crossfade gewünscht ist)
      // Für diese Anfrage fokussieren wir uns auf den verbesserten Crossfade.
      // Wenn andere Effekte vorhanden sind, kann hier ein Zufallsgenerator verwendet werden:
      // return effects[Math.floor(Math.random() * effects.length)];
      return effects[0]; // Vorerst nur den verbesserten Crossfade verwenden
    }

    function rotateImages() {
      if (visibleTiles.length === 0 || hiddenImageDivs.length === 0) return;

      const randomTileIndex = Math.floor(Math.random() * visibleTiles.length);
      const visibleTile = visibleTiles.eq(randomTileIndex);
      const currentVisibleImg = visibleTile.find('img');

      const randomHiddenIndex = Math.floor(Math.random() * hiddenImageDivs.length);
      const currentHiddenDiv = hiddenImageDivs.eq(randomHiddenIndex);
      const currentHiddenImg = currentHiddenDiv.find('img');

      if (!currentVisibleImg.length || !currentHiddenImg.length) return;

      const swapAttributes = ['src', 'srcset', 'sizes', 'alt'];
      const visibleAttrs = {};
      const hiddenAttrs = {};

      swapAttributes.forEach(attr => {
        visibleAttrs[attr] = currentVisibleImg.attr(attr);
        hiddenAttrs[attr] = currentHiddenImg.attr(attr);
      });

      const visibleId = currentVisibleImg.attr('data-id');
      const hiddenId = currentHiddenDiv.attr('data-id'); // ID vom Div nehmen

      // Verhindere, dass dasselbe Bild getauscht wird (wenn nur ein verstecktes Bild vorhanden ist und es bereits angezeigt wird)
      if (visibleId === hiddenId && hiddenImageDivs.length > 1) {
        // Versuche es erneut mit einer kleinen Verzögerung, um Endlosschleifen zu vermeiden, falls alle Bilder gleich sind
        rotationTimer = setTimeout(rotateImages, 100);
        return;
      }

      const effectFn = getRandomTransitionEffect({
        transitionSpeed,
        visibleImg: currentVisibleImg,
        hiddenImg: currentHiddenImg,
        visibleAttrs,
        hiddenAttrs,
        visibleId,
        hiddenId,
        hiddenDiv: currentHiddenDiv,
        wallTile: visibleTile,
        callback: function () {
          // Reset transition on visibleImg after animation to prevent interference
          currentVisibleImg.css('transition', 'none');
          rotationTimer = setTimeout(rotateImages, animationSpeed);
        }
      });
      effectFn();
    }

    if (visibleTiles.length > 0 && hiddenImageDivs.length > 0) {
      rotationTimer = setTimeout(rotateImages, animationSpeed);
    }

    $(document).on('visibilitychange', function () {
      if (document.hidden) {
        clearTimeout(rotationTimer);
      } else {
        if (visibleTiles.length > 0 && hiddenImageDivs.length > 0) {
          rotationTimer = setTimeout(rotateImages, Math.min(1000, animationSpeed)); // Startet schneller, wenn Tab wieder aktiv wird
        }
      }
    });
  });
});
