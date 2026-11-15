## Information

Releases use the following numbering system:
**{major}.{minor}.{incremental}**

* major: Major refactoring or rewrite - make sure you read and test very carefully!
* minor: Breaking change in some circumstances, or a new feature. Read carefully and make sure you understand the impact of the change.
* incremental: A "safe" change / improvement. Should *always* be safe to upgrade.

* **[BC]**: Items marked with [BC] indicate a breaking change that will require updates to your code if you are using that code in your extension.

## Release 1.5.2 (2026-09-22)

* [!23](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/23) Fix blank map. An incorrect `this` binding introduced in 1.5.0 threw before the map was given a view, so the display rendered as an empty grey box with neither tiles nor markers.
* [!24](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/24) Fix click-to-highlight never working. `addSearchDisplayLoadListener()` bound its handlers to the display's placeholder rows, because core dispatches the `load` event before the results have rendered.

## Release 1.5.1 (2026-09-16)

* [!17](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/17) Set `referrerPolicy` on the OpenStreetMap tile layer.
* [!18](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/18) [#7](https://lab.civicrm.org/extensions/searchkitmaps/-/issues/7) Fix map fields in SearchKit by passing the saved search to `searchMeta` lookups.
* [!19](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/19) Tidy up JS: remove lo-dash, use arrow functions and `const`.
* [!20](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/20) Update Leaflet.markercluster 1.4.1 to 1.5.3, whose css was already 1.5.3, and regenerate `css/searchkitmaps.css` so it matches what `Bundle::buildAssetCss()` builds.

## Release 1.5.0 (2026-08-21)

* [#5](https://lab.civicrm.org/extensions/searchkitmaps/-/issues/5) Add `addSearchDisplayGridClickToHighlightOnMap()` for click-to-highlight on Grid displays (Table was already supported), and `addSearchDisplayLoadListener()` to (re-)bind click handlers using the searchDisplay `load` event instead of polling. **[BC]** Requires CiviCRM 6.16+.
* [!13](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/13) Fix custom marker rendering for hex color codes.

## Release 1.4.2 (2026-05-07)

* [!10](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/10) highlightOnMap: better handling of panTo/zoom if clustering for when clustering is enabled.
* [!11](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/11) Avoid a feedback loop between a map and a list view.

## Release 1.4.1 (2026-04-13)

* Fix javascript errors for existing search displays.

## Release 1.4.0 (2026-04-06)

* Add demo form.
* Support custom marker colours/icons via extra-markers library.
* Support filtering table and map.
* Fix re-rendering in preview dialog.
* Support setting height and maxWidth.

## Release 1.3.2 (2026-02-10)

* Fix popups if icon not enabled.
* Fix admin preview and clean up display of fields in admin.

## Release 1.3.1 (2026-02-06)

* [#3](https://lab.civicrm.org/extensions/searchkitmaps/-/issues/3) Fix anonymous display of maps.

## Release 1.3.0 (2025-10-05)

* [!6](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/6) - Cleanup angular module declarations.
* [!7](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/7) - dev/core#5041 - Add role + aria-hidden to FontAwesome icons (Improves accessibility for assistive technology).

## Release 1.2.2 (2025-07-03)

*Minor adjustments for future compatibility*

* [!4](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/4) - Stop relying on core to supply dataType.
* [!5](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/5) - Remove unnecessary code.

## Releaes 1.2.1 (2025-03-17)

* Fix markercluster undefined error.
* Log console warning if lat/lng data is invalid.
* Don't expose filesystem path in js/css bundle.

## Release 1.2 (2025-02-13)

* [!3](https://lab.civicrm.org/extensions/searchkitmaps/-/merge_requests/3) - Split Admin and display into own angular modules
* Civix update.

## Release 1.1 (2024-07-26)

* Add "Cluster" support for map pins.
* Fix map display for anonymous users.

## Release 1.0 (2024-03-02)

Initial release.
