#!/bin/sh
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

#./artisan route:cache # Laravel cache
#./artisan config:cache # Laravel cache

echo "Migrating ......"

./artisan migrate

echo "Restarting queues ..."
./artisan queue:restart

echo "Starting crond ..."
/usr/sbin/crond

/usr/sbin/nginx -c /etc/nginx/nginx.conf

supervisorctl reload
/run.sh
