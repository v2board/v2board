git fetch --all && git reset --hard origin/master && git pull origin master
php composer.phar update -vvv
php artisan v2board:update
php artisan config:cache
pm2 restart pm2.yaml
