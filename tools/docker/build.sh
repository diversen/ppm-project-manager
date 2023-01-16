#!/bin/sh
# Start
docker build -f tools/docker/Dockerfile.php  -t php-cli-server .

# Create and start
docker run -p 3306:3306 --name mysql-server -e MYSQL_ROOT_PASSWORD=password -d mysql:8.0