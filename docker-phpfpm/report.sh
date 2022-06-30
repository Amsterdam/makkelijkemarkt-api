#!/bin/sh

startYear=$(date +'%Y')
startDate="$startYear-01-01"
endDate=$(date +'%Y-%m-%d')
targetDir="/var/www/public/download"
csvFile="$targetDir/factuur-report-$startDate-$endDate.csv"

mkdir -p "$targetDir"

php /var/www/bin/console app:factuur:report $startDate $endDate | tee "$csvFile"
md5sum "$csvFile" > "$csvFile.md5"

find "$targetDir" -type f -mtime +14 -delete
