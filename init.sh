wget https://getcomposer.org/download/1.9.0/composer.phar
php composer install
php artisan key:generate
php artisan config:cache