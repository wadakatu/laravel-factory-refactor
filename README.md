# Laravel-Factory-Refactor

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

If you want to change the target directory, you can do that by using the `--test_dir` option.

```bash
php artisan refactor:factory --test_dir tests/Feature
```

---

By default, `app/Models` is the base directory for model classes.

If you want to change the model directory, you can do that by using the `--model_dir` option.

```bash
php artisan refactor:factory --model_dir app/Models/ForTest
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
