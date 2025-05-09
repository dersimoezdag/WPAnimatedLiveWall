jQuery(document).ready(function ($) {
  // Initialisiere jede Animated Live Wall auf der Seite
  $('.wp-animated-live-wall').each(function () {
    const wall = $(this);

    // CSS-Variablen für das Grid aus Daten-Attributen setzen
    const rows = parseInt(wall.data('rows')) || 3;
    const columns = parseInt(wall.data('columns')) || 4;
    const animationSpeed = parseInt(wall.data('animation-speed')) || 5000;
    const transitionSpeed = parseInt(wall.data('transition')) || 400;
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

    // Wenn wir keine versteckten Bilder haben oder keine sichtbaren Kacheln,
    // gibt es nichts zu tun
    if (hiddenContainer.length === 0 || visibleTiles.length === 0) return;

    const hiddenImages = hiddenContainer.find('div');
    if (hiddenImages.length === 0) return;

    let rotationTimer;

    /**
     * Führt die Bildrotation zwischen sichtbaren und versteckten Bildern durch
     */
    function rotateImages() {
      // Eine zufällige sichtbare Kachel auswählen
      const randomTileIndex = Math.floor(Math.random() * visibleTiles.length);
      const visibleTile = visibleTiles.eq(randomTileIndex);
      const visibleImg = visibleTile.find('img');

      // Ein zufälliges verstecktes Bild auswählen
      const randomHiddenIndex = Math.floor(Math.random() * hiddenImages.length);
      const hiddenDiv = hiddenImages.eq(randomHiddenIndex);
      const hiddenImg = hiddenDiv.find('img');

      // Wenn eines der Bilder nicht vorhanden ist, abbrechen
      if (!visibleImg.length || !hiddenImg.length) return;

      // Attribute für den Bildtausch speichern
      const swapAttributes = ['src', 'srcset', 'sizes', 'alt'];
      const visibleAttrs = {};
      const hiddenAttrs = {};

      // Aktuelle Attribute speichern
      swapAttributes.forEach(attr => {
        visibleAttrs[attr] = visibleImg.attr(attr);
        hiddenAttrs[attr] = hiddenImg.attr(attr);
      });

      // Zusätzlich die ID-Attribute speichern
      const visibleId = visibleImg.attr('data-id');
      const hiddenId = hiddenDiv.attr('data-id');

      // Bilder mit Animations-Effekt austauschen
      visibleImg.fadeOut(transitionSpeed, function () {
        // Attribute des sichtbaren Bildes aktualisieren
        swapAttributes.forEach(attr => {
          visibleImg.attr(attr, hiddenAttrs[attr]);
        });
        visibleImg.attr('data-id', hiddenId);

        // Attribute des versteckten Bildes aktualisieren
        swapAttributes.forEach(attr => {
          hiddenImg.attr(attr, visibleAttrs[attr]);
        });
        hiddenDiv.attr('data-id', visibleId);

        // Bild wieder einblenden
        visibleImg.fadeIn(transitionSpeed);
      });

      // Nächsten Bildwechsel planen
      rotationTimer = setTimeout(rotateImages, animationSpeed);
    }

    // Animation erst nach einer kurzen Verzögerung starten
    rotationTimer = setTimeout(rotateImages, animationSpeed);

    // Animations-Pausierung bei Sichtwechsel des Browsertabs
    $(document).on('visibilitychange', function () {
      if (document.hidden) {
        // Animation pausieren wenn Tab nicht sichtbar
        clearTimeout(rotationTimer);
      } else {
        // Animation fortsetzen wenn Tab wieder sichtbar
        rotationTimer = setTimeout(rotateImages, 1000);
      }
    });
  });
});
