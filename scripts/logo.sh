#!/bin/sh
text-to-svg-cli --config=logo.json
cp logo.svg www/App/templates/assets/logo.svg
