#!/usr/bin/env bash

# Wait for MySQL...
counter=1
while ! mysql -h mysql -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "show databases;" > /dev/null 2>&1; do
    sleep 1
    counter=$((counter + 1))
    if [ $((counter % 20)) -eq 0 ]; then
        mysql -h mysql -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "show databases;"
        >&2 echo "Still waiting for MySQL (Count: $counter)."
    fi
done

counter=1
while ! curl --fail http://gpw-web > /dev/null 2>&1; do
    sleep 1
    counter=$((counter + 1))
    if [ $((counter % 20)) -eq 0 ]; then
        >&2 echo "Still waiting for Web (Count: $counter)."
    fi
done

indexer -c /var/lib/manticore/conf/manticore.conf --all

service cron start
crontab /var/lib/manticore/conf/crontab

searchd --nodetach --config /var/lib/manticore/conf/manticore.conf
