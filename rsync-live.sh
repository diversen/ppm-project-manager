#!/bin/sh
rsync -avr --exclude 'logs' --exclude 'config-locale/*' * dennis@159.69.152.206:/home/dennis/www/ppm.10kilobyte.com/htdocs/
