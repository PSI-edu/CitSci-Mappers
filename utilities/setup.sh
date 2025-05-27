#!/bin/bash

# To run this, copy it to the home directory and run `chmod +x setup.sh`


sudo apt update
sudo apt -y install apache2
sudo apt -y install certbot python3-certbot-apache
# add ServerName and edit ServerAlias in file below
# also add beneath DocumentRoot
# Redirect / https://yourserver.com/
# <Directory /var/www/html>
#    Options Indexes FollowSymLinks
#    AllowOverride All
#    Require all granted
# </Directory>
sudo vi /etc/apache2/sites-available/000-default.conf
sudo apache2ctl configtest
sudo apache2ctl restart
sudo certbot --apache
sudo apt -y install php libapache2-mod-php php-mysql
sudo apt install mysql-client-core-8.0
sudo apt install git
sudo apt -y install nodejs
sudo apt -y install npm
sudo apt-get update && sudo apt-get install -y \
    php-gd \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev
cd ~
git clone https://github.com/PSI-edu/CitSci-Mappers.git
