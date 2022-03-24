#!/bin/sh
text-to-svg-cli --config=logo/logo.json
cp logo/logo.svg www/App/templates/assets/logo.svg
