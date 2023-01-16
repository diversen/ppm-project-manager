#!/bin/sh
docker start mysql-server
docker run -it -v $PWD:/app --network=host --rm --name cli-server php-cli-server 