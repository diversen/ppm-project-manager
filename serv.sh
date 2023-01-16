#!/bin/sh

if [ -z "$1" ]
  then
    PORT=8000
fi

if [ "$1" ]
  then
    PORT=$1
fi

php -S localhost:$PORT -t www 
