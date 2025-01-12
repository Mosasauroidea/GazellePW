#!/bin/bash

run_service()
{
    /etc/init.d/$1 start || exit 1
}

# We'll need these anyway so why not kill some time while waiting on MySQL to be ready
su -c 'echo "====== Composer Install ======"; \
    composer --version; \
    composer install; \
' gazelle
echo -e "\n====== Yarn Install ======"
yarn
echo -e "\n====== Yarn Start ======"
yarn start &

# Wait for MySQL...
counter=1
while ! mysql -h mysql -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "show databases;" > /dev/null 2>&1; do
    sleep 1
    counter=$((counter + 1))
    if [ $((counter % 20)) -eq 0 ]; then
        mysql -h mysql -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "show databases;"
        >&2 echo "Still waiting for MySQL (Count: $counter)."
    fi;
done

echo "Run migrations..."
if ! FKEY_MY_DATABASE=1 LOCK_MY_DATABASE=1 /var/www/vendor/bin/phinx migrate; then
    echo "PHINX FAILED TO RUN MIGRATIONS"
    exit 1
fi

if [ ! -f /etc/php/7.4/cli/conf.d/99-boris.ini ]; then
    echo "Initialize Boris..."
    grep '^disable_functions' /etc/php/7.4/cli/php.ini \
        | sed -r 's/pcntl_(fork|signal|signal_dispatch|waitpid),//g' \
        > /etc/php/7.4/cli/conf.d/99-boris.ini
fi

echo "Start services..."

mkdir -p /var/www/logs
mkdir -p /var/www/.cache
chmod 777 /var/www/.cache
chown gazelle:gazelle  /var/www/.cache
truncate -s0  /var/www/logs/*.log

run_service cron
run_service nginx
run_service php7.4-fpm

crontab /var/www/.docker/web/crontab

tail -f /var/www/logs/*.log
