(function(angular, $) {
  "use strict";

  angular.module('searchAdminDisplayMapLeaflet').component('searchAdminDisplayMapLeaflet', {
    bindings: {
      display: '<',
      apiEntity: '<',
      apiParams: '<'
    },
    require: {
      parent: '^crmSearchAdminDisplay'
    },
    templateUrl: '~/searchAdminDisplayMapLeaflet/searchAdminDisplayMapLeaflet.html',
    controller: function($scope, searchMeta, crmUiHelp) {
      const ts = $scope.ts = CRM.ts('org.civicrm.search_kit');

      $scope.hs = crmUiHelp({file: 'CRM/Search/Help/Display'});

      // init in $onInit
      this.allColumns = [];
      const filteredColumns = [];

      this.getDefaultSettings = () => ({
        format: {
          height: 480,
          max_width: 768,
        },
        markers: {
          custom: false,
          shape: 'circle',
        }
      });

      this.getLabelForFields = (fields) => ({
        'lat': ts('Latitude'),
        'lon': ts('Longitude'),
        'popupHtml': ts('Popup HTML'),
        'customMarkerColor': ts('Custom Marker Color'),
        'customMarkerIcon': ts('Custom Marker Icon'),
      }[fields]);

      this.getAcceptableDataTypesForFields = (fields) => ({
        'lat': ['Float'],
        'lon': ['Float'],
        'popupHtml': null,
        'customMarkerColor': null,
        'customMarkerIcon': null,
      }[fields]);

      this.getColumnOptionsForFields = (fields) => {
        if (filteredColumns[fields]) {
          return filteredColumns[fields];
        }
        const acceptableTypes = this.getAcceptableDataTypesForFields(fields);

        if (!acceptableTypes) {
          return this.allColumns;
        }
        return (filteredColumns[fields] = this.allColumns.filter(
          (col) => {
            const info = searchMeta.parseExpr(col.key, this.parent.savedSearch);
            const field = (info.args.find((a) => a.type === 'field') || {}).field || {};
            const dataType = (info.fn && info.fn.data_type) || field.data_type;
            return acceptableTypes.includes(dataType);
          }
        ));
      };

      this.initColumnForFields = (fields) => {
        // all acceptable columns
        const acceptable = this.getColumnOptionsForFields(fields);

        // dont set if already used in another column
        const alreadyUsedKeys = (this.display.settings && this.display.settings.columns) ?
          this.display.settings.columns.map((col) => col.key) :
          [];

        const available = acceptable.filter((col) => (!alreadyUsedKeys.includes(col.key)));

        // choose the first found or create empty
        const col = available.length > 0 ? available[0] : { key: '' };
        col.mapTo = fields;
        return col;
      };


      this.$onInit = () => {
        this.allColumns = this.apiParams.select.map((col) => searchMeta.fieldToColumn(col, {label: true}, this.parent.savedSearch));

        if (!this.display.settings) {
          this.display.settings = this.getDefaultSettings();
        }
        if (!this.display.settings.columns) {
          // push sequentially to avoid getting fixed as the same object
          this.display.settings.columns = [];
          this.display.settings.columns.push(this.initColumnForFields('lat'));
          this.display.settings.columns.push(this.initColumnForFields('lon'));
          this.display.settings.columns.push(this.initColumnForFields('popupHtml'));
          this.display.settings.columns.push(this.initColumnForFields('customMarkerColor'));
          this.display.settings.columns.push(this.initColumnForFields('customMarkerIcon'));
        }
      };
    }
  });

})(angular, CRM.$);
