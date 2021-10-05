rm -rf composer.phar
curl https://github.com/composer/composer/releases/latest/download/composer.phar > composer.phar
php composer.phar install -vvv
php artisan v2board:install
