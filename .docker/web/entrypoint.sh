#!/bin/bash

run_service()
{
    /etc/init.d/$1 start || exit 1
}

# We'll need these anyway so why not kill some time while waiting on MySQL to be ready
su -c 'echo -e "====== Composer Install ======"; \
    composer --version; \
    composer install; \
    echo -e "\n====== Yarn Install ======"; \
    yarn; \
    echo -e "\n====== Yarn Start ======"; \
    yarn start & \
' gazelle

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

if [ ! -f /var/www/classes/config.php ]; then
    bash /var/www/.docker/web/generate-config.sh
    chmod 664 /var/www/classes/config.php
    chown -R gazelle:gazelle /var/www/classes/config.php
fi

echo "Run migrations..."
if ! FKEY_MY_DATABASE=1 LOCK_MY_DATABASE=1 /var/www/vendor/bin/phinx migrate; then
    echo "PHINX FAILED TO RUN MIGRATIONS"
    exit 1
fi

if [ ! -f /etc/php/7.3/cli/conf.d/99-boris.ini ]; then
    echo "Initialize Boris..."
    grep '^disable_functions' /etc/php/7.3/cli/php.ini \
        | sed -r 's/pcntl_(fork|signal|signal_dispatch|waitpid),//g' \
        > /etc/php/7.3/cli/conf.d/99-boris.ini
fi

echo "Start services..."

touch /var/log/fpm-php.www.log
chmod 777 /var/log/fpm-php.www.log

run_service cron
run_service nginx
run_service php7.3-fpm

crontab /var/www/.docker/web/crontab

tail -f /var/log/nginx/access.log
