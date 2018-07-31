#!/usr/bin/env bash

docker run -i \
    --rm \
    -v $PWD:/app \
    -w /app \
    --user $(id -u):www-data \
    composer update --prefer-dist -o -vvv
