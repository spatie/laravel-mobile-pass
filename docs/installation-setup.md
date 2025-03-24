---
title: Installation & setup
weight: 4
---

You can install the package via composer:

```bash
composer require spatie/laravel-mobile-pass
```

## Register your application at Apple

TODO: provide instructions

## Publishing the config file

Optionally, you can publish the `mobile-health` config file with this command.

```bash
php artisan vendor:publish --tag="mobile-pass-config"
```

This is the content of the published config file:

```php
TODO: paste final config file here
```

## Migrating the database

The package uses the database to track generate passes and registrations. You can publish and run the included migration to create the necessary tables.

```bash
php artisan vendor:publish --tag="mobile-pass-migrations"
php artisan migrate
```

## Registering the routes

The package can receive device registration requests and logs from Apple. To set up the necessary routes, call the `mobilePass` macro in your routes file.

```php
// in your routes file

Route::mobilePass();
```
