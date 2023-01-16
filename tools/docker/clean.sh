#!/bin/sh
# Remove php-cli-server image
docker rm php-cli-server

# Remove mysql-server container
docker stop mysql-server
docker rm mysql-server