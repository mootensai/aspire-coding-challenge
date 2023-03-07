#!/usr/bin/env bash

composer install
php artisan migrate:fresh
php artisan db:seed
php artisan serve