## Development

### Libraries

#### leaflet

Using 1.9.4 - https://github.com/Leaflet/Leaflet

Download assets into css/images/js:
- css/leaflet.css
- images/layers.png, layers-2x.png, marker-icon.png, marker-icon-2x.png, marker-shadow.png
- js/leaflet.js, leaflet.js.map
- js/leaflet-src.js, leaflet-src.js.map
- js/leaflet-src.esm.js, leaflet-src.esm.js.map

Edit css to set image path = ../images/*.png

#### markercluster

Using 1.5.3 - https://github.com/Leaflet/Leaflet.markercluster

Download assets into css/js:
- css/MarkerCluster.css
- css/MarkerCluster.Default.css
- js/leaflet.markercluster.js, leaflet.markercluster.js.map
- js/leaflet.markercluster-src.js, leaflet.markercluster-src.js.map

No edits needed - the css references no images.

#### extra-markers

Using 1.2.2 - https://github.com/coryasilva/Leaflet.ExtraMarkers

Download assets into css/images/js:
- css/leaflet.extra-markers.min.css
- images/markers_default.png
- images/markers_default@2x.png
- images/markers_shadow.png
- images/markers_shadow@2x.png
- js/leaflet.extra-markers.js
- js/leaflet.extra-markers.min.js

Edit css to set image path = ../images/*.png

### searchkitmaps.css

Built using assetbuilder. But we have a static copy in css/searchkitmaps.css for use by angular,
because an angular module descriptor takes file paths and cannot point at an assetbuilder URL.

**Regenerate it whenever any vendored css above changes**, or the two diverge silently - the static
copy is what angular serves. It is the concatenation `Civi\Searchkitmaps\Bundle::buildAssetCss()`
performs, in that order, joined with a newline:

    php -r '$f = ["css/leaflet.css", "css/MarkerCluster.css", "css/MarkerCluster.Default.css", "css/leaflet.extra-markers.min.css"];
      file_put_contents("css/searchkitmaps.css", implode("\n", array_map("file_get_contents", $f)));'

Keep it byte-identical to that output - do not reformat it - so it can be checked by regenerating
and diffing.

CI enforces this: the `css-bundle` job runs `tools/check-css-bundle.php`, which rebuilds the
concatenation and compares. It reads the file list out of `buildAssetCss()` rather than repeating
it, so the check follows the code.
