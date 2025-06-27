<?php

/* -------------------------------
   Set Mode
   ------------------------------- */

$localhost_dev = true; // Set to true if using localhost for development, false for anything else

/* -------------------------------
   Database Settings
   ------------------------------- */

$db_host = "db";
$db_port = 3306;
$db_username = "mappers_dbuser";
$db_password = "Ch4ngeM3"; // Using Docker? Check in docker-compose.yml
$db_name = "mappers_db";


/* -------------------------------
    Where is the vue app located?
   ------------------------------- */

$vue_url ="http://localhost:4040";

/* -------------------------------
   Auth0 Info
   ------------------------------- */

$auth0_audience = "set this in auth0";
$auth0_domain = ""; // e.g. "dev-12345678.us.auth0.com"
$auth0_api_secret = ""; // 32 random characters, used to verify the API requests
$auth_client_id = ""; // Your Auth0 Client ID