# README

![logo.svg](App/templates/assets/logo.svg)

PPM (PPM project mangement) is a single user project, task, and time management system. 

It has the following features:

- Create projects
- Create tasks connected to projects
- Add time entries on tasks
- Overview of time used per week / task / project
- Mobile and desktop friendly
- Fast: 100 lighthouse score

Made so that you can see what you spend my spare time on. 

# Usage

You can try it or use it on https://ppm.10kilobyte.com/account

It only works with `await / async` and some other 'modern' features enabled in the browser. 

There is no transpiling of the javascript. 

# Install

You will need a recently version of PHP. E.g. `PHP 8.0.0`

Clone the source code: 

    git clone https://github.com/diversen/php-project-manager

Install composer packages:

    cd php-project-manager && composer install

## Create a MySQL database

You will need some kind of database. I use MySQL, but maybe it will work with other databases as well. 

You don't have to use docker - this is just for ease of setup if you don't have a database server. 

Using docker for the database:

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

The above setup corresponds with the default `config/DB.php`. If you have altered the database name or the server user or the server password, you will need to edit it in this file. 

## Configuration

If you want to use SMTP for email confirmation, then you will need to remove `no_email_verify` in `Config/Account.php`. 

And you will need to edit the `SMTP` configuration in `config/SMTP.php`. 

If you need google login, you can setup google OAuth `config/Google.php`.

You can look at the other `config/` files, but you don't need to change these in order to run the system local now: 

    ./serv

The above command just runs the built-in PHP server. 

Point a browser to http://localhost:8000 and create an account, and log in.

## Other useful docker commands

List conainers 

    docker container ls

Stop container (mysql-server):

    docker stop mysql-server

Start container (mysql-server) again:

    docker start mysql-server

Remove container (you will need run 'run' command again):

    docker rm mysql-server

# Credits

[water.css](https://watercss.kognise.dev/) (A drop-in collection of CSS styles)

[logo.svg](https://github.com/bubkoo/logo.svg) (Create svg logos)

# License

MIT © [Dennis Iversen](https://github.com/diversen)

