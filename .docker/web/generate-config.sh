#!/usr/bin/env bash

THIS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

TARGET=${THIS_DIR}/../../classes/config.php

if [ -f ${TARGET} ]; then
    exit 0;
fi

echo "GENERATING GAZELLE CONFIG..."
echo ""
sed -Ef $THIS_DIR/generate-config.sed \
    -e "s~\\\$MYSQL_USER~${MYSQL_USER}~" \
    -e "s~\\\$MYSQL_PASSWORD~${MYSQL_PASSWORD}~" \
    -e "s~\\\$TMDB_API_KEY~${TMDB_API_KEY}~" \
    -e "s~\\\$OMDB_API_KEY~${OMDB_API_KEY}~" \
    -e "s~\\\$DOUBAN_API_URL~${DOUBAN_API_URL}~" \
    -e "s~\\\$SITE_HOST~${SITE_HOST}~" \
    ${THIS_DIR}/../../classes/config.template.php  > ${TARGET}

echo ""
