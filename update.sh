git fetch --all && git reset --hard origin/master && git pull origin master
rm -rf composer.lock composer.phar
curl https://github.com/composer/composer/releases/latest/download/composer.phar > composer.phar
php composer.phar update -vvv
php artisan v2board:update
php artisan config:cache
pm2 restart pm2.yaml
