# CitSci-Mappers
Software to support web-based image annotation.

# Before you get started
### Images
- A logo (150 x 150) for the login screen. (This will need to be publicly hosted)
- A Favicon (replace what's in the /src/assets/images directory. Details at end of readme on how to create)


### Setup your server
You will need a LAMP server that also has NodeJS installed.

N.B. The software was developed on a Mac with Docker and
deployed on AWS using an Ubuntu EC2 node and MariaDB RDS.

We assume you have the software cloned from github and either on your development server (option 1) or on a machine
running docker (option 2).

### Option 1: LAMP Stack
This software is designed to run on a standard LINUX server running an Apache with
MariaDB/MySQL, and PHP. You will also need a development environment that also has NodeJS + npm
installed or the ability to build NodeJS + npm on your production environment.

Need help getting setup? We
recommend the DigitalOcean [LAMP Stack on Ubuntu](https://www.digitalocean.com/community/tutorials/how-to-install-lamp-stack-on-ubuntu),
[Let's Encrypt](https://www.digitalocean.com/community/tutorials/how-to-secure-apache-with-let-s-encrypt-on-ubuntu),
and [NodeJS](https://www.digitalocean.com/community/tutorials/how-to-install-node-js-on-ubuntu-22-04) tutorials.
There is an example script of everything we did to setup our server in the utilities folder.

### Option 2: Docker (Good for development, not recommended for production)
You'll need the docker desktop and docker cli tools installed.

# Auth0 Setup
We are using Vue Composition API. Help setting up Auth0 comes from the [Vue.js Authentication By Example](https://developer.auth0.com/resources/guides/spa/vue/basic-authentication)
tutorial. Additional useful information here: https://developer.auth0.com/resources/guides/spa/vue/basic-authentication

Create a new application
- Single page application
- vue
- Social Connections (pick at will)
- Enable users to enter a user ID & Password (They will get grouchy if you don't do this)

Setup the app to run on your system
- copy .env.example to .env
- go to your application's Settings tab (Applications->Applications->Click application name->Settings)
- open .env and copy in the values from auth0 for Domain, ClientID, Client Secret

# API setup with MariaDB
You need to edit the settings.php file for the api
```
cd api
cp settings-example.php settings.php
vi settings.php
```

# Development Environment

Just want to see the app? Not interested in anything else? Have at it with npm!

` npm install `

` npm run dev `

But now you don't have a funcitoning API...

You do need the api if you want to do anything other than UX/UI work. This
means you need the api
directory to be accessible. One option is to point a virtual host at the 
api directory, and then put that URL in the .env file. Another option is Docker 
and put the docker container url (http://localhost:8081 by default) in .env

` docker-compose build --no-cache `

` docker-compose up --no-build `

With docker, it is running a build of the app, so you'll need to rerun that container to see changes made in vue.
One frankenstein solution is to use npm run dev for the vue app and docker for everything else. 

# Production Environment
clone the git repo to somewhere that isn't your web directory (I used ~), then cd into the repo, build 
everything, and then copy the dist contents to your web directory
```
cd ~
git clone https://github.com/PSI-edu/CitSci-Mappers.git
cd CitSci-Mappers
npm install
npm run build
sudo cp .htaccess /var/www/html
sudo cp dist/* /var/www/html
sudo cp api/* /var/www/html
```



# Extras
### Image creation
To create the favicon and navbar icon
- Create the icon as SVG and name it icon.svg
- Install Inkscape and ImageMagick from their websites
```
inkscape ./icon.svg --export-width=32 --export-filename="./tmp.png";
magick ./tmp.png ./icon-32.png;
rm ./tmp.png;
inkscape ./icon.svg --export-width=16 --export-filename="./tmp.png";
magick ./tmp.png ./icon-16.png;
rm ./tmp.png;
magick ./icon-32.png ./icon-16.png ./favicon.ico;
npx svgo --multipass icon.svg;
inkscape --export-type="png" --export-width=180 --export-filename="./apple-touch-icon.png" ./icon.svg;
inkscape ./icon.svg --export-width=36 --export-filename="./nav-icon.png";
```
- copy new files to images directory


