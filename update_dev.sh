#!/bin/bash

git fetch --all && git reset --hard origin/dev && git pull origin dev
git checkout dev
rm -rf composer.lock composer.phar
wget https://github.com/composer/composer/releases/latest/download/composer.phar -O composer.phar
php composer.phar update -vvv
php artisan v2board:update

if [ -f "/etc/init.d/bt" ]; then
  chown -R www $(pwd);
fi
