rm -rf composer.phar
wget https://getcomposer.org/download/2.0.13/composer.phar -O composer.phar
php composer.phar install -vvv
php artisan v2board:install
