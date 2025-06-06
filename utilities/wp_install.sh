#!/bin/bash

sudo apt update
sudo apt upgrade -y
sudo apt-get install -y php-xml php-xmlwriter curl php-curl

cd /tmp && wget https://wordpress.org/latest.tar.gz
tar -xvf latest.tar.gz
sudo mkdir /var/www/html/learn
sudo cp -r wordpress/* /var/www/html/learn
sudo chown -R www-data:www-data /var/www/html/learn/
sudo chmod -R 755 /var/www/html/learn/
sudo mkdir /var/www/html/learn/wp-content/uploads
sudo chown -R www-data:www-data /var/www/html/learn/wp-content/uploads
echo "now got to http://yourserver.com/learn and follow the instructions to set up WordPress"