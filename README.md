# Laravel-Factory-Refactor

[![Packagist Version](https://img.shields.io/packagist/v/wadakatu/laravel-factory-refactor)](https://packagist.org/packages/wadakatu/laravel-factory-refactor)
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/wadakatu/laravel-factory-refactor)](https://packagist.org/packages/wadakatu/laravel-factory-refactor)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/wadakatu/laravel-factory-refactor/blob/main/LICENSE)

This package will help you to refactor the style of factory call from helper to static method for Laravel 8.x.

With this package, you can save plenty of time and focus on other important things.

## Installation

You can install the package via composer:

```bash
composer require wadakatu/laravel-factory-refactor --dev
```

## How to Use

To refactor the style of factory call from helper to static method, run the artisan command:

```bash
php artisan refactor:factory
```

---

By default, all factory call under `tests/` directory are the target of refactoring.

If you want to change the target directory, you can do that by using the `--dir` option.

```bash
php artisan refactor:factory --dir tests/Feature
```

---

## Example

### Before
```phpt
        factory(User::class)->make();
        factory(App\Models\User::class)->make();
        factory(User::class, 10)->make();
        factory(App\Models\User::class, 10)->make();
```
### After
```phpt
        User::factory()->make();
        App\Models\User::factory()->make();
        User::factory()->count(10)->make();
        App\Models\User::factory()->count(10)->make();
```

## License

The MIT License (MIT). Please see [license file](LICENSE.md) for more information.
