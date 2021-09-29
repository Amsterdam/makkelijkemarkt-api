#!/bin/bash

# Exit when any command fails
set -e

SRC_DIR=/app/var/data
DST_DIR=/app/mercato

mkdir -p $SRC_DIR
mkdir -p $DST_DIR/fotos

# Unzip datafiles from Mercato stored in makkelijkemarkt objectstore
pushd $SRC_DIR
unzip -o Bestanden.zip -d $DST_DIR/
unzip -o Pasfotos.zip -d $DST_DIR/fotos/
popd

# Import the CSV data into the database
pushd $DST_DIR
php /app/bin/console makkelijkemarkt:import:perfectview:vervanger Vervangers.CSV --env=prod
php /app/bin/console makkelijkemarkt:import:perfectview:markt Marktnaam.CSV --env=prod
php /app/bin/console makkelijkemarkt:import:perfectview:koopman Koopman.CSV --env=prod
php /app/bin/console makkelijkemarkt:import:perfectview:sollicitatie Koopman_Markt.CSV --env=prod
php /app/bin/console makkelijkemarkt:import:perfectview:foto Koopman.CSV fotos --env=prod
popd
