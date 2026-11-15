<?php
/**
 * Verifies css/searchkitmaps.css is exactly what Bundle::buildAssetCss() concatenates.
 *
 * That file is a static copy of the bundle, loaded by
 * ang/crmSearchDisplayMapLeaflet.ang.php because an angular module descriptor takes
 * file paths and cannot point at an assetbuilder URL. Nothing else notices when the
 * two drift: css/ is excluded from the lint jobs, and the copy is only served to
 * angular. It had already missed a vendored bump once.
 *
 * The file list is read out of Bundle.php rather than repeated here, so this cannot
 * drift from the code it checks.
 */
$src = @file_get_contents('Civi/Searchkitmaps/Bundle.php');
if ($src === FALSE) {
  fwrite(STDERR, "Civi/Searchkitmaps/Bundle.php not found - run this from the extension root.\n");
  exit(1);
}
$fn = strstr($src, 'function buildAssetCss');
if ($fn === FALSE) {
  fwrite(STDERR, "Could not find buildAssetCss() in Civi/Searchkitmaps/Bundle.php.\n"
    . "If the bundle has been restructured, update this check too.\n");
  exit(1);
}
preg_match_all("/'(css\\/[^']+)'/", $fn, $m);
$files = $m[1];
if (count($files) < 2) {
  fwrite(STDERR, sprintf("Parsed %d css file(s) out of buildAssetCss(); expected the bundle list.\n"
    . "If the bundle has been restructured, update this check too.\n", count($files)));
  exit(1);
}
foreach ($files as $file) {
  if (!is_readable($file)) {
    fwrite(STDERR, "buildAssetCss() lists $file, which does not exist.\n");
    exit(1);
  }
}
$built = implode("\n", array_map('file_get_contents', $files));
$static = @file_get_contents('css/searchkitmaps.css');

if ($built === $static) {
  printf("css/searchkitmaps.css matches the bundle (%d files, %d bytes).\n", count($files), strlen($built));
  exit(0);
}

fwrite(STDERR, sprintf(
  "css/searchkitmaps.css does not match what Bundle::buildAssetCss() builds.\n\n"
  . "  bundle:   %d bytes from %s\n"
  . "  committed: %d bytes\n\n"
  . "If a vendored css file changed, regenerate the static copy - see docs/development.md.\n"
  . "Keep it byte-identical to the bundle output; do not reformat it.\n",
  strlen($built), implode(' + ', $files), strlen((string) $static)
));
exit(1);
