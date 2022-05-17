#!/bin/bash

# Exit when any command fails
set -e

SRC_DIR=/app/var/data
DST_DIR=/app/mercato

mkdir -p $SRC_DIR
mkdir -p $DST_DIR/fotos

# Unzip datafiles from Mercato stored in makkelijkemarkt objectstore
pushd $SRC_DIR
echo "unpacking Bestanden.zip"
stat Bestanden.zip
unzip -qq -o Bestanden.zip -d $DST_DIR/
echo "Done, unpacking Pasfotos.zip"
stat Pasfotos.zip
unzip -qq -o Pasfotos.zip -d $DST_DIR/fotos/
echo "Done"
popd

# Import the CSV data into the database
pushd $DST_DIR
echo "running import:perfectview:vervanger"
php /app/bin/console makkelijkemarkt:import:perfectview:vervanger Vervangers.CSV --env=prod
echo "Done, running import:perfectview:markt"
php /app/bin/console makkelijkemarkt:import:perfectview:markt Marktnaam.CSV --env=prod
echo "Done, running import:perfectview:koopman"
php /app/bin/console makkelijkemarkt:import:perfectview:koopman Koopman.CSV --env=prod
echo "Done, running import:perfectview:sollicitatie"
php /app/bin/console makkelijkemarkt:import:perfectview:sollicitatie Koopman_Markt.CSV --env=prod
echo "Done, running import:perfectview:foto"
php /app/bin/console makkelijkemarkt:import:perfectview:foto Koopman.CSV fotos --env=prod
echo "Done"
popd
