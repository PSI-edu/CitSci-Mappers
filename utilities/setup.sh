#!/bin/bash

# To run this, copy it to the home directory and run `chmod +x setup.sh`


sudo apt update
sudo apt -y install apache2
sudo apt -y install certbot python3-certbot-apache
# add ServerName and edit ServerAlias in file below
sudo vi /etc/apache2/sites-available/000-default.conf
sudo apache2ctl configtest
sudo certbot --apache
sudo apt -y install php libapache2-mod-php php-mysql
sudo apt install git
sudo apt -y install nodejs
sudo apt -y install npm
cd ~
git clone https://github.com/PSI-edu/mappers.git
