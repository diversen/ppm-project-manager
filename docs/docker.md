# Docker commands

I prefer just to install the php8.1-cli on my ubuntu-22.04 and a docker mysql-server image on my machine.

You may run it without both php or the mysql-server, and
the following docker images can be built and used for this purpose. 

## Build php-8.1-cli and mysql-server images

    ./tools/docker/build.sh

## Remove the images
    
    ./tools/docker/rm.sh

## Run the images

    ./tools/docker/run.sh

## Install vendor packages

    docker exec -it cli-server bash
    composer install
    exit

## Run a cli.sh command

    ./docker-cli.sh -h

## MySQL database creation and migration

    ./docker-cli.sh db --server-connect
    create database ppm;
    exit; # exit from container
    rm .migration # remove migration file if it exists from previous install
    ./docker-cli.sh migrate --up

Then you go to http://localhost:8000 and create an account.

# Docker mysql-server

Install (run) a MySQL image that will work (for development maybe):

    docker run -p 3306:3306 --name mysql-server -e MYSQL_ROOT_PASSWORD=password -d mysql:8.0

Connect using bash and create a database:

    docker exec -it mysql-server bash
    mysql -uroot -ppassword
    create database ppm;
    exit; # exit from mysql-server 
    exit; # exit from container

Or:

    ./cli.sh db --server-connect
    create database ppm;
    exit; # exit from mysql-server

List containers 

    docker container ls

Stop container (mysql-server):

    docker stop mysql-server

Start container (mysql-server) again:

    docker start mysql-server

Remove container (you will need run 'run' command again):

    docker rm mysql-server