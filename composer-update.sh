#!/usr/bin/env bash

docker run -i \
    --rm \
    -v $PWD:/app \
    -v $PWD/auth.json:/root/.composer/auth.json \
    -v $PWD/auth.json:/root/.config/composer/auth.json \
    -w /app \
    --user $(id -u):www-data \
    composer update --prefer-dist -o -vvv
