# CitSci-Mappers
Software to support web-based image annotation.

# Before you get started
### Images
- A logo (150 x 150) for the login screen. (This will need to be publicly hosted)
- A Favicon (replace what's in the /src/assets/images directory. Details at end of readme on how to create)

### Production auth domain
You will need a domain name (we used a subdomain, auth.yourdomain.com) that you can edit the DNS record for, 
including adding a CNAME record. This is required for the Auth0 setup.

### Setup your server
You will need a LAMP server that also has NodeJS installed.

N.B. The software was developed and tested on OSX with Docker and
deployed on AWS using Ubuntu EC2 and MariaDB on an RDS.

We assume you have the software cloned from github and either on your server (option 1) or on a machine
running docker (option 2).

### Option 1: LAMP Stack
This software is designed to run on a standard LINUX server running an Apache with
MariaDB/MySQL, and PHP. You will also need a development environment that also has NodeJS + npm
installed or the ability to build NodeJS + npm on your production environment. 
Need help getting setup? We recommend the DigitalOcean [LAMP Stack on Ubuntu](https://www.digitalocean.com/community/tutorials/how-to-install-lamp-stack-on-ubuntu),
[Let's Encrypt](https://www.digitalocean.com/community/tutorials/how-to-secure-apache-with-let-s-encrypt-on-ubuntu),
and [NodeJS](https://www.digitalocean.com/community/tutorials/how-to-install-node-js-on-ubuntu-22-04) tutorials.

You will also need the GD library installed for PHP.
```
sudo apt-get update && sudo apt-get install -y \
    php-gd \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev
```

We include a server setup script, ```utilities/setup.sh```, that 
can be used to set up a generic ubuntu server. To get started in ubuntu, copy the file into your root directory and run it.

Next up - Your database config!

You shouldn't use the root db user with the application. You will need to set up 
a project database and user. An example is below.
```
CREATE DATABASE 'mappers_db';
CREATE USER 'mappers_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVLEGES ON mappers_db.* to 'mappers_db'@'localhost';
FLUSH PRIVILEGES;
```

### Option 2: Docker (Good for development, not recommended for production)
You'll need the docker desktop and docker cli tools installed. We'll provide additional
details as we go through this tutorial. 

# Auth0 Setup
We are using Vue Composition API. Help setting up Auth0 comes from the [Vue.js Authentication By Example](https://developer.auth0.com/resources/guides/spa/vue/basic-authentication)
tutorial. Additional useful information here: https://developer.auth0.com/resources/guides/spa/vue/basic-authentication

Create a new application
- Single-page application
- vue
- Social Connections (pick at will)
- Enable users to enter a user ID & Password (They will get grouchy if you don't do this)
- After verifying it works, and configuring the layout, you'll be presented with a setup screen that needs a bunch of information
  - Application Login URI: Leave Blank
  - Allowed Callback URLs: http://YOURURL/callback
  - Allowed Logout URLs: http://YOURURL/
  - leave everything else as their default values
- Click "Save Changes"

Create a new action so each user gets written into the database. NOTE: development on localhost requires
a different setup than production, so you will need to do this twice, once for each environment.
- In auth0 go to Actions->Library
- Click [Create Action] to add a new action and select "Build from scratch"
- Give the action a name (We used "Consent Check"), and use the default runtime "Node 22"
- On the new screen, click the blue "Add Secret" button, and enter 
  - Key: CONSENT_FORM_URL
  - Value: https://your.url/consent 
- click the blue "Add Secret" button again, and this time enter
  - Key: PROFILE_URL
  - Value: https://your.url/profile

```
exports.onExecutePostLogin = async (event, api) => {
  // Check if the email is verified
  if (!event.user.email_verified) {
    api.access.deny(`Please verify your email before continuing.`);
  }
  const { consentGiven } = event.user.user_metadata || {};
  // redirect to consent form if user has not yet consented
  if (!consentGiven && api.redirect.canRedirect()) {
    const options = {
        query: {
          email: event.user.email,
          id: event.user_id,
        },
      };
    api.redirect.sendUserTo(event.secrets.CONSENT_FORM_URL, options);
  }
};

exports.onContinuePostLogin = async (event, api) => {
    if (event.request.body.confirm === "yes") {
      api.user.setUserMetadata("consentGiven", true);
      api.user.setUserMetadata("consentTimestamp", Date.now());
      api.redirect.sendUserTo(event.secrets.PROFILE_URL)
    return;
  } else {
    return api.access.deny("User did not consent");
  }
};
```
  - click "Save Draft" and "Deploy" 
  - Go to Actions -> Triggers -> post-login. 
  - Click "Custom" and drag your action into the workflow between Start and Complete
  - CLick Apply

Finally, set it up so that if people use multiple methods to login, the accounts are
linked using their email
- Click on Extensions
- Search on "link"
- install Auth0 Account Link
  - You will want to format this to match the rest of your styling

NOTE: YOU WILL NEED TO DO THIS SEPARATELY FOR BOTH YOUR DEV ENVIRONMENT AND YOUR 
PRODUCTION ENVIRONMENT AND BOTH WILL NEED TO BE TIED TO A DIFFERENT ACCOUNT or you will 
need to pay money. 

**For Your Production Environment**, setup a custom domain by going to Branding-> Custom Domains,
Adding a credit card (you will not be charged unless you go over the free tier), and then follow
the onscreen instructions. You will also need to get developer keys for your social logins. This is 
not covered in this readme, but you can help in the Auth0 documentation.

# Clone the Repo

How you do this is up to you. This code was developed locally using PHPStorm and we used 
"project from version control" to clone the repo, and used the terminal window in 
PHPStorm to run the commands for docker.

On our AWS servers we used the following commands to clone the repo (this is part of the
setup.sh script) into the users home directory. Compiled code will be moved into 
web-accessible directories later.

```
cd ~
git clone https://github.com/PSI-edu/CitSci-Mappers.git
```

# Architecture decisions

This software has a javascript (Vue) front end and a PHP API. You will also need to serve all 
the images people will be annotating. These can live in separate
places and before proceeding, you need to decide where you will stick everything. 

Our recommendations are as follows:
- **For development**, use Docker. This will put your API (unless you change things) at ```http://localhost:8081```.
- **For production**, either use virtual hosts to put the api and Vue apps on different URLs 
(for instance, ```api.yourdomain.com```) or put the api in a subdirectory. (for instance, 
```yourdomain.com/api```).
- **For all cases**: Put your images in one place that is publicly accessible and can be reached
by both your dev and production systems. We used AWS S3, but you can use any web server that is 
publicly accessible. If you use S3. You will need to set up a bucket policy to allow public access to the images. 

If you are using different domains for the API and Vue app, now is the time to setup your
virtual hosts and make sure they are working with at least a test page.

# Setup Vue app environment
- copy .env.example to .env
- go to your application's Auth0 Settings tab (Applications->Applications->Click application name->Settings)
- open .env and copy in the values from auth0 for Domain, ClientID, Client Secret, and add in the API url you decided on above

```
cd CitSci-Mappers
cp .env.example .env
vi .env
```


# API setup 
Next up you need to create and edit the settings.php file for the api
```
cd api
cp settings-example.php settings.php
vi settings.php
```


# Deploy the software

### Local Vue Development
Just want to see the app? Not interested in anything else? Have at it with npm on your localhost!

` npm install `

` npm run dev `

But now you don't have a functioning API...

You do need the api if you want to do anything other than UX/UI work. This
means you need the api directory on a webserver (system or docker based). 

### Docker for API Development (and Vue)

Start the docker container using

`docker-compose build --no-cache; docker-compose up -d --no-build; docker-compose exec api composer install`

With docker, it is running a build of the app, and a build of the api, so you'll need to rerun that container to see changes made in vue if you
use the docker container version of the Vue app. 
One frankenstein solution (which we used) is to use npm run dev for the vue app (localhost:4040) and docker container for the api (localhost:8081).


### LAMP stack for Dev or Production:
Build the distribution and copy it to your webserver. Example below:
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

We created a shell script to pull updates from the repo, build the distribution, and copy it as needed.
It assumes your Vue app is at /var/www/html and your api is at /var/www/html/api.
To take the best advantage of this script, copy it out of the repo and make an alias in your .bashrc file
```
cd ~
mkdir scripts
cd scripts
cp ~/CitSci-Mappers/utilities/dodist.sh .
```
Then add the following line to your .bashrc file
```
alias dodist='source ~/scripts/dodist.sh'
```
then run `source ~/.bashrc` to load the new alias.

Now you can type ```dodist``` from anywhere to pull the latest code from the repo, build the distribution, and copy it to your webserver."

# Install the database
Got to ```http://yourAPIurl.com/installer-tools/install.php```

That's it. You now have a database.
 
# Adding Data to annotate

NOTE TO DOCKER USERS: The utilities directory is not mounted in the docker container. You'll need to
move the utilities directory into the api directory and edit the path to settings.php DO NOT DO THIS
ON A PUBLICLY ACCESSIBLE CONTAINER. This is a localhost solution. To access Docker from the command line, 
use ```docker ps``` to get the container ID, and then use ```docker exec -it <container_id> bash``` to get a shell in the container.


This software allows people to map/annotate/comment on images that are displayed within a user interface.
It is understood these images are likely coming from something larger that we're calling a master image. Since
our software works entirely in pixel space, the database is designed to track the following information
- Which application an app belongs too
- Master Images and their metadata
- The Images the users will be shown that are chopped out of the master images and their metadata

The Master Image should be a PNG with the stretch you want displayed on screen.


TODO Make everything but imagechop possible through an admin interface

We have created tools in the utilities directory (Do not make this web accessible!!) that can be used to chop
up your master images, and upload details into the database. These are designed to be run from the commandline, 
and we recommend running imagechop locally and 
sftping or otherwise uploading the produced files to your image repo through a webtool.


Currently, the installer pre-installs 3 apps
- 1 - (not actually an app) - Look Ahead
- 2 - Mars Mosaic (app ID 2)
- 3 - Lunar Melt (app ID 3)

TODO Make app setup possible through admin interface

### create a scratch directory
```cd CitSci-Mappers/utilities; mkdir scratch```

### 1_imagechop.php
Please Note: This produces a ton of images that likely don't belong on your webserver.

Assumed file structure: Place the master image in ```utilities/scratch```
Usage: ```cd utilities; php 1_imagechop.php <master_image.png>```
Output (into scratch directory): 
* 1 text file following the naming convention `masterimagename.txt` with the following columns
  * Line 1 - Master Image Filename: the name of the master image (e.g. masterimagename.png)
  * Line 2 thru Line N, tab separated values as follows
    * image_name: the name of the image (e.g. masterimagename_X_Y.png)
    * x: the x coordinate of the top left corner of the image
    * y: the y coordinate of the top left corner of the image
* N 450x450 images with 10% overlap in each direction, and the following naming convention `masterimagename_X_Y.png`
Where X and Y are the pixel coordinates of the top left corner of the image.

The images should all be placed on a data server that is publicly accessible and that you don't have to pay
bandwidth charges for (this is where a lot of bandwidth costs come from). 
We used AWS S3 with the following directory structure
* ProjectName
  * Region
    * masterimagename.txt
    * masterimagename
      * masterimagename.png
      * masterimagename_X1_Y1.png
      * masterimagename_X2_Y2.png
      * ...
      * masterimagename_XN_YN.png
  
### 2_upload_imageset.php
SFTP your master_image.txt files from the computer where you did the image chop into the utilities/scratch directory on your server.
Assumed file structure: You guessed it, the master_image.txt file should be in the scratch directory.
Usage: ```cd utilities; php 2_upload_imageset.php app_id imageset_name <master_image.txt> https://Imageserver.com/path_to_images/```

These commands should be the same on both your dev and production servers if you put your images in a place
both can access. To make life easier, consider putting the compands in a script file. Example provided.

### 3_


# Extras
### Content management with Wordpress
This is not a Content Management System (CMS). We recommend using Wordpress to manage your content, and the included 
.htaccess file assumes it will be installed in the /learn directory of your webserver. 

You will need to create a database and database user for Wordpress:
```
mysql -u root -p 
CREATE DATABASE wp_db;
CREATE USER 'wp_user'@'YOURHOST' IDENTIFIED BY 'password'; # use % as wildcard for all hosts

```

Once you have the db and user created, use the wp_install script to install Wordpress on your server. 

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
- copy new files and archive the master image on your image server

### HTACCESS Password lock
Want to keep your site private for testing? Do it with htaccess!

```
sudo htpasswd -c /etc/apache2/.htpasswd BuildTeam
vi ~/CitSci-Mappers/.htaccess
```

Now add the following lines at the top
```
AuthType Basic
AuthName "Preview Access Only"
AuthUserFile /etc/apache2/.htpasswd
Require valid-user
```

You'll need to copy the .htaccess file to your web server and comment out the line to update .htaccess in your script dodist


