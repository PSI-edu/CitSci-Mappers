#!/bin/bash
cd ~/CitSci-Mappers
git fetch
git pull
npm install
npm run build
sudo rm -R /var/www/html/*
sudo mkdir /var/www/html/api
sudo cp -R ~/CitSci-Mappers/dist/* /var/www/html/
sudo cp -R ~/CitSci-Mappers/api/* /var/www/html/api/