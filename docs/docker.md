# Docker commands

## Build php-8.1-cli and mysql-server images

    ./tools/docker/build.sh

## Remove the images
    
    ./tools/docker/rm.sh

## Run the images

    ./tools/docker/run.sh

## Install vendor packages

When you run `./tools/docker/run.sh` you will get a container named `cli-server`.
You can connect to this container and run `composer install`:

    docker exec -it cli-server bash
    composer install
    exit

## Run a cli.sh command

    ./docker-cli.sh -h

## MySQL database creation and migration

You can open a new terminal and run the following commands: 

    ./docker-cli.sh db --server-connect
    create database ppm;
    exit; # exit from container
    rm .migration # remove migration file if it exists from previous install
    ./docker-cli.sh migrate --up

Then you go to http://localhost:8000 and create an account.

# Docker mysql-server

I personally prefer to use the php8-1-cli running on my own ubuntu machine, and then just
use a docker container for the mysql-server.

Install (run) a MySQL image that will work:

    docker run -p 3306:3306 --name mysql-server -e MYSQL_ROOT_PASSWORD=password -d mysql:8.0

Connect using bash and create a database:

    ./cli.sh db --server-connect
    create database ppm;

List containers 

    docker container ls

Stop container (mysql-server):

    docker stop mysql-server

Start container (mysql-server) again:

    docker start mysql-server

Remove container (you will need run 'run' command again):

    docker rm mysql-server