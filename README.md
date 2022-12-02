# README

![www/favicon_io/android-chrome-192x192.png](www/favicon_io/android-chrome-192x192.png)

PPM (PPM project mangement) is a single user project, task, and time management system. 

It has the following features:

- Create projects
- Create tasks connected to projects
- Add time entries on tasks
- Overview of time used per week / task / project
- Mobile and desktop friendly

# Usage

You can try it or use it on https://ppm.10kilobyte.com/

It only works with `await / async` and some other 'modern' features enabled in the browser. 

There is no transpiling of the javascript. 

If you have updated you browser the last couple of years you will be good to go. 

# Install

Works on:  `PHP >= 8.1`

Clone the source code: 

    git clone https://github.com/diversen/ppm-project-manager

Install composer packages:

    cd ppm-project-manager && composer install

Create a `config-locale` dir were you can different settings from machine to machine. 

The `config-locale` dir will override settings in `config`.

    mkdir config-locale

## Load MySQL DB

Create a database and change the settings in `config/DB.php` 

Check if you can connect:

    ./cli.sh db --connect
    exit

You can look at the other `config/` files, but you don't need to change these in order to run the system local now: 

Load the sql files found in `migration` into a database. 

    ./cli.sh migrate --up

## Run

Runs the built-in PHP server:

    ./serv

On an apache2 server you will need something like the following added to your configuration, e.g. in a  `.htaccess` file placed in `www` 

    RewriteEngine on
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?q=$1 [L,QSA]

## Login

Point a browser to http://localhost:8000, create an account and log in.

In the folder `config-locale` you can add any locale configuration the will override settings in `config`. 

Using SMTP for email confirmation:

* Remove `no_email_verify` in `Config/Account.php` or set it to false. 
* Edit the `SMTP` configuration in `config/SMTP.php`. 

Google login using OAuth:

* Setup google OAuth in `config/Google.php`.

# Docker commands

## MySQL

Install (run) a MySQL image that will work:

    docker run -p 3306:3306 --name mysql-server -e MYSQL_ROOT_PASSWORD=password -d mysql:5.7

Connect using bash and create a database:

    docker exec -it mysql-server bash
    mysql -uroot -ppassword
    create database ppm;
    exit; # exit from mysql-server 
    exit; # exit from container

Load SQL:

    docker exec -i mysql-server mysql -uroot -ppassword ppm  < ./sql/mysql.sql 

List conainers 

    docker container ls

Stop container (mysql-server):

    docker stop mysql-server

Start container (mysql-server) again:

    docker start mysql-server

Remove container (you will need run 'run' command again):

    docker rm mysql-server

# CSS

[water.css](https://watercss.kognise.dev/) (A drop-in collection of CSS styles)

# Build svg logo

[text-to-svg-cli](https://github.com/diversen/text-to-svg-cli/) (Create svg logos)

    npm install text-to-svg-cli -g
    text-to-svg-cli --config=logo.json
    scripts/logo.sh

[Favicon generator](https://favicon.io/favicon-generator/)

Convert:

    cd www && convert favicon_io/android-chrome-192x192.png -scale "70x70" assets/logo.png
	

# License

MIT © [Dennis Iversen](https://github.com/diversen)
