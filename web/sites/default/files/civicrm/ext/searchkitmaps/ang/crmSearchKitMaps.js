(function() {
  "use strict";

  function filterByBounds() {
    let elLatRange, elLonRange, elLatMin, elLatMax, elLonMin, elLonMax;
    try {
      elLatRange = document.getElementsByName('geo_code_1')[0];
      elLonRange = document.getElementsByName('geo_code_2')[0];
      elLatMin = elLatRange.getElementsByTagName('input')[0];
      elLatMax = elLatRange.getElementsByTagName('input')[1];
      elLonMin = elLonRange.getElementsByTagName('input')[0];
      elLonMax = elLonRange.getElementsByTagName('input')[1];
    }
    catch (err) {
      console.log('Could not find valid lat/lng range filter elements');
      return;
    }

    let bounds = CRM.searchkitmaps.map.getBounds();
    elLatMin.value = bounds.getSouth().toFixed(3);
    elLatMax.value = bounds.getNorth().toFixed(3);
    elLonMin.value = bounds.getWest().toFixed(3);
    elLonMax.value = bounds.getEast().toFixed(3);
    // Signal that the next results update was triggered by the map itself,
    // so the map display should not auto-fit and override the user's pan/zoom.
    CRM.searchkitmaps.boundsFromMap = true;
    elLatMin.dispatchEvent(new Event('change'));
    elLatMax.dispatchEvent(new Event('change'));
    elLonMin.dispatchEvent(new Event('change'));
    elLonMax.dispatchEvent(new Event('change'));
    elLatRange.style.display = 'none';
    elLonRange.style.display = 'none';
  }

  function highlightOnMap(id) {
    id = parseInt(id);
    if (isNaN(id)) return;

    const markers = CRM.searchkitmaps.markers;
    markers.eachLayer(function (layer) {
      if (layer.options.id !== id) return;

      if (typeof markers.zoomToShowLayer === 'function') {
        // Clustered: zoom/spiderfy until the marker is visible, then open popup
        markers.zoomToShowLayer(layer, function () {
          layer.openPopup();
        });
      } else {
        // Plain layerGroup (clustering off)
        CRM.searchkitmaps.map.panTo(layer.getLatLng());
        layer.openPopup();
      }
    });
  }

  function bindClickToHighlightOnMap(item) {
    item.addEventListener('click', function() {
      CRM.searchkitmaps.tableClicked = true;
      setTimeout(function() {
          CRM.searchkitmaps.tableClicked = false;
        },
        1000
      );
      CRM.searchkitmaps.highlightOnMap(this.dataset.entityId);
    });
  }

  function addSearchDisplayTableClickToHighlightOnMap(displayName) {
    document.querySelector("crm-search-display-table[display-name='" + displayName + "']").querySelectorAll('tr.ng-scope').forEach(bindClickToHighlightOnMap);
  }

  // Requires CiviCRM 6.16+ (data-entity-id + crm-search-display-griditem class on grid items).
  function addSearchDisplayGridClickToHighlightOnMap(displayName) {
    document.querySelector("crm-search-display-grid[display-name='" + displayName + "']").querySelectorAll('div.crm-search-display-griditem').forEach(bindClickToHighlightOnMap);
  }

  // Requires CiviCRM 6.16+, which dispatches a "load" event on a searchDisplay element
  // every time it (re-)renders its results after a filter/sort/page change. Use this instead
  // of polling (eg. document ajaxSuccess + setTimeout) to rebind click handlers to whichever
  // rows/grid-items are currently on screen. `tagName` is the searchDisplay element, eg.
  // "crm-search-display-table" or "crm-search-display-grid".
  function addSearchDisplayLoadListener(tagName, displayName, onLoad) {
    const el = document.querySelector(tagName + "[display-name='" + displayName + "']");
    if (!el) {
      console.log("searchkitmaps: could not find " + tagName + " with display-name '" + displayName + "'");
      return;
    }
    el.addEventListener('load', function() {
      // Core dispatches "load" from within the Angular digest, before ng-repeat has
      // replaced the display's placeholder rows with the real results. Defer to the
      // next task so `onLoad` sees the rendered rows rather than the placeholders.
      setTimeout(() => onLoad(displayName), 0);
    });
  }

  CRM.searchkitmaps.filterByBounds = filterByBounds;
  CRM.searchkitmaps.highlightOnMap = highlightOnMap;
  CRM.searchkitmaps.addSearchDisplayTableClickToHighlightOnMap = addSearchDisplayTableClickToHighlightOnMap;
  CRM.searchkitmaps.addSearchDisplayGridClickToHighlightOnMap = addSearchDisplayGridClickToHighlightOnMap;
  CRM.searchkitmaps.addSearchDisplayLoadListener = addSearchDisplayLoadListener;
})();
