jQuery(document).ready(function ($) {
  // Initialisiere jede Animated Live Wall auf der Seite
  $('.wp-animated-live-wall').each(function () {
    const wall = $(this);
    const rows = parseInt(wall.data('rows')) || 3;
    const columns = parseInt(wall.data('columns')) || 4;

    // Animationsgeschwindigkeit aus Datenattribut oder Standardwert nehmen
    const animationSpeed = parseInt(wall.data('animation-speed')) || 5000;

    // Setze CSS-Variablen für das Grid-Layout
    wall.css({
      'grid-template-columns': `repeat(${columns}, 1fr)`,
      'grid-template-rows': `repeat(${rows}, 1fr)`
    });

    // Suche alle sichtbaren Kacheln
    const visibleTiles = wall.find('.wall-tile');

    // Suche versteckte Bilder im versteckten Container
    const hiddenImages = wall.find('.wpalw-hidden-images > div');

    // Wenn keine versteckten Bilder vorhanden sind, keine Rotation notwendig
    if (hiddenImages.length === 0) {
      return;
    }

    // Rotation-Timeout-ID für späteren Zugriff speichern
    let rotationTimeout;

    // Funktion für die Bildrotation definieren
    function rotateImage() {
      // Wähle eine zufällige sichtbare Kachel
      const randomTileIndex = Math.floor(Math.random() * visibleTiles.length);
      const randomTile = $(visibleTiles[randomTileIndex]);
      const visibleImg = randomTile.find('img');

      // Wähle ein zufälliges verstecktes Bild
      const randomHiddenIndex = Math.floor(Math.random() * hiddenImages.length);
      const randomHiddenDiv = $(hiddenImages[randomHiddenIndex]);
      const randomHiddenImg = randomHiddenDiv.find('img');

      // Hole Attribute vom versteckten Bild
      const hiddenSrc = randomHiddenImg.attr('src');
      const hiddenSrcset = randomHiddenImg.attr('srcset');
      const hiddenSizes = randomHiddenImg.attr('sizes');
      const hiddenId = randomHiddenDiv.data('id');

      // Hole Attribute vom sichtbaren Bild
      const visibleSrc = visibleImg.attr('src');
      const visibleSrcset = visibleImg.attr('srcset');
      const visibleSizes = visibleImg.attr('sizes');
      const visibleId = visibleImg.data('id');

      // Animiere den Bildwechsel
      visibleImg.fadeOut(400, function () {
        // Tausche die Attribute
        visibleImg.attr({
          src: hiddenSrc,
          srcset: hiddenSrcset,
          sizes: hiddenSizes,
          'data-id': hiddenId
        });

        // Aktualisiere verstecktes Bild mit den vorherigen sichtbaren Bildattributen
        randomHiddenImg.attr({
          src: visibleSrc,
          srcset: visibleSrcset,
          sizes: visibleSizes
        });
        randomHiddenDiv.attr('data-id', visibleId);

        // Blende das neue Bild ein
        visibleImg.fadeIn(400, function () {
          // Setze nächste Rotation nach Animation
          rotationTimeout = setTimeout(rotateImage, animationSpeed);
        });
      });
    }

    // Starte die erste Rotation nach Verzögerung
    rotationTimeout = setTimeout(rotateImage, animationSpeed);

    // Pausiere die Rotation, wenn das Fenster nicht sichtbar ist (Tab-Wechsel, etc.)
    $(document).on('visibilitychange', function () {
      if (document.hidden) {
        // Wenn Seite nicht sichtbar, Rotation stoppen
        clearTimeout(rotationTimeout);
      } else {
        // Wenn Seite wieder sichtbar, Rotation neu starten
        rotationTimeout = setTimeout(rotateImage, animationSpeed);
      }
    });
  });
});
