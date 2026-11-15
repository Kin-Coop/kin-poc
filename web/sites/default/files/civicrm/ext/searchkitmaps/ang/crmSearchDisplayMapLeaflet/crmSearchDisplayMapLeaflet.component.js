(function(angular, $, _, L) {
  "use strict";

  angular.module('crmSearchDisplayMapLeaflet').component('crmSearchDisplayMapLeaflet', {
    bindings: {
      apiEntity: '@',
      search: '<',
      display: '<',
      apiParams: '<',
      settings: '<',
      filters: '<',
      totalCount: '=?'
    },
    require: {
      afFieldset: '?^^afFieldset'
    },
    templateUrl: '~/crmSearchDisplayMapLeaflet/crmSearchDisplayMapLeaflet.html',
    controller: function($scope, $element, searchDisplayBaseTrait) {
      const ts = $scope.ts = CRM.ts('org.civicrm.search_kit');

      // Mix in required traits
      // TODO: Use CRM.utils.cloneDeep() when bumping core req to 6.20
      angular.extend(this, _.cloneDeep(searchDisplayBaseTrait));

      let map = null;

      let isRendered = false;

      let waitingToRun = null;

      this.$onInit = () => {
        this.initializeDisplay($scope, $element);

        async function loadMap() {
          const displayElement = $element[0];
          renderMap(displayElement.getElementsByClassName('crm-search-kit-map')[0]);
        }
        loadMap();
      };

      const renderMap = (mapElement) => {
        if (this.settings.format.max_width === undefined) {
          this.settings.format.max_width = 768;
        }
        if (this.settings.format.max_width > 0) {
          mapElement.style.maxWidth = this.settings.format.max_width + 'px';
        }
        mapElement.style.height = this.settings.format.height + 'px';

        if (CRM.searchkitmaps.map) {
          map = CRM.searchkitmaps.map;
          map.remove();
        }
        //else {
          map = L.map(mapElement, {
            renderer: L.canvas()
          });
          CRM.searchkitmaps.map = map;
     //   }

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
          referrerPolicy: 'strict-origin-when-cross-origin'
        }).addTo(map);
      };

      // Create new layerGroup for the markers
      let markersLayerGroup = L.layerGroup();

      this.onPostRun.push(() => {
        if (!map) {
          return;
        }
        if (map.hasLayer(markersLayerGroup)) {
          markersLayerGroup.clearLayers();
        }

        if (!this.results) {
          return;
        }

        if (this.settings.cluster) {
          markersLayerGroup = L.markerClusterGroup({showCoverageOnHover: false});
        }

        const markerData = this.results.map((record) => {
          return {
            // @fixme: Make marker position adjustment optional - by setting, or only if identical lon/lat? eg. with +-(Math.random()/1000)
            'lat': record.data[this.settings.columns[0].key],
            'lon': record.data[this.settings.columns[1].key],
            'popupHtml': renderPopupHtml(record),
            'options': {
              'id': record.data.id,
              'riseOnHover': true,
            },
            'customMarkerColor': (record.columns[3] === undefined) ? 'blue' : record.columns[3].val.toString(),
            'customMarkerIcon': (record.columns[4] === undefined) ? '' : record.columns[4].val.toString(),
          };
        });

        function renderPopupHtml(record) {
          const colData = record.columns[2];
          let html = colData.val.toString();
          if (colData.icons) {
            if (colData.icons.left) {
              html = '<i class="crm-i ' + colData.icons.left[0] + '" role="img" aria-hidden="true"></i> ' + html;
            }
            else if (colData.icons.right) {
              html += ' <i class="crm-i ' + colData.icons.right[0] + '" role="img" aria-hidden="true"></i>';
            }
          }
          return html;
        }

        const latlngs = this.results.map((record) => {
          return L.latLng(record.data[this.settings.columns[0].key], record.data[this.settings.columns[1].key]);
        });

        L.Icon.Default.imagePath = CRM.crmSearchDisplayMapLeaflet.basePath + '/images/';

        for (let i = 0; i < markerData.length; i++) {
          if ((markerData[i].lat === null) || (markerData[i].lon === null)) {
            continue;
          }

          if (this.settings.markers !== undefined && this.settings.markers.custom) {
            // Creates a custom marker using the configuration from the searchdisplay
            markerData[i].options.icon = L.ExtraMarkers.icon({
              // ExtraMarkers requires a truthy icon property to render inner SVG when svg: true
              icon: markerData[i].customMarkerIcon || ' ',
              markerColor: markerData[i].customMarkerColor,
              shape: this.settings.markers.shape,
              prefix: 'fa',
              svg: true
            });
          }

          const marker = new L.marker([markerData[i].lat,markerData[i].lon], markerData[i].options)
            .bindPopup(markerData[i].popupHtml);
          markersLayerGroup.addLayer(marker);
        }
        map.addLayer(markersLayerGroup);
        if (CRM.searchkitmaps.boundsFromMap) {
          // Result update was triggered by the user panning/zooming the map;
          // leave the view alone instead of fitting to the new results.
          CRM.searchkitmaps.boundsFromMap = false;
        } else {
          try { map.fitBounds(latlngs); } catch (err) { console.log('Invalid Lat/Lon coordinates: check your search data'); }
        }

        CRM.searchkitmaps.markers = markersLayerGroup;
        CRM.searchkitmaps.map = map;

      });
    }
   });
})(angular, CRM.$, CRM._, CRM.searchkitmaps.leaflet);
