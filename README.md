# kin-poc

Website proof-of-concept based on CiviCRM and Drupal.

# Development install
These instructions are for using DDEV
- Git clone this project
- $ ddev config
  - docroot location = web
  - project type = drupal10
- $ ddev start
- $ ddev composer install
- download the two databases: kin_civi and kin_drup
- import databases:
  - $ ddev import-db --file=../kin_civi.sql.gz --database=kin_civi
  - $ ddev import-db --file=../kin_drup.sql.gz --database=kin_drup
- Configure settings files for civi and Drupal based on the example files in web/sites/default
