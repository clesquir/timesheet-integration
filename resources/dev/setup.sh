#!/usr/bin/env bash

set -ex

chmod +x bin/*
docker-compose down --remove-orphans
docker-compose build app
docker-compose run --rm app composer install
docker-compose up -d httpd
docker-compose ps
