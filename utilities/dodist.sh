#!/bin/bash
cd ~/CitSci-Mappers
git fetch
git pull
npm install
npm run build
cd api
sudo rm composer.lock
composer install
cd ..
sudo mkdir /var/www/html/api
sudo cp -R ~/CitSci-Mappers/.env /var/www/html/.env
sudo cp -R ~/CitSci-Mappers/.htaccess /var/www/html/.htaccess
sudo cp -R ~/CitSci-Mappers/dist/* /var/www/html/
sudo cp -R ~/CitSci-Mappers/api/* /var/www/html/api/
sudo cp -R ~/CitSci-Mappers/api/.htaccess /var/www/html/api/.htaccess