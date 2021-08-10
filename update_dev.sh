git fetch --all && git reset --hard origin/dev && git pull origin dev
rm -rf composer.lock composer.phar
wget https://getcomposer.org/download/2.0.13/composer.phar
php composer.phar update -vvv
php artisan v2board:update
php artisan config:cache
pm2 restart pm2.yaml
