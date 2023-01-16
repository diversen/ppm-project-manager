#!/bin/sh
docker start mysql-server
docker run -it -v $PWD:/app --network=host --rm --name pebble-server php-cli-server