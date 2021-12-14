#!/bin/bash

EXIT_CODE=0

# Exit when any command fails
set -e

SRC_DIR=/app/var/data
DST_DIR=/app/mercato

mkdir -p $SRC_DIR
mkdir -p $DST_DIR/fotos

# Unzip datafiles from Mercato stored in makkelijkemarkt objectstore
pushd $SRC_DIR
#check file age
MAXAGE=90000 # 25 hours = 90.000 seconcs
FILEAGE=$(($(date +%s)-$(stat -c %Y -- Bestanden.zip)))
PHOTOAGE=$(($(date +%s)-$(stat -c %Y -- Pasfotos.zip)))
if [ $FILEAGE -gt $MAXAGE ]
then
    echo "Bestanden.zip is older than 1 day!"
    EXIT_CODE=99
fi
if [ $PHOTOAGE -gt $MAXAGE ]
then
    echo "Pasfotos.zip is older than 1 day!"
    EXIT_CODE=99
fi
echo "unpacking Bestanden.zip"
unzip -qq -o Bestanden.zip -d $DST_DIR/
echo "Done, unpacking Pasfotos.zip"
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

exit $EXIT_CODE