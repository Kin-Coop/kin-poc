#!/bin/bash
set -e

# === Configuration ===
REMOTE_SSH="kin@webarch8.co.uk"
REMOTE_PATH="/home/kin"    # adjust to your live web root or Drupal root
TMP_DIR="/tmp"
DATE=$(date +"%Y%m%d-%H%M")

DRUPAL_DB="kin_drup"
CIVI_DB="kin_civi"

# === Paths on local (DDEV) ===
#PROJECT_NAME=$(ddev describe --json-output | jq -r '.raw.name')
PROJECT_NAME=$kin

echo "Starting database sync from live to local ($PROJECT_NAME)..."

# === 1️⃣ Dump the live Drupal DB ===
echo "Dumping Drupal database..."
ssh "$REMOTE_SSH" "mysqldump --single-transaction --quick --lock-tables=false $DRUPAL_DB | gzip -c" > "drupal-$DATE.sql.gz"

echo "Dumping CiviCRM database..."
ssh "$REMOTE_SSH" "mysqldump --single-transaction --quick --lock-tables=false $CIVI_DB | gzip -c" > "civi-$DATE.sql.gz"

echo "Importing Drupal database into DDEV..."
ddev import-db --database=$DRUPAL_DB --file="drupal-$DATE.sql.gz"

echo "Importing Civi database into DDEV..."
ddev import-db --database=$CIVI_DB --file="civi-$DATE.sql.gz"

# === 4️⃣ Clean up ===
rm "drupal-$DATE.sql.gz"
rm "civi-$DATE.sql.gz"

echo "✅ Done! Both Drupal and CiviCRM databases synced from live."
