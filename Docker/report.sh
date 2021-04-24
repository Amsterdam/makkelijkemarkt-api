#!/bin/bash

startYear=$(date +'%Y')
startDate="$startYear-01-01"
endDate=$(date +'%Y-%m-%d')
targetDir="/app/public/download"
csvFile="$targetDir/factuur-report-$startDate-$endDate.csv"

mkdir -p "$targetDir"

php /app/bin/console makkelijkemarkt:report:factuur $startDate $endDate --env=prod > "$csvFile"
md5sum "$csvFile" > "$csvFile.md5"

find "$targetDir" -type f -mtime +14 -delete
