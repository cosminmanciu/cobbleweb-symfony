# CobbleWeb

## Requirements:
- mysql:8.0.33
- php:8.2
- nginx:1.21

## Features
- Symfony App 5.4
- 2 related Entities, One for Users and one for Photos
- 2 services : One for Local Photo Upload and one for Aws

## How to run the project

- Open git terminal
- `git clone https://github.com/cosminmanciu/cobbleweb-symfony.git`
- `cd .docker`
- `docker-compose up -d`
- Connect to Docker PHP container
- `composer install`
- `php bin/console doctrine:schema:update --force`
