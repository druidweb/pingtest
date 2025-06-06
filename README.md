# Ping CRM

A demo application to illustrate how Inertia.js works.

![](https://raw.githubusercontent.com/druidweb/pingcrm/main/screenshot.png)

This version is completely updated to use

- Laravel 12.x
- PHP 8.3
- Inertia 2.x
- Vue 3.x
- Vite 6.x
- Tailwind 4.x
- Pest 3.x

Special thanks to the following people for their PR contributions to the original `inertiajs/pingcrm` repository:

- [Billy Bouman](https://github.com/BillyBouman-2B-IT)
- [Martin Chevignard](https://github.com/mchev)
- [Kajal Kumar Das](https://github.com/kajal452)
- [Nathan Taylor](https://github.com/ntaylor-86)

Your pull requests to the original repository were very valuable and much appreicated. Thank you!

As a general note, the version uses `bun` as the package manager. You can use `npm` or `yarn` if you prefer, but `bun` is the default.

We also require PHP 8.3, it seems that 8.4 has issues with code coverage reporting. Once these issues are resolved we'll update the mandatory PHP version to 8.4.

## Installation

Clone the repo locally:

```sh
git clone https://github.com/druidweb/pingcrm.git pingcrm
cd pingcrm
```

Install PHP dependencies:

```sh
composer install
```

Install Node dependencies:

```sh
bun i
```

Start the dev server:

```sh
bun run dev
```

Build production assets:

```sh
bun run build
```

For updating node and composer dependencies::

```sh
bun run cb
```

Setup configuration:

```sh
cp .env.example .env
```

Generate application key:

```sh
php artisan key:generate
```

Create an SQLite database. You can also use another database (MySQL, Postgres), simply update your configuration accordingly.

```sh
touch database/database.sqlite
```

Run database migrations:

```sh
php artisan migrate
```

Run database seeder:

```sh
php artisan db:seed
```

Run the dev server (the output will give the address):

```sh
php artisan serve
```

You're ready to go! Visit Ping CRM in your browser, and login with:

- **Username:** johndoe@example.com
- **Password:** secret

## Running tests

To run the Ping CRM tests, run:

```
php artisan test
```
