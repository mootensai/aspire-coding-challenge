#!/usr/bin/env bash

composer install
php artisan sail:install
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate:fresh
./vendor/bin/sail artisan db:seed